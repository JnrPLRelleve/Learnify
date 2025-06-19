<?php //remove student specific sa studentList
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['student_id'])) {
    $conn = new mysqli("localhost", "root", "", "learnify");
    if ($conn->connect_error) {
        http_response_code(500);
        echo 'fail';
        exit();
    }
    $student_id = $conn->real_escape_string($_POST['student_id']);
    // Disable foreign key checks, delete enrollments, progress_tracking, quiz_results, then delete student, then re-enable
    $conn->query('SET FOREIGN_KEY_CHECKS=0');
    $conn->query("DELETE FROM enrollments WHERE student_id = '$student_id'");
    $conn->query("DELETE FROM progress_tracking WHERE student_id = '$student_id'");
    $conn->query("DELETE FROM quiz_results WHERE student_id = '$student_id'");
    $sql = "DELETE FROM users WHERE id = '$student_id' AND role = 'student'";
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
