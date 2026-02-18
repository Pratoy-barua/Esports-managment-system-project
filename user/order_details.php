<?php
require_once '../config/database.php';
require_once '../config/session.php';
requireLogin();

$conn = getConnection();
$user_id = $_SESSION['user_id'];

$order_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

// 🔐 Order belongs to user check
$orderSql = "SELECT * FROM orders 
             WHERE order_id = $order_id AND user_id = $user_id";
$order = getSingleRow($conn, $orderSql);

if (!$order) {
    header("Location: orders.php");
    exit;
}

// Fetch order items
$itemSql = "SELECT oi.*, p.product_name, p.product_image 
            FROM order_items oi
            JOIN products p ON oi.product_id = p.product_id
            WHERE oi.order_id = $order_id";
$items = getAllRows($conn, $itemSql);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Order Details | ESportsHub</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="../assets/css/dashboard.css">
</head>
<body class="dashboard-body">

<div class="dashboard-container">
    <?php include 'includes/sidebar.php'; ?>

    <main class="main-content">
        <div class="top-bar">
            <h1>Order #<?= $order_id ?></h1>
        </div>

        <div class="dashboard-content">

            <!-- Order Info -->
            <div style="background:#1e293b; padding:20px; border-radius:10px; margin-bottom:20px;">
                <p><strong>Status:</strong> <?= $order['status'] ?></p>
                <p><strong>Total:</strong> ৳ <?= number_format($order['total_amount'], 2) ?></p>
                <p><strong>Order Date:</strong> <?= date('d M Y, h:i A', strtotime($order['created_at'])) ?></p>
            </div>

            <!-- Order Items -->
            <?php if ($items): ?>
                <div style="display:grid; grid-template-columns:repeat(auto-fill,minmax(220px,1fr)); gap:20px;">
                    <?php foreach ($items as $item): ?>
                        <div style="background:#1e293b; padding:15px; border-radius:10px; border:1px solid #334155;">
                            
                            <div style="height:140px; overflow:hidden; border-radius:8px; margin-bottom:10px;">
                                <img src="../uploads/products/<?= htmlspecialchars($item['product_image']) ?>"
                                     style="width:100%; height:100%; object-fit:cover;"
                                     onerror="this.src='../assets/images/placeholder.jpg'">
                            </div>

                            <h3 style="font-size:1rem; margin-bottom:5px;">
                                <?= htmlspecialchars($item['product_name']) ?>
                            </h3>

                            <p style="color:#94a3b8; font-size:14px;">
                                Quantity: <?= $item['quantity'] ?>
                            </p>

                            <p style="color:#22c55e; font-weight:bold;">
                                ৳ <?= number_format($item['price'], 2) ?>
                            </p>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <p>No items found for this order.</p>
            <?php endif; ?>

            <a href="orders.php"
               style="display:inline-block; margin-top:25px; color:#6366f1; text-decoration:none;">
                ← Back to Orders
            </a>

        </div>
    </main>
</div>

</body>
</html>

<?php closeConnection($conn); ?>
