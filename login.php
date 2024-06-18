<?php
include 'db.php';

$username = $_POST['username'];
$password = $_POST['password'];

$sql = "SELECT id, password FROM users WHERE username = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $username);
$stmt->execute();
$stmt->store_result();
$stmt->bind_result($id, $hashed_password);

if ($stmt->num_rows > 0) {
    $stmt->fetch();
    if (password_verify($password, $hashed_password)) {
        echo "Login successful";
        // Set session variables and redirect user to another page
    } else {
        echo "Invalid username or password";
    }
} else {
    echo "Invalid username or password";
}
$stmt->close();
$conn->close();
?>
