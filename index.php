<?php
session_start();
include 'includes/db_connect.php';

// Query products
$sql = "SELECT p.id, p.name, p.description, p.price, p.image, u.username
        FROM products p
        JOIN users u ON p.seller_id = u.id
        ORDER BY p.created_at DESC;";
$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>BCShop</title>
<link rel="stylesheet" href="css/style.css">
</head>
<body>
<header>
    <div class="container">
        <h1>BCShop</h1>
        <nav>
            <ul>
                <li><a href="index.php">Home</a></li>
                <?php if (isset($_SESSION['user_id'])): ?>
                    <li><a href="add_product.php">Add Product</a></li>
                    <li><a href="my_orders.php">My Orders</a></li>
                    <li><a href="manage_products.php">Manage Products</a></li>
                    <li><a href="cart.php">Cart</a></li>
                    <?php if (isset($_SESSION['role']) && $_SESSION['role'] == 'admin'): ?>
                        <li><a href="manage_products.php">Manage Products</a></li>
                        <li><a href="admin_dashboard.php">Admin Dashboard</a></li>
                    <?php endif; ?>
                    <li><a href="logout.php">Logout (<?php echo htmlspecialchars($_SESSION['username']); ?>)</a></li>
                <?php else: ?>
                    <li><a href="login.php">Login</a></li>
                    <li><a href="register.php">Register</a></li>
                <?php endif; ?>
            </ul>
        </nav>
    </div>
</header>

<div class="container">
    <h2>Available Products</h2>
    <div class="product-grid">
        <?php
        if ($result && $result->num_rows > 0) {
            while($row = $result->fetch_assoc()) {
                ?>
                <div class="product-item">
                    <img src="<?php echo htmlspecialchars($row['image'] ?: 'https://via.placeholder.com/200x200?text=No+Image'); ?>" alt="<?php echo htmlspecialchars($row['name']); ?>">
                    <h3><?php echo htmlspecialchars($row['name']); ?></h3>
                    <p><?php echo htmlspecialchars(substr($row['description'], 0, 100)) . '...'; ?></p>
                    <p>Available: <?php echo htmlspecialchars($row['quantity']); ?> units</p>
                    <p>Seller: <?php echo htmlspecialchars($row['username']); ?></p>
                    <div class="price">$<?php echo number_format($row['price'], 2); ?></div>
                    <a href="product.php?id=<?php echo $row['id']; ?>" class="view-details-btn">View Details</a>
                </div>
                <?php
            }
        } else {
            echo "<p>No products available yet.</p>";
        }
        ?>
    </div>
</div>

<?php $conn->close(); ?>
</body>
<footer>
    <p>&copy; 2025 BCshop. All rights reserved.</p>
</footer>
</html>
