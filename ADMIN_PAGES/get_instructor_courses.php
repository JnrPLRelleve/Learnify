<?php //GET INTRUCTOR NAME ON THE COURSELIST PAGE
header('Content-Type: application/json');
$conn = new mysqli("localhost", "root", "", "learnify");
if ($conn->connect_error) {
    echo json_encode([]);
    exit();
}
$user_id = isset($_GET['user_id']) ? $conn->real_escape_string($_GET['user_id']) : '';
$courses = [];
if ($user_id !== '') {
    $sql = "SELECT courses.* FROM courses JOIN users ON courses.instructor_id = users.id WHERE users.id = '$user_id'";
    $result = $conn->query($sql);
    if ($result && $result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $courses[] = $row;
        }
    }
}
echo json_encode($courses);
$conn->close();
