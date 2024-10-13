<?php
session_start();
include header.php';

$conn = new mysqli("localhost:8080", "root", "root", "shop_db");
$id = $_GET['id'];
$result = $conn->query("SELECT * FROM products WHERE id=$id");
$product = $result->fetch_assoc();

if (isset($_POST['edit_product'])) {
    $name = $_POST['name'];
    $description = $_POST['description'];
    $price = $_POST['price'];

    if ($_FILES['image']['name']) {
        $image = $_FILES['image']['name'];
        $target_dir = "C:/Users/acer/Desktop/Online shop/image";
        $target_file = $target_dir . basename($image);
        move_uploaded_file($_FILES['image']['tmp_name'], $target_file);
        $conn->query("UPDATE products SET name='$name', description='$description', price='$price', image='$image' WHERE id=$id");
    } else {
        $conn->query("UPDATE products SET name='$name', description='$description', price='$price' WHERE id=$id");
    }

    echo "Product updated successfully!";
    header('Location: manage_products.php');
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <title>Edit Product</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container">
        <h1>Edit Product</h1>
        <form method="POST" enctype="multipart/form-data">
            <div class="mb-3">
                <label for="name" class="form-label">Product Name</label>
                <input type="text" class="form-control" name="name" value="<?php echo $product['name']; ?>" required>
            </div>
            <div class="mb-3">
                <label for="description" class="form-label">Description</label>
                <textarea class="form-control" name="description" required><?php echo $product['description']; ?></textarea>
            </div>
            <div class="mb-3">
                <label for="price" class="form-label">Price</label>
                <input type="text" class="form-control" name="price" value="<?php echo $product['price']; ?>" required>
            </div>
            <div class="mb-3">
                <label for="image" class="form-label">Image</label>
                <input type="file" class="form-control" name="image">
                <img src="images/<?php echo $product['image']; ?>" width="100" class="mt-3">
            </div>
            <button type="submit" name="edit_product" class="btn btn-primary">Update Product</button>
        </form>
    </div>
     <?php include 'footer.php'; ?>
</body>
</html>
