/* global CoursePress */

(function() {
    'use strict';

    CoursePress.Define( 'Upload', function( $, doc, win ) {
        return CoursePress.Request.extend({
            url: win._coursepress.ajaxurl + '?action=coursepress_upload',
            parse: function ( response ) {
                var action = this.get('type');

                if ( response.success ) {
                    this.trigger('coursepress:success_' + action, response.data);
                } else {
                    this.trigger('coursepress:error_' + action, response.data);
                }
            },
            upload: function() {
                var data = this.toJSON();

                this.save(data, {
                    iframe: true,
                    files: $(':file'),
                    data: data
                });
            }
        });
    });
})();