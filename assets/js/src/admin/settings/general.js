/* global CoursePress */

(function() {
    'use strict';

    CoursePress.Define( 'GeneralSettings', function( $ ) {
        return CoursePress.View.extend({
            template_id: 'coursepress-general-setting-tpl',
            el: $('#coursepress-setting-general'),

            initialize: function( model ) {
                this.model = model;

                this.on( 'view_rendered', this.setUpUI, this );
                this.render();
            },

            setUpUI: function() {
                this.$('select').select2();
            },

            getModel: function() {
                return this.model;
            }
        });
    });
})();
