/* global CoursePress */

(function() {
    'use strict';
    CoursePress.Define('Toggle', function ($) {
        return CoursePress.View.extend({
            events: {
                'change': 'toggleStatus'
            },
            render: function () {
                this.$el.hide();
                $('<span class="coursepress-toggle">YES</span>').insertAfter(this.$el);
            },
            toggleStatus: function (ev) {
                var sender = $(ev.currentTarget),
                    is_checked = sender.is(':checked');

                if (is_checked) {
                    this.trigger('toggle_active');
                } else {
                    this.trigger('toggle_inactive');
                }
            }
        });
    });
})();