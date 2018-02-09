/* global CoursePress */

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
                'change [name=meta_course_open_ended]': 'toggleCourseAvailability',
                'change [name=meta_enrollment_open_ended]': 'toggleCourseEnrollmentDates',
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
                EditCourse.on('coursepress:before-next-step-course-type', this.updateCourseModel, this);

                this.on( 'view_rendered', this.setUI, this );
                this.render();
            },

            validate: function() {
                var proceed, post_title;

                proceed = true;
                post_title = this.$('[name="post_title"]');
                post_title.parent().removeClass('cp-error');

                this.courseEditor.goToNext = false;

                if ( ! this.model.get( 'post_title' ) ) {
                    proceed = false;
                    post_title.parent().addClass('cp-error');
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

                this.courseEditor.goToNext = proceed;
            },

            updateCourseModel: function() {
                this.courseEditor.updateCourse();
            },

            setUI: function() {
                this.$('.datepicker').datepicker({
                    dateFormat: 'MM dd, yy',
                    showOtherMonths: true,
                    selectOtherMonths: true
                });
                if ( this.model.get( 'payment_paid_course') ) {
                    this.$('[name="meta_payment_paid_course"]').trigger( 'change' );
                }
            },

            updateModel: function( ev ) {
                this.courseEditor.updateModel(ev);
            },

            updatePostName: function( ev ) {
                var sender = $(ev.currentTarget),
                    slugDiv = this.$('[name="post_name"]'),
                    title = sender.val();

                if ( title ) {
                    title = title.toLowerCase().replace( / /g, '-' );
                    title = title.toLowerCase().replace( /[^a-z0-9\-]/g, '-' );
                    title = title.toLowerCase().replace( /\-+/g, '-' );
                    title = title.toLowerCase().replace( /^\-+/g, '' );
                    title = title.toLowerCase().replace( /\-+$/g, '' );
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
                var paid, settings;

                paid = this.$(ev.currentTarget).is(':checked');
                settings = win._coursepress.settings;

                $('.cp-box-marketpress, .cp-box-woocommerce, .cp-box-payment').addClass( 'hidden' );

                if ( paid ) {
                    if ( _.contains(settings.extensions, 'marketpress' ) &&
                        settings.marketpress && settings.marketpress.enabled ) {
                        $('.cp-box-marketpress').removeClass('hidden');
                    } else if( _.contains( settings.extensions, 'woocommerce') &&
                        settings.woocommerce && settings.woocommerce.enabled ) {
                        $('.cp-box-woocommerce').removeClass('hidden');
                    } else {
                        $('.cp-box-payment').removeClass('hidden');
                    }
                }
            },

            toggleCourseAvailability: function( ev ) {
                var status = this.$(ev.currentTarget).is(':checked');
                var target = this.$('[name=meta_course_end_date]');
                if ( status ) {
                    target.attr( 'disabled', 'disabled' );
                } else {
                    target.removeAttr( 'disabled', 'disabled' );
                }

            },

            toggleCourseEnrollmentDates: function( ev ) {
                var status = this.$(ev.currentTarget).is(':checked');
                var target = this.$('[name=meta_enrollment_start_date], [name=meta_enrollment_end_date]');
                if ( status ) {
                    target.attr( 'disabled', 'disabled' );
                } else {
                    target.removeAttr( 'disabled', 'disabled' );
                }
            }


        });
    });
})();
