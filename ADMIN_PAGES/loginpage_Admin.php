<?php
session_start();
$conn = new mysqli("localhost", "root", "", "learnify");

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}


// Add default admin account if it doesn't exist
$default_admin_username = 'admin';
$default_admin_password = password_hash('admin123', PASSWORD_BCRYPT);
$check_admin_sql = "SELECT * FROM users WHERE username = '$default_admin_username' AND role = 'admin'";
$check_admin_result = $conn->query($check_admin_sql);

if ($check_admin_result->num_rows === 0) {
    $insert_admin_sql = "INSERT INTO users (fullname, username, password, role, status, created_at) 
                         VALUES ('Default Admin', '$default_admin_username', '$default_admin_password', 'admin', 'approved', NOW())";
    $conn->query($insert_admin_sql);
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
            if ($user['role'] === 'admin') {
                header("Location: admin.php");
            } else {
                header("Location: user_dashboard.php"); // Adjust this for non-admin roles
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
    <title>Admin</title>
    <link rel="stylesheet" href="loginpage_Admin.css">
</head>
<body>
    <div class="container">
        <img src="https://images.pexels.com/photos/714701/pexels-photo-714701.jpeg" alt="admin image">
        
        <div class="form-wrapper">
            <form action="loginpage_Admin.php" method="post">
                <h3>LOGIN</h3>
                <p class="wel">Welcome! Please login your <br>account.</p>
                <label for="username">Username</label><br>
                <input type="text" name="username" id="username" required><br>
                <label for="password">Password</label><br>
                <input type="password" name="password" id="password" minlength="3" maxlength="16" required><br>
                <a href="#" target="_blank" id="forgot_password">Forgot Password?</a><br>
                <input type="submit" value="LOGIN"><br>
            </form>
        </div>
    </div>
</body>

</html>