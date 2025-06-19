<?php // TO GET ALL THE STUDENTS ENROLLED COURSES
header('Content-Type: application/json');
$conn = new mysqli("localhost", "root", "", "learnify");
if ($conn->connect_error) {
    echo json_encode([]);
    exit();
}
$student_id = isset($_GET['student_id']) ? $conn->real_escape_string($_GET['student_id']) : '';
$courses = [];
if ($student_id !== '') {
    $sql = "SELECT c.id AS course_id, c.title, c.description, e.enrolled_at, pt.progress_percentage
            FROM enrollments e
            JOIN courses c ON e.course_id = c.id
            LEFT JOIN (
                SELECT pt1.student_id, pt1.course_id, pt1.progress_percentage, pt1.updated_at
                FROM progress_tracking pt1
                INNER JOIN (
                    SELECT student_id, course_id, MAX(updated_at) AS max_updated
                    FROM progress_tracking
                    GROUP BY student_id, course_id
                ) pt2 ON pt1.student_id = pt2.student_id AND pt1.course_id = pt2.course_id AND pt1.updated_at = pt2.max_updated
            ) pt ON pt.student_id = e.student_id AND pt.course_id = e.course_id
            WHERE e.student_id = '$student_id'";
    $result = $conn->query($sql);
    if ($result && $result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $courses[] = $row;
        }
    }
}
echo json_encode($courses);
$conn->close();
