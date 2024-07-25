<?php
include 'db.php';

// Function to log errors
function log_error($message) {
    $log_file = 'error_log.txt';
    file_put_contents($log_file, date('Y-m-d H:i:s') . " - " . $message . PHP_EOL, FILE_APPEND);
}

$postID = $_GET['postID'] ?? null;

if ($postID === null) {
    log_error('E1: Missing post ID');
    echo json_encode(['status' => 'error', 'message' => 'Missing post ID', 'code' => 'E1']);
    exit();
}

$sql = "SELECT P.PostID, P.MediaURL, P.Caption, P.Likes, U.FirstName, U.LastName 
        FROM Posts P 
        JOIN Users U ON P.UserID = U.UserID 
        WHERE P.PostID = ?";
$stmt = $conn->prepare($sql);

if ($stmt === false) {
    log_error('E2: Prepare failed: ' . htmlspecialchars($conn->error));
    echo json_encode(['status' => 'error', 'message' => 'Prepare failed: ' . htmlspecialchars($conn->error), 'code' => 'E2']);
    exit();
}

$stmt->bind_param("i", $postID);

if ($stmt->execute() === false) {
    log_error('E3: Execute failed: ' . htmlspecialchars($stmt->error));
    echo json_encode(['status' => 'error', 'message' => 'Execute failed: ' . htmlspecialchars($stmt->error), 'code' => 'E3']);
    exit();
}

$result = $stmt->get_result();

if ($result === false) {
    log_error('E4: Get result failed: ' . htmlspecialchars($stmt->error));
    echo json_encode(['status' => 'error', 'message' => 'Get result failed: ' . htmlspecialchars($stmt->error), 'code' => 'E4']);
    exit();
}

if ($result->num_rows > 0) {
    $post = $result->fetch_assoc();
    
    // Fetch comments
    $commentSql = "SELECT C.CommentText, U.FirstName, U.LastName 
                   FROM Comments C 
                   JOIN Users U ON C.UserID = U.UserID 
                   WHERE C.PostID = ? 
                   ORDER BY C.CreatedAt ASC";
    $commentStmt = $conn->prepare($commentSql);
    
    if ($commentStmt === false) {
        log_error('E5: Comment prepare failed: ' . htmlspecialchars($conn->error));
        echo json_encode(['status' => 'error', 'message' => 'Comment prepare failed: ' . htmlspecialchars($conn->error), 'code' => 'E5']);
        exit();
    }

    $commentStmt->bind_param("i", $postID);
    
    if ($commentStmt->execute() === false) {
        log_error('E6: Comment execute failed: ' . htmlspecialchars($commentStmt->error));
        echo json_encode(['status' => 'error', 'message' => 'Comment execute failed: ' . htmlspecialchars($commentStmt->error), 'code' => 'E6']);
        exit();
    }

    $commentResult = $commentStmt->get_result();
    $comments = [];
    
    while ($commentRow = $commentResult->fetch_assoc()) {
        $comments[] = $commentRow;
    }

    $post['Comments'] = $comments;
    echo json_encode($post);
} else {
    log_error('E7: Post not found');
    echo json_encode(['status' => 'error', 'message' => 'Post not found', 'code' => 'E7']);
}

$conn->close();
?>
