<?php
include 'blueprint.php'; // Include the database connection


// Query to count the total number of products
$sql = "SELECT COUNT(*) AS total_products FROM products";
$result = $conn->query($sql);
$totalProducts = 0; // Default value

if ($result->num_rows > 0) {
    $row = $result->fetch_assoc();
    $totalProducts = $row['total_products'];
}

// Query to get products that are either out of stock or below the restock level
$sql = "SELECT ItemID, productName, stockLevel, restockLevel, (restockLevel - stockLevel) AS stockDiff 
        FROM products 
        WHERE stockLevel = 0 OR stockLevel < restockLevel";

        // Query to count  total number of distinct product types i.e. unique SKUs
$sql_sku = "SELECT COUNT(DISTINCT SKU) AS unique_sku_count FROM products";
$result_sku = $conn->query($sql_sku);
$uniqueSkuCount = 0; // Default value

if ($result_sku->num_rows > 0) {
    $row_sku = $result_sku->fetch_assoc();
    $uniqueSkuCount = $row_sku['unique_sku_count'];
}

// Query to count total customers
$sql_customers = "SELECT COUNT(*) AS total_customers FROM users";
$result_customers = $conn->query($sql_customers);
$totalCustomers = 0; // Default value

if ($result_customers->num_rows > 0) {
    $row_customers = $result_customers->fetch_assoc();
    $totalCustomers = $row_customers['total_customers'];
}

$result = $conn->query($sql);

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
        /*  background image and theme colors */
        body {
            background-image: url('/Prototype Drafts/Admin Prototype/bg2.jpg');
            background-size: cover;
            background-position: center;
            background-repeat: no-repeat;
            background-attachment: fixed;
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
        }
    
        /* Sidebar */
        .sidebar {
            width: 250px;
            height: 100vh;
            position: fixed;
            top: 0;
            left: 0;
            background: white; /* Changed to white */
            color: black; /* Changed font color to black */
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
            color: black; /* Changed font color to black */
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
            background-color: rgba(200, 200, 200, 0.6); /* Updated hover effect */
            cursor: pointer;
            border-radius: 50px;
        }
    
        .sub-menu {
            display: none;
            position: absolute;
            left: 100%;
            top: 0;
            background: white; /* Ensure sub-menu background is white */
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
            color: black; /* Changed font color to black */
            text-decoration: none;
            display: block;
            border-radius: 5px; /* Ensure sub-menu items have rounded corners */
            position: relative; /* Ensure the pseudo-element is positioned correctly */
        }
    
        .sub-menu li a::after {
            content: '';
            display: block;
            width: 100%;
            height: 2px;
            background: orange; /* Orange underline */
            position: absolute;
            left: 0;
            bottom: 0;
            transform: scaleX(0); /* Start hidden */
            transition: transform 0.3s ease; /* Smooth transition */
        }
    
        .sub-menu li a:hover::after {
            transform: scaleX(1); /* Show underline on hover */
        }
    
        .sub-menu li a:hover {
            background-color: rgba(255, 165, 0, 0.3); /* Optional background change on hover */
        }
    
        /* Navigation bar styles */
        nav {
            background: white; /* Changed to white */
            color: black; /* Changed font color to black */
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
            border: 1px solid #ccc; /* Added border for clarity */
            outline: none;
            width: 200px;
            margin-right: 15px;
        }
    
        .nav-right i {
            color: black; /* Changed icon color to black */
            font-size: 30px;
            margin-left: 15px;
            cursor: pointer;
            transition: color 0.3s ease;
        }
    
        .nav-right i:hover {
            color: #2980b9;
        }
/*----------Nav left-----------------*/

    .nav-left{
        display: flex;
            align-items: center;
    }
    .nav-left i {
            font-size: 24px; /* Larger icon size */
            color: black;
            margin: 0 10px;
            position: relative;
        }

        .nav-left a {
    position: relative; /* Positioning context for the badge */
}

.notification-badge {
    position: absolute;
    top: -8px; /* Adjust as needed */
    right: -8px; /* Adjust as needed */
    background-color: red; /* Badge background color */
    color: white; /* Badge text color */
    border-radius: 50%; /* Make it round */
    padding: 3px 7px; /* Some padding */
    font-size: 12px; /* Adjust font size */
}

         /* Style for notification badge */
         .nav-left i .notification-badge {
            position: absolute;
            top: -8px;
            right: -8px;
            background-color: red;
            color: white;
            border-radius: 50%;
            padding: 3px 7px;
            font-size: 12px;
        }


        /* Hover effect for icons */
        .nav-left i:hover {
            color: #2565AE;
            cursor: pointer;
        }

        /* For notifications scroll */
        .notification-section {
            margin-top: 100px;
            padding: 20px;
        }

        .highlight {
            background-color: #f9f9f9;
            border: 1px solid #ccc;
            padding: 10px;
            border-radius: 5px;
        }
        /* Main Content Overlay */
        .main-content {
            margin-left: 270px;
            padding: 70px;
            background-color: rgba(255, 255, 255, 0.9);
            border: outset 2px orange;
            border-radius: 10px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            margin-top: 100px;
            margin-bottom: 100px;
            width: calc(100% - 500px);
        }
    
        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
        }
    
        .stats-cards {
            display: flex;
            flex-wrap: wrap; /* Ensures responsiveness on smaller screens */
            gap: 20px;
            justify-content: center; /* Center the buttons */
        }
    
        .card {
            background-color: white;
            padding: 20px;
            border-radius: 8px;
            width: 150px;
            text-align: center;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
        }
    
        .card i {
            border: 2px solid;
            border-radius: 50%;
            padding: 10px;
            margin-bottom: 10px;
            font-size: 24px;
        }
    
        .card .total-products i { color: #2980b9; border-color: #2980b9; }
        .card .orders i { color: #e67e22; border-color: #e67e22; }
        .card .total-stock i { color: #27ae60; border-color: #27ae60; }
        .card .out-of-stock i { color: #e74c3c; border-color: #e74c3c; }
    
        .charts-section {
            display: flex;
            gap: 20px;
            margin-top: 40px;
        }
    
        .chart {
            width: 48%;
            height: 400px;
            background-color: white;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
            position: relative;
            overflow: hidden;
        }
    
        .chart h4 {
            margin-bottom: 20px;
            font-size: 18px;
        }
    
        canvas {
            max-width: 100%;
            max-height: 300px;
        }
    
       /* Notifications Section */
       .notifications {
            background-color: #f9f9f9;
            padding: 20px;
            border: 1px solid #ddd;
            border-radius: 5px;
            margin: 20px 0;
        }

        .notification-section {
            margin-top: 10px;
        }

        .notification-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }

        .notification-table th,
        .notification-table td {
            border: 1px solid #ccc;
            padding: 10px;
            text-align: left;
        }

        .notification-table th {
            background-color: #f2f2f2;
            font-weight: bold;
        }

        .notification-table tr:nth-child(even) {
            background-color: #f9f9f9;
        }

        .notification-table tr:hover {
            background-color: #f1f1f1;
        }

        .notification-table td {
            font-size: 14px;
            color: #333;
        }

        /* Styling for the condition column */
        .notification-table td:first-child {
            font-weight: bold;
            color: #e74c3c;
        }

        /* Notification controls */
        .notification-controls {
            display: flex;
            align-items: center;
            margin-bottom: 15px;
        }

        .notification-controls .search-bar {
            padding: 10px;
            border-radius: 20px;
            border: 1px solid #ccc;
            outline: none;
            margin-right: 15px;
            flex: 1;
        }

        .delete-button {
            background-color: #e74c3c;
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 5px;
            cursor: pointer;
            transition: background-color 0.3s ease;
            margin-top: 20px;
        }

        .delete-button:hover {
            background-color: #c0392b;
        }

/*-----Quick Actions-----------*/
.quick-actions {
    display: flex;
    justify-content: center; /* Center the buttons */
    gap: 10px; /* Decrease space between buttons */
    flex-wrap: wrap; /* Allows wrapping on smaller screens */
}

.quick-action {
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 10px 20px;
    margin: 5px;
    background-color: #27ae60;
    color: white;
    text-decoration: none;
    border-radius: 5px;
}

/* Smaller button size for quick actions */
.small-btn {
    padding: 8px 12px;
    font-size: 0.9rem; /* Smaller text */
}

/* Green buttons */
.green-btn {
    background-color: #2ecc71;
}

/* Optional: Add hover effects */
.quick-action:hover {
    background-color:#3acc2e;
    transition: background-color 0.3s;
}
    </style>
    
</head>

<body>
   
    <div class="main-content">
        <!-- Header -->
        <div class="header" id="overview">
            <h1>Dashboard</h1>
            <p>Welcome Administrator!</p>
    </div>
    
      <!-- Stats Cards -->
<section class="stats-cards">
    <div class="card total-products" style="background-color: #3498db; color: white;">
        <i class="fas fa-box"></i>
        <h3>Total Products</h3>
        <p><?php echo $totalProducts; ?></p> <!-- Display the total products count -->
    </div>
    <div class="card orders" style="background-color: #e67e22; color: white;">
        <i class="fas fa-shopping-cart"></i>
        <h3>Orders</h3>
        <p>2859</p>
    </div>
    <div class="card total-customers" style="background-color: #e74c3c; color: white;">
        <i class="fas fa-users"></i>
        <h3>Total Customers</h3>
        <p><?php echo $totalCustomers; ?></p> <!-- Display total customers count -->
    </div>
    <div class="card unique-skus" style="background-color: #2ecc71; color: white;">
        <i class="fas fa-tag"></i>
        <h3>Unique SKUs</h3>
        <p><?php echo $uniqueSkuCount; ?></p> <!-- Display unique SKU count -->
    </div>
</section>

<!-- Add some spacing between the stats and quick actions -->
<br><br>

<!--Quick Actions-->
<div class="header">
    <h2>Quick Actions</h2>
</div>

<div class="quick-actions">
    <a href="products.php" class="quick-action small-btn">
        <i class="fas fa-plus"></i> Add Products
    </a>
    <a href="ordermanagement.php" class="quick-action small-btn">
        <i class="fas fa-shopping-cart"></i> View Orders
    </a>
    <a href="customer_management.php" class="quick-action small-btn">
        <i class="fas fa-users"></i> Customer Management
    </a>
    <a href="loginpage.php" class="quick-action logout small-btn">
        <i class="fas fa-sign-out-alt"></i> Log Out
    </a>
</div>
        <!-- Analytical Summary Section -->
        <section id="analytical-summary">
            <h2>Analytical Summary</h2>
            <section class="charts-section">
                <div class="chart">
                    <h4>Inventory Values</h4>
                    <canvas id="inventoryChart"></canvas>
                </div>
                <div class="chart">
                    <h4>Top Sales</h4>
                    <canvas id="salesChart"></canvas>
                </div>
            </section>
            <section class="charts-section">
                <div class="chart">
                    <h4>Expenses vs Profit</h4>
                    <canvas id="expenseProfitChart"></canvas>
                </div>
            </section>
            <h2>Analytical Summary</h2>
    <section class="charts-section">
        <div class="chart">
            <h4>Daily Customer Growth</h4>
            <canvas id="customerGrowthChart"></canvas>
        </div>
    </section>

  <!-- ---------Notifications Section ---------------->
        <div class="notifications"  id="notifications">
    <h2>Alerts</h2>
    <p> This is the section for low product alerts, that need to ne attended to urgently </p>
    <!-- Controls for filtering and searching -->
    <div class="notification-controls">
        <input type="text" class="search-bar" placeholder="Search by Item ID or Product Name" id="searchInput">
        <button class="delete-button" id="deleteButton">Delete Selected</button>
    </div>

    <div class="notification-section">
    <?php
    // Fetch notifications from the database (assuming $result already contains the fetched rows)
    $notificationsCount = 0;

    if ($result->num_rows > 0): ?>
        <!-- Count the number of notifications -->
        <?php $notificationsCount = $result->num_rows; ?>

        <!-- Display table for notifications -->
        <table class="notification-table" id="notificationsTable">
            <thead>
                <tr>
                    <th>Select</th>
                    <th>Condition</th>
                    <th>Item ID</th>
                    <th>Product Name</th>
                    <th>Stock Deficit (if applicable)</th>
                </tr>
            </thead>
            <tbody id="notificationBody">
                <?php while ($row = $result->fetch_assoc()): ?>
                    <tr>
                        <!-- Checkbox for selecting rows -->
                        <td>
                            <input type="checkbox" class="row-select" value="<?php echo htmlspecialchars($row['ItemID']); ?>">
                        </td>
                        <!-- Display notification condition based on stock level -->
                        <td>
                            <?php if ($row["stockLevel"] == 0): ?>
                                Out of Stock
                            <?php else: ?>
                                Low Stock (Below Restock Level)
                            <?php endif; ?>
                        </td>
                        <td><?php echo htmlspecialchars($row["ItemID"]); ?></td>
                        <td><?php echo htmlspecialchars($row["productName"]); ?></td>
                        <!-- If stock level is below restock, show stock deficit -->
                        <td>
                            <?php if ($row["stockLevel"] < $row["restockLevel"]): ?>
                                <?php echo htmlspecialchars($row["stockDiff"]); ?> units
                            <?php else: ?>
                                N/A
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    <?php else: ?>
        <p>No notifications at the moment.</p>
    <?php endif; ?>
</div>
</body>

<!-- Footer -->
<footer>
    &copy; 2024 Inventory Management System | All rights reserved
</footer>

<!-- Pass the notification count to JavaScript -->
<script>
    // Store the notification count from PHP in a JavaScript variable
    var notificationsCount = <?php echo json_encode($notificationsCount); ?>;
    
    // Update the notification bell with the count
    document.getElementById('notificationsCount').textContent = notificationsCount;
</script>

<!-- jQuery Document Ready -->
<script>
$(document).ready(function () {
    //--------------Notification Section------------------------
    
    // Search functionality for notifications
    $('#searchInput').on('input', function () {
        const searchTerm = $(this).val().toLowerCase();
        $('#notificationBody tr').each(function () {
            const itemID = $(this).find('td:nth-child(3)').text().toLowerCase();
            const productName = $(this).find('td:nth-child(4)').text().toLowerCase();

            // Show or hide rows based on search input
            $(this).toggle(itemID.includes(searchTerm) || productName.includes(searchTerm));
        });
    });

    // Handle deleting selected notifications (UI only)
    $('#deleteButton').click(function () {
        const selectedCheckboxes = $('.row-select:checked');
        if (selectedCheckboxes.length === 0) {
            Swal.fire('Error!', 'Please select at least one notification to delete.', 'error');
            return;
        }

        Swal.fire({
            title: 'Are you sure?',
            text: "You are about to remove selected notifications!",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Yes, delete them!'
        }).then((result) => {
            if (result.isConfirmed) {
                selectedCheckboxes.each(function () {
                    $(this).closest('tr').remove();
                });
                Swal.fire('Deleted!', 'Selected notifications have been removed.', 'success');
            }
        });
    });

    // Notification bell click functionality
    $('#notificationIcon').click(function (e) {
        e.preventDefault(); // Prevent the default link behavior
        // Animate scrolling to the notifications section
        $('html, body').animate({
            scrollTop: $("#notification-section").offset().top // Scroll to the top of the notifications section
        }, 500); // Duration of the scroll animation in milliseconds
    });

    // Highlight search term in the notifications section (if needed)
    $('#notification-section').removeClass('highlight'); // Remove highlight from notifications section
    const searchTerm = $('#searchInput').val().toLowerCase();
    if (searchTerm && $('#notification-section').text().toLowerCase().includes(searchTerm)) {
        $('#notification-section').addClass('highlight'); // Add highlight if the term is found
    }

    // ---------------------------------------- Chart Section -----------------------------------------------------
    // Inventory Chart
    const ctx1 = document.getElementById('inventoryChart').getContext('2d');
    const inventoryChart = new Chart(ctx1, {
        type: 'doughnut',
        data: {
            labels: ['Water', 'Juices', 'Custom Beverages', 'Distillation Equipment'],
            datasets: [{
                label: 'Inventory Values',
                data: [5000, 3000, 1500, 7000],
                backgroundColor: [
                    'rgba(75, 192, 192, 0.6)',
                    'rgba(153, 102, 255, 0.6)',
                    'rgba(255, 159, 64, 0.6)',
                    'rgba(255, 99, 132, 0.6)'
                ]
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false
        }
    });

    // Sales Chart
    const ctx2 = document.getElementById('salesChart').getContext('2d');
    const salesChart = new Chart(ctx2, {
        type: 'bar',
        data: {
            labels: ['January', 'February', 'March', 'April', 'May'],
            datasets: [{
                label: 'Top Sales',
                data: [12000, 15000, 14000, 13000, 16000],
                backgroundColor: 'rgba(54, 162, 235, 0.6)',
                borderColor: 'rgba(54, 162, 235, 1)',
                borderWidth: 1
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false
        }
    });

    // Expense vs Profit Chart
    const ctx3 = document.getElementById('expenseProfitChart').getContext('2d');
    const expenseProfitChart = new Chart(ctx3, {
        type: 'line',
        data: {
            labels: ['Dec', 'Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun'],
            datasets: [{
                label: 'Expenses',
                data: [30000, 40000, 35000, 45000, 50000, 55000, 60000],
                borderColor: 'red',
                fill: false
            }, {
                label: 'Profit',
                data: [10000, 15000, 20000, 25000, 30000, 35000, 40000],
                borderColor: 'green',
                fill: false
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false
        }
    });

    // Sales Chart - Fetch Customer Growth Data
    async function fetchCustomerGrowthData() {
        const response = await fetch('get_customer_growth.php');
        return await response.json(); // Return the data directly
    }

    // Create Customer Growth Chart
    async function createChart() {
        const { labels, data } = await fetchCustomerGrowthData();
        const ctx = document.getElementById('customerGrowthChart').getContext('2d');

        const customerGrowthChart = new Chart(ctx, {
            type: 'line',
            data: {
                labels: labels,
                datasets: [{
                    label: 'Monthly Customers',
                    data: data,
                    borderColor: 'rgba(75, 192, 192, 1)',
                    backgroundColor: 'rgba(75, 192, 192, 0.2)',
                    borderWidth: 2,
                    fill: true,
                    pointStyle: 'circle',
                    pointRadius: 5,
                    pointHoverRadius: 7,
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: {
                        display: true,
                        position: 'top',
                    },
                    tooltip: {
                        callbacks: {
                            label: function(tooltipItem) {
                                return `Customers: ${tooltipItem.raw}`;
                            }
                        }
                    }
                },
                scales: {
                    x: {
                        title: {
                            display: true,
                            text: 'Month'
                        },
                        grid: {
                            display: false
                        }
                    },
                    y: {
                        title: {
                            display: true,
                            text: 'Number of Customers'
                        },
                        beginAtZero: true,
                        ticks: {
                            stepSize: 1 // Ensures counts increment by 1
                        },
                        grid: {
                            color: 'rgba(200, 200, 200, 0.5)'
                        }
                    }
                }
            }
        });
    }

    // Call the createChart function on page load
    createChart();

    // -------------------------------------------- Search bar functionality -------------------------------------
    $('#searchBar').on('input', function () {
        const searchTerm = $(this).val().toLowerCase();
        
        // Filter the items on the page based on the search term
        $('.main-content *').each(function () {
            const content = $(this).text().toLowerCase();
            $(this).toggle(content.includes(searchTerm));
        });
    });

    // Quick action for logout confirmation
    $('.quick-action.logout').on('click', function (e) {
        e.preventDefault();
        Swal.fire({
            title: 'Are you sure you want to log out?',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Yes, log out!',
            cancelButtonText: 'No, stay logged in'
        }).then((result) => {
            if (result.isConfirmed) {
                window.location.href = 'login_page.php'; // Redirect to login page
            }
        });
    });
});
</script>


</html>
