/* global CoursePress */

(function() {
    'use strict';

    CoursePress.Define( 'PopUp', function() {
        return CoursePress.View.extend({
            template_id: 'coursepress-popup-tpl',
            className: 'coursepress-popup',
            events: {
                'click .btn-ok': 'remove'
            },
            render: function() {
                CoursePress.View.prototype.render.apply( this );

                this.$el.appendTo( 'body' );
            }
        });
    });
})();