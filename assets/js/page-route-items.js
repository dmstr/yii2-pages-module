$(function () {
  $(document).on('change','select[name="Tree[route]"]', function () {
    var self = $(this);
    $.post(self.data('request-url'), {value: self.val()}, function (resp, status) {
      if (status === 'success') {
        var schema = resp.schema;
        if (schema !== undefined) {
          var editorId = self.data('editor-id');
          var editor = window[editorId];

          var element = editor.element;
          var options = editor.options;

          window[editorId].destroy();

          options.schema = JSON.parse(schema);

          window[editorId] = new JSONEditor(element, {schema: JSON.parse(schema)});
        }
      } else {
        console.error('Something went wrong');
      }
    });
  });
});
