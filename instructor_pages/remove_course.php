<?php
session_start();
if (!isset($_SESSION['username']) || !isset($_SESSION['role']) || $_SESSION['role'] !== 'instructor') {
    echo json_encode(['success' => false, 'error' => 'Unauthorized']);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['id'])) {
    $courseId = intval($_POST['id']);
    $conn = new mysqli('localhost', 'root', '', 'learnify');
    if ($conn->connect_error) {
        echo json_encode(['success' => false, 'error' => 'DB connection failed']);
        exit();
    }
    // Delete enrollments for this course
    $stmt = $conn->prepare('DELETE FROM enrollments WHERE course_id = ?');
    $stmt->bind_param('i', $courseId);
    $stmt->execute();
    $stmt->close();
    // Optionally delete file_uploads for this course
    // $stmt = $conn->prepare('DELETE FROM file_uploads WHERE course_id = ?');
    // $stmt->bind_param('i', $courseId);
    // $stmt->execute();
    // $stmt->close();
    // Delete the course itself
    $stmt = $conn->prepare('DELETE FROM courses WHERE id = ?');
    $stmt->bind_param('i', $courseId);
    $success = $stmt->execute();
    $stmt->close();
    $conn->close();
    echo json_encode(['success' => $success]);
    exit();
} else {
    echo json_encode(['success' => false, 'error' => 'Invalid request']);
    exit();
}
