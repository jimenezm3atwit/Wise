<?php
include 'db.php';

$postID = $_GET['postID'] ?? null;

if ($postID === null) {
    echo json_encode(['status' => 'error', 'message' => 'Missing post ID']);
    exit();
}

$sql = "SELECT P.PostID, P.MediaURL, P.Caption, P.Likes, U.FirstName, U.LastName 
        FROM Posts P 
        JOIN Users U ON P.UserID = U.UserID 
        WHERE P.PostID = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $postID);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $post = $result->fetch_assoc();

    // Fetch comments
    $commentSql = "SELECT C.CommentText, U.FirstName, U.LastName 
                   FROM Comments C 
                   JOIN Users U ON C.UserID = U.UserID 
                   WHERE C.PostID = ? 
                   ORDER BY C.CreatedAt DESC";
    $commentStmt = $conn->prepare($commentSql);
    $commentStmt->bind_param("i", $postID);
    $commentStmt->execute();
    $commentResult = $commentStmt->get_result();

    $comments = [];
    while ($commentRow = $commentResult->fetch_assoc()) {
        $comments[] = $commentRow;
    }

    $post['Comments'] = $comments;

    echo json_encode($post);
} else {
    echo json_encode(['status' => 'error', 'message' => 'Post not found']);
}

$stmt->close();
$conn->close();
?>
