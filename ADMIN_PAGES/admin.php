<?php
//server connection and session management
session_start();
if (!isset($_SESSION['username']) || !isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header('Location: loginpage_Admin.php');
    exit();
}
if (!isset($_SESSION['last_regeneration'])) {
    $_SESSION['last_regeneration'] = time();
} elseif (time() - $_SESSION['last_regeneration'] > 24000) {  //40min regen
    
    session_regenerate_id(true);
    $_SESSION['last_regeneration'] = time();
}

$conn = new mysqli("localhost", "root", "", "learnify");

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
// Fetch instructor's profile picture
$profilePic = '../images/AdminPerson.jpg'; // default
if (isset($_SESSION['username'])) {
    $user_stmt = $conn->prepare('SELECT profile_picture FROM users WHERE username = ?');
    $user_stmt->bind_param('s', $_SESSION['username']);
    $user_stmt->execute();
    $user_stmt->bind_result($profile_picture);
    $user_stmt->fetch();
    $user_stmt->close();
    if (!empty($profile_picture) && file_exists('../images/profile_pics/' . $profile_picture)) {
        $profilePic = '../images/profile_pics/' . $profile_picture;
    }
}

if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['username'], $_POST['action'])) {
    session_start(); 
    if (!isset($_SESSION['username']) || $_SESSION['role'] !== 'admin') {
        echo "error: Unauthorized access.";
        exit();
    }

    $username = $conn->real_escape_string($_POST['username']);
    $action = $_POST['action'];

    if ($action === 'accept') {
        $sql = "UPDATE users SET status = 'approved' WHERE username = '$username'";
    } elseif ($action === 'decline') {
        $sql = "DELETE FROM users WHERE username = '$username'";
    }

    if (isset($sql)) {
        if ($conn->query($sql) === TRUE) {
            echo "success";
        } else {
            error_log("SQL Error: " . $conn->error);
            error_log("SQL Query: " . $sql);
            echo "error: " . $conn->error;
        }
    } else {
        echo "error: Invalid action.";
    }
    exit();
}

?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>WELCOME ADMIN</title>
    <link rel="stylesheet" href="admin.css">
</head>
<body>
    <div class="container">
        <div class="sidebar">
            <div class="admin-avatar">
                <img src="<?php echo htmlspecialchars($profilePic); ?>" alt="Admin Avatar" class="avatar">
            </div>
            <div class="sidebar-content">
                <div class="heading">ADMIN</div>
                <button class="nav-btn" onclick="location.href='courseList.php'">Courses</button>
                <button class="nav-btn" onclick="location.href='instructorList.php'">Instructors</button>
                <button class="nav-btn" onclick="location.href='studentList.php'">Students</button>
                <button class="admin-list-btn" onclick="location.href='admin_List.php'">Admin List</button>
                <button class="logout-btn" type="button" onclick="window.location.href='admin_Logout.php'">Logout</button>
            </div>
        </div>
        <div class="main">
            <h1 class="welcome-heading">Welcome, <?php echo htmlspecialchars($_SESSION['username'] ?? 'Admin'); ?>!</h1>
            <p class="welcome-message">-We are glad to have you back. Manage your tools and resources efficiently, and stay on top of your dashboard to streamline your workflow.</p>
        </div>
    </div>
    
</body>
</html>
