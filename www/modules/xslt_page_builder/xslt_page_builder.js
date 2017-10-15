$(document).ready(function(){
    alertIfAdBlocked();
    fixEmails();
    
    $('[data-toggle="tooltip"]').tooltip();
    if ($(document).height() <= $(window).height()) $('footer.footer').addClass('navbar-fixed-bottom');
    
    /* Items per page */
    if($.cookie('itemsPerPage')){
        $('#itemsPerPage').val($.cookie('itemsPerPage'));
    }

    $('#itemsPerPage').change(function() {
        var perPage = $('#itemsPerPage option:selected').val();
        $.cookie('itemsPerPage', perPage, { path: '/' });
        location.href = '?page=1';
    });
});

/* ===== Sticky Navbar ===== */
//$(window).load(function(){
//    $(".navbar").sticky({ topSpacing: 0 });
//});

