<?php

    // Start session and check if user is logged in as admin
    $conn = new mysqli('localhost', 'root', '', 'learnify');
    if ($conn->connect_error) {
        die('Connection failed: ' . $conn->connect_error);
    }
    $sql = "SELECT user_id, fullname, role FROM users WHERE role = 'admin'";
    $result = $conn->query($sql);
    
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Admin List</title>
    <link rel="stylesheet" href="admin_list.css" />
</head>
<body>
    
        <div class="admin-list-container">
            <h1>Admin List</h1>
            <div class="admin-actions" style="margin-bottom: 15px;">
                <button class="add-admin-button" onclick="window.location.href='add_admin.php'">Add Admin</button>
            </div>
            <div class="admin-search">
                <input type="text" placeholder="Search admin..." class="search-input" id="adminSearchInput" />
                <button class="search-button" id="adminSearchBtn">Search</button>
            </div>
            <div class="admin-content" id="adminContent">
                <?php if ($result && $result->num_rows > 0): ?>
                    <?php while($row = $result->fetch_assoc()): ?>
                        <div class="admin-item" data-fullname="<?php echo htmlspecialchars(strtolower($row['fullname'])); ?>" data-userid="<?php echo htmlspecialchars(strtolower($row['user_id'])); ?>">
                            <?php
                                $pic = '../images/AdminPerson.jpg';
                                // Fetch profile_picture for this admin
                                $user_id = $row['user_id'];
                                $picRes = $conn->query("SELECT profile_picture FROM users WHERE user_id='".$conn->real_escape_string($user_id)."' AND role='admin' LIMIT 1");
                                if ($picRes && $picRow = $picRes->fetch_assoc()) {
                                    if (!empty($picRow['profile_picture']) && file_exists('../images/profile_pics/' . $picRow['profile_picture'])) {
                                        $pic = '../images/profile_pics/' . $picRow['profile_picture'];
                                    }
                                }
                            ?>
                            <img src="<?php echo htmlspecialchars($pic); ?>" alt="Admin" class="admin-image" />
                            <div class="admin-text">
                                <h2><?php echo htmlspecialchars($row['fullname']); ?></h2>
                                <p><span class="admin-id">ID: <?php echo htmlspecialchars($row['user_id']); ?></span> • <?php echo htmlspecialchars($row['role']); ?></p>
                            </div>
                            <div class="admin-buttons">
                                <button class="edit-button" onclick="openEditAdminModal('<?php echo addslashes($row['user_id']); ?>', '<?php echo addslashes($row['fullname']); ?>', '<?php echo addslashes($pic); ?>')">Edit</button>
                                <button class="delete-button" onclick="deleteAdmin('<?php echo addslashes($row['user_id']); ?>', this)">Delete</button>
                            </div>
                        </div>
                    <?php endwhile; ?>
                <?php else: ?>
                    <p>No admins found.</p>
                <?php endif; ?>
            </div>
            <?php $conn->close(); ?>
            <?php
                $referer = isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : '';
                $backUrl = 'admin.php';
                if (strpos($referer, 'studentList.php') !== false) {
                    $backUrl = 'studentList.php';
                } elseif (strpos($referer, 'instructorList.php') !== false) {
                    $backUrl = 'instructorList.php';
                }
            ?>
            <button class="back-button" onclick="window.location.href='<?php echo $backUrl; ?>'">Back to Dashboard</button>
        </div>

</body>
</html>
<script src="admin_list.js"></script>
