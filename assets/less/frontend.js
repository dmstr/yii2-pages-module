$(document).on('ready cookieUpdate',function () {

    // TODO: check value
    if ($.cookie('app-frontend-view-mode')) {
        $('.dmstr-pages-invisible-frontend').hide();
    } else {
        $('.dmstr-pages-invisible-frontend').show();
    }

});
