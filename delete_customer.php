<?php
session_start(); // Start the session

include 'blueprint.php'; // Include your database connection file

// Check if the request method is POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get the raw POST data
    $data = json_decode(file_get_contents("php://input"), true);
    
    // Check if the ID is set
    if (isset($data['id'])) {
        $id = intval($data['id']); // Get the customer ID
        
        // Prepare the SQL statement to delete the customer
        $stmt = $conn->prepare("DELETE FROM users WHERE id = ? AND role = 'customer'");
        $stmt->bind_param("i", $id);
        
        // Execute the statement
        if ($stmt->execute()) {
            // Check if a row was affected (i.e., customer was deleted)
            if ($stmt->affected_rows > 0) {
                echo json_encode(['success' => true, 'message' => 'Customer deleted successfully.']);
            } else {
                echo json_encode(['success' => false, 'message' => 'Customer not found.']);
            }
        } else {
            echo json_encode(['success' => false, 'message' => 'Failed to delete customer.']);
        }
        
        $stmt->close(); // Close the statement
    } else {
        echo json_encode(['success' => false, 'message' => 'Invalid request.']);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request method.']);
}

$conn->close(); // Close the database connection
