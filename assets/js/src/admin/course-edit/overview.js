/* global CoursePress */

CoursePress.Define( 'CourseOverview', function($, doc, win) {
    var EditCourse = CoursePress.StepsModal.extend({
        el: $('#course-edit-template'),
        initialize: function() {
            // Load course-type view
            this.once( 'coursepress:step-course-type-changed', this.courseTypeView, this);
            // Load course settings view
            this.once('coursepress:step-course-settings-changed', this.courseSettingsView, this);
            // Load course units view
            this.once('coursepress:step-course-units-changed', this.courseUnitsView, this);
            // Load course students view
            this.once('coursepress:step-course-students-changed', this.courseStudents, this);

            CoursePress.StepsModal.prototype.initialize.apply(this, arguments);
        },
        courseTypeView: function() {
            this.courseType = new CoursePress.CourseType(this.model.toJSON());
        },
        courseSettingsView: function() {},
        courseUnitsView: function() {},
        courseStudentsView: function() {}
    });
    new EditCourse(win._coursepress.course);
});