<?php
session_start();
header('Content-Type: application/json');
if (!isset($_SESSION['username']) || !isset($_SESSION['role']) || $_SESSION['role'] !== 'instructor') {
    echo json_encode(['success' => false, 'error' => 'Unauthorized']);
    exit;
}
if (!isset($_POST['quiz_name'])) {
    echo json_encode(['success' => false, 'error' => 'No quiz name provided']);
    exit;
}
$conn = new mysqli('localhost', 'root', '', 'learnify');
if ($conn->connect_error) {
    echo json_encode(['success' => false, 'error' => 'DB connection error']);
    exit;
}
$quiz_name = $conn->real_escape_string($_POST['quiz_name']);
$username = $conn->real_escape_string($_SESSION['username']);
$res = $conn->query("SELECT id FROM users WHERE username = '$username' LIMIT 1");
if (!$res || !$res->num_rows) {
    echo json_encode(['success' => false, 'error' => 'Instructor not found']);
    exit;
}
$row = $res->fetch_assoc();
$instructor_id = intval($row['id']);
$del = $conn->query("DELETE FROM quizzes WHERE saved_quiz_name = '$quiz_name' AND instructor_id = $instructor_id");
if ($del) {
    echo json_encode(['success' => true]);
} else {
    echo json_encode(['success' => false, 'error' => 'Delete failed']);
}
?>
