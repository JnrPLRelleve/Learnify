<?php
session_start();
if (!isset($_SESSION['username']) || !isset($_SESSION['role']) || $_SESSION['role'] !== 'student') {
    header('Location: ../login_pages/loginpage.php');
    exit();
}
// Fetch profile picture filename from DB
$profilePic = '../images/AdminPerson.jpg'; // default
$conn = new mysqli('localhost', 'root', '', 'learnify');
if (!$conn->connect_error) {
    $username = $_SESSION['username'];
    $res = $conn->query("SELECT profile_picture FROM users WHERE username='".$conn->real_escape_string($username)."' LIMIT 1");
    if ($res && $row = $res->fetch_assoc()) {
        if (!empty($row['profile_picture']) && file_exists('../images/profile_pics/' . $row['profile_picture'])) {
            $profilePic = '../images/profile_pics/' . $row['profile_picture'];
        }
    }
    $conn->close();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Quiz Interface</title>
    <link rel="stylesheet" href="student_dashboard.css">
    <link rel="stylesheet" href="student_quiz_interface.css">
    
</head>
<body>
<div class="container">
    <aside class="sidebar">
        <div class="profile_pic"><img src="<?php echo htmlspecialchars($profilePic); ?>" alt="sample"></div>
        <h2><span style="font-size:1em;font-weight:400;"><?php echo htmlspecialchars($_SESSION['username']); ?></span></h2>

        <button class="sidebar_btn" onclick="window.location.href='student_dashboard.php'">Courses</button>
        <button class="sidebar_btn" onclick="window.location.href='lessons.php'">Lessons</button>
        <button class="sidebar_btn" onclick="window.location.href='Student_Quiz_Inter.php'">Quiz Interface</button>
        <button class="settings_btn" onclick="window.location.href='STU_settings.php'">Settings</button>
        <button class="logout_btn" type="button" onclick="window.location.href='../login_pages/logout.php'">Logout</button>
    </aside>
    <main class="main_content">
        <div class="quiz-interface-btns">
            <div class="quiz-box-btn" onclick="window.location.href='student_quizzes.php'">QUIZZES</div>
            <div class="quiz-box-btn" onclick="window.location.href='student_results.php'">RESULT</div>
            
        </div>
    </main>
</div>
</body>
</html>
