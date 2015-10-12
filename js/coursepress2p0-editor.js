var CoursePress = CoursePress || {};

(function ( $ ) {

    CoursePress.Events = CoursePress.Events || _.extend( {}, Backbone.Events );

    CoursePress.editor = CoursePress.editor || {};

    if ( typeof getUserSetting !== 'undefined' ) {
        CoursePress.editor.init_mode = getUserSetting( 'editor' );
    }

    CoursePress.editor.create = function ( target, id, name, content, append, height ) {

        if ( undefined === height ) {
            height = 400;
        }

        var mceInit_object = 'dummy_editor_id';

        if ( undefined === tinyMCEPreInit ) {
            return false;
        }

        if( undefined === tinyMCEPreInit.mceInit[ mceInit_object ] ) {

            var keys = _.keys( tinyMCEPreInit.mceInit );

            if( keys.length === 0 ) {
                return false;
            } else {
                mceInit_object = keys[0];
            }

        }

        if ( undefined === append || true === append ) {
            append = true;
        } else {
            append = false;
        }

        id = id.replace( /\#/g, '' );

        var editor = _coursepress._dummy_editor;
        editor = editor.replace( /dummy_editor_id/g, id );
        editor = editor.replace( /dummy_editor_content/g, content );
        editor = editor.replace( /dummy_editor_name/g, name );
        editor = editor.replace( /rows="\d*"/g, 'style="height: ' + height + 'px"' ); // remove rows attribute
        editor = editor.replace( /wp-editor-area/, 'wp-editor-area ' + $( target ).attr('class') );

        if ( append ) {
            $( target ).append( editor );
        } else {
            $( target ).replaceWith( editor );
        }

        var options = JSON.parse( JSON.stringify( tinyMCEPreInit.mceInit[ mceInit_object ] ) );
        if ( undefined !== options ) {
            options.body_class = options.body_class.replace( /dummy_editor_id/g, id );
            options.selector = options.selector.replace( /dummy_editor_id/g, id );
            options.init_instance_callback = 'CoursePress.editor.on_init'; // code to execute after editor is created
            options.cache_suffix = '';
            options.setup = function ( ed ) {
                ed.on( 'keyup', function ( arr ) {
                    var content = CoursePress.editor.content( id );
                    $( '#' + id ).html( content );

                    CoursePress.Events.trigger( 'editor:keyup', ed );
                } );
            };
            tinyMCE.init( options );
            tinyMCEPreInit.mceInit[ id ] = options;
        }

        if( undefined !== tinyMCEPreInit.qtInit[ mceInit_object ] ) {
            var options = JSON.parse( JSON.stringify( tinyMCEPreInit.qtInit[ mceInit_object ] ) );
            if ( undefined !== options ) {
                options.id = id;
                options = quicktags( options );
                tinyMCEPreInit.qtInit[ id ] = options;
            }
        } else {
            tinyMCEPreInit.qtInit[ id ] = {};
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

    CoursePress.editor.set_height = function ( id, height ) {
        $( '#wp-' + id + '-editor-container' ).removeAttr( 'rows' );
        $( '#wp-' + id + '-wrap iframe' ).css( 'height', height + 'px' )
    }

    CoursePress.editor.on_init = function ( instance ) {

        var mode = CoursePress.editor.init_mode;
        var qt_button_id = "#" + instance.id + '-html';
        var mce_button_id = "#" + instance.id + '-tmce';
        var button_wrapper = "#wp-" + instance.id + '-editor-tools .wp-editor-tabs';

        // Old buttons has too much script behaviour associated with it, lets drop them
        $( qt_button_id ).detach();
        $( mce_button_id ).detach();

        var mce_button = '<button id="' + instance.id + '-visual' + '" class="wp-switch-editor switch-tmce" type="button">' + _coursepress.editor_visual + '</button>';
        var qt_button = '<button id="' + instance.id + '-text' + '" class="wp-switch-editor switch-html" type="button">' + _coursepress.editor_text + '</button>';

        // Add dummy button to deal with weird auto-clicking
        $( button_wrapper ).append( '<button class="hidden"></button>' );
        $( button_wrapper + ' [class="hidden"]' ).on( "click", function ( e ) {
            e.preventDefault();
            e.stopPropagation();
        } );

        $( button_wrapper ).append( mce_button );
        $( button_wrapper + ' #' + instance.id + '-visual' ).on( "click", function ( e ) {
            e.preventDefault();
            e.stopPropagation();
            switchEditors.go( instance.id, 'tmce' );
        } );
        $( button_wrapper ).append( qt_button );
        $( button_wrapper + ' #' + instance.id + '-text' ).on( "click", function ( e ) {
            e.preventDefault();
            e.stopPropagation();
            switchEditors.go( instance.id, 'html' );
        } );

        if ( 'html' === mode ) {
            $( button_wrapper + ' #' + instance.id + '-text' ).click();
        }

        CoursePress.Events.trigger( 'editor:created', instance );
    }

    CoursePress.editor.init = function( selector ) {

        selector = selector || '.coursepress-editor';

        // Bring on the Visual Editor

        // First get rid of some redundant data
        $.each( tinyMCEPreInit.mceInit, function ( subindex, subeditor ) {
            var subid = subeditor.selector.replace( '#', '' );
            if( subid !== 'dummy_editor_id' ) {
                try {
                    delete tinyMCEPreInit.mceInit[ subid ];
                    delete tinyMCEPreInit.qtInit[ subid ];
                    delete tinyMCE.EditorManager.editors[ subid ];

                    // Get rid of other redundancy
                    $.each( tinyMCE.EditorManager.editors, function ( idx ) {
                        try {
                            var eid = tinyMCE.EditorManager.editors[ idx ].id;
                            if ( subid === eid ) {
                                delete tinyMCE.EditorManager.editors[ idx ];
                            }
                            ;
                        } catch ( ei ) {
                        }
                    } );
                } catch ( e ) {
                }
            }
        } );

        $.each( $( selector ), function ( index, editor ) {
            var id = $( editor ).attr( 'id' );

            var content = $( '#' + id ).val();
            var name = $( editor ).attr( 'name' );
            var height = $( editor ).attr( 'data-height' ) ? $( editor ).attr( 'data-height' ) : 300;

            CoursePress.editor.create( editor, id, name, content, false, height );

            $( '[name="' + name + '"]' ).off('keyup');
            $( '[name="' + name + '"]' ).on('keyup', function( object ) {

                // Fix Enter/Return key
                if( 13 === object.keyCode ) {
                    $( this ).val( $( this ).val() + "\n" );
                }

                CoursePress.Events.trigger( 'editor:keyup', this );
            });

        } );

    }



})( jQuery );
