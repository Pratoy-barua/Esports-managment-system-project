<?php


require_once __DIR__ . '/../config/session.php';
require_once __DIR__ . '/../config/database.php';

requireAdmin();
$conn = getConnection();


$totalOrders = $conn->query("SELECT COUNT(*) total FROM orders")->fetch_assoc()['total'] ?? 0;

$todayOrders = $conn->query("
    SELECT COUNT(*) total 
    FROM orders 
    WHERE DATE(created_at) = CURDATE()
")->fetch_assoc()['total'] ?? 0;

$pendingOrders = $conn->query("
    SELECT COUNT(*) total 
    FROM orders 
    WHERE order_status = 'Processing'
")->fetch_assoc()['total'] ?? 0;

$completedOrders = $conn->query("
    SELECT COUNT(*) total 
    FROM orders 
    WHERE order_status = 'Delivered'
")->fetch_assoc()['total'] ?? 0;

$totalRevenue = $conn->query("
    SELECT IFNULL(SUM(total_amount),0) total 
    FROM orders 
    WHERE payment_status = 'Successful'
")->fetch_assoc()['total'] ?? 0;

// ===== Fetch Orders =====
$orders = $conn->query("
    SELECT 
        o.order_id,
        o.total_amount,
        o.payment_method,
        o.payment_status,
        o.order_status,
        o.created_at,
        u.username,
        u.full_name
    FROM orders o
    JOIN users u ON u.user_id = o.user_id
    ORDER BY o.order_id DESC
");
?>
<!DOCTYPE html>
<html>
<head>
    <title>Order Management</title>
    <style>
        body { background:#0f172a; color:#e2e8f0; font-family:Segoe UI; }
        .admin-content { margin-left:260px; padding:30px; }
        h1 { margin-bottom:10px; }

        .dashboard { display:grid; grid-template-columns:repeat(auto-fit,minmax(200px,1fr)); gap:20px; margin-bottom:30px; }
        .card { background:#1e293b; padding:20px; border-radius:12px; border:1px solid #334155; }
        .card h3 { font-size:13px; color:#94a3b8; }
        .card p { font-size:22px; font-weight:bold; margin-top:6px; }

        .table-box { background:#1e293b; padding:20px; border-radius:12px; }
        table { width:100%; border-collapse:collapse; margin-top:10px; }
        th, td { padding:12px; border-bottom:1px solid #334155; font-size:14px; }
        th { background:#334155; color:#94a3b8; text-align:left; }

        .badge { padding:4px 8px; border-radius:5px; font-size:12px; font-weight:600; }
        .pending { color:#f59e0b; }
        .completed { color:#22c55e; }
        .cancelled { color:#ef4444; }

        a { color:#818cf8; text-decoration:none; font-size:13px; }
        a:hover { text-decoration:underline; }
    </style>
</head>
<body>

<div class="admin-content">
    <h1>Order Management</h1>
    <p style="color:#94a3b8;margin-bottom:20px;">Monitor and control all orders</p>

    <!-- Dashboard -->
    <div class="dashboard">
        <div class="card">
            <h3>Total Orders</h3>
            <p><?= $totalOrders ?></p>
        </div>
        <div class="card">
            <h3>Orders Today</h3>
            <p><?= $todayOrders ?></p>
        </div>
        <div class="card">
            <h3>Pending Orders</h3>
            <p><?= $pendingOrders ?></p>
        </div>
        <div class="card">
            <h3>Completed Orders</h3>
            <p><?= $completedOrders ?></p>
        </div>
        <div class="card">
            <h3>Total Revenue</h3>
            <p>৳ <?= number_format($totalRevenue,2) ?></p>
        </div>
    </div>

    <!-- Orders Table -->
    <div class="table-box">
        <h3>Order List</h3>

        <table>
            <thead>
                <tr>
                    <th>Order ID</th>
                    <th>User</th>
                    <th>Total</th>
                    <th>Payment</th>
                    <th>Order Status</th>
                    <th>Date</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
            <?php if($orders && $orders->num_rows > 0): ?>
                <?php while($row = $orders->fetch_assoc()): ?>
                <tr>
                    <td>#<?= $row['order_id']; ?></td>
                    <td>
                        <?= htmlspecialchars($row['full_name']); ?><br>
                        <small>@<?= $row['username']; ?></small>
                    </td>
                    <td>৳ <?= number_format($row['total_amount'],2); ?></td>
                    <td><?= $row['payment_method']; ?><br>
                        <small><?= $row['payment_status']; ?></small>
                    </td>
                    <td>
                        <?php
                        $cls = strtolower($row['order_status']);
                        ?>
                        <span class="badge <?= $cls ?>">
                            <?= $row['order_status']; ?>
                        </span>
                    </td>
                    <td><?= date('d M Y', strtotime($row['created_at'])); ?></td>
                    <td>
                        <a href="view_order.php?id=<?= $row['order_id']; ?>">View</a>
                    </td>
                </tr>
                <?php endwhile; ?>
            <?php else: ?>
                <tr>
                    <td colspan="7" style="text-align:center;color:#94a3b8;">
                        No orders found
                    </td>
                </tr>
            <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

</body>
</html>
