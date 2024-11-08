<?php
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    include_once('db_connection.php'); // Assume this connects to your DB

    $productName = $_POST['productName'];
    $SKU = $_POST['SKU'];
    $price = $_POST['price'];
    $Category = $_POST['Category'];
    $stockLevel = $_POST['stockLevel'];
    $restockLevel = $_POST['restockLevel'];
    $availabilityStatus = $_POST['availabilityStatus'];
    $volume = $_POST['volume'];
    $expirationDate = $_POST['expirationDate'];
    $created_at = $_POST['createdAt']; // Correct column name

    // Handle file upload
    $targetFile = null;
    if (isset($_FILES['imageURL']) && $_FILES['imageURL']['error'] == 0) {
        $targetDir = "uploads/";
        $targetFile = $targetDir . basename($_FILES["imageURL"]["name"]);
        move_uploaded_file($_FILES["imageURL"]["tmp_name"], $targetFile);
    }

    // Insert into the database
    $sql = "INSERT INTO products (productName, SKU, price, Category, stockLevel, restockLevel, availabilityStatus, volume, imageURL, expirationDate, created_at)
            VALUES ('$productName', '$SKU', '$price', '$Category', '$stockLevel', '$restockLevel', '$availabilityStatus', '$volume', '$targetFile', '$expirationDate', '$created_at')";

    if (mysqli_query($conn, $sql)) {
        header("Location: products.php?success=Product Added Successfully");
    } else {
        $errorMsg = "Failed to add the product. Database error: " . mysqli_error($conn);
        header("Location: products.php?error=" . urlencode($errorMsg));
    }
    exit;
}
