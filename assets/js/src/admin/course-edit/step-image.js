/* global CoursePress */

(function() {
    'use strict';

    CoursePress.Define( 'Step_IMAGE', function() {
        return CoursePress.View.extend({
            template_id: 'coursepress-step-image',
            stepModel: false,
            events: {
                'change [name="meta_show_media_caption"]': 'toggleMediaCaption',
                'change [name="meta_caption_field"]': 'toggleCustomCaption',
                'change [name]': 'updateModel'
            },
            initialize: function( model, stepModel ) {
                this.stepModel = stepModel;
                this.on( 'view_rendered', this.setUI, this );
                this.render();
            },
            setUI: function() {
                this.image = new CoursePress.AddImage( this.$('.cp-add-image-input') );
            },
            toggleMediaCaption: function(ev) {
                var sender = this.$(ev.currentTarget),
                    is_checked = sender.is(':checked'),
                    div = this.$('.image-custom-caption');

                div[ is_checked ? 'slideDown' : 'slideUp' ]();
            },
            toggleCustomCaption: function() {
                var checked = this.$('[name="meta_caption_field"]:checked'),
                    input = this.$('[name="meta_caption_custom_text"]');

                if ( 'custom' === checked.val() ) {
                    input.removeAttr('disabled').focus();
                } else {
                    input.attr('disabled', 'disabled');
                }
            },
            updateModel: function(ev) {
                this.stepModel.updateModel(ev);
            }
        });
    });
})();