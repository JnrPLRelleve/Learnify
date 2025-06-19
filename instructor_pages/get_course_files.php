<?php
session_start();
header('Content-Type: application/json');
if (!isset($_SESSION['username']) || $_SESSION['role'] !== 'instructor') {
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
// Get instructor user_id
$stmt = $conn->prepare('SELECT id FROM users WHERE username = ?');
$stmt->bind_param('s', $_SESSION['username']);
$stmt->execute();
$stmt->bind_result($user_id);
$stmt->fetch();
$stmt->close();
if (!$user_id) {
    echo json_encode([]);
    $conn->close();
    exit();
}
// Get files for this course and instructor
$stmt = $conn->prepare('SELECT file_name, file_type, uploaded_at FROM file_uploads WHERE user_id = ? AND course_id = ? ORDER BY uploaded_at DESC');
$stmt->bind_param('ii', $user_id, $course_id);
$stmt->execute();
$result = $stmt->get_result();
$files = [];
while ($row = $result->fetch_assoc()) {
    $files[] = $row;
}
$stmt->close();
$conn->close();
echo json_encode($files);
