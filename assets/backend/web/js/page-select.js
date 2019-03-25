$(function () {

  $(document).on("click",".kv-tree li[data-key]", function (e) {
    e.stopPropagation();
    var pageId = $(this).data("key");
    var searchParams = new URLSearchParams(window.location.search);
    searchParams.set("pageId", pageId);
    var newUrl = window.location.origin + window.location.pathname + "?" + searchParams.toString();

    history.pushState({}, null, newUrl);
  });

});