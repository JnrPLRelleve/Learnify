<?php
session_start();
if (!isset($_SESSION['username']) || !isset($_SESSION['role']) || $_SESSION['role'] !== 'instructor') {
    header('Location: ../loginpage.php');
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quiz Interface</title>
    <link rel="stylesheet" href="quiz.css">
</head>
<body>
    <div class="container">
        <aside class="sidebar">
            <div class="profile_pic"><img src="../images/AdminPerson.jpg" alt="Instructor Profile"></div>
            <h2>INSTRUCTOR</h2>
            <button class="course_btn" onclick="location.href='instructor_dashboard.php'">Courses</button>
            <button class="quiz_btn">Quiz Interface</button>
            <button class="create_btn" onclick="location.href='create_course.php'">Create Course</button>

            <button class="settings_btn">Settings</button>
            <button class="logout_btn" type="button" onclick="window.location.href='../login_pages/logout.php'">Logout</button>
        </aside>

        <main class="main_content">
            <div class="quiz_options">
                <div class="quiz_card" onclick="alert('Create Quiz Clicked!')">
                    <h1>create<br>quiz</h1>
                </div>
                <div class="quiz_card" onclick="alert('View Grades Clicked!')">
                    <h1>view<br>grades</h1>
                </div>
            </div>
        </main>
    </div>
</body>
</html>