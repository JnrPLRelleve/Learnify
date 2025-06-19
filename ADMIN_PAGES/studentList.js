function viewCourses(event, name) {
    event.stopPropagation();
    alert('View courses for ' + name);
}
function deleteStudentCard(event, name) {
    event.stopPropagation();
    if (confirm('Are you sure you want to remove ' + name + '?')) {
        event.target.closest('.card').remove();
    }
}

var currentStudentId = null;
function viewCourses(event, studentId, username) {
    currentStudentId = studentId;
    event.stopPropagation();
    var xhr = new XMLHttpRequest();
    xhr.open('GET', 'get_student_courses.php?student_id=' + encodeURIComponent(studentId), true);
    xhr.onload = function() {
        if (xhr.status === 200) {
            var courses = JSON.parse(xhr.responseText);
            var tableBody = document.getElementById('studentCoursesTable').getElementsByTagName('tbody')[0];
            tableBody.innerHTML = '';
            if (courses.length > 0) {
                courses.forEach(function(course) {
                    var tr = document.createElement('tr');
                    var tdTitle = document.createElement('td');
                    tdTitle.textContent = course.title;
                    tdTitle.style.padding = '6px 8px';
                    tdTitle.style.border = '1px solid #bbb';
                    var tdDesc = document.createElement('td');
                    tdDesc.textContent = course.description;
                    tdDesc.style.padding = '6px 8px';
                    tdDesc.style.border = '1px solid #bbb';
                    var tdEnrolled = document.createElement('td');
                    tdEnrolled.textContent = course.enrolled_at;
                    tdEnrolled.style.padding = '6px 8px';
                    tdEnrolled.style.border = '1px solid #bbb';
                    var tdActions = document.createElement('td');
                    tdActions.style.padding = '6px 8px';
                    tdActions.style.border = '1px solid #bbb';
                    var unenrollBtn = document.createElement('button');
                    unenrollBtn.textContent = 'Unenroll';
                    unenrollBtn.className = 'unenroll-btn';
                    unenrollBtn.onclick = function(e) {
                        e.stopPropagation();
                        if(confirm('Are you sure you want to unenroll this student from the course?')) {
                            unenrollStudentFromCourse(course.course_id, studentId, tr);
                        }
                    };
                    tdActions.appendChild(unenrollBtn);
                    tr.appendChild(tdTitle);
                    tr.appendChild(tdDesc);
                    tr.appendChild(tdEnrolled);
                    tr.appendChild(tdActions);
                    tableBody.appendChild(tr);
                    // Progress bar row under the course row
                    var progressTr = document.createElement('tr');
                    var progressTd = document.createElement('td');
                    progressTd.colSpan = 4;
                    var progress = course.progress_percentage !== null ? course.progress_percentage : 0;
                    progressTd.innerHTML = `<div style='margin:6px 0 12px 0; background:#eee; border-radius:6px; height:16px; width:100%; overflow:hidden; border:1px solid #bbb;'>
                        <div style='background:#4caf50; height:100%; width:${progress}%; color:#fff; text-align:center; line-height:16px; font-size:0.95em;'>${progress}%</div>
                    </div>`;
                    progressTr.appendChild(progressTd);
                    tableBody.appendChild(progressTr);
                });
            } else {
                var tr = document.createElement('tr');
                var td = document.createElement('td');
                td.colSpan = 4;
                td.textContent = 'No enrolled courses found.';
                td.style.padding = '6px 8px';
                td.style.border = '1px solid #bbb';
                tr.appendChild(td);
                tableBody.appendChild(tr);
            }
            document.getElementById('studentCoursesModalTitle').textContent = 'Enrolled Courses for ' + username;
            document.getElementById('studentCoursesModal').style.display = 'flex';
        }
    };
    xhr.send();
}
function closeStudentCoursesModal() {
    document.getElementById('studentCoursesModal').style.display = 'none';
}
function unenrollStudentFromCourse(courseId, studentId, rowElem) {
    var xhr = new XMLHttpRequest();
    xhr.open('POST', 'unenroll_student.php', true);
    xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
    xhr.onload = function() {
        if(xhr.status === 200 && xhr.responseText === 'success') {
            rowElem.remove();
        } else {
            alert('Failed to unenroll student from course.');
        }
    };
    xhr.send('course_id=' + encodeURIComponent(courseId) + '&student_id=' + encodeURIComponent(studentId));
}
function unenrollAllCourses() {
    if(!currentStudentId) return;
    if(confirm('Are you sure you want to unenroll this student from ALL courses?')) {
        var xhr = new XMLHttpRequest();
        xhr.open('POST', 'unenroll_all_student_courses.php', true);
        xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
        xhr.onload = function() {
            if(xhr.status === 200 && xhr.responseText === 'success') {
                var tableBody = document.getElementById('studentCoursesTable').getElementsByTagName('tbody')[0];
                tableBody.innerHTML = '';
                var tr = document.createElement('tr');
                var td = document.createElement('td');
                td.colSpan = 4;
                td.textContent = 'No enrolled courses found.';
                td.style.padding = '6px 8px';
                td.style.border = '1px solid #bbb';
                tr.appendChild(td);
                tableBody.appendChild(tr);
            } else {
                alert('Failed to unenroll student from all courses.');
            }
        };
        xhr.send('student_id=' + encodeURIComponent(currentStudentId));
    }
}
function deleteStudentCard(event, studentId) {
    event.stopPropagation();
    if (confirm('Are you sure you want to remove this student?')) {
        var xhr = new XMLHttpRequest();
        xhr.open('POST', 'delete_student.php', true);
        xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
        xhr.onload = function() {
            if(xhr.status === 200 && xhr.responseText === 'success') {
                event.target.closest('.card').remove();
            } else {
                alert('Failed to delete student.');
            }
        };
        xhr.send('student_id=' + encodeURIComponent(studentId));
    }
}
document.getElementById('search').addEventListener('input', function() {
    const query = this.value.trim().toLowerCase();
    document.querySelectorAll('.user-cards .card').forEach(function(card) {
        const name = card.querySelector('.name').textContent.toLowerCase();
        const uid = card.querySelector('.uid').textContent.toLowerCase();
        if (name.includes(query) || uid.includes(query)) {
            card.style.display = '';
        } else {
            card.style.display = 'none';
        }
    });
});