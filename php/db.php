<?php
$servername = "localhost";
$username = "root"; // Default XAMPP user
$password = "akki"; // Default XAMPP password
$database = "easeestate"; // Your database name

$conn = new mysqli($servername, $username, $password, $database);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>
