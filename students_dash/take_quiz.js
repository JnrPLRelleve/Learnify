document.addEventListener('DOMContentLoaded', function() {
    // JS logic for quiz navigation and submission
    const questions = window.questions || [];
    let currentQuestion = 0;
    let answers = [];
    const quizBlock = document.getElementById('quizQuestionBlock');
    const nextBtn = document.getElementById('nextBtn');
    const submitBtn = document.getElementById('submitBtn');
    const quizResultBox = document.getElementById('quizResultBox');

    function renderQuestion(idx) {
        if (!questions[idx]) return;
        const q = questions[idx];
        let html = `<div class="quiz-question">Q${idx+1}: ${q.question}</div>`;
        html += '<div class="quiz-options">';
        ['a','b','c','d'].forEach(function(opt) {
            const optKey = 'option_' + opt;
            html += `<label><input type="radio" name="option" value="${opt.toUpperCase()}" ${answers[idx]===opt.toUpperCase()?'checked':''}>${q[optKey]}</label>`;
        });
        html += '</div>';
        quizBlock.innerHTML = html;
        // Restore answer if already selected
        const radios = quizBlock.querySelectorAll('input[type="radio"]');
        radios.forEach(function(radio) {
            radio.addEventListener('change', function() {
                answers[idx] = radio.value;
            });
        });
    }
    window.nextQuestion = function() {
        if (typeof answers[currentQuestion] === 'undefined') {
            alert('Please select an answer.');
            return;
        }
        if (currentQuestion < questions.length-1) {
            currentQuestion++;
            renderQuestion(currentQuestion);
            if (currentQuestion === questions.length-1) {
                nextBtn.style.display = 'none';
                submitBtn.style.display = '';
            }
        }
    };
    window.submitQuiz = function() {
        if (typeof answers[currentQuestion] === 'undefined') {
            alert('Please select an answer.');
            return;
        }
        // Submit answers via AJAX
        fetch('submit_quiz.php', {
            method: 'POST',
            headers: {'Content-Type': 'application/json'},
            body: JSON.stringify({
                course_id: window.course_id,
                quiz_id: window.quiz_id,
                answers: answers
            })
        })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                quizResultBox.style.display = 'block';
                quizResultBox.innerHTML = `<div class="score">Score: ${data.score}</div>` +
                    `<div class="result-detail">Correct: ${data.correct} / ${data.total}</div>` +
                    `<div class="result-detail">${data.message || ''}</div>`;
                document.getElementById('quizForm').style.display = 'none';
            } else {
                alert(data.error || 'Submission failed.');
            }
        });
    };
    // Initial render
    if (questions.length > 0) {
        renderQuestion(0);
        nextBtn.style.display = questions.length > 1 ? '' : 'none';
        submitBtn.style.display = questions.length > 1 ? 'none' : '';
    }
});