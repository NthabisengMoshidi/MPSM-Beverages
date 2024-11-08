<?php
include 'db_connection.php'; // Include DB connection

// Define the path to the debug log file
$debug_log = 'C:/xampp/htdocs/ITPJA/Prototype Drafts/Admin Prototype/debug_log.txt';

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);  
error_reporting(E_ALL);

// Log debug messages  
if (!function_exists('log_debug')) {
    function log_debug($message) {
        global $debug_log;
        $timestamp = date('Y-m-d H:i:s');
        file_put_contents($debug_log, "[$timestamp] $message" . PHP_EOL, FILE_APPEND);
    }
}

// Check database connection
if (!$conn) {
    log_debug("Database connection error.");
    echo json_encode(['error' => 'Database connection failed']);
    exit();
}

// Count unread notifications
$notification_count_query = "SELECT COUNT(*) AS unread_count FROM notifications WHERE is_read = 0";
$result_count = $conn->query($notification_count_query);

if ($result_count) {
    $row = $result_count->fetch_assoc();
    $notification_count = $row['unread_count'];
    log_debug("Unread notifications count: $notification_count");
} else {
    log_debug("Error counting unread notifications: " . $conn->error);
    $notification_count = 0; // Default to 0 if there's an error
}

// Check if ItemID is provided in the request
if (isset($_GET['ItemID'])) {
    $itemId = intval($_GET['ItemID']);
    log_debug("Received request to fetch product details for ItemID: $itemId");

    // Prepare SQL query to fetch the product details
    $query = $conn->prepare("SELECT * FROM products WHERE ItemID = ?");
    if ($query === false) {
        log_debug("SQL error (prepare): " . $conn->error);
        echo json_encode(['error' => 'Database error during preparation']);
        exit();
    }

    // Bind parameters and execute query
    $query->bind_param('i', $itemId);
    if (!$query->execute()) {
        log_debug("SQL error (execute): " . $query->error);
        echo json_encode(['error' => 'Database error during execution']);
        exit();
    }

    // Fetch the result and return product details as JSON
    $result = $query->get_result();
    if ($result->num_rows > 0) {
        $product = $result->fetch_assoc();
        log_debug("Product details retrieved successfully for ItemID: $itemId");
        echo json_encode($product);
    } else {
        log_debug("No product found with ItemID: $itemId");
        echo json_encode(['error' => 'No product found']);
    }

    // Close the query
    $query->close();
}


