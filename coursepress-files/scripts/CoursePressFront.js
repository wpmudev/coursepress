var CoursePress = CoursePress || {};

(function ( $ ) {

    CoursePress.Models = CoursePress.Models || {};
    CoursePress.UI = CoursePress.UI || {};
    CoursePress.utility = CoursePress.utility || {};

    CoursePress.Models.CourseFront = Backbone.Model.extend( {
        url: _coursepress._ajax_url + '?action=course_front',
        parse: function ( response, xhr ) {

            // Trigger course update events
            if ( true === response.success ) {
                this.set( 'response_data', response.data );
                this.trigger( 'coursepress:' + response.data.action + '_success', response.data );
            } else {
                this.set( 'response_data', {} );
                if( response.data ) {
                    this.trigger( 'coursepress:' + response.data.action + '_error', response.data );
                }
            }
        },
        defaults: {}
    } );

    // Init YouTube
    //var tag = document.createElement( 'script' );
    //tag.src = "https://www.youtube.com/iframe_api";
    //var firstScriptTag = document.getElementsByTagName( 'script' )[ 0 ];
    //firstScriptTag.parentNode.insertBefore( tag, firstScriptTag );

    function bind_buttons() {

        $( '.apply-button' ).on( 'click', function ( e ) {
            var target = e.currentTarget;

            if ( $( target ).attr( 'data-link' ).length > 0 ) {
                location.href = $( target ).attr( 'data-link' );
            }
        } );

        $( 'button' ).on( 'click', function ( e ) {
            var target = e.currentTarget;

            if ( $( target ).attr( 'data-link' ).length > 0 ) {
                location.href = $( target ).attr( 'data-link' );
            }
        } );

        // Make course boxes clickable
        $( '.course_list_box_item.clickable' ).on( 'click', function( e ) {
            var target = e.currentTarget;

            if ( $( target ).attr( 'data-link' ).length > 0 ) {
                location.href = $( target ).attr( 'data-link' );
            }
        } );


        $( '.li-locked-unit a' ).on('click', function( e ) {
            e.stopImmediatePropagation();
            e.preventDefault();
        } );


        //$( '.view-response' ).link_popup( { link_text:  _coursepress.workbook_view_answer });
        //$( '.view-response' ).link_popup( { link_text:  '<span class="dashicons dashicons-visibility"></span>' });
        $( '.workbook-table .view-response' ).link_popup( { link_text:  '<span class="dashicons dashicons-visibility"></span>', offset_x: -160 });
        $( '.workbook-table .feedback' ).link_popup( { link_text:  '<span class="dashicons dashicons-admin-comments"></span>' });


    }


    function bind_module_actions() {

        // Resubmit
        $( '.module-container .module-result .resubmit a' ).on( 'click', function ( e ) {

            var parent = $( this ).parents( '.module-container' );
            var elements = $( parent ).find( '.module-elements' );
            var response = $( parent ).find( '.module-response' );
            var result = $( parent ).find( '.module-result' );

            $( elements ).removeClass( 'hide' );
            $( response ).addClass( 'hide' );
            $( result ).addClass( 'hide' );

        } );


        // Validate File Selected
        $( '.module-container input[type=file]' ).on( 'change', function ( e ) {

            var parent = $( this ).parents( '.module-container' );
            var filename = $( this ).val();
            var extension = filename.split( '.' ).pop();
            var allowed_extensions = _.keys( _coursepress.allowed_student_extensions );

            var allowed_string = allowed_extensions.join( ', ' );

            var progress = $( parent ).find( '.upload-progress' );

            var allowed = _.contains( allowed_extensions, extension );

            $( progress ).find( '.invalid-extension' ).detach();

            if ( !allowed ) {
                console.log( progress );
                $( progress ).append( '<span class="invalid-extension">' + _coursepress.invalid_upload_message + allowed_string + '</span>' );
                console.log( 'NOT ALLOWED!' );
            }

        } );

        // Submit Result
        $( '.module-submit-action' ).on( 'click', function ( e ) {

            var el = this;
            var parent = $( el ).parents( '.module-container' );
            var elements = $( parent ).find( '.module-elements' );
            var response = $( parent ).find( '.module-response' );
            var result = $( parent ).find( '.module-result' );

            var module_id = $( parent ).attr( 'data-module' );
            var module_type = $( parent ).attr( 'data-type' );
            var course_id = $( parent ).find( '[name=course_id]' ).val();
            var unit_id = $( parent ).find( '[name=unit_id]' ).val();
            var student_id = $( parent ).find( '[name=student_id]' ).val();
            var value = '';

            var not_valid = false;

            switch ( module_type ) {

                case 'input-checkbox':
                    value = [];
                    $.each( $( parent ).find( '[name="module-' + module_id + '"]:checked' ), function ( i, item ) {
                        value.push( $( item ).val() );
                    } );
                    not_valid = value.length === 0;
                    break;
                case 'input-radio':
                    var el = $( parent ).find( '[name="module-' + module_id + '"]:checked' );
                    if ( el ) {
                        value = $( el ).val();
                    } else {
                        not_valid = true;
                    }

                    break;
                case 'input-select':
                    value = $( parent ).find( '[name=module-' + module_id + ']' ).val();
                    break;
                case 'input-text':
                    value = $( parent ).find( '[name=module-' + module_id + ']' ).val();
                    not_valid = value.trim().length === 0;
                    break;
                case 'input-textarea':
                    value = $( parent ).find( '[name=module-' + module_id + ']' ).val();
                    not_valid = value.trim().length === 0;
                    break;
                case 'input-upload':

                    if ( supportAjaxUploadWithProgress() ) {

                        var formData = new FormData();

                        var file = $( parent ).find( '[name=module-' + module_id + ']' )[ 0 ].files[ 0 ];

                        // Exit if extension not supported
                        var extension = file.name.split( '.' ).pop();
                        var allowed_extensions = _.keys( _coursepress.allowed_student_extensions );
                        var allowed = _.contains( allowed_extensions, extension );

                        if ( !allowed ) {
                            return;
                        }

                        var uri = '';
                        formData.append( 'course_action', 'upload-file' );
                        formData.append( 'course_id', course_id );
                        formData.append( 'unit_id', unit_id );
                        formData.append( 'module_id', module_id );
                        formData.append( 'student_id', student_id );
                        formData.append( 'src', 'ajax' );
                        formData.append( 'file', file );

                        var xhr = new XMLHttpRequest();

                        // Started
                        xhr.upload.addEventListener( 'loadstart', function ( e ) {

                            var progress = $( parent ).find( '.upload-progress' );
                            $( progress ).find( '.spinner' ).detach();
                            $( progress ).append( '<span class="image spinner">&#xf111</span>' );

                        }, false );
                        // Progress
                        xhr.upload.addEventListener( 'progress', function ( e ) {

                            var percent = e.loaded / e.total * 100;
                            var percent_el = $( parent ).find( '.upload-percent' );
                            percent = parseInt( percent );

                            if ( percent_el.length > 0 ) {
                                $( percent_el ).replaceWith( '<span class="upload-percent">' + percent + '%</span>' );
                            } else {
                                $( parent ).find( '.upload-progress' ).append( '<span class="upload-percent">' + percent + '%</span>' );
                            }

                        }, false );

                        xhr.upload.addEventListener( 'load', function ( e ) {
                            // Keep this here for future
                        }, false );

                        xhr.addEventListener( 'readystatechange', function ( e ) {
                            var status, text, readyState;
                            try {
                                readyState = e.target.readyState;
                                //text = e.target.responseText;
                                status = e.target.status;
                            }
                            catch ( err ) {
                                return;
                            }

                            // Set a default as ready state might trigger xhr requests
                            var data = { success: false };
                            try {
                                data = JSON.parse( e.target.responseText );
                            } catch( e ){}

                            if ( readyState == 4 && status == '200' && data.success ) {

                                $( parent ).find( '.upload-percent' ).detach();
                                $( parent ).find( '.upload-progress .spinner' ).detach();
                                $( result ).detach();
                                $( elements ).addClass( 'hide' );
                                $( response ).replaceWith( '<div class="module-response">' +
                                    '<p class="file_holder">' + _coursepress.file_uploaded_message + '</p>' +
                                    '</div>'
                                );

                            } else if( readyState == 4 ) {
                                $( parent ).find( '.upload-percent' ).detach();
                                $( parent ).find( '.upload-progress .spinner' ).detach();
                                $( result ).detach();
                                $( elements ).addClass( 'hide' );
                                $( response ).replaceWith( '<div class="module-response">' +
                                    '<p class="file_holder">' + _coursepress.file_upload_fail_message + '</p>' +
                                    '</div>'
                                );
                            }

                        }, false );

                        // Set up request
                        xhr.open( 'POST', uri, true );

                        // Fire!
                        xhr.send( formData );

                    } else {
                        $( parent ).find( 'form' ).submit();
                    }

                    // No processing past this point
                    return;

                    break;

            }

            if ( not_valid ) {
                return;
            }
            // Add Spinner
            $( elements ).find( '.response-processing' ).detach();
            $( elements ).find( '.module-submit-action' ).append( '<span class="response-processing image spinner">&#xf111</span>' );

            // Record Response
            var model = new CoursePress.Models.CourseFront();

            model.set( 'action', 'record_module_response' );
            model.set( 'course_id', course_id );
            model.set( 'unit_id', unit_id );
            model.set( 'module_id', module_id );
            model.set( 'student_id', student_id );
            model.set( 'response', value );

            model.save();

            model.on( 'coursepress:record_module_response_success', function ( data ) {
                $( elements ).find( '.response-processing' ).detach();

                $( result ).detach();
                $( elements ).addClass( 'hide' );

                var html = '<div class="module-response">' +
                    '<p class="file_holder">' + _coursepress.response_saved_message + '</p>' +
                    '</div>';

                console.log( response );
                if( 0 === response.length ) {
                    $( parent ).append( html );
                } else {
                    $( response ).replaceWith( html );
                }


            } );

            model.on( 'coursepress:record_module_response_error', function ( data ) {
                $( elements ).find( '.response-processing' ).detach();

                $( result ).detach();
                $( elements ).addClass( 'hide' );

                var html = '<div class="module-response">' +
                    '<p class="file_holder">' + _coursepress.response_fail_message + '</p>' +
                    '</div>';

                if( 0 === response.length ) {
                    $( parent ).append( html );
                } else {
                    $( response ).replaceWith( html );
                }
            } );


        } );

    }

    function supportAjaxUploadWithProgress() {
        return supportFileAPI() && supportAjaxUploadProgressEvents() && supportFormData();
        // Is the File API supported?
        function supportFileAPI() {
            var fi = document.createElement( 'INPUT' );
            fi.type = 'file';
            return 'files' in fi;
        };
        // Are progress events supported?
        function supportAjaxUploadProgressEvents() {
            var xhr = new XMLHttpRequest();
            return !!(xhr && ('upload' in xhr) && ('onprogress' in xhr.upload));
        };
        // Is FormData supported?
        function supportFormData() {
            return !!window.FormData;
        }
    }


    function course_completion() {

        var model = new CoursePress.Models.CourseFront();

        model.set( 'action', 'calculate_completion' );
        model.set( 'course_id', _coursepress.current_course );
        model.set( 'student_id', _coursepress.current_student );
        model.save();

    }

    function external() {
        //$( 'input.knob' ).knob();

        var a_col = $( 'ul.units-archive-list a' ).css('color');
        var p_col = $( 'body' ).css('color').replace('rgb(', '' ).replace(')', '' ).split( ',');
        var init = { color: a_col }
        var circles = $( '.course-progress-disc' ).circleProgress( { fill: init, emptyFill: 'rgba(' + p_col[0] + ', ' + p_col[1] + ', ' + p_col[2] + ', .1)' });
        $.each( circles, function ( i, item ) {

            var parent = $( item ).parents('ul')[0];
            var a_col = $( parent ).find('a').css('color');

            $( item ).on( 'circle-animation-progress', function ( e, v ) {
                var obj = $( this ).data( 'circle-progress' ),
                    ctx = obj.ctx,
                    s = obj.size,
                    sv = (100 * v).toFixed(),
                    ov = (100 * obj.value ).toFixed(),
                    fill = obj.arcFill;
                sv = 100 - sv;
                if ( sv < ov ) {
                    sv = ov;
                }
                ctx.save();
                ctx.font = s / 4.5 + "px sans-serif";
                ctx.textAlign = 'center';
                ctx.textBaseline = 'middle';
                ctx.fillStyle = fill;
                ctx.fillText( sv + '%', s / 2 + s / 80, s / 2 );
                ctx.restore();
            } );

            $( item ).on( 'circle-animation-end', function ( e ) {
                var obj = $( this ).data( 'circle-progress' ),
                    ctx = obj.ctx,
                    s = obj.size,
                    sv = (100 * obj.value ).toFixed(),
                    fill = obj.arcFill;
                obj.drawFrame( obj.value );
                ctx.font = s / 4.5 + "px sans-serif";
                ctx.textAlign = 'center';
                ctx.textBaseline = 'middle';
                ctx.fillStyle = a_col;
                ctx.fillText( sv + '%', s / 2, s / 2 );
            } );

            // In case animation doesn't run
            var obj = $( item ).data( 'circle-progress' ),
                ctx = obj.ctx,
                s = obj.size,
                sv = (100 * obj.value ).toFixed(),
                fill = obj.arcFill;
            ctx.font = s / 4.5 + "px sans-serif";
            ctx.textAlign = 'center';
            ctx.textBaseline = 'middle';
            ctx.fillStyle = fill;
            ctx.fillText( sv + '%', s / 2, s / 2 + s / 80 );


        } );

    }


    function bind_course_discussions() {

        $( '.course-discussion-content.new .button-links .submit-discussion' ).on( 'click', function( e ) {
            $( this ).parents( 'form' ).submit();
        } );

    }


    $( document ).ready( function ( $ ) {

        bind_buttons();

        bind_module_actions();

        course_completion();

        bind_course_discussions();

        external();


    } );


})( jQuery );


CoursePress.current = CoursePress.current || {};

//function onYouTubeIframeAPIReady() {
//
//    var $ = jQuery;
//
//    // Course Featured Video
//    var videoID = $( '#feature-video-div' ).attr( 'data-video' );
//    var width = $( '#feature-video-div' ).attr( 'data-width' );
//    var height = $( '#feature-video-div' ).attr( 'data-height' );
//    CoursePress.current.featuredVideo = new YT.Player( 'feature-video-div',
//        {
//            videoId: videoID,
//            width: width,
//            height: height,
//            playerVars: { 'controls': 0, 'modestbranding': 1, 'rel': 0, 'showinfo': 0 },
//            events: {
//                //'onReady': function( event ) {}
//                //'onPlaybackQualityChange': onPlayerPlaybackQualityChange,
//                //'onStateChange': onPlayerStateChange,
//                //'onError': onPlayerError
//            }
//        }
//    );
//
//}




