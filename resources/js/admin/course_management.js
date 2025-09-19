document.addEventListener('DOMContentLoaded', function() {
    // Search functionality
    const searchInput = document.querySelector('.search-box input');
    const tableRows = document.querySelectorAll('.course-table tbody tr');

    if (searchInput) {
        searchInput.addEventListener('input', function() {
            const searchTerm = this.value.toLowerCase();
            tableRows.forEach(row => {
                const courseName = row.cells[0].textContent.toLowerCase();
                const instructor = row.cells[1].textContent.toLowerCase();
                if (courseName.includes(searchTerm) || instructor.includes(searchTerm)) {
                    row.style.display = '';
                } else {
                    row.style.display = 'none';
                }
            });
        });
    }

    // Action button interactions
    const actionButtons = document.querySelectorAll('.btn-small');
    actionButtons.forEach(button => {
        button.addEventListener('click', function(e) {
            e.preventDefault();
            const action = this.textContent;
            const courseName = this.closest('tr').cells[0].textContent;
            alert(`${action} action clicked for: ${courseName}`);
        });
    });

    // Pagination
    const paginationButtons = document.querySelectorAll('.pagination button');
    paginationButtons.forEach(button => {
        button.addEventListener('click', function() {
            paginationButtons.forEach(btn => btn.classList.remove('active'));
            if (!isNaN(this.textContent)) {
                this.classList.add('active');
            }
        });
    });

    // Create New Course button
    const createButton = document.querySelector('.btn-primary');
    if (createButton) {
        createButton.addEventListener('click', function() {
            alert('Create New Course functionality would open here');
        });
    }
});
