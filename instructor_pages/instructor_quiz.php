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


// Handle AJAX request to save quiz
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['quizData'], $_POST['quizName'])) {
    $response = ["success" => false, "message" => "Unknown error."];
    $quizData = json_decode($_POST['quizData'], true);
    $quiz_name = trim($_POST['quizName']);
    $set_attempts = isset($_POST['setAttempts']) ? intval($_POST['setAttempts']) : 1;
    // DO NOT reset $instructor_id here! Use the one from above.
    // Only proceed if a valid instructor_id was found
    if ($instructor_id > 0 && is_array($quizData) && count($quizData) > 0 && $quiz_name !== '') {
        // Get the current max quizzes_id from quizzes table and increment by 1
        $result = $conn->query("SELECT MAX(quizzes_id) AS max_quizzes_id FROM quizzes");
        $row = $result ? $result->fetch_assoc() : null;
        $quizzes_id = ($row && $row['max_quizzes_id']) ? intval($row['max_quizzes_id']) + 1 : 1;
        // Only insert question rows below
        $stmt = $conn->prepare("INSERT INTO quizzes (quizzes_id, instructor_id, set_attempts, question, option_a, option_b, option_c, option_d, correct_option, saved_quiz_name) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
        if (!$stmt) {
            $response = ["success" => false, "message" => "Prepare failed: " . $conn->error];
            header('Content-Type: application/json');
            echo json_encode($response);
            exit();
        }
        $question = $option_a = $option_b = $option_c = $option_d = $correct = $quiz_name_var = '';
        $allSuccess = true;
        $stmt->bind_param('iiisssssss', $quizzes_id, $instructor_id, $set_attempts, $question, $option_a, $option_b, $option_c, $option_d, $correct, $quiz_name_var);
        foreach ($quizData as $q) {
            $question = isset($q['question']) ? trim($q['question']) : '';
            $option_a = isset($q['options']['A']) ? trim($q['options']['A']) : '';
            $option_b = isset($q['options']['B']) ? trim($q['options']['B']) : '';
            $option_c = isset($q['options']['C']) ? trim($q['options']['C']) : '';
            $option_d = isset($q['options']['D']) ? trim($q['options']['D']) : '';
            $correct = isset($q['correct']) ? trim($q['correct']) : '';
            $quiz_name_var = $quiz_name;
            if (empty($question) || empty($option_a) || empty($option_b) || empty($option_c) || empty($option_d) || empty($correct)) {
                continue;
            }
            if (!$stmt->execute()) {
                $allSuccess = false;
                $response = ["success" => false, "message" => "Error saving question: " . $stmt->error];
                break;
            }
        }
        $stmt->close();
        if ($allSuccess) {
            $response = ["success" => true, "message" => "Quiz created with unique quizzes_id $quizzes_id and set_attempts $set_attempts!"];
        }
    } else if ($instructor_id === 0) {
        $response = ["success" => false, "message" => "Instructor not found. Please log in again."];
    } else {
        $response = ["success" => false, "message" => "No quiz data received or quiz name missing."];
    }
    header('Content-Type: application/json');
    echo json_encode($response);
    exit();
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
    <title>Instructor Quiz Builder</title>
    <link rel="stylesheet" href="instructor_quiz.css">
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
        <main class="main_content" style="display: flex; flex-direction: row; gap: 40px; align-items: flex-start;">
            <div style="flex:1;">
                <form class="quiz-form" onsubmit="event.preventDefault(); addQuestion();">
                    <label>Quiz Name:
                        <input type="text" id="quizNameInput" required>
                    </label>
                    <h1 class="quiz-title">Create a New Quiz</h1>
                    <label>Question:
                        <input type="text" id="questionInput">
                    </label>
                    <label>Option A:
                        <input type="text" id="optionA">
                    </label>
                    <label>Option B:
                        <input type="text" id="optionB">
                    </label>
                    <label>Option C:
                        <input type="text" id="optionC">
                    </label>
                    <label>Option D:
                        <input type="text" id="optionD">
                    </label>
                    <label>Correct Answer:
                        <select id="correctOption">
                            <option value="A">A</option>
                            <option value="B">B</option>
                            <option value="C">C</option>
                            <option value="D">D</option>
                        </select>
                    </label>
                    <button class="add-btn" type="submit">Add Question</button>
                    <button class="add-btn" type="button" id="setAttemptsBtn" style="margin-left:10px;">Set Attempts</button>
                    <span id="attemptsDisplay" style="margin-left:10px;color:#5b6291;"></span>
                </form>
                <div id="saveResult"></div>
            </div>
            <div style="flex:1;">
                <div id="quizListContainer">
                    <table id="quizTable" class="quiz-table">
                        <thead>
                            <tr><th colspan="10">Quiz Questions</th></tr>
                        </thead>
                        <tbody id="quizList"></tbody>
                    </table>
                    <div class="pagination-controls">
                        <button id="prevBtn" onclick="prevPage()">Previous</button>
                        <button id="nextBtn" onclick="nextPage()">Next</button>
                    </div>
                    <button id="saveQuizBtn" onclick="saveQuiz()" style="display:none; margin-top: 16px;">Save Quiz</button>
                </div>
            </div>
        </main>
    </div>
<script src="instructor_quiz.js"></script>
</body>
</html>
