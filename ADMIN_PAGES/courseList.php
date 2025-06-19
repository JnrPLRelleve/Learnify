<?php
//server connection and session management
session_start();
if (!isset($_SESSION['username']) || !isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header('Location: loginpage_Admin.php');
    exit();
}
if (isset($_POST['logout'])) {
    session_unset();
    session_destroy();
    header('Location: loginpage_Admin.php');
    exit();
}

$conn = new mysqli("localhost", "root", "", "learnify");
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
// Fetch ADMIN profile picture
$profilePic = '../images/AdminPerson.jpg'; // default
if (isset($_SESSION['username'])) {
    $user_stmt = $conn->prepare('SELECT profile_picture FROM users WHERE username = ?');
    $user_stmt->bind_param('s', $_SESSION['username']);
    $user_stmt->execute();
    $user_stmt->bind_result($profile_picture);
    $user_stmt->fetch();
    $user_stmt->close();
    if (!empty($profile_picture) && file_exists('../images/profile_pics/' . $profile_picture)) {
        $profilePic = '../images/profile_pics/' . $profile_picture;
    }
}


$courses = [];
$sql = "SELECT c.*, u.fullname AS instructor_fullname FROM courses c LEFT JOIN users u ON c.instructor_id = u.id";
$result = $conn->query($sql);
if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $courses[] = $row;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Course List</title>
    <link rel="stylesheet" href="courseList.css">
</head>
<body>
    <div class="container">
        <div class="sidebar">
            <div class="admin-avatar">
                <img src="<?php echo htmlspecialchars($profilePic); ?>" alt="Admin Avatar" class="avatar">
            </div>
            <div class="sidebar-content">
                <div class="heading">ADMIN:<span style="font-size:0.9em;font-weight:400;display:block;margin-top:2px;letter-spacing:0.5px;">@<?php echo htmlspecialchars($_SESSION['username'] ?? ''); ?></span></div>
                <button class="nav-btn selected" onclick="location.href='courseList.php'">Courses</button>
                <button class="nav-btn" onclick="location.href='instructorList.php'">Instructors</button>
                <button class="nav-btn" onclick="location.href='studentList.php'">Students</button>
                <button class="admin-list-btn" onclick="location.href='admin_List.php'">Admin List</button>
                <button class="logout-btn" type="button" onclick="window.location.href='admin_Logout.php'">Logout</button>
            </div>
        </div>
        <div class="main">
            <label class="search-label" for="search">Search:</label>
            <span class="search-wrap"><input class="inputSearch" type="search" id="search" placeholder="Search by instructor, course name or course id..."></span>
            <div class="user-cards">
                <?php foreach ($courses as $course): ?>
                <div class="card" data-title="<?php echo htmlspecialchars($course['title']); ?>" data-description="<?php echo htmlspecialchars($course['description']); ?>" data-instructor="<?php echo htmlspecialchars($course['instructor_fullname'] ?? 'N/A'); ?>" data-created="<?php echo htmlspecialchars($course['created_at']); ?>">
                    <div class="info">
                        <span class="name">Course: <?php echo htmlspecialchars($course['title']); ?></span>
                        <span class="instructor-fullname">Instructor: <?php echo htmlspecialchars($course['instructor_fullname'] ?? 'N/A'); ?></span>
                        <span class="uid">Course ID: CRS-<?php echo str_pad($course['id'], 3, '0', STR_PAD_LEFT); ?></span>
                    </div>
                    <div class="actions">
                        <button class="view-btn" onclick="viewCourse(event, this)">view details</button>
                        <button class="remove-btn" onclick="deleteCourseCard(event, <?php echo (int)$course['id']; ?>)">remove</button>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
    <!-- Modal Structure -->
    <div id="courseModal" style="display:none; position:fixed; top:0; left:0; width:100vw; height:100vh; background:rgba(0,0,0,0.4); z-index:1000; align-items:center; justify-content:center;">
        <div style="background:#fff; padding:32px 28px; border-radius:10px; min-width:340px; max-width:90vw; box-shadow:0 2px 16px rgba(0,0,0,0.18); position:relative;">
            <button onclick="closeModal()" style="position:absolute; top:10px; right:14px; background:none; border:none; font-size:1.5rem; cursor:pointer;">&times;</button>
            <h2 id="modalTitle">Title: </h2>
            <p id="modalDescription"></p>
            <p><strong>Instructor:</strong> <span id="modalInstructor"></span></p>
            <p><strong>Created At:</strong> <span id="modalCreated"></span></p>
        </div>
    </div>
    <script src="courselist.js"></script>
  
</body>
</html>