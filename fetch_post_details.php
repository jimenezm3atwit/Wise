<?php
include 'db.php';

if (!isset($_GET['postID'])) {
    die('Post ID is required');
}

$postID = intval($_GET['postID']);

$sql = "SELECT P.PostID, P.MediaURL, P.Caption, P.Likes, U.FirstName, U.LastName 
        FROM Posts P 
        JOIN Users U ON P.UserID = U.UserID 
        WHERE P.PostID = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $postID);
$stmt->execute();
$result = $stmt->get_result();
$post = $result->fetch_assoc();

$commentsSql = "SELECT C.Comment, U.FirstName, U.LastName 
                FROM Comments C 
                JOIN Users U ON C.UserID = U.UserID 
                WHERE C.PostID = ? 
                ORDER BY C.CreatedAt ASC";
$commentsStmt = $conn->prepare($commentsSql);
$commentsStmt->bind_param("i", $postID);
$commentsStmt->execute();
$commentsResult = $commentsStmt->get_result();

$comments = [];
while ($comment = $commentsResult->fetch_assoc()) {
    $comments[] = $comment;
}

$post['Comments'] = $comments;

header('Content-Type: application/json');
echo json_encode($post);

$stmt->close();
$commentsStmt->close();
$conn->close();
?>
