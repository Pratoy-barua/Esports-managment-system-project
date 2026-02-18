<?php
/**
 * Admin - Remove Team Member
 * Path: /admin/remove_member.php
 */

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/session.php';

requireAdmin();

if (
    !isset($_GET['team_id'], $_GET['user_id']) ||
    !is_numeric($_GET['team_id']) ||
    !is_numeric($_GET['user_id'])
) {
    header('Location: teams.php');
    exit;
}

$team_id  = (int) $_GET['team_id'];
$user_id  = (int) $_GET['user_id'];
$admin_id = $_SESSION['user_id'];

$conn = getConnection();

try {
    $conn->begin_transaction();

    /* ===============================
        CHECK MEMBER ROLE
    ================================ */
    $stmt = $conn->prepare(
        "SELECT role FROM team_members 
         WHERE team_id = ? AND user_id = ?"
    );
    $stmt->bind_param("ii", $team_id, $user_id);
    $stmt->execute();
    $member = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    if (!$member) {
        throw new Exception('Member not found in this team');
    }

    $isLeader = ($member['role'] === 'Captain');

    /* ===============================
        REMOVE MEMBER
    ================================ */
    $stmt = $conn->prepare(
        "DELETE FROM team_members 
         WHERE team_id = ? AND user_id = ?"
    );
    $stmt->bind_param("ii", $team_id, $user_id);
    $stmt->execute();
    $stmt->close();

    /* ===============================
        IF LEADER REMOVED
    ================================ */
    if ($isLeader) {

        // Find next available member
        $stmt = $conn->prepare(
            "SELECT user_id FROM team_members 
             WHERE team_id = ? LIMIT 1"
        );
        $stmt->bind_param("i", $team_id);
        $stmt->execute();
        $newLeader = $stmt->get_result()->fetch_assoc();
        $stmt->close();

        if ($newLeader) {
            // Assign new leader
            $stmt = $conn->prepare(
                "UPDATE team_members 
                 SET role = 'Captain' 
                 WHERE team_id = ? AND user_id = ?"
            );
            $stmt->bind_param("ii", $team_id, $newLeader['user_id']);
            $stmt->execute();
            $stmt->close();

            // Update teams table
            $stmt = $conn->prepare(
                "UPDATE teams 
                 SET captain_id = ? 
                 WHERE team_id = ?"
            );
            $stmt->bind_param("ii", $newLeader['user_id'], $team_id);
            $stmt->execute();
            $stmt->close();

        } else {
            // No members left → auto disband
            $stmt = $conn->prepare(
                "UPDATE teams 
                 SET status = 'deleted' 
                 WHERE team_id = ?"
            );
            $stmt->bind_param("i", $team_id);
            $stmt->execute();
            $stmt->close();
        }
    }

    /* ===============================
        ADMIN LOG
    ================================ */
    $stmt = $conn->prepare(
        "INSERT INTO admin_logs
        (admin_id, action, target_type, target_id, description, ip_address)
        VALUES (?, 'REMOVE_TEAM_MEMBER', 'team', ?, ?, ?)"
    );

    $description = "Removed user {$user_id} from team {$team_id}";
    $ip_address  = $_SERVER['REMOTE_ADDR'] ?? 'unknown';

    $stmt->bind_param(
        "iiss",
        $admin_id,
        $team_id,
        $description,
        $ip_address
    );
    $stmt->execute();
    $stmt->close();

    $conn->commit();

    header("Location: view_team.php?id={$team_id}&success=member_removed");
    exit;

} catch (Exception $e) {
    $conn->rollback();
    header(
        "Location: view_team.php?id={$team_id}&error=" . urlencode($e->getMessage())
    );
    exit;
}
