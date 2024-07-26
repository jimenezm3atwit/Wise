<?php
include 'db.php';
session_start();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $currentUserID = $_SESSION['userid'];
    $followingID = $_POST['followingID'];

    // Delete follow record
    $sql = "DELETE FROM Follows WHERE FollowerID = ? AND FollowingID = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ii", $currentUserID, $followingID);

    if ($stmt->execute()) {
        echo json_encode(['status' => 'success', 'message' => 'Follow removed']);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Error removing follow']);
    }

    $stmt->close();
    $conn->close();
} else {
    echo json_encode(['status' => 'error', 'message' => 'Invalid request']);
}
?>
