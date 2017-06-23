/* global CoursePress */

(function() {
    'use strict';

    CoursePress.Define( 'ShortcodesSettings', function( $ ) {
        return CoursePress.View.extend({
            template_id: 'coursepress-shortcodes-setting-tpl',
            el: $('#coursepress-setting-shortcodes'),
            events: {
                'click .cp-select-list.cp-type li': 'typeSelect',
                'click .cp-select-list.cp-sub-type li': 'subTypeSelect'
            },

            typeSelect: function(ev) {
                var target = $(ev.currentTarget);
                var subtype = target.data( 'id' );
                this.setActiveItem( target );
                this.$('.cp-sub-type').addClass( 'inactive' );
                this.$('.cp-sub-type li').removeClass('active');
                this.$('#' + subtype ).removeClass( 'inactive' );
                this.$( '.cp-shortcode-details' ).removeClass( 'active' ).addClass( 'inactive' );
            },

            subTypeSelect: function(ev) {
                var target = $(ev.currentTarget);
                this.setActiveItem( target );
                this.$( '.cp-shortcode-details' ).removeClass( 'active' ).addClass( 'inactive' );
                this.$( '#' + target.data( 'id' ) ).removeClass( 'inactive' ).addClass( 'active' );
            },

            setActiveItem: function(target) {
                target.siblings().removeClass('active');
                target.addClass( 'active' );
            }
        });
    });
})();