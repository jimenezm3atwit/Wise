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

// Fetch advisories
$advisorySql = "SELECT UA.Description, U.FirstName, U.LastName FROM User_Advisories UA JOIN Users U ON UA.UserID = U.UserID ORDER BY UA.DateReported DESC";
$advisoryResult = $conn->query($advisorySql);

$conn->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Main Page - Weather Advisory App</title>
    <link rel="stylesheet" href="styles.css">
    <script src="https://maps.googleapis.com/maps/api/js?key=AIzaSyC28g17oi2QqW1fuRQnGgO8TzP0w8U59Zg&callback=initMap" async defer></script> 
    <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
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
            width: 40%;
            border-radius: 15px; /* Rounded corners */
            box-shadow: 0 4px 8px 0 rgba(0,0,0,0.2);
            text-align: center;
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
        .btn {
            background-color: #3b7a57; /* Green button */
            border: none;
            color: white;
            padding: 10px 20px;
            margin: 10px;
            cursor: pointer;
            border-radius: 4px;
            font-size: 16px;
        }
        .btn:hover {
            background-color: #3b7a57; /* Darker green on hover */
        }

        /* Styling for activity feed */
        #activity-container {
            height: 500px; /* Same height as the map */
            overflow-y: auto; /* Enable vertical scrolling */
        }

        .advisory {
            word-wrap: break-word; /* Ensures long words break properly */
            margin-bottom: 10px;
            border-bottom: 1px solid #ddd; /* Adds a bottom border to each advisory */
            padding-bottom: 10px; /* Adds some padding below the text */
            padding: 10px;
            background-color: #f9f9f9; /* Light background color for advisory */
            border-radius: 5px; /* Rounded corners for advisory box */
        }
    </style>
</head>
<body>
    <div class="container">
        <aside class="sidebar">
            <ul>
                <li><a href="explore.php">Explore</a></li>
                <li><a href="#" id="createBtn">Create</a></li>
                <li><a href="profile.php">Profile</a></li>
            </ul>
        </aside>
        <div class="main-content">
            <header>
                <div class="input-box">
                    <input id="input" type="text" placeholder="Enter location" required aria-label="Location">
                    <i class='bx bx-search'></i>
                    <button id="submit" onclick="fetchWeather()">Submit</button>
                </div>
                <div class="user-info">
                    <div class="user-icon"><?php echo $firstName . " " . $lastName; ?></div>
                    <form action="logout.php" method="post" style="display:inline;">
                        <button type="submit" class="btn">Logout</button>
                    </form>
                </div>
            </header>
            <div class="body-content">
                <div class ="left-side">
                    <div id="map-container">
                        <div id="map" style="height: 500px;"></div>
                    </div>
                    <div id="suggested-container">
                        <div id = "suggested2">
                            <h3>Suggested Activities</h3>
                            <div id="suggested"></div>
                        </div>
                    </div>
                </div>
                <div class="right-side">
                    <div id="weather-container">
                        <div id="weather">
                            <p id="city"></p>
                            <p id="error"></p>
                            <p id="temperature"></p>
                            <p id="daily"></p>
                            <p id="humidity"></p>
                            <p id="wind"></p>
                            <p id="sun"></p>
                            <p id="conditions"></p>
                            <div class="video-background">
                                <video id="myVideo" autoplay muted loop>
                                    <source id="videoSource" src="" type="video/mp4">
                                </video>
                            </div>
                        </div>
                    </div>
                    <div id="activity-container">
                        <div id="activity">
                            <h3>Activity Feed</h3>
                            <?php
                            if ($advisoryResult->num_rows > 0) {
                                while ($advisoryRow = $advisoryResult->fetch_assoc()) {
                                    echo "<div class='advisory'>";
                                    echo "<p><strong>" . htmlspecialchars($advisoryRow['FirstName']) . " " . htmlspecialchars($advisoryRow['LastName']) . ":</strong> " . nl2br(htmlspecialchars($advisoryRow['Description'])) . "</p>";
                                    echo "</div>";
                                }
                            } else {
                                echo "<p>No advisories reported yet.</p>";
                            }
                            ?>
                        </div>
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
                <form id="createPost" method="post" enctype="multipart/form-data">
                    <input type="file" name="postMedia" accept="image/*,video/*" required>
                    <textarea name="caption" placeholder="Write a caption..." required></textarea>
                    <button type="submit" class="btn">Upload</button>
                </form>
            </div>

            <!-- Report Advisory Form -->
            <div id="reportAdvisoryForm" class="create-form" style="display:none;">
                <h3>Report an Advisory</h3>
                <form id="reportAdvisory" method="post">
                    <textarea name="advisoryText" placeholder="Write your advisory..." required></textarea>
                    <button type="submit" class="btn">Submit</button>
                </form>
            </div>
        </div>
    </div>

    <script src="main.js"></script>
    <script>
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

        // Initialize map on load
        initMap();
    </script>
</body>
</html>
