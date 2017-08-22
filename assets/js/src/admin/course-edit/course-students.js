/* global CoursePress, console */

(function() {
    'use strict';

    CoursePress.Define( 'Course_Students', function($, doc, win) {
        var Students, Invite, InviteItem;

        Students = new CoursePress.Request();
        InviteItem = CoursePress.View.extend({
            template_id: 'coursepress-invited-student',
            tagName: 'tr'
        });

        Invite = CoursePress.View.extend({
            view: false,
            events: {
                'click .send-invite': 'sendInvitation',
                'change [name]': 'updateModel',
                'focus [name]': 'removeErrorMarker'
            },
            initialize: function(model, View) {
                this.view = View;
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
                    this.$('.send-invite').addClass('active');
                    this.model.set( 'action', 'send_student_invite' );
                    this.model.set('course_id', this.course_id);
                    this.model.off( 'coursepress:success_send_student_invite' );
                    this.model.on( 'coursepress:success_send_student_invite', this.invitationSent, this );
                    this.model.save();
                }
            },
            invitationSent: function(data) {
                var invited;

                invited = this.view.addInvitee(data);
                invited.$el.addClass('invitee-active');

                _.delay(function() {
                    invited.$el.removeClass('invitee-active');
                }, 1500);

                this.$('.send-invite').removeClass('active');
                this.$('[name="first_name"],[name="last_name"],[name="email"]').val('');
            }
        });

        return CoursePress.View.extend({
            template_id: 'coursepress-students-tpl',
            courseView: false,
            el: $('#course-students'),

			events: {
				'click .cp-btn-withdraw-student': 'withdrawStudent',
			},

            initialize: function( model, courseView ) {
                this.model = model;
                this.courseView = courseView;
                this.on( 'view_rendered', this.setUI, this );
                courseView.on( 'coursepress:step-before-change', this.removeClass, this );
                courseView.on( 'coursepress:step-changed', this.addClass, this );
                this.render();
            },

            removeClass: function() {
                $('.course-content').removeClass('coursepress-page-transparent');
            },
            addClass: function(current) {
                if ( 'course-students' === current ) {
                    $('.course-content').addClass('coursepress-page-transparent');
                }
            },

            setUI: function() {
                $('.course-content').addClass('coursepress-page-transparent');
                Invite = Invite.extend({
                    el: $('#student-invites'),
                    course_id: this.model.get('ID')
                });
                this.invite = new Invite({}, this);

                if ( win._coursepress.invited_students ) {
                    _.each( win._coursepress.invited_students, function(student) {
                        this.addInvitee(student);
                    }, this );
                }
            },

            addInvitee: function( data ) {
                var invited, list;

                list = this.$('#invited-list');
                invited = new InviteItem(data);
                invited.$el.prependTo(list);

                list.find('.no-invites').hide();

                return invited;
            },

            withdrawStudent: function() {
                console.log('aaa');
                return false;
                /*
                    this.model.set( 'action', 'send_student_invite' );
                    this.model.set('course_id', this.course_id);
                    this.model.off( 'coursepress:success_send_student_invite' );
                    this.model.on( 'coursepress:success_send_student_invite', this.invitationSent, this );
                    this.model.save();
                    */
            }

        });
    });
})();
