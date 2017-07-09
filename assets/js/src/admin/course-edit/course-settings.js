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
                var summary, content, proceed;

                proceed = true;
                summary = this.$('[name="post_excerpt"]');
                content = this.$('[name="post_content"]');
                this.courseEditor.goToNext = true;

                if ( ! this.model.get('post_excerpt') ) {
                    summary.parent().addClass('cp-error');
                    proceed = false;
                }
                if ( ! this.model.get('post_content') ) {
                    content.parent().addClass('cp-error');
                    proceed = false;
                }

                if ( false === proceed ) {
                    this.courseEditor.goToNext = false;
                    return false;
                }

                this.courseEditor.updateCourse();
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