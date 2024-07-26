<?php
session_start();

if (!isset($_SESSION['username'])) {
    echo json_encode(['status' => 'error', 'message' => 'Unauthorized']);
    exit();
}

include 'db.php';

$userID = $_SESSION['userid'];
$followingID = intval($_POST['followingID']);

$sql = "DELETE FROM Follows WHERE FollowerID = ? AND FollowingID = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("ii", $userID, $followingID);

if ($stmt->execute()) {
    echo json_encode(['status' => 'success']);
} else {
    echo json_encode(['status' => 'error', 'message' => 'Failed to unfollow user']);
}

$stmt->close();
$conn->close();
?>
