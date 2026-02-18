<?php
require_once '../config/database.php';
require_once '../config/session.php';
requireLogin();

$conn = getConnection();
$user_id = $_SESSION['user_id'];

// Fetch user orders
$sql = "SELECT * FROM orders 
        WHERE user_id = $user_id 
        ORDER BY created_at DESC";
$orders = getAllRows($conn, $sql);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>My Orders | ESportsHub</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="../assets/css/dashboard.css">
</head>
<body class="dashboard-body">

<div class="dashboard-container">
    <?php include 'includes/sidebar.php'; ?>

    <main class="main-content">
        <div class="top-bar">
            <h1>My Orders</h1>
        </div>

        <div class="dashboard-content">

            <?php if (isset($_GET['success'])): ?>
                <div class="alert alert-success">
                    <?= htmlspecialchars($_GET['success']) ?>
                </div>
            <?php endif; ?>

            <?php if ($orders && count($orders) > 0): ?>
                <table style="width:100%; background:#1e293b; border-radius:10px;">
                    <tr style="background:#334155;">
                        <th>Order ID</th>
                        <th>Total</th>
                        <th>Status</th>
                        <th>Date</th>
                        <th>Details</th>
                    </tr>

                    <?php foreach ($orders as $order): ?>
                        <tr>
                            <td>#<?= $order['order_id'] ?></td>
                            <td>৳ <?= number_format($order['total_amount'], 2) ?></td>
                            <td>
                                <?php
                                    $color = '#facc15';
                                    if ($order['status'] === 'Completed') $color = '#22c55e';
                                    if ($order['status'] === 'Cancelled') $color = '#ef4444';
                                ?>
                                <span style="color:<?= $color ?>; font-weight:600;">
                                    <?= $order['status'] ?>
                                </span>
                            </td>
                            <td><?= date('d M Y', strtotime($order['created_at'])) ?></td>
                            <td>
                                <a href="order_details.php?id=<?= $order['order_id'] ?>"
                                   style="color:#6366f1; text-decoration:none;">
                                    View
                                </a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </table>
            <?php else: ?>
                <p style="color:#94a3b8;">You have not placed any orders yet.</p>
            <?php endif; ?>

        </div>
    </main>
</div>

</body>
</html>

<?php closeConnection($conn); ?>
