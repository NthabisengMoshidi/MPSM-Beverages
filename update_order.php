<?php
// Enable error reporting for debugging
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
ini_set('error_log', 'debug_log.txt'); // Specify the log file path

// Log the request method
error_log('Request method: ' . $_SERVER['REQUEST_METHOD'] . "\n", 3, 'debug_log.txt');

// Include database connection
include('db_connection.php');

// Enable MySQLi error reporting for better error handling
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

// Set a default response array
$response = ['success' => false, 'message' => 'An unexpected error occurred.'];

try {
    // Check if request method is POST
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        // Get the form data
        $orderId = intval($_POST['order_id']);
        $customerName = $_POST['customer_name'];
        $customerEmail = $_POST['customer_email'];
        $totalAmount = floatval($_POST['total_amount']);
        $status = $_POST['status'];
        $shipmentDate = $_POST['shipment_date'] ?? null; // Ensure shipment date is optional
        $promoCode = $_POST['promo_code'] ?? null;       // Ensure promo code is optional
        $deliveryAddress = $_POST['delivery_address'];
        $city = $_POST['city']; // Retrieve the city field

        // Assuming order items are passed as a JSON string
        $orderItems = json_decode($_POST['order_items'], true); // Decode JSON into an array

        // Log the received data for debugging
        error_log("\nReceived data: orderID=$orderId, customerName=$customerName, email=$customerEmail, totalAmount=$totalAmount, status=$status, shipmentDate=$shipmentDate, city=$city, orderItems=" . print_r($orderItems, true) . "\n", 3, 'debug_log.txt');

        // Prepare the SQL update statement for the orders table
        $updateOrderSql = "UPDATE orders SET 
                            total_amount = ?, 
                            status = ?, 
                            shipment_date = ?, 
                            promo_code = ?, 
                            delivery_address = ? 
                          WHERE id = ?";

        if ($updateOrderStmt = $conn->prepare($updateOrderSql)) {
            $updateOrderStmt->bind_param("dssssi", $totalAmount, $status, $shipmentDate, $promoCode, $deliveryAddress, $orderId);

            if ($updateOrderStmt->execute()) {
                error_log("Order ID $orderId updated successfully.\n", 3, 'debug_log.txt');

                // Update order items if they are provided
                if (!empty($orderItems)) {
                    foreach ($orderItems as $item) {
                        $itemId = intval($item['id']);
                        $quantity = intval($item['quantity']);
                        $price = floatval($item['price']);

                        $updateItemSql = "UPDATE order_items SET 
                                            quantity = ?, 
                                            price = ? 
                                          WHERE id = ? AND order_id = ?";

                        if ($updateItemStmt = $conn->prepare($updateItemSql)) {
                            $updateItemStmt->bind_param("diii", $quantity, $price, $itemId, $orderId);
                            $updateItemStmt->execute();
                            $updateItemStmt->close();
                        }
                    }
                }

                // Update user details based on the email
                $updateUserSql = "UPDATE users SET 
                                    first_name = ?, 
                                    last_name = ?, 
                                    email = ?, 
                                    city = ? 
                                  WHERE email = ?";

                // Split customer name into first and last with validation
                $nameParts = explode(' ', $customerName, 2);
                $firstName = $nameParts[0];
                $lastName = isset($nameParts[1]) ? $nameParts[1] : '';

                if ($updateUserStmt = $conn->prepare($updateUserSql)) {
                    $updateUserStmt->bind_param("sssss", $firstName, $lastName, $customerEmail, $city, $customerEmail);
                    $updateUserStmt->execute();
                    $updateUserStmt->close();
                }

                $response['success'] = true;
                $response['message'] = 'Order and related data updated successfully.';
            } else {
                throw new Exception("Failed to update order.");
            }
        } else {
            throw new Exception("Failed to prepare update statement for orders.");
        }
    } else {
        throw new Exception("Invalid request method.");
    }
} catch (Exception $e) {
    error_log("Exception: " . $e->getMessage() . "\n", 3, 'debug_log.txt');
    $response['message'] = $e->getMessage();
} finally {
    header('Content-Type: application/json');
    echo json_encode($response);
    $conn->close();
}
