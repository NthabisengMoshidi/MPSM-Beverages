<?php
include('blueprint.php');

// Function to fetch a single value from the database with error handling
function fetchSingleValue($query, $conn) {
    $result = mysqli_query($conn, $query);
    if (!$result) {
        die("Query failed: " . mysqli_error($conn)); // Display error message if query fails
    }
    $row = mysqli_fetch_assoc($result);
    return $row ? array_values($row)[0] : 0; // Return the single value or 0 if no result
}

// Fetch analytics data
$totalCustomers = fetchSingleValue("SELECT COUNT(*) AS total_customers FROM users", $conn);
$totalAvailableProducts = fetchSingleValue("SELECT COUNT(*) AS available_products FROM products WHERE availabilityStatus = 'Available'", $conn);
$outOfStockProducts = fetchSingleValue("SELECT COUNT(*) AS out_of_stock FROM products WHERE stockLevel = 0", $conn);
$totalConfirmedOrders = fetchSingleValue("SELECT COUNT(*) AS confirmed_orders FROM orders WHERE status = 'Confirmed'", $conn);
$totalSales = fetchSingleValue("SELECT SUM(total_amount) AS total_sales FROM orders WHERE status = 'Confirmed'", $conn);

// Fetch sales trend data by month
$salesTrendQuery = "SELECT DATE_FORMAT(created_at, '%Y-%m') AS month, SUM(total_amount) AS monthly_sales
                    FROM orders
                    WHERE status = 'Confirmed'
                    GROUP BY month
                    ORDER BY month ASC";
$salesTrendResult = mysqli_query($conn, $salesTrendQuery);
if (!$salesTrendResult) {
    die("Sales trend query failed: " . mysqli_error($conn));
}

$months = [];
$monthlySales = [];
while ($row = mysqli_fetch_assoc($salesTrendResult)) {
    $months[] = $row['month'];
    $monthlySales[] = $row['monthly_sales'];
}

// Fetch top-selling products based on total revenue
$topProductsQuery = "SELECT product_name, SUM(total_price) AS total_revenue
                     FROM orders 
                     JOIN order_items ON orders.id = order_items.order_id
                     WHERE orders.status = 'Confirmed'
                     GROUP BY order_items.product_name
                     ORDER BY total_revenue DESC
                     LIMIT 10";

$topProductsResult = mysqli_query($conn, $topProductsQuery);
if (!$topProductsResult) {
    die("Top products query failed: " . mysqli_error($conn));
}

$topProducts = [];
while ($row = mysqli_fetch_assoc($topProductsResult)) {
    $topProducts[] = ['name' => htmlspecialchars($row['product_name']), 'revenue' => $row['total_revenue']];
}

// Fetching Order Status Distribution data
$orderStatusQuery = "SELECT status, COUNT(*) AS count FROM orders GROUP BY status";
$orderStatusResult = mysqli_query($conn, $orderStatusQuery);
if (!$orderStatusResult) {
    die("Order status query failed: " . mysqli_error($conn));
}

$orderStatuses = [];
$orderStatusCounts = [];
while ($row = mysqli_fetch_assoc($orderStatusResult)) {
    $orderStatuses[] = $row['status'];
    $orderStatusCounts[] = $row['count'];
}

// Fetching Revenue by Order Type data
$orderTypeQuery = "SELECT order_type, SUM(total_amount) AS revenue FROM orders WHERE status = 'Confirmed' GROUP BY order_type";
$orderTypeResult = mysqli_query($conn, $orderTypeQuery);
if (!$orderTypeResult) {
    die("Order type query failed: " . mysqli_error($conn));
}

$orderTypes = [];
$orderTypeRevenues = [];
while ($row = mysqli_fetch_assoc($orderTypeResult)) {
    $orderTypes[] = $row['order_type'];
    $orderTypeRevenues[] = $row['revenue'];
}

// Fetch total sales amount grouped by customer demographics location (city)
// Fetch the number of users grouped by their city
$cityUserCountQuery = "
    SELECT 
        users.city AS city, 
        COUNT(users.id) AS user_count
    FROM 
        users
    WHERE 
        users.city IN ('Johannesburg', 'Pretoria', 'Ekurhuleni', 'Tshwane', 
                       'Midrand', 'Soweto', 'Centurion', 'Benoni', 
                       'Brakpan', 'Randburg', 'Roodepoort', 'Other')
    GROUP BY 
        users.city
    ORDER BY 
        user_count DESC";

$cityUserCountResult = mysqli_query($conn, $cityUserCountQuery);
if (!$cityUserCountResult) {
    die("City user count query failed: " . mysqli_error($conn));
}

$cityUserCountData = [];
$cities = [];
$userCounts = [];
while ($row = mysqli_fetch_assoc($cityUserCountResult)) {
    $cities[] = htmlspecialchars($row['city']);
    $userCounts[] = (int)$row['user_count']; // Ensure user count is an integer
}


// Fetch active vs. non-active users data
$activeUsersQuery = "
    SELECT 
        DATE_FORMAT(created_at, '%Y-%m') AS month,
        SUM(CASE WHEN is_active = 1 THEN 1 ELSE 0 END) AS active_users,
        SUM(CASE WHEN is_active = 0 THEN 1 ELSE 0 END) AS non_active_users
    FROM 
        users
    GROUP BY 
        month
    ORDER BY 
        month ASC";

$activeUsersResult = mysqli_query($conn, $activeUsersQuery);
if (!$activeUsersResult) {
    die("Active vs non-active users query failed: " . mysqli_error($conn));
}

$months = [];
$activeUsersCounts = [];
$nonActiveUsersCounts = [];
while ($row = mysqli_fetch_assoc($activeUsersResult)) {
    $months[] = $row['month'];
    $activeUsersCounts[] = $row['active_users'];
    $nonActiveUsersCounts[] = $row['non_active_users'];
}


// Query to count active and non-active users
$activeQuery = "SELECT COUNT(*) as activeCount FROM users WHERE is_active = 1";
$nonActiveQuery = "SELECT COUNT(*) as nonActiveCount FROM users WHERE is_active = 0";

$activeResult = $conn->query($activeQuery);
$nonActiveResult = $conn->query($nonActiveQuery);

$activeUsersCount = $activeResult->fetch_assoc()['activeCount'];
$nonActiveUsersCount = $nonActiveResult->fetch_assoc()['nonActiveCount'];



// Renamed query to count users grouped by month
$userCountQuery = "
    SELECT DATE_FORMAT(created_at, '%Y-%m') AS month, COUNT(*) AS userCount
    FROM users
    GROUP BY month
    ORDER BY month;
";

$result = $conn->query($userCountQuery);

$months = [];
$userCounts = [];

if ($result->num_rows > 0) {
    // Fetch results and populate arrays
    while ($row = $result->fetch_assoc()) {
        $months[] = $row['month']; // e.g. '2024-01'
        $userCounts[] = $row['userCount'];
    }
} else {
    // No data available
    $months = [];
    $userCounts = [];
}

?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Analytics Summary</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    <style>
    body {
        font-family: Arial, sans-serif;
        margin-top: 100px;
        padding: 0;
        background-color: white;
        color: #333;
    }
    h1, h2 {
        text-align: center;
        color: #333;
        margin-top: 20px;
        margin-bottom: 40px;
    }
    .analytics-summary {
        display: flex;
        gap: 15px;
        justify-content: center;
        flex-wrap: wrap;
        margin-top: 70px;
        margin-bottom: 40px;
    }
    .analytics-card {
        width: 150px;
        padding: 15px;
        text-align: center;
        border-radius: 8px;
        box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        color: #ffffff;
    }
    .analytics-card i {
        font-size: 28px;
        margin-bottom: 8px;
    }
    .total-customers { background-color: #2980b9; }
    .available-products { background-color: #27ae60; }
    .out-of-stock { background-color: #e74c3c; }
    .confirmed-orders { background-color: #e67e22; }
    .total-sales { background-color: #3498db; }

    .analytics-charts {
        display: flex;
        flex-direction: column;
        gap: 30px;
        align-items: center;
        max-width: 1200px;
        margin: auto;
    }

    .chart-row {
        display: flex;
        justify-content: center;
        gap: 20px;
        flex-wrap: wrap;
    }
    .sales-overview{
        width: 650px;
        padding: 15px;
        background-color: #ffffff;
        border-radius: 8px;
        box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
        text-align: center;
    }

  .top-products, .chart-card {
        width: 300px;
        padding: 15px;
        background-color: #ffffff;
        border-radius: 8px;
        box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
        text-align: center;
    }

    .top-products ul {
        list-style-type: none;
        padding: 0;
        margin: 0;
    }
    .top-products li {
        padding: 8px;
        background-color: #f0f0f0;
        border: 1px solid #ddd;
        border-radius: 5px;
        margin-bottom: 8px;
        color: #333;
        display: flex;
        justify-content: space-between;
        align-items: center;
    }
    .top-products li i {
        color: #2980b9;
        margin-right: 8px;
    }
    .top-products li span.rank {
        font-weight: bold;
        color: #555;
    }
    .top-products li .fa-star {
        color: gold;
        margin-left: 5px;
    }

</style>

<h1>Analytics Dashboard</h1>
<h2>Analytical Summary</h2>

<div class="analytics-summary">
    <div class="analytics-card total-customers">
        <i class="fa fa-users"></i>
        <h3><?php echo $totalCustomers; ?></h3>
        <p>Total Customers</p>
    </div>
    <div class="analytics-card available-products">
        <i class="fa fa-box-open"></i>
        <h3><?php echo $totalAvailableProducts; ?></h3>
        <p>Available Products</p>
    </div>
    <div class="analytics-card out-of-stock">
        <i class="fa fa-times-circle"></i>
        <h3><?php echo $outOfStockProducts; ?></h3>
        <p>Out of Stock</p>
    </div>
    <div class="analytics-card confirmed-orders">
        <i class="fas fa-check-circle"></i>
        <h3><?php echo $totalConfirmedOrders; ?></h3>
        <p>Confirmed Orders</p>
    </div>
    <div class="analytics-card total-sales">
        <i class="fa fa-dollar-sign"></i>
        <h3><?php echo "R" . number_format($totalSales, 2); ?></h3>
        <p>Total Sales Revenue</p>
    </div>
</div>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sales Analytics</title>
    <link rel="stylesheet" href="path/to/your/styles.css"> <!-- Link to your CSS -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body>

<h2>Sales Analytics</h2>

<div class="analytics-charts">
    <div class="chart-row">
        <div class="sales-overview">
            <h2>Sales Trend</h2>
            <canvas id="salesTrendChart" width="250" height="150"></canvas>
        </div>
        <div class="top-products">
            <h2><i class="fas fa-trophy" style="color: gold;"></i> Top Selling Products</h2>
            <ul>
                <?php foreach (array_slice($topProducts, 0, 7) as $index => $product): ?>
                    <li>
                        <span class="rank"><?php echo $index + 1; ?>.</span>
                        <span><?php echo htmlspecialchars($product['name']); ?></span>
                        <span>R<?php echo number_format($product['revenue'], 2); ?></span>
                        <i class="fas fa-star"></i>
                    </li>
                <?php endforeach; ?>
            </ul>
        </div>
    </div>
    <h2>Order Analysis</h2>
    <div class="chart-row">
       
        <div class="chart-card">
            <h3>Order Status Distribution</h3>
            <canvas id="orderStatusChart" width="250" height="250"></canvas>
        </div>
        <div class="chart-card">
            <h3>Revenue by Order Type</h3>
            <canvas id="orderTypeChart" width="250" height="250"></canvas>
        </div>
    </div>
    <div class="chart-card" style="width: 50%;">
        <h2>Total Sales by City</h2>
        <canvas id="salesChart"></canvas>
    </div>
    <h1>User Analysis</h1>
    <div class="chart-row">
        <div class="chart-card">
            <h3>Active vs Non-Active Users</h3>
            <canvas id="userStatusChart" width="250" height="250"></canvas>
        </div>
        <div class="chart-card">
            <h3>Total Number of Users</h3>
            <canvas id="userCountChart" width="250" height="250"></canvas>
        </div>
    </div>
</div>

<script>
// Data for the sales trend chart
const months = <?php echo json_encode($months); ?>;
const monthlySales = <?php echo json_encode($monthlySales); ?>;

const salesTrendCtx = document.getElementById('salesTrendChart').getContext('2d');
const salesTrendChart = new Chart(salesTrendCtx, {
    type: 'line',
    data: {
        labels: months,
        datasets: [{
            label: 'Monthly Sales',
            data: monthlySales,
            borderColor: 'rgba(41, 128, 185, 1)',
            backgroundColor: 'rgba(41, 128, 185, 0.2)',
            borderWidth: 2,
            fill: true,
        }]
    },
    options: {
        responsive: true,
        scales: {
            y: {
                beginAtZero: true,
                title: {
                    display: true,
                    text: 'Sales Amount (R)',
                },
            },
        },
    },
});

// Data for the order status distribution chart
const orderStatuses = <?php echo json_encode($orderStatuses); ?>;
const orderStatusCounts = <?php echo json_encode($orderStatusCounts); ?>;

const orderStatusCtx = document.getElementById('orderStatusChart').getContext('2d');
const orderStatusChart = new Chart(orderStatusCtx, {
    type: 'pie',
    data: {
        labels: orderStatuses,
        datasets: [{
            label: 'Order Status Distribution',
            data: orderStatusCounts,
            backgroundColor: [
                'rgba(155, 89, 182, 0.5)',
                'rgba(52, 152, 219, 0.5)',
                'rgba(46, 204, 113, 0.5)',
                'rgba(241, 196, 15, 0.5)',
                'rgba(231, 76, 60, 0.5)',
            ],
            borderColor: 'rgba(0, 0, 0, 0.1)',
            borderWidth: 1,
        }]
    },
    options: {
        responsive: true,
        plugins: {
            legend: {
                position: 'top',
            },
            tooltip: {
                callbacks: {
                    label: function(tooltipItem) {
                        return tooltipItem.label + ': ' + tooltipItem.raw + ' orders';
                    }   
                }
            }
        }
    },
});

// Data for the revenue by order type chart
const orderTypes = <?php echo json_encode($orderTypes); ?>;
const orderTypeRevenues = <?php echo json_encode($orderTypeRevenues); ?>;

const orderTypeCtx = document.getElementById('orderTypeChart').getContext('2d');
const orderTypeChart = new Chart(orderTypeCtx, {
    type: 'pie',
    data: {
        labels: orderTypes,
        datasets: [{
            label: 'Revenue by Order Type',
            data: orderTypeRevenues,
            backgroundColor: [
                '#FF69B4',
                '#1E90FF',
                '#FF69B4',
                '#1E90FF',
                '#FF69B4',
                '#1E90FF',
            ],
            borderColor: '#FFFFFF',
            borderWidth: 1,
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
                        return `${tooltipItem.label}: R${tooltipItem.raw.toFixed(2)}`;
                    }
                }
            },
        },
    },
});

// Data for the total sales by city chart
const cities = <?php echo json_encode($cities); ?>;
const userCounts = <?php echo json_encode($userCounts); ?>;

const colors = [
    'rgba(52, 152, 219, 0.5)',
    'rgba(231, 76, 60, 0.5)',
    'rgba(46, 204, 113, 0.5)',
    'rgba(155, 89, 182, 0.5)',
    'rgba(241, 196, 15, 0.5)',
    'rgba(41, 128, 185, 0.5)',
    'rgba(26, 188, 156, 0.5)',
    'rgba(243, 156, 18, 0.5)',
    'rgba(52, 73, 94, 0.5)',
    'rgba(189, 195, 199, 0.5)',
];

const datasets = [{
    label: 'Number of Users',
    data: userCounts,
    backgroundColor: colors.slice(0, cities.length),
    borderColor: colors.slice(0, cities.length).map(color => color.replace('0.5', '1')),
    borderWidth: 1,
}];

const salesChartCtx = document.getElementById('salesChart').getContext('2d');
const salesChart = new Chart(salesChartCtx, {
    type: 'bar',
    data: {
        labels: cities,
        datasets: datasets,
    },
    options: {
        responsive: true,
        scales: {
            y: {
                beginAtZero: true,
                title: {
                    display: true,
                    text: 'User Count',
                },
                ticks: {
                    stepSize: 1,
                },
            },
        },
    },
});

// User status chart
const activeUsersCount = <?php echo $activeUsersCount; ?>;
const nonActiveUsersCount = <?php echo $nonActiveUsersCount; ?>;

const userStatusCtx = document.getElementById('userStatusChart').getContext('2d');
const userStatusChart = new Chart(userStatusCtx, {
    type: 'pie',
    data: {
        labels: ['Active Users', 'Non-Active Users'],
        datasets: [{
            data: [activeUsersCount, nonActiveUsersCount],
            backgroundColor: [
                'rgba(76, 175, 80, 0.6)', // Active Users color
                'rgba(244, 67, 54, 0.6)'  // Non-Active Users color
            ],
            borderColor: [
                'rgba(76, 175, 80, 1)', // Active Users border color
                'rgba(244, 67, 54, 1)'  // Non-Active Users border color
            ],
            borderWidth: 1
        }]
    },
    options: {
        responsive: true,
        plugins: {
            legend: {
                position: 'top',
            },
            tooltip: {
                callbacks: {
                    label: function(tooltipItem) {
                        return tooltipItem.label + ': ' + tooltipItem.raw;
                    }
                }
            }
        }
    }
});

// User count chart
const userCountMonths = <?php echo json_encode($months); ?>;
const userCountsData = <?php echo json_encode($userCounts); ?>;

const userCountCtx = document.getElementById('userCountChart').getContext('2d');

if (userCountMonths.length > 0 && userCountsData.length > 0) {
    const userCountChart = new Chart(userCountCtx, {
        type: 'line',
        data: {
            labels: userCountMonths,
            datasets: [{
                label: 'Number of Users',
                data: userCountsData,
                fill: false,
                borderColor: 'rgba(76, 175, 80, 1)', // Line color
                backgroundColor: 'rgba(76, 175, 80, 0.2)', // Fill color below the line
                borderWidth: 2,
                pointRadius: 4,
                pointBackgroundColor: 'rgba(76, 175, 80, 1)', // Point color
                pointBorderColor: '#fff',
            }]
        },
        options: {
            responsive: true,
            scales: {
                x: {
                    title: {
                        display: true,
                        text: 'Month'
                    }
                },
                y: {
                    title: {
                        display: true,
                        text: 'Number of Users'
                    },
                    beginAtZero: true
                }
            },
            plugins: {
                legend: {
                    position: 'top',
                },
                tooltip: {
                    callbacks: {
                        label: function(tooltipItem) {
                            return `${tooltipItem.dataset.label}: ${tooltipItem.raw}`;
                        }
                    }
                }
            }
        }
    });
} else {
    console.error("Data for user count chart is missing or invalid.");
}
</script>

</body>
</html>
