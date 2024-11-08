<?php
// Include database connection
include 'db_connection.php'; // Make sure this file contains the necessary database connection code

// Initialize response array
$response = array('success' => false, 'message' => '');

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Check if orderID is passed
    if (isset($_POST['orderID'])) {
        $orderID = intval($_POST['orderID']);

        // Prepare a SQL statement to delete the order
        $sql = "DELETE FROM orders WHERE id = ?";
        if ($stmt = $conn->prepare($sql)) {
            $stmt->bind_param("i", $orderID);

            // Attempt to execute the prepared statement
            if ($stmt->execute()) {
                // Check if any rows were affected
                if ($stmt->affected_rows > 0) {
                    $response['success'] = true;
                    $response['message'] = 'Order successfully deleted.';
                } else {
                    $response['message'] = 'Order not found or could not be deleted.';
                }
            } else {
                $response['message'] = 'Error executing query: ' . $stmt->error;
            }

            // Close the prepared statement
            $stmt->close();
        } else {
            $response['message'] = 'Error preparing query: ' . $conn->error;
        }
    } else {
        $response['message'] = 'Order ID not provided.';
    }
} else {
    $response['message'] = 'Invalid request method.';
}

// Close the database connection
$conn->close();

// Return response in JSON format
echo json_encode($response);
