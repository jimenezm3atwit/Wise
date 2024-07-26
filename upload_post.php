<?php
session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['username'])) {
    echo json_encode(['status' => 'error', 'message' => 'Unauthorized']);
    exit();
}

include 'db.php';

$userID = $_SESSION['userid'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $targetDir = "uploads/";
    $targetFile = $targetDir . basename($_FILES["postMedia"]["name"]);
    $uploadOk = 1;
    $imageFileType = strtolower(pathinfo($targetFile, PATHINFO_EXTENSION));

    // Check if image file is a actual image or fake image
    $check = getimagesize($_FILES["postMedia"]["tmp_name"]);
    if ($check !== false) {
        $uploadOk = 1;
    } else {
        echo json_encode(['status' => 'error', 'message' => 'File is not an image.']);
        $uploadOk = 0;
    }

    // Check if file already exists
    if (file_exists($targetFile)) {
        echo json_encode(['status' => 'error', 'message' => 'Sorry, file already exists.']);
        $uploadOk = 0;
    }

    // Check file size
    if ($_FILES["postMedia"]["size"] > 500000) {
        echo json_encode(['status' => 'error', 'message' => 'Sorry, your file is too large.']);
        $uploadOk = 0;
    }

    // Allow certain file formats
    if ($imageFileType != "jpg" && $imageFileType != "png" && $imageFileType != "jpeg" && $imageFileType != "gif" && $imageFileType != "mp4") {
        echo json_encode(['status' => 'error', 'message' => 'Sorry, only JPG, JPEG, PNG, GIF & MP4 files are allowed.']);
        $uploadOk = 0;
    }

    // Check if $uploadOk is set to 0 by an error
    if ($uploadOk == 0) {
        // If everything is ok, try to upload file
        echo json_encode(['status' => 'error', 'message' => 'Sorry, your file was not uploaded.']);
    } else {
        if (move_uploaded_file($_FILES["postMedia"]["tmp_name"], $targetFile)) {
            $caption = $_POST['caption'];
            $sql = "INSERT INTO Posts (UserID, MediaURL, Caption) VALUES (?, ?, ?)";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("iss", $userID, $targetFile, $caption);

            if ($stmt->execute()) {
                echo json_encode(['status' => 'success', 'message' => 'Post created successfully!']);
            } else {
                echo json_encode(['status' => 'error', 'message' => 'Error creating post: ' . $stmt->error]);
            }

            $stmt->close();
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Sorry, there was an error uploading your file.']);
        }
    }
}

$conn->close();
?>
