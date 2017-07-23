/* global CoursePress */

(function() {
    'use strict';

    CoursePress.Define( 'Step_TEXT', function() {
       return CoursePress.View.extend({
           initialize: function( model ) {
               this.model = model;
               this.render();
           },
           render: function() {
               var self = this;

               this.visualEditor({
                   content: this.model.get('post_content'),
                   container: this.$el,
                   callback: function( content ) {
                       self.model.set( 'post_content', content );
                   }
               });
           }
       });
    });
})();