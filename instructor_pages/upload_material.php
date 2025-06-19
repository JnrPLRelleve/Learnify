<?php
session_start();
header('Content-Type: application/json');
if (!isset($_SESSION['username']) || $_SESSION['role'] !== 'instructor') {
    echo json_encode(['success' => false, 'error' => 'Unauthorized']);
    exit();
}
if (!isset($_POST['course_id'])) {
    echo json_encode(['success' => false, 'error' => 'Missing data']);
    exit();
}
$course_id = intval($_POST['course_id']);
$upload_dir = '../uploads/';
if (!is_dir($upload_dir)) {
    mkdir($upload_dir, 0777, true);
}
if (!isset($_FILES['file']) || $_FILES['file']['error'] !== UPLOAD_ERR_OK) {
    echo json_encode(['success' => false, 'error' => 'File upload error']);
    exit();
}
$file = $_FILES['file'];
$ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
$filename = uniqid('mat_') . '.' . $ext;
$filepath = $upload_dir . $filename;
if (!move_uploaded_file($file['tmp_name'], $filepath)) {
    echo json_encode(['success' => false, 'error' => 'Failed to move file']);
    exit();
}
// Determine file_type by extension
switch ($ext) {
    case 'pdf':
        $file_type = 'pdf';
        break;
    case 'mp4': case 'avi': case 'mov': case 'wmv':
        $file_type = 'video';
        break;
    case 'jpg': case 'jpeg': case 'png': case 'gif': case 'bmp':
        $file_type = 'image';
        break;
    case 'doc': case 'docx': case 'ppt': case 'pptx': case 'xls': case 'xlsx': case 'txt': case 'csv':
        $file_type = 'document';
        break;
    default:
        $file_type = 'other';
}
// Get instructor user_id
$conn = new mysqli('localhost', 'root', '', 'learnify');
if ($conn->connect_error) {
    echo json_encode(['success' => false, 'error' => 'DB connection failed']);
    exit();
}
$stmt = $conn->prepare('SELECT id FROM users WHERE username = ?');
$stmt->bind_param('s', $_SESSION['username']);
$stmt->execute();
$stmt->bind_result($user_id);
$stmt->fetch();
$stmt->close();
if (!$user_id) {
    echo json_encode(['success' => false, 'error' => 'User not found']);
    $conn->close();
    exit();
}
// Insert into file_uploads (id, user_id, course_id, file_name, file_type, uploaded_at)
$stmt = $conn->prepare('INSERT INTO file_uploads (user_id, course_id, file_name, file_type, uploaded_at) VALUES (?, ?, ?, ?, NOW())');
$stmt->bind_param('iiss', $user_id, $course_id, $filename, $file_type);
$success = $stmt->execute();
$stmt->close();
$conn->close();
echo json_encode(['success' => $success]);
