<?php
$host = "localhost";
$username = "root";
$password = "akki";
$database = "easeestate";

$conn = new mysqli($host, $username, $password, $database);

if ($conn->connect_error) {
    die("Database connection failed: " . $conn->connect_error);
}

$message = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = trim($_POST["username"]);
    $email = trim($_POST["email"]);
    $password = trim($_POST["password"]);

    if (empty($username) || empty($email) || empty($password)) {
        $message = "<span style='color: red;'>All fields are required!</span>";
    } else {
        $checkUser = $conn->prepare("SELECT * FROM users WHERE email = ?");
        $checkUser->bind_param("s", $email);
        $checkUser->execute();
        $result = $checkUser->get_result();

        if ($result->num_rows > 0) {
            $message = "<span style='color: red;'>User already exists!</span>";
        } else {
            $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

            $stmt = $conn->prepare("INSERT INTO users (username, email, password) VALUES (?, ?, ?)");
            $stmt->bind_param("sss", $username, $email, $hashedPassword);

            if ($stmt->execute()) {
                $message = "<span style='color: green;'>Registration successful! Redirecting...</span>";
                echo "<script>setTimeout(() => { window.location.href = 'login.php'; }, 2000);</script>";
            } else {
                $message = "<span style='color: red;'>Registration failed! Try again.</span>";
            }
            $stmt->close();
        }
        $checkUser->close();
    }
}
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Register</title>
  <link rel="stylesheet" href="css/login.css">
  <link rel="icon" href="css/logos.png" sizes="32x32" type="image/png">
</head>
<body>
  <div class="wrapper">
    <form id="registration-form" action="register.php" method="POST">
      <h1>Register</h1>
      <div class="input-box">
        <input type="text" id="username" name="username" placeholder="Enter username" required>
      </div>
      <div class="input-box">
        <input type="email" id="email" name="email" placeholder="Enter email" required>
      </div>
      <div class="input-box">
        <input type="password" id="password" name="password" placeholder="Set password" required>
      </div>
      <button type="submit" class="btn">Register</button>
      <div id="registration-message" style="margin-top: 10px; font-weight: bold;">
        <?php echo $message; ?>
      </div>
    </form>
  </div>
</body>
</html>
