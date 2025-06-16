<?php
session_start();
include 'includes/db_connect.php';

// Redirect if no product ID is provided
if (!isset($_GET['id'])) {
    header("Location: index.php");
    exit;
}

$product_id = intval($_GET['id']);

// Fetch product
$stmt = $conn->prepare("SELECT p.*, u.username AS seller_username FROM products p JOIN users u ON p.seller_id = u.id WHERE p.id = ?");
$stmt->bind_param("i", $product_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    echo "Product not found.";
    exit;
}
$product = $result->fetch_assoc();
$stmt->close();

// Handle Add to Cart
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_to_cart'])) {
    $quantity = max(1, intval($_POST['quantity'] ?? 1)); // Default to 1 if empty

    // Initialize the cart if it doesn't exist
    if (!isset($_SESSION['cart'])) {
        $_SESSION['cart'] = [];
    }

    $found = false;

    // If product is already in cart, update quantity
    foreach ($_SESSION['cart'] as &$item) {
        if ($item['product_id'] === $product_id) {
            $item['quantity'] += $quantity;
            $found = true;
            break;
        }
    }
    unset($item); // Break reference

    // If product not in cart, add it
    if (!$found) {
        $_SESSION['cart'][] = [
            'product_id' => $product_id,
            'quantity' => $quantity
        ];
    }

    // Redirect to cart or stay on page
    header("Location: cart.php");
    exit;
}
?>

<!DOCTYPE html>
<html>
<head>
    <title><?php echo htmlspecialchars($product['name']); ?> - BCShop</title>
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
                    <li><a href="cart.php">Cart</a></li>
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
    <h2><?php echo htmlspecialchars($product['name']); ?></h2>
    <img src="<?php echo htmlspecialchars($product['image'] ?: 'https://via.placeholder.com/400x400?text=No+Image'); ?>" alt="<?php echo htmlspecialchars($product['name']); ?>" style="max-width: 400px;">
    <p><strong>Description:</strong> <?php echo htmlspecialchars($product['description']); ?></p>
    <p><strong>Price:</strong> $<?php echo number_format($product['price'], 2); ?></p>
    <p><strong>Seller:</strong> <?php echo htmlspecialchars($product['seller_username']); ?></p>

    <form action="product.php?id=<?php echo $product_id; ?>" method="POST">
        <input type="hidden" name="product_id" value="<?php echo $product['id']; ?>">
        <label for="quantity">Quantity:</label>
        <input type="number" name="quantity" id="quantity" value="1" min="1" required>
        <br><br>
        <input type="submit" name="add_to_cart" value="Add to Cart">
    </form>
</div>

<footer>
    <p>&copy; 2025 BCShop. All rights reserved.</p>
</footer>
</body>
</html>

<?php $conn->close(); ?>
