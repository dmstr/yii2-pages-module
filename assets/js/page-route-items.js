$(function () {
  $(document).on('change','select[name="Tree[route]"]', function () {
    var self = $(this);
    $.post(self.data('request-url'), {value: self.val()}, function (resp, status) {
      if (status === 'success') {
        var schema = resp.schema;
        if (schema !== undefined && schema !== false) {
          var editorId = self.data('editor-id');
          var editor = window[editorId];
          var element = editor.element;

          window[editorId].destroy();

          window[editorId] = new JSONEditor(element, {
            schema: JSON.parse(schema),
            theme: "bootstrap3",
            disable_collapse: true,
            disable_edit_json: true,
            disable_properties: true
          });
        }
      } else {
        console.error('Something went wrong');
      }
    });
  });
});
