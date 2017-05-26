/* global CoursePress */

CoursePress.Define( 'CourseOverview', function($, doc, win) {
    var EditCourse = CoursePress.StepsModal.extend({
        el: $('#course-edit-template')
    });

    new EditCourse(win.cpVars.course);
});