<?php
include 'db.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['status' => 'error', 'message' => 'Invalid request method']);
    error_log('Invalid request method');
    exit();
}

$postID = isset($_POST['postID']) ? intval($_POST['postID']) : 0;

if ($postID === 0) {
    echo json_encode(['status' => 'error', 'message' => 'Post ID is required']);
    error_log('Post ID is required');
    exit();
}

session_start();
$userID = isset($_SESSION['userid']) ? intval($_SESSION['userid']) : 0;

if ($userID === 0) {
    echo json_encode(['status' => 'error', 'message' => 'User not logged in']);
    error_log('User not logged in');
    exit();
}

// Check if the user has already liked the post
$checkSql = "SELECT * FROM Likes WHERE PostID = ? AND UserID = ?";
$checkStmt = $conn->prepare($checkSql);

if ($checkStmt === false) {
    echo json_encode(['status' => 'error', 'message' => 'Prepare failed: ' . htmlspecialchars($conn->error)]);
    error_log('Prepare failed: ' . htmlspecialchars($conn->error));
    exit();
}

$checkStmt->bind_param('ii', $postID, $userID);
$checkStmt->execute();
$checkResult = $checkStmt->get_result();

if ($checkResult->num_rows > 0) {
    // User has already liked the post
    echo json_encode(['status' => 'error', 'message' => 'Already liked the post']);
    error_log('User has already liked the post');
    $checkStmt->close();
    $conn->close();
    exit();
}

$checkStmt->close();

// User has not liked the post, add the like
$insertSql = "INSERT INTO Likes (PostID, UserID) VALUES (?, ?)";
$insertStmt = $conn->prepare($insertSql);

if ($insertStmt === false) {
    echo json_encode(['status' => 'error', 'message' => 'Prepare failed: ' . htmlspecialchars($conn->error)]);
    error_log('Prepare failed: ' . htmlspecialchars($conn->error));
    exit();
}

$insertStmt->bind_param('ii', $postID, $userID);

if ($insertStmt->execute() === false) {
    echo json_encode(['status' => 'error', 'message' => 'Execute failed: ' . htmlspecialchars($insertStmt->error)]);
    error_log('Execute failed: ' . htmlspecialchars($insertStmt->error));
    exit();
}

// Increment the like count for the post
$updateLikesSql = "UPDATE Posts SET Likes = Likes + 1 WHERE PostID = ?";
$updateLikesStmt = $conn->prepare($updateLikesSql);

if ($updateLikesStmt === false) {
    echo json_encode(['status' => 'error', 'message' => 'Prepare failed: ' . htmlspecialchars($conn->error)]);
    error_log('Prepare failed: ' . htmlspecialchars($conn->error));
    exit();
}

$updateLikesStmt->bind_param('i', $postID);

if ($updateLikesStmt->execute() === false) {
    echo json_encode(['status' => 'error', 'message' => 'Execute failed: ' . htmlspecialchars($updateLikesStmt->error)]);
    error_log('Execute failed: ' . htmlspecialchars($updateLikesStmt->error));
    exit();
}

echo json_encode(['status' => 'success', 'message' => 'Like added']);
error_log('Like added');
$insertStmt->close();
$updateLikesStmt->close();
$conn->close();
?>
