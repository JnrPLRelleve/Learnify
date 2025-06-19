<?php
session_start();

if (!isset($_SESSION['username'])) {
    header("Location: loginpage.php");
    exit();
}

if ($_SESSION['role'] !== 'student') {
    header("Location: loginpage.php");
    exit();
}

// Fetch profile picture filename from DB
$profilePic = '../images/AdminPerson.jpg'; // default
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
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>STUDENT DASHBOARD</title>
    <link rel="stylesheet" href="student_dashboard.css">
</head>
<body>
    <div class="container">
        <aside class="sidebar">
            <div class="profile_pic"><img src="<?php echo htmlspecialchars($profilePic); ?>" alt="sample"></div>
            <h2><span style="font-size:1em;font-weight:400;"><?php echo htmlspecialchars($_SESSION['username']); ?></span></h2>

            <button class="sidebar_btn">Courses</button>
            <button class="sidebar_btn" onclick="window.location.href='lessons.php'">Lessons</button>
            <button class="sidebar_btn" onclick="window.location.href='Student_Quiz_Inter.php'">Quiz Interface</button>

            <button class="settings_btn" onclick="window.location.href='STU_settings.php'">Settings</button>
            <button class="logout_btn" type="button" onclick="window.location.href='../login_pages/logout.php'">Logout</button>
        </aside>

        <main class="main_content">
            
            <div class="courses_list" id="coursesList">                
                <div class="course_card add_new" id="addCourseBtn">
                    <div class="course_icon"></div>
                    <h3>Enroll Course</h3>
                </div>
            </div>
            <div class="search_bar">
                <label for="search">Search:</label>
                <input type="text" id="search" placeholder="enrolled courses...">
            </div>

            <div class="enrolled_courses_section" id="enrolledCoursesSection" style="margin-bottom:32px;">
                <h2 style="margin-top:32px; margin-bottom:12px;">My Enrolled Courses</h2>
                <div id="enrolledCoursesList" style="display:flex; flex-wrap:wrap; gap:12px; max-height:450px; overflow-y:auto;"></div>
            </div>
            

            
        </main>
    </div>

    <div class="modal" id="courseModal" style="display: none;">
        <div class="modal_content" style="max-width:600px; width:90vw; max-height:80vh; overflow-y:auto;">
            <button class="close_modal" id="closeModalBtn">Back</button>
            <h1 id="H1_course" style="margin-bottom: 24px;">Select a Course</h1>
            <div id="courseList" style="display: flex; flex-wrap: wrap; gap: 16px; justify-content: center; max-height:48vh; overflow-y:auto;">
            <?php
            $conn = new mysqli('localhost', 'root', '', 'learnify');
            if ($conn->connect_error) {
                echo '<p>Database connection error.</p>';
            } else {
                $sql = "SELECT courses.id, courses.title, courses.description, courses.instructor_id, users.username AS instructor_name FROM courses LEFT JOIN users ON courses.instructor_id = users.id";
                $result = $conn->query($sql);
                if ($result && $result->num_rows > 0) {
                    while ($row = $result->fetch_assoc()) {
                        $courseName = htmlspecialchars($row['title']);
                        $instructorName = htmlspecialchars($row['instructor_name'] ?? 'Unknown');
                        $description = nl2br(htmlspecialchars($row['description']));
                        echo '<div class="course_card_option" data-course-id="' . $row['id'] . '" data-course-name="' . $courseName . '" data-instructor-name="' . $instructorName . '" style="border:1px solid #ccc; border-radius:8px; padding:16px; min-width:220px; cursor:pointer; background:#f9f9f9; transition:box-shadow 0.2s; position:relative;">';
                        echo '<div style="font-weight:bold; font-size:1.1em; margin-bottom:8px;">' . $courseName . '</div>';
                        echo '<div style="color:#555; margin-bottom:8px;">Instructor: ' . $instructorName . '</div>';
                        echo '<button class="view_desc_btn" style="margin-bottom:8px;">View Description</button>';
                        echo '<div class="course_desc" style="display:none; color:#333; font-size:0.97em; margin-bottom:8px; background:#eef2fa; border-radius:6px; padding:8px;">' . $description . '</div>';
                        echo '</div>';
                    }
                } else {
                    echo '<p>No courses available.</p>';
                }
                $conn->close();
            }
            ?>
            </div>
            <button class="enroll_btn" id="enrollBtn">Enroll</button>
            <script>
            document.addEventListener('DOMContentLoaded', function() {
                document.querySelectorAll('.course_card_option').forEach(function(card) {
                    card.addEventListener('click', function(e) {
                        // Only select card if not clicking the view description button
                        if (!e.target.classList.contains('view_desc_btn')) {
                            document.querySelectorAll('.course_card_option').forEach(function(c) {
                                c.style.boxShadow = '';
                                c.removeAttribute('data-selected');
                            });
                            card.style.boxShadow = '0 0 0 2px #007bff';
                            card.setAttribute('data-selected', 'true');
                        }
                    });
                    // View Description button logic
                    var viewBtn = card.querySelector('.view_desc_btn');
                    var descDiv = card.querySelector('.course_desc');
                    if (viewBtn && descDiv) {
                        viewBtn.addEventListener('click', function(e) {
                            e.stopPropagation();
                            descDiv.style.display = descDiv.style.display === 'none' ? 'block' : 'none';
                            viewBtn.textContent = descDiv.style.display === 'none' ? 'View Description' : 'Hide Description';
                        });
                    }
                });

                // Enroll button logic
                document.getElementById('enrollBtn').addEventListener('click', function() {
                    var selected = document.querySelector('.course_card_option[data-selected="true"]');
                    if (!selected) {
                        alert('Please select a course to enroll.');
                        return;
                    }
                    var courseId = selected.getAttribute('data-course-id');
                    fetch('enroll_course.php', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                        body: 'course_id=' + encodeURIComponent(courseId)
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            loadEnrolledCourses();
                            alert('Enrolled successfully!');
                        } else {
                            alert(data.error || 'Failed to enroll.');
                        }
                    })
                    .catch(() => alert('Error connecting to server.'));
                });

                // Load enrolled courses on page load
                loadEnrolledCourses();

                // Search functionality for enrolled courses
                document.getElementById('search').addEventListener('input', function() {
                    const searchTerm = this.value.trim().toLowerCase();
                    const cards = document.querySelectorAll('#enrolledCoursesList .enrolled_course_card');
                    cards.forEach(function(card) {
                        const title = card.querySelector('div[style*="font-weight:bold"]')?.textContent.toLowerCase() || '';
                        const instructor = card.querySelector('div[style*="color:#555"]')?.textContent.toLowerCase() || '';
                        if (title.includes(searchTerm) || instructor.includes(searchTerm)) {
                            card.style.display = '';
                        } else {
                            card.style.display = 'none';
                        }
                    });
                });

                function loadEnrolledCourses() {
                    fetch('get_enrolled_courses.php')
                    .then(response => response.json())
                    .then(data => {
                        var list = document.getElementById('enrolledCoursesList');
                        list.innerHTML = '';
                        if (data.length === 0) {
                            list.innerHTML = '<div style="color:#888;">No enrolled courses yet.</div>';
                        } else {
                            data.forEach(function(course) {
                                var div = document.createElement('div');
                                div.className = 'enrolled_course_card';
                                div.style = 'border:1px solid #bbb; border-radius:8px; padding:12px 18px; background:#f5faff; min-width:180px;';
                                div.innerHTML = '<div style="font-weight:bold;">' + course.title + '</div>' +
                                    '<div style="color:#555; font-size:0.97em;">Instructor: ' + course.instructor_name + '</div>' +
                                    '<button class="view_enrolled_desc_btn" style="margin-top:8px; margin-bottom:8px;">View Description</button>' +
                                    '<div class="enrolled_course_desc" style="display:none; color:#333; font-size:0.97em; margin-bottom:8px; background:#eef2fa; border-radius:6px; padding:8px;">' + (course.description || 'No description available.') + '</div>';
                                list.appendChild(div);
                                // Add toggle logic for description
                                var viewBtn = div.querySelector('.view_enrolled_desc_btn');
                                var descDiv = div.querySelector('.enrolled_course_desc');
                                if (viewBtn && descDiv) {
                                    viewBtn.addEventListener('click', function(e) {
                                        descDiv.style.display = descDiv.style.display === 'none' ? 'block' : 'none';
                                        viewBtn.textContent = descDiv.style.display === 'none' ? 'View Description' : 'Hide Description';
                                    });
                                }
                            });
                        }
                    });
                }
            });
            </script>
        </div>
    </div>

    <div class="modal" id="courseDetailsModal" style="display: none;">
        <div class="modal_content">
            <button class="close_modal" id="closeDetailsModalBtn">Close</button>
            <h1 id="courseDetailsTitle"></h1>
            <p id="courseDetailsText">Lorem ipsum dolor sit amet, consectetur adipiscing elit. Sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat.</p>
            <div class="modal_actions">
                <button class="unenroll_btn" id="unenrollBtn">Unenroll</button>
                <button class="view_materials_btn" id="viewMaterialsBtn">View Materials</button>
            </div>
        </div>
    </div>
    
    
    

    <script src="student_dashboard.js"></script>
</body>
</html>