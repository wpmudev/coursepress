/* global CoursePress, _ */

(function(){
    'use strict';

    CoursePress.Define('CourseType', function( $, doc, win ) {
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
                'focus [name]': 'removeErrorMarker',
                'click .sample-course-btn': 'selectSampleCourse'
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
                    if ( ! this.model.course_start_date &&
                        ! this.model.course_end_date &&
                        ! this.model.enrollment_start_date &&
                        ! this.model.enrollment_end_date ) {
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
                this.$('.datepicker').datepicker({dateFormat: 'MM dd, yy' });
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

            selectSampleCourse: function() {
                this.sample = new CoursePress.SampleCourse({}, this);
            },

            changeCoursePaid: function(ev) {
                var paid = this.$(ev.currentTarget).is(':checked'),
                    settings = win._coursepress.settings;

                if ( paid ) {
                    if ( settings.marketpress && settings.marketpress.enabled ) {
                        $('.cp-box-marketpress').removeClass('hidden');
                    } else {
                        $('.cp-box-off').removeClass('hidden');
                    }
                } else {
                    $('.cp-box-marketpress').addClass('hidden');
                }
            }
        });
    });
})();
