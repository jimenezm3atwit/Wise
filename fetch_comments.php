<?php
include 'db.php';

$postID = $_GET['postID'];

$sql = "SELECT C.CommentText, U.FirstName, U.LastName FROM Comments C JOIN Users U ON C.UserID = U.UserID WHERE C.PostID = ? ORDER BY C.CreatedAt DESC";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $postID);
$stmt->execute();
$result = $stmt->get_result();

$comments = [];
while ($row = $result->fetch_assoc()) {
    $comments[] = $row;
}

echo json_encode($comments);

$stmt->close();
$conn->close();
?>
