/* global CoursePress, _coursepress */

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

            uploadFile: function( ev ) {
                var valid = this.validateFile();
                var options = $('[type=checkbox]', $(ev.currentTarget).closest( 'form' ) );
                var uploadModel = this.uploadModel;
                if ( valid ) {
                    /**
                     * Show popup
                     */
                    new CoursePress.PopUp({
                        type: 'info',
                        message: win._coursepress.text.importing_courses
                    });
                    /**
                     * Import options
                     */
                    options.each( function() {
                        uploadModel.set( $(this).attr('name'), $(this).is( ':checked' ) );
                    });
                    uploadModel.set( 'type', 'import_file' );
                    uploadModel.on( 'coursepress:success_import_file', this.uploadCourseSuccess, this );
                    uploadModel.on( 'coursepress:error_import_file', this.uploadCourseError, this );
                    uploadModel.upload();
                }
                return false;
            },

            uploadCourseSuccess: function() {
                /**
                 * reset form
                 */
                $('#form-import input[type=checkbox]').removeAttr('checked');
                $('#form-import input[type=file]').val('');
                /**
                 * remove popup
                 */
                $('.coursepress-popup').detach();
            },

            uploadCourseError: function() {
                this.uploadCourseSuccess();
            },

            uploadCourse: function( data ) {
                this.model.set( 'action', 'import_course' );
                this.model.set( data );
                this.model.off( 'coursepress:success_import_course' );
                this.model.on( 'coursepress:successs_import_course', this.maybeContinue, this );
                this.model.save({
                    wait: true
                });
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

            events: {
                'click #coursepress-export-button': 'exportCourses',
                'change input[name="coursepress[all]"]': 'switchAll',
                'change label.course input[type=checkbox]': 'maybeTurnOffAll',
            },

            initialize: function() {
                this.on( 'view_rendered', this.setUpForms, this );
                this.render();
            },

            setUpForms: function() {
                this.importForm = CourseImport.extend({el: this.$('#form-import') });
                this.importForm = new this.importForm();
                this.exportForm = this.$('#form-export');
                this.courses = this.$( 'label.course input[type=checkbox]', this.exportForm );
                this.allCourses = this.$( 'input[name="coursepress[all]"]', this.exportForm );
            },

            switchAll: function( ev ) {
                this.courses.each(function() {
                    this.checked = $(ev.currentTarget).is(':checked');
                });
            },

            maybeTurnOffAll: function( ev ) {
                if ( ! $(ev.currentTarget).is(':checked') ) {
                    this.allCourses.each( function() {
                        this.checked = false;
                    });
                }
            },

            exportCourses: function() {
                var checked = this.$( 'label.course input[type=checkbox]:checked', this.exportForm );
                if ( 0 === checked.length ) {
                    window.alert( _coursepress.text.export.no_items );
                    return false;
                }
                this.exportForm.submit();
                return false;
            }
        });
    });
})();
