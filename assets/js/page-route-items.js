$(function () {


  $('button.kv-create').on('click', function () {
    window.jsonEditors = [];
  })

  $(document).on('change', 'select[name="Tree[route]"]', function () {
    var self = $(this);
    $.post(self.data('request-url'), {value: self.val()}, function (resp, status) {
      if (status === 'success') {
        var schema = resp.schema;
        if (schema !== undefined && schema !== false) {
          var jsonEditorList = window.jsonEditors;
          var editorId = self.data('editor-id');
          var editor = jsonEditorList.find(editor => editor.element.id === "tree-request_params-container");

          if (editor !== undefined) {
            var element = editor.element;

            editor.destroy();

            var editorIndex = jsonEditorList.indexOf(editor);
            jsonEditorList[editorIndex] = new JSONEditor(element, {
              schema: JSON.parse(schema),
              theme: "bootstrap3",
              disable_collapse: true,
              disable_edit_json: true,
              disable_properties: true
            });

            $('input[name="Tree[request_params]"]').val(JSON.stringify(jsonEditorList[editorIndex].getValue()));

          } else {
            console.error('Editor not found.');
          }


        }
      } else {
        console.error('Something went wrong.');
      }
    });
  });

});
