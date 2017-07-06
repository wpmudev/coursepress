/* global CoursePress */

(function() {
    'use strict';

    CoursePress.Define( 'ImportExportSettings', function( $, doc, win ) {
        return CoursePress.View.extend({
            template_id: 'coursepress-import-export-setting-tpl',
            el: $('#coursepress-setting-import-export'),
            events: {
                'submit form': 'uploadFile',
                'change [name="file"]': 'validateUploadFile'
            },

            uploadFile: function() {
            },

            validateUploadFile: function( ev ) {
                var file = this.$( ev.currentTarget ),
                    value = file.val(),
                    file_type = value.substring( value.lastIndexOf('.')+1 ),
                    form = file.parent('form'),
                    errorContainer = form.find( '.cp-alert-error' );

                if ( 'json' !== file_type ) {
                    errorContainer.html( win._coursepress.text.invalid_file_type ).show();
                    form.addClass('active');
                } else {
                    errorContainer.hide();
                    form.removeClass('active');
                }
            }
        });
    });
})();