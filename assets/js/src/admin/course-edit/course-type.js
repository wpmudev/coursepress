/* global CoursePress */

(function(){
    'use strict';

    CoursePress.Define('CourseType', function($) {
        return CoursePress.View.extend({
            template_id: 'coursepress-course-type-tpl',
            el: $('.coursepress-page #course-type'),
            courseEditor: false,
            events: {
                'keyup [name="post_name"]': 'updateSlug',
                'change [name="course_type"]': 'changeCourseType'
            },
            initialize: function(model, EditCourse) {
                // Let's inherit the model object from EditCourse
                this.model = model;

                // Validate course type data
                this.courseEditor = EditCourse;
                EditCourse.on('coursepress:validate-course-type', this.validate, this);

                this.on( 'view_rendered', this.setSelect2, this );

                this.render();
            },
            validate: function() {
                //@todo: validate course type
            },

            setSelect2: function() {
                //this.$('select').select2();
            },

            updateSlug: function(ev) {
                var sender = $(ev.target),
                    slugDiv = this.$('.cp-slug');

                slugDiv.html(sender.val());
            },
            changeCourseType: function(ev) {
                var sender = $(ev.currentTarget),
                    value = sender.val(),
                    div = this.$('#type-' + value );

                sender.parents('li').siblings().removeClass('active');
                sender.parents('li').addClass('active');
                div.siblings('.cp-course-type').removeClass('active').addClass('inactive');
                div.addClass('active').removeClass('inactive');
            }
        });
    });
})();