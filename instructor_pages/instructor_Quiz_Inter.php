<?php

session_start();
if (!isset($_SESSION['username']) || !isset($_SESSION['role']) || $_SESSION['role'] !== 'instructor') {
    header('Location: ../loginpage.php');
    exit();
}

$conn = new mysqli('localhost', 'root', '', 'learnify');
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Get instructor user id
$instructor_id = null;
if (isset($_SESSION['username'])) {
    $user_stmt = $conn->prepare('SELECT id FROM users WHERE username = ?');
    $user_stmt->bind_param('s', $_SESSION['username']);
    $user_stmt->execute();
    $user_stmt->bind_result($instructor_id);
    $user_stmt->fetch();
    $user_stmt->close();
}
// Database connection
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "learnify";
$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

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
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quiz Interface</title>
    <link rel="stylesheet" href="instructor_quiz.css">
    <link rel="stylesheet" href="instructor_Quiz_Inter.css">
</head>
<body>
<div class="container">
    <aside class="sidebar">
        <div class="profile_pic"><img src="<?php echo htmlspecialchars($profilePic); ?>" alt="sample"></div>
        <h2><span style="font-size:1em;font-weight:400;"><?php echo htmlspecialchars($_SESSION['username']); ?></span></h2>
        <button class="sidebar_btn" onclick="window.location.href='instructor_dashboard.php'">Courses</button>
        <button class="sidebar_btn" onclick="location.href='instructor_Quiz_Inter.php'">Quiz Interface</button>
        <button class="sidebar_btn" onclick="location.href='create_course.php'">Create Course</button>
        <button class="sidebar_btn" onclick="location.href='manage_Materials.php'">Materials</button>

        <button class="settings_btn" onclick="location.href='INS_settings.php'">Settings</button>
        <button class="logout_btn1" type="button" onclick="window.location.href='../login_pages/logout.php'">Logout</button>
    </aside>
    <main class="main_content">
        <div class="quiz-interface-btns">
            <div class="quiz-box-btn" onclick="window.location.href='instructor_quiz.php'">
                <span class="title">Create Quiz</span>
                <span class="desc">Build new quizzes for your courses</span>
            </div>
            <div class="quiz-box-btn" onclick="window.location.href='instructor_viewSaved_Quiz.php'">
                <span class="title">Deploy Quiz</span>
                <span class="desc">Assign quizzes to courses</span>
            </div>
            <div class="quiz-box-btn" onclick="window.location.href='instructor_QuizResults.php'">
                <span class="title">Courses Quiz Result</span>
                <span class="desc">View students' quiz results</span>
            </div>
        </div>
    </main>
</div>
</body>
</html>
