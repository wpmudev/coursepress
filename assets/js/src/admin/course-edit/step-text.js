/* global CoursePress */

(function() {
    'use strict';

    CoursePress.Define( 'Step_TEXT', function() {
       return CoursePress.View.extend({
           stepView: false,
           initialize: function( model, stepView ) {
               this.stepView = stepView;
               this.render();
           },
           render: function() {
               var self = this;

               this.visualEditor({
                   content: this.model.get('post_content'),
                   container: this.$el,
                   callback: function( content ) {
                       // self.model.post_content = content;
                       self.model.set( 'post_content', content );
                       //self.stepView.model.set('post_content', content);
                       //self.stepView.trigger('coursepress:model_updated', self.stepView.model, self.stepView);
                   }
               });

               // Always show content.
               this.model.set('meta_show_content', true);
               this.model.set('show_content', true);
           }
       });
    });
})();