/* global CoursePress */

(function() {
    'use strict';

    // Instructor and Facilitator selection popup.
    CoursePress.Define( 'CourseModal', function($, doc, win) {

        return CoursePress.View.extend({
            template_id: false,
            className: 'coursepress-modal',
            events: {
                'click .cp-close': 'remove',
                'click .cp-send-invite': 'sendInvite',
                'click .cp-assign-user': 'assignUser',
                'focus input[type=text]': 'removeErrorMarker'
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
                                course_id: course_id
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
                    }
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
                var email = this.$('#cp-invite-email-' + this.type);
                var first_name = this.$('#cp-invite-first-name-' + this.type);
                var last_name = this.$('#cp-invite-last-name-' + this.type);
                if ( '' !== email.val() && '' !== first_name.val() ) {
                    this.request.set( {
                        'action': 'send_email_invite',
                        'type': this.type,
                        'email': email.val(),
                        'first_name': first_name.val(),
                        'last_name': last_name.val(),
                        'course_id': this.course.course_id
                    } );
                    this.request.save();
                } else {
                    if ( '' === email.val() ) {
                        this.setErrorMarker( email, false );
                    }
                    if ( '' === first_name.val() ) {
                        this.setErrorMarker( first_name, false );
                    }
                }
            },

            /**
             * After invitation success.
             *
             * @param data
             */
            inviteSuccess: function ( data ) {
                this.$('#cp-invite-first-name-' + this.type).val('');
                this.$('#cp-invite-last-name-' + this.type).val('');
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
                if ( typeof data.name !== 'undefined' && typeof data.id !== 'undefined'
                    && ! this.course.$('ul#cp-list-' + this.type + ' li[data-user-id=' + data.id + ']').length ) {
                    this.course.$('ul#cp-list-' + this.type).append('<li data-user-id="' + data.id + '">' + data.name + '</li>');
                }
                if ( this.course.$('#cp-no-' + this.type).length ) {
                    this.course.$('#cp-no-' + this.type).remove();
                }
                this.$('#cp-course-' + this.type).val('');
                this.showResponse(data, this.assgn_resp);
                /**
                 * reset dropdown
                 */
                $('#cp-course-instructor').empty();
                $('#cp-course-facilitator').empty();
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
            }
        });
    });
})();
