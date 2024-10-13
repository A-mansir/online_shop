<?php
session_start();
include 'header.php';
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
<!DOCTYPE html>
<html lang="en">
<head>
    <title>Online Shop</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background-color: #f8f9fa; /* Light background for the entire page */
        }
        .product-image {
            height: 250px; /* Increased the image height for better visibility */
            object-fit: cover; /* Ensure the image covers the area without distortion */
            border-top-left-radius: 8px;
            border-top-right-radius: 8px;
        }
        .card {
            border: 150px; /* Remove default border */
            border-radius: 12px; /* Rounded corners */
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.1); /* Bolder shadow for card */
            transition: transform 0.3s, box-shadow 0.3s; /* Smooth hover effect */
        }
        .card:hover {
            transform: translateY(-10px); /* Lift the card on hover */
            box-shadow: 0 12px 24px rgba(0, 0, 0, 0.2); /* Stronger shadow on hover */
        }
        .card-title {
            color: #343a40; /* Darker text for product name */
            font-weight: bold;
            margin-bottom: 8px;
        }
        .card-text {
            color: #6c757d; /* Subtle color for description */
        }
        .price-text {
            color: #dc3545; /* Bright red for price */
            font-weight: bold;
            margin-bottom: 15px;
        }
        .btn-primary {
            background-color: #007bff; /* Bright blue button */
            border-color: #007bff;
        }
        .btn-primary:hover {
            background-color: #0056b3; /* Darker blue on hover */
            border-color: #0056b3;
        }
        .search-bar {
            margin-bottom: 40px;
        }
        .search-input {
            border-radius: 30px;
            padding: 10px 20px;
            font-size: 16px;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
        }
        .search-btn {
            border-radius: 30px;
            padding: 10px 20px;
        }
    </style>
</head>
<body>
    <div class="container mt-5">
        <h1 class="text-center mb-5">Discover Our Products</h1>
        
        <!-- Search Form -->
        <form method="GET" action="" class="search-bar d-flex justify-content-center">
            <input type="text" name="search" class="form-control search-input w-50" placeholder="Search for products..." value="<?php echo htmlspecialchars($search_query); ?>">
            <button class="btn btn-primary search-btn ms-3" type="submit">Search</button>
        </form>

        <div class="row">
            <?php if ($result->num_rows > 0) { ?>
                <?php while ($row = $result->fetch_assoc()) { ?>
                    <div class="col-md-3 mb-4"> <!-- Adjust the column size to fit more cards -->
                        <div class="card h-100">
                            <img src="admin/image/<?php echo $row['image']; ?>" class="card-img-top product-image" alt="<?php echo $row['name']; ?>">
                            <div class="card-body text-center">
                                <h5 class="card-title"><?php echo $row['name']; ?></h5>
                                <p class="card-text"><?php echo substr($row['description'], 0, 50); ?>...</p> <!-- Show a preview of the description -->
                                <p class="price-text">NGN <?php echo number_format($row['price'], 2); ?></p>
                                <a href="cart.php?id=<?php echo $row['id']; ?>" class="btn btn-primary">Add to Cart</a>
                            </div>
                        </div>
                    </div>
                <?php } ?>
            <?php } else { ?>
                <p class="text-center">No products found for "<?php echo htmlspecialchars($search_query); ?>"</p>
            <?php } ?>
        </div>
    </div>
    <?php include 'footer.php'; ?>
</body>
</html>