<?php
session_start();
if (!isset($_SESSION['username'])) {
    header("Location: login.php");
    exit();
}

include 'db.php';

$userID = $_SESSION['userid'];
$sql = "SELECT FirstName, LastName FROM Users WHERE UserID = ?";
$stmt = $conn->prepare($sql);

if ($stmt === false) {
    die('Prepare failed: ' . htmlspecialchars($conn->error));
}

$stmt->bind_param("i", $userID);

if ($stmt->execute() === false) {
    die('Execute failed: ' . htmlspecialchars($stmt->error));
}

$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $row = $result->fetch_assoc();
    $firstName = htmlspecialchars($row['FirstName']);
    $lastName = htmlspecialchars($row['LastName']);
} else {
    $firstName = "User";
    $lastName = "";
}

$stmt->close();

// Fetch all posts
$sql = "SELECT P.PostID, P.MediaURL, P.Caption, P.Likes, U.FirstName, U.LastName 
        FROM Posts P 
        JOIN Users U ON P.UserID = U.UserID 
        ORDER BY P.CreatedAt DESC";
$result = $conn->query($sql);

if ($result === false) {
    die('Query failed: ' . htmlspecialchars($conn->error));
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Explore - WeatherWise</title>
    <link rel="stylesheet" href="explore.css">
</head>
<body>
    <div class="container">
        <aside class="sidebar">
            <ul>
                <li><a href="index.php">Home</a></li>
                <li><a href="search.php">Search</a></li>
                <li><a href="explore.php">Explore</a></li>
                <li><a href="notifications.php">Notifications</a></li>
                <li><a href="create.php">Create</a></li>
                <li><a href="profile.php">Profile</a></li>
            </ul>
        </aside>
        <div class="main-content">
            <header>
                <h2>Explore</h2>
            </header>
            <div class="grid">
                <?php
                if ($result->num_rows > 0) {
                    while ($row = $result->fetch_assoc()) {
                        echo "<div class='grid-item' data-postid='" . $row['PostID'] . "'>";
                        echo "<img src='" . htmlspecialchars($row['MediaURL']) . "' alt='Post Image'>";
                        echo "</div>";
                    }
                } else {
                    echo "<p>No posts to show.</p>";
                }
                ?>
            </div>
        </div>
    </div>

    <div id="postModal" class="modal">
        <div class="modal-content">
            <span class="close" onclick="closeCreateModal()">&times;</span>
            <div id="postDetails"></div>
        </div>
    </div>

    <script src="explore.js"></script>
</body>
</html>
