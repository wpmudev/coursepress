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
            meta_items = CoursePress.Course.fix_checkboxes( meta_items, step, "0" );
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

        data.meta_setup_marker = step;

        CoursePress.Course.set( 'data', data );

    }

    course_structure_update = function () {

        $.each( $( '.step-content .course-structure tr.unit' ), function ( uidx, unit ) {

            // Make sure its a tree node
            var match;
            if ( match = $( unit ).attr( 'class' ).match( /treegrid-\d{1,10}/g )[ 0 ] ) {

                var unit_id = match.trim().split( '-' ).pop();

                var pages_selector = '.step-content .course-structure tr.page.treegrid-parent-' + unit_id;
                var pages = $( pages_selector );

                // Do pages first
                $.each( pages, function ( pidx, page ) {

                    var page_id = $( page ).attr( 'class' ).match( /treegrid-\d{1,10}/g )[ 0 ].trim().split( '-' ).pop();
                    var modules_selector = '.step-content .course-structure tr.module.treegrid-parent-' + page_id;

                    var modules_visible_boxes = modules_selector + ' [name*="meta_structure_visible_modules"]';
                    var modules_visible_count = $( modules_visible_boxes ).length;
                    var modules_visible_checked = $( modules_visible_boxes + ':checked' ).length;

                    $( '.step-content .course-structure .treegrid-' + page_id + ' [name*=meta_structure_visible_pages]' ).prop(
                        'checked',
                        modules_visible_count == modules_visible_checked && modules_visible_checked > 0
                    )

                    var modules_preview_boxes = modules_selector + ' [name*="meta_structure_preview_modules"]';
                    var modules_preview_count = $( modules_preview_boxes ).length;
                    var modules_preview_checked = $( modules_preview_boxes + ':checked' ).length;

                    $( '.step-content .course-structure .treegrid-' + page_id + ' [name*=meta_structure_preview_pages]' ).prop(
                        'checked',
                        modules_preview_count == modules_preview_checked && modules_preview_checked > 0
                    )

                } );

                // Then do units
                var pages_visible_boxes = pages_selector + ' [name*="meta_structure_visible_pages"]';
                var pages_visible_count = $( pages_visible_boxes ).length;
                var pages_visible_checked = $( pages_visible_boxes + ':checked' ).length;

                $( '.step-content .course-structure .treegrid-' + unit_id + ' [name*=meta_structure_visible_units]' ).prop(
                    'checked',
                    pages_visible_count == pages_visible_checked && pages_visible_checked > 0
                )

                var pages_preview_boxes = pages_selector + ' [name*="meta_structure_preview_pages"]';
                var pages_preview_count = $( pages_preview_boxes ).length;
                var pages_preview_checked = $( pages_preview_boxes + ':checked' ).length;

                $( '.step-content .course-structure .treegrid-' + unit_id + ' [name*=meta_structure_preview_units]' ).prop(
                    'checked',
                    pages_preview_count == pages_preview_checked && pages_preview_checked > 0
                )

            }

        } );

    }


    function setup_UI() {

        // Setup Accordion
        $( "#course-setup-steps" ).accordion( {
            disabled: true,
            autoHeight: false,
            collapsible: true,
            heightStyle: "content",
            active: 0,
            animate: 200 // collapse will take 300ms
        } );

        // Slide Accordion into Position
        $( '#course-setup-steps .step-title' ).bind( 'click', function ( e ) {

            var self = this;
            console.log( self );

            var step = parseInt( $( self ).attr( 'class' ).match( /step-\d{1,10}/g )[ 0 ].trim().split( '-' ).pop() );

            pre_step = 1 < ( step - 1 ) ? step - 1 : 1;
            next_step = ( step + 1 ) > 6 ? 6 : step + 1;

            console.log( 'next: ' + next_step );
            console.log( 'prev: ' + pre_step );

            if ( !$( self ).find( '.status' ).hasClass( 'saved' ) && !$( '.step-title.step-' + pre_step ).find( '.status' ).hasClass( 'saved' ) ) {
                $( self ).effect( 'highlight', { color: '#ffabab', duration: 300 } );
                return;
            }

            // Manually handle the accordion so we don't progress too soon
            $( "#course-setup-steps" ).accordion( "enable" ).accordion( { active: (step - 1) } ).accordion( "disable" );

            setTimeout( function () {
                theOffset = $( self ).offset();
                $( 'body,html' ).animate( { scrollTop: theOffset.top - 110, duration: 200 } );
            }, 200 ); // ensure the collapse animation is done

        } );

        // Setup Chosen
        //$( ".chosen-select" ).chosen( { disable_search_threshold: 10 } );
        $( ".chosen-select.medium" ).chosen( { disable_search_threshold: 5, width: "40%" } );

        // Tree for course structure
        $( "table.course-structure-tree" ).treegrid( { initialState: 'expanded' } );

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

        // Handle Course Structure Checkboxes
        $( '.step-content .course-structure input[type="checkbox"]' ).on( 'click', function ( e ) {

            var checkbox = e.currentTarget;
            var handled = false;

            var name = $( checkbox ).attr( 'name' );

            // Units
            if ( name.match( /meta_structure_.*_units.*/g ) ) {
                var type = name.match( /meta_structure_visible_units.*/g ) ? 'visible' : 'preview';
                var parent_class = $( $( '[name="' + name + '"]' ).parents( 'tr[class*="treegrid-"]' )[ 0 ] ).attr( 'class' ).match( /treegrid-\d{1,10}/g )[ 0 ].trim();
                var parent_id = parent_class.split( '-' ).pop();
                var parent_selector = '.step-content .course-structure .treegrid-parent-' + parent_id;
                var page_selector = parent_selector + ' [name*="meta_structure_' + type + '_pages"]';
                var checked = $( checkbox )[ 0 ].checked;

                var pages = $( page_selector );

                $.each( pages, function ( index, page ) {

                    $( page ).prop( 'checked', checked );

                    parent_class = $( $( page ).parents( 'tr[class*="treegrid-"]' )[ 0 ] ).attr( 'class' ).match( /treegrid-\d{1,10}/g )[ 0 ].trim();
                    parent_id = parent_class.split( '-' ).pop();
                    parent_selector = '.step-content .course-structure .treegrid-parent-' + parent_id;
                    var module_selector = parent_selector + ' [name*="meta_structure_' + type + '_modules"]';

                    $( module_selector ).prop( 'checked', checked );

                } );

                handled = true;
            }

            // Pages
            if ( !handled && name.match( /meta_structure_.*_pages.*/g ) ) {
                var type = name.match( /meta_structure_visible_pages.*/g ) ? 'visible' : 'preview';
                var parent_class = $( $( '[name="' + name + '"]' ).parents( 'tr[class*="treegrid-"]' )[ 0 ] ).attr( 'class' ).match( /treegrid-\d{1,10}/g )[ 0 ].trim();
                var parent_id = parent_class.split( '-' ).pop();
                var parent_selector = '.step-content .course-structure .treegrid-parent-' + parent_id;

                var checked = $( checkbox )[ 0 ].checked;
                var module_selector = parent_selector + ' [name*="meta_structure_' + type + '_modules"]';

                $( module_selector ).prop( 'checked', checked );
            }

            // Update the toggles
            course_structure_update();

        } );

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

        // Get setup marker and advance accordion
        var setup_marker = $( '#course-setup-steps .step-title .status.setup_marker' );
        if ( $( setup_marker ).length > 0 ) {
            setup_marker = parseInt( $( $( setup_marker ).parents( '.step-title' )[ 0 ] ).attr( 'class' ).match( /step-\d{1,10}/g )[ 0 ].trim().split( '-' ).pop() )
            setup_marker = ( setup_marker + 1 ) > 6 ? 6 : setup_marker + 1;
            $( '#course-setup-steps .step-title.step-' + setup_marker ).click();
        }

    } );


})( jQuery );


