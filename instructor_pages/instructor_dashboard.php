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

// Get instructor user id
$instructor_id = null;
$profilePic = '../images/AdminPerson.jpg'; // default
if (isset($_SESSION['username'])) {
    $user_stmt = $conn->prepare('SELECT id, profile_picture FROM users WHERE username = ?');
    $user_stmt->bind_param('s', $_SESSION['username']);
    $user_stmt->execute();
    $user_stmt->bind_result($instructor_id, $profile_picture);
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
    <title>Instructor Dashboard</title>
    <link rel="stylesheet" href="instructor_dashboard.css">
    
</head>
<body>
    <div class="container">
        <aside class="sidebar">
            <div class="profile_pic"><img src="<?php echo htmlspecialchars($profilePic); ?>" alt="sample"></div>
            <h2><span style="font-size:1em;font-weight:400;"><?php echo htmlspecialchars($_SESSION['username']); ?></span></h2>
            <button class="sidebar_btn">Courses</button>
            <button class="sidebar_btn" onclick="location.href='instructor_Quiz_Inter.php'">Quiz Interface</button>
            <button class="sidebar_btn" onclick="location.href='create_course.php'">Create Course</button>
            <button class="sidebar_btn" onclick="location.href='manage_Materials.php'">Materials</button>
            <button class="settings_btn" onclick="location.href='INS_settings.php'">Settings</button>
            <button class="logout_btn" type="button" onclick="window.location.href='../login_pages/logout.php'">Logout</button>
        </aside>

        <main class="main_content">
            <div class="search_bar">
                <label for="search">Search:</label>
                <input type="text" id="search" placeholder="Sort By Course name...">
            </div>

            <div class="courses_list" id="coursesList">                
                <div class="course_card add_new" id="addCourseBtn" onclick="window.location.href='create_course.php'" style="cursor:pointer;">
                    <h3>Add Course</h3>
                </div>
                <?php
                    $stmt = $conn->prepare("SELECT id, title, description, section, created_at FROM courses WHERE instructor_id = ?");
                    $stmt->bind_param('i', $instructor_id);
                    $stmt->execute();
                    $result = $stmt->get_result();
                    while ($row = $result->fetch_assoc()) { //card for courses
                        echo '<div class="course_card course_item" style="cursor:pointer;" 
                            data-id="' . htmlspecialchars($row['id']) . '" 
                            data-title="' . htmlspecialchars($row['title']) . '" 
                            data-section="' . htmlspecialchars($row['section']) . '" 
                            data-description="' . htmlspecialchars($row['description']) . '" 
                            data-created_at="' . htmlspecialchars($row['created_at']) . '">';
                        echo '<div class="course_card_info">';
                        
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

    <div id="uploadModal" class="modal" style="display:none;">
        <div class="modal_content">
            <button class="close_modal" id="closeUploadModalBtn">&times;</button>
            <h2>Upload Learning Material</h2>
            <form id="uploadForm" enctype="multipart/form-data">
                <input type="hidden" name="course_id" id="uploadCourseId">
                <div style="margin-bottom:12px;">
                    <label for="file">Select File:</label>
                    <input type="file" name="file" id="file" required accept=".pdf,.mp4,.avi,.mov,.jpg,.jpeg,.png,.doc,.docx,.ppt,.pptx,.xls,.xlsx,.txt">
                </div>
                <div style="margin-bottom:12px;">
                    <label for="description">Description:</label>
                    <textarea name="description" id="description" rows="2" style="width:100%;"></textarea>
                </div>
                <div style="margin-bottom:12px;">
                    <label for="file_type">File Type:</label>
                    <select name="file_type" id="file_type" required>
                        <option value="pdf">PDF</option>
                        <option value="video">Video</option>
                        <option value="image">Image</option>
                        <option value="document">Document</option>
                        <option value="other">Other</option>
                    </select>
                </div>
                <button type="submit" class="upload_btn_card" style="margin-top:8px;">Upload</button>
            </form>
            <div id="uploadStatus" style="margin-top:10px;color:#007bff;"></div>
            <div id="fileListForCourse" style="margin-top:12px;"></div>
        </div>
    </div>

    <script src="instructor.js"></script>
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        // Remove button logic
        document.querySelectorAll('.remove_btn_card').forEach(function(btn) {
            btn.addEventListener('click', function(e) {
                e.stopPropagation();
                const courseCard = btn.closest('.course_card');
                const courseId = courseCard.getAttribute('data-id');
                if (confirm('Are you sure you want to remove this course?')) {
                    fetch('remove_course.php', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                        body: 'id=' + encodeURIComponent(courseId)
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            courseCard.remove();
                        } else {
                            alert('Failed to remove course.');
                        }
                    })
                    .catch(() => alert('Error connecting to server.'));
                }
            });
        });
        // Search bar logic
        document.getElementById('search').addEventListener('input', function() {
            const query = this.value.toLowerCase();
            document.querySelectorAll('.course_card.course_item').forEach(function(card) {
                const title = card.getAttribute('data-title').toLowerCase();
                if (title.includes(query)) {
                    card.style.display = '';
                } else {
                    card.style.display = 'none';
                }
            });
        });
        // Upload modal logic
        var courseUploadBtns = document.querySelectorAll('.course_card.course_item .upload_btn_card');
        courseUploadBtns.forEach(function(btn) {
            btn.addEventListener('click', function(e) {
                e.stopPropagation();
                const courseCard = btn.closest('.course_card');
                const courseId = courseCard.getAttribute('data-id');
                document.getElementById('uploadCourseId').value = courseId;
                document.getElementById('uploadModal').style.display = 'block';
                document.getElementById('uploadStatus').textContent = '';
                // Fetch and display files for this course
                fetch('get_course_files.php?course_id=' + encodeURIComponent(courseId))
                    .then(response => response.json())
                    .then(files => {
                        var fileListDiv = document.getElementById('fileListForCourse');
                        if (!fileListDiv) {
                            fileListDiv = document.createElement('div');
                            fileListDiv.id = 'fileListForCourse';
                            document.querySelector('#uploadModal .modal_content').appendChild(fileListDiv);
                        }
                        fileListDiv.innerHTML = '<h4 style="margin-top:18px;">Files for this course:</h4>';
                        if (files.length === 0) {
                            fileListDiv.innerHTML += '<div style="color:#888;">No files uploaded yet.</div>';
                        } else {
                            files.forEach(function(file) {
                                fileListDiv.innerHTML += '<div style="margin-bottom:8px;">' +
                                    '<a href="../uploads/' + encodeURIComponent(file.file_name) + '" target="_blank">' + file.file_name + '</a>' +
                                    ' <span style="color:#888;font-size:0.95em;">(' + file.file_type + ')</span>' +
                                    '</div>';
                            });
                        }
                    });
            });
        });
        document.getElementById('closeUploadModalBtn').onclick = function() {
            document.getElementById('uploadModal').style.display = 'none';
        };
        // Handle file upload
        document.getElementById('uploadForm').onsubmit = function(e) {
            e.preventDefault();
            var formData = new FormData(this);
            fetch('upload_material.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                document.getElementById('uploadStatus').textContent = data.success ? 'Upload successful!' : (data.error || 'Upload failed.');
                if (data.success) {
                    setTimeout(function() {
                        document.getElementById('uploadModal').style.display = 'none';
                    }, 1200);
                }
            })
            .catch(() => {
                document.getElementById('uploadStatus').textContent = 'Error uploading file.';
            });
        };
    });
    </script>
</body>
</html>
