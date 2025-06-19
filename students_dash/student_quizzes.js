document.getElementById('courseSearch').addEventListener('input', function() {
    const searchTerm = this.value.trim().toLowerCase();
    const courseSections = document.querySelectorAll('.quiz-course-section');
    courseSections.forEach(function(section) {
        const courseTitle = section.querySelector('h2')?.textContent.toLowerCase() || '';
        if (courseTitle.includes(searchTerm)) {
            section.style.display = '';
        } else {
            section.style.display = 'none';
        }
    });
});