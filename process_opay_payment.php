<?php
header('Content-Type: application/json');

// Get the input from the POST request
$data = json_decode(file_get_contents('php://input'), true);

$amount = $data['amount'];
$currency = $data['currency'];
$email = $data['email'];

// Prepare your request to Opay
$opay_api_url = 'https://sandbox.opay.com/api/v1/merchant/transaction'; // Use sandbox for testing

// Create a payload for Opay
$payload = [
    'amount' => $amount,
    'currency' => $currency,
    'email' => $email,
    // Add other necessary fields as required by Opay
];

// Use cURL to send the request to Opay
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $opay_api_url);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Content-Type: application/json',
    'Authorization: Bearer YOUR_OPAY_API_KEY' // Replace with your Opay API key
]);

$response = curl_exec($ch);
curl_close($ch);

// Decode the response
$response_data = json_decode($response, true);

if ($response_data['status'] === 'success') {
    // Success response from Opay
    echo json_encode([
        'status' => 'success',
        'payment_url' => $response_data['payment_url'] // This should be the URL to redirect the user to for payment
    ]);
} else {
    // Handle the error
    echo json_encode([
        'status' => 'error',
        'message' => $response_data['message']
    ]);
}
