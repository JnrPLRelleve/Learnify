// JS for lessons.php
const courses = window.courses || [];
let currentPage = 0;
function renderCourseBox(page) {
    const list = document.getElementById('courseBoxList');
    list.innerHTML = '';
    if (courses.length === 0) {
        list.innerHTML = '<div class="empty-message">You are not enrolled in any courses.</div>';
        document.getElementById('coursePageInfo').textContent = '';
        document.getElementById('prevCourseBtn').style.display = 'none';
        document.getElementById('nextCourseBtn').style.display = 'none';
        document.getElementById('materialsList').innerHTML = '<div class="empty-message">Select a course to view its learning materials.</div>';
        return;
    }
    const course = courses[page];
    if (!course) return;
    const div = document.createElement('div');
    div.className = 'course-box';
    div.setAttribute('data-course-id', course.id);
    div.innerHTML =
        '<div class="course-title" style="font-weight:bold;font-size:1.1em;margin-bottom:8px;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;">' + course.title + '</div>' +
        '<div class="instructor-name" style="color:#555; margin-bottom:8px; font-size:0.97em;">Instructor: ' + (course.instructor_name ? course.instructor_name : '') + '</div>' +
        '<div class="progress-container" id="progress-bar-' + course.id + '" style="width:100px; height:10px; background:#e0e0e0; border-radius:6px; margin:6px 0 0 0;">' +
        '<div class="progress-bar" style="height:100%; width:0%; background:#007bff; border-radius:6px;"></div>' +
        '</div>' +
        '<span class="progress-label" id="progress-label-' + course.id + '" style="font-size:0.95em; color:#007bff;">0%</span>';
    div.onclick = function() {
        document.querySelectorAll('.course-box').forEach(function(b) { b.classList.remove('active'); });
        div.classList.add('active');
        loadMaterials(course.id);
        showWithdrawBtn(course.id);
    };
    list.appendChild(div);
    document.getElementById('coursePageInfo').textContent = (page+1) + ' / ' + courses.length;
    document.getElementById('prevCourseBtn').style.display = (page > 0) ? '' : 'none';
    document.getElementById('nextCourseBtn').style.display = (page < courses.length-1) ? '' : 'none';
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
function showWithdrawBtn(courseId) {
    document.querySelectorAll('.withdraw-btn').forEach(function(btn) {
        btn.style.display = 'none';
        btn.onclick = null;
    });
    var btn = document.querySelector('.withdraw-btn[data-course-id="' + courseId + '"]');
    if (btn) {
        btn.style.display = 'inline-block';
        btn.onclick = function() {
            if (confirm('Are you sure you want to withdraw from this course?')) {
                fetch('withdraw_course.php', {
                    method: 'POST',
                    headers: {'Content-Type': 'application/x-www-form-urlencoded'},
                    body: 'course_id=' + encodeURIComponent(courseId)
                })
                .then(res => res.json())
                .then(function(data) {
                    if (data.success) {
                        localStorage.removeItem('done_materials_' + courseId + '_student');
                        const idx = courses.findIndex(c => c.id == courseId);
                        if (idx !== -1) {
                            courses.splice(idx, 1);
                            if (currentPage >= courses.length) currentPage = Math.max(0, courses.length-1);
                            renderCourseBox(currentPage);
                        }
                        btn.style.display = 'none';
                        document.getElementById('materialsList').innerHTML = '<div class="empty-message">Select a course to view its learning materials.</div>';
                    } else {
                        alert(data.error || 'Failed to withdraw from course.');
                    }
                });
            }
        };
    }
}
document.addEventListener('DOMContentLoaded', function() {
    renderCourseBox(currentPage);
});
function groupByType(files) {
    const types = { pdf: [], video: [], image: [], document: [], other: [] };
    files.forEach(file => {
        let t = file.file_type ? file.file_type.toLowerCase() : 'other';
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
    const key = 'done_materials_' + courseId + '_student';
    try {
        return JSON.parse(localStorage.getItem(key)) || [];
    } catch {
        return [];
    }
}
function setDoneMaterials(courseId, arr) {
    const key = 'done_materials_' + courseId + '_student';
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
function loadMaterials(courseId) {
    fetch('get_course_materials.php?course_id=' + encodeURIComponent(courseId))
        .then(response => response.json())
        .then(data => {
            var list = document.getElementById('materialsList');
            list.innerHTML = '';
            list.style.maxHeight = '320px';
            list.style.overflowY = 'auto';
            if (data.length === 0) {
                list.innerHTML = '<div class="empty-message">No materials uploaded for this course.</div>';
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
                    const table = document.createElement('table');
                    table.className = 'materials-table';
                    table.style.marginBottom = '18px';
                    table.style.width = '100%';
                    const thead = document.createElement('thead');
                    thead.innerHTML = `<tr><th colspan="10" style="text-align:left;font-size:1.1em;">${folderNames[type]}</th></tr>`;
                    table.appendChild(thead);
                    const tbody = document.createElement('tbody');
                    grouped[type].forEach(file => {
                        allFiles.push(file.file_name);
                        const tr = document.createElement('tr');
                        const tdFile = document.createElement('td');
                        tdFile.innerHTML = `<a href="../uploads/${encodeURIComponent(file.file_name)}" target="_blank">${file.file_name}</a>`;
                        const tdBtn = document.createElement('td');
                        const doneBtn = document.createElement('button');
                        doneBtn.textContent = 'Done';
                        doneBtn.className = 'done-btn';
                        let doneMaterials = getDoneMaterials(courseId);
                        if (doneMaterials.includes(file.file_name)) {
                            tr.style.background = '#e6ffe6';
                            doneBtn.disabled = true;
                            doneBtn.textContent = 'Completed';
                        }
                        doneBtn.onclick = function() {
                            let doneMaterials = getDoneMaterials(courseId);
                            if (!doneMaterials.includes(file.file_name)) {
                                doneMaterials.push(file.file_name);
                                setDoneMaterials(courseId, doneMaterials);
                                tr.style.background = '#e6ffe6';
                                doneBtn.disabled = true;
                                doneBtn.textContent = 'Completed';
                                let completedCount = 0;
                                allFiles.forEach(f => { if (getDoneMaterials(courseId).includes(f)) completedCount++; });
                                let percent = allFiles.length > 0 ? Math.round((completedCount / allFiles.length) * 100) : 0;
                                if (percent > 100) percent = 100;
                                updateProgressBar(courseId, percent);
                                sendProgress(courseId, getDoneMaterials(courseId));
                            }
                        };
                        tdBtn.appendChild(doneBtn);
                        tr.appendChild(tdFile);
                        tr.appendChild(tdBtn);
                        tbody.appendChild(tr);
                    });
                    table.appendChild(tbody);
                    list.appendChild(table);
                }
            });
            if (!hasAny) {
                list.innerHTML = '<div class="empty-message">No materials uploaded for this course.</div>';
                updateProgressBar(courseId, 0);
            } else {
                let doneMaterials = getDoneMaterials(courseId);
                let completedCount = allFiles.filter(f => doneMaterials.includes(f)).length;
                let percent = allFiles.length > 0 ? Math.round((completedCount / allFiles.length) * 100) : 0;
                if (percent > 100) percent = 100;
                updateProgressBar(courseId, percent);
                sendProgress(courseId, doneMaterials.filter(f => allFiles.includes(f)));
            }
        });
}