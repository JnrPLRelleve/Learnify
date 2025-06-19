// Quiz builder JS moved from instructor_quiz.php
let quizQuestions = [];
let setAttempts = 1;
let currentPage = 1;
const questionsPerPage = 5;

function addQuestion() {
    const question = document.getElementById('questionInput').value.trim();
    const optionA = document.getElementById('optionA').value.trim();
    const optionB = document.getElementById('optionB').value.trim();
    const optionC = document.getElementById('optionC').value.trim();
    const optionD = document.getElementById('optionD').value.trim();
    const correct = document.getElementById('correctOption').value;
    if (!question || !optionA || !optionB || !optionC || !optionD) {
        alert('Please fill in all fields.');
        return;
    }
    quizQuestions.push({
        question,
        options: { A: optionA, B: optionB, C: optionC, D: optionD },
        correct
    });
    document.getElementById('questionInput').value = '';
    document.getElementById('optionA').value = '';
    document.getElementById('optionB').value = '';
    document.getElementById('optionC').value = '';
    document.getElementById('optionD').value = '';
    document.getElementById('correctOption').value = 'A';
    renderQuizList();
    document.getElementById('saveQuizBtn').style.display = quizQuestions.length > 0 ? 'block' : 'none';
}

function renderQuizList() {
    const quizList = document.getElementById('quizList');
    quizList.innerHTML = '';
    const start = (currentPage - 1) * questionsPerPage;
    const end = start + questionsPerPage;
    const pageData = quizQuestions.slice(start, end);
    pageData.forEach((q, idx) => {
        const realIdx = start + idx;
        quizList.innerHTML += `
            <tr class='question-row'>
                <td colspan='10'>
                    <div class='question improved-question'>${realIdx+1}. ${q.question}</div>
                    <div class='options improved-options'>
                        <div><span class='option-label'>A:</span> ${q.options.A}</div>
                        <div><span class='option-label'>B:</span> ${q.options.B}</div>
                        <div><span class='option-label'>C:</span> ${q.options.C}</div>
                        <div><span class='option-label'>D:</span> ${q.options.D}</div>
                    </div>
                    <div class='correct improved-correct'><b>Correct:</b> <span>${q.correct}</span></div>
                    <button class='remove-btn improved-remove' onclick='removeQuestion(${realIdx})'>Remove</button>
                </td>
            </tr>
        `;
    });
    document.getElementById('saveQuizBtn').style.display = quizQuestions.length > 0 ? 'inline-block' : 'none';
    updatePagination();
}

function prevPage() {
    if (currentPage > 1) {
        currentPage--;
        renderQuizList();
    }
}
function nextPage() {
    if (currentPage * questionsPerPage < quizQuestions.length) {
        currentPage++;
        renderQuizList();
    }
}
function editQuestion(idx) {
    const q = quizQuestions[idx];
    document.getElementById('questionInput').value = q.question;
    document.getElementById('optionA').value = q.options.A;
    document.getElementById('optionB').value = q.options.B;
    document.getElementById('optionC').value = q.options.C;
    document.getElementById('optionD').value = q.options.D;
    document.getElementById('correctOption').value = q.correct;
    quizQuestions.splice(idx, 1);
    renderQuizList();
    document.getElementById('saveQuizBtn').style.display = quizQuestions.length > 0 ? 'block' : 'none';
}
function deleteQuestion(idx) {
    quizQuestions.splice(idx, 1);
    renderQuizList();
    document.getElementById('saveQuizBtn').style.display = quizQuestions.length > 0 ? 'block' : 'none';
}
function saveQuiz() {
    const quizName = document.getElementById('quizNameInput').value.trim();
    if (!quizName) {
        alert('Please enter a quiz name.');
        return;
    }
    if (quizQuestions.length === 0) {
        alert('Add at least one question.');
        return;
    }
    fetch('instructor_quiz.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: 'quizData=' + encodeURIComponent(JSON.stringify(quizQuestions)) + '&quizName=' + encodeURIComponent(quizName) + '&setAttempts=' + setAttempts
    })
    .then(res => res.json())
    .then(data => {
        document.getElementById('saveResult').innerText = data.message;
        if (data.success) {
            quizQuestions = [];
            renderQuizList();
            document.getElementById('quizNameInput').value = '';
            document.getElementById('saveQuizBtn').style.display = 'none';
        }
    });
}
function updatePagination() {
    document.getElementById('prevBtn').disabled = currentPage === 1;
    document.getElementById('nextBtn').disabled = currentPage * questionsPerPage >= quizQuestions.length;
}
document.addEventListener('DOMContentLoaded', function() {
    renderQuizList();
    document.getElementById('setAttemptsBtn').onclick = function() {
        let val = prompt('Enter allowed number of attempts for this quiz:', setAttempts);
        if (val !== null) {
            val = parseInt(val);
            if (!isNaN(val) && val > 0) {
                setAttempts = val;
                document.getElementById('attemptsDisplay').textContent = `Attempts: ${setAttempts}`;
            } else {
                alert('Please enter a valid positive number.');
            }
        }
    };
    document.getElementById('attemptsDisplay').textContent = `Attempts: ${setAttempts}`;
});
