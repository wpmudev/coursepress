/* global CoursePress */

(function() {
    'use strict';

    CoursePress.Define( 'ShortcodesSettings', function( $ ) {
        return CoursePress.View.extend({
            template_id: 'coursepress-shortcodes-setting-tpl',
            el: $('#coursepress-setting-shortcodes')
        });
    });
})();