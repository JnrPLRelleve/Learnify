<?php
// Database connection
$conn = new mysqli("localhost", "root", "", "learnify");
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
// Fetch instructors from the database
$instructors = [];
$sql = "SELECT * FROM users WHERE role = 'instructor'";
$result = $conn->query($sql);
if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $instructors[] = $row;
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
            <div class="admin-avatar"></div>
            <div class="sidebar-content">
                <div class="heading">ADMIN</div>
                <button class="nav-btn" onclick="location.href='courseList.php'">Courses</button>
                <button class="nav-btn selected" onclick="location.href='instructorList.php'">Instructors</button>
                <button class="nav-btn" onclick="location.href='studentList.php'">Students</button>
                <button class="admin-list-btn" onclick="location.href='admin_List.php'">Admin List</button>
                <button class="logout-btn" type="button" onclick="window.location.href='../admin_Logout.php'">Logout</button>

            </div>
        </div>
        <div class="main">
            <label class="search-label" for="search">Search:</label>
            <span class="search-wrap"><input class="inputSearch" type="search" id="search" placeholder="Sort By..."></span>
            <div class="user-cards">
                <?php foreach ($instructors as $instructor): ?>
                  <div class="card">
                    <div class="profile-pic"></div>
                    <div class="info">
                        <span class="name"><?php echo htmlspecialchars($instructor['username']); ?></span>
                        <span class="uid">uid: <?php echo htmlspecialchars($instructor['user_id']); ?></span>
                        <span class="contact">contact</span>
                    </div>
                    <div class="actions">
                        <button class="view-btn" onclick="viewCourses(event, '<?php echo addslashes($instructor['username']); ?>')">view courses</button>
                        <button class="remove-btn" onclick="deleteInstructorCard(event, '<?php echo addslashes($instructor['username']); ?>')">remove</button>
                    </div>
                  </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
    <script>
        function viewCourses(event, name) {
            event.stopPropagation();
            alert('View courses for ' + name);
        }
        function deleteInstructorCard(event, name) {
            event.stopPropagation();
            if (confirm('Are you sure you want to remove ' + name + '?')) {
                event.target.closest('.card').remove();
            }
        }
    </script>
</body>
</html>
