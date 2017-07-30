/* global CoursePress */

(function() {
    'use strict';

    CoursePress.Define( 'Step_VIDEO', function() {
       return CoursePress.View.extend({
           template_id: 'coursepress-step-video',
           video_source: false,
           stepView: false,
           events: {
               'change [name="meta_allow_retries"]': 'toggleGreyBox'
           },
           initialize: function( model, stepView ) {
               this.model = model;
               this.stepView = stepView;
               this.on( 'view_rendered', this.setUI, this );
               this.render();
           },
           setUI: function() {
               this.video_source = new CoursePress.AddVideo(this.$('.cp-add-video'));
           },
           toggleGreyBox: function(ev) {
               this.stepView.toggleGreyBox(ev);
           }
       });
    });
})();