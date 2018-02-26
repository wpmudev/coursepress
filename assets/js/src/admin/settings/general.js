/* global CoursePress */

(function() {
    'use strict';

    CoursePress.Define( 'GeneralSettings', function( $ ) {
        return CoursePress.View.extend({
            template_id: 'coursepress-general-setting-tpl',
            el: $('#coursepress-setting-general'),
            events: {
                'change [name]': 'updateModel',
            },

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
            },
            updateModel: function(ev) {
                var sender = $(ev.currentTarget),
                    name = sender.attr('name'),
                    value = sender.val(),
                    first, model;

                if ( sender.is('[type="checkbox"]') ) {
                    value = sender.is(':checked') ? value : 0;
                }

                name = name.split('.');
                first = name.shift();
                model = this.model[first];

                if ( name.length ) {
                    _.each(name, function (t) {
                        model[t] = value;
                    }, this);
                    this.model[first] = model;
                } else {
                    this.model[first] = value;
                }
            },
        });
    });
})();
