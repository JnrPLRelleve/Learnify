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
$course_id = isset($_GET['course_id']) ? intval($_GET['course_id']) : 0;
$quiz_id = isset($_GET['quiz_id']) ? intval($_GET['quiz_id']) : 0;
$course_title = '';
$questions = [];
$max_attempts = 1;
$current_attempts = 0;
if ($course_id > 0 && $quiz_id > 0) {
    // Get course title
    $course_title_res = $conn->query("SELECT title FROM courses WHERE id = $course_id LIMIT 1");
    if ($course_title_res && $course_title_res->num_rows > 0) {
        $course_title_row = $course_title_res->fetch_assoc();
        $course_title = $course_title_row['title'];
    }
    // Fetch all questions for this quizzes_id
    $qres = $conn->query("SELECT id, question, option_a, option_b, option_c, option_d, correct_option FROM quizzes WHERE quizzes_id = $quiz_id ORDER BY id ASC");
    if ($qres && $qres->num_rows > 0) {
        while ($row = $qres->fetch_assoc()) {
            $questions[] = $row;
        }
    }
    // Get set_attempts from quizzes meta row
    $attempts_res = $conn->query("SELECT set_attempts FROM quizzes WHERE quizzes_id = $quiz_id AND set_attempts IS NOT NULL LIMIT 1");
    if ($attempts_res && $attempts_res->num_rows > 0) {
        $attempts_row = $attempts_res->fetch_assoc();
        $max_attempts = intval($attempts_row['set_attempts']);
    }
    // Get current attempts from quiz_results (count unique quiz_attempts for this student and quizzes_id)
    $student_id = 0;
    $res = $conn->query("SELECT id FROM users WHERE username = '" . $conn->real_escape_string($_SESSION['username']) . "' LIMIT 1");
    if ($res && $res->num_rows > 0) {
        $row = $res->fetch_assoc();
        $student_id = intval($row['id']);
    }
    $attempts_count_res = $conn->query("SELECT COUNT(DISTINCT quiz_attempts) as cnt FROM quiz_results WHERE student_id = $student_id AND take_quiz_id IN (SELECT id FROM quizzes WHERE quizzes_id = $quiz_id)");
    if ($attempts_count_res && $attempts_count_res->num_rows > 0) {
        $attempts_count_row = $attempts_count_res->fetch_assoc();
        $current_attempts = intval($attempts_count_row['cnt']);
    }
    // Enforce attempts limit: if current_attempts >= max_attempts, do not allow retake
    if ($current_attempts >= $max_attempts) {
        $questions = [];
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
    <title>Take Quiz</title>
    <link rel="stylesheet" href="student_dashboard.css">
    <link rel="stylesheet" href="take_quiz.css">
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
        <div class="quiz-container">
            <div id="quizResultBox" class="quiz-result-box"></div>
            <h1 style="color:#5b6291; margin-bottom: 18px;">Quiz: <?php echo htmlspecialchars($course_title); ?></h1>
            <?php if (empty($questions)): ?>
                <div style="text-align:center;">You have 0 attempts for this quiz.</div>
            <?php elseif ($current_attempts >= $max_attempts): ?>
                <div style="text-align:center; color:red; font-size:1.2em;">You have reached the maximum number of attempts (<?php echo $max_attempts; ?>) for this quiz.</div>
            <?php else: ?>
                <div style="text-align:center; color:#5b6291; font-size:1.1em; margin-bottom:10px;">Attempt <?php echo $current_attempts+1; ?> of <?php echo $max_attempts; ?></div>
                <form id="quizForm" onsubmit="event.preventDefault(); submitQuiz();">
                    <div id="quizQuestionBlock"></div>
                    <button type="button" class="quiz-btn" id="nextBtn" onclick="nextQuestion()">Next</button>
                    <button type="submit" class="quiz-btn" id="submitBtn" style="display:none;">Submit Quiz</button>
                </form>
            <?php endif; ?>
        </div>
    </main>
</div>
<script>
const questions = <?php echo json_encode($questions); ?>;
let current = 0;
let answers = [];
function renderQuestion() {
    if (questions.length === 0) return;
    const q = questions[current];
    let html = `<div class='quiz-question'>${current+1}. ${q.question}</div><div class='quiz-options'>`;
    ['A','B','C','D'].forEach(opt => {
        html += `<label><input type='radio' name='option' value='${opt}' ${answers[current]===opt?'checked':''}> ${q['option_'+opt.toLowerCase()]}</label>`;
    });
    html += '</div>';
    document.getElementById('quizQuestionBlock').innerHTML = html;
    document.getElementById('nextBtn').style.display = (current < questions.length-1) ? 'inline-block' : 'none';
    document.getElementById('submitBtn').style.display = (current === questions.length-1) ? 'inline-block' : 'none';
}
document.getElementById('quizQuestionBlock') && renderQuestion();
function nextQuestion() {
    const selected = document.querySelector('input[name="option"]:checked');
    if (!selected) { alert('Please select an option.'); return; }
    answers[current] = selected.value;
    current++;
    renderQuestion();
}
function submitQuiz() {
    const selected = document.querySelector('input[name="option"]:checked');
    if (!selected) { alert('Please select an option.'); return; }
    answers[current] = selected.value;
    let correct = 0;
    let resultDetails = '';
    let resultsToSave = [];
    questions.forEach((q, idx) => {
        const isCorrect = (answers[idx] && answers[idx] === q.correct_option) ? 1 : 0;
        if (isCorrect) correct++;
        resultDetails += `<div class='result-detail'>Q${idx+1}: <b>${answers[idx]||'-'}</b> <span style='color:#888;'>(Correct: <b>${q.correct_option}</b>)</span></div>`;
        resultsToSave.push({
            take_quiz_id: q.id,
            selected_option: answers[idx] || '',
            is_correct: isCorrect
        });
    });
    document.getElementById('quizResultBox').style.display = 'block';
    document.getElementById('quizResultBox').innerHTML = `<div class='score'>Result: ${correct} / ${questions.length}</div>` + resultDetails;
    document.getElementById('quizForm').style.display = 'none';
    // Save result to DB, including quiz_attempts
    fetch('save_quiz_result.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({
            course_id: <?php echo json_encode($course_id); ?>,
            quiz_id: <?php echo json_encode($quiz_id); ?>,
            results: resultsToSave,
            score: correct,
            total: questions.length,
            quiz_attempts: <?php echo json_encode($current_attempts + 1); ?>
        })
    });
}
</script>
</body>
</html>
