<?php
require_once '../config/session.php';
require_once '../config/database.php';

requireAdmin();
$conn = getConnection();

if (!isset($_GET['id'])) {
    header("Location: products.php?error=invalid_id");
    exit();
}

$product_id = (int)$_GET['id'];

// get image name
$product = getSingleRow($conn, "SELECT product_image FROM products WHERE product_id = $product_id");

if (!$product) {
    header("Location: products.php?error=not_found");
    exit();
}

// delete image
if (!empty($product['product_image'])) {
    $imgPath = __DIR__ . '/../uploads/products/' . $product['product_image'];
    if (file_exists($imgPath)) {
        unlink($imgPath);
    }
}

// delete product
if (executeQuery($conn, "DELETE FROM products WHERE product_id = $product_id")) {
    header("Location: products.php?success=deleted");
} else {
    header("Location: products.php?error=delete_failed");
}
