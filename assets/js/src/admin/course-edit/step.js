/* global CoursePress */

(function() {
    'use strict';

    CoursePress.Define( 'Step', function( $, doc, win ) {
        var Module;

        Module = CoursePress.Request.extend({});

       return CoursePress.View.extend({
           template_id: 'coursepress-step-tpl',
           type: 'text',
           className: 'unit-step-module open',
           stepController: false,
           events: {
               'click .step-toggle-button': 'toggleContents',
               'click .step-config button': 'toggleDropdown',
               'click .menu-item-delete': 'removeStep',
               'click .menu-item-duplicate': 'duplicateStep',
               'focus [name]': 'removeErrorMarker',
               'change [name]': 'updateModel'
           },

           initialize: function(model, stepController) {

	           if (model.get) {
		           model = model.toJSON();
	           }
               var default_vars = this.getDefaults();
	           model = _.extend({}, default_vars[model.module_type], model);
	           this.model = new Module(model);
	           this.type = this.model.get('module_type');
	           this.model.set('cid', this.model.cid);

               this.stepController = stepController;
               this.on( 'view_rendered', this.setStep, this );
               this.render();
           },

           setStep: function() {
               var step, self, has_modules, move_item;

               self = this;
               step = 'Step_' + this.type.toUpperCase();

               if ( ! CoursePress[step] ) {
                   return;
               }

               step = new CoursePress[step]({model: this.model}, this );
               step.$el.appendTo(this.$('.cp-step-content'));

               _.delay(function() {
                   self.unitSteps = $('.unit-steps');
                   self.unitSteps.sortable({
                       axis: 'y',
                       stop: function () {
                           self.reOrderSequence();
                       }
                   });
               }, 100 );


               move_item = this.$('.menu-item-move');
               has_modules = this.stepController.unitModel.editCourseView.model.get('meta_with_modules');

               if ( has_modules ) {
                   move_item.show();
               } else {
                   move_item.hide();
               }

               CoursePress.Events.trigger( 'coursepress:step_rendered', this );

               return step;
           },

           getDefaults: function () {
               var defaults, default_vars;

               defaults = {
                   post_title: win._coursepress.text.untitled,
                   post_content: '',
                   show_title: true,
                   meta_show_title: true,
                   mandatory: false,
                   meta_mandatory: false,
                   assessable: false,
                   meta_assessable: false,
                   show_content: false,
                   meta_show_content: false,
                   allow_retries: false,
                   meta_allow_retries: false,
                   retry_attempts: 0,
                   meta_retry_attempts: 0,
                   minimum_grade: 100,
                   meta_minimum_grade: 100
               };

               default_vars = {
                   text: _.extend({
                       module_type: 'text',
                       meta_module_type: 'text'
                   }, defaults),
                   image: _.extend({}, defaults, {
                       module_type: 'image',
                       meta_module_type: 'image',
                       image_url: '',
                       meta_image_url: '',
                       caption_field: 'media',
                       meta_caption_field: 'media',
                       caption_custom_text: '',
                       meta_caption_custom_text: '',
                       image_url_thumbnail_id: 0,
                       meta_image_url_thumbnail_id: 0,
                       show_media_cation: false,
                       meta_show_media_caption: false
                   }),
                   'input-upload': _.extend({
                       module_type: 'input-upload',
                       meta_module_type: 'input-upload',
                       allowed_file_types: ['image', 'pdf', 'zip'],
                       meta_allowed_file_types: ['image', 'pdf', 'zip']
                   }, defaults),
                   discussion: _.extend({
                       module_type: 'discussion',
                       meta_module_type: 'discussion'
                   }, defaults),
                   video: _.extend({
                       module_type: 'video',
                       meta_module_type: 'video',
                       video_url: '',
                       meta_video_url: '',
                       show_media_caption: false,
                       meta_show_media_caption: false,
                       video_player_width: 0,
                       meta_video_player_with: 0,
                       video_player_height: 0,
                       meta_video_player_height: 0,
                       video_autoplay: false,
                       meta_video_autoplay: false,
                       video_loop: false,
                       meta_video_loop: false,
                       video_hide_controls: false,
                       meta_video_hide_controls: false,
                       hide_related_media: 1,
                       meta_hide_related_media: 1
                   }, defaults),
                   audio: _.extend({
                       module_type: 'audio',
                       meta_module_type: 'audio',
                       audio_url: '',
                       meta_audio_url: '',
                       audio_url_thumbnail_id: 0,
                       meta_audio_url_thumbnail_id: 0,
                       loop: false,
                       meta_loop: false,
                       autoplay: false,
                       meta_autoplay: false
                   }, defaults),
                   download: _.extend({
                       module_type: 'download',
                       meta_module_type: 'download',
                       file_url: '',
                       meta_file_url: '',
                       link_text: '',
                       meta_link_text: ''
                   }, defaults),
                   zipped: _.extend({
                       module_type: 'zipped',
                       meta_module_type: 'zipped',
                       zip_url: '',
                       meta_zip_url: '',
                       primary_file: '',
                       meta_primary_file: '',
                       link_text: '',
                       meta_link_text: ''
                   }, defaults),
                   'input-quiz': _.extend({
                       module_type: 'input-quiz',
                       meta_module_type: 'input-quiz',
                       questions: []
                   }, defaults),
                   'input-written': _.extend({
                       module_type: 'input-written',
                       meta_module_type: 'input-written',
                       meta_questions: []
                   }, defaults)
               };

               return default_vars;
           },

           toggleContents: function() {
               if ( this.$el.is('.open') ) {
                   this.$el.removeClass('open');
               } else {
                   this.$el.addClass('open');
               }
           },

           reOrderSequence: function() {
               this.trigger( 'coursepress:step_reordered', this.model );
           },

           toggleDropdown: function(ev) {
               var sender = this.$(ev.currentTarget),
                   div = sender.parent(),
                   is_open = div.is('.open');

               if ( is_open ) {
                   div.removeClass('open');
               } else {
                   div.addClass('open');
               }
           },

           removeStep: function() {
               this.model.set( 'deleted', true );
               this.trigger( 'coursepress:model_updated', this.model, this );

               this.remove();
           },

           duplicateStep: function() {
               var newModel = JSON.parse(JSON.stringify(this.model));
	           newModel = _.omit(newModel, ['ID', 'cid']);

	           this.stepController.setStep(newModel);
	           this.stepController.reorderSteps();
           },

           toggleGreyBox: function(ev) {
               var sender, box, is_checked;

               sender = this.$(ev.currentTarget);
               is_checked = sender.is(':checked');
               box = sender.parents('div').first().next('.cp-box-grey');

               if ( box.length ) {
                   box[ is_checked ? 'slideDown' : 'slideUp']();
               }
           }
       });
    });
})();