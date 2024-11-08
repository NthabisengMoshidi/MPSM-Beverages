<?php
// Enable error reporting for debugging
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Include the database connection file
include('db_connection.php');

// Set the content type to JSON at the beginning
header('Content-Type: application/json');

// Function to log debug messages
function logDebug($message) {
    $logFile = 'debug_log.txt';
    $timestamp = date('Y-m-d H:i:s');
    file_put_contents($logFile, "[$timestamp] $message\n", FILE_APPEND);
}

// Check if the 'orderID' parameter is present in the GET request
if (isset($_GET['orderID'])) {
    $orderID = intval($_GET['orderID']);
    
    // Prepare the SQL query to fetch the order details with JOIN
    $sql = "SELECT o.id, o.order_number, o.total_amount, o.status, o.shipment_date, o.delivery_address,
                   u.first_name AS customer_first_name, u.last_name AS customer_last_name, u.email AS customer_email,
                   oi.sku, oi.product_name, oi.quantity, oi.price, oi.total_price
            FROM orders o
            JOIN users u ON o.user_id = u.id
            JOIN order_items oi ON o.id = oi.order_id
            WHERE o.id = ?";

    // Prepare the statement
    if ($stmt = $conn->prepare($sql)) {
        $stmt->bind_param("i", $orderID);
        
        // Execute the query
        if ($stmt->execute()) {
            $result = $stmt->get_result();

            // Check if any row was returned
            if ($result->num_rows > 0) {
                // Initialize response array
                $orderData = [];
                $orderItems = [];

                // Fetch data and separate order from items
                while ($row = $result->fetch_assoc()) {
                    if (empty($orderData)) {
                        $orderData = [
                            'id' => $row['id'],
                            'order_number' => $row['order_number'],
                            'total_amount' => $row['total_amount'],
                            'status' => $row['status'],
                            'shipment_date' => $row['shipment_date'],
                            'delivery_address' => $row['delivery_address'],
                            'customer_name' => $row['customer_first_name'] . ' ' . $row['customer_last_name'],
                            'customer_email' => $row['customer_email'],
                            'items' => []
                        ];
                    }

                    // Add item details to the order items array
                    $orderItems[] = [
                        'sku' => $row['sku'],
                        'product_name' => $row['product_name'],
                        'quantity' => $row['quantity'],
                        'price' => $row['price'],
                        'total_price' => $row['total_price']
                    ];
                }

                // Append items to order data
                $orderData['items'] = $orderItems;

                // Log fetched order data for debugging
                logDebug("Fetched order details for Order ID $orderID: " . json_encode($orderData));

                // Return JSON response
                echo json_encode($orderData);
            } else {
                $error = 'Order not found';
                logDebug("Error for Order ID $orderID: $error");
                echo json_encode(['error' => $error]);
            }
        } else {
            $error = 'Failed to fetch order details';
            logDebug("Error for Order ID $orderID: $error");
            echo json_encode(['error' => $error]);
        }

        // Close the statement
        $stmt->close();
    } else {
        $error = 'Failed to prepare order query';
        logDebug("Error for Order ID $orderID: $error");
        echo json_encode(['error' => $error]);
    }
} else {
    $error = 'Missing orderID parameter';
    logDebug("Error: $error");
    echo json_encode(['error' => $error]);
}

// Close the database connection
$conn->close();
