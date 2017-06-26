/* global CoursePress */

(function() {
    'use strict';

    CoursePress.Define( 'CapabilitiesSettings', function( $ ) {
        return CoursePress.View.extend({
            template_id: 'coursepress-capabilities-setting-tpl',
            el: $('#coursepress-setting-capabilities'),
            events: {
                'click .cp-select-list.cp-capabilities li': 'showHideCaps'
            },

            showHideCaps: function(ev) {
                var target = $(ev.currentTarget);
                var capsDiv = target.data( 'id' );
                this.setActiveItem( target );
                this.$( '.cp-caps-fields' ).addClass( 'inactive' );
                this.$('#' + capsDiv ).removeClass( 'inactive' );
            },

            setActiveItem: function(target) {
                target.siblings().removeClass('active');
                target.addClass( 'active' );
            }
        });
    });
})();