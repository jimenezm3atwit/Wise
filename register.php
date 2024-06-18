<?php
include 'db.php'; // Include the database connection

$firstname = $_POST['firstname'];
$lastname = $_POST['lastname'];
$username = $_POST['username'];
$password = $_POST['password']; // Consider hashing the password

// SQL to insert new user
$sql = "INSERT INTO users (firstname, lastname, username, password) VALUES (?, ?, ?, ?)";
$stmt = $conn->prepare($sql);
$stmt->bind_param("ssss", $firstname, $lastname, $username, password_hash($password, PASSWORD_DEFAULT));
if ($stmt->execute()) {
    echo "New record created successfully";
} else {
    echo "Error: " . $stmt->error;
}
$stmt->close();
$conn->close();
?>
