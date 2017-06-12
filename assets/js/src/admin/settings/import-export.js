/* global CoursePress */

(function() {
    'use strict';

    CoursePress.Define( 'ImportExportSettings', function( $ ) {
        return CoursePress.View.extend({
            template_id: 'coursepress-import-export-setting-tpl',
            el: $('#coursepress-setting-import-export')
        });
    });
})();