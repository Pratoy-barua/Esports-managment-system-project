<?php


require_once __DIR__ . '/../config/session.php';
require_once __DIR__ . '/../config/database.php';

// session.php e thaka requireAdmin function call kora hoyeche
requireAdmin();

$conn = getConnection();



// Total products
$totalProducts = $conn->query("SELECT COUNT(*) AS total FROM products")->fetch_assoc()['total'] ?? 0;

// Active products
$activeProducts = $conn->query("SELECT COUNT(*) AS total FROM products WHERE is_active = 1")->fetch_assoc()['total'] ?? 0;

// Out of stock
$outOfStock = $conn->query("SELECT COUNT(*) AS total FROM products WHERE stock_quantity <= 0")->fetch_assoc()['total'] ?? 0;

// Total product sales
$totalSales = $conn->query("
    SELECT IFNULL(SUM(oi.quantity * oi.price), 0) AS total
    FROM order_items oi
    JOIN orders o ON o.order_id = oi.order_id
    WHERE o.order_status = 'Delivered'
")->fetch_assoc()['total'];

// Fetch products for the list
$products = $conn->query("
    SELECT product_id, product_name, category, price, stock_quantity, is_active, product_image
    FROM products
    ORDER BY product_id DESC
");

// Admin info for header (Assuming session has these)
$admin_name = $_SESSION['full_name'] ?? 'Admin';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin | Product Management</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <style>
        /* Hubohu Dashboard er CSS setup */
        * { margin: 0; padding: 0; box-sizing: border-box; }
        
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: #0f172a;
            color: #e2e8f0;
        }
        
        .admin-layout {
            display: flex;
            min-height: 100vh;
        }
        
        .admin-sidebar {
            width: 260px;
            background: #1e293b;
            padding: 20px;
            position: fixed;
            height: 100vh;
            overflow-y: auto;
        }
        
        .admin-logo {
            text-align: center;
            padding: 20px 0;
            border-bottom: 1px solid #334155;
            margin-bottom: 20px;
        }
        
        .admin-logo h2 {
            color: #818cf8;
            font-size: 22px;
        }
        
        .admin-nav a {
            display: flex;
            align-items: center;
            padding: 12px 16px;
            color: #cbd5e1;
            text-decoration: none;
            border-radius: 8px;
            margin-bottom: 5px;
            transition: all 0.3s;
        }
        
        .admin-nav a:hover, .admin-nav a.active {
            background: #334155;
            color: #818cf8;
        }
        
        .admin-nav a i {
            margin-right: 12px;
            width: 20px;
        }
        
        .admin-content {
            margin-left: 260px;
            flex: 1;
            padding: 30px;
        }
        
        .admin-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
        }
        
        .admin-header h1 {
            font-size: 28px;
            color: #f1f5f9;
        }

        .admin-user {
            display: flex;
            align-items: center;
            gap: 15px;
        }

        .admin-user img {
            width: 45px;
            height: 45px;
            border-radius: 50%;
            border: 2px solid #818cf8;
        }
        
        /* Stats & Cards */
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }
        
        .stat-card {
            background: #1e293b;
            padding: 25px;
            border-radius: 12px;
            border-left: 4px solid #06b6d4; /* Default border color */
        }
        
        .stat-card h3 {
            font-size: 14px;
            color: #94a3b8;
            margin-bottom: 10px;
        }
        
        .stat-card .stat-value {
            font-size: 28px;
            font-weight: bold;
            color: #f1f5f9;
        }

        /* Table styles inside Card */
        .table-card {
            background: #1e293b;
            padding: 25px;
            border-radius: 12px;
        }

        .table-card h2 {
            margin-bottom: 20px;
            color: #f1f5f9;
            font-size: 20px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        th {
            text-align: left;
            background: #334155;
            color: #94a3b8;
            padding: 12px;
            font-size: 14px;
        }

        td {
            padding: 12px;
            border-bottom: 1px solid #334155;
            font-size: 14px;
            color: #cbd5e1;
        }

        .badge {
            padding: 4px 8px;
            border-radius: 4px;
            font-size: 12px;
            font-weight: 600;
        }
        .badge-active { background: rgba(16, 185, 129, 0.2); color: #10b981; }
        .badge-inactive { background: rgba(239, 68, 68, 0.2); color: #ef4444; }

        .btn-add {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 10px 16px;
            background: #6366f1;
            color: #fff;
            border-radius: 8px;
            text-decoration: none;
            font-weight: 600;
            font-size: 14px;
            transition: 0.3s;
        }
        .btn-add:hover { background: #4f46e5; }

        .action-links a {
            color: #818cf8;
            text-decoration: none;
            margin-right: 10px;
        }
        .action-links a:hover { text-decoration: underline; }
    </style>
</head>
<body>

<div class="admin-layout">
    <aside class="admin-sidebar">
        <div class="admin-logo">
            <h2><i class="fas fa-gamepad"></i> ESportsHub</h2>
            <p style="font-size: 12px; color: #64748b;">Admin Panel</p>
        </div>
        
        <nav class="admin-nav">
            <a href="dashboard.php">
                <i class="fas fa-chart-line"></i> Dashboard
            </a>
            <a href="users.php">
                <i class="fas fa-users"></i> User Management
            </a>
            <a href="tournaments.php">
                <i class="fas fa-trophy"></i> Tournaments
            </a>
            <a href="hosting.php">
                <i class="fas fa-calendar-check"></i> Hosting Requests
            </a>
            <a href="teams.php">
                <i class="fas fa-users-gear"></i> Teams
            </a>
            <a href="products.php" class="active">
                <i class="fas fa-box"></i> Products & Orders
            </a>
            <a href="subscriptions.php">
                <i class="fas fa-crown"></i> Subscriptions
            </a>
            <a href="messages.php">
                <i class="fas fa-envelope"></i> Messages
            </a>
            <a href="notifications.php">
                <i class="fas fa-bell"></i> Notifications
            </a>
            <a href="logs.php">
                <i class="fas fa-history"></i> Activity Logs
            </a>
            <a href="logout.php">
                <i class="fas fa-sign-out-alt"></i> Logout
            </a>
        </nav>
    </aside>

    <main class="admin-content">
        <div class="admin-header">
            <div>
                <h1>Product Management</h1>
                <p style="color:#64748b;">Manage products, inventory and sales overview</p>
            </div>
            <div class="admin-user">
                <div>
                    <div style="font-weight: 600;"><?php echo htmlspecialchars($admin_name); ?></div>
                    <div style="font-size: 13px; color: #64748b;">Administrator</div>
                </div>
                <img src="../assets/images/default-avatar.png" alt="Admin">
            </div>
        </div>

        <div style="margin-bottom: 25px;">
            <a href="add_product.php" class="btn-add">
                <i class="fas fa-plus"></i> Add New Product
            </a>
        </div>

        <div class="stats-grid">
            <div class="stat-card" style="border-left-color: #3b82f6;">
                <h3>Total Products</h3>
                <div class="stat-value"><?php echo $totalProducts; ?></div>
            </div>
            <div class="stat-card" style="border-left-color: #10b981;">
                <h3>Active Products</h3>
                <div class="stat-value"><?php echo $activeProducts; ?></div>
            </div>
            <div class="stat-card" style="border-left-color: #ef4444;">
                <h3>Out of Stock</h3>
                <div class="stat-value"><?php echo $outOfStock; ?></div>
            </div>
            <div class="stat-card" style="border-left-color: #14b8a6;">
                <h3>Total Sales</h3>
                <div class="stat-value">৳ <?php echo number_format($totalSales, 2); ?></div>
            </div>
        </div>

        <div class="table-card">
            <h2><i class="fas fa-list" style="margin-right: 10px; color: #818cf8;"></i> Product List</h2>
            <div style="overflow-x: auto;">
                <table>
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Image</th>
                            <th>Name</th>
                            <th>Category</th>
                            <th>Price</th>
                            <th>Stock</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php if ($products && $products->num_rows > 0): ?>
                        <?php while($row = $products->fetch_assoc()): ?>
                            <tr>
                                <td>#<?= $row['product_id']; ?></td>
                                <td>
                                    <img src="../uploads/products/<?= !empty($row['product_image']) ? $row['product_image'] : 'default-product.png'; ?>" 
                                         width="40" height="40" 
                                         style="object-fit:cover; border-radius:6px; background:#334155;">
                                </td>
                                <td><strong><?= htmlspecialchars($row['product_name']); ?></strong></td>
                                <td><?= htmlspecialchars($row['category']); ?></td>
                                <td>৳ <?= number_format($row['price'], 2); ?></td>
                                <td>
                                    <span style="<?= $row['stock_quantity'] <= 0 ? 'color:#ef4444; font-weight:bold;' : ''; ?>">
                                        <?= $row['stock_quantity']; ?>
                                    </span>
                                </td>
                                <td>
                                    <?php if($row['is_active']): ?>
                                        <span class="badge badge-active">Active</span>
                                    <?php else: ?>
                                        <span class="badge badge-inactive">Inactive</span>
                                    <?php endif; ?>
                                </td>
                                <td class="action-links">
                                    <a href="view_product.php?id=<?= $row['product_id']; ?>" title="View"><i class="fas fa-eye"></i></a>
                                    <a href="edit_product.php?id=<?= $row['product_id']; ?>" title="Edit"><i class="fas fa-edit"></i></a>
                                    <a href="delete_product.php?id=<?= $row['product_id']; ?>" 
                                       style="color: #ef4444;"
                                       onclick="return confirm('Are you sure you want to deactivate/delete this product?');" title="Delete">
                                       <i class="fas fa-trash"></i>
                                    </a>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="8" style="text-align:center; padding: 30px; color: #64748b;">No products found.</td>
                        </tr>
                    <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </main>
</div>

</body>
</html>