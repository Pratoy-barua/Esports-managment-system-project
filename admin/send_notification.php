<?php
session_start();
require_once '../config/database.php';
require_once '../config/session.php';

requireAdmin();
$conn = getConnection();

// Validate ID
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header("Location: notifications.php");
    exit;
}

$notification_id = (int) $_GET['id'];
$admin_id = $_SESSION['user_id'];

$conn->begin_transaction();

try {

    /* =========================
       1. Fetch Admin Notification
    ========================== */
    $stmt = $conn->prepare("
        SELECT *
        FROM admin_notifications
        WHERE id = ? AND status = 'draft'
        LIMIT 1
    ");
    $stmt->bind_param("i", $notification_id);
    $stmt->execute();
    $notification = $stmt->get_result()->fetch_assoc();

    if (!$notification) {
        throw new Exception("Notification not found or already sent.");
    }

    /* =========================
       2. Resolve Target Users
       users PK = user_id
    ========================== */
    $users = [];

    switch ($notification['target_type']) {

        case 'ALL':
            $users = $conn->query("
                SELECT user_id
                FROM users
                WHERE is_active = 1
            ")->fetch_all(MYSQLI_ASSOC);
            break;

        case 'STUDENTS':
            $users = $conn->query("
                SELECT user_id
                FROM users
                WHERE profession = 'Student'
                  AND is_active = 1
            ")->fetch_all(MYSQLI_ASSOC);
            break;

        case 'SUBSCRIBED_STUDENTS':
            $users = $conn->query("
                SELECT DISTINCT s.user_id
                FROM subscriptions s
                JOIN users u ON u.user_id = s.user_id
                WHERE s.is_active = 1
                  AND u.is_active = 1
            ")->fetch_all(MYSQLI_ASSOC);
            break;

        case 'UNIVERSITY':
            $stmt = $conn->prepare("
                SELECT u.user_id
                FROM users u
                JOIN student_profiles sp ON sp.user_id = u.user_id
                JOIN universities uni ON uni.university_id = sp.university_id
                WHERE uni.name = ?
                  AND u.is_active = 1
            ");
            $stmt->bind_param("s", $notification['target_value']);
            $stmt->execute();
            $users = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
            break;

        case 'PROFESSION':
            $stmt = $conn->prepare("
                SELECT user_id
                FROM users
                WHERE profession = ?
                  AND is_active = 1
            ");
            $stmt->bind_param("s", $notification['target_value']);
            $stmt->execute();
            $users = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
            break;

        case 'INDIVIDUAL':
            $stmt = $conn->prepare("
                SELECT user_id
                FROM users
                WHERE (username = ? OR email = ?)
                  AND is_active = 1
            ");
            $stmt->bind_param(
                "ss",
                $notification['target_value'],
                $notification['target_value']
            );
            $stmt->execute();
            $users = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
            break;
    }

    if (empty($users)) {
        throw new Exception("No active users found.");
    }

    /* =========================
       3. Insert User Notifications
    ========================== */
    $insert = $conn->prepare("
        INSERT INTO notifications
        (user_id, title, message, notification_type, link_url)
        VALUES (?, ?, ?, ?, ?)
    ");

    $message = !empty($notification['short_message'])
        ? $notification['short_message']
        : $notification['full_message'];

    foreach ($users as $u) {
        $insert->bind_param(
            "issss",
            $u['user_id'],
            $notification['title'],
            $message,
            $notification['type'],
            $notification['redirect_url']
        );
        $insert->execute();
    }

    /* =========================
       4. Update Admin Notification
    ========================== */
    $upd = $conn->prepare("
        UPDATE admin_notifications
        SET status = 'sent'
        WHERE id = ?
    ");
    $upd->bind_param("i", $notification_id);
    $upd->execute();

    /* =========================
       5. Admin Log
    ========================== */
    $log = $conn->prepare("
        INSERT INTO admin_logs
        (admin_id, action, target_type, target_id, description, ip_address)
        VALUES (?, 'SEND', 'Notification', ?, ?, ?)
    ");

    $desc = "Sent notification '{$notification['title']}' to " . count($users) . " users";
    $ip = $_SERVER['REMOTE_ADDR'] ?? 'UNKNOWN';

    $log->bind_param("iiss", $admin_id, $notification_id, $desc, $ip);
    $log->execute();

    $conn->commit();
    header("Location: notifications.php?sent=1");
    exit;

} catch (Exception $e) {
    $conn->rollback();
    die("Error: " . $e->getMessage());
}
