<?php
session_start();
include 'db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_FILES['postMedia']) && isset($_POST['caption'])) {
        // Maximum file size in bytes (e.g., 10MB)
        $maxFileSize = 10 * 1024 * 1024;

        if ($_FILES['postMedia']['size'] > $maxFileSize) {
            echo json_encode(['status' => 'error', 'message' => 'File size exceeds the maximum limit.']);
            exit();
        }

        $userID = $_SESSION['userid'];
        $caption = htmlspecialchars($_POST['caption']);
        
        $targetDir = "uploads/";
        $fileName = basename($_FILES['postMedia']['name']);
        $fileExtension = pathinfo($fileName, PATHINFO_EXTENSION);

        // Generate a unique name for the file
        $uniqueName = uniqid() . '.' . $fileExtension;
        $targetFilePath = $targetDir . $uniqueName;

        // Check if the file is an image, video, or other allowed type
        $fileType = mime_content_type($_FILES['postMedia']['tmp_name']);
        error_log("Detected MIME type: $fileType");
        $allowedTypes = [
            'image/jpeg', // Covers both .jpg and .jpeg files
            'image/png', 
            'image/gif', 
            'video/mp4',
            'application/pdf', // PDF files
            'audio/mpeg',      // MP3 audio files
            'audio/wav'        // WAV audio files
        ];

        // Use getimagesize for image validation
        $imageInfo = getimagesize($_FILES['postMedia']['tmp_name']);
        $isImage = $imageInfo !== false;
        error_log("Is image: " . ($isImage ? "Yes" : "No"));

        if (in_array($fileType, $allowedTypes) || ($isImage && in_array($imageInfo['mime'], ['image/jpeg', 'image/png', 'image/gif']))) {
            if (move_uploaded_file($_FILES['postMedia']['tmp_name'], $targetFilePath)) {
                $sql = "INSERT INTO Posts (UserID, MediaURL, Caption, CreatedAt) VALUES (?, ?, ?, NOW())";
                $stmt = $conn->prepare($sql);
                $stmt->bind_param("iss", $userID, $targetFilePath, $caption);

                if ($stmt->execute()) {
                    echo json_encode(['status' => 'success', 'message' => 'Post created successfully!']);
                } else {
                    echo json_encode(['status' => 'error', 'message' => 'Failed to create post.']);
                }

                $stmt->close();
            } else {
                echo json_encode(['status' => 'error', 'message' => 'Sorry, there was an error uploading your file.']);
            }
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Invalid file type.']);
        }
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Invalid request.']);
    }

    $conn->close();
} else {
    echo json_encode(['status' => 'error', 'message' => 'Invalid request method.']);
}
