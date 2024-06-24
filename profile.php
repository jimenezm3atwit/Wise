<?php
session_start();
if (!isset($_SESSION['userid'])) {
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
    $profilePhoto = "default.jpg"; // Use a default profile photo
}

$stmt->close();
$conn->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profile - Weather Advisory App</title>
    <link rel="stylesheet" href="pfstyles.css">
</head>
<body>
    <div class="profile-container">
        <div class="sidebar">
            <ul>
                <li><a href="index.php">Home</a></li>
                <li><a href="#">Search</a></li>
                <li><a href="#">Explore</a></li>
                <li><a href="#">Notifications</a></li>
                <li><a href="#">Create</a></li>
                <li><a href="profile.php">Profile</a></li>
            </ul>
        </div>
        <div class="profile-main">
            <div class="profile-header">
                <img src="uploads/<?php echo htmlspecialchars($profilePhoto); ?>" alt="Profile Photo" class="profile-photo">
                <div class="profile-info">
                    <h1><?php echo htmlspecialchars($firstName . " " . $lastName); ?></h1>
                    <div class="profile-buttons">
                        <button class="edit-profile" onclick="document.getElementById('editProfileModal').style.display='block'">Edit Profile</button>
                        <button class="view-archive">View Archive</button>
                    </div>
                    <form action="logout.php" method="post" style="display:inline;">
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
                    <button class="post-tab active">Posts</button>
                    <button class="post-tab">Saved</button>
                    <button class="post-tab">Tagged</button>
                </div>
                <div class="post-content">
                    <!-- User posts will be displayed here -->
                    <div class="post"><i class='bx bx-plus'></i> New</div>
                </div>
            </div>
        </div>
    </div>

    <!-- Edit Profile Modal -->
    <div id="editProfileModal" class="modal">
        <div class="modal-content">
            <span class="close" onclick="document.getElementById('editProfileModal').style.display='none'">&times;</span>
            <h2>Edit Profile</h2>
            <form action="upload.php" method="post" enctype="multipart/form-data">
                <label for="profilePhoto">Upload New Profile Picture:</label>
                <input type="file" name="profilePhoto" id="profilePhoto" accept="image/*" required>
                <button type="submit" class="btn">Upload</button>
            </form>
        </div>
    </div>

    <script>
        // Get the modal
        var modal = document.getElementById('editProfileModal');

        // When the user clicks anywhere outside of the modal, close it
        window.onclick = function(event) {
            if (event.target == modal) {
                modal.style.display = "none";
            }
        }
    </script>
</body>
</html>
