<?php
include 'db.php';
session_start();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $currentUserID = $_SESSION['userid'];
    $followingID = $_POST['followingID'];

    // Check if the user is already following
    $sql = "SELECT * FROM Follows WHERE FollowerID = ? AND FollowingID = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ii", $currentUserID, $followingID);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        echo json_encode(['status' => 'error', 'message' => 'Already following']);
    } else {
        // Insert follow record
        $sql = "INSERT INTO Follows (FollowerID, FollowingID) VALUES (?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ii", $currentUserID, $followingID);

        if ($stmt->execute()) {
            echo json_encode(['status' => 'success', 'message' => 'Follow added']);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Error adding follow']);
        }

        $stmt->close();
    }

    $conn->close();
} else {
    echo json_encode(['status' => 'error', 'message' => 'Invalid request']);
}
?>
