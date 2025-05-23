<?php


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
            <div class="admin-search">
                <input type="text" placeholder="Search admin..." class="search-input" />
                <button class="search-button">Search</button>
            </div>
            
            <div class="admin-content">
                <?php if ($result && $result->num_rows > 0): ?>
                    <?php while($row = $result->fetch_assoc()): ?>
                        <div class="admin-item">
                            <img src="images/AdminPerson.jpg" alt="Admin" class="admin-image" />
                            <div class="admin-text">
                                <h2><?php echo htmlspecialchars($row['fullname']); ?></h2>
                                <p><span class="admin-id">ID: <?php echo htmlspecialchars($row['user_id']); ?></span> • <?php echo htmlspecialchars($row['role']); ?></p>
                            </div>
                            <div class="admin-buttons">
                                <button class="edit-button">Edit</button>
                                <button class="delete-button">Delete</button>
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
