/* global CoursePress */

(function() {
    'use strict';

    CoursePress.Define( 'CapabilitiesSettings', function( $ ) {
        return CoursePress.View.extend({
            template_id: 'coursepress-capabilities-setting-tpl',
            el: $( '#coursepress-setting-capabilities' ),
            events: {
                'click .cp-select-list.cp-capabilities li': 'showHideCaps',
                'change [name]': 'updateModel'
            },
            current: 'instructor',
            initialize: function( model ) {
                this.model = model;
                this.render();
            },

            updateModel: function(ev) {
                var sender = $(ev.currentTarget),
                    name = sender.attr('name'),
                    value = sender.val();

                if ( sender.is('[type="checkbox"],[type="radio"]') ) {
                    value = sender.is(':checked') ? value : false;
                }

                if ( ! this.model[this.current] ) {
                    this.model[this.current] = {};
                }
                this.model[this.current][name] = value;
            },

            getModel: function() {
                return this.model;
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
