document.addEventListener("DOMContentLoaded", () => {
    const addNewCourseBtn = document.querySelector("#addCourseBtn");
    const modal = document.querySelector("#courseModal");
    const closeModalBtn = document.querySelector("#closeModalBtn");
    const enrollBtn = document.querySelector("#enrollBtn");
    const courseOptions = document.querySelectorAll(".course_option");
    const courseDetailsModal = document.querySelector("#courseDetailsModal");
    const closeDetailsModalBtn = document.querySelector("#closeDetailsModalBtn");
    const courseDetailsTitle = document.querySelector("#courseDetailsTitle");
    const courseDetailsText = document.querySelector("#courseDetailsText");
    let selectedCourseName = "";
    let selectedInstructorName = "";
    let currentCourseCard;

    addNewCourseBtn.addEventListener("click", () => {
        modal.style.display = "flex";
    });

    closeModalBtn.addEventListener("click", () => {
        modal.style.display = "none";
    });

    courseOptions.forEach(button => {
        button.addEventListener("click", () => {
            selectedCourseName = button.getAttribute("data-course-name");
            selectedInstructorName = button.getAttribute("data-instructor-name");
            courseOptions.forEach(btn => btn.classList.remove("selected"));
            button.classList.add("selected");
        });
    });

    enrollBtn.addEventListener("click", () => {
        if (selectedCourseName) {
            const newCourseCard = document.createElement("div");
            newCourseCard.classList.add("course_card");
            newCourseCard.innerHTML = `
                <div class="course_icon"></div>
                <h3>${selectedCourseName}</h3>
                <footer style="margin-left: auto;">${selectedInstructorName}</footer>
            `;
            newCourseCard.addEventListener("click", () => {
                courseDetailsTitle.textContent = selectedCourseName;
                currentCourseCard = newCourseCard;
                courseDetailsModal.style.display = "flex";
            });
            document.querySelector("#coursesList").insertBefore(newCourseCard, addNewCourseBtn);
            modal.style.display = "none";
        } else {
            alert("Please select a course to enroll.");
        }
    });

    closeDetailsModalBtn.addEventListener("click", () => {
        courseDetailsModal.style.display = "none";
    });

    document.querySelector("#unenrollBtn").addEventListener("click", () => {
        if (currentCourseCard) {
            currentCourseCard.remove();
            alert("You have unenrolled from the course.");
            courseDetailsModal.style.display = "none";
        }
    });

    document.querySelector("#viewMaterialsBtn").addEventListener("click", () => {
        alert("Viewing materials for " + courseDetailsTitle.textContent);
    });
});