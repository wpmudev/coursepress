var CoursePress = CoursePress || {};

(function ( $ ) {

    CoursePress.Models = CoursePress.Models || {}

    CoursePress.Models.Course = CoursePress.Models.Course || Backbone.Model.extend( {
        url: _coursepress._ajax_url + '?action=update_course',
        parse: function ( response, xhr ) {

            // Trigger course update events
            if ( true === response.success ) {
                this.set( 'response_data', response.data );
                this.trigger( 'coursepress:' + response.data.action + '_success', response.data );
            } else {
                this.set( 'response_data', {} );
                this.trigger( 'coursepress:' + response.data.action + '_error', response.data );
            }

            CoursePress.Course.set( 'action', '' );
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

    CoursePress.Course.get_step = function ( step, action_type ) {

        if ( typeof action_type == 'undefined' ) {
            action_type = 'next';
        }

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
            var meta_items = $( '.step-content.step-3 [name^="meta_"]' ).serializeArray();
            meta_items = CoursePress.Course.fix_checkboxes( meta_items, step, "0" );
            CoursePress.Course.add_array_to_data( data, meta_items );
        }

        // Step 4 Data
        if ( 4 <= step ) {
            var meta_items = $( '.step-content.step-4 [name^="meta_"]' ).serializeArray();
            meta_items = CoursePress.Course.fix_checkboxes( meta_items, step, "0" );
            CoursePress.Course.add_array_to_data( data, meta_items );
        }

        // Step 1 Data
        if ( 5 <= step ) {
            var meta_items = $( '.step-content.step-5 [name^="meta_"]' ).serializeArray();
            meta_items = CoursePress.Course.fix_checkboxes( meta_items, step, "0" );
            CoursePress.Course.add_array_to_data( data, meta_items );
        }

        // Step 1 Data
        if ( 6 <= step ) {
            var meta_items = $( '.step-content.step-6 [name^="meta_"]' ).serializeArray();
            meta_items = CoursePress.Course.fix_checkboxes( meta_items, step, "0" );
            CoursePress.Course.add_array_to_data( data, meta_items );
        }

        var next_step = step;

        if ( 'next' === action_type ) {
            data.meta_setup_marker = step;
            next_step = next_step !== 6 ? next_step + 1 : next_step;
        }
        if ( 'update' === action_type ) {
            //data.meta_setup_marker = step;
        }
        if ( 'prev' === action_type ) {
            data.meta_setup_marker = step - 1;
            next_step = next_step !== 1 ? next_step - 1 : next_step;
        }
        if ( 'finish' === action_type ) {
            data.meta_setup_marker = step;
        }


        CoursePress.Course.set( 'data', data );
        CoursePress.Course.set( 'action', 'update_course' );
        CoursePress.Course.set( 'next_step', next_step );

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
            var step = parseInt( $( self ).attr( 'class' ).match( /step-\d{1,10}/g )[ 0 ].trim().split( '-' ).pop() );

            pre_step = 1 < ( step - 1 ) ? step - 1 : 1;
            next_step = ( step + 1 ) > 6 ? 6 : step + 1;

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

        // ===== DATE PICKERS =====
        $( '.dateinput' ).datepicker( {
            dateFormat: 'yy-mm-dd'
            //firstDay: coursepress.start_of_week
        } );
        $( '.date' ).click( function ( event ) {
            if ( !$( this ).parents( 'div' ).hasClass( 'disabled' ) ) {
                $( this ).find( '.dateinput' ).datepicker( "show" );
            }
        } );

        $( '[name="meta_enrollment_open_ended"]' ).change( function () {
            if ( this.checked ) {
                $( this ).parents( '.enrollment-dates' ).find( '.start-date' ).addClass( 'disabled' );
                $( this ).parents( '.enrollment-dates' ).find( '.start-date input' ).attr( 'disabled', 'disabled' );
                $( this ).parents( '.enrollment-dates' ).find( '.end-date' ).addClass( 'disabled' );
                $( this ).parents( '.enrollment-dates' ).find( '.end-date input' ).attr( 'disabled', 'disabled' );
            } else {
                $( this ).parents( '.enrollment-dates' ).find( '.start-date' ).removeClass( 'disabled' );
                $( this ).parents( '.enrollment-dates' ).find( '.start-date input' ).removeAttr( 'disabled' );
                $( this ).parents( '.enrollment-dates' ).find( '.end-date' ).removeClass( 'disabled' );
                $( this ).parents( '.enrollment-dates' ).find( '.end-date input' ).removeAttr( 'disabled' );
            }
        } );

        $( '[name="meta_course_open_ended"]' ).change( function () {
            if ( this.checked ) {
                $( this ).parents( '.course-dates' ).find( '.end-date' ).addClass( 'disabled' );
                $( this ).parents( '.course-dates' ).find( '.end-date input' ).attr( 'disabled', 'disabled' );
            } else {
                $( this ).parents( '.course-dates' ).find( '.end-date' ).removeClass( 'disabled' );
                $( this ).parents( '.course-dates' ).find( '.end-date input' ).removeAttr( 'disabled' );
            }
        } );
        // ===== END DATE PICKERS =====

        // Spinners
        $( ".spinners" ).spinner();


        $( '[name="meta_class_limited"]' ).change( function () {
            console.log( 'yup' );
            console.log( $( this ).parents( '.class-size' ).find( '.num-students' ) );
            if ( this.checked ) {
                console.log( 'checked' );
                $( this ).parents( '.class-size' ).find( '.num-students' ).removeClass( 'disabled' );
                $( this ).parents( '.class-size' ).find( '.num-students input' ).removeAttr( 'disabled' );
            } else {
                console.log( 'unchecked' );
                $( this ).parents( '.class-size' ).find( '.num-students' ).addClass( 'disabled' );
                $( this ).parents( '.class-size' ).find( '.num-students input' ).attr( 'disabled', 'disabled' );
            }
        } );

        // ====== COURSEPRESS UI TOGGLES =====
        $( '.coursepress-ui-toggle-switch' ).coursepress_ui_toggle();

    }

    function bind_buttons() {

        // NEXT BUTTON
        $( '.step-content .button.step.prev, .step-content .button.step.next, .step-content .button.step.update, .step-content .button.step.finish' ).on( 'click', function ( e ) {

            var target = e.currentTarget;

            var step;
            var action_type;
            var next_step;

            // Get the right step
            step = $( target ).hasClass( 'step-1' ) ? 1 : null;
            step = $( target ).hasClass( 'step-2' ) ? 2 : step;
            step = $( target ).hasClass( 'step-3' ) ? 3 : step;
            step = $( target ).hasClass( 'step-4' ) ? 4 : step;
            step = $( target ).hasClass( 'step-5' ) ? 5 : step;
            step = $( target ).hasClass( 'step-6' ) ? 6 : step;

            // Get the type
            action_type = $( target ).hasClass( 'prev' ) ? 'prev' : null;
            action_type = $( target ).hasClass( 'next' ) ? 'next' : action_type;
            action_type = $( target ).hasClass( 'update' ) ? 'update' : action_type;
            action_type = $( target ).hasClass( 'finish' ) ? 'finish' : action_type;

            if ( null !== step ) {
                $( '.step-title.step-' + step ).find( '.status' ).removeClass( 'saved' );
                $( '.step-title.step-' + step ).find( '.status' ).removeClass( 'save-error' );
                $( '.step-title.step-' + step ).find( '.status' ).removeClass( 'save-attention' );
                $( '.step-title.step-' + step ).find( '.status' ).addClass( 'save-process' );
                CoursePress.Course.get_step( step, action_type );
                CoursePress.Course.save();
            }


        } );

        // BROWSE MEDIA BUTTONS
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

        // ADD INSTRUCTOR
        $( '.button.instructor-assign' ).on( 'click', function ( e ) {

            var instructor_id = parseInt( $( $( 'select[name="instructors"]' )[ 0 ] ).val() );
            var instructor_name = $( $( 'select[name="instructors"]' )[ 0 ] )[ 0 ].textContent;

            CoursePress.Course.set( 'action', 'add_instructor' );
            var data = {
                instructor_id: instructor_id,
                course_id: _coursepress.course_id,
                instructor_name: instructor_name
            };
            CoursePress.Course.set( 'data', data );
            CoursePress.Course.save();

        } );


        // REMOVE INSTRUCTOR
        bind_remove_button( '.instructor-avatar-holder .instructor-remove a' );
        bind_remove_button( '.instructor-avatar-holder .invite-remove a', true );

        // INSTRUCTOR INVITATIONS
        // Submit Invite on 'Return/Enter'
        $( '.instructor-invite input' ).keypress( function ( event ) {
            if ( event.which == 13 ) {
                switch ( $( this ).attr( 'name' ) ) {

                    case "invite_instructor_first_name":
                        $( '[name=invite_instructor_last_name]' ).trigger( 'focus' );
                        break;
                    case "invite_instructor_last_name":
                        $( '[name=invite_instructor_email]' ).trigger( 'focus' );
                        break;
                    case "invite_instructor_email":
                    case "invite_instructor_trigger":
                        $( '#invite-instructor-trigger' ).trigger( 'click' );
                        $( '[name=invite_instructor_first_name]' ).trigger( 'focus' );
                        break;
                }
                event.preventDefault();
            }
        } );

        $( '#invite-instructor-trigger' ).on( 'click', function ( e ) {

            // Really basic validation
            var email = $( '[name=invite_instructor_email]' ).val();
            var first_name = $( '[name=invite_instructor_first_name]' ).val();
            var last_name = $( '[name=invite_instructor_last_name]' ).val();
            var parent = $( e.currentTarget ).parent();

            var email_valid = email.match( _coursepress.email_validation_pattern ) !== null

            if ( email_valid ) {

                CoursePress.Course.set( 'action', 'invite_instructor' );
                var data = {
                    first_name: first_name,
                    last_name: last_name,
                    email: email,
                    course_id: _coursepress.course_id
                };
                CoursePress.Course.set( 'data', data );
                CoursePress.Course.save();

            } else {
                console.log( 'DO SOMETHING TO THE UI!' );
            }

        } );

        $( '[name="meta_enrollment_type"]' ).on( 'change', function () {

            var options = $( this ).val();
            $( '.step-content.step-6 .enrollment-type-options' ).addClass( 'hidden' );
            $( '.step-content.step-6 .enrollment-type-options.' + options ).removeClass( 'hidden' );

        } );


        // "This is a paid course" checkbox
        $( '[name="meta_payment_paid_course"]' ).on( 'change', function () {
            if ( this.checked ) {
                $( this ).parents( '.step-content.step-6' ).find( '.payment-message' ).removeClass( 'hidden' );
                $( this ).parents( '.step-content.step-6' ).find( '.is_paid_toggle' ).removeClass( 'hidden' );
            } else {
                $( this ).parents( '.step-content.step-6' ).find( '.payment-message' ).addClass( 'hidden' );
                $( this ).parents( '.step-content.step-6' ).find( '.is_paid_toggle' ).addClass( 'hidden' );
            }
        } );

        $( '[name="publish-course-toggle"]' ).on( 'change', function ( e, state ) {

            var nonce = $( this ).attr( 'data-nonce' );
            var status = 'off' === state ? 'draft' : 'publish';
            CoursePress.Course.set( 'action', 'toggle_course_status' );
            var data = {
                nonce: nonce,
                status: status,
                state: state,
                course_id: _coursepress.course_id
            };
            CoursePress.Course.set( 'data', data );
            CoursePress.Course.save();

        } );

    }

    /**
     * Used to bind instructor boxes. Separated to be invoked on individual buttons.
     * @param selector
     */
    function bind_remove_button( selector, remove_pending ) {

        if ( undefined === remove_pending ) {
            remove_pending = false;
        }

        $( selector ).on( 'click', function ( e ) {

            var target = e.currentTarget;

            if ( !remove_pending ) {
                var instructor_id = parseInt( $( $( target ).parents( '.instructor-avatar-holder' )[ 0 ] ).attr( 'id' ).match( /instructor_holder_\d{1,10}/g )[ 0 ].trim().split( '_' ).pop() );

                // Confirm before deleting
                if ( confirm( _coursepress.instructor_delete_confirm ) ) {
                    CoursePress.Course.set( 'action', 'delete_instructor' );
                    var data = { instructor_id: instructor_id, course_id: _coursepress.course_id };
                    CoursePress.Course.set( 'data', data );
                    CoursePress.Course.save();
                }
            } else {
                var invite_code = $( $( target ).parents( '.instructor-avatar-holder' )[ 0 ] ).attr( 'id' ).replace( 'instructor_holder_', '' );

                // Confirm before deleting
                if ( confirm( _coursepress.instructor_delete_invite_confirm ) ) {
                    CoursePress.Course.set( 'action', 'delete_instructor_invite' );
                    var data = { invite_code: invite_code, course_id: _coursepress.course_id };
                    CoursePress.Course.set( 'data', data );
                    CoursePress.Course.save();
                }
            }


        } );
    }

    function bind_coursepress_events() {


        /**
         * COURSE UPDATE
         */
        CoursePress.Course.on( 'coursepress:update_course_success', function ( data ) {

            $( '.step-title.step-' + data.last_step ).find( '.status' ).addClass( 'saved' );
            $( '.step-title.step-' + data.last_step ).find( '.status' ).removeClass( 'save-error' );
            $( '.step-title.step-' + data.last_step ).find( '.status' ).removeClass( 'save-attention' );
            $( '.step-title.step-' + data.last_step ).find( '.status' ).removeClass( 'save-process' );
            if ( data.next_step != data.last_step ) {
                $( '.step-title.step-' + data.next_step ).click();
            }

        } );

        CoursePress.Course.on( 'coursepress:update_course_error', function ( data ) {

            $( '.step-title.step-' + data.last_step ).find( '.status' ).removeClass( 'saved' );
            $( '.step-title.step-' + data.last_step ).find( '.status' ).addClass( 'save-error' );
            $( '.step-title.step-' + data.last_step ).find( '.status' ).removeClass( 'save-attention' );
            $( '.step-title.step-' + data.last_step ).find( '.status' ).removeClass( 'save-process' );

            console.log( data );
        } );

        /**
         * INSTRUCTOR ACTIONS
         */
        CoursePress.Course.on( 'coursepress:add_instructor_success', function ( data ) {

            var remove_buttons = true; // permission required
            var content = '';
            if ( remove_buttons ) {
                content += '<div class="instructor-avatar-holder" id="instructor_holder_' + data.instructor_id + '"><div class="instructor-status"></div><div class="instructor-remove"><a><span class="dashicons dashicons-dismiss"></span></a></div>' + _coursepress.instructor_avatars[ data.instructor_id ] + '<span class="instructor-name">' + data.instructor_name + '</span></div><input type="hidden" id="instructor_' + data.instructor_id + '" name="instructor[]" value="' + data.instructor_id + '" />';
            } else {
                content += '<div class="instructor-avatar-holder" id="instructor_holder_' + data.instructor_id + '"><div class="instructor-status"></div>' + _coursepress.instructor_avatars[ data.instructor_id ] + '<span class="instructor-name">' + data.instructor_name + '</span></div><input type="hidden" id="instructor_' + data.instructor_id + '" name="instructor[]" value="' + data.instructor_id + '" />';
            }

            if ( $( '.instructor-avatar-holder.empty' ).length > 0 ) {
                $( '.instructor-avatar-holder.empty' ).detach();
            }

            if ( $( '#instructor_holder_' + data.instructor_id ).length === 0 ) {
                $( '#instructors-info' ).append( content );
                bind_remove_button( '#instructor_holder_' + data.instructor_id + ' .instructor-remove a' );
            }

        } );

        CoursePress.Course.on( 'coursepress:delete_instructor_success', function ( data ) {

            var empty_holder = '<div class="instructor-avatar-holder empty"><span class="instructor-name">' + _coursepress.instructor_empty_message + '</span></div>';

            // Remove Instructor Avatar
            $( '#instructor_holder_' + data.instructor_id ).detach();

            if ( $( '.instructor-avatar-holder' ).length === 0 ) {
                $( '#instructors-info' ).append( empty_holder );
            }


        } );

        CoursePress.Course.on( 'coursepress:invite_instructor_success', function ( data ) {
            var email = data.data.email;
            var invite_code = data.invite_code;
            var img = CoursePress.utility.get_gravatar_image( email, 80 );

            var remove_buttons = true; // permission required
            var content = '';
            var message = '';

            if ( remove_buttons ) {
                content += '<div class="instructor-avatar-holder pending-invite" id="instructor_holder_' + invite_code + '"><div class="instructor-status">' + _coursepress.instructor_pednding_status + '</div><div class="invite-remove"><a><span class="dashicons dashicons-dismiss"></span></a></div>' + img + '<span class="instructor-name">' + data.data.first_name + ' ' + data.data.last_name + '</span></div>';
            } else {
                content += '<div class="instructor-avatar-holder pending-invite" id="instructor_holder_' + invite_code + '"><div class="instructor-status">' + _coursepress.instructor_pednding_status + '</div>' + img + '<span class="instructor-name">' + data.data.first_name + ' ' + data.data.last_name + '</span></div>';
            }

            if ( $( '.instructor-avatar-holder.empty' ).length > 0 ) {
                $( '.instructor-avatar-holder.empty' ).detach();
            }

            if ( $( '#instructor_holder_' + invite_code ).length === 0 ) {
                $( '#instructors-info' ).append( content );
                bind_remove_button( '#instructor_holder_' + invite_code + ' .invite-remove a', true );

                message = ' <span class="message"><span class="dashicons dashicons-yes"></span> ' + data.message[ 'sent' ] + '</span>';

            } else {
                message = ' <span class="message"><span class="dashicons dashicons-yes"></span> ' + data.message[ 'exists' ] + '</span>';
            }

            $( '.instructor-invite .submit-message' ).append( message );
            $( '.instructor-invite .submit-message .message' ).fadeOut( 3000 );


        } );

        CoursePress.Course.on( 'coursepress:invite_instructor_error', function ( data ) {

            message = ' <span class="message"><span class="dashicons dashicons-yes"></span> ' + data.message[ 'send_error' ] + '</span>';
            $( '.instructor-invite .submit-message' ).append( message );
            $( '.instructor-invite .submit-message .message' ).fadeOut( 3000 );

        } );

        CoursePress.Course.on( 'coursepress:delete_instructor_invite_success', function ( data ) {
            $( '#instructor_holder_' + data.invite_code ).detach();
        } );

        CoursePress.Course.on( 'coursepress:toggle_course_status_success', function ( data ) {
            $( '[name="publish-course-toggle"]' ).attr( 'data-nonce', data.nonce );
        } );

        CoursePress.Course.on( 'coursepress:toggle_course_status_error', function ( data ) {

            // Toggle back
            var toggle = $( '[name="publish-course-toggle"]' );
            if ( $( toggle ).hasClass( 'on' ) ) {
                $( toggle ).removeClass( 'on' ).addClass( 'off' );
            } else if ( $( toggle ).hasClass( 'off' ) ) {
                $( toggle ).removeClass( 'off' ).addClass( 'on' );
            }


        } );


    }


    // Try to keep only one of these blocks and use functions/objects instead
    $( document ).ready( function ( $ ) {

        setup_UI();
        bind_buttons();
        bind_coursepress_events();

        // Get setup marker and advance accordion
        var setup_marker = $( '#course-setup-steps .step-title .status.setup_marker' ).click();
        //if ( $( setup_marker ).length > 0 ) {
        //    var step;
        //
        //    setup_marker = parseInt( $( $( setup_marker ).parents( '.step-title' )[ 0 ] ).attr( 'class' ).match( /step-\d{1,10}/g )[ 0 ].trim().split( '-' ).pop() )
        //    setup_marker = ( setup_marker + 1 ) > 6 ? 6 : setup_marker + 1;
        //    $( '#course-setup-steps .step-title.step-' + setup_marker ).click();
        //}

    } );


})( jQuery );


