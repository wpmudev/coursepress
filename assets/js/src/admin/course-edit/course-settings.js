/* global CoursePress */

(function() {
    'use strict';

    CoursePress.Define( 'CourseSettings', function($) {
        return CoursePress.View.extend({
            el: $('#course-settings'),
            template_id: 'coursepress-course-settings-tpl',
            courseEditor: false,
            initialize: function(model, EditCourse) {
                this.model = model;
                this.courseEditor = EditCourse;

                EditCourse.on('coursepress:validate-course-settings', this.validate, this);

                this.on( 'view_rendered', this.setUpUI, this );

                this.render();
            },
            validate: function() {
                // @todo: do course settings validataion
            },
            setUpUI: function() {
                // set feature image
                this.listing_image = new CoursePress.AddImage( this.$('#listing_image') );

                // set category
                var catSelect = this.$('#course-categories');
                catSelect.select2({
                    placeholder: catSelect.attr('placeholder')
                });

                this.$('[name="meta_enrollment_type"]').select2();
            }
        });
    });
})();