/* global CoursePress */

(function() {
    'use strict';

    CoursePress.Define( 'Course_Students', function($) {
        var Students;

        Students = new CoursePress.Request();

        return CoursePress.View.extend({
            template_id: 'coursepress-students-tpl',
            el: $('#course-students'),
            initialize: function( model ) {
                this.model = model;
                this.on( 'view_rendered', this.setUI, this );
                this.render();
            },
            setUI: function() {
                $('.course-content').addClass('coursepress-page-transparent');
            }
        });
    });
})();