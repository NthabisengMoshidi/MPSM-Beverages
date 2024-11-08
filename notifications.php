<?php
session_start(); // Start session to access session data

// Include the blueprint which handles DB connection, fetching notifications, and styling
include('blueprint.php');

// Define the path to the debug log file
$debug_log = 'C:/xampp/htdocs/ITPJA/Prototype Drafts/Admin Prototype/debug_log.txt';

// Function to log to debug file    
if (!function_exists('log_debug')) {
    function log_debug($message) {
        global $debug_log;
        $timestamp = date('Y-m-d H:i:s');
        file_put_contents($debug_log, "[$timestamp] $message" . PHP_EOL, FILE_APPEND);
    }
}

// Fetch notifications from the database into the session
$select_notifications = "SELECT * FROM notifications ORDER BY created_at DESC";
log_debug("Executing query to fetch notifications: $select_notifications");

$result = $conn->query($select_notifications);
if ($result && $result->num_rows > 0) {
    $_SESSION['notifications'] = [];
    log_debug("Notifications found in database: {$result->num_rows} rows.");
    
    while ($row = $result->fetch_assoc()) {
        $_SESSION['notifications'][] = [
            'title' => $row['notification_title'] ?? '',
            'message' => $row['notification_message'] ?? '',
            'created_at' => $row['created_at'] ?? '',
            'ItemID' => $row['ItemID'] ?? '',
            'order_id' => $row['order_id'] ?? '',
            'read_status' => $row['is_read'] ?? 0 // Default to unread
        ];
    }
    log_debug("Notifications successfully fetched and stored in session.");
} else {
    log_debug("No notifications found in the database.");
    $_SESSION['notifications'] = []; // Ensure session variable is empty if nothing is found
}

// Handle deletion of notifications
if (isset($_POST['delete_notifications'])) {
    log_debug("Delete notifications request received.");

    if (!empty($_POST['notifications'])) {
        $notification_ids = $_POST['notifications']; // Array of selected notification IDs
        log_debug("Selected notification IDs for deletion: " . print_r($notification_ids, true));

        $item_ids = [];
        $order_ids = [];
        
        foreach ($notification_ids as $id) {
            if (strpos($id, 'ItemID_') === 0) {
                $item_ids[] = intval(str_replace('ItemID_', '', $id));
            } elseif (strpos($id, 'order_id_') === 0) {
                $order_ids[] = intval(str_replace('order_id_', '', $id));
            }
        }
        log_debug("Parsed Item IDs: " . implode(', ', $item_ids));
        log_debug("Parsed Order IDs: " . implode(', ', $order_ids));

        // Delete Item notifications
        if (!empty($item_ids)) {
            $placeholders = implode(',', array_fill(0, count($item_ids), '?'));
            $delete_query_items = "DELETE FROM notifications WHERE ItemID IN ($placeholders)";
            log_debug("Delete Query for ItemIDs: $delete_query_items");

            if ($stmt = $conn->prepare($delete_query_items)) {
                $stmt->bind_param(str_repeat('i', count($item_ids)), ...$item_ids);
                if ($stmt->execute()) {
                    log_debug(count($item_ids) . " Item notifications deleted successfully.");
                } else {
                    log_debug("Deletion failed for Item notifications: " . $stmt->error);
                }
                $stmt->close();
            } else {
                log_debug("Statement preparation failed for Item deletion: " . $conn->error);
            }
        }

        // Delete Order notifications
        if (!empty($order_ids)) {
            $placeholders = implode(',', array_fill(0, count($order_ids), '?'));
            $delete_query_orders = "DELETE FROM notifications WHERE order_id IN ($placeholders)";
            log_debug("Delete Query for Orders: $delete_query_orders");

            if ($stmt = $conn->prepare($delete_query_orders)) {
                $stmt->bind_param(str_repeat('i', count($order_ids)), ...$order_ids);
                if ($stmt->execute()) {
                    log_debug(count($order_ids) . " Order notifications deleted successfully.");
                } else {
                    log_debug("Deletion failed for Order notifications: " . $stmt->error);
                }
                $stmt->close();
            } else {
                log_debug("Statement preparation failed for Order deletion: " . $conn->error);
            }
        }

        $_SESSION['notification_deletion_success'] = 'Selected notifications deleted successfully.';
        log_debug("Notifications deleted successfully. Redirecting to notifications page.");
        header("Location: notifications.php");
        exit();
    } else {
        $_SESSION['notification_deletion_error'] = 'Please select at least one notification to delete.';
        log_debug("No notifications selected for deletion.");
    }
}

// Mark as Read/Unread
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['is_read'])) {
    log_debug("Mark as read/unread request received.");

    $id = null;
    $column = '';

    if (isset($_POST['item_id'])) {
        $id = intval(str_replace('ItemID_', '', $_POST['item_id']));
        $column = 'ItemID';
        log_debug("Marking ItemID $id as read/unread.");
    } elseif (isset($_POST['order_id'])) {
        $id = intval(str_replace('order_id_', '', $_POST['order_id']));
        $column = 'order_id';
        log_debug("Marking order_id $id as read/unread.");
    }

    $isRead = intval($_POST['is_read']);
    log_debug("New read status: $isRead for $column ID: $id");

    if ($id !== null && $column && in_array($isRead, [0, 1])) { // Validate input
        $updateQuery = "UPDATE notifications SET is_read = ? WHERE $column = ?";
        log_debug("Executing update query: $updateQuery");

        if ($stmt = $conn->prepare($updateQuery)) {
            $stmt->bind_param('ii', $isRead, $id);
            if ($stmt->execute()) {
                log_debug("Notification status updated successfully for $column ID $id.");
                $_SESSION['update_success'] = 'Notification status updated successfully.';
            } else {
                log_debug("Database update failed: " . $stmt->error);
                $_SESSION['update_error'] = 'Failed to update notification status.';
            }
            $stmt->close();
        } else {
            log_debug("Statement preparation failed for update query: " . $conn->error);
            $_SESSION['update_error'] = 'Error in preparing the database statement.';
        }
    } else {
        log_debug("Invalid input provided for marking as read/unread.");
        $_SESSION['update_error'] = 'Invalid input provided.';
    }

    log_debug("Redirecting to notifications page after marking read/unread.");
    header('Location: notifications.php');
    exit();
}
?>



<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>

    <title>Notifications</title>
    <style>
        /* Custom CSS for the notifications page, while keeping the blueprint CSS intact */

        /* Main content styling */
        .main-content {
            display: flex;
            flex-direction: column; /* Stack elements vertically */
            align-items: center; /* Center horizontally */
            height: auto; /* Auto height */
            margin: 100px; /* Remove default margin */
            padding: 20px;
            background-color: white; /* Optional: background color for contrast */
        }

        /* Title styling */
        .notifications-title {
            font-size: 2em;
            font-weight: bold;
            margin-bottom: 20px;
            text-align: center; /* Center title */
        }

        /* Filter section styling */
        .filter-section {
            width: 90%; /* Make the filter section responsive */
            max-width: 800px; /* Increased maximum width */
            padding: 10px; /* Reduced padding */
            background-color: #f1f1f1; /* Light background for contrast */
            border-radius: 8px; /* Rounded corners */
            margin-bottom: 20px; /* Space below filter section */
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1); /* Subtle shadow */
        }

        /* Filter label styling */
        .filter-label {
            font-weight: bold; /* Make labels bold */
        }

        /* Filter input styling */
        .filter-input {
            width: 150px; /* Set width for compactness */
            padding: 5px; /* Reduced padding */
            border-radius: 5px; /* Rounded corners */
            border: 1px solid #ccc; /* Border styling */
            margin-left: 10px; /* Space between input and label */
        }

        /* Notification form styling */
        .notifications-form {
            width: 90%; /* Make the form responsive */
            max-width: 800px; /* Increased maximum width */
            padding: 30px; /* Increased padding */
            border: outset 2px orange; /* Orange border */
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.2); /* Box shadow */
            background-color: white; /* Form background color */
            border-radius: 8px; /* Optional: rounded corners */
        }
/* Notifications container styling */
.notifications {
    margin-top: 20px;
    padding-bottom: 10px;
}

/* Single notification block */
.notification {
    margin-bottom: 20px;
    padding-bottom: 10px;
}

/* Notification title styling */
.notification-title {
    font-size: 1.2em;
    font-weight: bold;
    display: flex; /* Use flexbox to align items */
    align-items: center; /* Center items vertically */
    margin: 0 0 5px 40px; /* Margin for spacing */
}

/* Message and date styling */
.notification-message {
    font-size: 1em;
    display: flex; /* Use flexbox for message and date */
    align-items: center; /* Center items vertically */
    margin: 0 0 5px 20px; /* Maintain spacing below message */
}

/* Date styling */
.notification-date {
    font-style: italic;
    color: gray;
    margin-left: 10px; /* Space between message and date */
    display: inline; /* Keep in the same line */
}

/* Single divider below all notifications */
.notifications-divider {
    border-top: 1px solid #ccc; /* Single divider line */
    margin: 20px 0; /* Space above and below the line */
}

/* Larger checkbox styling */
.notification-checkbox {
    width: 20px; /* Custom width */
    height: 20px; /* Custom height */
    margin-right: 2px; /* Space between checkbox and title */
}

        /*--------------------Delete Button------------------------------*/
        .delete-button {
            background-color: red; /* Red background */
            color: white; /* White font color */
            border: none; /* Remove default border */
            border-radius: 8px; /* Rounded corners */
            padding: 10px 20px; /* Padding for better size */
            font-size: 16px; /* Font size */
            cursor: pointer; /* Change cursor to pointer */
            margin-bottom: 20px; /* Space below the button */
            transition: background-color 0.3s ease; /* Smooth transition */
        }

        /* Hover effect */
        .delete-button:hover {
            background-color: darkred; /* Darker red on hover */
        }

        /* ----- Additional button styles -------------*/
        .mark-read-button {
            margin-left: 10px; /* Space between date and button */
            padding: 5px 10px;
            border: none;
            border-radius: 5px;
            background-color: #007bff;
            color: white;
            cursor: pointer;
            transition: background-color 0.3s ease;
        }

        .mark-read-button:hover {
            background-color: #0056b3; /* Darker blue on hover */
        }


    </style>
</head>

<body>
    
<div class="main-content">
<h1 class="notifications-title">Notifications</h1>

    <!-- Filter Section -->
    <div class="filter-section">
        <h2 style="margin: 0;">Filter Notifications</h2>
        
        <label class="filter-label" for="filterTitle">Title:</label>
        <input type="text" id="filterTitle" class="filter-input" placeholder="Enter title..." oninput="applyFilters()">

        <div style="display: flex; align-items: center; gap: 10px;">
            <label class="filter-label" for="filterDate">Date:</label>
            <input type="date" id="filterDate" class="filter-input" oninput="applyFilters()">

            <label class="filter-label" for="filterStatus">Status:</label>
            <select id="filterStatus" class="filter-input" onchange="applyFilters()">
                <option value="">All</option>
                <option value="read">Read</option>
                <option value="unread">Unread</option>
            </select>
        </div>
    </div>
<!-- --------------HTML FORM code ----------------------------->
<form class="notifications-form" action="notifications.php" method="post">
    <div class="notifications">
        <?php
        // Check if notifications exist in the session
        if (isset($_SESSION['notifications']) && !empty($_SESSION['notifications'])) {
            foreach ($_SESSION['notifications'] as $notification) {
                // Determine if the notification is an order or an item
                $notificationId = !empty($notification['order_id']) ? $notification['order_id'] : (isset($notification['ItemID']) ? $notification['ItemID'] : null);
                $idPrefix = !empty($notification['order_id']) ? 'order_id_' : 'ItemID_';

                // Output each notification with relevant details and read/unread styling
                echo '<div class="notification"' . ($notification['read_status'] ? ' style="opacity: 0.5;"' : '') . '>';
                
                // Notification Title
                echo '<div class="notification-title">' . htmlspecialchars($notification['title']) . '</div>';
                
                // Checkbox for selecting notifications to delete
                if ($notificationId !== null) {
                    echo '<input type="checkbox" class="notification-checkbox" name="notifications[]" value="' . 
                        $idPrefix . htmlspecialchars($notificationId, ENT_QUOTES, 'UTF-8') . '">';
                }
                
                // Notification Message
                echo '<div class="notification-message">' . htmlspecialchars($notification['message']) . '</div>';
                
                // Notification Date
                echo '<div class="notification-date">Date: ' . date('F j, Y, g:i a', strtotime($notification['created_at'])) . '</div>';
                
                // Read/Unread toggle button with AJAX functionality
                echo '<button type="button" class="mark-read-button" data-id="' . 
                    $idPrefix . htmlspecialchars($notificationId, ENT_QUOTES, 'UTF-8') . 
                    '" onclick="toggleReadStatus(this)">' . 
                    ($notification['read_status'] ? 'Mark as Unread' : 'Mark as Read') . 
                    '</button>';
                
                echo '</div>'; // Close notification div
            }
        } else {
            echo '<p>No notifications available.</p>';
        }
        ?>
    </div>

    <!-- Button to delete selected notifications -->
    <button type="submit" name="delete_notifications" class="delete-button">Delete Selected</button>
</form>



<!-- ------------JavaScript for handling read/unread toggle with AJAX ------------------------>
<script>
    function toggleReadStatus(button) {
        const notificationId = button.getAttribute('data-id');
        const isRead = button.innerText.includes('Unread') ? 0 : 1;
        
        // Create AJAX request
        const xhr = new XMLHttpRequest();
        xhr.open("POST", "notifications.php", true);
        xhr.setRequestHeader("Content-type", "application/x-www-form-urlencoded");

        // Update button text upon success
        xhr.onreadystatechange = function () {
            if (xhr.readyState == 4 && xhr.status == 200) {
                button.innerText = isRead ? 'Mark as Read' : 'Mark as Unread';
                button.parentElement.style.opacity = isRead ? '1' : '0.5';
                window.location.reload();
            }
            else {
                console.error("Failed to update notification status");
            }
        };

        // Send AJAX request with required data
        xhr.send("is_read=" + isRead + "&" + (notificationId.startsWith("ItemID_") ? "item_id=" : "order_id=") + notificationId);
    }

//-----------------------delete JS code---------------------------------
    document.querySelector('.delete-button').addEventListener('click', function(event) {
    event.preventDefault(); // Prevents default form submission

    const checkedNotifications = [...document.querySelectorAll('.notification-checkbox:checked')];
    const notifications = checkedNotifications.map(checkbox => checkbox.value).join('&notifications[]=');

    const xhr = new XMLHttpRequest();
    xhr.open("POST", "notifications.php", true);
    xhr.setRequestHeader("Content-type", "application/x-www-form-urlencoded");

    xhr.onreadystatechange = function() {
        if (xhr.readyState === 4 && xhr.status === 200) {
            const response = JSON.parse(xhr.responseText);
            if (response.status === 'success') {
                // Reload the page to reflect the updated notification list
                window.location.reload();
            } else {
                console.error("Failed to delete notifications");
            }
        }
    };

    xhr.send("delete_notifications=1&notifications[]=" + notifications);
});

</script>


<script>
function applyFilters() {
    const title = document.getElementById('filterTitle').value.toLowerCase();
    const date = document.getElementById('filterDate').value;
    const status = document.getElementById('filterStatus').value;

    const notifications = document.querySelectorAll('.notification');
    
    notifications.forEach(notification => {
        const notificationTitle = notification.querySelector('.notification-title').textContent.toLowerCase();
        const notificationDate = notification.querySelector('.notification-date').textContent;
        const isRead = notification.style.opacity === '0.5'; // Adjust based on your opacity setting

        let isVisible = true;

        if (title && !notificationTitle.includes(title)) {
            isVisible = false;
        }

        if (date && !notificationDate.includes(date)) {
            isVisible = false;
        }

        if (status === 'read' && !isRead) {
            isVisible = false;
        } else if (status === 'unread' && isRead) {
            isVisible = false;
        }

        notification.style.display = isVisible ? 'block' : 'none';
    });
}

</script>


</body>



<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
<?php if (isset($_SESSION['notification_deletion_success'])): ?>
    Swal.fire({
        icon: 'success',
        title: 'Success',
        text: '<?php echo $_SESSION['notification_deletion_success']; ?>',
        showConfirmButton: true,
    }).then(() => {
        window.location.href = 'notifications.php';
    });
    <?php unset($_SESSION['notification_deletion_success']); ?>
<?php elseif (isset($_SESSION['notification_deletion_error'])): ?>
    Swal.fire({
        icon: 'error',
        title: 'Error',
        text: '<?php echo $_SESSION['notification_deletion_error']; ?>',
        showConfirmButton: true,
    });
    <?php unset($_SESSION['notification_deletion_error']); ?>
<?php endif; ?>
</script>

</html>