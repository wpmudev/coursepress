/* global CoursePress */

(function(){
    'use strict';

    CoursePress.Define('CourseModel', function(){
       return CoursePress.Request.extend({
           updateCourseData: function() {
               this.set('action', 'update_course');
               this.off('coursepress:error_update_course');
               this.on('coursepress:error_update_course', this.courseUpdateError, this);
               this.save();
           },
           courseUpdateError: function() {
               // @todo: show error message
           }
       });
    });
})();