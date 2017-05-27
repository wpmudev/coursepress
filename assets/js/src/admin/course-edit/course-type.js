/* global CoursePress */

(function(){
    'use strict';

    CoursePress.Define('CourseType', function($) {
        return CoursePress.View.extend({
            template_id: 'coursepress-course-type-tpl',
            el: $('.coursepress-page #course-type'),
            courseEditor: false,
            initialize: function(model, EditCourse) {
                // Let's inherit the model object from EditCourse
                this.model = model;

                // Validate course type data
                this.courseEditor = EditCourse;
                EditCourse.on('coursepress:validate-course-type', this.validate, this);

                this.render();
            },
            validate: function() {
                //@todo: validate course type
            }
        });
    });
})();