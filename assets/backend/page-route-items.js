$(function () {

  // var viewInput = $('.field-tree-view');

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
          var editor = jsonEditorList.find(function (editor) {
            return editor.element.id === "tree-request_params-container";
          });

          if (editor !== undefined) {
            var element = editor.element;
            var editorIndex = jsonEditorList.indexOf(editor);
            // we only want to change schema, so get defined options from current editor.
            var editorOptions = editor.options;
            editorOptions.schema = JSON.parse(schema);
            // recreate Editor
            editor.destroy();
            jsonEditorList[editorIndex] = new JSONEditor(element, editorOptions);

            // inital update of value
            $('input[name="Tree[request_params]"]').val(JSON.stringify(jsonEditorList[editorIndex].getValue()));

            // update/init change callback for newly inserted editor which update the input value
            jsonEditorList[editorIndex].on('change', function () {
              $('input[name="Tree[request_params]"]').val(JSON.stringify(jsonEditorList[editorIndex].getValue()));
            });

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
