var CoursePress = CoursePress || {};

(function ( $ ) {

    CoursePress.Models = CoursePress.Models || {}

    CoursePress.Models.Course = CoursePress.Models.Course || Backbone.Model.extend( {
        url: _coursepress._ajax_url + '?action=update_course',
        parse: function ( response, xhr ) {

            // Trigger course update events
            if ( true === response.success ) {
                this.set( 'response_data', response.data );
                this.trigger( 'coursepress:course_updated', response.data );
            } else {
                this.set( 'response_data', {} );
                this.trigger( 'coursepress:course_update_error', response.data );
            }

        },
        defaults: {}
    } );

    CoursePress.Course = new CoursePress.Models.Course();

    CoursePress.Course.multiple_elements = function ( items, needle ) {

        var item_count = 0;
        $.each( items, function ( index, element ) {
            if ( needle === element.name ) {
                item_count += 1;
            }
        } );

        return item_count > 1;

    }

    CoursePress.Course.add_array_to_data = function ( data, items ) {
        var item_count = 0;
        var last_item = '';

        $.each( items, function ( index, element ) {

            var name = element.name.replace( /(\[|\]\[)/g, '/' ).replace( /\]/g, '' );//.replace(/\/$/g, '');

            if ( last_item !== name ) {
                item_count = 0;
            }

            if ( name.match( /\/$/g ) || CoursePress.Course.multiple_elements( items, element.name ) ) {
                console.log( item_count + ': ' + element.name );
                if ( name.match( /\/$/g ) ) {
                    CoursePress.utility.update_object_by_path( data, name + item_count, element.value );
                } else {
                    CoursePress.utility.update_object_by_path( data, name + '/' + item_count, element.value );
                }
            } else {
                CoursePress.utility.update_object_by_path( data, name, element.value );
            }

            item_count += 1;
            last_item = name;

        } );
    }

    CoursePress.Course.fix_checkboxes = function ( items, step, false_value ) {
        var meta_items = $( '.step-content.step-' + step + ' [name^="meta_"]' );

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

    CoursePress.Course.get_step = function ( step ) {

        var data = {};

        data.step = step;

        // Step 1 Data
        if ( 1 <= step ) {
            data.course_id = $( '[name="course_id"]' ).val();
            data.course_name = $( '[name="course_name"]' ).val();

            data.course_excerpt = tinyMCE && tinyMCE.get( 'courseExcerpt' ) ? tinyMCE.get( 'courseExcerpt' ).getContent() : $( '[name="course_excerpt"]' ).val();

            var meta_items = $( '.step-content.step-1 [name^="meta_"]' ).serializeArray();
            meta_items = CoursePress.Course.fix_checkboxes( meta_items, step );
            CoursePress.Course.add_array_to_data( data, meta_items );
        }

        // Step 2 Data
        if ( 2 <= step ) {

            data.course_description = tinyMCE && tinyMCE.get( 'courseDescription' ) ? tinyMCE.get( 'courseDescription' ).getContent() : $( '[name="course_description"]' ).val();

            var meta_items = $( '.step-content.step-2 [name^="meta_"]' ).serializeArray();
            meta_items = CoursePress.Course.fix_checkboxes( meta_items, step );
            CoursePress.Course.add_array_to_data( data, meta_items );
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

        // Tree for course structure
        $( "table.course-structure-tree" ).treegrid( { initialState: 'collapsed' } );

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

            if ( !$( '.step-title.step-' + step ).find( '.status' ).hasClass( 'saved' ) && !$( '.step-title.step-' + next_step ).find( '.status' ).hasClass( 'saved' ) ) {
                e.stopPropagation();
                e.preventDefault();
                return;
            }

            if ( null !== step ) {
                $( '.step-title.step-' + step ).find( '.status' ).removeClass( 'saved' );
                $( '.step-title.step-' + step ).find( '.status' ).removeClass( 'save-error' );
                $( '.step-title.step-' + step ).find( '.status' ).removeClass( 'save-attention' );
                $( '.step-title.step-' + step ).find( '.status' ).addClass( 'save-process' );
                CoursePress.Course.get_step( step );
                CoursePress.Course.save();
            }


        } );

        $( '.button.browse-media-field' ).browse_media_field();

    }

    function bind_coursepress_events() {

        CoursePress.Course.on( 'coursepress:course_updated', function ( data ) {

            $( '.step-title.step-' + data.last_step ).find( '.status' ).addClass( 'saved' );
            $( '.step-title.step-' + data.last_step ).find( '.status' ).removeClass( 'save-error' );
            $( '.step-title.step-' + data.last_step ).find( '.status' ).removeClass( 'save-attention' );
            $( '.step-title.step-' + data.last_step ).find( '.status' ).removeClass( 'save-process' );
            if ( data.next_step != data.last_step ) {
                $( '.step-title.step-' + data.next_step ).click();
            }

        } );

        CoursePress.Course.on( 'coursepress:course_update_error', function ( data ) {

            $( '.step-title.step-' + data.last_step ).find( '.status' ).removeClass( 'saved' );
            $( '.step-title.step-' + data.last_step ).find( '.status' ).addClass( 'save-error' );
            $( '.step-title.step-' + data.last_step ).find( '.status' ).removeClass( 'save-attention' );
            $( '.step-title.step-' + data.last_step ).find( '.status' ).removeClass( 'save-process' );

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


