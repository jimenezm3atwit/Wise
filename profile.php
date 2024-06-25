<?php
session_start();
if (!isset($_SESSION['username'])) {
    header("Location: login.php");
    exit();
}

include 'db.php';

$userID = $_SESSION['userid'];
$sql = "SELECT FirstName, LastName, ProfilePhoto FROM Users WHERE UserID = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $userID);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $row = $result->fetch_assoc();
    $firstName = $row['FirstName'];
    $lastName = $row['LastName'];
    $profilePhoto = $row['ProfilePhoto'];
} else {
    // Handle case where user is not found, for now, we'll just set defaults
    $firstName = "User";
    $lastName = "";
    $profilePhoto = "uploads/default.jpg"; // Default profile picture
}

$stmt->close();
$conn->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profile - WeatherWise</title>
    <link rel="stylesheet" href="pfstyles.css">
</head>
<body>
    <div class="container">
        <aside class="sidebar">
            <ul>
                <li><a href="index.php">Home</a></li>
                <li><a href="#">Search</a></li>
                <li><a href="#">Explore</a></li>
                <li><a href="#">Notifications</a></li>
                <li><a href="#">Create</a></li>
            </ul>
        </aside>
        <div class="profile-content">
            <div class="profile-header">
                <img src="<?php echo htmlspecialchars($profilePhoto); ?>" alt="Profile Photo" class="profile-photo">
                <h2><?php echo htmlspecialchars($firstName . " " . $lastName); ?></h2>
                <div class="profile-actions">
                    <form action="upload.php" method="post" enctype="multipart/form-data" style="display: inline;">
                        <label for="fileToUpload" class="btn">Edit Profile</label>
                        <input type="file" name="fileToUpload" id="fileToUpload" style="display: none;" onchange="this.form.submit()">
                    </form>
                    <button class="btn">View Archive</button>
                    <form action="logout.php" method="post" style="display: inline;">
                        <button type="submit" class="btn">Logout</button>
                    </form>
                </div>
            </div>
            <div class="profile-stats">
                <div class="stats">
                    <span class="stat-number">0</span> posts
                </div>
                <div class="stats">
                    <span class="stat-number">0</span> followers
                </div>
                <div class="stats">
                    <span class="stat-number">0</span> following
                </div>
            </div>
            <div class="profile-bio">
                <p><?php echo htmlspecialchars($firstName); ?> ABOUT ME SECTION</p>
            </div>
            <div class="profile-posts">
                <div class="post-header">
                    <button class="post-tab active" onclick="showPosts()">Posts</button>
                    <button class="post-tab" onclick="showSaved()">Saved</button>
                    <button class="post-tab" onclick="showTagged()">Tagged</button>
                </div>
                <div class="post-content">
                    <div id="posts" class="post active">
                        <p>New</p>
                    </div>
                    <div id="saved" class="post">
                        <p>Saved Content</p>
                    </div>
                    <div id="tagged" class="post">
                        <p>Tagged Content</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <script>
        function showPosts() {
            document.getElementById('posts').style.display = 'block';
            document.getElementById('saved').style.display = 'none';
            document.getElementById('tagged').style.display = 'none';
        }

        function showSaved() {
            document.getElementById('posts').style.display = 'none';
            document.getElementById('saved').style.display = 'block';
            document.getElementById('tagged').style.display = 'none';
        }

        function showTagged() {
            document.getElementById('posts').style.display = 'none';
            document.getElementById('saved').style.display = 'none';
            document.getElementById('tagged').style.display = 'block';
        }

        // Default to showing posts
        showPosts();
    </script>
</body>
</html>
