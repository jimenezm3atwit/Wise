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
$sql = "SELECT P.PostID, P.MediaURL, P.Caption, U.FirstName, U.LastName, U.UserID
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
                <li><a href="#" id="createBtn">Create</a></li>
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
                        echo "<div class='grid-item' data-postid='{$row['PostID']}'>";
                        echo "<img src='{$row['MediaURL']}' alt='Post Image'>";
                        echo "<p>{$row['Caption']}</p>";
                        echo "<p>by <a href='profile.php?userid={$row['UserID']}'>{$row['FirstName']} {$row['LastName']}</a></p>";
                        echo "</div>";
                    }
                }
                ?>
            </div>
        </div>
    </div>

    <!-- Modal for post details -->
    <div id="postModal" class="modal">
        <div class="modal-content">
            <span class="close">&times;</span>
            <div id="postDetails"></div>
        </div>
    </div>

    <!-- Create Modal -->
    <div id="createModal" class="modal">
        <div class="modal-content">
            <span class="close" onclick="closeCreateModal()">&times;</span>
            <h2>Create New Content</h2>
            <button class="btn" onclick="openCreatePostForm()">Create a Post</button>

            <!-- Create Post Form -->
            <div id="createPostForm" class="create-form" style="display:none;">
                <h3>Create a Post</h3>
                <form id="createPost" method="post" enctype="multipart/form-data">
                    <input type="file" name="postMedia" accept="image/*,video/*" required>
                    <textarea name="caption" placeholder="Write a caption..." required></textarea>
                    <button type="submit" class="btn">Upload</button>
                </form>
            </div>
        </div>
    </div>

    <script src="explore.js"></script>
    <script>
        function openCreateModal() {
            document.getElementById('createModal').style.display = 'block';
        }

        function closeCreateModal() {
            document.getElementById('createModal').style.display = 'none';
            document.getElementById('createPostForm').style.display = 'none';
        }

        function openCreatePostForm() {
            document.getElementById('createPostForm').style.display = 'block';
        }

        document.getElementById('createBtn').addEventListener('click', function() {
            openCreateModal();
        });

        window.onclick = function(event) {
            const createModal = document.getElementById('createModal');
            if (event.target == createModal) {
                createModal.style.display = 'none';
            }
        }
    </script>
</body>
</html>
