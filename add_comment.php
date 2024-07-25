<?php
session_start();
include 'db.php';

$postID = $_POST['postID'] ?? null;
$commentText = $_POST['commentText'] ?? null;
$userID = $_SESSION['userid'] ?? null;

if ($postID === null || $commentText === null || $userID === null) {
    echo json_encode(['status' => 'error', 'message' => 'Missing data']);
    exit();
}

$sql = "INSERT INTO Comments (PostID, UserID, CommentText) VALUES (?, ?, ?)";
$stmt = $conn->prepare($sql);
$stmt->bind_param("iis", $postID, $userID, $commentText);

if ($stmt->execute()) {
    echo json_encode(['status' => 'success']);
} else {
    echo json_encode(['status' => 'error', 'message' => 'Failed to add comment']);
}

$stmt->close();
$conn->close();
?>
