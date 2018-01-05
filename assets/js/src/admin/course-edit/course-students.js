/* global CoursePress, _coursepress */

(function() {
    'use strict';

    CoursePress.Define( 'Course_Students', function($, doc, win) {
        var Students, Invite, InviteItem, AddItem;

        Students = new CoursePress.Request();
        InviteItem = CoursePress.View.extend({
            template_id: 'coursepress-invited-student',
            tagName: 'tr',
            events: {
                'click .remove-invite': 'removeInvitation',
            },

            // Send ajax to remove invitation.
            removeInvitation: function( ev ) {

                var target = $( ev.currentTarget ),
                    email = target.data( 'email' );
                // Send ajax request.
                if ( email ) {
                    this.model.set( 'action', 'remove_student_invite' );
                    this.model.set( 'course_id', this.course_id );
                    this.model.on( 'coursepress:success_remove_student_invite', this.invitationRemoved, this );
                    this.model.save();
                }
            },

            // Remove removed student from list.
            invitationRemoved: function( data ) {
                if ( data.email ) {
                    var btn =  this.$( 'button, input[type="button"]' );
                    // Remove closese tr.
                    btn.closest('tr').detach();
                    // If there are no invites left, show empty message.
                    if ( 2 > $('#invited-list tr').length ) {
                        $('#invited-list tr.no-invites').show();
                    }
                }
            }
        });

        Invite = CoursePress.View.extend({
            view: false,
            events: {
                'click .send-invite': 'sendInvitation',
                'change [name]': 'updateModel',
                'focus [name]': 'removeErrorMarker',
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
                    this.model.set( 'course_id', this.course_id );
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
                'click #add-student-button': 'addStudent',
                'change thead [type=checkbox]': 'toggleCheckboxes',
                'click .bulkactions [type=submit]': 'bulkActionWithdrawStudents',
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
                this.setupAjaxSelect2();
            },

            addInvitee: function( data ) {
                var invited, list;

                list = this.$('#invited-list');
                invited = new InviteItem(data);
                invited.course_id = this.model.get('ID');
                invited.$el.prependTo(list);

                list.find('.no-invites').hide();

                return invited;
            },

            withdrawStudent: function( ev ) {
                var target = $( ev.currentTarget );
                if ( window.confirm( _coursepress.text.confirm.student.withdraw ) ) {
                    var model = new CoursePress.Request();
                    model.set( 'action', 'withdraw_student' );
                    model.set('course_id', this.model.get('ID' ) );
                    model.set('student_id', target.data('id' ) );
                    model.on( 'coursepress:success_withdraw_student', this.withdrawStudentSuccess, this );
                    model.save();
                }
                return false;
            },

            withdrawStudentSuccess: function( data ) {
                $('#student-'+data.student_id).detach();
                if ( 2 > $('#coursepress-table-students tr').length ) {
                    $('#coursepress-table-students tr.noitems').show();
                    $('.tablenav.cp-admin-pagination').hide();
                }
            },

            /**
             * Setup select2 using ajax search.
             *
             * We are using ajax, so we can exclude the assigned users live.
             */
            setupAjaxSelect2: function () {
                var selector = $('#add-student-select');
                // Current course id.
                selector.select2({
                    minimumInputLength: 1,
                    placeholder: win._coursepress.text.student_search,
                    ajax: {
                        url: win._coursepress.ajaxurl,
                            dataType: 'json',
                            delay: 500,
                            data: function (params) {
                            return {
                                search: params.term,
                                _wpnonce: win._coursepress._wpnonce,
                                action: 'coursepress_search_students',
                                course_id: win._coursepress.course.ID
                            };
                        },
                        processResults: function (data) {
                            return {
                                results: $.map(data.data, function(obj) {
                                    return { id: obj.ID, text: obj.display_name };
                                 })
                            };
                        },
                        cache: true
                    },
                });
            },

            addStudent: function() {
                var model = new CoursePress.Request();
                model.set('action', 'add_student_to_course');
                model.set('course_id', win._coursepress.course.ID);
                model.set('student_id', $('#add-student-select').val());
                model.set( '_wpnonce',  win._coursepress._wpnonce );
                model.on( 'coursepress:success_add_student_to_course', this.addStudentSuccess, this );
                model.save();
            },

            addStudentSuccess: function( data ) {
                var added, list;
                list = this.$('#coursepress-table-students');
                AddItem = CoursePress.View.extend({
                    template_id: 'coursepress-course-add-student',
                    tagName: 'tr',
                    id: 'student-' + data.ID
                });
                added = new AddItem(data);
                added.$el.prependTo(list);
                $('#coursepress-table-students tr.noitems').hide();
            },

            toggleCheckboxes: function( ev ) {
                var target = $( ev.currentTarget );
                var status = target.is(':checked');
                $('#course-students tbody [type=checkbox]').each( function() {
                    if ( status ) {
                        $(this).attr( 'checked', 'checked' );
                    } else {
                        $(this).removeAttr( 'checked' );
                    }
                });
            },

            bulkActionWithdrawStudents: function( ev ) {
                var target = $( ev.currentTarget ).closest('.tablenav');
                var students = $('#course-students tbody [type=checkbox]:checked');
                var text = _coursepress.text.course.students.confirm + '\n';
                var ids = [];
                var model = new CoursePress.Request();
                if ( 'delete' !== $('[name=action]', target).val() ) {
                    return;
                }
                if ( 0 === students.length ) {
                    window.alert( _coursepress.text.course.students.no_items );
                    return;
                }
                students.each( function() {
                    text += '\n';
                    text += $('.user_login', $(this).closest('tr')).html();
                    text += ' ';
                    text += $('.display_name', $(this).closest('tr')).html();
                    ids.push( $(this).val() );
                });
                if ( ! window.confirm( text ) ) {
                    return;
                }
                model.set('action', 'withdraw_students');
                model.set('course_id', win._coursepress.course.ID);
                model.set('students', ids);
                model.set( '_wpnonce',  win._coursepress._wpnonce );
                model.on( 'coursepress:success_withdraw_students', this.bulkActionWithdrawStudentsSuccess, this );
                model.on( 'coursepress:error_withdraw_students', this.bulkActionWithdrawStudentsError, this );
                model.save();
            },

            bulkActionWithdrawStudentsSuccess: function() {
                window.location.reload();
            },

            bulkActionWithdrawStudentsError: function( data ) {
                window.alert( data.message );
            }
        });
    });
})();
