/* global CoursePress */

(function() {
    'use strict';

    CoursePress.Define( 'ExtensionsSettings', function( $ ) {

        return CoursePress.View.extend({
            template_id: 'coursepress-extensions-setting-tpl',
            el: $( '#coursepress-setting-extensions' )
        });
    });

})();