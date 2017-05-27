/* global CoursePress */

CoursePress.Define( 'View', function($) {
    _.mixin({
        isTrue: function( value, selected ) {
            if ( _.isArray(selected) )
                return _.contains(selected, value);
            else if ( _.isObject(selected) )
                return !!selected[value];
            else
                return value === selected;
        },
        checked: function( value, selected ) {
            return _.isTrue( value, selected ) ? 'checked="checked"' : '';
        },
        selected: function( value, selected ) {
            return _.isTrue( value, selected ) ? 'selected="selected"' : '';
        },
        _getTemplate: function( template_id, data ) {
            var settings = {
                    evaluate:    /<#([\s\S]+?)#>/g,
                    interpolate: /\{\{\{([\s\S]+?)\}\}\}/g,
                    escape:      /\{\{([^\}]+?)\}\}(?!\})/g
                },
                tpl = _.template( $('#' + template_id ).html(), null, settings );

            return tpl(data);
        }
    });

    return Backbone.View.extend({
        template_id: '',
        model: {},
        initialize: function() {
            if ( arguments && arguments[0] ) {
                this.model = new CoursePress.Request(arguments[0]);
            }
            this.render();
        },
        render: function() {
            if ( this.template_id ) {
                var model = !!this.model.get ? this.model : this.model.toJSON();
                this.$el.html(_._getTemplate( this.template_id, model ));
            }
        }
    });
});