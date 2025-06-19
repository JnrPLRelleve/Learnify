document.addEventListener('DOMContentLoaded', function() {
    // Filter by course
    const courseFilter = document.getElementById('courseFilter');
    if (courseFilter) {
        courseFilter.addEventListener('change', function() {
            const val = this.value;
            document.querySelectorAll('.course-block').forEach(function(block) {
                if (val === 'all' || block.id === val) {
                    block.style.display = '';
                } else {
                    block.style.display = 'none';
                }
            });
        });
    }
    // Search by student name
    const studentSearch = document.getElementById('studentSearch');
    if (studentSearch) {
        studentSearch.addEventListener('input', function() {
            const searchVal = this.value.trim().toLowerCase();
            document.querySelectorAll('.course-block').forEach(function(block) {
                let anyVisible = false;
                block.querySelectorAll('tbody tr').forEach(function(row) {
                    const nameCell = row.querySelector('td');
                    if (nameCell && nameCell.textContent.toLowerCase().includes(searchVal)) {
                        row.style.display = '';
                        anyVisible = true;
                    } else if (row.querySelector('td[colspan]')) {
                        // No students row
                        row.style.display = '';
                    } else {
                        row.style.display = 'none';
                    }
                });
                // Hide course block if no students match
                block.style.display = anyVisible || searchVal === '' ? '' : 'none';
            });
        });
    }
});