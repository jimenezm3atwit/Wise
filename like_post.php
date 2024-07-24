<?php
session_start();
include 'db.php';

$postID = $_POST['postID'] ?? null;
$userID = $_SESSION['userid'] ?? null;

if ($postID === null || $userID === null) {
    echo json_encode(['status' => 'error', 'message' => 'Missing data']);
    exit();
}

// Check if the user has already liked the post
$checkSql = "SELECT * FROM PostLikes WHERE PostID = ? AND UserID = ?";
$checkStmt = $conn->prepare($checkSql);

if ($checkStmt === false) {
    echo json_encode(['status' => 'error', 'message' => 'Prepare failed: ' . htmlspecialchars($conn->error)]);
    exit();
}

$checkStmt->bind_param("ii", $postID, $userID);

if ($checkStmt->execute() === false) {
    echo json_encode(['status' => 'error', 'message' => 'Execute failed: ' . htmlspecialchars($checkStmt->error)]);
    exit();
}

$checkResult = $checkStmt->get_result();

if ($checkResult->num_rows > 0) {
    echo json_encode(['status' => 'error', 'message' => 'Post already liked']);
    exit();
}

$checkStmt->close();

// Insert the like
$sql = "INSERT INTO PostLikes (PostID, UserID) VALUES (?, ?)";
$stmt = $conn->prepare($sql);

if ($stmt === false) {
    echo json_encode(['status' => 'error', 'message' => 'Prepare failed: ' . htmlspecialchars($conn->error)]);
    exit();
}

$stmt->bind_param("ii", $postID, $userID);

if ($stmt->execute()) {
    // Update the like count in the Posts table
    $updateSql = "UPDATE Posts SET Likes = Likes + 1 WHERE PostID = ?";
    $updateStmt = $conn->prepare($updateSql);

    if ($updateStmt === false) {
        echo json_encode(['status' => 'error', 'message' => 'Prepare failed: ' . htmlspecialchars($conn->error)]);
        exit();
    }

    $updateStmt->bind_param("i", $postID);

    if ($updateStmt->execute()) {
       echo json_encode(['status' => 'success']);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Failed to update like count: ' . htmlspecialchars($updateStmt->error)]);
    }

    $updateStmt->close();
} else {
   echo json_encode(['status' => 'error', 'message' => 'Failed to like post: ' . htmlspecialchars($stmt->error)]);
}

$stmt->close();
$conn->close();
?>
