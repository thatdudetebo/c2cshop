<?php
session_start();
include 'includes/db_connect.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: index.php");
    exit;
}

// Handle delete with reason
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_product'])) {
    $product_id = intval($_POST['product_id']);
    $reason = trim($_POST['reason']);
    $admin_id = $_SESSION['user_id'];

    if (!empty($reason)) {
        // Insert removal reason
        $stmt = $conn->prepare("INSERT INTO product_removals (product_id, removed_by, reason) VALUES (?, ?, ?)");
        $stmt->bind_param("iis", $product_id, $admin_id, $reason);
        $stmt->execute();
        $stmt->close();

        // Delete product
        $stmt = $conn->prepare("DELETE FROM products WHERE id = ?");
        $stmt->bind_param("i", $product_id);
        $stmt->execute();
        $stmt->close();

        $message = "Product ID $product_id removed successfully.";
    } else {
        $error = "Please provide a reason for deletion.";
    }
}

// Fetch all products
$result = $conn->query("SELECT p.*, u.username FROM products p JOIN users u ON p.seller_id = u.id ORDER BY p.created_at DESC");
?>

<!DOCTYPE html>
<html>
<head>
    <title>Admin Dashboard - BCShop</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
<header>
    <div class="container">
        <h1>Admin Dashboard</h1>
        <nav>
            <ul>
                <li><a href="index.php">Home</a></li>
                <li><a href="logout.php">Logout (<?php echo htmlspecialchars($_SESSION['username']); ?>)</a></li>
            </ul>
        </nav>
    </div>
</header>

<div class="container">
    <h2>Manage Products</h2>

    <?php if (isset($message)): ?>
        <p class="message success"><?php echo $message; ?></p>
    <?php elseif (isset($error)): ?>
        <p class="message error"><?php echo $error; ?></p>
    <?php endif; ?>

    <?php if ($result->num_rows > 0): ?>
        <table>
            <thead>
                <tr>
                    <th>Product</th>
                    <th>Seller</th>
                    <th>Price</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
            <?php while ($row = $result->fetch_assoc()): ?>
                <tr>
                    <td><?php echo htmlspecialchars($row['name']); ?></td>
                    <td><?php echo htmlspecialchars($row['username']); ?></td>
                    <td>$<?php echo number_format($row['price'], 2); ?></td>
                    <td>
                        <form method="POST" onsubmit="return confirm('Are you sure you want to delete this product?');">
                            <input type="hidden" name="product_id" value="<?php echo $row['id']; ?>">
                            <textarea name="reason" placeholder="Reason for deletion" required></textarea><br>
                            <input type="submit" name="delete_product" value="Delete" class="view-details-btn" style="background-color: red;">
                        </form>
                    </td>
                </tr>
            <?php endwhile; ?>
            </tbody>
        </table>
    <?php else: ?>
        <p>No products found.</p>
    <?php endif; ?>
</div>

<footer>
    <p>&copy; 2025 BCShop. All rights reserved.</p>
</footer>
</body>
</html>

<?php $conn->close(); ?>
