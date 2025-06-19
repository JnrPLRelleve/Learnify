<?php
// forgot_password.php
session_start();
$conn = new mysqli('localhost', 'root', '', 'learnify');
if ($conn->connect_error) {
    die('Database connection failed.');
}
$message = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user_id = $conn->real_escape_string($_POST['user_id'] ?? '');
    $new_username = $conn->real_escape_string($_POST['new_username'] ?? '');
    $new_password = $_POST['new_password'] ?? '';
    if (!$user_id || !$new_username || !$new_password) {
        $message = 'All fields are required.';
    } else {
        $check = $conn->query("SELECT id FROM users WHERE user_id = '$user_id'");
        if ($check && $check->num_rows > 0) {
            $hashed = password_hash($new_password, PASSWORD_BCRYPT);
            $update = $conn->query("UPDATE users SET username = '$new_username', password = '$hashed' WHERE user_id = '$user_id'");
            if ($update) {
                $message = 'Username and password updated successfully!';
            } else {
                $message = 'Failed to update. Please try again.';
            }
        } else {
            $message = 'User ID not found.';
        }
    }
}
$conn->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Forgot Password</title>
    <link rel="stylesheet" href="signup_Instructor.css">
</head>
<body>
    <div class="container">
        <div class="form-wrapper">
            <form method="post" action="">
                <h3>Forgot Password</h3>
                <label for="user_id">User ID</label><br>
                <input type="text" name="user_id" id="user_id" required><br>
                <label for="new_username">New Username</label><br>
                <input type="text" name="new_username" id="new_username" required><br>
                <label for="new_password">New Password</label><br>
                <input type="password" name="new_password" id="new_password" minlength="8" maxlength="16" required><br>
                <input type="submit" value="Reset"><br>
                <?php if ($message): ?>
                    <div style="color: green; margin-top:10px; font-weight:bold;"> <?= htmlspecialchars($message) ?> </div>
                <?php endif; ?>
                <button type="button" onclick="window.location.href='loginpage.php'">Back to Login</button>

            </form>

        </div>
    </div>
</body>
</html>
