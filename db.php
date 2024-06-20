<?php
$servername = "localhost";
$username = "root";
$password = "Admin1";
$dbname = "wise";

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>
