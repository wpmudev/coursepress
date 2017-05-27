/* global CoursePress */

CoursePress.Define('CourseType', function($) {
   return CoursePress.View.extend({
       template_id: 'coursepress-course-type-tpl',
       el: $('.coursepress-page #course-type')
   });
});