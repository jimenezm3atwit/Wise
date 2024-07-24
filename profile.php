<?php
session_start();
if (!isset($_SESSION['username'])) {
    header("Location: login.php");
    exit();
}

include 'db.php';

$userID = $_SESSION['userid'];
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
    // Handle case where user is not found, for now, we'll just set defaults
    $firstName = "User";
    $lastName = "";
    $profilePhoto = "uploads/default.jpg"; // Default profile picture
    $aboutMe = "This is the about me section.";
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
                <li><a href="#">Explore</a></li>
                <li><a href="#">Create</a></li>
            </ul>
        </aside>
        <div class="profile-content">
            <div class="profile-header">
                <img src="<?php echo htmlspecialchars($profilePhoto); ?>" alt="Profile Photo" class="profile-photo">
                <h2><?php echo htmlspecialchars($firstName . " " . $lastName); ?></h2>
                <div class="profile-actions">
                    <button class="btn" onclick="showEditModal()">Edit Profile</button>
                    <button class="btn">View Archive</button>
                    <form action="logout.php" method="post" style="display: inline;">
                        <button type="submit" class="btn">Logout</button>
                    </form>
                </div>
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
                    <form action="upload.php" method="post" enctype="multipart/form-data" id="uploadForm">
                        <label for="fileToUpload" class="btn">Choose Photo</label>
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
                <p><?php echo htmlspecialchars($aboutMe); ?></p>
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
            document.getElementById('posts').style.display = 'block';
            document.getElementById('saved').style.display = 'none';
            document.getElementById('tagged').style.display = 'block';
        }

        // Default to showing posts
        showPosts();

        // Close the modal if the user clicks outside of it
        window.onclick = function(event) {
            const editModal = document.getElementById('edit-modal');
            const photoUploadModal = document.getElementById('photo-upload-modal');
            const aboutMeModal = document.getElementById('about-me-modal');
            if (event.target == editModal) {
                editModal.style.display = 'none';
            }
            if (event.target == photoUploadModal) {
                photoUploadModal.style.display = 'none';
            }
            if (event.target == aboutMeModal) {
                aboutMeModal.style.display = 'none';
            }
        }
    </script>
</body>
</html>
