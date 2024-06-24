<?php
session_start();
if (!isset($_SESSION['username'])) {
    header("Location: login.php");
    exit();
}

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_FILES["profilePhoto"])) {
    include 'db.php';
    
    $userID = $_SESSION['userid'];
    $targetDir = "uploads/";
    $targetFile = $targetDir . basename($_FILES["profilePhoto"]["name"]);
    $uploadOk = 1;
    $imageFileType = strtolower(pathinfo($targetFile, PATHINFO_EXTENSION));

    // Check if uploads directory exists and is writable
    if (!is_dir($targetDir) && !mkdir($targetDir, 0755, true)) {
        die("Failed to create uploads directory");
    }

    // Check if image file is a actual image or fake image
    $check = getimagesize($_FILES["profilePhoto"]["tmp_name"]);
    if ($check !== false) {
        $uploadOk = 1;
    } else {
        echo "File is not an image.";
        $uploadOk = 0;
    }

    // Check file size
    if ($_FILES["profilePhoto"]["size"] > 500000) {
        echo "Sorry, your file is too large.";
        $uploadOk = 0;
    }

    // Allow certain file formats
    if ($imageFileType != "jpg" && $imageFileType != "png" && $imageFileType != "jpeg"
    && $imageFileType != "gif" ) {
        echo "Sorry, only JPG, JPEG, PNG & GIF files are allowed.";
        $uploadOk = 0;
    }

    // Check if $uploadOk is set to 0 by an error
    if ($uploadOk == 0) {
        echo "Sorry, your file was not uploaded.";
    // if everything is ok, try to upload file
    } else {
        if (move_uploaded_file($_FILES["profilePhoto"]["tmp_name"], $targetFile)) {
            $sql = "UPDATE Users SET ProfilePhoto = ? WHERE UserID = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("si", $targetFile, $userID);
            if ($stmt->execute()) {
                echo "The file ". htmlspecialchars(basename($_FILES["profilePhoto"]["name"])). " has been uploaded.";
            } else {
                echo "Sorry, there was an error updating your profile photo in the database.";
            }
            $stmt->close();
        } else {
            echo "Sorry, there was an error uploading your file.";
        }
    }
    $conn->close();
} else {
    echo "Invalid request.";
}
?>
