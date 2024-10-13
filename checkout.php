<?php
session_start();

// Corrected MySQL port from 8080 to 3306 (or remove the port entirely)
$conn = new mysqli("localhost:8080", "root", "root", "shop_db");

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

// Fetch the Pay on Delivery setting securely
$setting_result = $conn->query("SELECT pay_on_delivery_enabled FROM settings WHERE id = 1");
$setting = $setting_result->fetch_assoc();

$total_price = 0;
$cart = $_SESSION['cart'] ?? [];

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['payment_method'])) {
    $payment_method = $_POST['payment_method'];
    
    if ($payment_method === 'pay_on_delivery') {
        $user_id = $_SESSION['user_id'];
        $total_price = (float)$_POST['total_price'];

        foreach ($cart as $product_id => $quantity) {
            $product_id = (int)$product_id; // Sanitize input
            $quantity = (int)$quantity;     // Sanitize input
            $conn->query("INSERT INTO orders (user_id, product_id, quantity, total_price, payment_method, order_status) 
    VALUES ($user_id, $product_id, $quantity, $total_price, 'pay_on_delivery', 'pending')");

        }

        // Clear cart after placing the order
        unset($_SESSION['cart']);
        echo "Your order has been placed. Please prepare for payment on delivery.";
    }
}

// Calculate total price and fetch user details
foreach ($cart as $id => $quantity) {
    $id = (int)$id; // Sanitize input
    $result = $conn->query("SELECT * FROM products WHERE id=$id");
    $product = $result->fetch_assoc();
    $total_price += $product['price'] * $quantity;
}

$user_id = $_SESSION['user_id'];
$user_result = $conn->query("SELECT * FROM users WHERE id=$user_id");
$user = $user_result->fetch_assoc();
$email = $user['email'];

// Replace with your actual Paystack and Opay keys
$paystack_public_key = "pk_test_d50645fed5bc02b89e765feaa9d1744a3faaaaab";
$opay_public_key = "YOUR_OPAY_PUBLIC_KEY";
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Checkout</title>
    <script src="https://js.paystack.co/v1/inline.js"></script>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background-color: #f8f9fa;
            font-family: 'Roboto', sans-serif;
        }
        .checkout-container {
            margin-top: 50px;
            padding: 30px;
            background-color: #ffffff;
            border-radius: 12px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1);
        }
        .total-price {
            font-size: 1.8rem;
            font-weight: bold;
            color: #28a745;
        }
        .btn-pay {
            background-color: #007bff;
            color: white;
            transition: background-color 0.3s ease;
        }
        .btn-pay:hover {
            background-color: #0056b3;
        }
        .summary-item {
            border-bottom: 1px solid #dee2e6;
            padding: 10px 0;
        }
        .summary-item:last-child {
            border-bottom: none;
        }
    </style>
</head>
<body>
    <?php include 'header.php'; ?>

    <div class="container checkout-container">
        <h1 class="text-center mb-4">Checkout</h1>
        <div class="mb-4">
            <h5>Total Price: <span class="total-price">NGN <?php echo number_format($total_price, 2); ?></span></h5>
        </div>

        <div class="mb-4">
            <h5 class="text-muted">Order Summary</h5>
            <?php foreach ($cart as $id => $quantity): 
                $id = (int)$id; // Sanitize input
                $result = $conn->query("SELECT * FROM products WHERE id=$id");
                $product = $result->fetch_assoc(); ?>
                <div class="summary-item">
                    <div class="d-flex justify-content-between">
                        <span><?php echo htmlspecialchars($product['name']); ?> (<?php echo $quantity; ?>)</span>
                        <span>NGN <?php echo number_format($product['price'] * $quantity, 2); ?></span>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>

        <div class="mb-4">
            <h5>Select Payment Method</h5>
            <div>
                <input type="radio" id="paystack" name="payment_method" value="paystack" checked>
                <label for="paystack">Paystack</label>
            </div>
            <div>
                <input type="radio" id="opay" name="payment_method" value="opay">
                <label for="opay">Opay</label>
            </div>

            <?php if ($setting['pay_on_delivery_enabled']): ?>
                <div>
                    <input type="radio" id="pay_on_delivery" name="payment_method" value="pay_on_delivery">
                    <label for="pay_on_delivery">Pay on Delivery</label>
                </div>
            <?php endif; ?>
        </div>

        <form id="paymentForm">
            <input type="hidden" id="email" value="<?php echo htmlspecialchars($email); ?>">
            <input type="hidden" id="amount" value="<?php echo $total_price * 100; ?>">
            <input type="hidden" id="cart" value='<?php echo json_encode($cart); ?>'>
            <div class="text-center">
                <button type="button" onclick="pay()" class="btn btn-pay btn-lg">Pay Now</button>
            </div>
        </form>

        <script>
            function pay() {
                const paymentMethod = document.querySelector('input[name="payment_method"]:checked').value;

                if (paymentMethod === 'paystack') {
                    payWithPaystack();
                } else if (paymentMethod === 'opay') {
                    payWithOpay();
                } else if (paymentMethod === 'pay_on_delivery') {
                    handlePayOnDelivery();
                }
            }

            function payWithPaystack() {
                var handler = PaystackPop.setup({
                    key: '<?php echo $paystack_public_key; ?>',
                    email: document.getElementById('email').value,
                    amount: document.getElementById('amount').value,
                    currency: "NGN",
                    onClose: function() {
                        alert('Payment window closed.');
                    },
                    callback: function(response) {
                        const cart = document.getElementById('cart').value;
                        window.location.href = 'verify_transaction.php?reference=' + response.reference + '&cart=' + encodeURIComponent(cart);
                    }
                });
                handler.openIframe();
            }

            function payWithOpay() {
                const amount = document.getElementById('amount').value;
                const email = document.getElementById('email').value;

                const data = {
                    amount: amount,
                    currency: "NGN",
                    email: email,
                };

                fetch('process_opay_payment.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify(data),
                })
                .then(response => response.json())
                .then(data => {
                    if (data.status === 'success') {
                        window.location.href = data.payment_url;
                    } else {
                        alert("Error initiating Opay payment: " + data.message);
                    }
                })
                .catch((error) => {
                    console.error('Error:', error);
                    alert("An error occurred while processing the payment.");
                });
            }

            function handlePayOnDelivery() {
                document.body.innerHTML += `
                    <form id="payOnDeliveryForm" method="POST" action="">
                        <input type="hidden" name="payment_method" value="pay_on_delivery">
                        <input type="hidden" name="total_price" value="${document.getElementById('amount').value / 100}">
                    </form>`;
                document.getElementById('payOnDeliveryForm').submit();
            }
        </script>
    </div>

    <?php include 'footer.php'; ?>
</body>
</html>
