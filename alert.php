<?php
session_start();

// Function to add messages to the session
function add_message($type, $msg) {
    if (!isset($_SESSION[$type])) {
        $_SESSION[$type] = [];
    }
    // Limit the number of messages stored to prevent large arrays
    if (count($_SESSION[$type]) < 5) { // Store only the latest 5 messages
        $_SESSION[$type][] = $msg;
    }
}

// Display success messages
if (isset($_SESSION['success_msg'])) {
    foreach ($_SESSION['success_msg'] as $msg) {
        echo '<script>Swal.fire({ title: "Success!", text: "' . htmlspecialchars($msg, ENT_QUOTES) . '", icon: "success", confirmButtonText: "Okay" });</script>';
    }
    unset($_SESSION['success_msg']);
}

// Display error messages
if (isset($_SESSION['error_msg'])) {
    foreach ($_SESSION['error_msg'] as $msg) {
        echo '<script>Swal.fire({ title: "Error!", text: "' . htmlspecialchars($msg, ENT_QUOTES) . '", icon: "error", confirmButtonText: "Okay" });</script>';
    }
    unset($_SESSION['error_msg']);
}
?>
