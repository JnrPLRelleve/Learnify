<?php
session_start();
header('Content-Type: application/json');
if (!isset($_SESSION['username']) || $_SESSION['role'] !== 'student') {
    echo json_encode(['success' => false, 'error' => 'Unauthorized']);
    exit();
}
if (!isset($_POST['course_id'])) {
    echo json_encode(['success' => false, 'error' => 'Missing course_id']);
    exit();
}
$course_id = intval($_POST['course_id']);
$conn = new mysqli('localhost', 'root', '', 'learnify');
if ($conn->connect_error) {
    echo json_encode(['success' => false, 'error' => 'DB error']);
    exit();
}
// Get student id
$stmt = $conn->prepare('SELECT id FROM users WHERE username = ?');
$stmt->bind_param('s', $_SESSION['username']);
$stmt->execute();
$stmt->bind_result($student_id);
$stmt->fetch();
$stmt->close();
// Remove from enrollments
$stmt = $conn->prepare('DELETE FROM enrollments WHERE student_id = ? AND course_id = ?');
$stmt->bind_param('ii', $student_id, $course_id);
$stmt->execute();
$stmt->close();
// Remove progress (delete all progress rows for this student and course)
$stmt = $conn->prepare('DELETE FROM progress_tracking WHERE student_id = ? AND course_id = ?');
$stmt->bind_param('ii', $student_id, $course_id);
$stmt->execute();
$stmt->close();
// Also clear localStorage progress for this course (client-side JS required)
// Do NOT delete the course itself
$conn->close();
echo json_encode(['success' => true]);
