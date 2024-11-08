<?php
// Start the session
session_start();
include('config.php');

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    echo 'error';
    exit;
}

// Validate reviewID from the GET request
$reviewID = isset($_GET['reviewID']) ? intval($_GET['reviewID']) : 0;
$userID = $_SESSION['user_id']; // Get the logged-in user's ID

// Check if the review belongs to the logged-in user
$stmt = $conn->prepare("SELECT * FROM reviews WHERE reviewID = ? AND user_id = ?");
$stmt->bind_param("ii", $reviewID, $userID);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    // No review found or it doesn't belong to the user
    echo 'error';
    $stmt->close();
    exit;
}

// Proceed to delete the review if it belongs to the user
$stmt->close();
$stmt = $conn->prepare("DELETE FROM reviews WHERE reviewID = ? AND user_id = ?");
$stmt->bind_param("ii", $reviewID, $userID);
if ($stmt->execute()) {
    echo 'success';
} else {
    echo 'error';
}
$stmt->close();
$conn->close();
?>
