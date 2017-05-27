/* global CoursePress */

(function(){
    'use strict';

    CoursePress.Define('CourseUnits', function($){
       return CoursePress.View.extend({
           el: $('#course-units'),
           template_id: 'coursepress-course-units-tpl'
       });
    });
})();