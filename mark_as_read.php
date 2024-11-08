<?php
session_start();
include('blueprint.php'); // Ensure this includes the DB connection

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $itemId = isset($_POST['item_id']) ? intval(str_replace('ItemID_', '', $_POST['item_id'])) : null;
    $isRead = isset($_POST['is_read']) ? intval($_POST['is_read']) : null;

    if ($itemId !== null && $isRead !== null) {
        // Prepare and execute the update query
        $updateQuery = "UPDATE notifications SET is_read = ? WHERE ItemID = ?";
        if ($stmt = $conn->prepare($updateQuery)) {
            $stmt->bind_param('ii', $isRead, $itemId);
            if ($stmt->execute()) {
                // Set success message in session
                $_SESSION['update_success'] = 'Notification status updated successfully.';
            } else {
                // Set error message in session
                $_SESSION['update_error'] = 'Database update failed: ' . $stmt->error;
            }
            $stmt->close();
        } else {
            // Set error message in session
            $_SESSION['update_error'] = 'Statement preparation failed: ' . $conn->error;
        }
    } else {
        // Set error message for invalid input
        $_SESSION['update_error'] = 'Invalid input.';
    }
}

// Redirect back to the notifications page
//header('Location: notifications.php');
exit();
