<?php
session_start();
include 'includes/db_connect.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Handle form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Sanitize inputs
    $name = trim($_POST["name"]);
    $description = trim($_POST["description"]);
    $price = floatval($_POST["price"]);
    $quantity = intval($_POST["quantity"]);
    $seller_id = $_SESSION["user_id"];

    // Handle file upload (if applicable)
    $image = null;
    if (isset($_FILES["image"]) && $_FILES["image"]["error"] == 0) {
        $target_dir = "uploads/";
        $target_file = $target_dir . basename($_FILES["image"]["name"]);

        // Optional: Validate file type, size, etc.
        $allowed_types = ["jpg", "jpeg", "png", "gif"];
        $file_type = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));

        if (in_array($file_type, $allowed_types)) {
            if (move_uploaded_file($_FILES["image"]["tmp_name"], $target_file)) {
                $image = $target_file;
            }
        }
    }

    // Insert product into database
    $stmt = $conn->prepare("INSERT INTO products (name, description, price, quantity, seller_id, image, created_at) VALUES (?, ?, ?, ?, ?, ?, NOW())");
    $stmt->bind_param("ssdiss", $name, $description, $price, $quantity, $seller_id, $image);

    if ($stmt->execute()) {
        $message = "Product added successfully!";
    } else {
        $error = "Error: " . $stmt->error;
    }

    $stmt->close();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Add Product - BCShop</title>
<link rel="stylesheet" href="css/style.css">
</head>
<body>
<header>
    <div class="container">
        <h1>BCShop</h1>
        <nav>
            <ul>
                <li><a href="index.php">Home</a></li>
                <li><a href="add_product.php">Add Product</a></li>
                <li><a href="my_orders.php">My Orders</a></li>
                <li><a href="cart.php">Cart</a></li>
                <li><a href="logout.php">Logout (<?php echo htmlspecialchars($_SESSION['username']); ?>)</a></li>
            </ul>
        </nav>
    </div>
</header>

<div class="container">
    <h2>Add New Product</h2>

    <?php if (!empty($message)): ?>
        <div class="message success"><?php echo htmlspecialchars($message); ?></div>
    <?php elseif (!empty($error)): ?>
        <div class="message error"><?php echo htmlspecialchars($error); ?></div>
    <?php endif; ?>

    <form action="add_product.php" method="post" enctype="multipart/form-data">
        <div>
            <label for="name">Product Name:</label>
            <input type="text" name="name" id="name" required>
        </div>
        <div>
            <label for="description">Description:</label>
            <textarea name="description" id="description" required></textarea>
        </div>
        <div>
            <label for="price">Price ($):</label>
            <input type="number" step="0.01" name="price" id="price" required>
        </div>
        <div>
            <label for="quantity">Quantity:</label>
            <input type="number" name="quantity" id="quantity" required>
        </div>
        <div>
            <label for="image">Product Image (optional):</label>
            <input type="file" name="image" id="image" accept="image/*">
        </div>
        <input type="submit" value="Add Product">
    </form>
</div>

<footer>
    <p>&copy; 2025 BCshop. All rights reserved.</p>
</footer>
</body>
</html>
