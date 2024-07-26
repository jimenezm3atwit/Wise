<?php
include 'db.php';

if (isset($_GET['postID'])) {
    $postID = $_GET['postID'];

    // Fetch post details
    $sql = "SELECT P.PostID, P.MediaURL, P.Caption, P.Likes, U.FirstName, U.LastName, U.UserID 
            FROM Posts P 
            JOIN Users U ON P.UserID = U.UserID 
            WHERE P.PostID = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $postID);

    if ($stmt->execute()) {
        $result = $stmt->get_result();
        if ($result->num_rows > 0) {
            $post = $result->fetch_assoc();
            
            // Fetch comments for the post
            $sql_comments = "SELECT C.CommentID, C.Comment AS CommentText, C.UserID, U.FirstName, U.LastName 
                             FROM Comments C 
                             JOIN Users U ON C.UserID = U.UserID 
                             WHERE C.PostID = ? 
                             ORDER BY C.CreatedAt ASC";
            $stmt_comments = $conn->prepare($sql_comments);
            $stmt_comments->bind_param("i", $postID);
            $stmt_comments->execute();
            $result_comments = $stmt_comments->get_result();

            $comments = [];
            while ($row_comment = $result_comments->fetch_assoc()) {
                $comments[] = $row_comment;
            }

            $post['Comments'] = $comments;
            echo json_encode(['status' => 'success', 'post' => $post]);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Post not found']);
        }
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Error executing query']);
    }

    $stmt->close();
    $conn->close();
} else {
    echo json_encode(['status' => 'error', 'message' => 'Invalid request']);
}
?>
