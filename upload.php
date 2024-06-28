<?php
session_start();
if (!isset($_SESSION['username'])) {
    header("Location: login.php");
    exit();
}

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_FILES["fileToUpload"])) {
    if ($_FILES["fileToUpload"]["error"] != 0) {
        header("Location: profile.php?error=upload");
        exit();
    }

    $target_dir = "uploads/";
    $randomString = bin2hex(random_bytes(8)); // Generate a random string
    $target_file = $target_dir . $randomString . "_" . basename($_FILES["fileToUpload"]["name"]);
    $uploadOk = 1;
    $imageFileType = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));

    // Check if image file is a actual image or fake image
    $check = getimagesize($_FILES["fileToUpload"]["tmp_name"]);
    if ($check !== false) {
        $uploadOk = 1;
    } else {
        header("Location: profile.php?error=notimage");
        exit();
    }

    // Check if file already exists
    if (file_exists($target_file)) {
        header("Location: profile.php?error=exists");
        exit();
    }

    // Check file size
    if ($_FILES["fileToUpload"]["size"] > 500000) {
        header("Location: profile.php?error=toolarge");
        exit();
    }

    // Allow certain file formats
    if ($imageFileType != "jpg" && $imageFileType != "png" && $imageFileType != "jpeg" && $imageFileType != "gif") {
        header("Location: profile.php?error=format");
        exit();
    }

    // Check if $uploadOk is set to 0 by an error
    if ($uploadOk == 0) {
        header("Location: profile.php?error=unknown");
    } else {
        if (move_uploaded_file($_FILES["fileToUpload"]["tmp_name"], $target_file)) {
            include 'db.php';

            // Update the database with the path to the uploaded file
            $userID = $_SESSION['userid'];
            $sql = "UPDATE Users SET ProfilePhoto = ? WHERE UserID = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("si", $target_file, $userID);
            if ($stmt->execute()) {
                header("Location: profile.php");
                exit();
            } else {
                header("Location: profile.php?error=dbupdate");
                exit();
            }
            $stmt->close();
            $conn->close();
        } else {
            header("Location: profile.php?error=move");
            exit();
        }
    }
}
?>
