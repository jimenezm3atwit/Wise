<?php
session_start();
if (!isset($_SESSION['username'])) {
    header("Location: login.php");
    exit();
}

include 'db.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $aboutMe = $_POST['about_me'];
    $userID = $_SESSION['userid'];

    $sql = "UPDATE Users SET AboutMe = ? WHERE UserID = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("si", $aboutMe, $userID);

    if ($stmt->execute()) {
        header("Location: profile.php");
    } else {
        echo "Sorry, there was an error updating your About Me section.";
    }

    $stmt->close();
    $conn->close();
}
?>
