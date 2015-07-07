var CoursePress = CoursePress || {};

(function($){

    CoursePress.editor = function ( target, id, content, append ) {

        if( undefined === tinyMCEPreInit ) {
            return false;
        }

        if( undefined === append ) {
            append = true;
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

        var options = tinyMCEPreInit.mceInit['EDITORID'];
        if( undefined !== options ) {
            options.body_class = options.body_class.replace( /EDITORID/g, id );
            options.selector = options.selector.replace( /EDITORID/g, id );
            tinyMCE.init( options );
        }

        options = tinyMCEPreInit.qtInit['EDITORID'];
        if( undefined !== options ) {
            options.id = id;
            quicktags( options );
        }

    }

    // Add utility functions
    // not getting used at the moment, but keeping the code for later
    CoursePress.utility = CoursePress.utility || {};
    CoursePress.utility.merge_distinct = function( array1, array2 ) {
        var merged = array1;

        $.each( array2, function( key, value ) {
            if ( $.isArray( value ) && $.isArray( merged [ key ] ) ) {
                merged[ key ] = merge_distinct( merged[ key ], value );
            } else {
                merged[ key ] = value;
            }
        } );
        return merged;
    }

})(jQuery);