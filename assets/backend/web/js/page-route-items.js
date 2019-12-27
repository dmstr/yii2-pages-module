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
          var editor = jsonEditorList.find(function (editor) {
            return editor.element.id === "tree-request_params-container";
          });

          if (editor !== undefined) {
            var element = editor.element;

            editor.destroy();

            var editorIndex = jsonEditorList.indexOf(editor);
            jsonEditorList[editorIndex] = new JSONEditor(element, {
              schema: JSON.parse(schema),
              theme: "bootstrap3",
              ajax: true,
              disable_collapse: true
            });

            // inital update of value
            $('input[name="Tree[request_params]"]').val(JSON.stringify(jsonEditorList[editorIndex].getValue()));

            // update of value for newly inserted editor
            $('#tree-request_params-container').on('change', function () {
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
