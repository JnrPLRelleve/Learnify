<?php
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['instructor_id'])) {
    $conn = new mysqli("localhost", "root", "", "learnify");
    if ($conn->connect_error) {
        http_response_code(500);
        echo 'fail';
        exit();
    }
    $instructor_id = $conn->real_escape_string($_POST['instructor_id']);
    // Disable foreign key checks, delete courses and enrollments, then delete instructor, then re-enable
    $conn->query('SET FOREIGN_KEY_CHECKS=0');
    // Get all course ids for this instructor
    $result = $conn->query("SELECT id FROM courses WHERE instructor_id = '$instructor_id'");
    $course_ids = [];
    while ($result && $row = $result->fetch_assoc()) {
        $course_ids[] = $row['id'];
    }
    if (!empty($course_ids)) {
        $ids = implode(",", array_map('intval', $course_ids));
        $conn->query("DELETE FROM enrollments WHERE course_id IN ($ids)");
        $conn->query("DELETE FROM courses WHERE id IN ($ids)");
    }
    // Now delete the instructor
    $sql = "DELETE FROM users WHERE id = '$instructor_id' AND role = 'instructor'";
    $result2 = $conn->query($sql);
    $conn->query('SET FOREIGN_KEY_CHECKS=1');
    if ($result2 === TRUE) {
        echo 'success';
    } else {
        http_response_code(500);
        echo 'fail';
    }
    $conn->close();
} else {
    http_response_code(400);
    echo 'fail';
}
