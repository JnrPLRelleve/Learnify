<?php
session_start();
$conn = new mysqli("localhost", "root", "", "learnify");

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $fullname = $conn->real_escape_string($_POST['fullname']);
    $username = $conn->real_escape_string($_POST['username']);
    $password = password_hash($_POST['password'], PASSWORD_BCRYPT);
    $role = 'student';
    $created_at = date("Y-m-d H:i:s");

    // Query        the database for the last user_id (STUDENT USER)
    $sql_last_id = "SELECT user_id FROM users WHERE user_id LIKE 'STU-BN-%' ORDER BY created_at DESC LIMIT 1";
    $result = $conn->query($sql_last_id);

    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $last_id = $row['user_id'];
        $last_number = (int)substr($last_id, strrpos($last_id, '-') + 1);
        $new_number = str_pad($last_number + 1, 2, '0', STR_PAD_LEFT);
        $user_id = 'STU-BN-' . $new_number;
    } else {
        $user_id = 'STU-BN-01';
    }

    $sql = "INSERT INTO users (user_id, fullname, username, password, role, created_at) VALUES ('$user_id', '$fullname', '$username', '$password', '$role', '$created_at')";

    if ($conn->query($sql) === TRUE) {
        $_SESSION['username'] = $username;
        $_SESSION['role'] = $role;
        header("Location: dashboard.php");
        exit();
    } else {
        echo "Error: " . $sql . "<br>" . $conn->error;
    }
}

$conn->close();
?>

<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SignUp Students</title>
    <link rel="stylesheet" href="signup_Instructor.css">
</head>
<body>
    <div class="container">
        <img src="../images/student.jpg" alt="Instructor image">
        
        <div class="form-wrapper">
            <form action="signup_Students.php" method="post">
                <h3>SIGNUP</h3>
                <p class="wel">Welcome! Please create an <br>account.</p>
                <label for="fullname">Fullname</label><br>
                <input type="text" name="fullname" id="fullname" required><br>
                <label for="username">Username</label><br>
                <input type="text" name="username" id="username" required><br>
                <label for="password">Password</label><br>
                <input type="password" name="password" id="password" minlength="8" maxlength="16" required><br>
                <input type="submit" value="SIGNUP"><br>
                <p class="new">Already have an account? <a href="loginpage.html" id="signup">Login</a></p>
            </form>
        </div>
    </div>
</body>
</html>