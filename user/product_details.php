<?php
require_once '../config/database.php';
require_once '../config/session.php';
requireLogin();

$conn = getConnection();

// 1️⃣ Product ID check
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header("Location: products.php");
    exit;
}

$product_id = (int)$_GET['id'];

// 2️⃣ Fetch product
$sql = "SELECT * FROM products WHERE product_id = $product_id AND is_active = 1 LIMIT 1";
$product = getSingleRow($conn, $sql);

if (!$product) {
    header("Location: products.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title><?= htmlspecialchars($product['product_name']) ?> | ESportsHub</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="../assets/css/dashboard.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <style>
        .product-wrapper {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 40px;
            background: #1e293b;
            padding: 30px;
            border-radius: 14px;
            border: 1px solid #334155;
        }
        .product-image {
            width: 100%;
            height: 380px;
            background: #0f172a;
            border-radius: 12px;
            overflow: hidden;
        }
        .product-image img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }
        .price {
            color: #22c55e;
            font-size: 1.8rem;
            font-weight: bold;
            margin: 15px 0;
        }
        .badge {
            display: inline-block;
            padding: 5px 12px;
            background: #334155;
            border-radius: 999px;
            font-size: 13px;
            color: #cbd5e1;
            margin-bottom: 15px;
        }
        .buy-btn {
            margin-top: 25px;
            display: inline-block;
            padding: 14px 30px;
            background: #6366f1;
            color: #fff;
            border-radius: 8px;
            text-decoration: none;
            font-weight: 600;
            transition: 0.2s;
        }
        .buy-btn:hover {
            background: #4f46e5;
        }
        .out-stock {
            background: #ef4444;
        }
    </style>
</head>

<body class="dashboard-body">
<div class="dashboard-container">

    <?php include 'includes/sidebar.php'; ?>

    <main class="main-content">
        <div class="top-bar">
            <h1>Product Details</h1>
        </div>

        <div class="dashboard-content">
            <div class="product-wrapper">

                <div class="product-image">
                    <img src="../uploads/products/<?= htmlspecialchars($product['product_image']) ?>"
                         onerror="this.src='../assets/images/placeholder.jpg';">
                </div>

                <div>
                    <span class="badge"><?= htmlspecialchars($product['category']) ?></span>

                    <h2 style="margin: 10px 0;"><?= htmlspecialchars($product['product_name']) ?></h2>

                    <div class="price">৳ <?= number_format($product['price'], 2) ?></div>

                    <p style="color:#94a3b8; line-height:1.6;">
                        <?= nl2br(htmlspecialchars($product['description'])) ?>
                    </p>

                    <p style="margin-top:15px; color:#cbd5e1;">
                        <strong>Stock:</strong>
                        <?= $product['stock_quantity'] > 0 ? $product['stock_quantity'] : 'Out of stock' ?>
                    </p>

                    <?php if ($product['stock_quantity'] > 0): ?>
                        <a href="cart.php?add=<?= $product['product_id'] ?>" class="buy-btn">
                            <i class="fas fa-shopping-cart"></i> Add to Cart
                        </a>
                    <?php else: ?>
                        <span class="buy-btn out-stock">
                            <i class="fas fa-times"></i> Out of Stock
                        </span>
                    <?php endif; ?>

                </div>
            </div>
        </div>
    </main>
</div>
</body>
</html>

<?php closeConnection($conn); ?>