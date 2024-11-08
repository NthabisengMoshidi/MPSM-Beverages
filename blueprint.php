<?php  
include_once('db_connection.php');  // Include your database connection

// Start session if not already started
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
$conn->query("CALL get_notifications()");

// Define the path to the debug log file
$debug_log = 'C:/xampp/htdocs/ITPJA/Prototype Drafts/Admin Prototype/debug_log.txt';

// Enable error logging and report all errors
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Custom error handler to log errors
if (!function_exists('handleError')) {
    function handleError($errno, $errstr, $errfile, $errline) {
        global $debug_log;
        error_log("Error [$errno] in $errfile at line $errline: $errstr", 3, $debug_log);
        //header('Content-Type: application/json');
        echo json_encode(['error' => "An error occurred. Please check the logs."]);
        exit();
    }
}
set_error_handler("handleError");

// Log function definition
if (!function_exists('log_debug')) {
    function log_debug($message) {
        global $debug_log;
        $timestamp = date('Y-m-d H:i:s');
        file_put_contents($debug_log, "[$timestamp] $message" . PHP_EOL, FILE_APPEND);
    }
}

// Ensure connection is open
if (!$conn || !($conn instanceof mysqli)) {
    error_log("Connection failed or is closed: " . mysqli_connect_error(), 3, $debug_log);
    die("Connection failed or is closed: " . mysqli_connect_error());
} else {
    log_debug("Database connection established.");
}

// Fetch notification count from the database (count unread notifications)
$query = "SELECT COUNT(*) AS notification_count FROM notifications WHERE is_read = 0";  

// Ensure the connection is open before executing the query
if ($conn->ping()) {
    $result = $conn->query($query);
    if ($result && $row = $result->fetch_assoc()) {
        $_SESSION['notification_count'] = $row['notification_count'];  
        log_debug("Notification count updated: " . $row['notification_count']);
    } else {
        $_SESSION['notification_count'] = 0;  
        log_debug("Failed to fetch notification count: " . $conn->error);
    }
} else {
    log_debug("Connection is not valid for querying.");
}

// Include fetch_notifications.php for fetching notifications
include_once 'fetch_notifications.php'; 

?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Inventory Management</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.datatables.net/1.11.5/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <style> 
    /* Sidebar */
    .sidebar {
        width: 250px;
        height: 100vh;
        position: fixed;
        top: 0;
        left: 0;
        background: white;
        color: black;
        padding-top: 20px;
    }

    .sidebar .profile {
        text-align: center;
        margin-bottom: 20px;
    }

    .sidebar .profile img {
        width: 100px;
        height: 100px;
        border-radius: 50%;
        border: 2px solid white;
    }

    .sidebar ul {
        list-style-type: none;
        padding: 0;
    }

    .sidebar ul li {
        position: relative;
        padding: 15px;
        text-align: left;
    }

    .sidebar ul li a {
        color: black;
        text-decoration: none;
        font-size: 18px;
        transition: background 0.3s, border-radius 0.3s;
        display: flex;
        align-items: center;
        padding: 10px;
        border-radius: 50px;
    }

    .sidebar ul li a i {
        margin-right: 10px;
    }

    .sidebar ul li a:hover {
        background-color: rgba(200, 200, 200, 0.6);
        cursor: pointer;
        border-radius: 50px;
    }

    .sub-menu {
        display: none;
        position: absolute;
        left: 100%;
        top: 0;
        background: white;
        border-radius: 5px;
        box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
        z-index: 1;
    }

    .sidebar ul li:hover .sub-menu {
        display: block;
    }

    .sub-menu li {
        padding: 10px;
    }

    .sub-menu li a {
        color: black;
        text-decoration: none;
        display: block;
        border-radius: 5px;
    }

    .sub-menu li a:hover {
        background-color: rgba(255, 165, 0, 0.3);
    }

    /* Top Navigation bar */
    nav {
        background: white;
        color: black;
        padding: 15px;
        position: fixed;
        width: calc(100% - 250px);
        left: 250px;
        top: 0;
        z-index: 1;
        display: flex;
        justify-content: space-around;
    }

    .nav-right {
        display: flex;
        align-items: center;
    }

    .search-bar {
        padding: 10px;
        border-radius: 20px;
        border: 1px solid #ccc;
        outline: none;
        width: 200px;
        margin-right: 15px;
    }

    .nav-right i {
        color: black;
        font-size: 30px;
        margin-left: 15px;
        cursor: pointer;
        transition: color 0.3s ease;
    }

    .nav-right i:hover {
        color: #2980b9;
    }

    /* Left-side Navigation bar */
    .nav-left {
        display: flex;
        align-items: center;
    }

    .nav-left i {
        font-size: 24px;
        color: black;
        margin: 0 10px;
        position: relative;
    }

    .nav-left a {
        position: relative;
    }

    .notification-badge {
        position: absolute;
        top: -8px;
        right: -8px;
        background-color: red;
        color: white;
        border-radius: 50%;
        padding: 3px 7px;
        font-size: 12px;
    }

    .nav-left i:hover {
        color: #2565AE;
        cursor: pointer;
    }

    /* ----------Footer Styles -------------*/
    footer {
        position: relative; /* Ensures it stays within the document flow */
        bottom: 0;
        width: 100%;
        text-align: center;
        padding: 10px;
        background-color: #333;
        color: white;
        font-size: 14px;
        opacity: 0;
        transition: opacity 0.5s ease-in-out;
    }

    /* When user scrolls to the bottom, make the footer visible */
    .footer-visible {
        opacity: 1;
    }

    </style>

</head>

<body>

<div class="sidebar">
    <div class="profile">
        <img src="logo.png" alt="Profile Picture">
        <h3>Welcome Administrator</h3>
    </div>
    <ul>
        <li>
            <a href="dashboard.php" class="main-item">
                <i class="fas fa-tachometer-alt"></i> Dashboard <i class="fas fa-chevron-down"></i>
            </a>
            
        </li>
        <li>
            <a href="products.php" class="main-item">
                <i class="fas fa-archive"></i> Inventory <i class="fas fa-chevron-down"></i>
            </a>
            
        </li>
        <li>
            <a href="ordermanagement.php" class="main-item">
                <i class="fas fa-receipt"></i> Orders <i class="fas fa-chevron-down"></i>
            </a>
            
        </li>
        <li>
            <a href="Analytics.php" class="main-item">
                <i class="fas fa-file-invoice"></i> Analytics <i class="fas fa-chevron-down"></i>
            </a>
            
        </li>
        <li>
            <a href="Analytics.php" class="main-item">
                <i class="fas fa-chart-line"></i> Reporting <i class="fas fa-chevron-down"></i>
            </a>
            
        </li>
        <li>
            <a href="blueprint.php" class="main-item">
                <i class="fas fa-question-circle"></i> Support <i class="fas fa-chevron-down"></i>
            </a>
           
        </li>
        <li>
            <a href="customer_management.php" class="main-item">
                <i class="fas fa-cog"></i> Customer Management <i class="fas fa-chevron-down"></i>
            </a>
           
        </li>
    </ul>
</div>
<nav> 
    <div class="nav-left">
        <i class="fas fa-search"></i>
        <input type="text" class="search-bar" id="searchBar" placeholder="Search...">
        <a href="registrationpage.php">
            <i class="fas fa-user" id="profileIcon"></i> <!-- Profile icon -->
        </a>
        <i class="fas fa-cog"></i> <!-- Settings icon -->
        <a href="notifications.php">
            <i class="fas fa-bell" id="notificationIcon"></i>
            <span class="notification-badge" id="notificationCount">
                <?php echo isset($_SESSION['notification_count']) ? htmlspecialchars($_SESSION['notification_count']) : '0'; ?>
            </span> <!-- Display notification count -->
        </a>
    </div>
</nav>

<!-- Your other content here -->

</body>
</html>