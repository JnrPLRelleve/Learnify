// Open modal and set quiz name
function openSetToCourseModal(quizName) {
    document.getElementById('modalQuizName').value = quizName;
    document.getElementById('setToCourseModal').style.display = 'flex';
}
function closeSetToCourseModal() {
    document.getElementById('setToCourseModal').style.display = 'none';
}
document.addEventListener('DOMContentLoaded', function() {
    document.querySelectorAll('.set-to-course-btn').forEach(function(btn) {
        btn.addEventListener('click', function() {
            openSetToCourseModal(this.getAttribute('data-quiz'));
        });
    });
    // Cancel button styling
    var cancelBtn = document.querySelector('#setToCourseModal .delete-btn');
    if (cancelBtn) {
        cancelBtn.classList.add('cancel-btn');
    }
    // Success/error alerts
    if (window.set_success) {
        alert('Quiz successfully set to course!');
    } else if (window.set_error) {
        alert(window.set_error);
    }
});