<?php
session_start();
if (!isset($_SESSION['username'])) {
    header("Location: login.php");
    exit();
}

include 'db.php';

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_FILES["postMedia"])) {
    $target_dir = "uploads/";
    $target_file = $target_dir . uniqid() . basename($_FILES["postMedia"]["name"]);
    $uploadOk = 1;
    $fileType = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));

    // Check if file is an actual image or video
    $check = getimagesize($_FILES["postMedia"]["tmp_name"]);
    if ($check !== false || in_array($fileType, ["mp4", "avi", "mov", "wmv"])) {
        $uploadOk = 1;
    } else {
        echo "File is not an image or video.";
        $uploadOk = 0;
    }

    // Check file size
    if ($_FILES["postMedia"]["size"] > 10000000) { // 10MB limit
        echo "Sorry, your file is too large.";
        $uploadOk = 0;
    }

    // Allow certain file formats
    if (!in_array($fileType, ["jpg", "png", "jpeg", "gif", "mp4", "avi", "mov", "wmv"])) {
        echo "Sorry, only JPG, JPEG, PNG, GIF, MP4, AVI, MOV & WMV files are allowed.";
        $uploadOk = 0;
    }

    // Check if $uploadOk is set to 0 by an error
    if ($uploadOk == 0) {
        echo "Sorry, your file was not uploaded.";
    } else {
        if (move_uploaded_file($_FILES["postMedia"]["tmp_name"], $target_file)) {
            $userID = $_SESSION['userid'];
            $caption = $_POST['caption'];
            $sql = "INSERT INTO Posts (UserID, MediaPath, Caption, CreatedAt) VALUES (?, ?, ?, NOW())";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("iss", $userID, $target_file, $caption);
            if ($stmt->execute()) {
                header("Location: profile.php");
                exit();
            } else {
                echo "Sorry, there was an error saving your post.";
            }
            $stmt->close();
            $conn->close();
        } else {
            echo "Sorry, there was an error uploading your file.";
        }
    }
}
?>
