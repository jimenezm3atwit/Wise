<?php
include 'db.php';

if (isset($_GET['postID'])) {
    $postID = $_GET['postID'];

    // Fetch post details
    $postSql = "SELECT P.PostID, P.MediaURL, P.Caption, P.Likes, U.FirstName, U.LastName, U.UserID
                FROM Posts P 
                JOIN Users U ON P.UserID = U.UserID 
                WHERE P.PostID = ?";
    $stmt = $conn->prepare($postSql);
    if ($stmt) {
        $stmt->bind_param("i", $postID);
        $stmt->execute();
        $result = $stmt->get_result();
        if ($result->num_rows > 0) {
            $post = $result->fetch_assoc();
        } else {
            echo json_encode(["status" => "error", "message" => "Post not found"]);
            exit();
        }
        $stmt->close();
    } else {
        echo json_encode(["status" => "error", "message" => "Failed to prepare post statement: " . $conn->error]);
        exit();
    }

    // Fetch comments
    $commentsSql = "SELECT C.CommentID, C.CommentText, U.FirstName, U.LastName, U.UserID
                    FROM Comments C
                    JOIN Users U ON C.UserID = U.UserID
                    WHERE C.PostID = ?
                    ORDER BY C.CreatedAt ASC";
    $stmt = $conn->prepare($commentsSql);
    if ($stmt) {
        $stmt->bind_param("i", $postID);
        $stmt->execute();
        $commentsResult = $stmt->get_result();
        $comments = [];
        while ($row = $commentsResult->fetch_assoc()) {
            $comments[] = $row;
        }
        $stmt->close();
    } else {
        echo json_encode(["status" => "error", "message" => "Failed to prepare comments statement: " . $conn->error]);
        exit();
    }

    // Return post details and comments
    echo json_encode([
        "status" => "success",
        "post" => $post,
        "comments" => $comments
    ]);
} else {
    echo json_encode(["status" => "error", "message" => "No postID provided"]);
}
$conn->close();
?>
