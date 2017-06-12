/* global CoursePress */

(function() {
    'use strict';

    CoursePress.Define( 'CapabilitiesSettings', function( $ ) {
        return CoursePress.View.extend({
            template_id: 'coursepress-capabilities-setting-tpl',
            el: $('#coursepress-setting-capabilities')
        });
    });
})();