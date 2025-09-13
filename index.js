jQuery(document).ready(function ($) {
    $('#jaringlead-table').DataTable({
        processing: true,
        serverSide: false,
        ajax: {
            url: '/wp-json/jaringlead/v1/leads',
            dataSrc: 'data',
        },
        columns: [
            { data: 'name' },
            { data: 'phone' },
            { data: 'created_at' },
        ]
    });
});