var CoursePress = CoursePress || {};

(function($){


    CoursePress.event_queue = CoursePress.event_queue || []; //array

    CoursePress.editor = CoursePress.editor || {};

    CoursePress.editor.init_mode = getUserSetting('editor');

    CoursePress.editor.create = function ( target, id, content, append ) {

        if( undefined === tinyMCEPreInit ) {
            return false;
        }

        if( undefined === append ) {
            append = true;
        } else {
            append = false;
        }

        id = id.replace( /\#/g, '' );

        var editor = _coursepress._dummy_editor;
        editor = editor.replace( /EDITORID/g, id );
        editor = editor.replace( /CONTENT/g, content );

        if( append ) {
            $( target ).append( editor );
        } else {
            $( target ).replaceWith( editor );
        }

        var options = JSON.parse(JSON.stringify(tinyMCEPreInit.mceInit['EDITORID']));
        if( undefined !== options ) {
            options.body_class = options.body_class.replace( /EDITORID/g, id );
            options.selector = options.selector.replace( /EDITORID/g, id );
            options.init_instance_callback = 'CoursePress.editor.on_init'; // code to execute after editor is created
            tinyMCE.init( options );
            tinyMCEPreInit.mceInit[ id ] = options;
        }

        var options = JSON.parse(JSON.stringify(tinyMCEPreInit.qtInit['EDITORID']));
        if( undefined !== options ) {
            options.id = id;
            quicktags( options );
            tinyMCEPreInit.qtInit[ id ] = options;
        }
        QTags._buttonsInit();

        return true;
    }

    CoursePress.editor.content = function ( id, content ) {

        var mode = 'get';
        if( undefined !== content ) {
            mode = 'set'
        }

        if( undefined === tinyMCE ) {
            if( 'set' === mode ) {
                $( id ).val( content );
            }
            return $( id ).val();
        } else {
            if( 'set' === mode ) {
                tinyMCE.get( id ).setContent( content );
            }
            return tinyMCE.get( id ).getContent();
        }

    }

    CoursePress.editor.on_init = function( instance ) {

        // Fix up QT focus by "clicking" the button to fire switchEditors magic
        // Caveat, it all depends what the initial editor mode and will render all dynamic editors using current mode
        // initially.
        var mode = CoursePress.editor.init_mode;
        var qt_button_id = "#" + instance.id + '-html';

        if( 'html' === mode ) {
            $( qt_button_id ).click();
        }
    }

    // Add utility functions
    // not getting used at the moment, but keeping the code for later
    CoursePress.utility = CoursePress.utility || {};
    CoursePress.utility.merge_distinct = function( array1, array2 ) {
        var merged = array1;

        $.each( array2, function( key, value ) {
            if ( $.isArray( value ) && $.isArray( merged [ key ] ) ) {
                merged[ key ] = CoursePress.utility.merge_distinct( merged[ key ], value );
            } else {
                merged[ key ] = value;
            }
        } );
        return merged;
    }


})(jQuery);