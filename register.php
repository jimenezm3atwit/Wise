<?php
// Enable error reporting
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Include db.php
include('db.php');

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $firstname = $_POST['firstname'];
    $lastname = $_POST['lastname'];
    $username = $_POST['username'];
    $password1 = $_POST['password1'];
    $password2 = $_POST['password2'];

    // Check if passwords match
    if ($password1 != $password2) {
        $error_message = "Passwords do not match.";
    } else {
        // Hash the password
        $hashed_password = password_hash($password1, PASSWORD_DEFAULT);

        // Check if the username already exists
        $checkUserSql = "SELECT * FROM users WHERE username = ?";
        $checkStmt = $conn->prepare($checkUserSql);
        $checkStmt->bind_param("s", $username);
        $checkStmt->execute();
        $checkResult = $checkStmt->get_result();

        if ($checkResult->num_rows > 0) {
            $error_message = "Username already exists.";
        } else {
            // Prepare and bind
            $sql = "INSERT INTO users (firstname, lastname, username, password) VALUES (?, ?, ?, ?)";
            $stmt = $conn->prepare($sql);

            if ($stmt === false) {
                die("Error preparing the statement: " . $conn->error);
            }

            $stmt->bind_param("ssss", $firstname, $lastname, $username, $hashed_password);

            if ($stmt->execute()) {
                $success_message = "Registration successful!";
            } else {
                $error_message = "Error: " . $stmt->error;
            }

            $stmt->close();
        }

        $checkStmt->close();
    }

    $conn->close();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>WeatherWise Register</title>
    <link rel="stylesheet" href="registerstyle.css">
    <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
</head>
<body>
    <div class="video-background">
        <video autoplay muted loop>
            <source src="https://wwiserbucket.s3.us-east-2.amazonaws.com/3121263-uhd_3840_2160_24fps.mp4" type="video/mp4">
        </video>
    </div>
    <div class="wrapper">
        <div class="video-background-inner">
            <video autoplay muted loop>
                <source src="https://wwiserbucket.s3.us-east-2.amazonaws.com/3121263-uhd_3840_2160_24fps.mp4" type="video/mp4">
            </video>
        </div>
        <form action="" method="post" onsubmit="return checkPassword()">
            <h1>Register Account</h1>
            <?php
            if (!empty($error_message)) {
                echo '<p style="color: red;">' . $error_message . '</p>';
            }
            if (!empty($success_message)) {
                echo '<p style="color: green;">' . $success_message . '</p>';
            }
            ?>
            <div class="input-box">
                <input type="text" name="firstname" placeholder="First Name" required>
                <i class='bx bxs-user'></i>
            </div>
            <div class="input-box">
                <input type="text" name="lastname" placeholder="Last Name" required>
                <i class='bx bxs-user'></i>
            </div>
            <div class="input-box">
                <input type="text" name="username" placeholder="Enter Email" required>
                <i class='bx bxs-envelope'></i>
            </div>
            <div class="input-box">
                <input type="password" name="password1" placeholder="Enter Password" id="password1" required>
                <i class='bx bxs-lock-alt'></i>
            </div>
            <div class="input-box">
                <input type="password" name="password2" placeholder="Confirm Password" id="password2" required>
                <i class='bx bxs-lock-alt'></i>
            </div>
            <button type="submit" class="btn">Register</button>
        </form>
    </div>
    <script>
        function checkPassword() {
            let password1 = document.getElementById("password1").value;
            let password2 = document.getElementById("password2").value;

            if (password1 !== password2) {
                alert("Passwords do not match.");
                return false;
            }
            return true;
        }
    </script>
</body>
</html>
