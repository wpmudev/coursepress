/* global CoursePress */

(function() {
    'use strict';

    CoursePress.Define( 'EmailSettings', function( $ ) {
        return CoursePress.View.extend({
            template_id: 'coursepress-emails-setting-tpl',
            el: $('#coursepress-setting-emails')
        });
    });
})();