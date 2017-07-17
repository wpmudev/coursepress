/* global CoursePress */

(function() {
    'use strict';

    CoursePress.Define( 'Step', function( $ ) {
        var Module;

        Module = CoursePress.Request.extend({
            defaults: {
                type: 'text',
                post_title: 'Untitled',
                post_content: '',
                show_title: 1,
                meta_show_title: 1,
                menu_order: 1,
                meta_menu_order: 1,
                mandatory: false,
                meta_mandatory: false,
                show_content: true,
                meta_show_content: true,
                allowed_file_types: ['image', 'pdf', 'zip'],
                meta_allowed_file_types: ['image', 'pdf', 'zip']
            }
        });

       return CoursePress.View.extend({
           template_id: 'coursepress-step-tpl',
           type: 'text',
           className: 'unit-step-module open',
           stepController: false,
           events: {
               'click .step-toggle-button': 'toggleContents',
               'click .step-config button': 'toggleDropdown',
               'click .menu-item-delete': 'removeStep',
               'click .menu-item-duplicate': 'duplicateStep'
           },
           initialize: function(model, stepController) {
               this.model = new Module(model);
               this.type = this.model.get('type');
               this.stepController = stepController;
               this.on( 'view_rendered', this.setStep, this );
               this.render();
           },
           setStep: function() {
               var step, self, has_modules, move_item;

               self = this;
               step = 'Step_' + this.type.toUpperCase();
               step = new CoursePress[step]( this.model, this );
               step.$el.appendTo(this.$('.cp-step-content'));

               this.unitSteps = $('.unit-steps');
               this.unitSteps.sortable({
                   axis: 'y',
                   stop: function() {
                       self.reOrderSequence();
                   }
               });

               move_item = this.$('.menu-item-move');
               has_modules = this.stepController.unitModel.controller.editCourse.model.get('with_modules');

               if ( has_modules ) {
                   move_item.show();
               } else {
                   move_item.hide();
               }
           },
           toggleContents: function() {
               if ( this.$el.is('.open') ) {
                   this.$el.removeClass('open');
               } else {
                   this.$el.addClass('open');
               }
           },
           reOrderSequence: function() {},
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
               this.remove();
           },
           duplicateStep: function() {
               var step = new CoursePress.Step( this.model.toJSON(), this.stepController );
               step.$el.appendTo($('.unit-steps'));
               this.$('.step-config').removeClass('open');
           }
       });
    });
})();