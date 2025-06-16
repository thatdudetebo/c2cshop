<?php
session_start();
include 'includes/db_connect.php';

$cart = $_SESSION['cart'] ?? [];

$productDetails = [];

if (!empty($cart)) {
    $ids = array_column($cart, 'product_id');
    $placeholders = implode(',', array_fill(0, count($ids), '?'));

    $stmt = $conn->prepare("SELECT id, name, price, image FROM products WHERE id IN ($placeholders)");

    if ($stmt) {
        $stmt->bind_param(str_repeat('i', count($ids)), ...$ids);
        $stmt->execute();
        $result = $stmt->get_result();

        while ($row = $result->fetch_assoc()) {
            $productDetails[$row['id']] = $row;
        }

        $stmt->close();
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Your Cart - BCShop</title>
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
                    <li><a href="my_orders.php">My Orders</a></li>
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
    <h2>Your Cart</h2>

    <?php if (empty($cart)): ?>
        <p>Your cart is empty.</p>
    <?php else: ?>
        <div class="container">  
            <table>
                <thead>
                    <tr>
                        <th>Image</th>
                        <th>Product</th>
                        <th>Price</th>
                        <th>Quantity</th>
                        <th>Subtotal</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $total = 0;
                    foreach ($cart as $item):
                        $product = $productDetails[$item['product_id']] ?? null;
                        if ($product):
                            $subtotal = $product['price'] * $item['quantity'];
                            $total += $subtotal;
                    ?>
                        <tr>
                            <td><img src="<?php echo htmlspecialchars($product['image'] ?: 'https://via.placeholder.com/60'); ?>" width="60"></td>
                            <td><?php echo htmlspecialchars($product['name']); ?></td>
                            <td>$<?php echo number_format($product['price'], 2); ?></td>
                            <td><?php echo htmlspecialchars($item['quantity']); ?></td>
                            <td>$<?php echo number_format($subtotal, 2); ?></td>
                        </tr>
                    <?php endif; endforeach; ?>
                </tbody>
            </table>
        </div>
        <h3>Total: $<?php echo number_format($total, 2); ?></h3>

        <form action="checkout.php" method="POST" style="margin-top: 20px;">
            <input type="submit" value="Proceed to Checkout" class="view-details-btn">
        </form>
    <?php endif; ?>
</div>

<footer>
    <p>&copy; 2025 BCShop. All rights reserved.</p>
</footer>
</body>
</html>
