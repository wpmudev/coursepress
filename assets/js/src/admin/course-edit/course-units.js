/* global CoursePress */

(function() {
    'use strict';

    CoursePress.Define( 'UnitSteps', function() {
        return CoursePress.UnitsWithModuleList.extend({
            template_id: 'coursepress-unit-list-steps-tpl',
            with_modules: false
        });
    });
})();