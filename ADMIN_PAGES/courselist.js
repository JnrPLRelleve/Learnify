const userCards = document.querySelectorAll('.card');
const modal = document.getElementById('courseModal');
const modalOverlay = document.getElementById('modalOverlay');

userCards.forEach(card => {
    card.addEventListener('click', () => {
        modal.style.display = 'block';
        modalOverlay.style.display = 'block';
    });
});

function closeModal() {
    modal.style.display = 'none';
    modalOverlay.style.display = 'none';
}

function deleteCourse() {
    alert('Course deleted!');
    closeModal();
}
function viewCourse(event, btn) {
    event.stopPropagation();
    const card = btn.closest('.card');
    document.getElementById('modalTitle').textContent = 'Title: ' + card.getAttribute('data-title');
    document.getElementById('modalDescription').textContent = card.getAttribute('data-description');
    document.getElementById('modalInstructor').textContent = card.getAttribute('data-instructor');
    document.getElementById('modalCreated').textContent = card.getAttribute('data-created');
    document.getElementById('courseModal').style.display = 'flex';
}
function closeModal() {
    document.getElementById('courseModal').style.display = 'none';
}
function deleteCourseCard(event, courseId) {
    event.stopPropagation();
    if (confirm('Are you sure you want to remove this course?')) {
        var xhr = new XMLHttpRequest();
        xhr.open('POST', 'delete_course.php', true);
        xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
        xhr.onload = function() {
            if(xhr.status === 200 && xhr.responseText === 'success') {
                event.target.closest('.card').remove();
            } else {
                alert('Failed to delete course.');
            }
        };
        xhr.send('course_id=' + encodeURIComponent(courseId));
    }
}
// Search functionality
document.getElementById('search').addEventListener('input', function() {
    const query = this.value.trim().toLowerCase();
    document.querySelectorAll('.user-cards .card').forEach(function(card) {
        const title = card.querySelector('.name').textContent.toLowerCase();
        const instructor = card.querySelector('.instructor').textContent.toLowerCase();
        const courseId = card.querySelector('.uid').textContent.toLowerCase();
        if (
            title.includes(query) ||
            instructor.includes(query) ||
            courseId.includes(query)
        ) {
            card.style.display = '';
        } else {
            card.style.display = 'none';
        }
    });
});