/* global CoursePress */

(function() {
    'use strict';

    CoursePress.Define( 'CertificateSettings', function( $ ) {
        return CoursePress.View.extend({
            template_id: 'coursepress-certificate-setting-tpl',
            el: $('#coursepress-setting-certificate')
        });
    });
})();