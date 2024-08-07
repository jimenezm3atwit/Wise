<?php
session_start();
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    include 'db.php'; // Make sure this path is correct

    $username = $conn->real_escape_string($_POST['username']);
    $password = $conn->real_escape_string($_POST['password']);

    $sql = "SELECT UserID, Password FROM Users WHERE Username = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        if (password_verify($password, $row['Password'])) {
            // Start session and set session variables
            $_SESSION['userid'] = $row['UserID'];
            $_SESSION['username'] = $username;
            // Redirect to index.php
            header("Location: index.php");
            exit();
        } else {
            echo "<p>Invalid password</p>";
        }
    } else {
        echo "<p>Username does not exist</p>";
    }
    $stmt->close();
    $conn->close();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>WeatherWise Login</title>
    <link rel="stylesheet" href="loginstyle.css">
    <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
</head>
<body>
    <div class="video-background">
        <video autoplay muted loop>
            <source src="https://wwiserbucket.s3.us-east-2.amazonaws.com/4763873-uhd_3840_2160_24fps.mp4" type="video/mp4">
        </video>
    </div>
    <div class="container">
        <div class="about-section">
            <h2>About Us</h2>
            <h3>Welcome to WeatherWise!</h3>
            <p>Our app provides weather-based advisories and suggestions for outdoor activities. Whether it's a sunny day perfect for hiking or a rainy day requiring caution on the roads, we have you covered. Join our community to share and receive user-reported advisories and stay safe and informed.</p>
        </div>
        <div class="login-section">
            <div class="wrapper">
                <form action="login.php" method="post">
                    <h1>WeatherWise Login</h1>
                    <div class="input-box">
                        <input type="text" name="username" placeholder="Email" required aria-label="Username">
                        <i class='bx bxs-envelope'></i> <!-- You can change this icon to bxs-user if you prefer the user icon -->
                    </div>
                    <div class="input-box">
                        <input type="password" name="password" placeholder="Password" required aria-label="Password">
                        <i class='bx bxs-lock-alt'></i>
                    </div>
                    <div class="remember-forgot">
                        <label><input type="checkbox" name="remember"> Remember Me</label>
                    </div>
                    <button type="submit" class="btn">Login</button>
                    <div class="register-link">
                        <p>Don't have an account? <a href="register.php">Register</a></p> 
                    </div>
                </form>
            </div>
        </div>
    </div>
</body>
</html>
