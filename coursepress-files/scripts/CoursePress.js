var CoursePress = CoursePress || {};

(function ( $ ) {


    CoursePress.Events = CoursePress.Events || _.extend( {}, Backbone.Events );

    CoursePress.editor = CoursePress.editor || {};

    if( typeof getUserSetting !== 'undefined' ) {
        CoursePress.editor.init_mode = getUserSetting( 'editor' );
    }

    CoursePress.editor.create = function ( target, id, name, content, append, height ) {

        if ( undefined === height ) {
            height = 400;
        }

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
        editor = editor.replace( /dummy_editor_id/g, id );
        editor = editor.replace( /dummy_editor_content/g, content );
        editor = editor.replace( /dummy_editor_name/g, name );
        editor = editor.replace( /rows="\d*"/g, 'style="height: ' + height + 'px"' ); // remove rows attribute

        if ( append ) {
            $( target ).append( editor );
        } else {
            $( target ).replaceWith( editor );
        }

        var options = JSON.parse( JSON.stringify( tinyMCEPreInit.mceInit[ 'dummy_editor_id' ] ) );
        if ( undefined !== options ) {
            options.body_class = options.body_class.replace( /dummy_editor_id/g, id );
            options.selector = options.selector.replace( /dummy_editor_id/g, id );
            options.init_instance_callback = 'CoursePress.editor.on_init'; // code to execute after editor is created
            options.cache_suffix = '';
            options.setup = function ( ed ) {
                ed.on( 'keyup', function ( arr ) {
                    CoursePress.Events.trigger('editor:keyup',ed);
                } );
            };
            tinyMCE.init( options );
            tinyMCEPreInit.mceInit[ id ] = options;
        }

        var options = JSON.parse( JSON.stringify( tinyMCEPreInit.qtInit[ 'dummy_editor_id' ] ) );
        if ( undefined !== options ) {
            options.id = id;
            options = quicktags( options );
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

        while ( stack.length > 1 ) {
            var key = stack.shift();
            //console.log( key );
            if ( object[ key ] ) {
                object = object[ key ];
            } else {
                object[ key ] = {};
                object = object[ key ];
            }
        }
        object[ stack.shift() ] = value;

    }


    CoursePress.utility.get_object_path = function ( object, search_key, search_value, base ) {

        if ( undefined === base ) {
            base = '';
        }

        keys = Object.keys( object );

        while ( keys.length > 0 ) {

            var key = keys.shift();

            if ( _.isObject( object[ key ] ) ) {
                if ( base.length !== 0 ) {
                    base = base + '/' + key;
                } else {
                    base = key;
                }
                return CoursePress.utility.get_object_path( object[ key ], search_key, search_value, base );
            } else {

                if ( key === search_key && object[ key ] === search_value ) {
                    return base + '/' + key;
                }
            }

        }

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
        type = $( type ).hasClass( 'image_url' ) ? 'image_url' : type;
        type = $( type ).hasClass( 'audio_url' ) ? 'audio_url' : type;
        type = $( type ).hasClass( 'video_url' ) ? 'video_url' : type;

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

        // None of the above, so lets check for custom extensions
        if( _coursepress.allowed_extensions ) {

            if ( CoursePress.utility.in_array( extension, _coursepress.allowed_extensions ) ) {
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


    // Unserialize method from phpjs.org
    CoursePress.utility.unserialize = function ( data ) {
        //  discuss at: http://phpjs.org/functions/unserialize/
        //   example 1: unserialize('a:3:{i:0;s:5:"Kevin";i:1;s:3:"van";i:2;s:9:"Zonneveld";}');
        //   returns 1: ['Kevin', 'van', 'Zonneveld']
        //   example 2: unserialize('a:3:{s:9:"firstName";s:5:"Kevin";s:7:"midName";s:3:"van";s:7:"surName";s:9:"Zonneveld";}');
        //   returns 2: {firstName: 'Kevin', midName: 'van', surName: 'Zonneveld'}
        var that = this,
            utf8Overhead = function ( chr ) {
                // http://phpjs.org/functions/unserialize:571#comment_95906
                var code = chr.charCodeAt( 0 );
                if ( code < 0x0080 ) {
                    return 0;
                }
                if ( code < 0x0800 ) {
                    return 1;
                }
                return 2;
            };
        error = function ( type, msg, filename, line ) {
            throw new that.window[ type ]( msg, filename, line );
        };
        read_until = function ( data, offset, stopchr ) {
            var i = 2,
                buf = [],
                chr = data.slice( offset, offset + 1 );

            while ( chr != stopchr ) {
                if ( (i + offset) > data.length ) {
                    error( 'Error', 'Invalid' );
                }
                buf.push( chr );
                chr = data.slice( offset + (i - 1), offset + i );
                i += 1;
            }
            return [ buf.length, buf.join( '' ) ];
        };
        read_chrs = function ( data, offset, length ) {
            var i, chr, buf;

            buf = [];
            for ( i = 0; i < length; i++ ) {
                chr = data.slice( offset + (i - 1), offset + i );
                buf.push( chr );
                length -= utf8Overhead( chr );
            }
            return [ buf.length, buf.join( '' ) ];
        };
        _unserialize = function ( data, offset ) {
            var dtype, dataoffset, keyandchrs, keys, contig,
                length, array, readdata, readData, ccount,
                stringlength, i, key, kprops, kchrs, vprops,
                vchrs, value, chrs = 0,
                typeconvert = function ( x ) {
                    return x;
                };

            if ( !offset ) {
                offset = 0;
            }
            dtype = (data.slice( offset, offset + 1 ))
                .toLowerCase();

            dataoffset = offset + 2;

            switch ( dtype ) {
                case 'i':
                    typeconvert = function ( x ) {
                        return parseInt( x, 10 );
                    };
                    readData = read_until( data, dataoffset, ';' );
                    chrs = readData[ 0 ];
                    readdata = readData[ 1 ];
                    dataoffset += chrs + 1;
                    break;
                case 'b':
                    typeconvert = function ( x ) {
                        return parseInt( x, 10 ) !== 0;
                    };
                    readData = read_until( data, dataoffset, ';' );
                    chrs = readData[ 0 ];
                    readdata = readData[ 1 ];
                    dataoffset += chrs + 1;
                    break;
                case 'd':
                    typeconvert = function ( x ) {
                        return parseFloat( x );
                    };
                    readData = read_until( data, dataoffset, ';' );
                    chrs = readData[ 0 ];
                    readdata = readData[ 1 ];
                    dataoffset += chrs + 1;
                    break;
                case 'n':
                    readdata = null;
                    break;
                case 's':
                    ccount = read_until( data, dataoffset, ':' );
                    chrs = ccount[ 0 ];
                    stringlength = ccount[ 1 ];
                    dataoffset += chrs + 2;

                    readData = read_chrs( data, dataoffset + 1, parseInt( stringlength, 10 ) );
                    chrs = readData[ 0 ];
                    readdata = readData[ 1 ];
                    dataoffset += chrs + 2;
                    if ( chrs != parseInt( stringlength, 10 ) && chrs != readdata.length ) {
                        error( 'SyntaxError', 'String length mismatch' );
                    }
                    break;
                case 'a':
                    readdata = {};

                    keyandchrs = read_until( data, dataoffset, ':' );
                    chrs = keyandchrs[ 0 ];
                    keys = keyandchrs[ 1 ];
                    dataoffset += chrs + 2;

                    length = parseInt( keys, 10 );
                    contig = true;

                    for ( i = 0; i < length; i++ ) {
                        kprops = _unserialize( data, dataoffset );
                        kchrs = kprops[ 1 ];
                        key = kprops[ 2 ];
                        dataoffset += kchrs;

                        vprops = _unserialize( data, dataoffset );
                        vchrs = vprops[ 1 ];
                        value = vprops[ 2 ];
                        dataoffset += vchrs;

                        if ( key !== i )
                            contig = false;

                        readdata[ key ] = value;
                    }

                    if ( contig ) {
                        array = new Array( length );
                        for ( i = 0; i < length; i++ )
                            array[ i ] = readdata[ i ];
                        readdata = array;
                    }

                    dataoffset += 1;
                    break;
                default:
                    error( 'SyntaxError', 'Unknown / Unhandled data type(s): ' + dtype );
                    break;
            }
            return [ dtype, dataoffset - offset, typeconvert( readdata ) ];
        };

        return _unserialize( (data + ''), 0 )[ 2 ];
    }


    // Webkit MD5 method
    CoursePress.utility.md5 = function ( s ) {
        function L( k, d ) {
            return (k << d) | (k >>> (32 - d))
        }

        function K( G, k ) {
            var I, d, F, H, x;
            F = (G & 2147483648);
            H = (k & 2147483648);
            I = (G & 1073741824);
            d = (k & 1073741824);
            x = (G & 1073741823) + (k & 1073741823);
            if ( I & d ) {
                return (x ^ 2147483648 ^ F ^ H)
            }
            if ( I | d ) {
                if ( x & 1073741824 ) {
                    return (x ^ 3221225472 ^ F ^ H)
                } else {
                    return (x ^ 1073741824 ^ F ^ H)
                }
            } else {
                return (x ^ F ^ H)
            }
        }

        function r( d, F, k ) {
            return (d & F) | ((~d) & k)
        }

        function q( d, F, k ) {
            return (d & k) | (F & (~k))
        }

        function p( d, F, k ) {
            return (d ^ F ^ k)
        }

        function n( d, F, k ) {
            return (F ^ (d | (~k)))
        }

        function u( G, F, aa, Z, k, H, I ) {
            G = K( G, K( K( r( F, aa, Z ), k ), I ) );
            return K( L( G, H ), F )
        }

        function f( G, F, aa, Z, k, H, I ) {
            G = K( G, K( K( q( F, aa, Z ), k ), I ) );
            return K( L( G, H ), F )
        }

        function D( G, F, aa, Z, k, H, I ) {
            G = K( G, K( K( p( F, aa, Z ), k ), I ) );
            return K( L( G, H ), F )
        }

        function t( G, F, aa, Z, k, H, I ) {
            G = K( G, K( K( n( F, aa, Z ), k ), I ) );
            return K( L( G, H ), F )
        }

        function e( G ) {
            var Z;
            var F = G.length;
            var x = F + 8;
            var k = (x - (x % 64)) / 64;
            var I = (k + 1) * 16;
            var aa = Array( I - 1 );
            var d = 0;
            var H = 0;
            while ( H < F ) {
                Z = (H - (H % 4)) / 4;
                d = (H % 4) * 8;
                aa[ Z ] = (aa[ Z ] | (G.charCodeAt( H ) << d));
                H++
            }
            Z = (H - (H % 4)) / 4;
            d = (H % 4) * 8;
            aa[ Z ] = aa[ Z ] | (128 << d);
            aa[ I - 2 ] = F << 3;
            aa[ I - 1 ] = F >>> 29;
            return aa
        }

        function B( x ) {
            var k = "", F = "", G, d;
            for ( d = 0; d <= 3; d++ ) {
                G = (x >>> (d * 8)) & 255;
                F = "0" + G.toString( 16 );
                k = k + F.substr( F.length - 2, 2 )
            }
            return k
        }

        function J( k ) {
            k = k.replace( /rn/g, "n" );
            var d = "";
            for ( var F = 0; F < k.length; F++ ) {
                var x = k.charCodeAt( F );
                if ( x < 128 ) {
                    d += String.fromCharCode( x )
                } else {
                    if ( (x > 127) && (x < 2048) ) {
                        d += String.fromCharCode( (x >> 6) | 192 );
                        d += String.fromCharCode( (x & 63) | 128 )
                    } else {
                        d += String.fromCharCode( (x >> 12) | 224 );
                        d += String.fromCharCode( ((x >> 6) & 63) | 128 );
                        d += String.fromCharCode( (x & 63) | 128 )
                    }
                }
            }
            return d
        }

        var C = Array();
        var P, h, E, v, g, Y, X, W, V;
        var S = 7, Q = 12, N = 17, M = 22;
        var A = 5, z = 9, y = 14, w = 20;
        var o = 4, m = 11, l = 16, j = 23;
        var U = 6, T = 10, R = 15, O = 21;
        s = J( s );
        C = e( s );
        Y = 1732584193;
        X = 4023233417;
        W = 2562383102;
        V = 271733878;
        for ( P = 0; P < C.length; P += 16 ) {
            h = Y;
            E = X;
            v = W;
            g = V;
            Y = u( Y, X, W, V, C[ P + 0 ], S, 3614090360 );
            V = u( V, Y, X, W, C[ P + 1 ], Q, 3905402710 );
            W = u( W, V, Y, X, C[ P + 2 ], N, 606105819 );
            X = u( X, W, V, Y, C[ P + 3 ], M, 3250441966 );
            Y = u( Y, X, W, V, C[ P + 4 ], S, 4118548399 );
            V = u( V, Y, X, W, C[ P + 5 ], Q, 1200080426 );
            W = u( W, V, Y, X, C[ P + 6 ], N, 2821735955 );
            X = u( X, W, V, Y, C[ P + 7 ], M, 4249261313 );
            Y = u( Y, X, W, V, C[ P + 8 ], S, 1770035416 );
            V = u( V, Y, X, W, C[ P + 9 ], Q, 2336552879 );
            W = u( W, V, Y, X, C[ P + 10 ], N, 4294925233 );
            X = u( X, W, V, Y, C[ P + 11 ], M, 2304563134 );
            Y = u( Y, X, W, V, C[ P + 12 ], S, 1804603682 );
            V = u( V, Y, X, W, C[ P + 13 ], Q, 4254626195 );
            W = u( W, V, Y, X, C[ P + 14 ], N, 2792965006 );
            X = u( X, W, V, Y, C[ P + 15 ], M, 1236535329 );
            Y = f( Y, X, W, V, C[ P + 1 ], A, 4129170786 );
            V = f( V, Y, X, W, C[ P + 6 ], z, 3225465664 );
            W = f( W, V, Y, X, C[ P + 11 ], y, 643717713 );
            X = f( X, W, V, Y, C[ P + 0 ], w, 3921069994 );
            Y = f( Y, X, W, V, C[ P + 5 ], A, 3593408605 );
            V = f( V, Y, X, W, C[ P + 10 ], z, 38016083 );
            W = f( W, V, Y, X, C[ P + 15 ], y, 3634488961 );
            X = f( X, W, V, Y, C[ P + 4 ], w, 3889429448 );
            Y = f( Y, X, W, V, C[ P + 9 ], A, 568446438 );
            V = f( V, Y, X, W, C[ P + 14 ], z, 3275163606 );
            W = f( W, V, Y, X, C[ P + 3 ], y, 4107603335 );
            X = f( X, W, V, Y, C[ P + 8 ], w, 1163531501 );
            Y = f( Y, X, W, V, C[ P + 13 ], A, 2850285829 );
            V = f( V, Y, X, W, C[ P + 2 ], z, 4243563512 );
            W = f( W, V, Y, X, C[ P + 7 ], y, 1735328473 );
            X = f( X, W, V, Y, C[ P + 12 ], w, 2368359562 );
            Y = D( Y, X, W, V, C[ P + 5 ], o, 4294588738 );
            V = D( V, Y, X, W, C[ P + 8 ], m, 2272392833 );
            W = D( W, V, Y, X, C[ P + 11 ], l, 1839030562 );
            X = D( X, W, V, Y, C[ P + 14 ], j, 4259657740 );
            Y = D( Y, X, W, V, C[ P + 1 ], o, 2763975236 );
            V = D( V, Y, X, W, C[ P + 4 ], m, 1272893353 );
            W = D( W, V, Y, X, C[ P + 7 ], l, 4139469664 );
            X = D( X, W, V, Y, C[ P + 10 ], j, 3200236656 );
            Y = D( Y, X, W, V, C[ P + 13 ], o, 681279174 );
            V = D( V, Y, X, W, C[ P + 0 ], m, 3936430074 );
            W = D( W, V, Y, X, C[ P + 3 ], l, 3572445317 );
            X = D( X, W, V, Y, C[ P + 6 ], j, 76029189 );
            Y = D( Y, X, W, V, C[ P + 9 ], o, 3654602809 );
            V = D( V, Y, X, W, C[ P + 12 ], m, 3873151461 );
            W = D( W, V, Y, X, C[ P + 15 ], l, 530742520 );
            X = D( X, W, V, Y, C[ P + 2 ], j, 3299628645 );
            Y = t( Y, X, W, V, C[ P + 0 ], U, 4096336452 );
            V = t( V, Y, X, W, C[ P + 7 ], T, 1126891415 );
            W = t( W, V, Y, X, C[ P + 14 ], R, 2878612391 );
            X = t( X, W, V, Y, C[ P + 5 ], O, 4237533241 );
            Y = t( Y, X, W, V, C[ P + 12 ], U, 1700485571 );
            V = t( V, Y, X, W, C[ P + 3 ], T, 2399980690 );
            W = t( W, V, Y, X, C[ P + 10 ], R, 4293915773 );
            X = t( X, W, V, Y, C[ P + 1 ], O, 2240044497 );
            Y = t( Y, X, W, V, C[ P + 8 ], U, 1873313359 );
            V = t( V, Y, X, W, C[ P + 15 ], T, 4264355552 );
            W = t( W, V, Y, X, C[ P + 6 ], R, 2734768916 );
            X = t( X, W, V, Y, C[ P + 13 ], O, 1309151649 );
            Y = t( Y, X, W, V, C[ P + 4 ], U, 4149444226 );
            V = t( V, Y, X, W, C[ P + 11 ], T, 3174756917 );
            W = t( W, V, Y, X, C[ P + 2 ], R, 718787259 );
            X = t( X, W, V, Y, C[ P + 9 ], O, 3951481745 );
            Y = K( Y, h );
            X = K( X, E );
            W = K( W, v );
            V = K( V, g )
        }
        var i = B( Y ) + B( X ) + B( W ) + B( V );
        return i.toLowerCase()
    };

    CoursePress.utility.get_gravatar = function ( email, size, default_image, allowed_rating, force_default ) {
        email = typeof email !== 'undefined' ? email : 'john.doe@example.com';
        size = (size >= 1 && size <= 2048) ? size : 80;
        default_image = typeof default_image !== 'undefined' ? default_image : 'mm';
        allowed_rating = typeof allowed_rating !== 'undefined' ? allowed_rating : 'x';
        force_default = force_default === true ? 'y' : 'n';

        return ("https://secure.gravatar.com/avatar/" + CoursePress.utility.md5( email.toLowerCase().trim() ) + "?size=" + size + "&default=" + encodeURIComponent( default_image ) + "&rating=" + allowed_rating + (force_default === 'y' ? "&forcedefault=" + force_default : ''));
    }

    CoursePress.utility.get_gravatar_image = function ( email, size, alt, default_image, allowed_rating, force_default ) {
        var url = CoursePress.utility.get_gravatar( email, size, default_image, allowed_rating, force_default );

        alt = typeof alt !== 'undefined' ? alt : '';

        return '<img class="avatar avatar-' + size + ' photo" width="' + size + '" height="' + size + '" srcset="' + url + ' 2x" src="' + url + '" alt="' + alt + '">';
    }

    CoursePress.utility.fix_checkboxes = function ( items, selector, false_value ) {
        var meta_items = $( selector + ' [name^="meta_"]' );

        if ( undefined === false_value ) {
            false_value = false;
        }

        $.each( meta_items, function ( index, element ) {
            var name = $( element ).attr( 'name' );
            if ( 'checkbox' === element.type && undefined === _.findWhere( items, { name: name } ) ) {
                items.push( { name: name, value: false_value } );
            }
        } );

        return items;
    }

    CoursePress.utility.is_equal = function ( current_val, expected_val, strict ) {
        if ( undefined === strict ) {
            return current_val == expected_val;
        } else {
            return current_val === expected_val;
        }

    }

    CoursePress.utility.checked = function ( current_val, expected_val ) {
        return CoursePress.utility.is_equal( current_val, expected_val ) ? 'checked="checked"' : '';
    }

    CoursePress.utility.event_supported = function () {
        var TAGNAMES = {
            'select': 'input', 'change': 'input',
            'submit': 'form', 'reset': 'form',
            'error': 'img', 'load': 'img', 'abort': 'img', 'click': 'textarea'
        }

        function isEventSupported( eventName ) {
            var el = document.createElement( TAGNAMES[ eventName ] || 'div' );
            eventName = 'on' + eventName;
            var isSupported = (eventName in el);
            if ( !isSupported ) {
                el.setAttribute( eventName, 'return;' );
                isSupported = typeof el[ eventName ] == 'function';
            }
            el = null;
            return isSupported;
        }

        return isEventSupported;
    };

    CoursePress.utility.attachment_by_url = function ( url, target, fallback, field ) {
        var model = new CoursePress.Models.utility.Attachment();
        model.get_attachment( url, target, fallback, field );
    }

    CoursePress.utility.hex_to_rgb = function ( hex ) {
        // Expand shorthand form (e.g. "03F") to full form (e.g. "0033FF")
        var shorthandRegex = /^#?([a-f\d])([a-f\d])([a-f\d])$/i;
        hex = hex.replace(shorthandRegex, function(m, r, g, b) {
            return r + r + g + g + b + b;
        });

        var result = /^#?([a-f\d]{2})([a-f\d]{2})([a-f\d]{2})$/i.exec(hex);
        return result ? {
            r: parseInt(result[1], 16),
            g: parseInt(result[2], 16),
            b: parseInt(result[3], 16)
        } : null;
    }

    CoursePress.UI = CoursePress.UI || {};


    CoursePress.UI.toggle_switch = function( id, name, options ) {

        if( undefined === options ) {
            options = [];
        }

        var content = '';

        var control_class = options['class'] || '';
        var label = options['label'] || '';
        var label_class = options['label_class'] || '';
        var left = options['left'] || '';
        var left_class = options['left_class'] || '';
        var right = options['right'] || '';
        var right_class = options['right_class'] || '';
        var state = options['state'] || 'off';

        var data = '';
        if( options['data'] && _.isArray( options['data'] ) ) {
            $.each( options['data'], function( key, value ) {
                data += _.isString( value ) ? ' data-' + key + '="' + value + '"' : '';
            } );
        }

        content +='<div id="' + id + '" class="toggle-switch coursepress-ui-toggle-switch ' + control_class + ' ' + state + '" name="' + name + '" ' + data + '>';


        if( label.length > 0 ) {
            content += '<span class="label ' + label_class + '">' + label + '</span>';
        }

        if( left.length > 0 ) {
            content += '<span class="left ' + left_class + '">' + left + '</span>';
        }

        content += '<div class="control"><div class="toggle"></div></div>';


        if( right.length > 0 ) {
            content += '<span class="right ' + right_class + '">' + right + '</span>';
        }

        content += '</div>';

        return content;

    }

    CoursePress.UI.browse_media_field = function ( id, name, args ) {

        if ( undefined === name ) {
            name = id;
        }

        if ( undefined === args ) {
            args = {};
        }

        args.title = args.title ? args.title : '';
        args.container_class = args.container_class ? args.container_class : 'wide';
        args.textbox_class = args.textbox_class ? args.textbox_class : 'medium';
        args.value = args.value ? args.value : '';
        args.placeholder = args.placeholder ? args.placeholder : '';
        args.button_text = args.button_text ? args.button_text : '';
        args.type = args.type ? args.type : 'image';
        args.invalid_message = args.invalid_message ? args.invalid_message : _coursepress.invalid_extension_message;
        args.description = args.description ? args.description : '';

        var supported_extensions = false;
        if ( 'image' === args.type ) {
            supported_extensions = _coursepress.allowed_image_extensions.join( ', ' );
        }
        if ( 'audio' === args.type ) {
            supported_extensions = _coursepress.allowed_audio_extensions.join( ', ' );
        }
        if ( 'video' === args.type ) {
            supported_extensions = _coursepress.allowed_video_extensions.join( ', ' );
        }
        if ( ! supported_extensions && _coursepress.allowed_extensions ) {
            supported_extensions = _coursepress.allowed_extensions.join( ', ' );
        }

        var content = '<div class="' + args.container_class + '">';

        if ( args.title ) {
            content += '<label for="' + name + '">' + args.title;
        }

        if ( args.description ) {
            content += '<p class="description">' + args.description + '</p>';
        }

        if ( args.title ) {
            content += '</label>';
        }

        var input_id = id ? 'id="' + id + '"' : '';

        content += '<input class="' + args.textbox_class + ' ' + args.type + '_url" type="text" name="' + name + '" ' + input_id + ' placeholder="' + args.placeholder + '" value="' + args.value + '"/>' +
        '<input class="button browse-media-field" type="button" name="' + name + '-button" value="' + args.button_text + '"/>' +
        '<div class="invalid_extension_message">' + args.invalid_message + ' ' + supported_extensions + '</div>' +
        '</div>';

        return content;

    }

    CoursePress.UI.link_popup = function ( id, name, args ) {
        //console.log( args );
        if ( undefined === name ) {
            name = id;
        }

        if ( undefined === args ) {
            args = {};
        }

        var content = '';

        if( args.content.length <= 0 ) {
            return '';
        }

        args.content = args.content ? args.content : '';
        args.link_text = args.link_text ? args.link_text : '';
        args.class = args.class ? args.class : '';

        content = '<div id="' + id + '" name="' + name + '" class="link-popup ' + args.class + '">' +
        '<a class="popup-link">' + args.link_text + '</a>' +
        '<div class="popup hidden">' +
        '<div class="popup-before"></div>' +
        '<div class="popup-button">&times;</div>' +
        '<div class="popup-content">' +
        args.content +
        '</div>' +
        '</div>' +
        '</div>';

        return content;
    }

    // Add UI extensions
    $.fn.extend( {
            browse_media_field: function ( options ) {
                return this.each( function ( options ) {

                    var text_selector = $( this ).attr( 'name' ).replace( '-button', '' );
                    var parent = $( $( this ).parents( 'div' ).find( '#' + text_selector ) );

                    $( parent ).on( 'keyup', function () {

                        if ( CoursePress.utility.valid_media_extension( $( parent ).val(), parent ) ) {//extension is allowed
                            $( parent ).removeClass( 'invalid_extension_field' );
                            $( parent ).parent().find( '.invalid_extension_message' ).hide();
                        } else {//extension is not allowed
                            $( parent ).addClass( 'invalid_extension_field' );
                            $( parent ).parent().find( '.invalid_extension_message' ).show();
                        }
                    } );

                    $( this ).on( 'click', function () {
                        var self = this;
                        var target_url_field = parent;

                        wp.media.string.props = function ( props, attachment ) {
                            $( target_url_field ).val( props.url );

                            if ( CoursePress.utility.valid_media_extension( attachment.url, target_url_field ) ) {//extension is allowed
                                $( target_url_field ).removeClass( 'invalid_extension_field' );
                                $( target_url_field ).parent().find( '.invalid_extension_message' ).hide();
                            } else {//extension is not allowed
                                $( target_url_field ).addClass( 'invalid_extension_field' );
                                $( target_url_field ).parent().find( '.invalid_extension_message' ).show();
                            }
                            $( self ).trigger( 'change' );
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
                            $( self ).trigger( 'change' );
                        };
                        wp.media.editor.open( target_url_field );
                        return false;

                    } );

                } );
            },

            coursepress_ui_toggle: function ( options ) {
                return this.each( function ( options ) {
                    $( this ).off( 'click' );
                    $( this ).on( 'click', function () {
                        var state = '';

                        $( this ).addClass( 'toggle-ui-widget' );
                        if ( $( this ).hasClass( 'on' ) ) {
                            $( this ).removeClass( 'on' );
                            $( this ).addClass( 'off' );
                            state = 'off';
                        } else {
                            $( this ).removeClass( 'off' );
                            $( this ).addClass( 'on' );
                            state = 'on';
                        }
                        $( this ).trigger( 'change', state );

                        return;
                    } );
                } );
            },

            link_popup: function ( options ) {
                var self = this;
                self.options = options;
                self.link = {};
                this.each( function ( index, item ) {

                    var id = $( this ).attr( 'id' );
                    var name = $( this ).attr( 'name' );
                    var classes = $( this ).attr( 'class' );
                    var content = this.innerHTML;
                    var link_text = self.options || 'Link';

                    var args = {};

                    args.content = content;
                    args.link_text = self.options.link_text;
                    args.class = classes;
                    args.offset_x = self.options.offset_x || 35;

                    content = CoursePress.UI.link_popup( id, name, args );

                    $( item ).empty();
                    //$( item ).append( content );
                    $( item ).append( content );

                    var link = $( item ).find('.popup-link');

                    $( link ).on('click', function(event) {

                        $('.link-popup .popup-link').removeClass( 'open' );
                        $('.popup .popup-before[display!="none"], .popup .popup-button[display!="none"], .popup .popup-content[display!="none"]').parent().fadeOut(100);


                        if ( $( this ).hasClass('open') )
                        {
                            $( this ).removeClass('open');
                            $( document ).click();
                        } else {
                            event.stopPropagation();
                            $( this ).addClass('open');

                            var popup = $(this).siblings('.popup');
                            var popup_before = $(this).siblings('.popup').find('.popup-before');

                            if (($(document).width()) - ($(this).offset().left + args.offset_x) > popup.width()) {
                                popup.css("left", $(this).position().left + args.offset_x);
                            } else {
                                popup.css("left", $(this).position().left - (popup.width() + 10));
                                popup_before.css("transform", 'rotate(180deg)');
                                popup_before.css("-ms-transform", 'rotate(180deg)');
                                popup_before.css("-webkit-transform", 'rotate(180deg)');
                                popup_before.css("left", popup.width());
                            }

                            popup.css("position", "absolute");
                            popup.css("top", $(this).position().top - 7);
                            popup.fadeIn(300);
                        }

                    });

                    $('.popup').on('click', function(e) {
                        e.stopPropagation();
                    });

                    $('.popup-content').on('click', function(e) {
                        //e.preventDefault();
                    });


                } );

                $(document).click(function() {
                    $('.link-popup .popup-link').removeClass( 'open' );
                    $('.popup .popup-before[display!="none"], .popup .popup-button[display!="none"], .popup .popup-content[display!="none"]').parent().fadeOut(100);
                });

                $('.link-popup .popup-button ' ).on( 'click', function() {
                    $('.link-popup .popup-link').removeClass( 'open' );
                    $('.popup .popup-before[display!="none"], .popup .popup-button[display!="none"], .popup .popup-content[display!="none"]').parent().fadeOut(100);
                });

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


    // Models
    CoursePress.Models = CoursePress.Models || {};
    CoursePress.Models.utility = CoursePress.Models.utility || {};

    CoursePress.Models.utility.Attachment = Backbone.Model.extend( {
        initialize: function () {
            this.on( 'sync', this.process, this );
        },
        get_attachment: function ( attachment_url, target, fallback, field ) {
            this.attachment_url = attachment_url;
            this.target = target;
            this.field = field ? field : 'post_excerpt';
            this.fallback = fallback ? fallback : '';
            this.url = _coursepress._ajax_url + '?action=attachment_model&task=get&url=' + attachment_url;
            this.fetch();
        },
        process: function () {

            if ( this.get( 0 ) ) {
                var value = this.get( 0 )[ this.field ];
                value = value.length > 0 ? value : this.fallback;

                $( $( this.target )[ 0 ] ).html( value );
            }

        }
    } );


})( jQuery );