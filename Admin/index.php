<?php
session_start();
include 'header.php';
$servername = "localhost:8080";
$username = "root";
$password = "root";
$dbname = "shop_db";
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Check if admin is logged in
if (!isset($_SESSION['admin'])) {
    header("Location: admin_login.php");
    exit();
}

// Handle form submission to toggle feature
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $enabled = isset($_POST['pay_on_delivery']) ? 1 : 0;
    if ($conn->query("UPDATE settings SET pay_on_delivery_enabled = $enabled WHERE id = 1") === TRUE) {
        echo "Settings updated successfully!";
    } else {
        echo "Error updating settings: " . $conn->error;
    }
}

// Fetch current setting
$result = $conn->query("SELECT pay_on_delivery_enabled FROM settings WHERE id = 1");
$setting = $result->fetch_assoc();

// Fetch admin details (optional)
$sql = "SELECT * FROM admins";
$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background-color: #f8f9fa;
            font-family: Arial, sans-serif;
        }
        .container {
            margin-top: 50px;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Welcome to the Admin Dashboard</h1>
        <p class="lead">Manage your online shop efficiently!</p>

        <h2>Pay on Delivery Settings</h2>
        <form method="POST">
            <div class="form-check">
                <input class="form-check-input" type="checkbox" name="pay_on_delivery" id="pay_on_delivery" <?php echo $setting['pay_on_delivery_enabled'] ? 'checked' : ''; ?>>
                <label class="form-check-label" for="pay_on_delivery">
                    Enable Pay on Delivery
                </label>
            </div>
            <button type="submit" class="btn btn-primary mt-3">Save Changes</button>
        </form>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <?php include 'footer.php'; ?>
</body>
</html>
