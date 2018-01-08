/* global CoursePress */

(function() {
    'use strict';

    CoursePress.Define( 'Forums', function( $ ) {
        var Forums;

        Forums = CoursePress.View.extend({
            el: $('#cp-forums-table'),
            events: {
                'change .cp-toggle-forum-status': 'toggleDiscussionStatus',
            },

            initialize: function() {
                this.request = new CoursePress.Request();

                // On status toggle fail.
                this.request.on( 'coursepress:error_discussion_status_toggle', this.revertStatusToggle, this );

                this.render();
            },

            /**
             * Toggle discussion status.
             */
            toggleDiscussionStatus: function( ev ) {
                this.request.selector = $(ev.target);
                var status = this.request.selector.prop('checked') ? 'publish' : 'draft';
                this.request.set( {
                    'action' : 'discussion_status_toggle',
                    'discussion_id' : this.request.selector.val(),
                    'status' : status
                } );
                this.request.save();
            },

            /**
             * Revert toggled status.
             */
            revertStatusToggle: function() {
                var checked = this.request.selector.prop('checked');
                this.request.selector.prop('checked', !checked);
            },
        });
        Forums = new Forums();
    });

})();