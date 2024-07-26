<?php
session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['username'])) {
    echo json_encode(['status' => 'error', 'message' => 'Unauthorized']);
    exit();
}

include 'db.php';

$userID = $_SESSION['userid'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $advisoryText = $_POST['advisoryText'];

    $sql = "INSERT INTO User_Advisories (UserID, Description, DateReported) VALUES (?, ?, NOW())";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("is", $userID, $advisoryText);

    if ($stmt->execute()) {
        echo json_encode(['status' => 'success', 'message' => 'Advisory reported successfully!']);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Error reporting advisory: ' . $stmt->error]);
    }

    $stmt->close();
}

$conn->close();
?>
