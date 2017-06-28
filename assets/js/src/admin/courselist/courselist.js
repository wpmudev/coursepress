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
                'click #cp-search-clear': 'clearSearch'
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
                var target = $(ev.target),
                    request = new CoursePress.Request(),
                    status = 'draft';
                if ( target.prop('checked') ) {
                    status = 'publish';
                }
                request.set( {
                    'action' : 'course_status_toggle',
                    'course_id' : target.val(),
                    'status' : status,
                } ).save();
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
            }
        });

        CoursesList = new CoursesList();
    });
})();