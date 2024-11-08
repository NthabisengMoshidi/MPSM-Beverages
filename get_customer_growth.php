<?php
include 'db_connection.php'; // Include the database connection

// Query to get monthly user counts
$sql = "SELECT DATE_FORMAT(created_at, '%M %Y') AS creation_month, COUNT(*) AS user_count
        FROM users
        GROUP BY creation_month
        ORDER BY MIN(created_at)";


$result = $conn->query($sql);

$labels = [];
$data = [];

if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $labels[] = $row['creation_month']; // Use month format
        $data[] = $row['user_count'];
    }
}

// Return JSON response
echo json_encode(['labels' => $labels, 'data' => $data]);

$conn->close();
?>
