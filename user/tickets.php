<?php
require_once '../config/database.php';
require_once '../config/session.php';
requireLogin();

$conn = getConnection();
$user_id = $_SESSION['user_id'];

$sql = "SELECT * FROM tickets WHERE user_id = $user_id ORDER BY created_at DESC";
$tickets = getAllRows($conn, $sql);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Support Tickets - ESportsHub</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="../assets/css/dashboard.css">
    <link rel="stylesheet" href="../assets/css/modal.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body class="dashboard-body">
    <div class="dashboard-container">
        <?php include 'includes/sidebar.php'; ?>
        <main class="main-content">
            <div class="top-bar">
                <h1>Support Tickets</h1>
                <button class="btn btn-primary" onclick="alert('Create ticket form will be implemented')">
                    <i class="fas fa-plus"></i> New Ticket
                </button>
            </div>
            <div class="dashboard-content">
                <div class="section">
                    <?php if (empty($tickets)): ?>
                    <p class="empty-state">No support tickets yet. Use Messages to contact admin directly.</p>
                    <?php else: ?>
                    <div class="table-responsive">
                        <table class="data-table">
                            <thead>
                                <tr>
                                    <th>Ticket ID</th>
                                    <th>Subject</th>
                                    <th>Category</th>
                                    <th>Priority</th>
                                    <th>Status</th>
                                    <th>Created</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($tickets as $ticket): ?>
                                <tr>
                                    <td>#<?php echo $ticket['ticket_id']; ?></td>
                                    <td><?php echo htmlspecialchars($ticket['subject']); ?></td>
                                    <td><?php echo htmlspecialchars($ticket['category']); ?></td>
                                    <td><?php echo htmlspecialchars($ticket['priority']); ?></td>
                                    <td><span class="badge-status"><?php echo htmlspecialchars($ticket['status']); ?></span></td>
                                    <td><?php echo date('M d, Y', strtotime($ticket['created_at'])); ?></td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </main>
    </div>
</body>
</html>
<?php closeConnection($conn); ?>
