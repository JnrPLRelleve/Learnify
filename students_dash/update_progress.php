<?php
session_start();
header('Content-Type: application/json');
if (!isset($_SESSION['username']) || $_SESSION['role'] !== 'student') {
    echo json_encode(['success' => false, 'error' => 'Unauthorized']);
    exit();
}
if (!isset($_POST['course_id']) || !isset($_POST['done_files'])) {
    echo json_encode(['success' => false, 'error' => 'Missing data']);
    exit();
}
$course_id = intval($_POST['course_id']);
$done_files = $_POST['done_files'];
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
if (!$student_id) {
    echo json_encode(['success' => false, 'error' => 'Student not found']);
    $conn->close();
    exit();
}
// Get total files for this course (must match only files for this course, not all by instructor)
$stmt = $conn->prepare('SELECT COUNT(*) FROM file_uploads WHERE course_id = ?');
$stmt->bind_param('i', $course_id);
$stmt->execute();
$stmt->bind_result($total_files);
$stmt->fetch();
$stmt->close();
if ($total_files == 0) $total_files = 1; // avoid division by zero
// Only count as done those files that are actually in this course
$valid_files = [];
$stmt = $conn->prepare('SELECT file_name FROM file_uploads WHERE course_id = ?');
$stmt->bind_param('i', $course_id);
$stmt->execute();
$res = $stmt->get_result();
while ($row = $res->fetch_assoc()) {
    $valid_files[] = $row['file_name'];
}
$stmt->close();
$done_count = 0;
foreach ($done_files as $f) {
    if (in_array($f, $valid_files)) $done_count++;
}
$progress = round($done_count / $total_files * 100);
if ($progress > 100) $progress = 100;
// Check if a progress_tracking row exists for this student and course
$stmt = $conn->prepare('SELECT id FROM progress_tracking WHERE student_id = ? AND course_id = ?');
$stmt->bind_param('ii', $student_id, $course_id);
$stmt->execute();
$stmt->store_result();
if ($stmt->num_rows > 0) {
    // Update
    $stmt = $conn->prepare('UPDATE progress_tracking SET progress_percentage = ?, updated_at = NOW() WHERE student_id = ? AND course_id = ?');
    $stmt->bind_param('dii', $progress, $student_id, $course_id);
    $stmt->execute();
} else {
    // Insert
    $stmt = $conn->prepare('INSERT INTO progress_tracking (student_id, course_id, progress_percentage, updated_at) VALUES (?, ?, ?, NOW())');
    $stmt->bind_param('iid', $student_id, $course_id, $progress);
    $stmt->execute();
}
$stmt->close();
$conn->close();
echo json_encode(['success' => true, 'progress' => $progress]);
