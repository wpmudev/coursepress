/* global CoursePress */

(function(){
    'use strict';

    CoursePress.Define( 'CourseList', function( $, doc, win ) {
        var CoursesList;

        CoursesList = CoursePress.View.extend({
            el: $('#coursepress-courselist'),
            events: {
                'click .cp-reset-step': 'resetEditStep',
                'change .cp-toggle-course-status': 'toggleCourseStatus',
                'click .cp-row-actions .cp-delete': 'deleteCourse',
                'click .cp-row-actions .cp-restore': 'restoreCourse',
                'click .cp-row-actions .cp-trash': 'trashCourse',
                'click #cp-search-clear': 'clearSearch',
                'click .cp-dropdown-btn': 'toggleSubMenu',
                'click #bulk-actions .cp-btn': 'bulkActions'
            },

            initialize: function( model ) {
                this.model = model;
                this.request = new CoursePress.Request();
                // On status toggle fail.
                this.request.on( 'coursepress:error_course_status_toggle', this.revertStatusToggle, this );
                // On trash or delete or restore course
                this.request.on( 'coursepress:success_trash_course', this.reloadCourseList, this );
                this.request.on( 'coursepress:success_restore_course', this.reloadCourseList, this );
                this.request.on( 'coursepress:success_delete_course', this.reloadCourseList, this );
            },
            getModel: function() {
                return this.model;
            },

            /**
             * Resets browser saved step and load course setup.
             */
            resetEditStep: function(ev) {
                var sender = $(ev.target),
                    step = sender.data('step'),
                    course_id = sender.parents('td').first().data('id');
                CoursePress.Cookie('course_setup_step_' + course_id ).set( step, 86400 * 7);
            },

            /**
             * Toggle course status.
             */
            toggleCourseStatus: function(ev) {
                this.request.selector = $(ev.target);
                var status = this.request.selector.prop('checked') ? 'publish' : 'draft';
                this.request.set( {
                    'action' : 'course_status_toggle',
                    'course_id' : this.request.selector.val(),
                    'status' : status
                } );
                this.request.save();
            },

            /**
             * Revert toggled status.
             */
            revertStatusToggle: function(data) {
                var checked, popup;

                checked = this.request.selector.prop('checked');
                this.request.selector.prop('checked', !checked);
                popup = new CoursePress.PopUp({
                    type: 'error',
                    message: data.message
                });
            },

            trashCourse: function(ev) {
                this.course_id = this.$(ev.currentTarget).closest('td').data('id');
                if ( this.course_id ) {
                    this.request.set({
                        action: 'trash_course',
                        course_id: this.course_id
                    });
                    this.request.save();
                }
                return false;
            },

            restoreCourse: function(ev) {
                this.course_id = this.$(ev.currentTarget).closest('td').data('id');
                if ( this.course_id ) {
                    this.request.set({
                        action: 'restore_course',
                        course_id: this.course_id
                    });
                    this.request.save();
                }
                return false;
            },

            deleteCourse: function(ev) {
                var confirm, sender, dropdown;

                sender = this.$(ev.currentTarget);
                this.course_id = sender.closest('td').data('id');
                dropdown = sender.parents('.cp-dropdown');

                confirm = new CoursePress.PopUp({
                    type: 'warning',
                    message: win._coursepress.text.delete_course
                });
                confirm.on( 'coursepress:popup_ok', this.deleteCurrentCourse, this );
                dropdown.removeClass('open');
                return false;
            },

            deleteCurrentCourse: function() {
                if ( this.course_id ) {
                    this.request.set({
                        action: 'delete_course',
                        course_id: this.course_id
                    });
                    this.request.save();
                }
            },

            reloadCourseList: function() {
                win.location = win.self.location;
            },

            /**
             * Clear search form and submit.
             */
            clearSearch: function() {
                var s_input;

                s_input = this.$('input[name="s"]', '#cp-search-form');

                if ( ! s_input.val() ) {
                    return false;
                }

                // Removing name will exclude this field from form values.
                s_input.removeAttr('name');
                this.$('#cp-search-form').submit();
            },

            toggleSubMenu: function( ev ) {
                var dropdown = this.$( ev.currentTarget ).parent(),
                    is_open = dropdown.is( '.open' );

                if ( is_open ) {
                    dropdown.removeClass('open');
                } else {
                    dropdown.addClass('open');
                }
            },

            bulkActions: function( ev ) {
                var action = this.$( 'select', this.$( ev.currentTarget ).parent() ).val();
                if ( '-1' === action ) {
                    return;
                }
                var ids = [];
                this.$('.check-column input[type=checkbox]:checked').each( function() {
                    var value = parseInt( $(this).val() );
                    if ( 0 < value ) {
                        ids .push( value );
                    }
                });
                var model = new CoursePress.Request( this.getModel() );
                model.set( 'action', 'courses_bulk_action' );
                model.set( 'which', action );
                model.set( 'courses', ids );
                model.on( 'coursepress:success_courses_bulk_action', location.reload() );
                model.save();
                return;
            }

        });

        CoursesList = new CoursesList();
    });
})();
