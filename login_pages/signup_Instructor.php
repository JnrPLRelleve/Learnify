<?php
session_start();


if (!isset($_SESSION['username']) || !isset($_SESSION['role'])) {
    $_SESSION['username'] = null; 
    $_SESSION['role'] = null;
}

$conn = new mysqli("localhost", "root", "", "learnify");

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['ajax'])) {
    $fullname = $conn->real_escape_string($_POST['fullname']);
    $username = $conn->real_escape_string($_POST['username']);
    $password = password_hash($_POST['password'], PASSWORD_BCRYPT);
    $role = 'instructor';
    $status = 'pending';
    $created_at = date("Y-m-d H:i:s");

    // Check if the username already exists
    $check_sql = "SELECT * FROM users WHERE username = '$username'";
    $check_result = $conn->query($check_sql);

    if ($check_result->num_rows > 0) {
        echo json_encode(["status" => "error", "message" => "Username already exists."]);
    } else {
        // Query the database for the last instructor ID
        $sql_last_instructor_id = "SELECT user_id FROM users WHERE user_id LIKE 'INT-BN-%' ORDER BY created_at DESC LIMIT 1";
        $result_instructor = $conn->query($sql_last_instructor_id);

        if ($result_instructor->num_rows > 0) {
            $row_instructor = $result_instructor->fetch_assoc();
            $last_instructor_id = $row_instructor['user_id'];
            $last_instructor_number = (int)substr($last_instructor_id, strrpos($last_instructor_id, '-') + 1);
            $new_instructor_number = str_pad($last_instructor_number + 1, 2, '0', STR_PAD_LEFT);
            $user_id = 'INT-BN-' . $new_instructor_number;
        } else {
            $user_id = 'INT-BN-01';
        }

        // Update      the INSERT query to include the generated user_id
        $sql = "INSERT INTO users (user_id, fullname, username, password, role, status, created_at) VALUES ('$user_id', '$fullname', '$username', '$password', '$role', '$status', '$created_at')";

        if ($conn->query($sql) === TRUE) {
            // Set  variables after successful signup
            $_SESSION['username'] = $username;
            $_SESSION['role'] = $role;

            echo json_encode(["status" => "success", "message" => "Signup successful."]);
        } else {
            echo json_encode(["status" => "error", "message" => "Error: " . $conn->error]);
        }
    }
    exit();
}

$conn->close();
?>

<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SignUp Instructor</title>
    <link rel="stylesheet" href="signup_Instructor.css">
    <script src="jquery-3.6.0.min.js"></script> <!-- javaScript LIBRARY, not FRAMEWORK -->
    <script src="signup_instructor.js"></script>
</head>
<body>
    <div class="container">
        <img src="../images/admin.jpg" alt="Instructor image">
        
        <div class="form-wrapper">
            <form action="signup_Instructor.php" method="post">
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
