<?php
require_once '../config/database.php';
require_once '../config/session.php';
requireLogin();

$conn = getConnection();
$user_id = $_SESSION['user_id'];

// Fetch Active Products
$sql = "SELECT * FROM products WHERE is_active = 1 ORDER BY created_at DESC";
$products = getAllRows($conn, $sql);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Products - ESportsHub</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="../assets/css/dashboard.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>

<body class="dashboard-body">
<div class="dashboard-container">
    <?php include 'includes/sidebar.php'; ?>

    <main class="main-content">
        <div class="top-bar">
            <h1>Gaming Products</h1>
        </div>

        <div class="dashboard-content">
            <div class="section">

                <?php if ($products && count($products) > 0): ?>
                    <div style="display:grid; grid-template-columns:repeat(auto-fill,minmax(220px,1fr)); gap:20px;">

                        <?php foreach ($products as $product): ?>
                            <div style="background:#1e293b; padding:15px; border-radius:10px; border:1px solid #334155;">

                                <div style="width:100%; height:160px; overflow:hidden; border-radius:8px; margin-bottom:10px;">
                                    <img
                                        src="../uploads/products/<?= htmlspecialchars($product['product_image']) ?>"
                                        alt="<?= htmlspecialchars($product['product_name']) ?>"
                                        style="width:100%; height:100%; object-fit:cover;"
                                        onerror="this.src='../assets/images/placeholder.jpg'; this.onerror=null;"
                                    >
                                </div>

                                <h3 style="font-size:1.1rem; color:#fff;">
                                    <?= htmlspecialchars($product['product_name']) ?>
                                </h3>

                                <p style="color:#94a3b8; font-size:13px;">
                                    <?= htmlspecialchars($product['category']) ?>
                                </p>

                                <p style="font-weight:bold; color:#22c55e;">
                                    ৳ <?= number_format($product['price'], 2) ?>
                                </p>

                                <!-- BUY BUTTON -->
                                <button
                                    onclick="openBuyModal(
                                        <?= $product['product_id'] ?>,
                                        '<?= htmlspecialchars($product['product_name'], ENT_QUOTES) ?>',
                                        <?= $product['price'] ?>
                                    )"
                                    style="
                                        margin-top:12px;
                                        width:100%;
                                        padding:10px;
                                        background:#6366f1;
                                        color:#fff;
                                        border:none;
                                        border-radius:6px;
                                        cursor:pointer;
                                    ">
                                    Buy Now
                                </button>

                            </div>
                        <?php endforeach; ?>

                    </div>
                <?php else: ?>
                    <div style="text-align:center; padding:50px; color:#94a3b8;">
                        <i class="fa-solid fa-box-open" style="font-size:3rem;"></i>
                        <p>No products available right now.</p>
                    </div>
                <?php endif; ?>

            </div>
        </div>
    </main>
</div>

<!-- BUY MODAL -->
<div id="buyModal" style="
    display:none;
    position:fixed;
    top:0; left:0;
    width:100%; height:100%;
    background:rgba(0,0,0,0.7);
    z-index:999;
    align-items:center;
    justify-content:center;
">
    <div style="background:#1e293b; padding:25px; border-radius:12px; width:320px;">
        <h3 id="buyProductName"></h3>
        <p id="buyProductPrice" style="color:#22c55e; margin-bottom:15px;"></p>

        <p style="font-size:14px; color:#94a3b8;">Select Payment Method</p>

        <button onclick="completeBuy('Bkash')" style="width:100%; margin-top:10px;">Bkash</button>
        <button onclick="completeBuy('Nagad')" style="width:100%; margin-top:10px;">Nagad</button>

        <button onclick="closeBuyModal()" style="width:100%; margin-top:15px; background:#475569;">
            Cancel
        </button>
    </div>
</div>

<script>
function openBuyModal(id, name, price) {
    document.getElementById('buyProductName').innerText = name;
    document.getElementById('buyProductPrice').innerText = '৳ ' + price;
    document.getElementById('buyModal').style.display = 'flex';
}

function closeBuyModal() {
    document.getElementById('buyModal').style.display = 'none';
}

function completeBuy(method) {
    alert('Payment Successful via ' + method + ' ✅');
    closeBuyModal();
}
</script>

</body>
</html>

<?php closeConnection($conn); ?>
