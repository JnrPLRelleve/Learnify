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