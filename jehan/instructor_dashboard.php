<?php
session_start();
if (!isset($_SESSION['username']) || !isset($_SESSION['role']) || $_SESSION['role'] !== 'instructor') {
    header('Location: ../loginpage.php');
    exit();
}

$conn = new mysqli('localhost', 'root', '', 'learnify');
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Instructor Dashboard</title>
    <link rel="stylesheet" href="instructor_dashboard.css">
    
</head>
<body>
    <div class="container">
        <aside class="sidebar">
            <div class="profile_pic"><img src="../images/AdminPerson.jpg" alt="sample"></div>
            <h2>INSTRUCTOR</h2>
            <button class="sidebar_btn">Courses</button>
            <button class="sidebar_btn" onclick="location.href='quiz.php'">Quiz Interface</button>
            <button class="sidebar_btn" onclick="location.href='create_course.php'">Create Course</button>

            <button class="settings_btn">Settings</button>
            <button class="logout_btn" type="button" onclick="window.location.href='../logout.php'">Logout</button>
        </aside>

        <main class="main_content">
            <div class="search_bar">
                <label for="search">Search:</label>
                <input type="text" id="search" placeholder="Sort By...">
            </div>

            <div class="courses_list" id="coursesList">                
                <div class="course_card add_new" id="addCourseBtn" onclick="window.location.href='create_course.php'" style="cursor:pointer;">
                    <div class="course_icon"></div>
                    <h3>Add Course</h3>
                </div>
                <?php
                    $stmt = $conn->prepare("SELECT id, title, description, section, created_at FROM courses");
                    $stmt->execute();
                    $result = $stmt->get_result();
                    while ($row = $result->fetch_assoc()) {
                        echo '<div class="course_card course_item" style="cursor:pointer;" 
                            data-title="' . htmlspecialchars($row['title']) . '" 
                            data-section="' . htmlspecialchars($row['section']) . '" 
                            data-description="' . htmlspecialchars($row['description']) . '" 
                            data-created_at="' . htmlspecialchars($row['created_at']) . '">';
                        echo '<div class="course_card_info">';
                        echo '<div class="course_icon_large"></div>';
                        echo '<div>';
                        echo '<div class="course_title">' . htmlspecialchars($row['title']) . '</div>';
                        echo '<div class="course_section">' . htmlspecialchars($row['section']) . '</div>';
                        echo '</div>';
                        echo '</div>';
                        echo '<div class="course_card_actions">';
                        echo '<button class="upload_btn_card">UPLOAD</button>';
                        echo '<button class="remove_btn_card">remove</button>';
                        echo '</div>';
                        echo '</div>';
                    }
                    $stmt->close();
                ?>
            </div>
            
        </main>
    </div>

    <div id="courseDetailModal" class="modal">
        <div class="modal_content">
            <button class="close_modal" id="closeModalBtn">&times;</button>
            <h1 id="modalTitle"></h1>
            <p><strong>Section:</strong> <span id="modalSection"></span></p>
            <p><strong>Description:</strong></p>
            <p id="modalDescription"></p>
            <p><strong>Created At:</strong> <span id="modalCreatedAt"></span></p>
        </div>
    </div>

    <script>
    document.addEventListener('DOMContentLoaded', function() {
        const courseItems = document.querySelectorAll('.course_item');
        const modal = document.getElementById('courseDetailModal');
        const modalTitle = document.getElementById('modalTitle');
        const modalSection = document.getElementById('modalSection');
        const modalDescription = document.getElementById('modalDescription');
        const modalCreatedAt = document.getElementById('modalCreatedAt');
        const closeModalBtn = document.getElementById('closeModalBtn');

        courseItems.forEach(function(item) {
            item.addEventListener('click', function(e) {
                // Prevent click on buttons from opening modal
                if (e.target.tagName === 'BUTTON') return;
                modalTitle.textContent = item.getAttribute('data-title');
                modalSection.textContent = item.getAttribute('data-section');
                modalDescription.textContent = item.getAttribute('data-description');
                modalCreatedAt.textContent = item.getAttribute('data-created_at');
                modal.classList.add('show');
            });
        });
        closeModalBtn.addEventListener('click', function() {
            modal.classList.remove('show');
        });
        window.addEventListener('click', function(event) {
            if (event.target === modal) {
                modal.classList.remove('show');
            }
        });
    });
    </script>
</body>
</html>
