var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'))
var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
    return new bootstrap.Tooltip(tooltipTriggerEl)
});

document.addEventListener('DOMContentLoaded', (event) => {
    var table = document.querySelector('#table');
    $(table).bootstrapTable({
        onPostBody: function () {
            $('[data-toggle="tooltip"]').tooltip();
        }
    });
});
