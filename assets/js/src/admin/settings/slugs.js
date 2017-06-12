/* global CoursePress */

(function() {
    'use strict';

    CoursePress.Define( 'SlugsSettings', function($) {
        return CoursePress.View.extend({
            template_id: 'coursepress-slugs-setting-tpl',
            el: $('#coursepress-setting-slugs')
        });
    });
})();