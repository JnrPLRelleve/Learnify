<?php
session_start();
if (!isset($_SESSION['username']) || !isset($_SESSION['role']) || $_SESSION['role'] !== 'instructor') {
    header('Location: ../login_pages/loginpage.php');
    exit();
}

$servername = "localhost";
$username = "root";
$password = "";
$dbname = "learnify";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

if (
    $_SERVER['REQUEST_METHOD'] === 'POST' &&
    isset($_POST['courseName'], $_POST['courseSection'], $_POST['courseDescription'])
) {
    $title = $conn->real_escape_string(trim($_POST['courseName']));
    $section = $conn->real_escape_string(trim($_POST['courseSection']));
    $description = $conn->real_escape_string(trim($_POST['courseDescription']));
    $created_at = date('Y-m-d H:i:s');
    $instructor_username = $_SESSION['username'];
 
    $user_query = "SELECT id FROM users WHERE username = '" . $conn->real_escape_string($instructor_username) . "' LIMIT 1";
    $user_result = $conn->query($user_query);
    if ($user_result && $user_result->num_rows > 0) {
        $row = $user_result->fetch_assoc();
        $instructor_id = $row['id'];
        $stmt = $conn->prepare("INSERT INTO courses (title, description, instructor_id, section, created_at) VALUES (?, ?, ?, ?, ?)");
        $stmt->bind_param('ssiss', $title, $description, $instructor_id, $section, $created_at);
        if ($stmt->execute()) {
            $_SESSION['alert'] = '<div style="color:green;text-align:center;">Course successfully added!</div>';
            header('Location: create_course.php');
            exit();
        } else {
            $_SESSION['alert'] = '<div style="color:red;text-align:center;">Error creating course. Please try again.</div>';
        }
        $stmt->close();
    } else {
        $_SESSION['alert'] = '<div style="color:red;text-align:center;">Instructor not found.</div>';
    }
}

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
    <title>Create Course</title>
    <link rel="stylesheet" href="instructor_dashboard.css">
    <link rel="stylesheet" href="create_course.css">
</head>
<body>
    
    <div class="container">
        <aside class="sidebar">
            <div class="profile_pic"><img src="<?php echo htmlspecialchars($profilePic); ?>" alt="sample"></div>
            <h2><span style="font-size:1em;font-weight:400;"><?php echo htmlspecialchars($_SESSION['username']); ?></span></h2>
            <button class="sidebar_btn" onclick="window.location.href='instructor_dashboard.php'">Courses</button>
            <button class="sidebar_btn" onclick="location.href='instructor_Quiz_Inter.php'">Quiz Interface</button>
            <button class="sidebar_btn" onclick="location.href='create_course.php'">Create Course</button>
            <button class="sidebar_btn" onclick="location.href='manage_Materials.php'">Materials</button>

            <button class="settings_btn" onclick="location.href='INS_settings.php'">Settings</button>
            <button class="logout_btn1" type="button" onclick="window.location.href='../login_pages/logout.php'">Logout</button>
        </aside>
        <main class="main_content create-course-center">
            
            <form id="createCourseForm" method="post" action="" class="create-course-form">
                <h1>Create Course</h1>
                <?php if (isset($_SESSION['alert'])) { echo $_SESSION['alert']; unset($_SESSION['alert']); } ?>
                <label for="courseName">Title:</label>
                <input type="text" id="courseName" name="courseName" required placeholder="Enter title">

                <label for="courseSection">Section:</label>
                <input type="text" id="courseSection" name="courseSection" required placeholder="Enter section">

                <label for="courseDescription">Description:</label>
                <textarea id="courseDescription" name="courseDescription" required placeholder="Enter course description"></textarea>

                <div class="modal_actions">
                    <button type="submit" class="upload_btn">Create</button>
                    <button type="button" class="delete_btn" onclick="window.location.href='instructor_dashboard.php'">Cancel</button>
                </div>
            </form>
        </main>
    </div>
</body>
</html>
