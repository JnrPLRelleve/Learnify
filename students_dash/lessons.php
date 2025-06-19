<?php
session_start();
if (!isset($_SESSION['username']) || $_SESSION['role'] !== 'student') {
    header('Location: loginpage.php');
    exit();
}
$conn = new mysqli('localhost', 'root', '', 'learnify');
if ($conn->connect_error) {
    die('Database connection error.');
}

// Get student id
$stmt = $conn->prepare('SELECT id FROM users WHERE username = ?');
$stmt->bind_param('s', $_SESSION['username']);
$stmt->execute();
$stmt->bind_result($student_id);
$stmt->fetch();
$stmt->close();
if (!$student_id) {
    die('Student not found.');
}

// Get enrolled courses
$courses = [];
$stmt = $conn->prepare('SELECT courses.id, courses.title, users.username AS instructor_name FROM enrollments JOIN courses ON enrollments.course_id = courses.id JOIN users ON courses.instructor_id = users.id WHERE enrollments.student_id = ?');
$stmt->bind_param('i', $student_id);
$stmt->execute();
$result = $stmt->get_result();
while ($row = $result->fetch_assoc()) {
    $courses[] = $row;
}

$stmt->close();
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
    <title>Lessons</title>
    <link rel="stylesheet" href="student_dashboard.css">
    <link rel="stylesheet" href="lessons.css">
</head>
<body>
    <div class="container">
        <aside class="sidebar">
            <div class="profile_pic"><img src="<?php echo htmlspecialchars($profilePic); ?>" alt="sample"></div>
            <h2><span style="font-size:1em;font-weight:400;"><?php echo htmlspecialchars($_SESSION['username']); ?></span></h2>
            <button class="sidebar_btn" onclick="window.location.href='student_dashboard.php'">Courses</button>
            
            <button class="sidebar_btn" onclick="window.location.href='lessons.php'">Lessons</button>
            <button class="sidebar_btn" onclick="window.location.href='Student_Quiz_Inter.php'">Quiz Interface</button>
            <button class="settings_btn" onclick="window.location.href='STU_settings.php'">Settings</button>
            <button class="logout_btn" type="button" onclick="window.location.href='../login_pages/logout.php'">Logout</button>
        </aside>
        <main class="main_content">
            <h1>Lessons</h1>
            <div class="course-box-list" id="courseBoxList" style="display:flex;flex-wrap:wrap;gap:18px;margin-bottom:24px;justify-content:center;"></div>
            <div style="text-align:center;margin-bottom:24px;">
                <button id="prevCourseBtn" style="padding:8px 18px;margin-right:12px;border-radius:8px;border:1.5px solid #b3dbe6;background:#e6fdff;color:#38406a;font-weight:600;cursor:pointer;">Previous</button>
                <span id="coursePageInfo" style="font-size:1.1em;color:#38406a;"></span>
                <button id="nextCourseBtn" style="padding:8px 18px;margin-left:12px;border-radius:8px;border:1.5px solid #b3dbe6;background:#e6fdff;color:#38406a;font-weight:600;cursor:pointer;">Next</button>
            </div>
            <div class="materials-list" id="materialsList" style="max-height:320px; overflow-y:auto;">
                <div style="color:#888;">Select a course to view its learning materials.</div>
            </div>
            <div id="withdrawContainer" style="margin-top:18px; text-align:right;">
                <?php if (count($courses) > 0): ?>
                    <?php foreach ($courses as $i => $course): ?>
                        <button class="withdraw-btn" data-course-id="<?= $course['id'] ?>" style="display:none; background:#ff4d4d; color:#fff; border:none; border-radius:5px; padding:6px 18px; cursor:pointer; margin-top:8px;">Withdraw from <?= htmlspecialchars($course['title']) ?></button>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </main>
    </div>
    <script>
    const courses = <?php echo json_encode($courses); ?>;
let currentPage = 0;
function renderCourseBox(page) {
    const list = document.getElementById('courseBoxList');
    list.innerHTML = '';
    if (courses.length === 0) {
        list.innerHTML = '<div style="color:#888; padding:12px 0;">You are not enrolled in any courses.</div>';
        document.getElementById('coursePageInfo').textContent = '';
        document.getElementById('prevCourseBtn').style.display = 'none';
        document.getElementById('nextCourseBtn').style.display = 'none';
        document.getElementById('materialsList').innerHTML = '<div style="color:#888;">Select a course to view its learning materials.</div>';
        return;
    }
    const course = courses[page];
    if (!course) return;
    const div = document.createElement('div');
    div.className = 'course-box';
    div.setAttribute('data-course-id', course.id);
    div.style.background = '#f8fafd';
    div.style.border = '1.5px solid #b3dbe6';
    div.style.borderRadius = '12px';
    div.style.padding = '18px 24px';
    div.style.minWidth = '220px';
    div.style.maxWidth = '260px';
    div.style.cursor = 'pointer';
    div.style.boxShadow = '0 2px 8px rgba(91,98,145,0.08)';
    div.style.transition = 'box-shadow 0.2s';
    div.innerHTML =
        '<div style="font-weight:bold;font-size:1.1em;margin-bottom:8px;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;">' +
        course.title + '</div>' +
        '<div style="color:#555; margin-bottom:8px; font-size:0.97em;">Instructor: ' + (course.instructor_name ? course.instructor_name : '') + '</div>' +
        '<div class="progress-container" id="progress-bar-' + course.id + '" style="width:100px; height:10px; background:#e0e0e0; border-radius:6px; margin:6px 0 0 0;">' +
        '<div class="progress-bar" style="height:100%; width:0%; background:#007bff; border-radius:6px;"></div>' +
        '</div>' +
        '<span class="progress-label" id="progress-label-' + course.id + '" style="font-size:0.95em; color:#007bff;">0%</span>';
    div.onclick = function() {
        document.querySelectorAll('.course-box').forEach(function(b) { b.style.boxShadow = '0 2px 8px rgba(91,98,145,0.08)'; });
        div.style.boxShadow = '0 0 0 3px #007bff';
        loadMaterials(course.id);
        showWithdrawBtn(course.id);
    };
    list.appendChild(div);
    document.getElementById('coursePageInfo').textContent = (page+1) + ' / ' + courses.length;
    document.getElementById('prevCourseBtn').style.display = (page > 0) ? '' : 'none';
    document.getElementById('nextCourseBtn').style.display = (page < courses.length-1) ? '' : 'none';
    // Always load materials for the current course
    loadMaterials(course.id);
    showWithdrawBtn(course.id);
}
document.getElementById('prevCourseBtn').onclick = function() {
    if (currentPage > 0) {
        currentPage--;
        renderCourseBox(currentPage);
    }
};
document.getElementById('nextCourseBtn').onclick = function() {
    if (currentPage < courses.length-1) {
        currentPage++;
        renderCourseBox(currentPage);
    }
};
    function groupByType(files) {
        const types = { pdf: [], video: [], image: [], document: [], other: [] };
        files.forEach(file => {
            let t = file.file_type.toLowerCase();
            if (!types[t]) t = 'other';
            types[t].push(file);
        });
        return types;
    }
    function updateProgressBar(courseId, percent) {
        if (percent > 100) percent = 100;
        var bar = document.querySelector('#progress-bar-' + courseId + ' .progress-bar');
        var label = document.getElementById('progress-label-' + courseId);
        if (bar && label) {
            bar.style.width = percent + '%';
            label.textContent = percent + '%';
        }
    }
    function getDoneMaterials(courseId) {
        const key = 'done_materials_' + courseId + '_<?= $student_id ?>';
        try {
            return JSON.parse(localStorage.getItem(key)) || [];
        } catch {
            return [];
        }
    }
    function setDoneMaterials(courseId, arr) {
        const key = 'done_materials_' + courseId + '_<?= $student_id ?>';
        localStorage.setItem(key, JSON.stringify(arr));
    }
    function sendProgress(courseId, doneFiles) {
        fetch('update_progress.php', {
            method: 'POST',
            headers: {'Content-Type': 'application/x-www-form-urlencoded'},
            body: 'course_id=' + encodeURIComponent(courseId) + '&' + doneFiles.map(f => 'done_files[]=' + encodeURIComponent(f)).join('&')
        })
        .then(res => res.json())
        .then(function(data) {
            if (data.success) {
                updateProgressBar(courseId, data.progress);
                // If all files are unchecked (doneFiles.length === 0), also delete progress_tracking row for this course
                if (doneFiles.length === 0) {
                    fetch('delete_progress.php', {
                        method: 'POST',
                        headers: {'Content-Type': 'application/x-www-form-urlencoded'},
                        body: 'course_id=' + encodeURIComponent(courseId)
                    });
                }
            }
        });
    }
    function loadAllProgress() {
        fetch('get_progress.php')
            .then(res => res.json())
            .then(progress => {
                document.querySelectorAll('.course-tab').forEach(function(tab) {
                    var courseId = tab.getAttribute('data-course-id');
                    var percent = progress[courseId] ? progress[courseId] : 0;
                    updateProgressBar(courseId, percent);
                });
            });
    }
    function loadMaterials(courseId) {
        fetch('get_course_materials.php?course_id=' + encodeURIComponent(courseId))
            .then(response => response.json())
            .then(data => {
                var list = document.getElementById('materialsList');
                list.innerHTML = '';
                if (data.length === 0) {
                    list.innerHTML = '<div style="color:#888;">No materials uploaded for this course.</div>';
                    updateProgressBar(courseId, 0);
                    return;
                }
                const grouped = groupByType(data);
                const folderNames = {
                    pdf: 'PDFs',
                    video: 'Videos',
                    image: 'Images',
                    document: 'Documents',
                    other: 'Others'
                };
                let hasAny = false;
                let allFiles = [];
                Object.keys(folderNames).forEach(type => {
                    if (grouped[type].length > 0) {
                        hasAny = true;
                        const folder = document.createElement('div');
                        folder.style.marginBottom = '18px';
                        folder.innerHTML = '<div style="font-weight:bold;font-size:1.1em;margin-bottom:6px;">' + folderNames[type] + '</div>';
                        grouped[type].forEach(file => {
                            allFiles.push(file.file_name);
                        });
                        grouped[type].forEach(file => {
                            const item = document.createElement('div');
                            item.className = 'material-item';
                            const link = '<a href="../uploads/' + encodeURIComponent(file.file_name) + '" target="_blank">' + file.file_name + '</a>';
                            // Done button
                            const doneBtn = document.createElement('button');
                            doneBtn.textContent = 'Done';
                            doneBtn.style.marginLeft = '12px';
                            doneBtn.className = 'done-btn';
                            let doneMaterials = getDoneMaterials(courseId);
                            if (doneMaterials.includes(file.file_name)) {
                                item.style.background = '#e6ffe6';
                                doneBtn.disabled = true;
                                doneBtn.textContent = 'Completed';
                            }
                            doneBtn.onclick = function() {
                                let doneMaterials = getDoneMaterials(courseId);
                                if (!doneMaterials.includes(file.file_name)) {
                                    doneMaterials.push(file.file_name);
                                    setDoneMaterials(courseId, doneMaterials);
                                    item.style.background = '#e6ffe6';
                                    doneBtn.disabled = true;
                                    doneBtn.textContent = 'Completed';
                                    // Recalculate progress based on allFiles.length
                                    let completedCount = 0;
                                    allFiles.forEach(f => { if (getDoneMaterials(courseId).includes(f)) completedCount++; });
                                    let percent = allFiles.length > 0 ? Math.round((completedCount / allFiles.length) * 100) : 0;
                                    if (percent > 100) percent = 100;
                                    updateProgressBar(courseId, percent);
                                    sendProgress(courseId, getDoneMaterials(courseId));
                                }
                            };
                            item.innerHTML = link;
                            item.appendChild(doneBtn);
                            folder.appendChild(item);
                        });
                        list.appendChild(folder);
                    }
                });
                if (!hasAny) {
                    list.innerHTML = '<div style="color:#888;">No materials uploaded for this course.</div>';
                    updateProgressBar(courseId, 0);
                } else {
                    // Fix: progress should be based on allFiles.length and only count done files that exist in allFiles
                    let doneMaterials = getDoneMaterials(courseId);
                    // Only count as completed those files that are both in doneMaterials and in allFiles
                    let completedCount = allFiles.filter(f => doneMaterials.includes(f)).length;
                    let percent = allFiles.length > 0 ? Math.round((completedCount / allFiles.length) * 100) : 0;
                    if (percent > 100) percent = 100;
                    updateProgressBar(courseId, percent);
                    sendProgress(courseId, doneMaterials.filter(f => allFiles.includes(f)));
                }
            });
    }
    function showWithdrawBtn(courseId) {
        document.querySelectorAll('.withdraw-btn').forEach(function(btn) {
            btn.style.display = 'none';
        });
        var btn = document.querySelector('.withdraw-btn[data-course-id="' + courseId + '"]');
        if (btn) btn.style.display = 'inline-block';
    }
    document.querySelectorAll('.course-tab').forEach(function(tab, idx) {
        tab.addEventListener('click', function() {
            document.querySelectorAll('.course-tab').forEach(function(t) { t.classList.remove('active'); });
            tab.classList.add('active');
            var courseId = tab.getAttribute('data-course-id');
            loadMaterials(courseId);
            showWithdrawBtn(courseId);
        });
    });
    document.addEventListener('DOMContentLoaded', function() {
        renderCourseBox(currentPage);
        loadAllProgress();
    });
    document.querySelectorAll('.withdraw-btn').forEach(function(btn) {
        btn.addEventListener('click', function() {
            var courseId = btn.getAttribute('data-course-id');
            if (confirm('Are you sure you want to withdraw from this course?')) {
                fetch('withdraw_course.php', {
                    method: 'POST',
                    headers: {'Content-Type': 'application/x-www-form-urlencoded'},
                    body: 'course_id=' + encodeURIComponent(courseId)
                })
                .then(res => res.json())
                .then(function(data) {
                    if (data.success) {
                        // Remove localStorage progress for this course
                        localStorage.removeItem('done_materials_' + courseId + '_<?= $student_id ?>');
                        // Remove the course from the courses array
                        const idx = courses.findIndex(c => c.id == courseId);
                        if (idx !== -1) {
                            courses.splice(idx, 1);
                            if (currentPage >= courses.length) currentPage = Math.max(0, courses.length-1);
                            renderCourseBox(currentPage);
                        }
                        btn.style.display = 'none';
                        document.getElementById('materialsList').innerHTML = '<div style="color:#888;">Select a course to view its learning materials.</div>';
                        loadAllProgress();
                    } else {
                        alert(data.error || 'Failed to withdraw from course.');
                    }
                });
            }
        });
    });
    </script>
</body>
</html>
