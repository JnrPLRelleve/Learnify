<?php
// filepath: c:\xampp\htdocs\learnify\students_dash\delete_instructor.php
// This script deletes an instructor and all their courses (and related enrollments)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['instructor_id'])) {
    $conn = new mysqli('localhost', 'root', '', 'learnify');
    if ($conn->connect_error) {
        echo json_encode(['success' => false, 'error' => 'DB error']);
        exit();
    }
    $instructor_id = intval($_POST['instructor_id']);
    // Get all course ids for this instructor
    $courses = [];
    $res = $conn->query("SELECT id FROM courses WHERE instructor_id = $instructor_id");
    while ($row = $res && $res->fetch_assoc()) {
        $courses[] = $row['id'];
    }
    // Delete enrollments for these courses
    if (!empty($courses)) {
        $ids = implode(',', array_map('intval', $courses));
        $conn->query("DELETE FROM enrollments WHERE course_id IN ($ids)");
        $conn->query("DELETE FROM courses WHERE id IN ($ids)");
    }
    // Delete instructor
    $conn->query("DELETE FROM users WHERE id = $instructor_id AND role = 'instructor'");
    $conn->close();
    echo json_encode(['success' => true]);
    exit();
}
echo json_encode(['success' => false, 'error' => 'Invalid request']);
