/* global CoursePress */

(function() {
    'use strict';

    CoursePress.Define( 'Step_VIDEO', function() {
       return CoursePress.View.extend({
           template_id: 'coursepress-step-video',
           initialize: function( model ) {
               this.model = model;

               this.on( 'view_rendered', this.setUI, this );
               this.render();
           },
           setUI: function() {
           }
       });
    });
})();