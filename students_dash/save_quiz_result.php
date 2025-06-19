<?php
// c:/xampp/htdocs/learnify/students_dash/save_quiz_result.php
session_start();
header('Content-Type: application/json');
if (!isset($_SESSION['username']) || !isset($_SESSION['role']) || $_SESSION['role'] !== 'student') {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit();
}
$data = json_decode(file_get_contents('php://input'), true);
if (!$data || !isset($data['results'], $data['quiz_id'], $data['score'], $data['quiz_attempts'])) {
    echo json_encode(['success' => false, 'message' => 'Invalid data']);
    exit();
}
$conn = new mysqli('localhost', 'root', '', 'learnify');
if ($conn->connect_error) {
    echo json_encode(['success' => false, 'message' => 'DB error']);
    exit();
}
$student_username = $_SESSION['username'];
$student_id = 0;
$res = $conn->query("SELECT id FROM users WHERE username = '" . $conn->real_escape_string($student_username) . "' LIMIT 1");
if ($res && $row = $res->fetch_assoc()) {
    $student_id = intval($row['id']);
}
$course_id = isset($data['course_id']) ? intval($data['course_id']) : 0;
$quiz_id = intval($data['quiz_id']);
// Calculate next quiz_attempts value on backend
$attempts_count_res = $conn->query("SELECT MAX(quiz_attempts) as max_attempt FROM quiz_results WHERE student_id = $student_id AND course_id = $course_id AND take_quiz_id IN (SELECT id FROM quizzes WHERE quizzes_id = $quiz_id)");
$quiz_attempts = 1;
if ($attempts_count_res && $row = $attempts_count_res->fetch_assoc()) {
    $quiz_attempts = intval($row['max_attempt']) + 1;
}
$score = intval($data['score']);
$submitted_at = date('Y-m-d H:i:s');
$success = true;
foreach ($data['results'] as $result) {
    $take_quiz_id = intval($result['take_quiz_id']);
    $selected_option = $conn->real_escape_string($result['selected_option']);
    $is_correct = intval($result['is_correct']);
    $stmt = $conn->prepare("INSERT INTO quiz_results (take_quiz_id, student_id, course_id, selected_option, is_correct, score, submitted_at, quiz_attempts) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param('iiisissi', $take_quiz_id, $student_id, $course_id, $selected_option, $is_correct, $score, $submitted_at, $quiz_attempts);
    if (!$stmt->execute()) {
        $success = false;
        break;
    }
    $stmt->close();
}
echo json_encode(['success' => $success]);
