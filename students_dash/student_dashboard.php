<?php
session_start();

if (!isset($_SESSION['username'])) {
    header("Location: loginpage.php");
    exit();
}

if ($_SESSION['role'] !== 'student') {
    header("Location: loginpage.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>STUDENT DASHBOARD</title>
    <link rel="stylesheet" href="student_dashboard.css">
</head>
<body>
    <div class="container">
        <aside class="sidebar">
            <div class="profile_pic"><img src="../images/AdminPerson.jpg" alt="sample"></div>
            <h2>STUDENT</h2>
            <button class="sidebar_btn">Courses</button>
            <button class="sidebar_btn">Quiz Interface</button>
            <button class="settings_btn">Settings</button>
            <button class="logout_btn" type="button" onclick="window.location.href='../login_pages/logout.php'">Logout</button>
        </aside>

        <main class="main_content">
            <div class="search_bar">
                <label for="search">Search:</label>
                <input type="text" id="search" placeholder="Sort By...">
            </div>

            <div class="courses_list" id="coursesList">                
                <div class="course_card add_new" id="addCourseBtn">
                    <div class="course_icon"></div>
                    <h3>Enroll Course</h3>
                </div>
            </div>
        </main>
    </div>

    <div class="modal" id="courseModal" style="display: none;">
        <div class="modal_content">
            <button class="close_modal" id="closeModalBtn">Back</button>
            <h1>Select a Course</h1>
            <div id="courseList">
                <button class="course_option" data-course-name="Course 1" data-instructor-name="Instructor A">Course 1 - Instructor A</button>
                <button class="course_option" data-course-name="Course 2" data-instructor-name="Instructor B">Course 2 - Instructor B</button>
                <button class="course_option" data-course-name="Course 3" data-instructor-name="Instructor C">Course 3 - Instructor C</button>
                <button class="course_option" data-course-name="Course 4" data-instructor-name="Instructor D">Course 4 - Instructor D</button>
                <button class="course_option" data-course-name="Course 5" data-instructor-name="Instructor E">Course 5 - Instructor E</button>
            </div>
            <button class="enroll_btn" id="enrollBtn">Enroll</button>
        </div>
    </div>

    <div class="modal" id="courseDetailsModal" style="display: none;">
        <div class="modal_content">
            <button class="close_modal" id="closeDetailsModalBtn">Close</button>
            <h1 id="courseDetailsTitle"></h1>
            <p id="courseDetailsText">Lorem ipsum dolor sit amet, consectetur adipiscing elit. Sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat.</p>
            <div class="modal_actions">
                <button class="unenroll_btn" id="unenrollBtn">Unenroll</button>
                <button class="view_materials_btn" id="viewMaterialsBtn">View Materials</button>
            </div>
        </div>
    </div>
    
    
    

    <script src="student_dashboard.js"></script>
</body>
</html>