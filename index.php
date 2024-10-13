<?php
session_start();
include 'header.php';
include 'index.html';
$conn = new mysqli("localhost:8080", "root", "root", "shop_db");

// Handle product search
$search_query = "";
if (isset($_GET['search'])) {
    $search_query = $_GET['search'];
    // Escape the search query to prevent SQL injection
    $search_query = $conn->real_escape_string($search_query);
    $result = $conn->query("SELECT * FROM products WHERE name LIKE '%$search_query%' OR description LIKE '%$search_query%'");
} else {
    $result = $conn->query("SELECT * FROM products");
}
?>
