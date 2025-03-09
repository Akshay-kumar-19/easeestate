<?php
session_start(); // Start the session to store login status

// Assuming you have a MySQL connection setup
$host = "localhost";
$username = "root"; // MySQL username
$password = "akki"; // MySQL password
$database = "easeestate"; // Your database name

// Create a connection to MySQL
$conn = new mysqli($host, $username, $password, $database,);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$message = ''; // For storing error or success messages

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $usernameOrEmail = trim($_POST['usernameOrEmail']);
    $password = trim($_POST['password']);

    if (empty($usernameOrEmail) || empty($password)) {
        $message = "Both fields are required!";
    } else {
        // Query to check if the username or email exists
        $stmt = $conn->prepare("SELECT * FROM users WHERE username = ? OR email = ?");
        $stmt->bind_param("ss", $usernameOrEmail, $usernameOrEmail);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            $user = $result->fetch_assoc();
            if (password_verify($password, $user['password'])) {
                // Login successful, create session
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['username'] = $user['username'];

                // Redirect to dashboard.php
                header("Location: dashboard.php");
                exit();
            } else {
                $message = "Incorrect password!";
            }
        } else {
            $message = "User not found!";
        }
        $stmt->close();
    }
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="css/login.css">
    <link rel="icon" href="css/logos.png" sizes="32x32" type="image/png">
    <title>Login</title>
</head>
<body>
    <div class="wrapper">
        <h1>Login</h1>
        <form method="POST" action="login.php">
            <div class="input-box">
                <input type="text" name="usernameOrEmail" placeholder="Enter Username or Email" required>
            </div>
            <div class="input-box">
                <input type="password" name="password" placeholder="Enter Password" required>
            </div>
            <div class="remember-forgot">
                <label><input type="checkbox"> Remember me</label>
               <!-- <a href="#">Forgot password?</a>-->
            </div>
            <button type="submit" class="btn">Login</button>

            <!-- Display success or error message -->
            <?php if ($message): ?>
                <div id="login-message" class="message-box">
                    <p style="color: red;"><?php echo $message; ?></p>
                </div>
            <?php endif; ?>

            <div class="register-link">
                <p>Don't have an account? <a href="register.php">Register here</a></p>
            </div>
        </form>
    </div>

    <!-- Auto-hide messages after 3 seconds -->
    <script>
        setTimeout(function() {
            var messageElement = document.getElementById("login-message");
            if (messageElement) {
                messageElement.style.display = "none";
            }
        }, 3000); // Hides the message after 3 seconds
    </script>
</body>
</html>
