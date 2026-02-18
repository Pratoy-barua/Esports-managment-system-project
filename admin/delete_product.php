<?php
require_once __DIR__ . '/../config/session.php';
require_once __DIR__ . '/../config/database.php';

requireAdmin();
$conn = getConnection();

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header("Location: products.php?error=invalid_id");
    exit;
}

$product_id = (int) $_GET['id'];

try {
    // 🔹 Get product image name first
    $stmt = $conn->prepare("SELECT product_image FROM products WHERE product_id = ?");
    $stmt->bind_param("i", $product_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $product = $result->fetch_assoc();

    if (!$product) {
        throw new Exception("Product not found");
    }

    // 🔹 Delete product from DB
    $delStmt = $conn->prepare("DELETE FROM products WHERE product_id = ?");
    $delStmt->bind_param("i", $product_id);

    if (!$delStmt->execute()) {
        throw new Exception("Delete failed");
    }

    // 🔹 Delete image file (if exists)
    if (!empty($product['product_image'])) {
        $imagePath = __DIR__ . '/../uploads/products/' . $product['product_image'];
        if (file_exists($imagePath)) {
            unlink($imagePath);
        }
    }

    header("Location: products.php?success=product_deleted");
    exit;

} catch (Exception $e) {
    header("Location: products.php?error=delete_failed");
    exit;
}
