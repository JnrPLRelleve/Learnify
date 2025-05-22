<?php


// Regen ID for security
if (!isset($_SESSION['last_regeneration'])) {
    $_SESSION['last_regeneration'] = time();
} elseif (time() - $_SESSION['last_regeneration'] > 24000) {  //40min regen
    
    session_regenerate_id(true);
    $_SESSION['last_regeneration'] = time();
}

$conn = new mysqli("localhost", "root", "", "learnify");

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['username'], $_POST['action'])) {
    session_start(); 
    if (!isset($_SESSION['username']) || $_SESSION['role'] !== 'admin') {
        echo "error: Unauthorized access.";
        exit();
    }

    $username = $conn->real_escape_string($_POST['username']);
    $action = $_POST['action'];

    if ($action === 'accept') {
        $sql = "UPDATE users SET status = 'approved' WHERE username = '$username'";
    } elseif ($action === 'decline') {
        $sql = "DELETE FROM users WHERE username = '$username'";
    }

    if (isset($sql)) {
        if ($conn->query($sql) === TRUE) {
            echo "success";
        } else {
            error_log("SQL Error: " . $conn->error);
            error_log("SQL Query: " . $sql);
            echo "error: " . $conn->error;
        }
    } else {
        echo "error: Invalid action.";
    }
    exit();
}
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>WELCOME ADMIN </title>
    <link rel="stylesheet" href="admin.css">
</head>
<body>
    <div class="box1">
        <div class="box2">
          <h1 class="heading">ADMIN</h1>
                <button class="btn1" onclick="location.href='courseList.php'"> Courses </button>
                <button class="btn2" onclick="location.href='instructorList.php'"> Instructors </button>
                <button class="btn3" onclick="location.href='studentList.php'"> Students</button>
                <button class="btn4" onclick="location.href='admin_List.php'"> Admin List</button>
                <button class="btn5" type="button" onclick="window.location.href='admin_Logout.php'">Logout</button>
                <img class="image1" src="/images/AdminPerson.jpg" alt="image"></img>
      
        </div> 
        <div class="box3">
          <div class="box4">
            <div class="box5"></div>
            <h1 class="header2">Welcome, [Admin]!</h1>
            <h1 class="header2_1">Welcome, [Admin]!</h1>
            <p class="p1">-We are glad to have you back. Manage your tools and resources efficiently,
                        and stay on top of your dashboard to streamline your workflow. </p> 

                        <p class="p2">-We are glad to have you back.
                           Manage your tools and resources 
                           efficiently, and stay on top of your 
                            dashboard to streamline your workflow. </p>
                            
                            <p class="p3">-We are glad 
                              to have you back.
                              Manage your tools 
                              and resources 
                              efficiently,
                               and stay on 
                               top of your 
                               dashboard to 
                               streamline your workflow. 
                            </p>            
          </div> 

        </div>
     </div>         
</body>
</html>