/* global CoursePress */

(function() {
    'use strict';

    CoursePress.Define( 'Step', function( $, doc, win ) {
        var Module;

        Module = CoursePress.Request.extend({
            defaults: {
                type: 'text',
                post_title: '',
                post_content: '',
                meta_show_title: true
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
               var tpl, self;

               self = this;
               tpl = _._getTemplate( 'coursepress-step-' + this.type, this.model.toJSON() );
               this.$('.cp-step-content').html( tpl );

               if ( this.$('.wp-editor-wrap' ).length ) {
                   var textareas = this.$('.wp-editor-wrap textarea');

                   _.each( textareas, function( textarea ) {
                       textarea = $(textarea);

                       var name = textarea.attr('name');

                       if ( win.tinyMCEPreInit ) {
                           var editor = win.tinyMCEPreInit.mceInit[name];
                           win.tinymce.init(editor);
                       }
                   }, this );
               }
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