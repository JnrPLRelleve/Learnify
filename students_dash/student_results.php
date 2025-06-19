<?php
session_start();
if (!isset($_SESSION['username']) || !isset($_SESSION['role']) || $_SESSION['role'] !== 'student') {
    header('Location: ../login_pages/loginpage.php');
    exit();
}
$conn = new mysqli('localhost', 'root', '', 'learnify');
if ($conn->connect_error) {
    die('Connection failed: ' . $conn->connect_error);
}
$student_username = $_SESSION['username'];
$student_id = 0;
$res = $conn->query("SELECT id FROM users WHERE username = '" . $conn->real_escape_string($student_username) . "' LIMIT 1");
if ($res && $res->num_rows > 0) {
    $row = $res->fetch_assoc();
    $student_id = intval($row['id']);
}
// Fetch all quiz results for this student, grouped by quiz (score per quiz)
$results = [];
$sql = "SELECT qr.submitted_at, qr.score, q.saved_quiz_name, c.title as course_title
        FROM quiz_results qr
        JOIN quizzes q ON qr.take_quiz_id = q.id
        JOIN courses c ON FIND_IN_SET(q.quizzes_id, c.quiz_id) > 0
        WHERE qr.student_id = $student_id
        GROUP BY qr.submitted_at, q.saved_quiz_name, c.title, qr.score
        ORDER BY qr.submitted_at DESC";
$res = $conn->query($sql);
if ($res && $res->num_rows > 0) {
    while ($row = $res->fetch_assoc()) {
        $results[] = $row;
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
    <title>Quiz Results</title>
    <link rel="stylesheet" href="student_dashboard.css">
    <link rel="stylesheet" href="student_results.css">
    
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
        <div class="results-container">
            <div class="results-title">My Quiz Results</div>
            <?php if (empty($results)): ?>
                <div style="text-align:center;">No quiz results found.</div>
            <?php else: ?>
                <div id="results-list" style="max-height:400px; overflow-y:auto;"></div>
                <div style="text-align:center; margin-top:18px;">
                    <button id="prevPage" style="margin-right:10px;">Prev</button>
                    <span id="pageInfo"></span>
                    <button id="nextPage" style="margin-left:10px;">Next</button>
                </div>
                <script>
                const results = <?php echo json_encode($results); ?>;

                window.results = <?php echo json_encode($results); ?>;
                </script>
                <script src="student_results.js"></script>

                </script>
            <?php endif; ?>
        </div>
    </main>
</div>
</body>
</html>
