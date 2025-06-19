<?php
// update_instructor_name.php
// Usage: POST with 'instructor_id' and 'fullname'
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['instructor_id'], $_POST['fullname'])) {
    $conn = new mysqli('localhost', 'root', '', 'learnify');
    if ($conn->connect_error) {
        echo json_encode(['success' => false, 'error' => 'DB connection error']);
        exit;
    }
    $id = intval($_POST['instructor_id']);
    $fullname = $conn->real_escape_string($_POST['fullname']);
    $res = $conn->query("UPDATE users SET fullname = '$fullname' WHERE id = $id");
    if ($res) {
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'error' => 'Update failed']);
    }
    $conn->close();
} else {
    echo json_encode(['success' => false, 'error' => 'Missing data']);
}
?>
