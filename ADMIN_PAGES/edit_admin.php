<?php
// Handles both profile picture upload and password change for admin
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    header('Content-Type: application/json');
    $response = ["success"=>false, "message"=>"Unknown error."];
    $conn = new mysqli('localhost', 'root', '', 'learnify');
    if ($conn->connect_error) {
        $response['message'] = 'DB connection failed.';
        echo json_encode($response); exit();
    }
    if (isset($_POST['user_id']) && isset($_FILES['profile_pic'])) {
        // Handle profile picture upload
        $user_id = $conn->real_escape_string($_POST['user_id']);
        $file = $_FILES['profile_pic'];
        if ($file['error'] === 0) {
            $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
            $allowed = ['jpg','jpeg','png','gif'];
            if (in_array($ext, $allowed)) {
                $newName = 'profile_'.md5($user_id.time()).'.'.$ext;
                $dest = '../images/profile_pics/'.$newName;
                if (!is_dir('../images/profile_pics')) mkdir('../images/profile_pics', 0777, true);
                if (move_uploaded_file($file['tmp_name'], $dest)) {
                    $conn->query("UPDATE users SET profile_picture='$newName' WHERE user_id='$user_id'");
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
        echo json_encode($response); exit();
    }
    if (isset($_POST['user_id']) && isset($_POST['new_password'])) {
        // Handle password change
        $user_id = $conn->real_escape_string($_POST['user_id']);
        $new_password = $_POST['new_password'];
        $hashed = password_hash($new_password, PASSWORD_DEFAULT);
        $stmt = $conn->prepare("UPDATE users SET password=? WHERE user_id=?");
        $stmt->bind_param('ss', $hashed, $user_id);
        if ($stmt->execute()) {
            $response = ["success"=>true, "message"=>"Password updated!"];
        } else {
            $response['message'] = 'Failed to update password.';
        }
        $stmt->close();
        echo json_encode($response); exit();
    }
    $response['message'] = 'Invalid request.';
    echo json_encode($response); exit();
}
