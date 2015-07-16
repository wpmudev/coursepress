var CoursePress = CoursePress || {};

(function ( $ ) {


    CoursePress.event_queue = CoursePress.event_queue || []; //array

    CoursePress.editor = CoursePress.editor || {};

    CoursePress.editor.init_mode = getUserSetting( 'editor' );

    CoursePress.editor.create = function ( target, id, content, append ) {

        if ( undefined === tinyMCEPreInit ) {
            return false;
        }

        if ( undefined === append ) {
            append = true;
        } else {
            append = false;
        }

        id = id.replace( /\#/g, '' );

        var editor = _coursepress._dummy_editor;
        editor = editor.replace( /EDITORID/g, id );
        editor = editor.replace( /CONTENT/g, content );

        if ( append ) {
            $( target ).append( editor );
        } else {
            $( target ).replaceWith( editor );
        }

        var options = JSON.parse( JSON.stringify( tinyMCEPreInit.mceInit[ 'EDITORID' ] ) );
        if ( undefined !== options ) {
            options.body_class = options.body_class.replace( /EDITORID/g, id );
            options.selector = options.selector.replace( /EDITORID/g, id );
            options.init_instance_callback = 'CoursePress.editor.on_init'; // code to execute after editor is created
            tinyMCE.init( options );
            tinyMCEPreInit.mceInit[ id ] = options;
        }

        var options = JSON.parse( JSON.stringify( tinyMCEPreInit.qtInit[ 'EDITORID' ] ) );
        if ( undefined !== options ) {
            options.id = id;
            quicktags( options );
            tinyMCEPreInit.qtInit[ id ] = options;
        }
        QTags._buttonsInit();

        return true;
    }

    CoursePress.editor.content = function ( id, content ) {

        var mode = 'get';
        if ( undefined !== content ) {
            mode = 'set'
        }

        if ( undefined === tinyMCE ) {
            if ( 'set' === mode ) {
                $( id ).val( content );
            }
            return $( id ).val();
        } else {
            if ( 'set' === mode ) {
                tinyMCE.get( id ).setContent( content );
            }
            return tinyMCE.get( id ).getContent();
        }

    }

    CoursePress.editor.on_init = function ( instance ) {

        // Fix up QT focus by "clicking" the button to fire switchEditors magic
        // Caveat, it all depends what the initial editor mode and will render all dynamic editors using current mode
        // initially.
        var mode = CoursePress.editor.init_mode;
        var qt_button_id = "#" + instance.id + '-html';

        if ( 'html' === mode ) {
            $( qt_button_id ).click();
        }
    }

    // Add utility functions
    CoursePress.utility = CoursePress.utility || {};
    CoursePress.utility.merge_distinct = function ( array1, array2 ) {
        var merged = array1;

        $.each( array2, function ( key, value ) {
            if ( $.isArray( value ) && $.isArray( merged [ key ] ) ) {
                merged[ key ] = CoursePress.utility.merge_distinct( merged[ key ], value );
            } else {
                merged[ key ] = value;
            }
        } );
        return merged;
    }

    CoursePress.utility.update_object_by_path = function ( object, path, value ) {
        var stack = path.split( '/' );

        if( path === 'meta_course_category' ) {
            //console.log('MOO MOO MOO');
            //console.log( value );
        }

        while ( stack.length > 1 ) {
            var key = stack.shift();
            //console.log( key );
            if( object[ key ] ) {
                object = object[ key ];
            } else {
                object[key] = {};
                object = object[ key ];
            }
        }
        object[ stack.shift() ] = value;
    }

    CoursePress.utility.in_array = function ( value, array ) {
        return array.indexOf( value ) > -1;
    }

    CoursePress.utility.is_valid_url = function ( str ) {
        if ( str.indexOf( "http://" ) > -1 || str.indexOf( "https://" ) > -1 ) {
            return true;
        } else {
            return false;
        }
    }

    CoursePress.utility.valid_media_extension = function ( filename, type ) {
        type = $( type ).hasClass( 'image_url' ) ? 'image_url' : '';
        type = $( type ).hasClass( 'audio_url' ) ? 'audio_url' : type;
        type = $( type ).hasClass( 'video_url' ) ? 'video_url' : type;
        console.log( type );
        var extension = filename.split( '.' ).pop();
        var audio_extensions = _coursepress.allowed_audio_extensions;
        var video_extensions = _coursepress.allowed_video_extensions;
        var image_extensions = _coursepress.allowed_image_extensions;

        if ( type == 'featured_url' ) {
            type = 'image_url';
        }

        if ( type == 'course_video_url' ) {
            type = 'video_url';
        }

        if ( type == 'audio_url' ) {
            if ( CoursePress.utility.in_array( extension, audio_extensions ) ) {
                return true;
            } else {
                if ( CoursePress.utility.is_valid_url( filename ) && extension.length > 5 ) {
                    return true;
                } else {
                    if ( filename.length == 0 ) {
                        return true;
                    }
                    return false;
                }
            }
        }

        if ( type == 'video_url' ) {
            if ( CoursePress.utility.in_array( extension, video_extensions ) ) {
                return true;
            } else {
                if ( CoursePress.utility.is_valid_url( filename ) && extension.length > 5 ) {
                    return true;
                } else {
                    if ( filename.length == 0 ) {
                        return true;
                    }
                    return false;
                }
            }
        }

        if ( type == 'image_url' ) {
            if ( CoursePress.utility.in_array( extension, image_extensions ) ) {
                return true;
            } else {
                if ( CoursePress.utility.is_valid_url( filename ) && extension.length > 5 ) {
                    return true;
                } else {
                    if ( filename.length == 0 ) {
                        return true;
                    }
                    return false;
                }
            }
        }
    }

    CoursePress.UI = CoursePress.UI || {};

    // Add UI extensions
    $.fn.extend( {
            browse_media_field: function ( options ) {
                return this.each( function ( options ) {

                    $( this ).on( 'click', function () {

                        var text_selector = $( this ).attr( 'name' ).replace( '-button', '' );
                        var target_url_field = $( this ).parents( 'div' ).find( '#' + text_selector );

                        wp.media.string.props = function ( props, attachment ) {
                            $( target_url_field ).val( props.url );

                            if ( CoursePress.utility.valid_media_extension( attachment.url, target_url_field ) ) {//extension is allowed
                                $( target_url_field ).removeClass( 'invalid_extension_field' );
                                $( target_url_field ).parent().find( '.invalid_extension_message' ).hide();
                            } else {//extension is not allowed
                                $( target_url_field ).addClass( 'invalid_extension_field' );
                                $( target_url_field ).parent().find( '.invalid_extension_message' ).show();
                            }
                        }

                        wp.media.editor.send.attachment = function ( props, attachment ) {
                            $( target_url_field ).val( attachment.url );
                            if ( CoursePress.utility.valid_media_extension( attachment.url, target_url_field ) ) {//extension is allowed
                                $( target_url_field ).removeClass( 'invalid_extension_field' );
                                $( target_url_field ).parent().find( '.invalid_extension_message' ).hide();
                            } else {//extension is not allowed
                                $( target_url_field ).addClass( 'invalid_extension_field' );
                                $( target_url_field ).parent().find( '.invalid_extension_message' ).show();
                            }
                        };
                        console.log( wp );
                        wp.media.editor.open( target_url_field );
                        return false;

                    } );

                } );
            }
        }
    );

    $( '.certificate_background_button' ).on( 'click', function () {
        var target_url_field = $( this ).prevAll( ".certificate_background_url:first" );
        wp.media.string.props = function ( props, attachment ) {
            $( target_url_field ).val( props.url );

            if ( CoursePress.utility.valid_media_extension( attachment.url, target_url_field ) ) {//extension is allowed
                $( target_url_field ).removeClass( 'invalid_extension_field' );
                $( target_url_field ).parent().find( '.invalid_extension_message' ).hide();
            } else {//extension is not allowed
                $( target_url_field ).addClass( 'invalid_extension_field' );
                $( target_url_field ).parent().find( '.invalid_extension_message' ).show();
            }
        }

        wp.media.editor.send.attachment = function ( props, attachment ) {
            $( target_url_field ).val( attachment.url );
            if ( CoursePress.utility.valid_media_extension( attachment.url, target_url_field ) ) {//extension is allowed
                $( target_url_field ).removeClass( 'invalid_extension_field' );
                $( target_url_field ).parent().find( '.invalid_extension_message' ).hide();
            } else {//extension is not allowed
                $( target_url_field ).addClass( 'invalid_extension_field' );
                $( target_url_field ).parent().find( '.invalid_extension_message' ).show();
            }
        };

        wp.media.editor.open( this );
        return false;
    } );


})( jQuery );