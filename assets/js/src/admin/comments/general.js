/* global CoursePress */

(function(){
    'use strict';

    CoursePress.Define( 'CommentsList', function($) {
        var CommentsList;

        CommentsList = CoursePress.View.extend({
            el: $('#coursepress-comments-list'),
            events: {
                'click .column-author a.status': 'toggleCommentStatus'
            },

            initialize: function() {
                this.on( 'view_rendered', this.setUI, this );
                this.render();
            },

            // Setup UI.
            setUI: function() {
                this.$('select').select2();
            },

            /**
             * Toggle course status.
             */
            toggleCommentStatus: function(ev) {
                var request = new CoursePress.Request();
                var selector = $(ev.target);
                request.set( {
                    'action' : 'comment_status_toggle',
                    'id' : selector.data('id'),
                    'nonce' : selector.data('nonce')
                } );
                request.on( 'coursepress:success_comment_status_toggle', this.setStatusToggle, this );
                request.save();
                return false;

            },
            setStatusToggle: function(data) {
                var target = $('.comment-'+data.id);
                target.removeClass( 'approved unapproved', this.el ).addClass( data.status );
                $('a.status', target).html( data.button_text );
            }
        });

        CommentsList = new CommentsList();
    });
})();
