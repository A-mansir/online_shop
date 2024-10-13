<?php
session_start();
$conn = new mysqli("localhost:8080", "root", "root", "shop_db");

// Check if the user is logged in (assuming you store user ID in session)
if (!isset($_SESSION['user_id'])) {
    die("You must be logged in to edit your profile.");
}

$user_id = $_SESSION['user_id'];

// Fetch the user's current details from the database
$stmt = $conn->prepare("SELECT * FROM users WHERE id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();

// If form is submitted, update the profile
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = $_POST['name'];
    $email = $_POST['email'];
    $phone_number = $_POST['phone_number'];

    // Update the user's details in the database
    $stmt = $conn->prepare("UPDATE users SET name = ?, email = ?, phone_number = ? WHERE id = ?");
    $stmt->bind_param("sssi", $name, $email, $phone_number, $user_id);

    if ($stmt->execute()) {
        $success_message = "Profile updated successfully!";
        // Fetch the updated user details
        $stmt = $conn->prepare("SELECT * FROM users WHERE id = ?");
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $user = $result->fetch_assoc(); // Refresh user data after update
    } else {
        $error_message = "Failed to update profile.";
    }
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <title>Edit Profile</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container mt-4">
        <h1>Edit Profile</h1>

        <!-- Display success or error message -->
        <?php if (isset($success_message)) { ?>
            <div class="alert alert-success"><?php echo $success_message; ?></div>
        <?php } elseif (isset($error_message)) { ?>
            <div class="alert alert-danger"><?php echo $error_message; ?></div>
        <?php } ?>

        <form action="edit_profile.php" method="POST">
            <div class="mb-3">
                <label for="name" class="form-label">Full Name</label>
                <input type="text" class="form-control" id="name" name="name" value="<?php echo htmlspecialchars($user['name']); ?>" required>
            </div>
            <div class="mb-3">
                <label for="email" class="form-label">Email</label>
                <input type="email" class="form-control" id="email" name="email" value="<?php echo htmlspecialchars($user['email']); ?>" required>
            </div>
            <div class="mb-3">
                <label for="phone" class="form-label">phone number</label>
                <input type="text" class="form-control" id="phone_number" name="phone_number" value="<?php echo htmlspecialchars($user['phone_number']); ?>" required>
            </div>
            <button type="submit" class="btn btn-primary">Update Profile</button>
        </form>
    </div>
</body>
</html>
