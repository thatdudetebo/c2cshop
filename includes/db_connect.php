<?php
$servername = "localhost";
$username = "root"; // Default XAMPP MySQL username
$password = "";     // Default XAMPP MySQL password (empty)
$dbname = "c2cshop_db";

// $servername = "sql210.infinityfree.com";
// $username = "if0_39248464"; // Default XAMPP MySQL username
// $password = "Tz4RR5jKUT8ta";     // Default XAMPP MySQL password (empty)
// $dbname = "if0_39248464_XXX";

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
// Optional: Set character set for proper UTF-8 handling
$conn->set_charset("utf8mb4");
?>
