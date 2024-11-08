<?php
// Assuming you have a database connection in db_connection.php
include 'db_connection.php';

// Define the path to the debug log file
$debug_log = 'C:/xampp/htdocs/ITPJA/Prototype Drafts/Admin Prototype/debug_log.txt'; // Adjust path as needed

// Enable error logging
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Error handling function for general errors
function handleError($errno, $errstr, $errfile, $errline) {
    global $debug_log;
    error_log("Error [$errno] in $errfile at line $errline: $errstr", 3, $debug_log);
    echo json_encode(['error' => "An error occurred. Please check the logs."]);
    exit();
}
set_error_handler("handleError");

// Function to log messages to debug_log.txt
function log_debug($message) {
    global $debug_log;
    $timestamp = date('Y-m-d H:i:s');
    file_put_contents($debug_log, "[$timestamp] $message" . PHP_EOL, FILE_APPEND);
}

// Set the content type to JSON
header('Content-Type: application/json');

// Check if item_id is provided in the GET request
if (isset($_GET['item_id'])) {
    $itemId = intval($_GET['item_id']);
    
    // Log that the request was received
    log_debug("Received request to fetch product details for ItemID: $itemId");
    
    // Check if the database connection exists
    if ($conn->connect_error) {
        log_debug("Database connection error: " . $conn->connect_error);
        echo json_encode(['error' => 'Database connection failed']);
        exit();
    }

    // Prepare the SQL statement
    $query = $conn->prepare("SELECT * FROM products WHERE ItemID = ?");
    if ($query === false) {
        // Log SQL preparation error
        log_debug("SQL error (prepare): " . $conn->error);
        echo json_encode(['error' => 'Database error']);
        exit();
    }

    // Bind the parameter and execute the query
    $query->bind_param('i', $itemId);
    if (!$query->execute()) {
        // Log execution error
        log_debug("SQL error (execute): " . $query->error);
        echo json_encode(['error' => 'Database error']);
        exit();
    }

    // Get the result
    $result = $query->get_result();
    if ($result->num_rows > 0) {
        // Fetch the product details and return them as JSON
        $product = $result->fetch_assoc();
        log_debug("Product details retrieved successfully for ItemID: $itemId");
        echo json_encode($product);
    } else {
        // Log if no product is found
        log_debug("No product found with ItemID: $itemId");
        echo json_encode(['error' => 'Product not found']);
    }

    // Close the query and the connection
    $query->close();
    $conn->close();
} else {
    // Log if no item_id parameter is provided
    log_debug("No item_id parameter provided in the request");
    echo json_encode(['error' => 'Invalid request']);
}
?>
