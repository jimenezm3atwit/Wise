<?php
include 'db.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['status' => 'error', 'message' => 'Invalid request method']);
    error_log('Invalid request method');
    exit();
}

$postID = isset($_POST['postID']) ? intval($_POST['postID']) : 0;
$commentText = isset($_POST['commentText']) ? trim($_POST['commentText']) : '';

if ($postID === 0 || $commentText === '') {
    echo json_encode(['status' => 'error', 'message' => 'Post ID and comment text are required']);
    error_log('Post ID and comment text are required');
    exit();
}

session_start();
$userID = isset($_SESSION['userid']) ? intval($_SESSION['userid']) : 0;

if ($userID === 0) {
    echo json_encode(['status' => 'error', 'message' => 'User not logged in']);
    error_log('User not logged in');
    exit();
}

$sql = "INSERT INTO Comments (PostID, UserID, Comment, CreatedAt) VALUES (?, ?, ?, NOW())";
$stmt = $conn->prepare($sql);

if ($stmt === false) {
    echo json_encode(['status' => 'error', 'message' => 'Prepare failed: ' . htmlspecialchars($conn->error)]);
    error_log('Prepare failed: ' . htmlspecialchars($conn->error));
    exit();
}

$stmt->bind_param('iis', $postID, $userID, $commentText);
error_log("Bind param successful: postID=$postID, userID=$userID, commentText=$commentText");

if ($stmt->execute() === false) {
    echo json_encode(['status' => 'error', 'message' => 'Execute failed: ' . htmlspecialchars($stmt->error)]);
    error_log('Execute failed: ' . htmlspecialchars($stmt->error));
    exit();
}

echo json_encode(['status' => 'success']);
error_log('Comment added successfully');
$stmt->close();
$conn->close();
?>
