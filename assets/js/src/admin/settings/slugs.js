/* global CoursePress */

(function() {
    'use strict';

    CoursePress.Define( 'SlugsSettings', function($) {
        return CoursePress.View.extend({
            template_id: 'coursepress-slugs-setting-tpl',
            el: $('#coursepress-setting-slugs'),
            events: {
                'change [name]': 'updateModel'
            },
            initialize: function( model, settingView ) {
                this.model = ! model ? {} : model;
                this.settingView = settingView;

                this.on( 'view_rendered', this.setUpUI, this );
                this.render();
            },
            setUpUI: function() {
                this.$('select').select2();
            },
            updateModel: function(ev) {
                var sender = $(ev.currentTarget),
                    name = sender.attr('name'),
                    value = sender.val(),
                    first, model;

                if ( sender.is('[type="checkbox"],[type="radio"]') ) {
                    value = sender.is(':checked') ? value : false;
                }

                name = name.split('.');
                first = name.shift();

                if ( this.model[ first ] ) {
                    model = this.model[ first ];
                } else {
                    model = {};
                }

                if ( name.length ) {
                    _.each(name, function (t) {
                        model[t] = value;
                    }, this);
                    this.model[first] = model;
                } else {
                    this.model[first] = value;
                }
            },
            getModel: function() {
                return this.model;
            }
        });
    });
})();
