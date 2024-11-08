<?php
// Database connection
$servername = "localhost"; 
$username = "root"; 
$password = "P@ssword"; 
$dbname = "mpsm_ecommerce";

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Check if itemId is provided
if (isset($_POST['itemId'])) {
    $itemId = intval($_POST['itemId']); // Ensure it's an integer to prevent SQL injection

    // Prepare the delete statement
    $sql = "DELETE FROM products WHERE ItemID = ?";
    $stmt = $conn->prepare($sql);
    
    if ($stmt) {
        $stmt->bind_param("i", $itemId); // Bind parameters
        if ($stmt->execute()) {
            echo json_encode(["success" => true]);
        } else {
            echo json_encode(["success" => false, "message" => "Error deleting record: " . $stmt->error]);
        }
        $stmt->close();
    } else {
        echo json_encode(["success" => false, "message" => "Error preparing statement: " . $conn->error]);
    }
} else {
    echo json_encode(["success" => false, "message" => "No item ID provided."]);
}

$conn->close(); // Close the connection
?>
