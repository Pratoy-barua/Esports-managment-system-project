<?php
/**
 * Admin - View Product Details
 * Path: /admin/view_product.php
 */

require_once __DIR__ . '/../config/session.php';
require_once __DIR__ . '/../config/database.php';

requireAdmin();
$conn = getConnection();

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header("Location: products.php?error=invalid_product");
    exit;
}

$product_id = (int) $_GET['id'];

// Fetch product details
$stmt = $conn->prepare("
    SELECT 
        p.*,
        IFNULL(SUM(oi.quantity),0) AS total_sold
    FROM products p
    LEFT JOIN order_items oi ON oi.product_id = p.product_id
    LEFT JOIN orders o ON o.order_id = oi.order_id AND o.order_status = 'Delivered'
    WHERE p.product_id = ?
    GROUP BY p.product_id
");
$stmt->bind_param("i", $product_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    header("Location: products.php?error=product_not_found");
    exit;
}

$product = $result->fetch_assoc();
closeConnection($conn);
?>

<!DOCTYPE html>
<html>
<head>
    <title>View Product</title>
    <style>
        body { background:#0f172a; color:#e2e8f0; font-family:Segoe UI; }
        .admin-content { margin-left:260px; padding:30px; }
        .card { background:#1e293b; padding:30px; border-radius:14px; max-width:800px; }
        h1 { margin-bottom:15px; }
        .row { display:grid; grid-template-columns:1fr 1fr; gap:20px; }
        .field { margin-bottom:15px; }
        .label { font-size:13px; color:#94a3b8; margin-bottom:5px; }
        .value { font-size:15px; color:#f1f5f9; }
        .badge-active { color:#10b981; font-weight:bold; }
        .badge-inactive { color:#ef4444; font-weight:bold; }
        img { border-radius:10px; margin-top:10px; background:#334155; }
        a { color:#818cf8; text-decoration:none; }
        a:hover { text-decoration:underline; }
    </style>
</head>
<body>

<div class="admin-content">
    <a href="products.php">← Back to Product List</a>

    <h1>Product Details</h1>

    <div class="card">
        <div class="row">
            <div>
                <div class="field">
                    <div class="label">Product Name</div>
                    <div class="value"><?= htmlspecialchars($product['product_name']); ?></div>
                </div>

                <div class="field">
                    <div class="label">Category</div>
                    <div class="value"><?= htmlspecialchars($product['category']); ?></div>
                </div>

                <div class="field">
                    <div class="label">Price</div>
                    <div class="value">৳ <?= number_format($product['price'],2); ?></div>
                </div>

                <div class="field">
                    <div class="label">Stock Quantity</div>
                    <div class="value"><?= $product['stock_quantity']; ?></div>
                </div>

                <div class="field">
                    <div class="label">Status</div>
                    <div class="value">
                        <?= $product['is_active'] 
                            ? '<span class="badge-active">Active</span>' 
                            : '<span class="badge-inactive">Inactive</span>'; ?>
                    </div>
                </div>

                <div class="field">
                    <div class="label">Total Sold</div>
                    <div class="value"><?= $product['total_sold']; ?> unit(s)</div>
                </div>

                <div class="field">
                    <div class="label">Created At</div>
                    <div class="value"><?= $product['created_at']; ?></div>
                </div>
            </div>

            <div>
                <div class="label">Product Image</div>
                <?php if(!empty($product['product_image'])): ?>
                    <img src="../uploads/products/<?= $product['product_image']; ?>" width="220">
                <?php else: ?>
                    <p style="color:#94a3b8;">No image available</p>
                <?php endif; ?>
            </div>
        </div>

        <div class="field" style="margin-top:25px;">
            <div class="label">Description</div>
            <div class="value"><?= nl2br(htmlspecialchars($product['description'])); ?></div>
        </div>
    </div>
</div>

</body>
</html>
