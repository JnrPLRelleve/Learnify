var currentInstructorId = null;
function viewCourses(event, userId, username) {
    currentInstructorId = userId;
    event.stopPropagation();
    var xhr = new XMLHttpRequest();
    xhr.open('GET', 'get_instructor_courses.php?user_id=' + encodeURIComponent(userId), true);
    xhr.onload = function() {
        if (xhr.status === 200) {
            var courses = JSON.parse(xhr.responseText);
            var tableBody = document.getElementById('coursesTable').getElementsByTagName('tbody')[0];
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
                    var tdCreated = document.createElement('td');
                    tdCreated.textContent = course.created_at;
                    tdCreated.style.padding = '6px 8px';
                    tdCreated.style.border = '1px solid #bbb';
                    var tdDelete = document.createElement('td');
                    tdDelete.style.padding = '6px 8px';
                    tdDelete.style.border = '1px solid #bbb';
                    var delBtn = document.createElement('button');
                    delBtn.textContent = 'Delete';
                    delBtn.className = 'delete-course-btn';
                    delBtn.onclick = function(e) {
                        e.stopPropagation();
                        if(confirm('Are you sure you want to delete this course?')) {
                            deleteCourse(course.id, tr);
                        }
                    };
                    tdDelete.appendChild(delBtn);
                    tr.appendChild(tdTitle);
                    tr.appendChild(tdDesc);
                    tr.appendChild(tdCreated);
                    tr.appendChild(tdDelete);
                    tableBody.appendChild(tr);
                });
            } else {
                var tr = document.createElement('tr');
                var td = document.createElement('td');
                td.colSpan = 4;
                td.textContent = 'No courses found.';
                td.style.padding = '6px 8px';
                td.style.border = '1px solid #bbb';
                tr.appendChild(td);
                tableBody.appendChild(tr);
            }
            document.getElementById('coursesModalTitle').textContent = 'Courses by ' + username;
            document.getElementById('coursesModal').style.display = 'flex';
        }
    };
    xhr.send();
}
function deleteCourse(courseId, rowElem) {
    var xhr = new XMLHttpRequest();
    xhr.open('POST', 'delete_course.php', true);
    xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
    xhr.onload = function() {
        if(xhr.status === 200 && xhr.responseText === 'success') {
            rowElem.remove();
        } else {
            alert('Failed to delete course.');
        }
    };
    xhr.send('course_id=' + encodeURIComponent(courseId));
}
function closeCoursesModal() {
    document.getElementById('coursesModal').style.display = 'none';
}
function deleteInstructorCard(event, instructorId) {
    event.stopPropagation();
    if (confirm('Are you sure you want to remove this instructor?')) {
        var xhr = new XMLHttpRequest();
        xhr.open('POST', 'delete_instructor.php', true);
        xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
        xhr.onload = function() {
            if(xhr.status === 200 && xhr.responseText === 'success') {
                event.target.closest('.card').remove();
            } else {
                alert('Failed to delete instructor.');
            }
        };
        xhr.send('instructor_id=' + encodeURIComponent(instructorId));
    }
}
function deleteAllCourses() {
    if(!currentInstructorId) return;
    if(confirm('Are you sure you want to delete ALL courses for this instructor?')) {
        var xhr = new XMLHttpRequest();
        xhr.open('POST', 'delete_all_courses.php', true);
        xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
        xhr.onload = function() {
            if(xhr.status === 200 && xhr.responseText === 'success') {
                var tableBody = document.getElementById('coursesTable').getElementsByTagName('tbody')[0];
                tableBody.innerHTML = '';
                var tr = document.createElement('tr');
                var td = document.createElement('td');
                td.colSpan = 3;
                td.textContent = 'No courses found.';
                td.style.padding = '6px 8px';
                td.style.border = '1px solid #bbb';
                tr.appendChild(td);
                tableBody.appendChild(tr);
            } else {
                alert('Failed to delete all courses.');
            }
        };
        xhr.send('instructor_id=' + encodeURIComponent(currentInstructorId));
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