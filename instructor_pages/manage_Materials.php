<?php
session_start();
if (!isset($_SESSION['username']) || $_SESSION['role'] !== 'instructor') {
    header('Location: ../login_pages/loginpage.php');
    exit();
}
$conn = new mysqli('localhost', 'root', '', 'learnify');
if ($conn->connect_error) {
    die('Database connection error.');
}
$username = $_SESSION['username'];
// Get instructor id
$stmt = $conn->prepare('SELECT id FROM users WHERE username = ?');
$stmt->bind_param('s', $username);
$stmt->execute();
$stmt->bind_result($instructor_id);
$stmt->fetch();
$stmt->close();
if (!$instructor_id) {
    die('Instructor not found.');
}
// Get courses for instructor
$courses = [];
$res = $conn->query("SELECT id, title FROM courses WHERE instructor_id = $instructor_id");
while ($row = $res->fetch_assoc()) {
    $courses[] = $row;
}
// Get selected course
$selected_course_id = isset($_GET['course_id']) ? intval($_GET['course_id']) : ($courses[0]['id'] ?? 0);
$materials = [];
if ($selected_course_id) {
    $stmt = $conn->prepare('SELECT id, file_name, file_type, uploaded_at FROM file_uploads WHERE course_id = ? ORDER BY uploaded_at DESC');
    $stmt->bind_param('i', $selected_course_id);
    $stmt->execute();
    $result = $stmt->get_result();
    while ($row = $result->fetch_assoc()) {
        $materials[] = $row;
    }
    $stmt->close();
}
// Handle delete
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_material_id'])) {
    $del_id = intval($_POST['delete_material_id']);
    $stmt = $conn->prepare('SELECT file_name FROM file_uploads WHERE id = ? AND course_id = ?');
    $stmt->bind_param('ii', $del_id, $selected_course_id);
    $stmt->execute();
    $stmt->bind_result($del_file);
    $stmt->fetch();
    $stmt->close();
    if ($del_file && file_exists('../uploads/' . $del_file)) {
        unlink('../uploads/' . $del_file);
    }
    $stmt = $conn->prepare('DELETE FROM file_uploads WHERE id = ? AND course_id = ?');
    $stmt->bind_param('ii', $del_id, $selected_course_id);
    $stmt->execute();
    $stmt->close();
    header('Location: manage_Materials.php?course_id=' . $selected_course_id);
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Materials</title>
    <link rel="stylesheet" href="manage_Materials.css">
</head>
<body>
<div class="container">
    
    <aside class="sidebar">
        <button onclick="window.location.href='instructor_dashboard.php'" style="margin-bottom:18px; background:#38406a; color:#fff; border:none; border-radius:6px; padding:8px 22px; font-size:1em; cursor:pointer;">Back</button>

        <h2>Manage Materials</h2>
        <form method="get" action="" style="margin-bottom:24px;">
            <label for="course_id">Select Course:</label>
            <select name="course_id" id="course_id" onchange="this.form.submit()">
                <?php foreach ($courses as $c): ?>
                    <option value="<?= $c['id'] ?>" <?= $selected_course_id == $c['id'] ? 'selected' : '' ?>><?= htmlspecialchars($c['title']) ?></option>
                <?php endforeach; ?>
            </select>
        </form>
    </aside>
    <main class="main_content">
        <h1>Materials for <?= htmlspecialchars($courses[array_search($selected_course_id, array_column($courses, 'id'))]['title'] ?? '') ?></h1>
        <form id="uploadForm" enctype="multipart/form-data" method="post" style="margin-bottom:24px;">
            <input type="hidden" name="course_id" value="<?= $selected_course_id ?>">
            <input type="hidden" name="file_type" id="file_type">
            <label for="file">Upload Material:</label>
            <input type="file" name="file" id="file" required>
            <button type="submit">Upload</button>
        </form>
        <script>
        document.getElementById('file').addEventListener('change', function() {
            var val = this.value.split('.').pop().toLowerCase();
            var type = 'other';
            if(['pdf'].includes(val)) type = 'pdf';
            else if(['mp4','avi','mov','wmv'].includes(val)) type = 'video';
            else if(['jpg','jpeg','png','gif','bmp'].includes(val)) type = 'image';
            else if(['doc','docx','xls','xlsx','ppt','pptx','txt','csv'].includes(val)) type = 'document';
            document.getElementById('file_type').value = type;
        });
        document.getElementById('uploadForm').addEventListener('submit', function(e) {
            e.preventDefault();
            var form = this;
            var formData = new FormData(form);
            fetch('upload_material.php', {
                method: 'POST',
                body: formData
            })
            .then(async res => {
                let data;
                try {
                    data = await res.json();
                } catch (err) {
                    alert('Upload succeeded, but server did not return valid JSON.');
                    form.reset();
                    return;
                }
                if (data.success) {
                    alert('Material uploaded successfully!');
                    form.reset();
                } else {
                    alert(data.error || 'Upload failed.');
                }
            })
            .catch(function() {
                alert('Error connecting to server.');
            });
        });
        </script>
        <div class="materials-list">
            <table class="materials-table">
                <thead>
                    <tr>
                        <th>File Name</th>
                        <th>Type</th>
                        <th>Uploaded At</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                <?php foreach ($materials as $m): ?>
                    <tr>
                        <td><a href="../uploads/<?= urlencode($m['file_name']) ?>" target="_blank"><?= htmlspecialchars($m['file_name']) ?></a></td>
                        <td><?= htmlspecialchars($m['file_type']) ?></td>
                        <td><?= htmlspecialchars($m['uploaded_at']) ?></td>
                        <td>
                            <form method="post" style="display:inline;">
                                <input type="hidden" name="delete_material_id" value="<?= $m['id'] ?>">
                                <button type="submit" onclick="return confirm('Delete this material?')">Delete</button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
                <?php if (empty($materials)): ?>
                    <tr><td colspan="4" style="text-align:center;">No materials uploaded yet.</td></tr>
                <?php endif; ?>
                </tbody>
            </table>
        </div>
    </main>
</div>
</body>
</html>
