<?php
/**
 * Admin - Verify Payment
 * Path: /admin/verify_payment.php
 */

require_once __DIR__ . '/../config/session.php';
require_once __DIR__ . '/../config/database.php';

requireAdmin();
$conn = getConnection();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: orders.php");
    exit;
}

$order_id = (int) $_POST['order_id'];
$status = $_POST['payment_status']; // Successful | Failed

if (!in_array($status, ['Successful','Failed'])) {
    header("Location: orders.php?error=invalid_payment");
    exit;
}

try {
    $conn->begin_transaction();

    // Update payment
    $stmt = $conn->prepare(
        "UPDATE payments SET payment_status = ? WHERE order_id = ?"
    );
    $stmt->bind_param("si", $status, $order_id);
    $stmt->execute();

    // Sync order payment status
    $stmt2 = $conn->prepare(
        "UPDATE orders SET payment_status = ? WHERE order_id = ?"
    );
    $stmt2->bind_param("si", $status, $order_id);
    $stmt2->execute();

    // Admin log
    $admin_id = $_SESSION['user_id'];
    $desc = "Payment {$status} for Order {$order_id}";
    $log = $conn->prepare(
        "INSERT INTO admin_logs (admin_id, action_type, description, created_at)
         VALUES (?, 'PAYMENT_VERIFY', ?, NOW())"
    );
    $log->bind_param("is", $admin_id, $desc);
    $log->execute();

    // Notification
    $notif = $conn->prepare(
        "INSERT INTO notifications (user_id, message, created_at)
         SELECT user_id, ?, NOW() FROM orders WHERE order_id = ?"
    );
    $msg = "Payment for order #{$order_id} marked as {$status}";
    $notif->bind_param("si", $msg, $order_id);
    $notif->execute();

    $conn->commit();
    header("Location: view_order.php?id=$order_id&success=payment_updated");

} catch (Exception $e) {
    $conn->rollback();
    header("Location: view_order.php?id=$order_id&error=payment_failed");
} finally {
    closeConnection($conn);
}
