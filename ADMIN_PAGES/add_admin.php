<?php
// add_admin.php
$conn = new mysqli('localhost', 'root', '', 'learnify');
if ($conn->connect_error) {
    die('Connection failed: ' . $conn->connect_error);
}
$message = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $fullname = trim($_POST['fullname']);
    $username = trim($_POST['username']);
    $password = trim($_POST['password']);
    if ($fullname && $username && $password) {
        // Check if username already exists
        $check = $conn->prepare("SELECT 1 FROM users WHERE username = ? LIMIT 1");
        $check->bind_param('s', $username);
        $check->execute();
        $check->store_result();
        if ($check->num_rows > 0) {
            $message = 'Error: Username already exists.';
        } else {
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            $user_id = uniqid('admin_');
            $stmt = $conn->prepare("INSERT INTO users (user_id, fullname, username, password, role, created_at) VALUES (?, ?, ?, ?, 'admin', NOW())");
            $stmt->bind_param('ssss', $user_id, $fullname, $username, $hashed_password);
            if ($stmt->execute()) {
                $message = 'Admin account created successfully!';
            } else {
                $message = 'Error: ' . $stmt->error;
            }
            $stmt->close();
        }
        $check->close();
    } else {
        $message = 'Please fill in all fields.';
    }
}
$conn->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Add Admin</title>
    <link rel="stylesheet" href="add_admin.css" />
</head>
<body>
    <div class="admin-list-container">
        <h1>Add Admin</h1>
        <?php if ($message): ?>
            <div class="message <?php echo (strpos($message, 'successfully') !== false) ? 'success' : 'error'; ?>">
                <?php echo htmlspecialchars($message); ?>
            </div>
        <?php endif; ?>
        <form method="post" action="" id="addAdminForm">
            <label class="input-label" for="fullname">Full Name</label>
            <input type="text" id="fullname" name="fullname" placeholder="Full Name" required />
            <label class="input-label" for="username">Username</label>
            <input type="text" id="username" name="username" placeholder="Username" required />
            <label class="input-label" for="password">Password</label>
            <input type="password" id="password" name="password" placeholder="Password" required />
            <button type="submit">Create Admin</button>
        </form>
        <button class="back-button" onclick="window.location.href='admin_list.php'">Back to Admin List</button>
    </div>
</body>
</html>
