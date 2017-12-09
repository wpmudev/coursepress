/* global CoursePress, wp */

(function() {
    'use strict';

    CoursePress.Define( 'AddMedia', function( $, doc, win ) {
        return CoursePress.View.extend({
            frame: null,
            template_id: 'coursepress-add-media-tpl',
            className: 'cp-add-media-box',
            type: 'video',
            events: {
                'click .cp-browse-btn': 'toggleFrame'
            },

            initialize: function(element) {
                this.element = element;
                this.data = element.data();
                this.model = {
                    placeholder: element.data('placeholder')
                };
                this.on( 'view_rendered', this.setUI, this );
                this.render();
            },

            setUI: function() {
                this.element.hide();
                this.$el.insertAfter( this.element );
                this.input = this.$('.cp-add-media-input');
                this.input.val(this.element.val());
            },

            toggleFrame: function() {
                if ( ! win.wp || ! win.wp.media ) {
                    return; // @todo: show graceful error
                }

	            var frameTitle = this.input.data('title') ? this.input.data('title') : '';

	            if ( ! this.frame ) {
                    var settings = {
                        frame: 'select',
                        title: frameTitle,
                        library: {type: [this.data.type]}
                    };

                    this.frame = new wp.media(settings);
                    this.frame.on('select', this.setSelected, this);
                }

                this.frame.open();
            },

            setSelected: function() {
                var selected, id;

                selected = this.frame.state().get('selection').first();
                id = selected.get('id');

                this.input.val( selected.attributes.url );
                this.element.val( selected.attributes.url );
                this.element.trigger( 'change' );
            }
        });
    });
})();
