<?php
session_start();

if (!isset($_SESSION['username'])) {
    echo json_encode(['status' => 'error', 'message' => 'Unauthorized']);
    exit();
}

include 'db.php';

$userID = $_SESSION['userid'];
$followingID = intval($_POST['followingID']);

$sql = "INSERT INTO Follows (FollowerID, FollowingID) VALUES (?, ?)";
$stmt = $conn->prepare($sql);
$stmt->bind_param("ii", $userID, $followingID);

if ($stmt->execute()) {
    echo json_encode(['status' => 'success']);
} else {
    echo json_encode(['status' => 'error', 'message' => 'Failed to follow user']);
}

$stmt->close();
$conn->close();
?>
