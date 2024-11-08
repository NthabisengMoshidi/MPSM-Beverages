<?php
// Start the session to ensure user ID is available
session_start();

// Include database connection
include('config.php');

// Check if the request method is POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Sanitize the input data
    $itemID = intval($_POST['ItemID']);
    $rating = intval($_POST['rating']);
    $reviewText = trim($_POST['reviewText']);
    $reviewerName = trim($_POST['reviewerName']);

    // Validate the inputs
    if ($rating < 1 || $rating > 5) {
        header("Location: review.php?ItemId=$itemID&error=invalid_rating");
        die("Invalid rating. Please provide a rating between 1 and 5.");
        
    }

    // Check if the ItemID exists in the products table
    $checkStmt = $conn->prepare("SELECT COUNT(*) FROM products WHERE ItemID = ?");
    $checkStmt->bind_param("i", $itemID);
    $checkStmt->execute();
    $checkStmt->bind_result($count);
    $checkStmt->fetch();
    $checkStmt->close();

    if ($count === 0) {
        header("Location: review.php?ItemID=$itemID&error=product_not_found");
        die("Error: The specified product does not exist.");
    }

    // Prepare and execute the SQL statement to insert the review
    $stmt = $conn->prepare("INSERT INTO reviews (ItemID, user_id, rating, reviewText, reviewerName) VALUES (?, ?, ?, ?, ?)");

    // Assuming there's a user_id session variable; if user is not logged in, you can set a default user ID (like 1)
    $userId = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : 1; // Change to your session logic

    if ($stmt) {
        $stmt->bind_param("iiiss", $itemID, $userId, $rating, $reviewText, $reviewerName);

        if ($stmt->execute()) {
            // Redirect back to the product page with a success message
            header("Location: review.php?products_ItemID=" . $itemID . "&success=1");
            exit();
        } else {
            echo "Error saving review: " . $stmt->error;
        }

        $stmt->close();
    } else {
        echo "Error preparing statement: " . $conn->error;
    }
} else {
    // If not a POST request, redirect or show an error
    header("Location: review.php"); // Change to a suitable location
    exit();
}
?>
