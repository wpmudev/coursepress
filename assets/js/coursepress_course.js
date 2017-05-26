/*! CoursePress - v3.0-beta
 * https://premium.wpmudev.org/project/coursepress-pro/
 * Copyright (c) 2017; * Licensed GPLv2+ */
CoursePress.Define( 'CourseOverview', function($, doc, win) {
    var EditCourse = CoursePress.StepsModal.extend({
        el: $('#course-edit-template')
    });

    new EditCourse(win.cpVars.course);
});