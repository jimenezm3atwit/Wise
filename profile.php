<?php
session_start();
if (!isset($_SESSION['username'])) {
    header("Location: login.php");
    exit();
}

include 'db.php';

$currentUserID = $_SESSION['userid'];
$profileUserID = isset($_GET['userid']) ? $_GET['userid'] : $currentUserID;

$sql = "SELECT FirstName, LastName, ProfilePhoto, AboutMe FROM Users WHERE UserID = ?";
$stmt = $conn->prepare($sql);

if ($stmt === false) {
    die('Prepare failed: ' . htmlspecialchars($conn->error));
}

$stmt->bind_param("i", $profileUserID);

if ($stmt->execute() === false) {
    die('Execute failed: ' . htmlspecialchars($stmt->error));
}

$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $row = $result->fetch_assoc();
    $firstName = $row['FirstName'];
    $lastName = $row['LastName'];
    $profilePhoto = $row['ProfilePhoto'];
    $aboutMe = $row['AboutMe'];
} else {
    $firstName = "User";
    $lastName = "";
    $profilePhoto = "uploads/default.jpg"; // Default profile picture
    $aboutMe = "This is the about me section.";
}

$stmt->close();

// Fetch post, follower, and following counts
$postCount = 0;
$followerCount = 0;
$followingCount = 0;

$sql = "SELECT COUNT(*) as PostCount FROM Posts WHERE UserID = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $profileUserID);
$stmt->execute();
$result = $stmt->get_result();
if ($result->num_rows > 0) {
    $row = $result->fetch_assoc();
    $postCount = $row['PostCount'];
}
$stmt->close();

$sql = "SELECT COUNT(*) as FollowerCount FROM Follows WHERE FollowingID = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $profileUserID);
$stmt->execute();
$result = $stmt->get_result();
if ($result->num_rows > 0) {
    $row = $result->fetch_assoc();
    $followerCount = $row['FollowerCount'];
}
$stmt->close();

$sql = "SELECT COUNT(*) as FollowingCount FROM Follows WHERE FollowerID = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $profileUserID);
$stmt->execute();
$result = $stmt->get_result();
if ($result->num_rows > 0) {
    $row = $result->fetch_assoc();
    $followingCount = $row['FollowingCount'];
}
$stmt->close();

// Fetch all posts by the user
$sql = "SELECT P.PostID, P.MediaURL, P.Caption, P.Likes 
        FROM Posts P 
        WHERE P.UserID = ? 
        ORDER BY P.CreatedAt DESC";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $profileUserID);

if ($stmt->execute() === false) {
    die('Execute failed: ' . htmlspecialchars($stmt->error));
}

$result = $stmt->get_result();
$posts = [];

if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $posts[] = $row;
    }
}

$stmt->close();

// Check if the current user is already following this profile user
$isFollowing = false;
if ($profileUserID !== $currentUserID) {
    $sql = "SELECT * FROM Follows WHERE FollowerID = ? AND FollowingID = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ii", $currentUserID, $profileUserID);
    $stmt->execute();
    $result = $stmt->get_result();
    $isFollowing = $result->num_rows > 0;
    $stmt->close();
}

$conn->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profile - WeatherWise</title>
    <link rel="stylesheet" href="pfstyles.css">
    <style>
        .unfollow-btn {
            background-color: red;
        }
    </style>
</head>
<body>
    <div class="container">
        <aside class="sidebar">
            <ul>
                <li><a href="index.php">Home</a></li>
                <li><a href="explore.php">Explore</a></li>
                <li><a href="#" id="createPostLink">Create</a></li>
            </ul>
        </aside>
        <div class="profile-content">
            <div class="profile-header">
                <img src="<?php echo htmlspecialchars($profilePhoto); ?>" alt="Profile Photo" class="profile-photo">
                <h2><?php echo htmlspecialchars($firstName . " " . $lastName); ?></h2>
                <p><?php echo htmlspecialchars($aboutMe); ?></p>
                <div class="profile-actions">
                    <?php if ($profileUserID === $currentUserID): ?>
                        <button class="btn" onclick="showEditModal()">Edit Profile</button>
                        <form action="logout.php" method="post" style="display: inline;">
                            <button type="submit" class="btn">Logout</button>
                        </form>
                    <?php elseif ($profileUserID !== $currentUserID): ?>
                        <button class="btn <?php echo $isFollowing ? 'unfollow-btn' : ''; ?>" id="followBtn" data-userid="<?php echo $profileUserID; ?>">
                            <?php echo $isFollowing ? 'Unfollow' : 'Follow'; ?>
                        </button>
                    <?php endif; ?>
                </div>
                <div class="profile-stats">
                    <div class="stat">
                        <span class="stat-number"><?php echo $postCount; ?></span> posts
                    </div>
                    <div class="stat">
                        <span class="stat-number" id="followerCount"><?php echo $followerCount; ?></span> followers
                    </div>
                    <div class="stat">
                        <span class="stat-number"><?php echo $followingCount; ?></span> following
                    </div>
                </div>
            </div>
            <div class="profile-posts">
                <h3>Posts</h3>
                <div class="grid">
                    <?php
                    if (!empty($posts)) {
                        foreach ($posts as $post) {
                            echo "<div class='grid-item' data-postid='{$post['PostID']}'>";
                            echo "<img src='{$post['MediaURL']}' alt='Post Image'>";
                            echo "<p>{$post['Caption']}</p>";
                            echo "</div>";
                        }
                    } else {
                        echo "<p>No posts yet.</p>";
                    }
                    ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Edit Profile Modal -->
    <div id="edit-modal" class="modal">
        <div class="modal-content">
            <span class="close" onclick="closeEditModal()">&times;</span>
            <h2>Edit Profile</h2>
            <button class="btn" onclick="showPhotoUpload()">Edit Profile Photo</button>
            <button class="btn" onclick="showEditAboutMe()">Edit About Me</button>
        </div>
    </div>

    <!-- Upload Photo Modal -->
    <div id="photo-upload-modal" class="modal">
        <div class="modal-content">
            <span class="close" onclick="closePhotoUploadModal()">&times;</span>
            <h2>Upload Profile Photo</h2>
            <form action="upload.php" method="post" enctype="multipart/form-data" id="uploadProfilePhotoForm">
                <label for="profilePhotoUpload" class="btn">Choose Photo</label>
                <input type="file" name="fileToUpload" id="profilePhotoUpload" style="display: none;" onchange="submitProfilePhotoForm()">
            </form>
        </div>
    </div>

    <!-- Edit About Me Modal -->
    <div id="about-me-modal" class="modal">
        <div class="modal-content">
            <span class="close" onclick="closeAboutMeModal()">&times;</span>
            <h2>Edit About Me</h2>
            <form action="update_about_me.php" method="post">
                <textarea name="about_me" rows="5" cols="50"><?php echo htmlspecialchars($aboutMe); ?></textarea>
                <button type="submit" class="btn">Save</button>
            </form>
        </div>
    </div>

    <!-- Create Modal -->
    <div id="createPostModal" class="modal">
        <div class="modal-content">
            <span class="close" id="closeCreatePostModal">&times;</span>
            <h2>Create New Post</h2>
            <form id="createPostForm" method="post" enctype="multipart/form-data">
                <input type="file" name="postMedia" accept="image/*,video/*" required>
                <textarea name="caption" placeholder="Write a caption..." required></textarea>
                <button type="submit" class="btn">Upload</button>
            </form>
        </div>
    </div>

    <!-- Modal for post details -->
    <div id="postModal" class="modal">
        <div class="modal-content">
            <span class="close">&times;</span>
            <div id="postDetails"></div>
        </div>
    </div>

    <script src="profile.js"></script>
    <script>
        function showEditModal() {
            document.getElementById('edit-modal').style.display = 'block';
        }

        function closeEditModal() {
            document.getElementById('edit-modal').style.display = 'none';
        }

        function showPhotoUpload() {
            closeEditModal();
            document.getElementById('photo-upload-modal').style.display = 'block';
        }

        function closePhotoUploadModal() {
            document.getElementById('photo-upload-modal').style.display = 'none';
        }

        function showEditAboutMe() {
            closeEditModal();
            document.getElementById('about-me-modal').style.display = 'block';
        }

        function closeAboutMeModal() {
            document.getElementById('about-me-modal').style.display = 'none';
        }

        function submitProfilePhotoForm() {
            const fileInput = document.getElementById('profilePhotoUpload');
            if (fileInput.files.length > 0) {
                document.getElementById('uploadProfilePhotoForm').submit();
            } else {
                alert('Please select a file to upload.');
            }
        }
    </script>
</body>
</html>
