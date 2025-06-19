<?php
session_start();
$conn = new mysqli("localhost", "root", "", "learnify");

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $fullname = $conn->real_escape_string($_POST['fullname']);
    $username = $conn->real_escape_string($_POST['username']);
    $password = password_hash($_POST['password'], PASSWORD_BCRYPT);
    $role = 'student';
    $created_at = date("Y-m-d H:i:s");
    $user_id = isset($_POST['user_id']) ? $conn->real_escape_string($_POST['user_id']) : '';
    if (!$user_id) {
        die("Invalid registration. No user_id.");
    }
    $sql = "INSERT INTO users (user_id, fullname, username, password, role, created_at) VALUES ('$user_id', '$fullname', '$username', '$password', '$role', '$created_at')";

    if ($conn->query($sql) === TRUE) {
        $_SESSION['username'] = $username;
        $_SESSION['role'] = $role;
        echo "<script>alert('Signup successful! Please log in.'); window.location.href='loginpage.php';</script>";
        exit();
    } else {
        echo "Error: " . $sql . "<br>" . $conn->error;
    }
}

$conn->close();
?>

<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SignUp Students</title>
    <link rel="stylesheet" href="signup_Instructor.css">
</head>
<body>
<script>
async function getNextUserId() {
    // AJAX to get next user_id from server
    const res = await fetch('get_next_student_id.php');
    const data = await res.json();
    return data.user_id;
}
document.addEventListener('DOMContentLoaded', function() {
    const form = document.querySelector('form');
    form.addEventListener('submit', async function(e) {
        e.preventDefault();
        const user_id = await getNextUserId();
        let modal = document.createElement('div');
        modal.style.position = 'fixed';
        modal.style.top = 0;
        modal.style.left = 0;
        modal.style.width = '100vw';
        modal.style.height = '100vh';
        modal.style.background = 'rgba(0,0,0,0.5)';
        modal.style.display = 'flex';
        modal.style.justifyContent = 'center';
        modal.style.alignItems = 'center';
        modal.style.zIndex = 9999;
        modal.innerHTML = `<div style='background:#fff;padding:32px 24px;border-radius:12px;max-width:90vw;text-align:center;'><h2>Signup: Confirm Your User ID</h2><p>Your User ID is:</p><input id='userIdValue' value='${user_id}' readonly style='font-size:1.3em;font-weight:bold;margin-bottom:18px;text-align:center;border:none;background:transparent;width:100%;'><label style='display:block;margin-bottom:12px;'><input type='checkbox' id='agreeCheck'> I agree</label><button id='copyUserIdBtn' style='padding:8px 18px;border-radius:8px;background:#5b6291;color:#fff;border:none;cursor:pointer;font-size:1em;'>Copy User ID & Register</button><br><button id='cancelBtn' style='margin-top:10px;padding:6px 18px;border-radius:8px;background:#bbb;color:#222;border:none;cursor:pointer;font-size:1em;'>Cancel</button></div>`;
        document.body.appendChild(modal);
        document.getElementById('copyUserIdBtn').onclick = function() {
            var agree = document.getElementById('agreeCheck').checked;
            if (!agree) { alert('You must agree before proceeding.'); return; }
            var userId = document.getElementById('userIdValue').value;
            navigator.clipboard.writeText(userId).then(function(){
                // Add hidden input and submit
                let hidden = document.createElement('input');
                hidden.type = 'hidden';
                hidden.name = 'user_id';
                hidden.value = userId;
                form.appendChild(hidden);
                modal.remove();
                form.submit();
            });
        };
        document.getElementById('cancelBtn').onclick = function() {
            modal.remove();
        };
    });
});
</script>
    <div class="container">
        <img src="../images/student.jpg" alt="Instructor image">
        
        <div class="form-wrapper">
            <form action="signup_Students.php" method="post">
                <h3>SIGNUP</h3>
                <p class="wel">Welcome! Please create an <br>account.</p>
                <label for="fullname">Fullname</label><br>
                <input type="text" name="fullname" id="fullname" required><br>
                <label for="username">Username</label><br>
                <input type="text" name="username" id="username" required><br>
                <label for="password">Password</label><br>
                <input type="password" name="password" id="password" minlength="8" maxlength="16" required><br>
                <input type="submit" value="SIGNUP"><br>
                <p class="new">Already have an account? <a href="loginpage.php" id="signup">Login</a></p>
            </form>
        </div>
    </div>
</body>
</html>
