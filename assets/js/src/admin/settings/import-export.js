/* global CoursePress */

(function() {
    'use strict';

    CoursePress.Define( 'ImportExportSettings', function( $, doc, win ) {
        var CourseImport;

        CourseImport = CoursePress.View.extend({
            events: {
                'submit': 'uploadFile',
                'change [name="import"]': 'validateFile',
                'change [name]': 'updateModel'
            },
            initialize: function() {
                this.uploadModel = new CoursePress.Upload();
                this.model = new CoursePress.Request();
                this.render();
            },
            render: function() {
                this.errorContainer = this.$('.cp-alert-error');
            },
            uploadFile: function() {
                var valid = this.validateFile();

                if ( valid ) {
                    this.uploadModel.set( 'type', 'import_file' );
                    this.uploadModel.off( 'coursepress:success_import_file' );
                    this.uploadModel.on( 'coursepress:success_import_file', this.uploadCourse, this );
                    this.uploadModel.upload();
                }

                return false;
            },

            uploadCourse: function( data ) {
                this.model.set( 'action', 'import_course' );
                this.model.set( data );
                this.model.off( 'coursepress:success_import_course' );
                this.model.on( 'coursepress:successs_import_course', this.maybeContinue, this );
                this.model.save();
            },

            maybeContinue: function() {
            },

            validateFile: function() {
                var file = this.$('[name="import"]'),
                    value = file.val(),
                    file_type = value.substring( value.lastIndexOf('.') +1 );

                if ( 'json' !== file_type ) {
                    this.errorContainer.html( win._coursepress.text.invalid_file_type ).show();
                    this.$el.addClass('active');
                    return false;
                } else {
                    this.errorContainer.hide();
                    this.$el.removeClass('active');
                    return true;
                }
            }
        });

        return CoursePress.View.extend({
            template_id: 'coursepress-import-export-setting-tpl',
            el: $('#coursepress-setting-import-export'),
            initialize: function() {
                this.on( 'view_rendered', this.setUpForms, this );
                this.render();
            },
            setUpForms: function() {
                this.importForm = CourseImport.extend({el: this.$('#form-import') });
                this.importForm = new this.importForm();
                //this.exportForm = this.$('#form-export');
            }
        });
    });
})();