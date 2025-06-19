<?php
// Database connection
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "learnify";
$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Fetch all unique saved_quiz_name for this instructor
session_start();
$instructor_id = 0;
$session_user_id = isset($_SESSION['user_id']) ? intval($_SESSION['user_id']) : 0;
if ($session_user_id) {
    $user_result = $conn->query("SELECT id FROM users WHERE id = $session_user_id OR user_id = $session_user_id LIMIT 1");
    if ($user_result && $user_result->num_rows > 0) {
        $user_row = $user_result->fetch_assoc();
        $instructor_id = intval($user_row['id']);
    }
}
if ($instructor_id === 0 && isset($_SESSION['username'])) {
    $user_stmt = $conn->prepare('SELECT id FROM users WHERE username = ?');
    $user_stmt->bind_param('s', $_SESSION['username']);
    $user_stmt->execute();
    $user_stmt->bind_result($instructor_id);
    $user_stmt->fetch();
    $user_stmt->close();
}
$quizNames = [];
$sql = "SELECT saved_quiz_name, COUNT(*) as question_count FROM quizzes WHERE instructor_id = $instructor_id GROUP BY saved_quiz_name ORDER BY saved_quiz_name DESC";
$result = $conn->query($sql);
if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $quizNames[] = $row;
    }
}

// Fetch courses for this instructor
$courses = [];
$courses_sql = "SELECT id, title FROM courses WHERE instructor_id = $instructor_id ORDER BY created_at DESC";
$courses_result = $conn->query($courses_sql);
if ($courses_result && $courses_result->num_rows > 0) {
    while ($row = $courses_result->fetch_assoc()) {
        $courses[] = $row;
    }
}

// If a quiz name is selected, fetch its questions grouped by quizzes_id
$selectedQuiz = isset($_GET['quiz']) ? $conn->real_escape_string($_GET['quiz']) : '';
$quizQuestions = [];
if ($selectedQuiz) {
    // Fetch all questions for this quiz name, grouped by quizzes_id
    $qsql = "SELECT quizzes_id, GROUP_CONCAT(question SEPARATOR '\n') as questions, GROUP_CONCAT(option_a SEPARATOR '\n') as options_a, GROUP_CONCAT(option_b SEPARATOR '\n') as options_b, GROUP_CONCAT(option_c SEPARATOR '\n') as options_c, GROUP_CONCAT(option_d SEPARATOR '\n') as options_d, GROUP_CONCAT(correct_option SEPARATOR '\n') as correct_options FROM quizzes WHERE instructor_id = $instructor_id AND saved_quiz_name = '$selectedQuiz' GROUP BY quizzes_id ORDER BY quizzes_id ASC";
    $qres = $conn->query($qsql);
    if ($qres && $qres->num_rows > 0) {
        while ($row = $qres->fetch_assoc()) {
            $quizQuestions[] = $row;
        }
    }
}

// Handle Set to Course form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['set_to_course'])) {
    $selected_quiz_name = $conn->real_escape_string($_POST['quiz_name']);
    $selected_course_id = intval($_POST['course_id']);
    // Get all quizzes_id for this quiz name and instructor
    $quiz_ids_res = $conn->query("SELECT quizzes_id FROM quizzes WHERE instructor_id = $instructor_id AND saved_quiz_name = '$selected_quiz_name'");
    $quiz_ids = [];
    if ($quiz_ids_res && $quiz_ids_res->num_rows > 0) {
        while ($row = $quiz_ids_res->fetch_assoc()) {
            $quiz_ids[] = intval($row['quizzes_id']);
        }
    }
    if (!empty($quiz_ids)) {
        $quiz_ids_str = implode(',', $quiz_ids);
        // Remove these quizzes_id from all other courses
        $remove_sql = "UPDATE courses SET quiz_id = TRIM(BOTH ',' FROM REPLACE(CONCAT(',', quiz_id, ','), '," . str_replace(',', ',,', $quiz_ids_str) . ",', ',')) WHERE quiz_id IS NOT NULL AND quiz_id != '' AND id != $selected_course_id";
        $conn->query($remove_sql);
        // Now assign these quizzes_id to the selected course only
        $current_quiz_id_res = $conn->query("SELECT quiz_id FROM courses WHERE id = $selected_course_id");
        $current_quiz_ids = [];
        if ($current_quiz_id_res && $row = $current_quiz_id_res->fetch_assoc()) {
            $current_quiz_ids = array_filter(array_map('trim', explode(',', $row['quiz_id'])));
        }
        // Remove any of these quizzes_id from the current list (if present)
        $current_quiz_ids = array_diff($current_quiz_ids, $quiz_ids);
        // Add the new quizzes_id
        $all_quiz_ids = array_merge($current_quiz_ids, $quiz_ids);
        $quiz_ids_str_final = implode(',', $all_quiz_ids);
        $update = $conn->query("UPDATE courses SET quiz_id = '$quiz_ids_str_final' WHERE id = $selected_course_id");
        if ($update) {
            $set_success = true;
        } else {
            $set_error = 'Quiz not found or could not be set.';
        }
    }
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

// Fetch quizzes deployed to each course for this instructor
$deployed_quizzes_by_course = [];
foreach ($courses as $c) {
    $cid = intval($c['id']);
    // Get quiz_id(s) from courses table (comma-separated string)
    $quiz_ids = [];
    $course_quiz_id_res = $conn->query("SELECT quiz_id FROM courses WHERE id = $cid AND quiz_id IS NOT NULL AND quiz_id != ''");
    if ($course_quiz_id_res && $course_quiz_id_res->num_rows > 0) {
        $row = $course_quiz_id_res->fetch_assoc();
        $quiz_ids = array_filter(array_map('trim', explode(',', $row['quiz_id'])));
    }
    if (!empty($quiz_ids)) {
        $quiz_ids_str = implode(',', array_map('intval', $quiz_ids));
        $qres = $conn->query("SELECT saved_quiz_name FROM quizzes WHERE quizzes_id IN ($quiz_ids_str) AND instructor_id = $instructor_id");
        while ($qres && $row = $qres->fetch_assoc()) {
            $deployed_quizzes_by_course[$cid][] = $row['saved_quiz_name'];
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View Saved Quizzes</title>
    <link rel="stylesheet" href="instructor_viewSaved_Quiz.css">
    
</head>
<body>
<div class="container">
    <aside class="sidebar">
        <div class="profile_pic"><img src="<?php echo htmlspecialchars($profilePic); ?>" alt="sample"></div>
        <h2><span style="font-size:1em;font-weight:400;"><?php echo htmlspecialchars($_SESSION['username']); ?></span></h2>
        <button class="sidebar_btn" onclick="window.location.href='instructor_dashboard.php'">Courses</button>
        <button class="sidebar_btn" onclick="location.href='instructor_Quiz_Inter.php'">Quiz Interface</button>
        <button class="sidebar_btn" onclick="location.href='create_course.php'">Create Course</button>
        <button class="settings_btn" onclick="location.href='INS_settings.php'">Settings</button>
        <button class="logout_btn1" type="button" onclick="window.location.href='../login_pages/logout.php'">Logout</button>
    </aside>
    <main class="main_content" style="display: flex; flex-direction: row; gap: 40px; align-items: flex-start;">
        <div style="flex:1;">
            <h1 class="quiz-title">Saved Quizzes</h1>
            <div class="quiz-list-block">
                <table class="quiz-list-table">
                    <thead>
                        <tr>
                            <th style="width:60%;text-align:left;">Quiz Name</th>
                            <th style="width:15%;text-align:center;">Questions</th>
                            <th style="width:25%;text-align:center;">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php
                    $perPage = 4;
                    $page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
                    $totalQuizzes = count($quizNames);
                    $totalPages = max(1, ceil($totalQuizzes / $perPage));
                    $startIdx = ($page - 1) * $perPage;
                    $quizzesToShow = array_slice($quizNames, $startIdx, $perPage);
                    foreach($quizzesToShow as $quiz): ?>
                        <tr>
                            <td style="word-break:break-word;white-space:pre-line;vertical-align:middle;text-align:left;max-width:350px;"><?php echo htmlspecialchars($quiz['saved_quiz_name']); ?></td>
                            <td style="text-align:center;vertical-align:middle;"> <?php echo $quiz['question_count']; ?> </td>
                            <td style="text-align:center;vertical-align:middle;">
                                <div class="action-btn-group">
                                    <a href="?page=<?php echo $page; ?>&quiz=<?php echo urlencode($quiz['saved_quiz_name']); ?>" class="view-btn">View</a>
                                    <button class="delete-btn set-to-course-btn" data-quiz="<?php echo htmlspecialchars($quiz['saved_quiz_name']); ?>">Set to Course</button>
                                    <button class="delete-btn">Delete</button>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    <?php if (empty($quizNames)): ?>
                        <tr><td colspan="3" style="text-align:center;">No saved quizzes found.</td></tr>
                    <?php endif; ?>
                    </tbody>
                </table>
                <div class="pagination-controls">
                    <?php if ($page > 1): ?>
                        <a href="?page=<?php echo $page-1; ?>" class="view-btn">Previous</a>
                    <?php else: ?>
                        <button class="view-btn" disabled>Previous</button>
                    <?php endif; ?>
                    <?php if ($page < $totalPages): ?>
                        <a href="?page=<?php echo $page+1; ?>" class="view-btn">Next</a>
                    <?php else: ?>
                        <button class="view-btn" disabled>Next</button>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        <div style="flex:1;">
            <?php if ($selectedQuiz): ?>
            <?php
                $questionsPerPage = 2;
                $totalQuestions = count($quizQuestions);
                $totalPages = max(1, ceil($totalQuestions / $questionsPerPage));
                $page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
                $startIdx = ($page - 1) * $questionsPerPage;
                $questionsToShow = array_slice($quizQuestions, $startIdx, $questionsPerPage);
            ?>
            <div class="quiz-details-block" style="max-height:520px;overflow-y:auto;">
                <h3>Quiz: <?php echo htmlspecialchars($selectedQuiz); ?></h3>
                <?php foreach($questionsToShow as $idx => $q): ?>
                    <?php
                    // Split merged fields into arrays
                    $questionsArr = isset($q['questions']) ? explode("\n", $q['questions']) : [];
                    $optionsAArr = isset($q['options_a']) ? explode("\n", $q['options_a']) : [];
                    $optionsBArr = isset($q['options_b']) ? explode("\n", $q['options_b']) : [];
                    $optionsCArr = isset($q['options_c']) ? explode("\n", $q['options_c']) : [];
                    $optionsDArr = isset($q['options_d']) ? explode("\n", $q['options_d']) : [];
                    $correctArr = isset($q['correct_options']) ? explode("\n", $q['correct_options']) : [];
                    $numQuestions = count($questionsArr);
                    for ($i = 0; $i < $numQuestions; $i++): ?>
                        <div class="question-item">
                            <div class="question"><?php echo ($startIdx+$idx+1) . '.' . ($numQuestions > 1 ? ($i+1) : '') . ' ' . htmlspecialchars($questionsArr[$i] ?? ''); ?></div>
                            <div><span class="option-label">A:</span> <?php echo htmlspecialchars($optionsAArr[$i] ?? ''); ?></div>
                            <div><span class="option-label">B:</span> <?php echo htmlspecialchars($optionsBArr[$i] ?? ''); ?></div>
                            <div><span class="option-label">C:</span> <?php echo htmlspecialchars($optionsCArr[$i] ?? ''); ?></div>
                            <div><span class="option-label">D:</span> <?php echo htmlspecialchars($optionsDArr[$i] ?? ''); ?></div>
                            <div class="correct">Correct: <?php echo htmlspecialchars($correctArr[$i] ?? ''); ?></div>
                        </div>
                    <?php endfor; ?>
                <?php endforeach; ?>
                <?php if (empty($questionsToShow)): ?>
                    <div>No questions found for this quiz.</div>
                <?php endif; ?>
            </div>
            <div class="pagination-controls">
                <?php if ($page > 1): ?>
                    <a href="?quiz=<?php echo urlencode($selectedQuiz); ?>&page=<?php echo $page-1; ?>" class="view-btn">Previous</a>
                <?php else: ?>
                    <button class="view-btn" disabled>Previous</button>
                <?php endif; ?>
                <?php if ($page < $totalPages): ?>
                    <a href="?quiz=<?php echo urlencode($selectedQuiz); ?>&page=<?php echo $page+1; ?>" class="view-btn">Next</a>
                <?php else: ?>
                    <button class="view-btn" disabled>Next</button>
                <?php endif; ?>
            </div>
            <?php endif; ?>
        </div>
    </main>
</div>
<!-- Set to Course Modal -->
<div id="setToCourseModal" style="display:none;position:fixed;top:0;left:0;width:100vw;height:100vh;background:rgba(0,0,0,0.3);z-index:1000;align-items:center;justify-content:center;">
    <div style="background:#fff;padding:32px 24px;border-radius:12px;min-width:320px;max-width:90vw;">
        <form method="post" style="display:flex;flex-direction:column;gap:16px;">
            <input type="hidden" name="quiz_name" id="modalQuizName">
            <label for="courseSelect">Select Course:</label>
            <select name="course_id" id="courseSelect" required>
                <option value="">-- Select Course --</option>
                <?php foreach($courses as $c): ?>
                    <option value="<?php echo $c['id']; ?>"><?php echo htmlspecialchars($c['title']); ?></option>
                <?php endforeach; ?>
            </select>
            <button type="submit" name="set_to_course" class="view-btn">Set Quiz to Course</button>
            <button type="button" onclick="closeSetToCourseModal()" class="delete-btn" style="background:#888;">Cancel</button>
        </form>
    </div>
</div>
<?php if (isset($set_success) && $set_success): ?>
    <script>alert('Quiz successfully set to course!');</script>
<?php elseif (isset($set_error)): ?>
    <script>alert('<?php echo $set_error; ?>');</script>
<?php endif; ?>
<script src="instructor_viewSaved_Quiz.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    document.querySelectorAll('.delete-btn:not(.set-to-course-btn)').forEach(function(btn) {
        btn.style.minWidth = '80px';
        btn.addEventListener('click', function(e) {
            e.preventDefault();
            if (confirm('Are you sure you want to delete this quiz?')) {
                // Find the quiz name from the row
                var row = btn.closest('tr');
                var quizName = row && row.querySelector('td') ? row.querySelector('td').textContent.trim() : '';
                if (!quizName) return;
                fetch('delete_quiz.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                    body: 'quiz_name=' + encodeURIComponent(quizName)
                })
                .then(res => res.json())
                .then(function(data) {
                    if (data.success) {
                        row.remove();
                        alert('Quiz deleted successfully.');
                    } else {
                        alert(data.error || 'Failed to delete quiz.');
                    }
                })
                .catch(() => alert('Error connecting to server.'));
            }
        });
    });
});
</script>
</body>
</html>
