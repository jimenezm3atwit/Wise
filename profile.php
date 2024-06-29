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
    $profilePhoto = htmlspecialchars($row['ProfilePhoto']);
} else {
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
    <style>
        /* Add some styles for the modal */
        .modal {
            display: none;
            position: fixed;
            z-index: 1;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            overflow: auto;
            background-color: rgba(0,0,0,0.4);
            padding-top: 60px;
        }
        .modal-content {
            background-color: #fefefe;
            margin: 5% auto;
            padding: 20px;
            border: 1px solid #888;
            border-radius: 15px;
            width: 50%;
            max-width: 600px;
            box-shadow: 0px 0px 10px 0px rgba(0, 0, 0, 0.2);
        }
        .close {
            color: #aaa;
            float: right;
            font-size: 28px;
            font-weight: bold;
        }
        .close:hover,
        .close:focus {
            color: black;
            text-decoration: none;
            cursor: pointer;
        }
        .modal h2 {
            margin-top: 0;
            text-align: center;
        }
        .modal .btn {
            background-color: #4CAF50; /* Green button */
            border: none;
            color: white;
            padding: 10px 20px;
            margin: 10px auto;
            cursor: pointer;
            border-radius: 4px;
            display: block;
            width: fit-content;
        }
        .modal .btn:hover {
            background-color: #45A049; /* Darker green on hover */
        }
        .create-form {
            text-align: center;
        }
        .create-form input[type="file"],
        .create-form textarea {
            display: block;
            margin: 10px auto;
            padding: 10px;
            width: 80%;
            max-width: 500px;
            border: 1px solid #ccc;
            border-radius: 4px;
        }
    </style>
</head>
<body>
    <div class="container">
        <aside class="sidebar">
            <ul>
                <li><a href="index.php">Home</a></li>
                <li><a href="#">Search</a></li>
                <li><a href="#">Explore</a></li>
                <li><a href="#">Notifications</a></li>
                <li><a href="#" id="createBtn">Create</a></li>
                <li><a href="profile.php">Profile</a></li>
            </ul>
        </aside>
        <div class="profile-content">
            <div class="profile-header">
                <img src="<?php echo $profilePhoto; ?>" alt="Profile Photo" class="profile-photo">
                <h2><?php echo $firstName . " " . $lastName; ?></h2>
                <div class="profile-actions">
                    <button class="btn" id="editProfileBtn">Edit Profile</button>
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
                <p><?php echo $firstName; ?> ABOUT ME SECTION</p>
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

    <!-- Create Modal -->
    <div id="createModal" class="modal">
        <div class="modal-content">
            <span class="close" onclick="closeCreateModal()">&times;</span>
            <h2>Create New Content</h2>
            <button class="btn" onclick="openCreatePostForm()">Create a Post</button>
            <button class="btn" onclick="openReportAdvisoryForm()">Report an Advisory</button>

            <!-- Create Post Form -->
            <div id="createPostForm" class="create-form" style="display:none;">
                <h3>Create a Post</h3>
                <form action="upload_post.php" method="post" enctype="multipart/form-data">
                    <input type="file" name="postMedia" accept="image/*,video/*" required>
                    <textarea name="caption" placeholder="Write a caption..." required></textarea>
                    <button type="submit" class="btn">Upload</button>
                </form>
            </div>

            <!-- Report Advisory Form -->
            <div id="reportAdvisoryForm" class="create-form" style="display:none;">
                <h3>Report an Advisory</h3>
                <form action="report_advisory.php" method="post">
                    <textarea name="advisoryText" placeholder="Write your advisory..." required></textarea>
                    <button type="submit" class="btn">Submit</button>
                </form>
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

        function openCreateModal() {
            document.getElementById('createModal').style.display = 'block';
        }

        function closeCreateModal() {
            document.getElementById('createModal').style.display = 'none';
            document.getElementById('createPostForm').style.display = 'none';
            document.getElementById('reportAdvisoryForm').style.display = 'none';
        }

        function openCreatePostForm() {
            document.getElementById('createPostForm').style.display = 'block';
            document.getElementById('reportAdvisoryForm').style.display = 'none';
        }

        function openReportAdvisoryForm() {
            document.getElementById('createPostForm').style.display = 'none';
            document.getElementById('reportAdvisoryForm').style.display = 'block';
        }

        document.getElementById('createBtn').addEventListener('click', function() {
            openCreateModal();
        });

        // Default to showing posts
        showPosts();
    </script>
</body>
</html>
