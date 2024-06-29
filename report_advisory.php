<?php
session_start();
if (!isset($_SESSION['username'])) {
    header("Location: login.php");
    exit();
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    include 'db.php';

    $userID = $_SESSION['userid'];
    $advisoryText = nl2br(htmlspecialchars($_POST['advisoryText'], ENT_QUOTES, 'UTF-8')); // Convert newlines to <br> tags

    $sql = "INSERT INTO User_Advisories (UserID, Description) VALUES (?, ?)";
    $stmt = $conn->prepare($sql);

    if ($stmt === false) {
        die('Prepare failed: ' . htmlspecialchars($conn->error));
    }

    $stmt->bind_param("is", $userID, $advisoryText);

    if ($stmt->execute() === false) {
        die('Execute failed: ' . htmlspecialchars($stmt->error));
    }

    $stmt->close();
    $conn->close();

    header("Location: index.php");
    exit();
}
?>
