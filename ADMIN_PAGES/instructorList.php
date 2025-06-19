<?php
session_start();
if (!isset($_SESSION['username']) || !isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header('Location: loginpage_Admin.php');
    exit();
}
$conn = new mysqli("localhost", "root", "", "learnify");
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
// instructors from the database
$instructors = [];
$sql = "SELECT * FROM users WHERE role = 'instructor'";
$result = $conn->query($sql);
if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $instructors[] = $row;
    }
}
// Fetch instructor's profile picture
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
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Instructor List</title>
    <link rel="stylesheet" href="instructorList.css">
</head>
<body>
    <div class="container">
        <div class="sidebar">
            <div class="admin-avatar">
                <img src="<?php echo htmlspecialchars($profilePic); ?>" alt="Admin Avatar" class="avatar">
            </div>
            <div class="sidebar-content">
                <div class="heading">ADMIN:<span style="font-size:0.9em;font-weight:400;display:block;margin-top:2px;letter-spacing:0.5px;">@<?php echo htmlspecialchars($_SESSION['username'] ?? ''); ?></span></div>
                <button class="nav-btn" onclick="location.href='courseList.php'">Courses</button>
                <button class="nav-btn selected" onclick="location.href='instructorList.php'">Instructors</button>
                <button class="nav-btn" onclick="location.href='studentList.php'">Students</button>
                <button class="admin-list-btn" onclick="location.href='admin_List.php'">Admin List</button>
                <button class="logout-btn" type="button" onclick="window.location.href='admin_Logout.php'">Logout</button>

            </div>
        </div>
        <div class="main">
            <label class="search-label" for="search">Search:</label>
            <span class="search-wrap"><input class="inputSearch" type="search" id="search" placeholder="Sort By Name and UID..."></span>
            <div class="user-cards">
                <?php foreach ($instructors as $instructor): ?>
                  <div class="card">
                    <div class="profile-pic">
                      <?php
                        $pic = '../images/AdminPerson.jpg';
                        if (!empty($instructor['profile_picture']) && file_exists('../images/profile_pics/' . $instructor['profile_picture'])) {
                            $pic = '../images/profile_pics/' . $instructor['profile_picture'];
                        }
                      ?>
                      <img src="<?php echo htmlspecialchars($pic); ?>" alt="profile" style="width:60px;height:60px;border-radius:50%;object-fit:cover;">
                    </div>
                    <div class="info">
                        <span class="name"><?php echo htmlspecialchars($instructor['username']); ?></span>
                        <span class="uid">uid: <?php echo htmlspecialchars($instructor['user_id']); ?></span>
                        
                    </div>
                    <div class="actions">
                        <button class="view-btn" onclick="viewCourses(event, '<?php echo addslashes($instructor['id']); ?>', '<?php echo addslashes($instructor['username']); ?>')">view courses</button>
                        <button class="remove-btn" onclick="deleteInstructorCard(event, '<?php echo addslashes($instructor['id']); ?>')">remove</button>
                    </div>
                  </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
    <!-- Modal Structure for Courses -->
    <div id="coursesModal">
        <div class="modal-content">
            <button class="close-btn" onclick="closeCoursesModal()">&times;</button>
            <h2 id="coursesModalTitle"></h2>
            <hr>
            <table id="coursesTable">
                <thead>
                    <tr>
                        <th>Title</th>
                        <th>Description</th>
                        <th>Created At</th>
                        <th>Actions<br><button id="deleteAllBtn" onclick="deleteAllCourses()">Delete All</button></th>
                    </tr>
                </thead>
                <tbody></tbody>
            </table>
        </div>
    </div>
    <script src="instructorList.js"></script>
</body>
</html>
