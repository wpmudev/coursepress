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
            },
            initialize: function(model, EditCourse) {
                this.model = model;
                this.request = new CoursePress.Request();
                this.courseEditor = EditCourse;
                this.course_id = win._coursepress.course.ID;

                EditCourse.on('coursepress:validate-course-settings', this.validate, this);

                this.on( 'view_rendered', this.setUpUI, this );

                this.request.on( 'coursepress:success_create_course_category', this.updateCatSelection, this );
                this.request.on( 'coursepress:success_remove_from_course', this.removeUserTag, this );

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
                this.listing_video = new CoursePress.AddVideo( this.$('#listing_video') );

                // set category
                var catSelect = this.$('#course-categories');
                catSelect.select2({
                    tags: true,
                });

                this.$('[name="meta_enrollment_type"]').select2();
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
            }
        });
    });

    // Instructor and Facilitator selection popup.
    CoursePress.Define( 'CourseModal', function($, doc, win) {

        return CoursePress.View.extend({
            template_id: false,
            className: 'coursepress-modal',
            events: {
                'click .cp-close': 'remove',
                'click .cp-send-invite': 'sendInvite',
                'click .cp-assign-user': 'assignUser',
            },

            initialize: function( options ) {

	            // Set required variables.
                this.course = options.course;
                this.request = new CoursePress.Request();
	            this.template_id = options.template_id;
	            this.type = options.type;
                this.inv_resp = '.cp-invitation-response-' + options.type;
                this.assgn_resp = '.cp-assign-response-' + options.type;

				// Setup UI elements.
                this.on( 'view_rendered', this.setUpUI, this );

	            // Handle ajax request responses.
                this.request.on( 'coursepress:success_send_email_invite', this.inviteSuccess, this );
                this.request.on( 'coursepress:success_assign_to_course', this.assignSuccess, this );
                this.request.on( 'coursepress:error_send_email_invite', this.inviteError, this );
                this.request.on( 'coursepress:error_assign_to_course', this.assignError, this );

                this.render();
            },

	        /**
	         * Setup UI elements.
	         */
            setUpUI: function () {
                // Setup ajax select2.
                this.setupAjaxSelect2(this.$('#cp-course-instructor'), 'instructor');
                this.setupAjaxSelect2(this.$('#cp-course-facilitator'), 'facilitator');
            },

            /**
             * Setup select2 using ajax search.
             *
             * We are using ajax, so we can exclude the assigned users live.
             *
             * @param selector Dropdown selector.
             * @param type Type of user.
             */
            setupAjaxSelect2: function ( selector, type ) {

                // Current course id.
                var course_id = this.course.course_id;
                selector.select2({
                    minimumInputLength: 3,
                    width: '100%',
                    ajax: {
                        url: win._coursepress.ajaxurl,
                            dataType: 'json',
                            delay: 500,
                            data: function (params) {
                            return {
                                search: params.term,
                                _wpnonce: win._coursepress._wpnonce,
                                action: 'coursepress_get_users',
                                type: type,
                                course_id: course_id,
                            };
                        },
                        processResults: function (data) {
                            return {
                                results: $.map(data, function(obj) {
                                    return { id: obj.ID, text: obj.user_login };
                                })
                            };
                        },
                        cache: true
                    },
                });
            },

	        // On render.
            render: function() {
                CoursePress.View.prototype.render.apply( this );
                this.$el.appendTo( 'body' );
            },

            /**
             * Send invitation mail to the email.
             */
            sendInvite: function () {

	            // Email to send the invitation.
                var email = this.$('#cp-invite-email-' + this.type).val();
                if ( '' !== email ) {
                    this.request.set( {
                        'action': 'send_email_invite',
                        'type': this.type,
                        'email': email,
                        'course_id': this.course.course_id
                    } );
                    this.request.save();
                }
            },

            /**
             * After invitation success.
             *
             * @param data
             */
            inviteSuccess: function ( data ) {

                this.$('#cp-invite-email-' + this.type).val('');
                // Show response message.
                this.showResponse(data, this.inv_resp);
            },

            /**
             * After invitation error.
             *
             * @param data
             */
            inviteError: function ( data ) {

                // Show response message.
                this.showResponse(data, this.inv_resp);
            },

            /**
             * Assign instructor/facilitator to the course.
             */
            assignUser: function () {

                var user_id = this.$('#cp-course-' + this.type).val();
                if ( '' !== user_id ) {
                    this.request.set( {
                        'action': 'assign_to_course',
                        'type': this.type,
                        'course_id': this.course.course_id,
                        'user': user_id
                    } );
                    this.request.save();
                }
            },

            /**
             * After successful assign.
             *
             * @param data
             */
            assignSuccess: function ( data ) {

                // If user assigned, add them to the tags.
                if ( typeof data.name !== 'undefined' && typeof data.id !== 'undefined' ) {
                    this.course.$('ul#cp-list-' + this.type).append('<li data-user-id="' + data.id + '">' + data.name + '</li>');
                }
                if ( this.course.$('#cp-no-' + this.type).length ) {
                    this.course.$('#cp-no-' + this.type).remove();
                }
                this.$('#cp-course-' + this.type).val('');
                this.showResponse(data, this.assgn_resp);
            },

            /**
             * After error on assign.
             *
             * @param data
             */
            assignError: function ( data ) {

                // Show response message.
                this.showResponse(data, this.assgn_resp);
            },

            /**
             * Show response message.
             *
             * @param data
             * @param selector
             */
            showResponse: function ( data, selector_class ) {

                var selector = this.$(selector_class);
                // Show response message.
                selector.html(data.message).removeClass('inactive');
                window.setTimeout(function() {
                    selector.html('').addClass('inactive');
                }, 2500);
            },
        });
    });
})();