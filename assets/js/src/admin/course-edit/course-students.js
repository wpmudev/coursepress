/* global CoursePress */

(function() {
    'use strict';

    CoursePress.Define( 'Course_Students', function($) {
        var Students, Invite;

        Students = new CoursePress.Request();

        Invite = CoursePress.View.extend({
            events: {
                'click .send-invite': 'sendInvitation',
                'change [name]': 'updateModel',
                'focus [name]': 'removeErrorMarker'
            },
            initialize: function() {
                this.model = new CoursePress.Request();
            },
            sendInvitation: function() {
                var first_name, last_name, email, error;

                error = 0;
                first_name = this.$('[name="first_name"]');
                last_name = this.$('[name="last_name"]');
                email = this.$('[name="email"]');

                if ( ! this.model.get('first_name') ) {
                    first_name.parent().addClass('cp-error');
                    error++;
                }
                if ( ! this.model.get( 'last_name' ) ) {
                    last_name.parent().addClass('cp-error');
                    error++;
                }
                if ( ! this.model.get('email') ) {
                    email.parent().addClass('cp-error');
                    error++;
                }

                if ( ! error ) {
                    this.model.set( 'action', 'send_student_invite' );
                    this.model.off( 'coursepress:success_send_student_invite' );
                    this.model.on( 'coursepress:success_send_student_invite', this.invitationSent, this );
                    this.model.save();
                }
            },
            invitationSent: function() {
            }
        });

        return CoursePress.View.extend({
            template_id: 'coursepress-students-tpl',
            el: $('#course-students'),
            initialize: function( model ) {
                this.model = model;
                this.on( 'view_rendered', this.setUI, this );
                this.render();
            },
            setUI: function() {
                $('.course-content').addClass('coursepress-page-transparent');
                Invite = Invite.extend({
                    el: $('#student-invites')
                });
                this.invite = new Invite();
            }
        });
    });
})();