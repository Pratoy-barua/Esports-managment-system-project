<?php
require_once __DIR__ . '/../config/session.php';
require_once __DIR__ . '/../config/database.php';

requireAdmin();
$conn = getConnection();

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $name        = trim($_POST['product_name']);
    $category    = trim($_POST['category']);
    $description = trim($_POST['description']);
    $price       = floatval($_POST['price']);
    $stock       = intval($_POST['stock_quantity']);

    // Basic validation
    if ($name === '' || $category === '' || $price <= 0 || $stock < 0) {
        $error = "Please fill all required fields correctly.";
    } else {

        // Image handling vars
        $imageName = null;
        $uploadDir = __DIR__ . '/../uploads/products/';

        // Ensure directory exists
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0777, true);
        }

        if (!empty($_FILES['product_image']['name'])) {

            $allowedTypes = ['image/jpeg', 'image/png', 'image/webp'];
            
            // Validate Type
            if (!in_array($_FILES['product_image']['type'], $allowedTypes)) {
                $error = "Invalid image type. Only JPG, PNG, WEBP allowed.";
            } 
            // 🟢 IMPROVEMENT 1: File Size Check (Max 2MB)
            elseif ($_FILES['product_image']['size'] > 2 * 1024 * 1024) {
                $error = "Image size must be less than 2MB.";
            }
            else {
                $ext = pathinfo($_FILES['product_image']['name'], PATHINFO_EXTENSION);
                $imageName = 'product_' . time() . '.' . $ext;
                $uploadPath = $uploadDir . $imageName;
            }
        }

        if ($error === '') {
            try {
                $conn->begin_transaction();

                // Insert product info first
                $sql = "INSERT INTO products 
                        (product_name, category, description, price, stock_quantity, product_image, is_active, created_at)
                        VALUES (?, ?, ?, ?, ?, ?, 1, NOW())";

                $stmt = $conn->prepare($sql);
                $stmt->bind_param(
                    "sssdis",
                    $name, $category, $description, $price, $stock, $imageName
                );
                $stmt->execute();

                $product_id = $stmt->insert_id;

                // 🟢 IMPROVEMENT 2: Upload Check with Rollback
                if ($imageName) {
                    if (!move_uploaded_file($_FILES['product_image']['tmp_name'], $uploadPath)) {
                        // If upload fails, throw exception to trigger rollback
                        throw new Exception("Failed to upload image file.");
                    }
                }

                // Admin log
                $admin_id = $_SESSION['user_id'];
                $log = "Added new product ID {$product_id}";
                
                // ✅ FIXED: action_type -> action
                $logSql = "INSERT INTO admin_logs (admin_id, action, description, created_at)
                           VALUES (?, 'PRODUCT_ADD', ?, NOW())";
                $logStmt = $conn->prepare($logSql);
                $logStmt->bind_param("is", $admin_id, $log);
                $logStmt->execute();

                $conn->commit();
                $success = "Product added successfully.";

            } catch (Exception $e) {
                $conn->rollback(); // DB will cancel the insert if image upload fails
                // If file was somehow created but process failed later, remove it (Cleanup)
                if ($imageName && file_exists($uploadPath)) {
                    unlink($uploadPath);
                }
                $error = "Failed to add product: " . $e->getMessage();
            }
        }
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Add Product</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <style>
        body { background:#0f172a; color:#e2e8f0; font-family:Segoe UI; }
        .admin-content { margin-left:260px; padding:30px; }
        .card { background:#1e293b; padding:25px; border-radius:12px; max-width:700px; }
        label { display:block; margin-top:15px; font-size:14px; color:#94a3b8; }
        input, textarea, select {
            width:100%; padding:10px; margin-top:5px;
            background:#0f172a; color:#e2e8f0; border:1px solid #334155;
            border-radius:6px;
        }
        button {
            margin-top:20px; padding:12px 20px;
            background:#6366f1; border:none; color:#fff;
            border-radius:8px; cursor:pointer;
        }
        .msg-error { color:#ef4444; margin-bottom:10px; }
        .msg-success { color:#22c55e; margin-bottom:10px; }
    </style>
</head>
<body>

<div class="admin-content">
    <h1>Add New Product</h1>

    <div class="card">
        <?php if($error): ?><div class="msg-error"><?= $error ?></div><?php endif; ?>
        <?php if($success): ?><div class="msg-success"><?= $success ?></div><?php endif; ?>

        <form method="POST" enctype="multipart/form-data">
            <label>Product Name *</label>
            <input type="text" name="product_name" required>

            <label>Category *</label>
            <select name="category" required>
                <option value="">Select</option>
                <option value="Jersey">Jersey</option>
                <option value="Accessories">Accessories</option>
                <option value="Digital Items">Digital Items</option>
            </select>

            <label>Description</label>
            <textarea name="description" rows="4"></textarea>

            <label>Price *</label>
            <input type="number" step="0.01" name="price" required>

            <label>Stock Quantity *</label>
            <input type="number" name="stock_quantity" min="0" required>

            <label>Product Image (Max 2MB)</label>
            <input type="file" name="product_image">

            <button type="submit">
                <i class="fas fa-plus"></i> Add Product
            </button>
        </form>
    </div>
</div>

</body>
</html>