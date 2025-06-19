<?php
session_start();
if (!isset($_SESSION['username']) || !isset($_SESSION['role']) || $_SESSION['role'] !== 'student') {
    header('Location: ../login_pages/loginpage.php');
    exit();
}
// Fetch all available quizzes for courses the student is enrolled in
$conn = new mysqli('localhost', 'root', '', 'learnify');
if ($conn->connect_error) {
    die('Connection failed: ' . $conn->connect_error);
}
$quizzes = [];
$student_username = $_SESSION['username'];
// Get student id
$student_id = 0;
$student_result = $conn->query("SELECT id FROM users WHERE username = '" . $conn->real_escape_string($student_username) . "' LIMIT 1");
if ($student_result && $student_result->num_rows > 0) {
    $student_row = $student_result->fetch_assoc();
    $student_id = intval($student_row['id']);
}
// Get course ids the student is enrolled in
$enrolled_courses = [];
$enroll_result = $conn->query("SELECT c.id, c.title FROM enrollments e JOIN courses c ON e.course_id = c.id WHERE e.student_id = $student_id");
if ($enroll_result && $enroll_result->num_rows > 0) {
    while ($row = $enroll_result->fetch_assoc()) {
        $enrolled_courses[$row['id']] = $row['title'];
    }
}
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
    
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Available Quizzes</title>
    <link rel="stylesheet" href="student_dashboard.css">
   
    <link rel="stylesheet" href="student_quizzes.css">
    
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
        <h1 style="color:#5b6291; margin-bottom: 24px;">Available Quizzes</h1>
        <div class="search_bar" style="margin-bottom: 18px;">
            <label for="courseSearch">Search Courses:</label>
            <input type="text" id="courseSearch" placeholder="Type to search courses...">
        </div>
        <div class="quiz-list-section" style="max-height: 500px; overflow-y: auto;">
            <?php 
            $perPage = 10;
            $page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
            $totalCourses = count($enrolled_courses);
            $totalPages = max(1, ceil($totalCourses / $perPage));
            $startIdx = ($page - 1) * $perPage;
            $enrolled_courses_page = array_slice($enrolled_courses, $startIdx, $perPage, true);
            ?>
            <?php if (empty($enrolled_courses_page)): ?>
                <div style="text-align:center;">You are not enrolled in any courses.</div>
            <?php else: ?>
                <?php
                foreach ($enrolled_courses_page as $course_id => $course_title):
                    echo '<div class="quiz-course-section">';
                    echo '<h2 style="color:#5b6291; margin: 24px 0 10px 0; font-size:1.15em;">Course: ' . htmlspecialchars($course_title) . '</h2>';
                    // Display all quizzes for this course (all quizzes where quizzes_id is in courses.quiz_id)
                    $quiz_ids = [];
                    $course_quiz_id_res = $conn->query("SELECT quiz_id FROM courses WHERE id = $course_id AND quiz_id IS NOT NULL AND quiz_id != ''");
                    if ($course_quiz_id_res && $course_quiz_id_res->num_rows > 0) {
                        $row = $course_quiz_id_res->fetch_assoc();
                        $quiz_ids = array_filter(array_map('trim', explode(',', $row['quiz_id'])));
                    }
                    $quiz_list = [];
                    if (!empty($quiz_ids)) {
                        $quiz_ids_str = implode(',', array_map('intval', $quiz_ids));
                        $quiz_res = $conn->query("SELECT quizzes_id, saved_quiz_name FROM quizzes WHERE quizzes_id IN ($quiz_ids_str) GROUP BY quizzes_id, saved_quiz_name");
                        if ($quiz_res && $quiz_res->num_rows > 0) {
                            while ($quiz = $quiz_res->fetch_assoc()) {
                                $quiz_list[] = $quiz;
                            }
                        }
                    }
                    if (!empty($quiz_list)) {
                        echo '<table class="quiz-list-table">';
                        echo '<thead><tr><th style="width:50%;">Quiz Name</th><th style="width:50%;text-align:center;">Action</th></tr></thead>';
                        echo '<tbody>';
                        foreach ($quiz_list as $quiz) {
                            echo '<tr>';
                            echo '<td>' . htmlspecialchars($quiz['saved_quiz_name']) . '</td>';
                            // Do not show questions column
                            echo '<td style="text-align:center;"><a href="take_quiz.php?course_id=' . $course_id . '&quiz_id=' . $quiz['quizzes_id'] . '" class="start-quiz-btn">Start Quiz</a></td>';
                            echo '</tr>';
                        }
                        echo '</tbody></table>';
                    } else {
                        echo '<div style="margin-bottom:10px;color:#888;">No quizzes deployed for this course.</div>';
                    }
                    echo '</div>';
                endforeach;
                ?>
                <div class="pagination-controls" style="margin: 20px 0; text-align:center;">
                    <?php if ($page > 1): ?>
                        <a href="?page=<?php echo $page-1; ?>" class="start-quiz-btn" style="background:#5b6291;">Previous</a>
                    <?php else: ?>
                        <button class="start-quiz-btn" style="background:#b3b8d1;cursor:default;" disabled>Previous</button>
                    <?php endif; ?>
                    <span style="margin:0 12px;">Page <?php echo $page; ?> of <?php echo $totalPages; ?></span>
                    <?php if ($page < $totalPages): ?>
                        <a href="?page=<?php echo $page+1; ?>" class="start-quiz-btn" style="background:#5b6291;">Next</a>
                    <?php else: ?>
                        <button class="start-quiz-btn" style="background:#b3b8d1;cursor:default;" disabled>Next</button>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
        </div>
    </main>
</div>
<script src="student_quizzes.js"></script>
</body>
</html>
