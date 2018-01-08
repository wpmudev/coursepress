/* global CoursePress */

(function() {
    'use strict';

    CoursePress.Define( 'Forums', function( $, doc, win ) {
        var Forums;

        Forums = CoursePress.View.extend({
            el: $('#cp-forums-table'),
            events: {
                'change .cp-toggle-forum-status': 'toggleDiscussionStatus',
                'click .row-actions .cp-delete': 'deleteDiscussion',
                'click .row-actions .cp-restore': 'restoreDiscussion',
                'click .row-actions .cp-trash': 'trashDiscussion',
            },

            initialize: function() {
                this.request = new CoursePress.Request();
                // On status toggle fail.
                this.request.on( 'coursepress:error_discussion_status_toggle', this.revertStatusToggle, this );
                // On trash, delete, restore or duplicate course.
                this.request.on( 'coursepress:success_change_post', this.reloadForums, this );

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

            trashDiscussion: function(ev) {
                this.discussion_id = this.$(ev.currentTarget).closest('td').data('id');
                if ( this.discussion_id ) {
                    this.request.set({
                        action: 'change_post',
                        id: this.discussion_id,
                        type: 'discussion',
                        cp_action: 'trash',
                    });
                    this.request.save();
                }
                return false;
            },

            restoreDiscussion: function(ev) {
                this.discussion_id = this.$(ev.currentTarget).closest('td').data('id');
                if ( this.discussion_id ) {
                    this.request.set({
                        action: 'change_post',
                        id: this.discussion_id,
                        type: 'discussion',
                        cp_action: 'restore',
                    });
                    this.request.save();
                }
                return false;
            },

            deleteDiscussion: function(ev) {
                var confirm, sender, dropdown;
                sender = this.$(ev.currentTarget);
                this.discussion_id = sender.closest('td').data('id');
                dropdown = sender.parents('.cp-dropdown');
                confirm = new CoursePress.PopUp({
                    type: 'warning',
                    message: win._coursepress.text.delete_post
                });
                confirm.on( 'coursepress:popup_ok', this.deleteCurrentDiscussion, this );
                dropdown.removeClass('open');
                return false;
            },

            deleteCurrentDiscussion: function() {
                if ( this.discussion_id ) {
                    new CoursePress.PopUp({
                        type: 'info',
                        message: win._coursepress.text.deleting_post
                    });
                    this.request.set({
                        action: 'change_post',
                        id: this.discussion_id,
                        type: 'discussion',
                        cp_action: 'delete',
                    });
                    this.request.save();
                }
            },

            /**
             * Reload the Forums page.
             */
            reloadForums: function() {
                win.location = win.self.location;
            },
        });
        Forums = new Forums();
    });

})();