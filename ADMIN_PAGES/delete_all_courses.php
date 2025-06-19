<?php //ACTION, DELETE ALL ON THE INSTRUCTOR LIST PAGE
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['instructor_id'])) {
    $conn = new mysqli("localhost", "root", "", "learnify");
    if ($conn->connect_error) {
        http_response_code(500);
        echo 'fail';
        exit();
    }
    $instructor_id = $conn->real_escape_string($_POST['instructor_id']);
    $sql = "DELETE FROM courses WHERE instructor_id = '$instructor_id'";
    if ($conn->query($sql) === TRUE) {
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
