/* global CoursePress */

(function() {
    'use strict';

    CoursePress.Define( 'GeneralSettings', function( $ ) {
        return CoursePress.View.extend({
            template_id: 'coursepress-general-setting-tpl',
            el: $('#coursepress-setting-general')
        });
    });
})();