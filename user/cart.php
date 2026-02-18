<?php
require_once '../config/database.php';
require_once '../config/session.php';
requireLogin();

$conn = getConnection();

// 🛒 Cart session init
if (!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = [];
}

// ➕ ADD TO CART
if (isset($_GET['add']) && is_numeric($_GET['add'])) {
    $product_id = (int)$_GET['add'];

    if (!isset($_SESSION['cart'][$product_id])) {
        $_SESSION['cart'][$product_id] = 1;
    } else {
        $_SESSION['cart'][$product_id]++;
    }

    header("Location: cart.php");
    exit;
}

// ➖ REMOVE ITEM
if (isset($_GET['remove']) && is_numeric($_GET['remove'])) {
    unset($_SESSION['cart'][(int)$_GET['remove']]);
    header("Location: cart.php");
    exit;
}

// 🧹 CLEAR CART
if (isset($_GET['clear'])) {
    $_SESSION['cart'] = [];
    header("Location: cart.php");
    exit;
}

// 📦 FETCH PRODUCTS
$cartItems = [];
$total = 0;

if (!empty($_SESSION['cart'])) {
    $ids = implode(',', array_keys($_SESSION['cart']));
    $sql = "SELECT * FROM products WHERE product_id IN ($ids)";
    $cartItems = getAllRows($conn, $sql);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Your Cart | ESportsHub</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="../assets/css/dashboard.css">
</head>

<body class="dashboard-body">
<div class="dashboard-container">

<?php include 'includes/sidebar.php'; ?>

<main class="main-content">
    <div class="top-bar">
        <h1>Your Cart</h1>
    </div>

    <div class="dashboard-content">

        <?php if (!$cartItems): ?>
            <p style="color:#94a3b8;">Your cart is empty.</p>
        <?php else: ?>

        <table style="width:100%; background:#1e293b; border-radius:10px;">
            <tr style="background:#334155;">
                <th>Product</th>
                <th>Price</th>
                <th>Qty</th>
                <th>Total</th>
                <th></th>
            </tr>

            <?php foreach ($cartItems as $item): 
                $qty = $_SESSION['cart'][$item['product_id']];
                $lineTotal = $qty * $item['price'];
                $total += $lineTotal;
            ?>
            <tr>
                <td><?= htmlspecialchars($item['product_name']) ?></td>
                <td>৳ <?= number_format($item['price'],2) ?></td>
                <td><?= $qty ?></td>
                <td>৳ <?= number_format($lineTotal,2) ?></td>
                <td>
                    <a href="cart.php?remove=<?= $item['product_id'] ?>" style="color:red;">Remove</a>
                </td>
            </tr>
            <?php endforeach; ?>
        </table>

        <h3 style="margin-top:20px;">Grand Total: ৳ <?= number_format($total,2) ?></h3>

        <div style="margin-top:15px;">
            <a href="cart.php?clear=1" style="color:#ef4444;">Clear Cart</a>
            &nbsp; | &nbsp;
            <a href="checkout.php" style="color:#22c55e;">Proceed to Checkout</a>
        </div>

        <?php endif; ?>

    </div>
</main>
</div>
</body>
</html>

<?php closeConnection($conn); ?>
