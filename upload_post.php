<?php
session_start();
if (!isset($_SESSION['username'])) {
    header("Location: login.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_FILES['postMedia'])) {
    include 'db.php';

    $userID = $_SESSION['userid'];
    $caption = $_POST['caption'];
    $target_dir = "uploads/";
    $target_file = $target_dir . basename($_FILES["postMedia"]["name"]);
    $uploadOk = 1;
    $imageFileType = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));

    // Check if image file is an actual image or fake image
    $check = getimagesize($_FILES["postMedia"]["tmp_name"]);
    if ($check !== false) {
        $uploadOk = 1;
    } else {
        echo "File is not an image.";
        $uploadOk = 0;
    }

    // Check file size
    if ($_FILES["postMedia"]["size"] > 5000000) { // 5MB limit
        echo "Sorry, your file is too large.";
        $uploadOk = 0;
    }

    // Allow certain file formats
    if ($imageFileType != "jpg" && $imageFileType != "png" && $imageFileType != "jpeg"
        && $imageFileType != "gif" && $imageFileType != "mp4" && $imageFileType != "mov") {
        echo "Sorry, only JPG, JPEG, PNG, GIF, MP4 & MOV files are allowed.";
        $uploadOk = 0;
    }

    // Check if $uploadOk is set to 0 by an error
    if ($uploadOk == 0) {
        echo "Sorry, your file was not uploaded.";
    } else {
        if (move_uploaded_file($_FILES["postMedia"]["tmp_name"], $target_file)) {
            $mediaURL = htmlspecialchars($target_file);
            $sql = "INSERT INTO Posts (UserID, MediaURL, Caption, CreatedAt) VALUES (?, ?, ?, NOW())";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("iss", $userID, $mediaURL, $caption);

            if ($stmt->execute()) {
                echo "The file " . basename($_FILES["postMedia"]["name"]) . " has been uploaded.";
            } else {
                echo "Sorry, there was an error uploading your file.";
            }
            $stmt->close();
        } else {
            echo "Sorry, there was an error uploading your file.";
        }
    }
    $conn->close();
    header("Location: explore.php"); // Redirect to explore page after upload
    exit();
} else {
    echo "Invalid request.";
}
?>
