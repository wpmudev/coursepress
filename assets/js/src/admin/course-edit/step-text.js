/* global CoursePress */

(function() {
    'use strict';

    CoursePress.Define( 'Step_TEXT', function( $, doc, win ) {
       return CoursePress.View.extend({
           initialize: function( model ) {
               this.model = model;
               this.render();
           },
           render: function() {
               var id = 'post_editor_' + this.model.get('menu_order');
               this.visualEditor({
                   id: id,
                   content: '',
                   container: this.$el,
                   callback: function( content ) {

                   }
               });
           },
           render33: function() {
               var tpl, id, settings, mceinit, qtinit;

               if ( win.tinyMCEPreInit ) {
                   mceinit = win.tinyMCEPreInit.mceInit['post_content'];
                   qtinit = win.tinyMCEPreInit.qtInit['post_content'];
               }

               tpl = $('#' + this.template_id).html();
               id = 'post_editor_' + this.model.get('menu_order');
               tpl = tpl.replace( /post_editor/g, id );
               settings = {
                   evaluate: /<#([\s\S]+?)#>/g,
                   interpolate: /\{\{\{([\s\S]+?)\}\}\}/g,
                   escape: /\{\{([^\}]+?)\}\}(?!\})/g
               };
               tpl = _.template( tpl, null, settings );
               tpl = tpl( this.model.toJSON() );
               this.$el.append(tpl);

               mceinit.selector = '#' + id;
               qtinit.id = id;
               win.tinyMCEPreInit.mceInit[id] = mceinit;
               win.tinyMCEPreInit.qtInit[id] = qtinit;

               _.delay(function() {
                   win.tinymce.init(mceinit);
                   win.quicktags(qtinit);
               }, 100 );
           }
       });
    });
})();