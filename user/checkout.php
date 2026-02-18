<?php
require_once '../config/database.php';
require_once '../config/session.php';
requireLogin();

$conn = getConnection();
$user_id = $_SESSION['user_id'];

// 🛑 Cart empty guard
if (!isset($_SESSION['cart']) || empty($_SESSION['cart'])) {
    header("Location: products.php");
    exit;
}

// Fetch cart products
$ids = implode(',', array_keys($_SESSION['cart']));
$sql = "SELECT * FROM products WHERE product_id IN ($ids)";
$products = getAllRows($conn, $sql);

$total = 0;
foreach ($products as $p) {
    $total += $p['price'] * $_SESSION['cart'][$p['product_id']];
}

// PLACE ORDER
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $conn->begin_transaction();

    try {
        // 1️⃣ Create Order
        $orderSql = "INSERT INTO orders (user_id, total_amount, status, created_at)
                     VALUES (?, ?, 'Pending', NOW())";
        $stmt = $conn->prepare($orderSql);
        $stmt->bind_param("id", $user_id, $total);
        $stmt->execute();
        $order_id = $stmt->insert_id;

        // 2️⃣ Order Items
        $itemSql = $conn->prepare("
            INSERT INTO order_items (order_id, product_id, price, quantity)
            VALUES (?, ?, ?, ?)
        ");

        foreach ($products as $p) {
            $qty = $_SESSION['cart'][$p['product_id']];
            $itemSql->bind_param("iidi", $order_id, $p['product_id'], $p['price'], $qty);
            $itemSql->execute();

            // 3️⃣ Reduce Stock
            $conn->query("
                UPDATE products 
                SET stock_quantity = stock_quantity - $qty 
                WHERE product_id = {$p['product_id']}
            ");
        }

        // 4️⃣ Clear Cart
        $_SESSION['cart'] = [];

        $conn->commit();
        header("Location: orders.php?success=Order placed successfully!");
        exit;

    } catch (Exception $e) {
        $conn->rollback();
        $error = "Order failed. Try again.";
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Checkout | ESportsHub</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="../assets/css/dashboard.css">
</head>
<body class="dashboard-body">
<div class="dashboard-container">

<?php include 'includes/sidebar.php'; ?>

<main class="main-content">
    <div class="top-bar">
        <h1>Checkout</h1>
    </div>

    <div class="dashboard-content">

        <table style="width:100%; background:#1e293b; border-radius:10px;">
            <tr style="background:#334155;">
                <th>Product</th>
                <th>Price</th>
                <th>Qty</th>
                <th>Total</th>
            </tr>

            <?php foreach ($products as $p): 
                $qty = $_SESSION['cart'][$p['product_id']];
                $line = $qty * $p['price'];
            ?>
            <tr>
                <td><?= htmlspecialchars($p['product_name']) ?></td>
                <td>৳ <?= number_format($p['price'],2) ?></td>
                <td><?= $qty ?></td>
                <td>৳ <?= number_format($line,2) ?></td>
            </tr>
            <?php endforeach; ?>
        </table>

        <h3 style="margin-top:20px;">
            Grand Total: ৳ <?= number_format($total,2) ?>
        </h3>

        <form method="POST" style="margin-top:20px;">
            <button type="submit" class="btn btn-primary">
                Confirm Order
            </button>
        </form>

    </div>
</main>
</div>
</body>
</html>

<?php closeConnection($conn); ?>
