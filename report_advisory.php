<?php
session_start();
if (!isset($_SESSION['username'])) {
    header("Location: login.php");
    exit();
}

include 'db.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $userID = $_SESSION['userid'];
    $advisoryText = $_POST['advisoryText'];
    $sql = "INSERT INTO Advisories (UserID, AdvisoryText, CreatedAt) VALUES (?, ?, NOW())";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("is", $userID, $advisoryText);
    if ($stmt->execute()) {
        header("Location: index.php");
        exit();
    } else {
        echo "Sorry, there was an error submitting your advisory.";
    }
    $stmt->close();
    $conn->close();
}
?>
