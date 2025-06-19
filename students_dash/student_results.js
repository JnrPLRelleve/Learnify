
const pageSize = 5;
let currentPage = 1;
function renderResultsPage(page) {
    const start = (page-1)*pageSize;
    const end = start+pageSize;
    const pageResults = results.slice(start, end);
    let html = '';
    pageResults.forEach(function(r) {
        html += `<div class="result-row">
            <div class="quiz-name">Quiz: ${r.saved_quiz_name}</div>
            <div class="course-title">Course: ${r.course_title}</div>
            <div class="score">Score: ${r.score}</div>
            <div class="submitted">Submitted: ${r.submitted_at}</div>
        </div>`;
    });
    document.getElementById('results-list').innerHTML = html;
    document.getElementById('pageInfo').textContent = `Page ${currentPage} of ${Math.ceil(results.length/pageSize)}`;
    document.getElementById('prevPage').disabled = (currentPage === 1);
    document.getElementById('nextPage').disabled = (currentPage === Math.ceil(results.length/pageSize));
}
document.getElementById('prevPage').onclick = function() {
    if (currentPage > 1) { currentPage--; renderResultsPage(currentPage); }
};
document.getElementById('nextPage').onclick = function() {
    if (currentPage < Math.ceil(results.length/pageSize)) { currentPage++; renderResultsPage(currentPage); }
};
renderResultsPage(currentPage);