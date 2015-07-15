var CoursePress = CoursePress || {};

(function ( $ ) {

    CoursePress.Models = CoursePress.Models || {}

    CoursePress.Models.Course = CoursePress.Models.Course || Backbone.Model.extend( {
        url: _coursepress._ajax_url + '?action=update_course',
        parse: function ( response, xhr ) {

            // Trigger course update events
            if( true === response.success ) {
                this.set( 'response_data', response.data );
                this.trigger( 'coursepress:course_updated', response.data );
            } else {
                this.set( 'response_data', {} );
                this.trigger( 'coursepress:course_update_error', response.data );
            }

        },
        defaults: {
        }
    } );

    CoursePress.Course = new CoursePress.Models.Course();

    CoursePress.Course.get_step = function ( step ) {

        var data = {};

        data.step = step;

        // Step 1 Data
        if ( 1 <= step ) {
            data.course_id =  $('[name="course_id"]' ).val();
            data.course_name = $('[name="course_name"]' ).val();

            data.course_excerpt = tinyMCE && tinyMCE.get( 'courseExcerpt' ) ? tinyMCE.get( 'courseExcerpt' ).getContent() : $('[name="course_excerpt"]' ).val();

            data.listing_image = $('[name="listing_image"]' ).val();
            data.course_categories = $('[name="course_categories"]' ).val();
            data.course_language = $('[name="course_language"]' ).val();
        }

        // Step 2 Data
        if ( 2 <= step ) {
            data.step_2_val = "I have data from step 2";
        }

        // Step 3 Data
        if ( 3 <= step ) {
            data.step_3_val = "I have data from step 3";
        }

        // Step 4 Data
        if ( 4 <= step ) {
            data.step_4_val = "I have data from step 4";
        }

        // Step 1 Data
        if ( 5 <= step ) {
            data.step_5_val = "I have data from step 5";
        }

        // Step 1 Data
        if ( 6 <= step ) {
            data.step_6_val = "I have data from step 6";
        }


        CoursePress.Course.set( 'data', data );

        //console.log( CoursePress.Course );

    }

    function setup_UI() {
        // Setup Accordion
        $( "#course-setup-steps" ).accordion( {
            autoHeight: false,
            collapsible: true,
            heightStyle: "content",
            active: 0,
            animate: 300 // collapse will take 300ms
        } );


        // Slide Accordion into Position
        $( '#course-setup-steps h3' ).bind( 'click', function () {
            var self = this;
            setTimeout( function () {
                theOffset = $( self ).offset();
                $( 'body,html' ).animate( { scrollTop: theOffset.top - 100 } );
            }, 310 ); // ensure the collapse animation is done
        } );

        // Setup Chosen
        $( ".chosen-select" ).chosen( { disable_search_threshold: 10 } );
    }

    function bind_buttons() {

        $( '.step-content .button.step.next' ).on( 'click', function ( e ) {

            var target = e.currentTarget;

            var step;
            var next_step;

            // Get the right step
            step = $( target ).hasClass( 'step-1' ) ? 1 : null;
            step = $( target ).hasClass( 'step-2' ) ? 2 : step;
            step = $( target ).hasClass( 'step-3' ) ? 3 : step;
            step = $( target ).hasClass( 'step-4' ) ? 4 : step;
            step = $( target ).hasClass( 'step-5' ) ? 5 : step;
            step = $( target ).hasClass( 'step-6' ) ? 6 : step;

            next_step = 6 < step ? step + 1 : 6;

            if( ! $( '.step-title.step-' + step ).find('.status' ).hasClass( 'saved' ) && ! $( '.step-title.step-' + next_step ).find('.status' ).hasClass( 'saved' ) ) {
                e.stopPropagation();
                e.preventDefault();
                return;
            }

            if ( null !== step ) {
                $( '.step-title.step-' + step ).find('.status' ).removeClass('saved');
                $( '.step-title.step-' + step ).find('.status' ).removeClass('save-error');
                $( '.step-title.step-' + step ).find('.status' ).removeClass('save-attention');
                $( '.step-title.step-' + step ).find('.status' ).addClass('save-process');
                CoursePress.Course.get_step( step );
                CoursePress.Course.save();
            }


        } );

        $('.button.browse-media-field').browse_media_field();

    }

    function bind_coursepress_events() {

        CoursePress.Course .on( 'coursepress:course_updated', function ( data ) {

            $( '.step-title.step-' + data.last_step ).find('.status' ).addClass('saved');
            $( '.step-title.step-' + data.last_step ).find('.status' ).removeClass('save-error');
            $( '.step-title.step-' + data.last_step ).find('.status' ).removeClass('save-attention');
            $( '.step-title.step-' + data.last_step ).find('.status' ).removeClass('save-process');
            if( data.next_step != data.last_step ) {
                $( '.step-title.step-' + data.next_step ).click();
            }

        } );

        CoursePress.Course .on( 'coursepress:course_update_error', function ( data ) {

            $( '.step-title.step-' + data.last_step ).find('.status' ).removeClass('saved');
            $( '.step-title.step-' + data.last_step ).find('.status' ).addClass('save-error');
            $( '.step-title.step-' + data.last_step ).find('.status' ).removeClass('save-attention');
            $( '.step-title.step-' + data.last_step ).find('.status' ).removeClass('save-process');

            console.log( data );

        } );

    }


    // Try to keep only one of these blocks and use functions/objects instead
    $( document ).ready( function ( $ ) {

        setup_UI();
        bind_buttons();
        bind_coursepress_events();

    } );


})( jQuery );


