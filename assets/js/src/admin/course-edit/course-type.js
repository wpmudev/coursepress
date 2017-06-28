/* global CoursePress, _ */

(function(){
    'use strict';

    CoursePress.Define('CourseType', function($) {
        return CoursePress.View.extend({
            template_id: 'coursepress-course-type-tpl',
            el: $('.coursepress-page #course-type'),
            courseEditor: false,
            events: {
                'keyup [name="post_title"]': 'updatePostName',
                'keyup [name="post_name"]': 'updateSlug',
                'change [name="meta_course_type"]': 'changeCourseType'
            },
            initialize: function(model, EditCourse) {
                // Let's inherit the model object from EditCourse
                this.model = model;

                // Validate course type data
                this.courseEditor = EditCourse;
                EditCourse.on('coursepress:validate-course-type', this.validate, this);

                this.on( 'view_rendered', this.setUI, this );

                this.render();
            },
            validate: function() {
                var proceed = true;

                if ( _.isEmpty( this.model.post_title ) ) {
                    proceed = false;
                }

                if ( _.isTrue( this.model.payment_paid_course) ) {
                    // @todo: Validate MP and Woo
                }
                if ( 'manual' === this.model.course_type ) {
                    // Check course dates
                    if ( _.isEmpty( this.model.course_start_date ) &&
                        _.isEmpeyt( this.model.course_end_date ) &&
                        _.isEmpty( this.model.enrollment_start_date ) &&
                        _.isEmpty( this.model.enrollment_end_date ) ) {
                        proceed = false;
                    }
                }

                if ( ! _.isTrue(proceed ) ) {
                    this.courseEditor.goToNext = false;
                }
            },

            setUI: function() {
                var options = {
                    dateFormat: 'MM dd, yy'
                    },
                    names = '[name="meta_course_start_date"],[name="meta_course_end_date"],[name="meta_enrollment_start_date"],[name="enrollment_end_date"]';

                this.$( names ).datepicker( options );
            },

            updatePostName: function( ev ) {
                var sender = $(ev.currentTarget),
                    slugDiv = this.$('[name="post_name"]'),
                    title = sender.val();

                if ( title ) {
                    title = title.toLowerCase().replace( / /g, '-' );
                }
                slugDiv.val(title);
                slugDiv.trigger('keyup');
            },
            updateSlug: function(ev) {
                var sender = $(ev.target),
                    slugDiv = this.$('.cp-slug');

                slugDiv.html(sender.val());
            },
            changeCourseType: function(ev) {
                var sender = $(ev.currentTarget),
                    value = sender.val(),
                    div = this.$('#type-' + value );

                sender.parents('li').siblings().removeClass('active');
                sender.parents('li').addClass('active');
                div.siblings('.cp-course-type').removeClass('active').addClass('inactive');
                div.addClass('active').removeClass('inactive');
            }
        });
    });
})();