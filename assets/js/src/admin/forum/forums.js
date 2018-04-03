/* global CoursePress */

(function() {
    'use strict';

    CoursePress.Define( 'Forums', function( $, doc, win ) {
        var Forums;

        Forums = CoursePress.View.extend({
            el: $('#coursepress-forums'),
            events: {
                'change .cp-toggle-forum-status': 'toggleDiscussionStatus',
                'click .row-actions .cp-delete': 'deleteDiscussion',
                'click .row-actions .cp-restore': 'restoreDiscussion',
                'click .row-actions .cp-trash': 'trashDiscussion',
                'click #bulk-actions .cp-btn': 'bulkActions',
            },

            // Initialize.
            initialize: function() {
                this.request = new CoursePress.Request();
                // On status toggle fail.
                this.request.on( 'coursepress:error_discussion_status_toggle', this.revertStatusToggle, this );
                // On trash, delete, restore or duplicate forum.
                this.request.on( 'coursepress:success_change_post', this.reloadForums, this );
                this.request.on( 'coursepress:success_change_forums_status', this.reloadForums, this );

                this.on( 'view_rendered', this.setUI, this );
                this.render();
            },

            // Setup UI.
            setUI: function() {
                this.$('select').select2();
            },

            // Toggle discussion status.
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

            // Revert toggled status.
            revertStatusToggle: function() {
                var checked = this.request.selector.prop('checked');
                this.request.selector.prop('checked', !checked);
            },

            // Trash single discussion.
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

            // Restore single discussion.
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

            // Handle delete action.
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

            // Delete single forum.
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

            // Process bulk actions.
            bulkActions: function() {
                var action, ids, items, confirm;
                action = $('#bulk-action-selector-top').val();
                if ( '-1' === action ) {
                    return;
                }
                ids = [];
                items = $('.check-column-value input:checked');
                items.each( function() {
                    var value = parseInt( $(this).val() );
                    if ( 0 < value ) {
                        ids.push( value );
                    }
                });
                if ( ids.length === 0 ) {
                    new CoursePress.PopUp({
                        type: 'info',
                        message: win._coursepress.text.forums.no_items
                    });
                    return;
                }
                this.ids = ids;
                this.action = action;
                if ( 'delete' === action ) {
                    confirm = new CoursePress.PopUp({
                        type: 'warning',
                        message: win._coursepress.text.forums.delete_confirm
                    });
                    confirm.on( 'coursepress:popup_ok', this.bulkActionsSave, this );
                } else {
                    this.bulkActionsSave( this );
                }
                return;
            },

            // Process bulk actions ajax.
            bulkActionsSave: function() {
                if ( this.ids && this.action ) {
                    if ( 'delete' === this.action ) {
                        new CoursePress.PopUp({
                            type: 'info',
                            message: win._coursepress.text.forums.deleting_forums
                        });
                    }
                    this.request.set({
                        action: 'change_forums_status',
                        forums: this.ids,
                        cp_action: this.action,
                        _wpnonce: win._coursepress._wpnonce,
                    });
                    this.request.save();
                }
            },

            // Reload the Forums page.
            reloadForums: function() {
                win.location = win.self.location;
            },
        });
        Forums = new Forums();
    });

})();