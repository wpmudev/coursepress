/* global CoursePress */

(function() {
    'use strict';

    CoursePress.Define( 'Step', function() {
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
                meta_mandatory: false
            }
        });

       return CoursePress.View.extend({
           template_id: 'coursepress-step-tpl',
           type: 'text',
           className: 'unit-step-module open',
           events: {
               'click .step-toggle-button': 'toggleContents'
           },
           initialize: function( model ) {
               this.model = new Module(model);
               this.type = this.model.get('type');
               this.on( 'view_rendered', this.setStep, this );
               this.render();
           },
           setStep: function() {
               var step;

               step = 'Step_' + this.type.toUpperCase();
               step = new CoursePress[step]( this.model );
               step.$el.appendTo( this.$('.cp-step-content' ) );
           },
           toggleContents: function() {
               if ( this.$el.is('.open' ) ) {
                   this.$el.removeClass('open');
               } else {
                   this.$el.addClass('open');
               }
           }
       });
    });
})();