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
$stmt->bind_param("i", $userID);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $row = $result->fetch_assoc();
    $firstName = $row['FirstName'];
    $lastName = $row['LastName'];
} else {
    // Handle case where user is not found, for now, we'll just set defaults
    $firstName = "User";
    $lastName = "";
}

$stmt->close();
$conn->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Main Page - Weather Advisory App</title>
    <link rel="stylesheet" href="styles.css">
    <script src="https://maps.googleapis.com/maps/api/js?key=AIzaSyAnnTUI-fzM3lyIilxG8EGYr9iGEbpdveM&callback=initMap" async defer></script>
    <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
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
                <li><a href="#">Profile</a></li>
            </ul>
        </aside>
        <div class="main-content">
            <header>
                <div class="input-box">
                    <input id="input" type="text" placeholder="Location" required aria-label="Location">
                    <i class='bx bx-search'></i>
                    <button id="submit" onclick="fetchWeather()">Submit</button>
                </div>
                <div class="user-info">
                    <div class="user-icon"><?php echo htmlspecialchars($firstName . " " . $lastName); ?></div>
                    <form action="logout.php" method="post" style="display:inline;">
                        <button type="submit" class="btn">Logout</button>
                    </form>
                </div>
            </header>
            <div class="body-content">
                <div id="map-container">
                    <div id="map"></div>
                </div>
                <div class="right-side">
                    <div id="weather-container">
                        <div id="weather">
                            <p id="city"></p>
                            <p id="error"></p>
                            <p id="temperature"></p>
                            <p id="daily"></p>
                            <p id="humidity"></p>
                            <p id="1hrRain"></p>
                            <p id="sun"></p>
                        </div>
                    </div>
                    <div id="activity-container">
                        <div id="activity">
                            <p>Activity Feed Here</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <script src="main.js"></script>
</body>
</html>
//run
