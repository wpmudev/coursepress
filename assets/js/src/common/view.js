/* global CoursePress */

CoursePress.Define( 'View', function($, doc, win) {
    _.mixin({
        isTrue: function( value, selected ) {
            if ( _.isArray(selected) )
                return _.contains(selected, value);
            else if ( _.isObject(selected) )
                return selected[value] ? true : false;
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
        template_id: false,
        model: CoursePress.Request,
        initialize: function() {
            Backbone.View.prototype.initialize.apply( this, arguments );

            this.render();
        },
        render: function() {
            if ( this.template_id ) {
                this.$el.html( _._getTemplate( this.template_id, this.model.toJSON() ) );
            }
        }
    });
});