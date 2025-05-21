<?php
session_start();
$conn = new mysqli("localhost", "root", "", "learnify");

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $username = $conn->real_escape_string($_POST['username']);
    $password = $_POST['password'];

    $sql = "SELECT * FROM users WHERE username = '$username'";
    $result = $conn->query($sql);

    if ($result->num_rows === 1) {
        $user = $result->fetch_assoc();
        if (password_verify($password, $user['password'])) {
            $_SESSION['username'] = $user['username'];
            $_SESSION['role'] = $user['role'];

            // Redirect based on role
            if ($user['role'] === 'instructor') {
                header("Location: jehan/instructor_dashboard.php");
                
                
            } elseif ($user['role'] === 'student') {
                header("Location: students_dash/student_dashboard.php");
            }
            exit();
        } else {
            echo "<script>alert('Invalid password.');</script>";
        }
    } else {
        echo "<script>alert('No user found with that username.');</script>";
    }
}

$conn->close();
?>

<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Landing page</title>
    <link rel="stylesheet" href="loginpage.css">
</head>
<body>
    <div class="container">
        <img src="https://images.pexels.com/photos/301920/pexels-photo-301920.jpeg" alt="Study image">
        
        <div class="form-wrapper">
            <form action="loginpage.php" method="post">
                <h3>LOGIN</h3>
                <p class="wel">Welcome! Please login your <br>account.</p>
                <label for="username">Username</label><br>
                <input type="text" name="username" id="username" required><br>
                <label for="password">Password</label><br>
                <input type="password" name="password" id="password" minlength="8" maxlength="16" required><br>
                <a href="#" target="_blank" id="forgot_password">Forgot Password?</a><br>
                <input type="submit" value="LOGIN"><br>
                <a href="loginpage_Admin.php" id="signup">ADMIN</a></p>
                <p class="new">New User? <a href="role_Selection.html" id="signup">Signup</a></p>
        </div>
    </div>
</body>

</html>