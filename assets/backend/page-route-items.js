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
            var editorIndex = jsonEditorList.indexOf(editor);
            // we only want to change schema, so get defined options from current editor.
            var editorOptions = editor.options;
            editorOptions.schema = JSON.parse(schema);
            // reset startval && make all "new" options required in schema
            editorOptions.startval = {};
            editorOptions.required_by_default = true;
            // recreate Editor
            editor.destroy();
            // get current editor element once again, as the "old" element from within editor can be stale
            // if treeview has changed the .kv-detail-container
            var current_element = document.getElementById('tree-request_params-container');
            // clear already created forms from prev. container...
            current_element.innerHTML = '';
            // init new editor on current element
            jsonEditorList[editorIndex] = new JSONEditor(current_element, editorOptions);
            // jsonEditorList[editorIndex] = new JSONEditor(element, editorOptions);
            jsonEditorList[editorIndex].on('ready', function () {
              // inital update of hidden form input value
              $('input[name="Tree[request_params]"]').val(JSON.stringify(this.getValue()));
            });
            // update/init change callback for newly inserted editor which update the input value
            jsonEditorList[editorIndex].on('change', function () {
              $('input[name="Tree[request_params]"]').val(JSON.stringify(this.getValue()));
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
