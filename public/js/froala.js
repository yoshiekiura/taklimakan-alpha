
    $(function() 
    { 
        // Froala Toolbar for different screens : https://www.froala.com/wysiwyg-editor/examples/toolbar-buttons
        // More : https://www.froala.com/wysiwyg-editor/docs/options#toolbarButtons

        $('.froala-editor textarea').froalaEditor({
            // Default Button Set
            // toolbarButtons: ['fullscreen', 'bold', 'italic', 'underline', 'strikeThrough', 'subscript', 'superscript', '|', 'fontFamily', 'fontSize', 'color', 'inlineStyle', 'paragraphStyle', '|', 'paragraphFormat', 'align', 'formatOL', 'formatUL', 'outdent', 'indent', 'quote', '-', 'insertLink', 'insertImage', 'insertVideo', 'embedly', 'insertFile', 'insertTable', '|', 'emoticons', 'specialCharacters', 'insertHR', 'selectAll', 'clearFormatting', '|', 'print', 'spellChecker', 'help', 'html', '|', 'undo', 'redo']
            toolbarButtons: ['fullscreen', 'bold', 'italic', 'underline', 'strikeThrough', 'subscript', 'superscript', '|', 'fontFamily', 'fontSize', 'color', 'inlineStyle', 'paragraphStyle', '|', 'paragraphFormat', 'align', 'formatOL', 'formatUL', 'outdent', 'indent', 'quote', '-', 'insertLink', 'insertImage', 'insertVideo', 'embedly', 'insertFile', 'insertTable', '|', 'emoticons', 'specialCharacters', 'insertHR', 'selectAll', 'clearFormatting', '|', 'print', 'spellChecker', 'help', 'html', '|', 'undo', 'redo'],
            //toolbarButtons: ['undo', 'redo' , '|', 'bold', 'italic', 'underline', 'strikeThrough', 'subscript', 'superscript', 'outdent', 'indent', 'clearFormatting', 'insertTable', 'html'],
            toolbarButtonsXS: ['undo', 'redo' , '-', 'bold', 'italic', 'underline'],
//                }) 
    //});

    // $(document).ready(function () { $('textarea').froalaEditor() });

  //$(function() {

//    $('.selector')
  //    .froalaeditor({

            // https://www.froala.com/wysiwyg-editor/docs/concepts/image/upload

            // Set the image upload parameter.
            imageUploadParam: 'file',
 
            // Set the image upload URL.
            imageUploadURL: '/froala/image',
 
            // Additional upload params.
            imageUploadParams: {id: 'my_editor'},
 
            // Set request type.
            imageUploadMethod: 'POST',
 
            // Set max image size to 2MB.
            imageMaxSize: 2 * 1024 * 1024,
 
            // Allow to upload PNG and JPG.
            imageAllowedTypes: ['jpeg', 'jpg', 'png', 'gif', 'svg', 'webp']
        })
        .on('froalaEditor.image.beforeUpload', function (e, editor, images) {
            // Return false if you want to stop the image upload.
        })
        .on('froalaEditor.image.uploaded', function (e, editor, response) {
            // Image was uploaded to the server.
        })
        .on('froalaEditor.image.inserted', function (e, editor, $img, response) {
            // Image was inserted in the editor.
        })
        .on('froalaEditor.image.replaced', function (e, editor, $img, response) {
            // Image was replaced in the editor.
        })
        .on('froalaEditor.image.error', function (e, editor, error, response) {
            
            // Bad link.
            if (error.code == 1) { }
 
            // No link in upload response.
            else if (error.code == 2) { }
 
            // Error during image upload.
            else if (error.code == 3) { }
 
            // Parsing response failed.
            else if (error.code == 4) { }
 
            // Image too text-large.
            else if (error.code == 5) { }
 
            // Invalid image type.
            else if (error.code == 6) { }
 
            // Image can be uploaded only to same domain in IE 8 and IE 9.
            else if (error.code == 7) { }
 
            // Response contains the original server response to the request if available.

          });
      });


/*
    
    // Froala Toolbar for different screens : https://www.froala.com/wysiwyg-editor/examples/toolbar-buttons
    // More : https://www.froala.com/wysiwyg-editor/docs/options#toolbarButtons
    
    $(document).ready(function () {
     
         document.querySelectorAll( '.froala-editor textarea' ) {


            //$(function() 
            //{ 

                $('textarea').froalaEditor({

                    // Default / Full Button Set
                    // toolbarButtons: ['fullscreen', 'bold', 'italic', 'underline', 'strikeThrough', 'subscript', 'superscript', '|', 'fontFamily', 'fontSize', 'color', 'inlineStyle', 'paragraphStyle', '|', 'paragraphFormat', 'align', 'formatOL', 'formatUL', 'outdent', 'indent', 'quote', '-', 'insertLink', 'insertImage', 'insertVideo', 'embedly', 'insertFile', 'insertTable', '|', 'emoticons', 'specialCharacters', 'insertHR', 'selectAll', 'clearFormatting', '|', 'print', 'spellChecker', 'help', 'html', '|', 'undo', 'redo']
            
                    toolbarButtons: ['fullscreen', 'bold', 'italic', 'underline', 'strikeThrough', 'subscript', 'superscript', '|', 'fontFamily', 'fontSize', 'color', 'inlineStyle', 'paragraphStyle', '|', 'paragraphFormat', 'align', 'formatOL', 'formatUL', 'outdent', 'indent', 'quote', '-', 'insertLink', 'insertImage', 'insertVideo', 'embedly', 'insertFile', 'insertTable', '|', 'emoticons', 'specialCharacters', 'insertHR', 'selectAll', 'clearFormatting', '|', 'print', 'spellChecker', 'help', 'html', '|', 'undo', 'redo'],
                    //toolbarButtons: ['undo', 'redo' , '|', 'bold', 'italic', 'underline', 'strikeThrough', 'subscript', 'superscript', 'outdent', 'indent', 'clearFormatting', 'insertTable', 'html'],
                    toolbarButtonsXS: ['undo', 'redo' , '-', 'bold', 'italic', 'underline']

                }) 
            //})

        }              
    });

*/