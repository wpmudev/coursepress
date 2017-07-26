/* global CoursePress, console */

(function(){
    'use strict';

    CoursePress.Define( 'CommentsList', function($) {
        var CommentsList;

        CommentsList = CoursePress.View.extend({
            el: $('#coursepress-comments-list'),
            events: {
                'click .column-author a.status': 'toggleCommentStatus',
            },

            initialize: function() {
            },

            /**
             * Toggle course status.
             */
            toggleCommentStatus: function(ev) {
                var request = new CoursePress.Request();
                request.selector = $(ev.target);
                var status = request.selector.data('status') ? 0:1;
                request.set( {
                    'action' : 'comment_status_toggle',
                    'id' : request.selector.data('id'),
                    'nonce' : request.selector.data('nonce'),
                    'status' : status
                } );
                request.on( 'coursepress:success_comment_status_toggle', this.setStatusToggle, this );
                request.on( 'coursepress:error_comment_status_toggle', this.setStatusToggle, this );
                request.save();
                return false;

            },
            setStatusToggle: function(data) {
                console.log(data);
            }
        });

        CommentsList = new CommentsList();
    });
})();
