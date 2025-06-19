<?php
session_start();
header('Content-Type: application/json');
if (!isset($_SESSION['username']) || $_SESSION['role'] !== 'student') {
    echo json_encode([]);
    exit();
}
if (!isset($_GET['course_id'])) {
    echo json_encode([]);
    exit();
}
$course_id = intval($_GET['course_id']);
$conn = new mysqli('localhost', 'root', '', 'learnify');
if ($conn->connect_error) {
    echo json_encode([]);
    exit();
}
// Get instructor_id for the course
$stmt = $conn->prepare('SELECT instructor_id FROM courses WHERE id = ?');
$stmt->bind_param('i', $course_id);
$stmt->execute();
$stmt->bind_result($instructor_id);
$stmt->fetch();
$stmt->close();
if (!$instructor_id) {
    echo json_encode([]);
    $conn->close();
    exit();
}
// Only allow access if student is enrolled in the course
$stmt = $conn->prepare('SELECT 1 FROM enrollments WHERE student_id = (SELECT id FROM users WHERE username = ?) AND course_id = ?');
$stmt->bind_param('si', $_SESSION['username'], $course_id);
$stmt->execute();
$stmt->store_result();
if ($stmt->num_rows === 0) {
    echo json_encode([]);
    $stmt->close();
    $conn->close();
    exit();
}
$stmt->close();
// Get files uploaded by the instructor for this course ONLY
$stmt = $conn->prepare('SELECT file_name, file_type FROM file_uploads WHERE user_id = ? AND course_id = ?');
$stmt->bind_param('ii', $instructor_id, $course_id);
$stmt->execute();
$result = $stmt->get_result();
$files = [];
while ($row = $result->fetch_assoc()) {
    $files[] = $row;
}
$stmt->close();
$conn->close();
echo json_encode($files);
