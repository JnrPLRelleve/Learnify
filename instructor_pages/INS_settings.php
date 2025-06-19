<?php
// PHP backend for username/password change
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    session_start();
    $response = ["success"=>false, "message"=>"Unknown error."];
    $conn = new mysqli('localhost','root','','learnify');
    if ($conn->connect_error) {
        $response['message'] = 'DB error.';
        header('Content-Type: application/json');
        echo json_encode($response); exit();
    }
    $username = $_SESSION['username'];
    if ($_POST['action'] === 'change_username') {
        $new_username = trim($_POST['new_username']);
        $password = $_POST['password'];
        $res = $conn->query("SELECT password FROM users WHERE username='".$conn->real_escape_string($username)."' LIMIT 1");
        if ($res && $row = $res->fetch_assoc()) {
            if (password_verify($password, $row['password'])) {
                // Check if new username exists
                $exists = $conn->query("SELECT id FROM users WHERE username='".$conn->real_escape_string($new_username)."' LIMIT 1");
                if ($exists && $exists->num_rows > 0) {
                    $response['message'] = 'Username already taken.';
                } else {
                    $conn->query("UPDATE users SET username='".$conn->real_escape_string($new_username)."' WHERE username='".$conn->real_escape_string($username)."'");
                    $_SESSION['username'] = $new_username;
                    $response = ["success"=>true, "message"=>"Username updated!"];
                }
            } else {
                $response['message'] = 'Incorrect password.';
            }
        }
        header('Content-Type: application/json');
        echo json_encode($response); exit();
    } elseif ($_POST['action'] === 'change_password') {
        $current_password = $_POST['current_password'];
        $new_password = $_POST['new_password'];
        $confirm_password = $_POST['confirm_password'];
        $res = $conn->query("SELECT password FROM users WHERE username='".$conn->real_escape_string($username)."' LIMIT 1");
        if ($res && $row = $res->fetch_assoc()) {
            if (!password_verify($current_password, $row['password'])) {
                $response['message'] = 'Incorrect current password.';
            } elseif ($new_password !== $confirm_password) {
                $response['message'] = 'Passwords do not match.';
            } elseif (strlen($new_password) < 6) {
                $response['message'] = 'Password must be at least 6 characters.';
            } else {
                $hashed = password_hash($new_password, PASSWORD_DEFAULT);
                $conn->query("UPDATE users SET password='".$conn->real_escape_string($hashed)."' WHERE username='".$conn->real_escape_string($username)."'");
                $response = ["success"=>true, "message"=>"Password updated!"];
            }
        }
        header('Content-Type: application/json');
        echo json_encode($response); exit();
    }
}

// --- AJAX profile picture upload handler: must be at the very top, before any output ---
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['profile_pic'])) {
    error_reporting(0); // Hide warnings/notices from AJAX response
    ini_set('display_errors', 0);
    $response = ["success"=>false, "message"=>"Upload failed."];
    session_start();
    if (!isset($_SESSION['username'])) {
        $response['message'] = 'Not logged in.';
        header('Content-Type: application/json');
        echo json_encode($response);
        exit();
    }
    $user = $_SESSION['username'];
    $file = $_FILES['profile_pic'];
    if ($file['error'] === 0) {
        $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        $allowed = ['jpg','jpeg','png','gif'];
        if (in_array($ext, $allowed)) {
            $newName = 'profile_'.md5($user.time()).'.'.$ext;
            $dest = '../images/profile_pics/'.$newName;
            if (!is_dir('../images/profile_pics')) mkdir('../images/profile_pics', 0777, true);
            if (move_uploaded_file($file['tmp_name'], $dest)) {
                $conn = new mysqli('localhost','root','','learnify');
                $conn->query("UPDATE users SET profile_picture='$newName' WHERE username='$user'");
                $response = ["success"=>true, "message"=>"Profile picture updated!"];
            } else {
                $response['message'] = 'Failed to move file.';
            }
        } else {
            $response['message'] = 'Invalid file type.';
        }
    } else {
        $response['message'] = 'Upload error.';
    }
    header('Content-Type: application/json');
    echo json_encode($response);
    exit();
}

session_start();
if (!isset($_SESSION['username']) || !isset($_SESSION['role']) || $_SESSION['role'] !== 'instructor') {
    header('Location: ../loginpage.php');
    exit();
}

// Fetch the user's profile picture from the database
$profilePic = '../images/AdminPerson.jpg'; // default
if (isset($_SESSION['username'])) {
    $conn = new mysqli('localhost', 'root', '', 'learnify');
    if (!$conn->connect_error) {
        $username = $_SESSION['username'];
        $res = $conn->query("SELECT profile_picture FROM users WHERE username='".$conn->real_escape_string($username)."' LIMIT 1");
        if ($res && $row = $res->fetch_assoc()) {
            if (!empty($row['profile_picture']) && file_exists('../images/profile_pics/' . $row['profile_picture'])) {
                $profilePic = '../images/profile_pics/' . $row['profile_picture'];
            }
        }
        $conn->close();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Instructor Settings</title>
    <link rel="stylesheet" href="INS_settings.css">
</head>
<body>
    <div class="settings-container">
        <div class="settings-sidebar">
            <div class="profile_pic"><img src="<?php echo htmlspecialchars($profilePic); ?>" alt="instructor_picture"></div>
            <h2><span style="font-size:1em;font-weight:400; color: white; margin-top: 10px; margin-left: 55px;"><?php echo htmlspecialchars($_SESSION['username']); ?></span></h2>
            <button id="btnUsername" class="active">Change Credentials</button>
         
            <button id="btnProfilePic">Change Profile Picture</button>
            <button class="back-btn" onclick="window.location.href='instructor_dashboard.php'">Back</button>
        </div>
        <div class="settings-content" id="settingsContent">
            CONTENT
        </div>
    </div>
    <script src="INS_settings.js"></script>
</body>
</html>