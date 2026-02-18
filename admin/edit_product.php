<?php


require_once __DIR__ . '/../config/session.php';
require_once __DIR__ . '/../config/database.php';

requireAdmin();
$conn = getConnection();

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header("Location: products.php?error=invalid_product");
    exit;
}

$product_id = (int)$_GET['id'];
$error = '';
$success = '';

// Fetch product
$stmt = $conn->prepare("SELECT * FROM products WHERE product_id = ?");
$stmt->bind_param("i", $product_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    header("Location: products.php?error=product_not_found");
    exit;
}

$product = $result->fetch_assoc();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $name        = trim($_POST['product_name']);
    $category    = trim($_POST['category']);
    $description = trim($_POST['description']);
    $price       = floatval($_POST['price']);
    $stock       = intval($_POST['stock_quantity']);

    if ($name === '' || $category === '' || $price <= 0 || $stock < 0) {
        $error = "Invalid input values.";
    } else {

        $imageName = $product['product_image'];

        // Image replace (optional)
        if (!empty($_FILES['product_image']['name'])) {
            $allowed = ['image/jpeg','image/png','image/webp'];
            if (!in_array($_FILES['product_image']['type'], $allowed)) {
                $error = "Invalid image format.";
            } else {
                $ext = pathinfo($_FILES['product_image']['name'], PATHINFO_EXTENSION);
                $imageName = 'product_' . time() . '.' . $ext;
                $uploadPath = __DIR__ . '/../uploads/products/' . $imageName;
            }
        }

        if ($error === '') {
            try {
                $conn->begin_transaction();

                // Update product
                $sql = "UPDATE products 
                        SET product_name=?, category=?, description=?, price=?, stock_quantity=?, product_image=?
                        WHERE product_id=?";

                $stmt = $conn->prepare($sql);
                $stmt->bind_param(
                    "sssdisi",
                    $name,
                    $category,
                    $description,
                    $price,
                    $stock,
                    $imageName,
                    $product_id
                );
                $stmt->execute();

                // Upload image if changed
                if (!empty($_FILES['product_image']['name'])) {
                    move_uploaded_file($_FILES['product_image']['tmp_name'], $uploadPath);
                }

                // Admin log
                $admin_id = $_SESSION['user_id'];
                $desc = "Updated product ID {$product_id}";
                $log = $conn->prepare(
                    "INSERT INTO admin_logs (admin_id, action_type, description, created_at)
                     VALUES (?, 'PRODUCT_UPDATE', ?, NOW())"
                );
                $log->bind_param("is", $admin_id, $desc);
                $log->execute();

                $conn->commit();
                $success = "Product updated successfully.";

                // Refresh data
                $product['product_name'] = $name;
                $product['category'] = $category;
                $product['description'] = $description;
                $product['price'] = $price;
                $product['stock_quantity'] = $stock;
                $product['product_image'] = $imageName;

            } catch (Exception $e) {
                $conn->rollback();
                $error = "Update failed.";
            }
        }
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Edit Product</title>
    <style>
        body { background:#0f172a; color:#e2e8f0; font-family:Segoe UI; }
        .admin-content { margin-left:260px; padding:30px; }
        .card { background:#1e293b; padding:25px; border-radius:12px; max-width:700px; }
        label { display:block; margin-top:15px; color:#94a3b8; }
        input, textarea, select {
            width:100%; padding:10px; margin-top:5px;
            background:#0f172a; color:#e2e8f0; border:1px solid #334155;
            border-radius:6px;
        }
        button {
            margin-top:20px; padding:12px 20px;
            background:#22c55e; border:none; color:#fff;
            border-radius:8px; cursor:pointer;
        }
        .msg-error { color:#ef4444; margin-bottom:10px; }
        .msg-success { color:#22c55e; margin-bottom:10px; }
        img { margin-top:10px; border-radius:8px; }
    </style>
</head>
<body>

<div class="admin-content">
    <h1>Edit Product</h1>

    <div class="card">
        <?php if($error): ?><div class="msg-error"><?= $error ?></div><?php endif; ?>
        <?php if($success): ?><div class="msg-success"><?= $success ?></div><?php endif; ?>

        <form method="POST" enctype="multipart/form-data">
            <label>Product Name</label>
            <input type="text" name="product_name" value="<?= htmlspecialchars($product['product_name']); ?>" required>

            <label>Category</label>
            <select name="category" required>
                <option <?= $product['category']=='Jersey'?'selected':''; ?>>Jersey</option>
                <option <?= $product['category']=='Accessories'?'selected':''; ?>>Accessories</option>
                <option <?= $product['category']=='Digital Items'?'selected':''; ?>>Digital Items</option>
            </select>

            <label>Description</label>
            <textarea name="description" rows="4"><?= htmlspecialchars($product['description']); ?></textarea>

            <label>Price</label>
            <input type="number" step="0.01" name="price" value="<?= $product['price']; ?>" required>

            <label>Stock Quantity</label>
            <input type="number" name="stock_quantity" value="<?= $product['stock_quantity']; ?>" min="0" required>

            <label>Replace Image (optional)</label>
            <input type="file" name="product_image">

            <?php if($product['product_image']): ?>
                <img src="../uploads/products/<?= $product['product_image']; ?>" width="80">
            <?php endif; ?>

            <button type="submit">Update Product</button>
        </form>
    </div>
</div>

</body>
</html>
