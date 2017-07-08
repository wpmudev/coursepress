/* global CoursePress */

(function(){
    'use strict';

    CoursePress.Define( 'CourseList', function($) {
        var CoursesList;

        CoursesList = CoursePress.View.extend({
            el: $('#coursepress-courselist'),
            events: {
                'click .cp-reset-step': 'resetEditStep',
                'change .cp-toggle-course-status': 'toggleCourseStatus',
                'click .menu-item-duplicate-course': 'duplicateCourse',
                'click .menu-item-delete': 'deleteCourse',
                'click #cp-search-clear': 'clearSearch',
                'click .cp-dropdown-btn': 'toggleSubMenu'
            },

            initialize: function() {
                this.request = new CoursePress.Request();
                // On status toggle fail.
                this.request.on( 'coursepress:error_course_status_toggle', this.revertStatusToggle, this );
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
            revertStatusToggle: function() {
                var checked = this.request.selector.prop('checked');
                this.request.selector.prop('checked', !checked);
            },

            duplicateCourse: function() {
                // @todo: duplicate course here
            },

            deleteCourse: function() {
                // @todo: delete course
            },

            /**
             * Clear search form and submit.
             */
            clearSearch: function() {
                // Removing name will exclude this field from form values.
                this.$('input[name="s"]','#cp-search-form').removeAttr('name');
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
            }
        });

        CoursesList = new CoursesList();
    });
})();