<?php
session_start();
$conn = new mysqli("localhost:8080", "root", "root", "shop_db");

$result = $conn->query("SELECT * FROM products");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Online Shop</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;700&display=swap" rel="stylesheet">
    <style>
        body {
            background-color: #f8f9fa;
            font-family: 'Roboto', sans-serif;
        }
        .card {
            border-radius: 12px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1);
            transition: transform 0.2s;
        }
        .card:hover {
            transform: scale(1.05);
        }
        .card-img-top {
            height: 150px;
            object-fit: cover;
            border-top-left-radius: 12px;
            border-top-right-radius: 12px;
        }
        .card-title {
            font-weight: bold;
        }
        .btn-add-to-cart {
            background-color: #ffcc00; /* Bright yellow for visibility */
            color: black; /* Black text for contrast */
            font-weight: bold;
        }
        .btn-add-to-cart:hover {
            background-color: #e6b800; /* Darker yellow on hover */
            color: white; /* Change text color on hover for better visibility */
        }
    </style>
</head>
<body>
    <?php include 'header.php'; ?>

    <div class="container my-5">
        <h1 class="mt-4 text-center">Products</h1>
        <div class="row">
            <?php while ($row = $result->fetch_assoc()) { 
                $imagePath = "admin/image/" . htmlspecialchars($row['image']);
                ?>
                <div class="col-md-3">
                    <div class="card mb-4">
                        <?php if (file_exists($imagePath)) { ?>
                            <img src="<?php echo $imagePath; ?>" class="card-img-top" alt="<?php echo htmlspecialchars($row['name']); ?>">
                        <?php } else { ?>
                            <img src="placeholder.png" class="card-img-top" alt="Image not available"> <!-- Placeholder image -->
                        <?php } ?>
                        <div class="card-body">
                            <h5 class="card-title"><?php echo htmlspecialchars($row['name']); ?></h5>
                            <p class="card-text"><?php echo htmlspecialchars($row['description']); ?></p>
                            <p class="card-text">NGN <?php echo number_format($row['price'], 2); ?></p>
                            <a href="cart.php?id=<?php echo $row['id']; ?>" class="btn btn-add-to-cart">Add to Cart</a>
                        </div>
                    </div>
                </div>
            <?php } ?>
        </div>
    </div>

    <?php include 'footer.php'; ?>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
