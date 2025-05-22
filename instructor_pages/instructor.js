document.addEventListener('DOMContentLoaded', function() {
    const courseItems = document.querySelectorAll('.course_item');
    const modal = document.getElementById('courseDetailModal');
    const modalTitle = document.getElementById('modalTitle');
    const modalSection = document.getElementById('modalSection');
    const modalDescription = document.getElementById('modalDescription');
    const modalCreatedAt = document.getElementById('modalCreatedAt');
    const closeModalBtn = document.getElementById('closeModalBtn');

    courseItems.forEach(function(item) {
        item.addEventListener('click', function(e) {
            
            if (e.target.tagName === 'BUTTON') return;
            modalTitle.textContent = item.getAttribute('data-title');
            modalSection.textContent = item.getAttribute('data-section');
            modalDescription.textContent = item.getAttribute('data-description');
            modalCreatedAt.textContent = item.getAttribute('data-created_at');
            modal.classList.add('show');
        });
    });
    closeModalBtn.addEventListener('click', function() {
        modal.classList.remove('show');
    });
    window.addEventListener('click', function(event) {
        if (event.target === modal) {
            modal.classList.remove('show');
        }
    });
});
        