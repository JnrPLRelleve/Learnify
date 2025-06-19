<?php
// Database connection
$conn = new mysqli('localhost', 'root', '', 'learnify');
if ($conn->connect_error) {
    die('Connection failed: ' . $conn->connect_error);
}

// Get instructor id from session
session_start();
if (!isset($_SESSION['username']) || !isset($_SESSION['role']) || $_SESSION['role'] !== 'instructor') {
    header('Location: ../login_pages/loginpage.php');
    exit();
}
$instructor_username = $_SESSION['username'];
$instructor_id = 0;
$res = $conn->query("SELECT id FROM users WHERE username='".$conn->real_escape_string($instructor_username)."' LIMIT 1");
if ($res && $row = $res->fetch_assoc()) {
    $instructor_id = intval($row['id']);
}

// Fetch all courses for this instructor
$courses = [];
$res = $conn->query("SELECT id, title FROM courses WHERE instructor_id = $instructor_id");
while ($row = $res->fetch_assoc()) {
    $courses[$row['id']] = $row['title'];
}

// Fetch all students enrolled in these quizzes
$students = [];
if (!empty($courses)) {
    $quiz_ids = [];
    foreach ($courses as $cid => $ctitle) {
        $quiz_id_res = $conn->query("SELECT quiz_id FROM courses WHERE id = $cid");
        if ($quiz_id_res && $row = $quiz_id_res->fetch_assoc()) {
            $quiz_ids = array_merge($quiz_ids, array_filter(array_map('trim', explode(',', $row['quiz_id']))));
        }
    }
    $quiz_ids = array_unique($quiz_ids);
    if (!empty($quiz_ids)) {
        $quiz_ids_str = implode(',', array_map('intval', $quiz_ids));
        $sql = "SELECT e.course_id as quizzes_id, u.id as student_id, u.fullname, u.username FROM enrollments e JOIN users u ON e.student_id = u.id WHERE e.course_id IN ($quiz_ids_str)";
        $res = $conn->query($sql);
        while ($row = $res->fetch_assoc()) {
            $students[$row['quizzes_id']][] = $row;
        }
    }
}

// Fetch quiz results for each student in these courses
$quiz_results = [];
if (!empty($students)) {
    foreach ($students as $course_id => $stuArr) {
        foreach ($stuArr as $stu) {
            $sid = intval($stu['student_id']);
            // Get all quiz results for this student in this course
            $res = $conn->query("SELECT score, submitted_at, quiz_attempts FROM quiz_results WHERE student_id = $sid");
            $results = [];
            if ($res && $res instanceof mysqli_result) {
                while ($row = $res->fetch_assoc()) {
                    $results[] = $row;
                }
            }
            $quiz_results[$course_id][$sid] = $results;
        }
    }
}

// Fetch all quizzes for these courses (to get set_attempts and quiz name)
$course_quizzes = [];
if (!empty($courses)) {
    foreach ($courses as $cid => $ctitle) {
        // Get quiz_id(s) for this course (comma-separated quizzes_id)
        $quiz_id_res = $conn->query("SELECT quiz_id FROM courses WHERE id = $cid");
        $quiz_ids = [];
        if ($quiz_id_res && $row = $quiz_id_res->fetch_assoc()) {
            $quiz_ids = array_filter(array_map('trim', explode(',', $row['quiz_id'])));
        }
        if (!empty($quiz_ids)) {
            $quiz_ids_str = implode(',', array_map('intval', $quiz_ids));
            $res = $conn->query("SELECT quizzes_id, saved_quiz_name, MAX(set_attempts) as set_attempts FROM quizzes WHERE quizzes_id IN ($quiz_ids_str) GROUP BY quizzes_id, saved_quiz_name");
            while ($row = $res->fetch_assoc()) {
                $course_quizzes[$cid][] = $row;
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Course Quiz Results</title>
    <link rel="stylesheet" href="instructor_QuizResults.css">
</head>
<body>
<div class="back-link-top">
    <a href="instructor_dashboard.php" class="back-link">&larr; Back</a>
</div>
<div class="container">
    <h1>Courses Quiz Results</h1>
    <div class="filter-bar">
        <div>
            <label for="courseFilter">Filter by Course:</label>
            <select id="courseFilter">
                <option value="all">All Courses</option>
                <?php foreach ($courses as $course_id => $course_title): ?>
                    <option value="course_<?php echo $course_id; ?>"><?php echo htmlspecialchars($course_title); ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div>
            <label for="studentSearch">Search Student:</label>
            <input type="text" id="studentSearch" placeholder="Type student name...">
        </div>
    </div>
    <?php if (empty($courses)): ?>
        <div class="no-data">You have no courses assigned.</div>
    <?php else: ?>
        <?php foreach ($courses as $course_id => $course_title): ?>
            <div class="course-block" id="course_<?php echo $course_id; ?>">
                <h2>Course: <?php echo htmlspecialchars($course_title); ?></h2>
                <?php
                // Get all quizzes for this course
                $quiz_titles = [];
                $quiz_ids = [];
                $quiz_items = [];
                if (!empty($course_quizzes[$course_id])) {
                    foreach ($course_quizzes[$course_id] as $quiz) {
                        $quiz_titles[$quiz['quizzes_id']] = $quiz['saved_quiz_name'];
                        $quiz_ids[] = $quiz['quizzes_id'];
                        // Get number of items for this quiz
                        $qitem_res = $conn->query("SELECT COUNT(*) as cnt FROM quizzes WHERE quizzes_id = " . intval($quiz['quizzes_id']));
                        $qitem_row = $qitem_res && $qitem_res->num_rows > 0 ? $qitem_res->fetch_assoc() : null;
                        $quiz_items[$quiz['quizzes_id']] = $qitem_row ? intval($qitem_row['cnt']) : 0;
                    }
                }
                // Get all students enrolled in this course
                $stu_res = $conn->query("SELECT u.id, u.fullname FROM enrollments e JOIN users u ON e.student_id = u.id WHERE e.course_id = $course_id");
                ?>
                <table class="quiz-results-table">
                    <thead>
                        <tr>
                            <th>STUDENT NAMES:</th>
                            <?php foreach ($quiz_titles as $qid => $qtitle): ?>
                                <th><?php echo htmlspecialchars($qtitle); ?></th>
                                <th>Q<?php echo (array_search($qid, array_keys($quiz_titles)) + 1); ?> Score</th>
                                <th>Q<?php echo (array_search($qid, array_keys($quiz_titles)) + 1); ?> Grade</th>
                            <?php endforeach; ?>
                            <th>AVERAGE GRADE</th>
                            <th>LETTER GRADE</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php if ($stu_res && $stu_res->num_rows > 0): ?>
                        <?php while ($stu = $stu_res->fetch_assoc()): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($stu['fullname']); ?></td>
                                <?php
                                $percentages = [];
                                foreach ($quiz_ids as $qid) {
                                    // Get most recent score for this quiz and student
                                    $score_res = $conn->query("SELECT score FROM quiz_results qr JOIN quizzes qz ON qr.take_quiz_id = qz.id WHERE qr.student_id = " . intval($stu['id']) . " AND qz.quizzes_id = $qid ORDER BY qr.submitted_at DESC LIMIT 1");
                                    $score = null;
                                    $items = isset($quiz_items[$qid]) ? $quiz_items[$qid] : 0;
                                    $percent = null;
                                    $letter = '-';
                                    if ($score_res && $score_row = $score_res->fetch_assoc()) {
                                        $score = floatval($score_row['score']);
                                        if ($items > 0) {
                                            $percent = round(($score / $items) * 100, 2);
                                            // Letter grade for this quiz
                                            if ($percent >= 90) $letter = 'A';
                                            elseif ($percent >= 80) $letter = 'B';
                                            elseif ($percent >= 70) $letter = 'C';
                                            elseif ($percent >= 60) $letter = 'D';
                                            else $letter = 'F';
                                            $percentages[] = $percent;
                                        }
                                    }
                                    echo '<td>' . htmlspecialchars($quiz_titles[$qid]) . '</td>';
                                    echo '<td>' . ($score !== null ? $score . '/' . $items : '<span class="no-score">-</span>') . '</td>';
                                    echo '<td>' . ($letter !== '-' ? $letter : '<span class="no-score">-</span>') . '</td>';
                                }
                                $avg = count($percentages) > 0 ? round(array_sum($percentages)/count($percentages), 2) : '-';
                                $overall_letter = '-';
                                if ($avg !== '-') {
                                    if ($avg >= 90) $overall_letter = 'A';
                                    elseif ($avg >= 80) $overall_letter = 'B';
                                    elseif ($avg >= 70) $overall_letter = 'C';
                                    elseif ($avg >= 60) $overall_letter = 'D';
                                    else $overall_letter = 'F';
                                }
                                echo '<td class="avg-grade">' . ($avg !== '-' ? $avg . '%' : '<span class="no-score">No grades</span>') . '</td>';
                                echo '<td class="letter-grade">' . $overall_letter . '</td>';
                                ?>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr><td colspan="<?php echo 3 * count($quiz_titles) + 3; ?>" class="no-data">No students enrolled in this course.</td></tr>
                    <?php endif; ?>
                    </tbody>
                </table>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>
    <div class="grading-rubric">
        <h3>Grading Rubric</h3>
        <table>
            <thead>
                <tr>
                    <th>Grade</th>
                    <th>Score Range</th>
                </tr>
            </thead>
            <tbody>
                <tr><td>A</td><td>90 – 100</td></tr>
                <tr><td>B</td><td>80 – 89</td></tr>
                <tr><td>C</td><td>70 – 79</td></tr>
                <tr><td>D</td><td>60 – 69</td></tr>
                <tr><td>F</td><td>Below 60</td></tr>
            </tbody>
        </table>
    </div>
</div>
<div class="back-link-bottom">
    <a href="instructor_dashboard.php" class="back-link">&larr; Back</a>
</div>
<script src="instructor_QuizResults.js"></script>
</body>
</html>