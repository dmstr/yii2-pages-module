$(document).on('ready cookieUpdate',function () {

    // TODO: check value
    if (typeof $.cookie === 'function' && $.cookie('app-frontend-view-mode')) {
        $('.dmstr-pages-invisible-frontend').hide();
    } else {
        $('.dmstr-pages-invisible-frontend').show();
    }

});
