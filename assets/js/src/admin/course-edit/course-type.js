/* global CoursePress, _, _coursepress */

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
                'change [name="meta_course_type"]': 'changeCourseType',
                'change [name="meta_payment_paid_course"]': 'changeCoursePaid',
                'change [name]': 'updateModel',
                'focus [name]': 'removeErrorMarker'
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
                var proceed, post_title;

                proceed = true;
                post_title = this.$('[name="post_title"]');
                post_title.parent().removeClass('cp-error');
                this.courseEditor.goToNext = true;

                if ( ! this.model.get( 'post_title' ) ) {
                    proceed = false;
                    post_title.parent().addClass('cp-error');
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

                if ( ! proceed ) {
                    this.courseEditor.goToNext = false;

                    return false;
                }

                // Save the course
                this.courseEditor.updateCourse();
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
                sender.trigger( 'change' );
            },
            changeCourseType: function(ev) {
                var sender = $(ev.currentTarget),
                    value = sender.val(),
                    div = this.$('#type-' + value );

                sender.parents('li').siblings().removeClass('active');
                sender.parents('li').addClass('active');
                div.siblings('.cp-course-type').removeClass('active').addClass('inactive');
                div.addClass('active').removeClass('inactive');
            },
            changeCoursePaid: function(ev) {
                var paid = ev.currentTarget.checked;
                if ( paid ) {
                    if ( _coursepress.mp_is_on ) {
                        $('.cp-box-marketpress').removeClass( 'hidden' );
                    } else {
                        $('.cp-box-off').removeClass('hidden');
                    }
                } else {
                    $('.cp-box-marketpress').addClass( 'hidden' );
                }
            }
        });
    });
})();
