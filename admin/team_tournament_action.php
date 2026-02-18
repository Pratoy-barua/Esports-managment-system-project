<?php
/**
 * Admin - Team Tournament Actions
 * Path: /admin/team_tournament_action.php
 */

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/session.php';

requireAdmin();

if (
    !isset($_GET['team_id'], $_GET['tournament_id'], $_GET['action']) ||
    !is_numeric($_GET['team_id']) ||
    !is_numeric($_GET['tournament_id'])
) {
    header('Location: teams.php');
    exit;
}

$team_id       = (int) $_GET['team_id'];
$tournament_id = (int) $_GET['tournament_id'];
$action        = $_GET['action']; // remove | disqualify | ban
$admin_id      = $_SESSION['user_id'];

$conn = getConnection();

try {
    $conn->begin_transaction();

    switch ($action) {

        case 'remove':
            // Remove from tournament
            $stmt = $conn->prepare(
                "DELETE FROM participants 
                 WHERE team_id = ? AND tournament_id = ?"
            );
            $stmt->bind_param("ii", $team_id, $tournament_id);
            $stmt->execute();
            $stmt->close();

            $logAction = 'REMOVE_FROM_TOURNAMENT';
            $desc = "Removed team {$team_id} from tournament {$tournament_id}";
            break;

        case 'disqualify':
            // Mark as disqualified (recommended column)
            $stmt = $conn->prepare(
                "UPDATE participants 
                 SET status = 'disqualified' 
                 WHERE team_id = ? AND tournament_id = ?"
            );
            $stmt->bind_param("ii", $team_id, $tournament_id);
            $stmt->execute();
            $stmt->close();

            $logAction = 'DISQUALIFY_TEAM';
            $desc = "Disqualified team {$team_id} in tournament {$tournament_id}";
            break;

        case 'ban':
            // Soft ban team (recommended column in teams table)
            $stmt = $conn->prepare(
                "UPDATE teams SET is_banned = 1 WHERE team_id = ?"
            );
            $stmt->bind_param("i", $team_id);
            $stmt->execute();
            $stmt->close();

            $logAction = 'BAN_TEAM';
            $desc = "Banned team {$team_id} from future tournaments";
            break;

        default:
            throw new Exception("Invalid action");
    }

    /* ===============================
        ADMIN LOG
    ================================ */
    $stmt = $conn->prepare(
        "INSERT INTO admin_logs
        (admin_id, action, target_type, target_id, description, ip_address)
        VALUES (?, ?, 'team', ?, ?, ?)"
    );

    $ip = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
    $stmt->bind_param(
        "isiss",
        $admin_id,
        $logAction,
        $team_id,
        $desc,
        $ip
    );
    $stmt->execute();
    $stmt->close();

    $conn->commit();

    header("Location: view_team.php?id={$team_id}&success=action_done");
    exit;

} catch (Exception $e) {
    $conn->rollback();
    header(
        "Location: view_team.php?id={$team_id}&error=" . urlencode($e->getMessage())
    );
    exit;
}
