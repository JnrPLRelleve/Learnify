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