/* global CoursePress, tinyMCE, tinyMCEPreInit, _coursepress*/
(function() {
    'use strict';

    CoursePress.Define('Editor', function ($) {
      return CoursePress.View.extend({
         template_id: 'coursepress-mini-visual-editor',
         init_mode: (undefined !== window.getUserSetting) ? window.getUserSetting( 'editor' ) : false,
         initialize: function(input) {
            this.editor_id = input.editor_id;
            this.height    = input.height;
            this.append    = input.append;
            this.editor_container    = input.editor_container;
            this.content    = input.content;
            this.render();
         },
         render: function() {
            var editor, id, height, content;
            if ( undefined === tinyMCEPreInit ) {
      			return false;
      		}
            height = ('undefined' !== this.height) ? this.height : 400;

            editor = _._getTemplate(this.template_id, {});
            id = this.editor_id.replace( /\#/g, '' );

            // editor.match(/id="wp-dummy_editor_id-wrap">(.*|\s)/)[1];

      		editor = editor.replace( /dummy_editor_id/g, id );
      		editor = editor.replace( /dummy_editor_name/g, name );
      		editor = editor.replace( /rows="\d*"/g, 'style="height: ' + height + 'px"' ); // remove rows attribute
            // Fix whitespace bug
      		editor = editor.replace( /<p>\s/g, '' );

            if ( 'undefined' !== this.append ) {
      			$( this.editor_container ).append( editor );
      		} else {
      			$( this.editor_container ).replaceWith( editor );
      		}

            content = _.unescape(this.content);
      		$('textarea#' + id ).val(content);

            var options = JSON.parse( JSON.stringify( tinyMCEPreInit.mceInit[ 'dummy_editor_id' ] ) );
      		if ( undefined !== options ) {
      			options.body_class = options.body_class.replace( /dummy_editor_id/g, id );
      			options.selector = options.selector.replace( /dummy_editor_id/g, id );
      			options.init_instance_callback = this.on_init; // code to execute after editor is created
      			options.cache_suffix = '';
      			options.setup = function( ed ) {
      				ed.on( 'keyup', function() {
      					CoursePress.Events.trigger('editor:keyup',ed);
      				} );

      				/**
      				 * Markup changing catch-all hack
      				 *
      				 * Basically, trick the CoursePress listeners into believing
      				 * that node changes are coming from a 'keyup' event. They don't necessarily do.
      				 *
      				 * Fixes: https://app.asana.com/0/47062597347068/118070383825539
      				 *
      				 * Downside: this will be triggered *a lot*. The overall performance might
      				 * benefit from debouncing the actual listeners.
      				 */
      				ed.on('NodeChange', function () {
      					CoursePress.Events.trigger('editor:keyup',ed);
      				});
      				// End of hack

      			};
      			// Don't forget to add the trigger to the textarea if TinyMCE is not used (QTags mode)
      			$('textarea#' + id ).on( 'keyup', function() {
      				CoursePress.Events.trigger('editor:keyup',this);
      			});
      			tinyMCE.init( options );
      			tinyMCEPreInit.mceInit[ id ] = options;
      		}

      		options = JSON.parse( JSON.stringify( tinyMCEPreInit.qtInit[ 'dummy_editor_id' ] ) );
      		if ( undefined !== options ) {
      			options.id = id;
      			options = window.quicktags( options );
      			tinyMCEPreInit.qtInit[ id ] = options;
      		}
      		window.QTags._buttonsInit();

            return true;
         },
         on_init: function( instance ) {
        		var mode = this.init_mode;
        		var qt_button_id = '#' + instance.id + '-html';
        		var mce_button_id = '#' + instance.id + '-tmce';
        		var button_wrapper = '#wp-' + instance.id + '-editor-tools .wp-editor-tabs';

        		// Old buttons has too much script behaviour associated with it, lets drop them
        		$( qt_button_id ).detach();
        		$( mce_button_id ).detach();

        		var mce_button = '<button id="' + instance.id + '-visual' + '" class="wp-switch-editor switch-tmce" type="button">' + _coursepress.editor_visual + '</button>';
        		var qt_button = '<button id="' + instance.id + '-text' + '" class="wp-switch-editor switch-html" type="button">' + _coursepress.editor_text + '</button>';

        		// Add dummy button to deal with weird auto-clicking
        		$( button_wrapper ).append( '<button class="hidden"></button>' );
        		$( button_wrapper + ' [class="hidden"]' ).on( 'click', function( e ) {
        			e.preventDefault();
        			e.stopPropagation();
        		} );

        		$( button_wrapper ).append( mce_button );
        		$( button_wrapper + ' #' + instance.id + '-visual' ).on( 'click', function( e ) {
        			e.preventDefault();
        			e.stopPropagation();
        			window.switchEditors.go( instance.id, 'tmce' );
        		} );
        		$( button_wrapper ).append( qt_button );
        		$( button_wrapper + ' #' + instance.id + '-text' ).on( 'click', function( e ) {
        			e.preventDefault();
        			e.stopPropagation();
        			window.switchEditors.go( instance.id, 'html' );
        		} );

        		if ( 'html' === mode ) {
        			$( button_wrapper + ' #' + instance.id + '-text' ).click();
        		}
        	}
      });
    });
})();
