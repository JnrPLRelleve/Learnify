
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Landing page</title>
    <link rel="stylesheet" href="loginpage.css">
</head>
<body>
    <div class="container">
        <img src="../images/login.jpg" alt="Study image">
        
        <div class="form-wrapper">
            <form action="loginpage.php" method="post">
                <h3>LOGIN</h3>
                <p class="wel">Welcome! Please login your <br>account.</p>
                <label for="username">Username</label><br>
                <input type="text" name="username" id="username" required><br>
                <label for="password">Password</label><br>
                <input type="password" name="password" id="password" minlength="8" maxlength="16" required><br>
                <a href="#" target="_blank" id="forgot_password">Forgot Password?</a><br>
                <input type="submit" value="LOGIN"><br>
                <a href="../ADMIN_PAGES/loginpage_Admin.php" id="signup">ADMIN</a></p>
                <p class="new">New User? <a href="role_Selection.html" id="signup">Signup</a></p>
        </div>
    </div>
</body>

</html>