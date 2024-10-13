<?php
session_start();
$conn = new mysqli("localhost:8080", "root", "root", "shop_db");
include 'header.php';

// Initialize the cart if it doesn't exist
if (!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = array();
}

// Add items to the cart
if (isset($_GET['id'])) {
    $id = intval($_GET['id']); // Ensure the ID is an integer
    // Add product to cart if not already added
    if (!isset($_SESSION['cart'][$id])) {
        $_SESSION['cart'][$id] = 1; // Set quantity to 1
    }
}

// Remove items from the cart
if (isset($_GET['remove'])) {
    $removeId = intval($_GET['remove']);
    unset($_SESSION['cart'][$removeId]);
}

// Update item quantity in the cart
if (isset($_GET['update'])) {
    $updateId = intval($_GET['update']);
    $quantity = intval($_GET['quantity']);
    if ($quantity <= 0) {
        unset($_SESSION['cart'][$updateId]); // Remove the item if quantity is zero or less
    } else {
        $_SESSION['cart'][$updateId] = $quantity; // Update the quantity
    }
}

// Calculate total price
$total_price = 0;
foreach ($_SESSION['cart'] as $id => $quantity) {
    $result = $conn->query("SELECT * FROM products WHERE id=$id");
    if ($result) {
        $product = $result->fetch_assoc();
        if ($product) { // Check if product exists
            $total_price += $product['price'] * $quantity;
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <title>Shopping Cart</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .card {
            margin: 10px;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1 class="mt-4">Shopping Cart</h1>
        <table class="table table-bordered">
            <thead>
                <tr>
                    <th>Product</th>
                    <th>Quantity</th>
                    <th>Price</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($_SESSION['cart'] as $id => $quantity) {
                    $result = $conn->query("SELECT * FROM products WHERE id=$id");
                    if ($result) {
                        $product = $result->fetch_assoc();
                        if ($product) { // Check if product exists
                            ?>
                            <tr>
                                <td><?php echo htmlspecialchars($product['name']); ?></td>
                                <td>
                                    <form method="GET" action="">
                                        <input type="hidden" name="update" value="<?php echo $id; ?>">
                                        <input type="hidden" name="quantity" value="<?php echo $quantity; ?>">
                                        <button type="button" class="btn btn-secondary btn-sm" onclick="changeQuantity(<?php echo $id; ?>, -1)">-</button>
                                        <span id="quantity_<?php echo $id; ?>"><?php echo $quantity; ?></span>
                                        <button type="button" class="btn btn-secondary btn-sm" onclick="changeQuantity(<?php echo $id; ?>, 1)">+</button>
                                    </form>
                                </td>
                                <td>NGN <?php echo number_format($product['price'], 2); ?></td>
                                <td>
                                    <a href="?remove=<?php echo $id; ?>" class="btn btn-danger btn-sm">Remove</a>
                                </td>
                            </tr>
                            <?php
                        }
                    }
                } ?>
            </tbody>
        </table>
        <h4>Total Price: NGN <?php echo number_format($total_price, 2); ?></h4>
        <a href="checkout.php" class="btn btn-success">Proceed to Checkout</a>
        <a href="index.php" class="btn btn-success">Continue Shopping</a>
    </div>

    <script>
        function changeQuantity(id, delta) {
            const quantitySpan = document.getElementById(`quantity_${id}`);
            let currentQuantity = parseInt(quantitySpan.textContent);

            currentQuantity += delta;
            if (currentQuantity < 0) currentQuantity = 0; // Prevent negative quantity

            quantitySpan.textContent = currentQuantity; // Update the displayed quantity

            // Submit the form to update the quantity
            const form = quantitySpan.closest('form');
            form.querySelector('input[name="quantity"]').value = currentQuantity; // Set the new quantity

            // Submit the form
            form.submit();
        }
    </script>
</body>
</html>
