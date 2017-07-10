/* global CoursePress, _, Backbone */

(function(){
    'use strict';

    CoursePress.Define('View', function ( $, doc, win ) {
        _.mixin({
            isTrue: function (value, selected) {
                if (_.isArray(selected) ) {
                    return _.contains(selected, value);
                } else if (_.isObject(selected ) ) {
                    return !!selected[value];
                } else {
                    if ( _.isBoolean( value ) ) {
                        selected = parseInt(selected, 10) > 0 ? true : false;
                    }
                    return value === selected;
                }
            },
            checked: function (value, selected) {
                return _.isTrue(value, selected) ? 'checked="checked"' : '';
            },
            selected: function (value, selected) {
                return _.isTrue(value, selected) ? 'selected="selected"' : '';
            },
            _getTemplate: function (template_id, data) {
                var settings = {
                        evaluate: /<#([\s\S]+?)#>/g,
                        interpolate: /\{\{\{([\s\S]+?)\}\}\}/g,
                        escape: /\{\{([^\}]+?)\}\}(?!\})/g
                    },
                    tpl = $('#' + template_id);

                if ( tpl.length ) {
                    tpl = _.template( tpl.html(), null, settings);
                }

                return tpl(data);
            }
        });

        return Backbone.View.extend({
            template_id: '',
            model: {},
            events: {
                'change [name]': 'updateModel',
                'focus [name]': 'removeErrorMarker'
            },
            initialize: function () {
                if (arguments && arguments[0]) {
                    this.model = new CoursePress.Request(arguments[0]);
                }
                this.render();
            },
            render: function () {
                if ( ! _.isEmpty(this.template_id) ) {
                    var data = !!this.model.get ? this.model.toJSON() : this.model;
                    this.$el.html(_._getTemplate(this.template_id, data));
                }

                this.trigger( 'view_rendered' );

                /**
                 * Trigger whenever the view template is loaded
                 */
                CoursePress.Events.trigger('coursepress:view_rendered', this);

                return this;
            },
            updateModel: function(ev) {
                var input, name, type, value;

                input = $(ev.currentTarget);
                name = input.attr('name');

                if ( ( type = input.attr('type') ) &&
                    _.contains(['checkbox', 'radio'], type ) ) {
                    value = input.is(':checked') ? input.val() : false;
                } else {
                    value = input.val();
                }

                if ( !!this.model.get ) {
                    this.model.set(name, value);
                } else {
                    this.model[name] = value;
                }
            },
            removeErrorMarker: function( ev ) {
                var sender = this.$(ev.currentTarget),
                    error = sender.parents('.cp-error');

                if ( error.length ) {
                    error.removeClass('cp-error');
                }
            },
            setEditor: function( editor_id ) {
                var self = this;

                if ( win.tinyMCE && win.tinyMCE.get( editor_id ) ) {
                    this._setEditor( editor_id );
                } else {
                    this.$('#wp-' + editor_id + '-wrap .switch-tmce' ).one( 'click', function() {
                        _.delay(function() {
                            self._setEditor(editor_id);
                        }, 100 );
                    });
                }
            },
            _setEditor: function( editor_id ) {
                var content, textarea, self;

                self = this;

                if ( win.tinyMCE && win.tinyMCE.get( editor_id ) ) {
                    var editor = win.tinyMCE.get( editor_id );
                    editor.on( 'change', function() {
                        content = editor.getContent();
                        textarea = self.$('#' + editor_id );
                        textarea.val( content );
                        textarea.trigger('change');
                    });
                }
            }
        });
    });
})();