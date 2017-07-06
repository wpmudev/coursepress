/* global CoursePress, _, wp */

(function() {
    'use strict';

    CoursePress.Define( 'AddImage', function($, doc, win) {
       var frame, in_frame;

       // Determine whether or not the selected is from the frame
       in_frame = false;

       return CoursePress.View.extend({
           template_id: 'coursepress-add-image-tpl',
           input: false,
           events: {
               'change .cp-image-url': 'updateInput',
               'click .cp-btn-browse': 'selectImage',
               'click .cp-btn-clear': 'clearSelection'
           },
           data: {
               size: 'thumbnail',
               title: win._coursepress.text.media.select_image
           },
           initialize: function(input) {
               this.input = input.hide();

               if ( this.input.data('title') ) {
                   this.data.title = this.input.data('title');
               }
               if ( this.input.data('size') ) {
                   this.data.size = this.input.data('size');
               }

               this.thumbnail_id = this.input.attr('thumbnail');
               this.render();
           },
           render: function() {
               var html, data, thumbnail_id, value, src;
               thumbnail_id = this.input.data('thumbnail');
               value = src = this.input.val();

               data = {name: this.input.attr('name'), thumbnail_id: thumbnail_id, value: value};
               html = _._getTemplate(this.template_id, data);

               this.setElement(html);
               this.$el.insertAfter(this.input);
               this.thumbnail_box = this.$('.cp-thumbnail');

               this.image_id_input = this.$('.cp-thumbnail-id');
               this.image_id_input.off('change'); // Disable hooked change event
               this.image_id_input.on('change', this.input.prop('change'));
               this.image_url_input = this.$('.cp-image-url');

               if ( thumbnail_id ) {
                   this.image_id_input.val(thumbnail_id);
               }
               if ( src ) {
                   this.setThumbnail(src);
               }
           },
           updateInput: function(ev) {
               var input = $(ev.currentTarget);
               this.input.val(input.val());

               this.input.trigger('change');

               if ( ! in_frame ) {
                   this.image_id_input.val(0);
               }
               this.image_id_input.trigger('change');
           },
           selectImage: function() {

               if ( ! win.wp || ! win.wp.media ) {
                   return; // @todo: show graceful error
               }

               if ( ! frame ) {
                   var settings = {
                       frame: 'select',
                       title: this.data.title,
                       library: ['image']
                   };

                   frame = new wp.media(settings);

                   frame.on('open', this.openMediaFrame, this);
                   frame.on('select', this.setSelectedImage, this);
               }
               frame.open();
           },
           openMediaFrame: function() {
           },
           setSelectedImage: function() {
               var selected, thumbnail, id, url;

               selected = frame.state().get('selection').first();
               id = selected.get('id');

               in_frame = true;

               if ( !!selected.attributes.sizes.thumbnail ) {
                   thumbnail = selected.attributes.sizes.thumbnail.url;
                   this.setThumbnail(thumbnail);
               }

               url = selected.attributes.url;

               // Set correct url value
               this.input.val(url);

               this.image_url_input.val(url);
               this.image_url_input.trigger('change');
               this.image_id_input.val(id);
               this.image_id_input.trigger('change');
               this.input.trigger('change');

               // Restore before closing wpmedia
               in_frame = false;
           },
           setThumbnail: function(src) {
               this.thumbnail_box.css('background-image', 'url(' + src + ')');
           },
           clearSelection: function() {
               this.image_id_input.val('');
               this.image_url_input.val('');
               this.input.val('');
               this.thumbnail_box.css('background-image', '');
           }
       });
    });
})();