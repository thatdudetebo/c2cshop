<?php
session_start();
require_once 'includes/db_connect.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// Fetch orders for the current user's listed products
$sql = "SELECT o.id AS order_id, o.quantity, o.created_at,
               p.name AS product_name, p.price AS unit_price,
               u.username AS buyer_username
        FROM orders o
        INNER JOIN products p ON o.product_id = p.id
        INNER JOIN users u ON o.buyer_id = u.id
        WHERE p.seller_id = ?
        ORDER BY o.created_at DESC";

$stmt = $conn->prepare($sql);
if (!$stmt) {
    die("Query preparation failed: " . $conn->error);
}

$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>My Orders - BCShop</title>
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
                <li><a href="cart.php">Cart</a></li>
                <li><a href="logout.php">Logout (<?php echo htmlspecialchars($_SESSION['username']); ?>)</a></li>
            </ul>
        </nav>
    </div>
</header>

<div class="container">
    <h2>Orders for My Listed Products</h2>
    <?php if ($result->num_rows > 0): ?>
        <table style="width: 100%; border-collapse: collapse;">
            <thead>
                <tr style="background-color: #333; color: white;">
                    <th>Order ID</th>
                    <th>Product</th>
                    <th>Buyer</th>
                    <th>Quantity</th>
                    <th>Total Price</th>
                    <th>Ordered At</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($row = $result->fetch_assoc()): ?>
                    <tr style="background-color: #f8f8f8; color: black;">
                        <td><?php echo htmlspecialchars($row['order_id']); ?></td>
                        <td><?php echo htmlspecialchars($row['product_name']); ?></td>
                        <td><?php echo htmlspecialchars($row['buyer_username']); ?></td>
                        <td><?php echo htmlspecialchars($row['quantity']); ?></td>
                        <td>$<?php echo number_format($row['unit_price'] * $row['quantity'], 2); ?></td>
                        <td><?php echo htmlspecialchars($row['created_at']); ?></td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    <?php else: ?>
        <p>No orders have been placed for your products yet.</p>
    <?php endif; ?>
</div>

<?php
$stmt->close();
$conn->close();
?>

<footer>
    <p>&copy; 2025 BCShop. All rights reserved.</p>
</footer>
</body>
</html>
