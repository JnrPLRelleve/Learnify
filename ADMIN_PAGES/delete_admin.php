<?php

// This script handles the deletion of an admin user from the database.
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['user_id'])) {
    $conn = new mysqli('localhost', 'root', '', 'learnify');
    if ($conn->connect_error) {
        http_response_code(500);
        echo 'fail';
        exit();
    }
    $user_id = $conn->real_escape_string($_POST['user_id']);
    $sql = "DELETE FROM users WHERE user_id = '$user_id' AND role = 'admin'";
    $result = $conn->query($sql);
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
