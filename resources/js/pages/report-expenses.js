$(function () {
    const table = $('#expense-report-table');
    const ajaxUrl = table.data('url');

    // Set default dates
    const today = new Date();
    const firstDayOfMonth = new Date(today.getFullYear(), today.getMonth(), 1);

    // Only set default values if inputs are empty
    if (!$('#filter-start-date').val()) {
        $('#filter-start-date').val(firstDayOfMonth.toISOString().split('T')[0]);
    }
    if (!$('#filter-end-date').val()) {
        $('#filter-end-date').val(today.toISOString().split('T')[0]);
    }

    const dataTable = table.DataTable({
        processing: true,
        serverSide: true,
        responsive: true,
        ajax: {
            url: ajaxUrl,
            data: function (d) {
                d.start_date = $('#filter-start-date').val();
                d.end_date = $('#filter-end-date').val();
                d.area_id = $('#filter-area').val() || 'all'; // Handle if element doesn't exist
                d.category_id = $('#filter-category').val();
            }
        },
        columns: [
            { data: 'date', name: 'date' },
            { data: 'name', name: 'name' },
            { data: 'category_name', name: 'category_name' },
            { data: 'user_name', name: 'user.name' },
            { data: 'amount', name: 'amount', className: 'text-end' }
        ],
        drawCallback: function (settings) {
            const summary = settings.json.summary;
            if (summary) {
                $('#summary-total-expenses').text(summary.total_expenses);
                $('#summary-expense-count').text(summary.expense_count);
                $('#summary-most-expensive-category').text(summary.most_expensive_category);
            }
        }
    });

    $('#apply-filters').on('click', function () {
        dataTable.ajax.reload();
    });
});
