<?php
session_start();
if (!isset($_SESSION['username'])) {
    header("Location: login.php");
    exit();
}

include 'db.php';

if (isset($_GET['userid'])) {
    $userID = $_GET['userid'];
} else {
    $userID = $_SESSION['userid'];
}

$sql = "SELECT FirstName, LastName, ProfilePhoto, AboutMe FROM Users WHERE UserID = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $userID);
$stmt->execute();
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
    $profilePhoto = "uploads/default.jpg";
    $aboutMe = "This is the about me section.";
}

$stmt->close();

// Fetch user's posts
$sql = "SELECT P.PostID, P.MediaURL, P.Caption, U.FirstName, U.LastName 
        FROM Posts P 
        JOIN Users U ON P.UserID = U.UserID 
        WHERE U.UserID = ? 
        ORDER BY P.CreatedAt DESC";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $userID);
$stmt->execute();
$postsResult = $stmt->get_result();

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
                <li><a href="explore.php">Explore</a></li>
                <li><a href="#" id="createBtn">Create</a></li>
            </ul>
        </aside>
        <div class="profile-content">
            <div class="profile-header">
                <img src="<?php echo htmlspecialchars($profilePhoto); ?>" alt="Profile Photo" class="profile-photo">
                <h2><?php echo htmlspecialchars($firstName . " " . $lastName); ?></h2>
                <?php if ($userID == $_SESSION['userid']) { ?>
                    <div class="profile-actions">
                        <button class="btn" onclick="showEditModal()">Edit Profile</button>
                        <form action="logout.php" method="post" style="display: inline;">
                            <button type="submit" class="btn">Logout</button>
                        </form>
                    </div>
                <?php } ?>
            </div>
            <div id="edit-modal" class="modal">
                <div class="modal-content">
                    <span class="close" onclick="closeEditModal()">&times;</span>
                    <h2>Edit Profile</h2>
                    <button class="btn" onclick="showPhotoUpload()">Edit Profile Photo</button>
                    <button class="btn" onclick="showEditAboutMe()">Edit About Me</button>
                </div>
            </div>
            <div id="photo-upload-modal" class="modal">
                <div class="modal-content">
                    <span class="close" onclick="closePhotoUploadModal()">&times;</span>
                    <h2>Upload Profile Photo</h2>
                    <form action="upload_profile_photo.php" method="post" enctype="multipart/form-data" id="uploadForm">
                        <label for="fileToUpload" class="btn choose-photo-btn">Choose Photo</label>
                        <input type="file" name="fileToUpload" id="fileToUpload" style="display: none;" onchange="submitForm()">
                    </form>
                </div>
            </div>
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
            <div class="profile-stats">
                <div class="stats">
                    <span class="stat-number"><?php echo $postsResult->num_rows; ?></span> posts
                </div>
                <div class="stats">
                    <span class="stat-number">0</span> followers
                </div>
                <div class="stats">
                    <span class="stat-number">0</span> following
                </div>
            </div>
            <div class="profile-bio">
                <p><?php echo htmlspecialchars($aboutMe); ?></p>
            </div>
            <div class="profile-posts">
                <div class="post-content">
                    <div id="posts" class="grid">
                        <?php
                        if ($postsResult->num_rows > 0) {
                            while ($postRow = $postsResult->fetch_assoc()) {
                                echo "<div class='grid-item' data-postid='{$postRow['PostID']}'>";
                                echo "<img src='{$postRow['MediaURL']}' alt='Post Image' class='post-image'>";
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

    <script src="profile.js"></script>
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

        function submitForm() {
            const fileInput = document.getElementById('fileToUpload');
            if (fileInput.files.length > 0) {
                document.getElementById('uploadForm').submit();
            } else {
                alert('Please select a file to upload.');
            }
        }
    </script>
</body>
</html>
