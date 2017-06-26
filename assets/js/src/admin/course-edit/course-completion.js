/* global CoursePress */

(function() {
    'use strict';

    CoursePress.Define( 'CourseCompletion', function( $ ) {
        return CoursePress.View.extend({
            template_id: 'coursepress-course-completion-tpl',
            el: $('#course-completion'),
            courseEditor: false,
            events: {
                'change [name="basic_certificate"]': 'toggleSetting'
            },
            initialize: function(model, EditCourse) {
                this.model = _.extend({
                    basic_certificate: false,
                    certificate_background: '',
                    cert_margin: {
                        top: 0,
                        left: 0,
                        right: 0
                    },
                    page_orientation: 'L'
                }, model );
                this.courseEditor = EditCourse;

                this.on( 'view_rendered', this.setUpUI, this );

                this.render();
            },
            toggleSetting: function(ev) {
                var sender = $(ev.currentTarget),
                    is_checked = sender.is(':checked'),
                    container = this.$('#custom-certificate-setting');

                container[ is_checked ? 'slideDown' : 'slideUp' ]();
            },
            setUpUI: function() {
                this.background = new CoursePress.AddImage( this.$('[name="certificate_background"]') );
                this.$('select').select2();
            }
        });
    });
})();