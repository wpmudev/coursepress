/* global CoursePress, _, wp */

(function() {
    'use strict';

    CoursePress.Define( 'AddVideo', function($, doc, win) {
       var in_frame;

       // Determine whether or not the selected is from the frame
       in_frame = false;

       return CoursePress.View.extend({
           template_id: 'coursepress-add-video-tpl',
           input: false,
           events: {
               'change .cp-video-url': 'updateInput',
               'click .cp-btn-browse': 'selectVideo',
               'click .cp-btn-clear': 'clearSelection'
           },
           data: {
               title: win._coursepress.text.media.select_video
           },
           initialize: function(input) {
               this.input = input.hide();

               if ( this.input.data('title') ) {
                   this.data.title = this.input.data('title');
               }
               if ( this.input.data('size') ) {
                   this.data.size = this.input.data('size');
               }

               this.render();
           },
           render: function() {
               var html, data, value;
               value = this.input.val();

               data = {name: this.input.attr('name'), value: value};
               html = _._getTemplate(this.template_id, data);

               this.setElement(html);
               this.$el.insertAfter(this.input);

               this.video_url_input = this.$('.cp-video-url');
           },
           updateInput: function(ev) {
               var input = $(ev.currentTarget);
               this.input.val(input.val());

               this.input.trigger('change');
           },
           selectVideo: function() {

               if ( ! win.wp || ! win.wp.media ) {
                   return; // @todo: show graceful error
               }

	           var frameTitle = this.input.data('title') ? this.input.data('title') : '';

               if ( ! this.frame ) {
                   var settings = {
                       frame: 'select',
                       title: frameTitle,
                       library: {
                           type: [ 'video' ]
                       }
                   };

                   this.frame = new wp.media(settings);

                   this.frame.on('open', this.openMediaFrame, this);
                   this.frame.on('select', this.setSelectedVideo, this);
               }
               this.frame.open();
           },
           openMediaFrame: function() {},
           setSelectedVideo: function() {
               var selected, id, url;

               selected = this.frame.state().get('selection').first();
               id = selected.get('id');

               in_frame = true;

               // We need only videos.
               if ( typeof selected.attributes.type !== 'undefined' && selected.attributes.type === 'video' ) {
                   url = selected.attributes.url;

                   // Set correct url value
                   this.input.val( url );

                   this.video_url_input.val( url );
                   this.video_url_input.trigger( 'change' );
                   this.input.trigger( 'change' );
               } else {
                   // TODO
               }

               // Restore before closing wpmedia
               in_frame = false;
           },
           clearSelection: function() {
               this.video_url_input.val('');
               this.input.val('');
           }
       });
    });
})();
