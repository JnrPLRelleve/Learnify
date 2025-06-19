<?php
// filepath: c:\xampp\htdocs\learnify\login_pages\get_next_student_id.php
header('Content-Type: application/json');
$conn = new mysqli('localhost', 'root', '', 'learnify');
if ($conn->connect_error) {
    echo json_encode(['user_id' => 'STU-BN-01']);
    exit();
}
$sql_last_id = "SELECT user_id FROM users WHERE user_id LIKE 'STU-BN-%' ORDER BY created_at DESC LIMIT 1";
$result = $conn->query($sql_last_id);
if ($result && $result->num_rows > 0) {
    $row = $result->fetch_assoc();
    $last_id = $row['user_id'];
    $last_number = (int)substr($last_id, strrpos($last_id, '-') + 1);
    $new_number = str_pad($last_number + 1, 2, '0', STR_PAD_LEFT);
    $user_id = 'STU-BN-' . $new_number;
} else {
    $user_id = 'STU-BN-01';
}
echo json_encode(['user_id' => $user_id]);
$conn->close();
