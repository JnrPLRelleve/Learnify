<?php
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['course_id']) && isset($_POST['student_id'])) {
    $conn = new mysqli("localhost", "root", "", "learnify");
    if ($conn->connect_error) {
        http_response_code(500);
        echo 'fail';
        exit();
    }
    $course_id = $conn->real_escape_string($_POST['course_id']);
    $student_id = $conn->real_escape_string($_POST['student_id']);
    // Disable foreign key checks, delete, then re-enable
    $conn->query('SET FOREIGN_KEY_CHECKS=0');
    $sql = "DELETE FROM enrollments WHERE course_id = '$course_id' AND student_id = '$student_id'";
    $result = $conn->query($sql);
    $conn->query('SET FOREIGN_KEY_CHECKS=1');
    if ($result === TRUE) {
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
