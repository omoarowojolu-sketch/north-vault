<?php

require_once "../auth/check_admin.php";
require_once "../config/database.php";


// Check that a product ID was supplied
if (!isset($_GET["id"]) || !is_numeric($_GET["id"])) {
    die("Invalid product ID.");
}

$productId = (int) $_GET["id"];


// Get the product image before deleting
$stmt = $conn->prepare("
    SELECT image
    FROM products
    WHERE id = ?
");

$stmt->bind_param("i", $productId);
$stmt->execute();

$result = $stmt->get_result();

if ($result->num_rows === 0) {
    $stmt->close();
    die("Product not found.");
}

$product = $result->fetch_assoc();

$stmt->close();


// Delete product
$stmt = $conn->prepare("
    DELETE FROM products
    WHERE id = ?
");

$stmt->bind_param("i", $productId);

if ($stmt->execute()) {

    // Delete the image from the server too
    if (!empty($product["image"])) {

        $imagePath =
            "../images/products/" . $product["image"];

        if (file_exists($imagePath)) {
            unlink($imagePath);
        }
    }

}

$stmt->close();


// Return to products page
header("Location: products.php");
exit;

?>