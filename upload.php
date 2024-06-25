<?php
session_start();
if (!isset($_SESSION['userid'])) {
    header("Location: login.php");
    exit();
}

include 'db.php';

$target_dir = "uploads/";
$target_file = $target_dir . basename($_FILES["profilePhoto"]["name"]);
$uploadOk = 1;
$imageFileType = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));

// Check if image file is a actual image or fake image
if (isset($_POST["submit"])) {
    $check = getimagesize($_FILES["profilePhoto"]["tmp_name"]);
    if ($check !== false) {
        $uploadOk = 1;
    } else {
        echo "File is not an image.";
        $uploadOk = 0;
    }
}

// Check if file already exists
if (file_exists($target_file)) {
    echo "Sorry, file already exists.";
    $uploadOk = 0;
}

// Check file size
if ($_FILES["profilePhoto"]["size"] > 500000) {
    echo "Sorry, your file is too large.";
    $uploadOk = 0;
}

// Allow certain file formats
if ($imageFileType != "jpg" && $imageFileType != "png" && $imageFileType != "jpeg" && $imageFileType != "gif") {
    echo "Sorry, only JPG, JPEG, PNG & GIF files are allowed.";
    $uploadOk = 0;
}

// Check if $uploadOk is set to 0 by an error
if ($uploadOk == 0) {
    echo "Sorry, your file was not uploaded.";
// if everything is ok, try to upload file
} else {
    if (move_uploaded_file($_FILES["profilePhoto"]["tmp_name"], $target_file)) {
        // Update the database with the new profile photo path
        $sql = "UPDATE Users SET ProfilePhoto = ? WHERE UserID = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("si", $target_file, $_SESSION['userid']);
        if ($stmt->execute()) {
            echo "The file ". htmlspecialchars(basename($_FILES["profilePhoto"]["name"])). " has been uploaded.";
            header("Location: profile.php");
            exit();
        } else {
            echo "Sorry, there was an error updating your profile.";
        }
    } else {
        echo "Sorry, there was an error uploading your file.";
    }
}
?>
