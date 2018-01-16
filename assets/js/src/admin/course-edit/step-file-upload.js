/* global CoursePress */

(function() {
    'use strict';

    CoursePress.Define( 'Step_INPUT-UPLOAD', function($) {
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
               var input, name, allowed_types_name, allowed_type_inputs, allowed_types;

               allowed_types_name = 'meta_allowed_file_types';
               input = $(ev.currentTarget);
               name = input.attr('name');

               if (name === allowed_types_name) {
                   allowed_types = [];
                   allowed_type_inputs = this.$('[name="' + allowed_types_name + '"]');
                   allowed_type_inputs.each(function (index, input) {
                       if ($(input).is(':checked')) {
                           allowed_types.push($(input).val());
                       }
                   });
                   this.model.set(allowed_types_name, allowed_types);
               }

               this.stepView.updateModel(ev);
           }
       });
    });
})();