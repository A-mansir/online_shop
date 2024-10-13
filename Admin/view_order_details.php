<?php
session_start();
include 'header.php';

// Connect to the database
$conn = new mysqli("localhost:8080", "root", "root", "shop_db", 3306); // Corrected the connection parameters

// Check for connection errors
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Get the order ID from the URL
$order_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

// Fetch the order details using a prepared statement
$stmt = $conn->prepare("
    SELECT o.id, o.total_price AS total_amount, o.address, o.phone_number, o.order_date, u.name AS customer_name 
    FROM orders o 
    JOIN users u ON o.user_id = u.id 
    WHERE o.id = ?
");
$stmt->bind_param("i", $order_id);
$stmt->execute();
$order_result = $stmt->get_result();

// Check if the order exists
$order = $order_result->fetch_assoc();
if (!$order) {
    die("Order not found with ID: $order_id");
}

// Fetch the order items using a prepared statement
$order_items_query = "
    SELECT oi.product_id, oi.quantity, p.name AS product_name, p.price 
    FROM order_items oi
    JOIN products p ON oi.product_id = p.id
    WHERE oi.order_id = ?
";
$order_items_stmt = $conn->prepare($order_items_query);
$order_items_stmt->bind_param("i", $order_id);
$order_items_stmt->execute();
$order_items_result = $order_items_stmt->get_result();

// Initialize total amount
$total_amount = 0;
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <title>Order Details</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container mt-4">
        <h1>Order Details</h1>
        <h5>Order ID: <?php echo htmlspecialchars($order['id']); ?></h5>
        <h5>Customer Name: <?php echo htmlspecialchars($order['customer_name']); ?></h5>
        <h5>Customer Address: <?php echo htmlspecialchars($order['address'] ?? 'N/A'); ?></h5>
        <h5>Phone Number: <?php echo htmlspecialchars($order['phone_number'] ?? 'N/A'); ?></h5>
        <h5>Order Date: <?php echo htmlspecialchars($order['order_date']); ?></h5>

        <h2 class="mt-4">Order Items</h2>
        <table class="table">
            <thead>
                <tr>
                    <th>Product Name</th>
                    <th>Price</th>
                    <th>Quantity</th>
                    <th>Subtotal</th>
                </tr>
            </thead>
            <tbody>
                <?php 
                // Check if there are ordered items
                if ($order_items_result->num_rows > 0) {
                    while ($item = $order_items_result->fetch_assoc()) {
                        $subtotal = $item['price'] * $item['quantity'];
                        $total_amount += $subtotal;
                ?>
                <tr>
                    <td><?php echo htmlspecialchars($item['product_name']); ?></td>
                    <td>NGN <?php echo number_format($item['price'], 2); ?></td>
                    <td><?php echo $item['quantity']; ?></td>
                    <td>NGN <?php echo number_format($subtotal, 2); ?></td>
                </tr>
                <?php 
                    } 
                } else {
                    echo "<tr><td colspan='4'>No items found for this order.</td></tr>";
                }
                ?>
            </tbody>
        </table>

        <h4 class="mt-4">Grand Total: NGN <?php echo number_format($total_amount, 2); ?></h4>
        <a href="index.php" class="btn btn-primary mt-3">Back to Orders</a>
    </div>
    <?php include 'footer.php'; ?>
</body>
</html>

<?php
// Close connections
$stmt->close();
$order_items_stmt->close();
$conn->close();
?>
