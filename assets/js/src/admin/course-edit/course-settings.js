/* global CoursePress */

(function() {
    'use strict';

    CoursePress.Define( 'CourseSettings', function( $, doc, win ) {
        return CoursePress.View.extend({
            el: $('#course-settings'),
            template_id: 'coursepress-course-settings-tpl',
            courseEditor: false,
            events: {
                'click #cp-create-cat': 'createCategory',
                'keyup .cp-categories-selector .select2-search__field': 'updateSearchValue',
                'click #cp-instructor-selector': 'instructorSelection',
                'click #cp-facilitator-selector': 'facilitatorSelection',
                'click ul.cp-tagged-list-removable li': 'removeUser',
                'change [name]': 'updateModel',
                'focus [name]': 'removeErrorMarker',
                'change [name="meta_enrollment_type"]': 'toggleBoxes'
            },

            initialize: function(model, EditCourse) {
                this.model = model;
                this.request = new CoursePress.Request();
                this.courseEditor = EditCourse;
                this.course_id = win._coursepress.course.ID;

                EditCourse.on('coursepress:validate-course-settings', this.validate, this);
                EditCourse.on( 'coursepress:before-next-step-course-settings', this.updateCourseModel, this );

                this.on( 'view_rendered', this.setUpUI, this );

                this.request.on( 'coursepress:success_create_course_category', this.updateCatSelection, this );
                this.request.on( 'coursepress:success_remove_from_course', this.removeUserTag, this );

                this.render();
            },

            validate: function() {
                var summary, content, proceed;

                proceed = true;
                summary = this.$('.cp-course-overview');
                content = this.$('.cp-course-description');
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
            },

            updateCourseModel: function() {
                this.courseEditor.updateCourse();
            },

            setUpUI: function() {
                var self, enrollment_type;

                self = this;

                // set feature image
                this.listing_image = new CoursePress.AddImage( this.$('#listing_image') );
                this.listing_video = new CoursePress.AddVideo( this.$('#listing_video') );

                // set category
                var catSelect = this.$('#course-categories');
                catSelect.select2({
                    tags: true
                });

                enrollment_type = this.$('[name="meta_enrollment_type"]');
                enrollment_type.select2();
                enrollment_type.on( 'change', function(ev) {
                    self.updateModel(ev);
                    self.toggleBoxes(ev);
                });

                _.delay(function() {
                    self.visualEditor({
                        content: self.model.get( 'post_excerpt' ),
                        container: self.$('.cp-course-overview'),
                        callback: function( content ) {
                            self.model.set( 'post_excerpt', content );
                        },
                        onFocusCallback: function () {
                            var summary = self.$('.cp-course-overview');
                            summary.parent().removeClass('cp-error');
                        }
                    });

                }, 100 );


                _.delay(function() {
                    self.visualEditor({
                        content: self.model.get('post_content'),
                        container: self.$('.cp-course-description'),
                        callback: function( content ) {
                            self.model.set( 'post_content', content );
                        },
                        onFocusCallback: function () {
                            var description = self.$('.cp-course-description');
                            description.parent().removeClass('cp-error');
                        }
                    });
                }, 500 );
            },

            /**
             * Create new course category.
             *
             * @param ev Current selector.
             */
            createCategory: function () {
                var name = this.$('#course-categories-search').val();
                if ('' !== name) {
                    this.request.set( {
                        'action': 'create_course_category',
                        'name': name
                    } );
                    this.request.save();
                }
            },

            /**
             * Update category selector.
             *
             * @param response Ajax response data.
             */
            updateCatSelection: function (response) {
                var selected = this.$('#course-categories').val();
                selected = null === selected ? [] : selected;
                selected.push(response);
                this.$('#course-categories').val(selected).trigger('change');
            },

            /**
             * Update hidden field value for search.
             *
             * @param ev
             */
            updateSearchValue: function (ev) {
                var target = $(ev.currentTarget);
                this.$('#course-categories-search').val(target.val());
            },

            /**
             * Instructor selection.
             */
            instructorSelection: function () {

                // Call instructor popup.
                new CoursePress.CourseModal({
                    course: this,
                    template_id: 'coursepress-course-instructor-selection-tpl',
	                type: 'instructor'
                });
            },

            /**
             * Facilitator selection.
             */
            facilitatorSelection: function () {

                // Call facilitator popup.
                new CoursePress.CourseModal({
                    course: this,
                    template_id: 'coursepress-course-facilitator-selection-tpl',
	                type: 'facilitator'
                });
            },

            /**
             * Remove facilitator/instructor from course.
             */
            removeUser: function (ev) {

                var target = $(ev.currentTarget);
                var type = target.parent().data('user-type');
                var user_id = target.data('user-id');
                if ( '' !== user_id ) {
                    this.request.set( {
                        'action': 'remove_from_course',
                        'type': type,
                        'course_id': this.course_id,
                        'user': user_id
                    } );
                    this.request.target = target;
                    this.request.save();
                }
            },

            /**
             * Remove user tag if removed from course.
             */
            removeUserTag: function () {

                if ( typeof this.request.target !== 'undefined' ) {
                    this.request.target.remove();
                }
            },

            updateModel: function(ev) {
                this.courseEditor.updateModelValues(ev);
            },

            toggleBoxes: function( ev ) {
                var sender, type, boxes;

                sender = this.$(ev.currentTarget);
                type = sender.val();
                boxes = this.$('.cp-boxes');

                boxes.slideUp();

                if ( 'passcode' === type ) {
                    this.$('.cp-passcode-box').slideDown();
                } else if ( 'prerequisite' === type ) {
                    this.$('.cp-requisite-box' ).slideDown();
                }
            }
        });
    });
})();