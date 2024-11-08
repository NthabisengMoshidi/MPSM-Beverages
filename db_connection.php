<?php
$servername = "localhost"; 
$username = "root"; // Database username
$password = "P@ssword"; // Database password
$dbname = "mpsm_ecommerce";

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Example query to fetch notification count from a notifications table
$query = "SELECT COUNT(*) as count FROM notifications WHERE status = 'unread'";
$result = $conn->query($query);
if ($result) {
    $row = $result->fetch_assoc();
    $notificationCount = $row['count'];
} else {
    $notificationCount = 0; // Fallback if query fails
}


?>


