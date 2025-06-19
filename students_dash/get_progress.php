<?php
session_start();
header('Content-Type: application/json');
if (!isset($_SESSION['username']) || $_SESSION['role'] !== 'student') {
    echo json_encode([]);
    exit();
}
$conn = new mysqli('localhost', 'root', '', 'learnify');
if ($conn->connect_error) {
    echo json_encode([]);
    exit();
}
$stmt = $conn->prepare('SELECT id FROM users WHERE username = ?');
$stmt->bind_param('s', $_SESSION['username']);
$stmt->execute();
$stmt->bind_result($student_id);
$stmt->fetch();
$stmt->close();
$sql = "SELECT course_id, progress_percentage FROM progress_tracking WHERE student_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param('i', $student_id);
$stmt->execute();
$result = $stmt->get_result();
$progress = [];
while ($row = $result->fetch_assoc()) {
    $progress[$row['course_id']] = $row['progress_percentage'];
}
$stmt->close();
$conn->close();
echo json_encode($progress);
