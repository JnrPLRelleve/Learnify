<?php
session_start();
header('Content-Type: application/json');
if (!isset($_SESSION['username']) || $_SESSION['role'] !== 'student') {
    echo json_encode([]);
    exit();
}
$conn = new mysqli('localhost', 'root', '', 'learnify');
if ($conn->connect_error) {
    echo json_encode([]);
    exit();
}
$username = $_SESSION['username'];
// Get student id
$stmt = $conn->prepare("SELECT id FROM users WHERE username = ?");
$stmt->bind_param("s", $username);
$stmt->execute();
$stmt->bind_result($student_id);
$stmt->fetch();
$stmt->close();
if (!$student_id) {
    echo json_encode([]);
    exit();
}
// Get enrolled courses with description
$sql = "SELECT c.id, c.title, c.description, u.username AS instructor_name
        FROM enrollments e
        JOIN courses c ON e.course_id = c.id
        LEFT JOIN users u ON c.instructor_id = u.id
        WHERE e.student_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $student_id);
$stmt->execute();
$result = $stmt->get_result();
$courses = [];
while ($row = $result->fetch_assoc()) {
    $courses[] = [
        'id' => $row['id'],
        'title' => $row['title'],
        'description' => $row['description'],
        'instructor_name' => $row['instructor_name'] ?? 'Unknown'
    ];
}
echo json_encode($courses);
$stmt->close();
$conn->close();
?>
