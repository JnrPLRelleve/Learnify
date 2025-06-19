<?php //DELETE COURSE ON THE COURSELIST PAGE
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['course_id'])) {
    $conn = new mysqli("localhost", "root", "", "learnify");
    if ($conn->connect_error) {
        http_response_code(500);
        echo 'fail';
        exit();
    }
    $course_id = $conn->real_escape_string($_POST['course_id']);
    // Disable foreign key checks, delete enrollments, then delete course, then re-enable
    $conn->query('SET FOREIGN_KEY_CHECKS=0');
    $conn->query("DELETE FROM enrollments WHERE course_id = '$course_id'");
    $conn->query("DELETE FROM quiz_results WHERE course_id = '$course_id'");
    $conn->query("DELETE FROM progress_tracking WHERE course_id = '$course_id'");
    $sql = "DELETE FROM courses WHERE id = '$course_id'";
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
