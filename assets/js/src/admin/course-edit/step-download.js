/* global CoursePress */

(function() {
    'use strict';

    CoursePress.Define( 'Step_DOWNLOAD', function() {
        return CoursePress.View.extend({
            template_id: 'coursepress-step-download',
            stepView: false,
            events: {
                'change [name="meta_show_content"]': 'toggleContent',
                'change [name]': 'updateModel'
            },
            initialize: function( model, stepView ) {
                this.stepView = stepView;
                this.on( 'view_rendered', this.setUI, this );
                this.render();
            },
            setUI: function() {
                var self = this;

                this.description = this.$('.cp-step-description');
                this.visualEditor({
                    container: this.description,
                    content: this.model.get('post_content'),
                    callback: function( content ) {
                        self.model.set( 'post_content', content );
                    }
                });
                this.source = new CoursePress.AddMedia( this.$('.cp-add-media') );
            },
            toggleContent: function(ev) {
                var sender = this.$(ev.currentTarget),
                    is_checked = sender.is(':checked'),
                    content = this.$('.cp-step-description');

                if ( is_checked ) {
                    content.slideDown();
                } else {
                    content.slideUp();
                }
            },
            updateModel: function(ev) {
                this.stepView.updateModel(ev);
            }
        });
    });
})();