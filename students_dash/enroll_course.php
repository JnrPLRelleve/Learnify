<?php
session_start();
header('Content-Type: application/json');
if (!isset($_SESSION['username']) || $_SESSION['role'] !== 'student') {
    echo json_encode(['success' => false, 'error' => 'Unauthorized']);
    exit();
}
if (!isset($_POST['course_id'])) {
    echo json_encode(['success' => false, 'error' => 'No course selected']);
    exit();
}
$course_id = intval($_POST['course_id']);
$conn = new mysqli('localhost', 'root', '', 'learnify');
if ($conn->connect_error) {
    echo json_encode(['success' => false, 'error' => 'DB connection failed']);
    exit();
}
// Get student id
$stmt = $conn->prepare('SELECT id FROM users WHERE username = ?');
$stmt->bind_param('s', $_SESSION['username']);
$stmt->execute();
$stmt->bind_result($student_id);
$stmt->fetch();
$stmt->close();
if (!$student_id) {
    echo json_encode(['success' => false, 'error' => 'Student not found']);
    exit();
}
// Prevent duplicate enrollment
$stmt = $conn->prepare('SELECT id FROM enrollments WHERE student_id = ? AND course_id = ?');
$stmt->bind_param('ii', $student_id, $course_id);
$stmt->execute();
$stmt->store_result();
if ($stmt->num_rows > 0) {
    echo json_encode(['success' => false, 'error' => 'Already enrolled']);
    $stmt->close();
    exit();
}
$stmt->close();
// Enroll
$stmt = $conn->prepare('INSERT INTO enrollments (student_id, course_id) VALUES (?, ?)');
$stmt->bind_param('ii', $student_id, $course_id);
$success = $stmt->execute();
$stmt->close();
$conn->close();
echo json_encode(['success' => $success]);
