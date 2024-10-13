<?php
session_start();
include 'header.php';
include 'login.html';
$servername = "localhost:8080";
$username = "root";
$password = "root";
$dbname = "shop_db";
$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = $_POST['username'];
    $password = $_POST['password'];

    $sql = "SELECT * FROM admins WHERE username = '$username' AND password = '$password'";
    $result = $conn->query($sql);

    if ($result->num_rows == 1) {
        // Admin found, set session variable
        $_SESSION['admin'] = $username;
        header("Location: index.php"); // Redirect to admin dashboard
        exit();
    } else {
        echo "Invalid username or password";
    }
}

$conn->close();
?>