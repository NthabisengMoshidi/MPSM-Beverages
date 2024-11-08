<?php
session_start(); // Start the session

include 'blueprint.php'; // Include your database connection file

// Check if the request method is POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get the posted data
    $id = intval($_POST['id']); // Get the customer ID
    $first_name = $_POST['first_name']; // Get the first name
    $last_name = $_POST['last_name']; // Get the last name
    $email = $_POST['email']; // Get the email
    $phone = $_POST['phone']; // Get the phone number
    $home_address = $_POST['home_address']; // Get the home address
    $city = $_POST['city']; // Get the city

    // Prepare the SQL statement to update the customer
    $stmt = $conn->prepare("UPDATE users SET first_name = ?, last_name = ?, email = ?, phone = ?, home_address = ?, city = ? WHERE id = ? AND role = 'customer'");
    $stmt->bind_param("ssssssi", $first_name, $last_name, $email, $phone, $home_address, $city, $id); // Corrected here

    // Execute the statement
    if ($stmt->execute()) {
        // Check if any rows were updated (i.e., customer details were changed)
        if ($stmt->affected_rows > 0) {
            echo json_encode(['success' => true, 'message' => 'Customer updated successfully.']);
        } else {
            echo json_encode(['success' => false, 'message' => 'No changes made or customer not found.']);
        }
    } else {
        echo json_encode(['success' => false, 'message' => 'Failed to update customer.']);
    }
    
    $stmt->close(); // Close the statement
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request method.']);
}

$conn->close(); // Close the database connection
