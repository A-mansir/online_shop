<?php
session_start();
$conn = new mysqli("localhost:8080", "root", "root", "shop_db");

$reference = $_GET['reference'];
$cart = json_decode(urldecode($_GET['cart']), true); // Decode cart data

if (!$reference) {
    die('No reference supplied');
}

$secret_key = "sk_test_c69589f9a5d87987a32ea1a99e4067829dd09b0b";

$curl = curl_init();
curl_setopt_array($curl, array(
    CURLOPT_URL => "https://api.paystack.co/transaction/verify/" . rawurlencode($reference),
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_HTTPHEADER => [
        "Authorization: Bearer $secret_key",
        "Content-Type: application/json"
    ]
));

$response = curl_exec($curl);
curl_close($curl);

$transaction = json_decode($response);

if ($transaction->status && $transaction->data->status == 'success') {
    // Payment successful, insert order into database

    // Assuming you have user_id stored in the session
    $user_id = $_SESSION['user_id'];

    // Fetch user details from the database
    $user_result = $conn->query("SELECT * FROM users WHERE id = $user_id");
    $user = $user_result->fetch_assoc();

    // Retrieve address and phone number
    $address = $user['address']; // Assuming this is how you retrieve the address
    $phone_number = $user['phone_number']; // Retrieve phone number
    $total_amount = $transaction->data->amount / 100; // Convert back to Naira from Kobo

    // Prepare and execute insert statement
    $stmt = $conn->prepare("INSERT INTO orders (user_id, total_amount, address, phone_number) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("idss", $user_id, $total_amount, $address, $phone_number);

    if ($stmt->execute()) {
        $order_id = $conn->insert_id; // Get the last inserted order ID

        // Insert order items
        foreach ($cart as $product_id => $quantity) {
            $stmt = $conn->prepare("INSERT INTO order_items (order_id, product_id, quantity) VALUES (?, ?, ?)");
            $stmt->bind_param("iii", $order_id, $product_id, $quantity);
            $stmt->execute();
        }

        // Redirect or display success message
        header("Location: index.php?success=order_placed");
        exit();
    } else {
        die("Error inserting order: " . $stmt->error);
    }

} else {
    echo "Payment failed!";
    header("Location: index.php?error=payment_failed");
    exit();
}
?>
