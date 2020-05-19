function toggleCellOutline () {
    if (typeof $.cookie === 'function' && $.cookie('app-frontend-view-mode')) {
        $('.dmstr-pages-invisible-frontend').hide();
    } else {
        $('.dmstr-pages-invisible-frontend').show();
    }
}

$(document).ready(toggleCellOutline);
$(document).on('cookieUpdate', toggleCellOutline);
