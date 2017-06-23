/* global CoursePress */

(function() {
    'use strict';

    CoursePress.Define( 'GeneralSettings', function( $ ) {
        return CoursePress.View.extend({
            template_id: 'coursepress-general-setting-tpl',
            el: $('#coursepress-setting-general'),
            render: function() {
                CoursePress.View.prototype.render.apply( this );

                this.enableSelect2();
            },
            enableSelect2: function() {
                this.$('select').select2();
                this.$('.wpui-checkbox-wrapper')
            },
            getModel: function() {
                return this.model.toJSON();
            }
        });
    });
})();