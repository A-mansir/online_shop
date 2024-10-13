<?php
session_start();
include 'header.php';
// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

$conn = new mysqli("localhost:8080", "root", "root", "shop_db");

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Query to fetch orders along with customer names, sorted by order_date in descending order
$query = "SELECT o.id, u.name AS customer_name, o.total_price AS total_amount, o.order_date 
          FROM orders o 
          JOIN users u ON o.user_id = u.id
          ORDER BY o.order_date DESC";  // Sorting by order_date in descending order

$result = $conn->query($query);

// Check for query errors
if (!$result) {
    die("Query failed: " . $conn->error);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <title>View Orders</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container">
        <h1>Order List</h1>
        <table class="table">
            <thead>
                <tr>
                    <th>Order ID</th>
                    <th>Customer Name</th>
                    <th>Order Date</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($row = $result->fetch_assoc()) { ?>
                    <tr>
                        <td><?php echo htmlspecialchars($row['id']); ?></td>
                        <td><?php echo htmlspecialchars($row['customer_name']); ?></td>
                        <td><?php echo htmlspecialchars($row['order_date']); ?></td>
                        <td><a href="view_order_details.php?id=<?php echo $row['id']; ?>" class="btn btn-info btn-sm">View Details</a></td>
                    </tr>
                <?php } ?>
            </tbody>
        </table>
    </div>
    <?php include 'footer.php'; ?>
</body>
</html>
