/* global CoursePress */

(function() {
    'use strict';

    CoursePress.Define( 'Step_INPUT-UPLOAD', function() {
       return CoursePress.View.extend({
           template_id: 'coursepress-step-file-upload',
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
           },
           toggleContent: function(ev) {
               var sender = this.$(ev.currentTarget),
                   is_checked = sender.is(':checked');

               this.description[is_checked ? 'slideDown' : 'slideUp']();
           },

           updateModel: function(ev) {
               this.stepView.updateModel(ev);
           }
       });
    });
})();