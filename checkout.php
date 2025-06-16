<?php
session_start();
include 'includes/db_connect.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

if (empty($_SESSION['cart'])) {
    echo "Your cart is empty.";
    exit;
}

$user_id = $_SESSION['user_id'];
$cart = $_SESSION['cart'];

// Collect product prices from DB
$ids = implode(',', array_map(fn($item) => intval($item['product_id']), $cart));
$sql = "SELECT id, price FROM products WHERE id IN ($ids)";
$result = $conn->query($sql);

$product_prices = [];
while ($row = $result->fetch_assoc()) {
    $product_prices[$row['id']] = $row['price'];
}

$conn->begin_transaction();

try {
    $stmt = $conn->prepare("INSERT INTO orders (user_id, product_id, quantity, total_price) VALUES (?, ?, ?, ?)");

    foreach ($cart as $item) {
        $pid = $item['product_id'];
        $qty = max(1, intval($item['quantity']));
        $price = $product_prices[$pid] ?? 0;
        $total = $qty * $price;

        $stmt->bind_param("iiid", $user_id, $pid, $qty, $total);
        $stmt->execute();
    }

    $conn->commit();
    $_SESSION['cart'] = []; // Clear cart

    $success = true;
} catch (Exception $e) {
    $conn->rollback();
    $success = false;
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Checkout - BCShop</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
<header>
    <div class="container">
        <h1>BCShop</h1>
        <nav>
            <ul>
                <li><a href="index.php">Home</a></li>
                <li><a href="my_orders.php">My Orders</a></li>
                <li><a href="logout.php">Logout (<?php echo htmlspecialchars($_SESSION['username']); ?>)</a></li>
            </ul>
        </nav>
    </div>
</header>

<div class="container">
    <h2>Checkout</h2>
    <?php if ($success): ?>
        <div class="message success">Thank you! Your order has been placed.</div>
        <p><a href="index.php">Return to Home</a></p>
    <?php else: ?>
        <div class="message error">Sorry, there was a problem processing your order.</div>
        <p><a href="cart.php">Return to Cart</a></p>
    <?php endif; ?>
</div>

<footer>
    <p>&copy; 2025 BCShop. All rights reserved.</p>
</footer>
</body>
</html>

<?php
$stmt->close();
$conn->close();
?>
