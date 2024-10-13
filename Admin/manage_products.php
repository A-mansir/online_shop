<?php
session_start();
include 'header.php';

$conn = new mysqli("localhost:8080", "root", "root", "shop_db");

// Check for connection errors
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
// Handle product addition
if (isset($_POST['add_product'])) {
    $name = $_POST['name'];
    $description = $_POST['description'];
    $price = $_POST['price'];
    
    // Check if the image file was uploaded
    if (isset($_FILES['image']) && $_FILES['image']['error'] == 0) {
        $image = $_FILES['image']['name'];
        $target_dir = "image/"; // Adjusted path to the 'image' directory in the main project folder
        $target_file = $target_dir . basename($image);

        // Attempt to move the uploaded file
        if (move_uploaded_file($_FILES['image']['tmp_name'], $target_file)) {
            // Insert the product into the database
            $conn->query("INSERT INTO products (name, description, price, image) VALUES ('$name', '$description', '$price', '$image')");
            // Redirect to the same page after adding the product
            header("Location: " . $_SERVER['PHP_SELF']);
            exit(); // Terminate the script after the redirect
        } else {
            echo "Error uploading the image.";
        }
    } else {
        echo "No image uploaded or there was an error with the upload.";
    }
}

// Handle product deletion
if (isset($_GET['delete_id'])) {
    $id = $_GET['delete_id'];
    $conn->query("DELETE FROM products WHERE id=$id");
    // Redirect to the same page after deleting the product
    header("Location: " . $_SERVER['PHP_SELF']);
    exit(); // Terminate the script after the redirect
}

// Fetch products
$result = $conn->query("SELECT * FROM products");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <title>Manage Products</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container">
        <h1>Manage Products</h1>
        <form method="POST" enctype="multipart/form-data">
            <div class="mb-3">
                <label for="name" class="form-label">Product Name</label>
                <input type="text" class="form-control" name="name" required>
            </div>
            <div class="mb-3">
                <label for="description" class="form-label">Description</label>
                <textarea class="form-control" name="description" required></textarea>
            </div>
            <div class="mb-3">
                <label for="price" class="form-label">Price</label>
                <input type="text" class="form-control" name="price" required>
            </div>
            <div class="mb-3">
                <label for="image" class="form-label">Image</label>
                <input type="file" class="form-control" name="image" required>
            </div>
            <button type="submit" name="add_product" class="btn btn-primary">Add Product</button>
        </form>

        <h2 class="mt-5">Product List</h2>
        <table class="table">
            <thead>
                <tr>
                    <th>Name</th>
                    <th>Description</th>
                    <th>Price</th>
                    <th>Image</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($row = $result->fetch_assoc()) { ?>
                    <tr>
                        <td><?php echo htmlspecialchars($row['name']); ?></td>
                        <td><?php echo htmlspecialchars($row['description']); ?></td>
                        <td>NGN <?php echo number_format($row['price'], 2); ?></td>
                        <td>
                            <img src="image/<?php echo htmlspecialchars($row['image']); ?>" width="50" alt="<?php echo htmlspecialchars($row['name']); ?>">
                        </td>
                        <td>
                            <a href="edit_product.php?id=<?php echo $row['id']; ?>" class="btn btn-warning btn-sm">Edit</a>
                            <a href="manage_products.php?delete_id=<?php echo $row['id']; ?>" class="btn btn-danger btn-sm">Delete</a>
                        </td>
                    </tr>
                <?php } ?>
            </tbody>
        </table>
    </div>
    <?php include 'footer.php'; ?>
</body>
</html>
