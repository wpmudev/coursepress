var CoursePress = CoursePress || {};

(function ( $ ) {

    CoursePress.Views = CoursePress.Views || {};
    CoursePress.Models = CoursePress.Models || {};
    CoursePress.Collections = CoursePress.Collections || {};
    CoursePress.Helpers = CoursePress.Helpers || {};


    /** Expand the CoursePress Object for handling Modules **/
    CoursePress.Helpers.Module = CoursePress.Helpers.Module || {};

    CoursePress.Helpers.Module.refresh_ui = function () {

        // Bring on the Visual Editor
        $.each( $( '.unit-builder-modules .editor' ), function ( index, editor ) {
            var id = $( editor ).attr( 'id' );

            // Get rid of redundant editors... easier than trying to unload and recreate
            var search = id.split( '_' );
            search.pop();
            search = search.join( '_' );
            var match = new RegExp( search, "gi" );
            $.each( tinyMCEPreInit.mceInit, function ( subindex, subeditor ) {
                var subid = subeditor.selector.replace( '#', '' );
                if ( match.test( subid ) && subid !== id ) {
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

            var content = $( '#' + id ).val();
            var name = $( editor ).attr( 'name' );
            var height = $( editor ).attr( 'data-height' ) ? $( editor ).attr( 'data-height' ) : 400;

            CoursePress.editor.create( editor, id, name, content, false, height );

        } );

        // Fix Accordion
        if ( $( '.unit-builder-modules' ).hasClass( 'ui-accordion' ) ) {
            $( '.unit-builder-modules' ).accordion( 'destroy' );
        }

        var element = 0;
        if ( CoursePress.UnitBuilder.activeModuleRef && CoursePress.UnitBuilder.activeModuleRef.length > 0 ) {
            var active = $( '[data-cid="' + CoursePress.UnitBuilder.activeModuleRef + '"]' )[ 0 ];
            var element = parseInt( $( active ).attr( 'data-order' ) ) - 1;
        }

        // Pass in heightStyle or it chops off the bottom of modules.
        var self = this;
        $( '.unit-builder-modules' ).accordion( {
            heightStyle: "content",
            collapsible: true,
            header: "> div > h3",
            active: element
        } ).sortable( {
            axis: "y",
            handle: "h3",
            stop: function ( event, ui ) {
                ui.item.children( "h3" ).triggerHandler( "focusout" );

                var modules = $( '.module-holder' );
                $.each( modules, function ( index, module ) {

                    var current_order = parseInt( $( module ).attr( 'data-order' ) );
                    var new_order = index + 1;
                    var cid = $( module ).attr( 'data-cid' );

                    $( module ).attr( 'data-order', new_order );
                    if ( current_order !== new_order ) {
                        CoursePress.UnitBuilder.module_collection._byId[ cid ].set_meta( 'module_order', new_order );
                        //self.parentView.module_collection._byId[ cid ].set( 'flag', 'dirty' );
                        $( module ).addClass( 'dirty' );
                    }

                } );


                $( this ).accordion( "refresh" );
            }
        } );

        // Sortable Tabs
        $( '.unit-builder-tabs ul' ).sortable( {

            stop: function ( event, ui ) {

                var units = $( '.unit-builder-tabs ul li' );
                $.each( units, function ( index, unit ) {

                    var current_order = parseInt( $( unit ).attr( 'data-order' ) );
                    var new_order = index + 1;
                    var cid = $( unit ).attr( 'data-cid' );

                    $( unit ).attr( 'data-order', new_order );

                    if ( current_order !== new_order ) {
                        var meta = CoursePress.UnitBuilder.unit_collection._byId[ cid ].get( 'meta' );
                        meta[ 'unit_order' ] = new_order;
                        CoursePress.UnitBuilder.unit_collection._byId[ cid ].set( 'meta', meta );
                        CoursePress.UnitBuilder.unit_collection._byId[ cid ].set( 'flag', 'dirty' );
                        $( unit ).addClass( 'dirty' );
                    }

                } );

            }

        } );

        // Attach Media Browser behavior
        $( '.button.browse-media-field' ).browse_media_field();

        $( '.unit-builder-pager ul li' ).removeClass( 'active' );
        $( '.unit-builder-pager ul li[data-page="' + CoursePress.UnitBuilder.activePage + '"]' ).addClass( 'active' );

        // ====== COURSEPRESS UI TOGGLES =====
        if ( 'publish' === CoursePress.UnitBuilder.activeUnitStatus ) {
            $( '#unit-live-toggle' ).removeClass( 'off' );
            $( '#unit-live-toggle' ).addClass( 'on' );
            $( '#unit-live-toggle-2' ).removeClass( 'off' );
            $( '#unit-live-toggle-2' ).addClass( 'on' );
        } else {
            $( '#unit-live-toggle' ).removeClass( 'on' );
            $( '#unit-live-toggle' ).addClass( 'off' );
            $( '#unit-live-toggle-2' ).removeClass( 'on' );
            $( '#unit-live-toggle-2' ).addClass( 'off' );
        }
        $( '.coursepress-ui-toggle-switch' ).coursepress_ui_toggle();

        // Delete Page button
        if ( CoursePress.UnitBuilder.totalPages === 1 ) {
            $( '.unit-delete-page-button' ).addClass( 'hidden' );
        } else {
            $( '.unit-delete-page-button' ).removeClass( 'hidden' );
        }

        // Make pages droppable
        $( '.unit-builder-pager ul li[data-page]' ).droppable( {
            activeClass: 'page-droppable',
            hoverClass: 'page-droppable-hover',
            accept: '.unit-builder-modules .group',
            tolerance: "pointer",
            drop: function ( event, ui ) {
                var el = event.target;
                var page = parseInt( $( el ).attr( 'data-page' ) );
                var current_page = CoursePress.UnitBuilder.activePage;

                var mod_el = ui.draggable;

                if ( current_page !== page ) {

                    var mod_ref = $( $( mod_el ).find( '.module-holder' )[ 0 ] ).attr( 'data-cid' );
                    var meta = CoursePress.UnitBuilder.module_collection._byId[ mod_ref ].get( 'meta' );
                    meta[ 'module_page' ] = page;
                    meta[ 'module_order' ] = meta[ 'module_order' ] + 900;
                    CoursePress.UnitBuilder.module_collection._byId[ mod_ref ].set( 'meta', meta );
                    CoursePress.UnitBuilder.module_collection._byId[ mod_ref ].trigger( 'change' );
                    $( mod_el ).detach();
                } else {

                }

            }
        } );

        // ===== DATE PICKERS =====
        $( '.dateinput' ).datepicker( {
            dateFormat: 'yy-mm-dd'
            //firstDay: coursepress.start_of_week
        } );
        $( '.date' ).off( 'click' );
        $( '.date' ).on( 'click', function ( event ) {
            //if ( !$( this ).parents( 'div' ).hasClass( 'disabled' ) ) {
            $( this ).find( '.dateinput' ).datepicker( "show" );
            //}
        } );

        // Fix heights if needed
        var button_position = $( '.button-add-new-unit' ).position().top + $( '.button-add-new-unit' ).innerHeight() + 20;
        var current_min = parseFloat( $( '#unit-builder .tab-content' ).css( 'min-height' ).replace( 'px', '' ) );
        if ( current_min < 818 ) {
            current_min = 818;
            $( '#unit-builder .tab-content' ).css( 'min-height', current_min + 'px' );
        }
        if ( current_min < button_position ) {
            $( '#unit-builder .tab-content' ).css( 'min-height', button_position + 'px' );
        }


    }

    CoursePress.Helpers.Module.save_unit = function ( e, custom_event ) {
        $( '.unit-buttons .unit-save-button' ).prepend( '<i class="fa fa-spinner fa-spin save-progress"></i> ' );

        var nonce = $( '#unit-builder' ).attr( 'data-nonce' );

        // Save modules first... just in case the unit is deleted to avoid orphans
        CoursePress.UnitBuilder.module_collection.url = _coursepress._ajax_url + '?action=unit_builder&task=modules_update&course_id=' + _coursepress.course_id + '&unit_id=' + CoursePress.UnitBuilder.activeUnitID + '&page=' + CoursePress.UnitBuilder.activePage + '&wp_nonce=' + nonce;
        Backbone.sync( 'update', CoursePress.UnitBuilder.module_collection, {
            success: function ( response, status, options ) {
                $( '#unit-builder' ).attr( 'data-nonce', response[ 'nonce' ] );
            },
            'error': function ( response, status, options ) {

            }
        } );

        // Now update the units
        CoursePress.UnitBuilder.unit_collection.url = _coursepress._ajax_url + '?action=unit_builder&task=units_update&course_id=' + _coursepress.course_id + '&wp_nonce=' + nonce;
        Backbone.sync( 'update', CoursePress.UnitBuilder.unit_collection, {
            success: function ( response, status, options ) {
                $( '.save-progress' ).detach();
                nonce = response[ 'nonce' ];
                $( '#unit-builder' ).attr( 'data-nonce', nonce );
                CoursePress.UnitBuilder.unit_collection.trigger( custom_event );
            },
            error: function ( response, status, options ) {
                $( '.save-progress' ).detach();
                $( e.currentTarget ).prepend( '<i class="fa fa-info-circle save-progress"></i> ' );
            }
        } );

        // Reset URL
        CoursePress.UnitBuilder.unit_collection.url = _coursepress._ajax_url + '?action=unit_builder&task=units&course_id=' + _coursepress.course_id;
    }

    CoursePress.Helpers.Module.toggle_unit_state = function ( e ) {
        var nonce = $( '#unit-builder' ).attr( 'data-nonce' );

        this.switch = function ( state, unit_id, unit_ref ) {

            if ( 'publish' === state ) {
                $( '#unit-live-toggle' ).removeClass( 'off' );
                $( '#unit-live-toggle' ).addClass( 'on' );
                $( '#unit-live-toggle-2' ).removeClass( 'off' );
                $( '#unit-live-toggle-2' ).addClass( 'on' );
            } else {
                $( '#unit-live-toggle' ).removeClass( 'on' );
                $( '#unit-live-toggle' ).addClass( 'off' );
                $( '#unit-live-toggle-2' ).removeClass( 'on' );
                $( '#unit-live-toggle-2' ).addClass( 'off' );
            }

            var current_state = CoursePress.UnitBuilder.unit_collection._byId[ unit_ref ].get( 'post_status' )

            if ( current_state !== state ) {
                CoursePress.UnitBuilder.unit_collection._byId[ unit_ref ].set( 'post_status', state );
                CoursePress.UnitBuilder.unit_collection._byId[ unit_ref ].trigger( 'change' );

                // Toggle the dot
                $( '.unit-builder-tabs [data-tab="' + unit_id + '"]' ).removeClass( 'unit-draft' );
                $( '.unit-builder-tabs [data-tab="' + unit_id + '"]' ).removeClass( 'unit-live' );
                if ( 'publish' === state ) {
                    $( '.unit-builder-tabs [data-tab="' + unit_id + '"]' ).addClass( 'unit-live' );
                } else {
                    $( '.unit-builder-tabs [data-tab="' + unit_id + '"]' ).addClass( 'unit-draft' );
                }

            }

        }

        self = this;

        var unit_id = CoursePress.UnitBuilder.activeUnitID;
        var unit_ref = CoursePress.UnitBuilder.activeUnitRef;
        var state = CoursePress.UnitBuilder.unit_collection._byId[ unit_ref ].get( 'post_status' );
        state = 'publish' === state ? 'draft' : 'publish';

        CoursePress.UnitBuilder.unit_collection.url = _coursepress._ajax_url + '?action=unit_builder&task=unit_toggle&course_id=' + _coursepress.course_id + '&wp_nonce=' + nonce + '&state=' + state + '&unit_id=' + unit_id;
        Backbone.sync( 'update', CoursePress.UnitBuilder.unit_collection, {
            success: function ( response, status, options ) {
                self.switch( response[ 'post_status' ], unit_id, unit_ref );
                $( '#unit-builder' ).attr( 'data-nonce', response[ 'nonce' ] );
            },
            error: function ( response, status, options ) {
                self.switch( response[ 'post_status' ], unit_id, unit_ref );
                $( '#unit-builder' ).attr( 'data-nonce', response[ 'nonce' ] );
            }
        } );

        // Reset URL
        CoursePress.UnitBuilder.unit_collection.url = _coursepress._ajax_url + '?action=unit_builder&task=units&course_id=' + _coursepress.course_id;

    }


    // Start Rendering the Module
    CoursePress.Helpers.Module.render_module = function ( module, current_order ) {

        var types = _coursepress.unit_builder_module_types;
        var labels = _coursepress.unit_builder_module_labels;
        var content;
        var data;

        if ( module.module_type() && _coursepress.unit_builder_templates[ module.module_type() ].trim().length > 0 ) {
            data = JSON.parse( _coursepress.unit_builder_templates[ module.module_type() ] );
        }

        if ( undefined === data || undefined === _coursepress.unit_builder_module_types[ data[ 'type' ] ] ) {
            return '';
        }

        // Replace template data
        data[ 'id' ] = module.get( 'ID' );
        data[ 'title' ] = module.get( 'post_title' );
        data[ 'duration' ] = module.get_meta( 'duration' );
        data[ 'type' ] = module.module_type();
        data[ 'mode' ] = types[ data[ 'type' ] ][ 'mode' ];
        data[ 'show_title' ] = module.fix_boolean( module.get_meta( 'show_title' ) );
        data[ 'mandatory' ] = module.fix_boolean( module.get_meta( 'mandatory' ) );
        data[ 'assessable' ] = module.fix_boolean( module.get_meta( 'assessable' ) );
        data[ 'minimum_grade' ] = module.get_meta( 'minimum_grade', 100 );
        data[ 'allow_retries' ] = module.fix_boolean( module.get_meta( 'allow_retries' ) );
        data[ 'retry_attempts' ] = module.get_meta( 'retry_attempts', 0 );
        var post_content = module.get( 'post_excerpt' );
        post_content = post_content && post_content.length > 0 ? post_content : module.get( 'post_content' );
        data[ 'content' ] = post_content.trim();
        data[ 'order' ] = module.get_meta( 'order', 0 );
        data[ 'page' ] = module.get_meta( 'page', 1 );

        content = '<h3 class="module-holder-title ' + data[ 'type' ] + '"><span class="label">' + data[ 'title' ] + '</span><span class="module-type">' + types[ data[ 'type' ] ][ 'title' ] + '</span></h3>' +
        '<div class="module-holder ' + data[ 'type' ] + ' mode-' + data[ 'mode' ] + '" data-id="' + data[ 'id' ] + '" data-type="' + data[ 'type' ] + '" data-order="' + current_order + '" data-cid="' + module.cid + '">';

        // Display the body of the module?
        if ( ( types[ data[ 'type' ] ][ 'body' ] && 'hidden' !== types[ data[ 'type' ] ][ 'body' ] ) || !types[ data[ 'type' ] ][ 'body' ] ) {

            content += '<div class="module-header">' +
            '<label class="module-title"><span class="label">' + labels[ 'module_title' ] + '</span>' +
            '<span class="description">' + labels[ 'module_title_desc' ] + '</span>' +
            '<input class="module-title-text" type="text" name="post_title" value="' + data[ 'title' ] + '" /></label>';

            content += '<input type="hidden" name="meta_module_type" value="' + data[ 'type' ] + '" />';

            content +=
                '<label class="module-duration"><span class="label">' + labels[ 'module_duration' ] + '</span>' +
                '<input type="text" name="meta_duration" value="' + data[ 'duration' ] + '" /></label>';

            // Show Title
            content += '<label class="module-show-title">' +
            '<input type="checkbox" name="meta_show_title[' + module.cid + ']" value="1" ' + CoursePress.utility.checked( data[ 'show_title' ], 1 ) + ' />' +
            '<span class="label">' + labels[ 'module_show_title' ] + '</span>' +
            '<span class="description">' + labels[ 'module_show_title_desc' ] + '</span>' +
            '</label>';


            // Only for user inputs
            if ( 'input' === data[ 'mode' ] ) {

                // Mandatory
                content += '<label class="module-mandatory">' +
                '<input type="checkbox" name="meta_mandatory[' + module.cid + ']" value="1" ' + CoursePress.utility.checked( data[ 'mandatory' ], 1 ) + ' />' +
                '<span class="label">' + labels[ 'module_mandatory' ] + '</span>' +
                '<span class="description">' + labels[ 'module_mandatory_desc' ] + '</span>' +
                '</label>';

                // Assessable
                content += '<label class="module-assessable">' +
                '<input type="checkbox" name="meta_assessable[' + module.cid + ']" value="1" ' + CoursePress.utility.checked( data[ 'assessable' ], 1 ) + ' />' +
                '<span class="label">' + labels[ 'module_assessable' ] + '</span>' +
                '<span class="description">' + labels[ 'module_assessable_desc' ] + '</span>' +
                '</label>';

                // Minimum Grade
                content += '<label class="module-minimum-grade">' +
                '<span class="label">' + labels[ 'module_minimum_grade' ] + '</span>' +
                '<input type="text" name="meta_minimum_grade" value="' + data[ 'minimum_grade' ] + '" />' +
                '<span class="description">' + labels[ 'module_minimum_grade_desc' ] + '</span>' +
                '</label>';

                // Allow Retries
                content += '<label class="module-allow-retries">' +
                '<input type="checkbox" name="meta_allow_retries[' + module.cid + ']" value="1" ' + CoursePress.utility.checked( data[ 'allow_retries' ], 1 ) + ' />' +
                '<span class="label">' + labels[ 'module_allow_retries' ] + '</span>' +
                '<input type="text" name="meta_retry_attempts" value="' + data[ 'retry_attempts' ] + '" />' +
                '<span class="description">' + labels[ 'module_allow_retries_desc' ] + '</span>' +
                '</label>';
            }
            //
            //    // Excerpt
            if ( ( types[ data[ 'type' ] ][ 'excerpt' ] && 'hidden' !== types[ data[ 'type' ] ][ 'excerpt' ] ) || !types[ data[ 'type' ] ][ 'excerpt' ] ) {

                var textarea_name = '';
                // Use timestamps to make sure we get fresh editors each time we render... redundant editors are deleted later
                if ( 0 === parseInt( data[ 'id' ] ) || _.isNaN( parseInt( data[ 'id' ] ) ) ) {
                    textarea_name = 'post_content_' + new Date().getTime();
                } else {
                    var textarea_name = 'post_content_' + data[ 'id' ] + '_' + new Date().getTime();
                }

                var textareaID = textarea_name;

                var content_label = 'input' === data[ 'mode' ] ? labels[ 'module_question' ] : labels[ 'module_content' ];
                var content_descrtiption = 'input' === data[ 'mode' ] ? labels[ 'module_question_desc' ] : labels[ 'module_content_desc' ];
                var editor_height = data[ 'editor_height' ] ? 'data-height="' + data[ 'editor_height' ] + '"' : '';

                content += '<label class="module-excerpt">' +
                '<span class="label">' + content_label + '</span>' +
                '<span class="description">' + content_descrtiption + '</span>' +
                '<textarea class="editor" name="' + textarea_name + '" id="' + textareaID + '" ' + editor_height + '>' + data[ 'content' ] + '</textarea>' +
                '</label>';
            }

            // Now it gets tricky...
            content += '</div>';

            // RENDER COMPONENTS
            content += '<div class="module-components">' +
            CoursePress.Helpers.Module.render_components( module, data ) +
            '</div>';

        }

        // Delete Module
        content += '<div class="unit-buttons"><div class="button unit-delete-module-button"><i class="fa fa-trash-o"></i> ' + labels[ 'module_delete' ] + '</div></div>'

        content += '</div>';

        return content;

    }

    CoursePress.Helpers.Module.render_components = function ( module, data ) {
        var types = _coursepress.unit_builder_module_types;
        var labels = _coursepress.unit_builder_module_labels;

        var content = '';

        var components = _.isArray( data[ 'components' ] ) ? data[ 'components' ] : [];

        var component_data = {};

        // Deal with each components...
        $.each( components, function ( key, component ) {

            var label = component[ 'label' ] ? component[ 'label' ] : '';
            var description = component[ 'description' ] ? component[ 'description' ] : '';
            var label_class = component[ 'class' ] ? 'class="' + component[ 'class' ] + '"' : '';
            var component_key = key;
            var component_selector = 'module-component-' + component_key;

            content += '<div class="module-component ' + component_selector + '">' +
            '<label data-key="label" ' + label_class + '>' +
            '<span class="label">' + label + '</span>' +
            '<span class="description">' + description + '</span></label>';

            var items = _.isArray( component[ 'items' ] ) ? component[ 'items' ] : [];

            // Deal with each item of the components
            $.each( items, function ( idx, item ) {

                var item_type = item[ 'type' ] ? item[ 'type' ] : '';

                switch ( item_type ) {

                    case 'text-input':
                        var meta_value = item[ 'name' ].replace( 'meta_', '' );
                        meta_value = module.get_meta( meta_value );
                        var attr = item[ 'name' ] ? ' name="' + item[ 'name' ] + '"' : '';
                        attr += item[ 'class' ] ? ' class="' + item[ 'class' ] + '"' : '';
                        var label = item[ 'label' ] ? item[ 'label' ] : '';
                        var label_tag = item[ 'label_tag' ] ? item[ 'label_tag' ] : '';
                        var placeholder = item[ 'placeholder' ] ? item[ 'placeholder' ] : '';

                        if ( label.length > 1 ) {
                            content += '<' + label_tag + '>' + label + '</' + label_tag + '>';
                        }

                        content += '<input type="text"' + attr + ' value="' + meta_value + '" placeholder="' + placeholder + '" />';
                        break;

                    case 'text':
                        var attr = item[ 'class' ] ? ' class="' + item[ 'class' ] + '"' : '';
                        var text = item[ 'text' ] ? item[ 'text' ] : '';
                        content += '<div' + attr + '>' + text + '</div>';
                        break;

                    case 'select-select':
                    case 'radio-select':
                        //var attr = item[ 'name' ] ? ' name="' + item[ 'name' ] + '[]"' : '';
                        //attr += item[ 'class' ] ? ' class="' + item[ 'class' ] + '"' : '';
                        var name = item[ 'name' ] ? item[ 'name' ] : '';
                        var attr = item[ 'class' ] ? ' class="' + item[ 'class' ] + '"' : '';

                        var answers = module.get_meta( 'answers' );

                        //answers = answers.length > 0 ? CoursePress.utility.unserialize( answers ) : item['answers'];
                        answers = answers.length > 0 ? answers : item[ 'answers' ];

                        var selected = module.get_meta( 'answers_selected', parseInt( item[ 'selected' ] ) );
                        content += '<div class="answer-group">';
                        $.each( answers, function ( index, answer ) {

                            // Legacy answers
                            if ( _.isNaN( parseInt( selected ) ) ) {
                                selected = selected == answer ? index : selected;
                            }

                            option_name = name + '_selected[' + module.cid + ']';
                            content += '<div class="answer"><input type="radio" name="' + option_name + '" value="' + index + '" ' + CoursePress.utility.checked( parseInt( selected ), index ) + ' />';
                            content += '<input type="text" ' + attr + ' value="' + answer + '" name="' + name + '[]" /> <span class="remove-item"></span><i class="fa fa-trash-o"></i></span></div>';
                        } );
                        content += '</div>';
                        content += '<a class="add-item">' + _coursepress.unit_builder_add_answer_label + '</a>';
                        break;

                    case 'checkbox-select':
                        var name = item[ 'name' ] ? item[ 'name' ] : '';
                        var attr = item[ 'class' ] ? ' class="' + item[ 'class' ] + '"' : '';

                        var answers = module.get_meta( 'answers' );
                        //answers = answers.length > 0 ? CoursePress.utility.unserialize( answers ) : item['answers'];
                        answers = answers.length > 0 ? answers : item[ 'answers' ];

                        var selected = module.get_meta( 'answers_selected' );
                        //selected = selected.length > 0 ? CoursePress.utility.unserialize( selected ) : item['selected'];
                        selected = selected.length > 0 ? selected : item[ 'selected' ];

                        // Deal with legacy
                        if ( _.isNaN( parseInt( selected[ 0 ] ) ) ) {
                            $.each( selected, function ( index, item ) {
                                selected[ index ] = _.indexOf( answers, item );
                            } );
                        }

                        content += '<div class="answer-group">';
                        $.each( answers, function ( index, answer ) {
                            var checked = _.indexOf( selected, index ) > -1 ? 'checked="checked"' : '';
                            option_name = name + '_selected[' + module.cid + '][]';
                            content += '<div class="answer"><input type="checkbox" name="' + option_name + '" value="' + index + '" ' + checked + ' />';
                            content += '<input type="text" ' + attr + ' value="' + answer + '" name="' + name + '[]" /> <span class="remove-item"><i class="fa fa-trash-o"></i></span></div>';
                        } );
                        content += '</div>';
                        content += '<a class="add-item">' + _coursepress.unit_builder_add_answer_label + '</a>';
                        break;

                    case 'media-caption-settings':

                        var container_class = item[ 'class' ] ? ' class="' + item[ 'class' ] + '"' : '';
                        var option_class = item[ 'option_class' ] ? ' class="' + item[ 'option_class' ] + '"' : '';
                        var show_caption = item[ 'label' ] ? item[ 'label' ] : '';
                        var media_caption = item[ 'option_labels' ] ? item[ 'option_labels' ][ 'media' ] : '';
                        var custom_caption = item[ 'option_labels' ] ? item[ 'option_labels' ][ 'custom' ] : '';
                        var placeholder = item[ 'placeholder' ] ? item[ 'placeholder' ] : '';
                        var enable_name = item[ 'enable_name' ] ? item[ 'enable_name' ] : '';
                        var option_name = item[ 'option_name' ] ? item[ 'option_name' ] : '';
                        var option_text_name = item[ 'input_name' ] ? item[ 'input_name' ] : '';
                        var no_caption = item[ 'no_caption' ] ? item[ 'no_caption' ] : '';

                        var show_caption_value = module.get_meta( enable_name );
                        var caption_type = module.get_meta( option_name );
                        var caption_text = module.get_meta( option_text_name );

                        enable_name = enable_name + '[' + module.cid + ']';
                        option_name = option_name + '[' + module.cid + ']';

                        content += '<div ' + container_class + '>' +
                        '<label><input type="checkbox" value="1" name="' + enable_name + '" ' + CoursePress.utility.checked( show_caption_value, 1 ) + ' />' +
                        '<span>' + show_caption + '</span></label>' +
                        '<div ' + option_class + '>' +
                        '<label><input type="radio" value="media" name="' + option_name + '" ' + CoursePress.utility.checked( caption_type, 'media' ) + ' />' +
                        '<span>' + media_caption + '</span></label>' +
                        '<div class="existing">' + no_caption + '</div>' +
                        '<label><input type="radio" value="custom" name="' + option_name + '" ' + CoursePress.utility.checked( caption_type, 'custom' ) + ' />' +
                        '<span>' + custom_caption + '</span></label><br />' +
                        '<input type="text" placeholder="' + placeholder + '" value="' + caption_text + '" name="' + option_text_name + '" />' +
                        '</div>' +
                        '</div>';

                        // Fetch caption asynchronously
                        if ( component_data.media_url && component_data.media_url.length > 0 ) {
                            CoursePress.utility.attachment_by_url( component_data.media_url, '.' + component_selector + ' .existing', no_caption );
                            component_data.media_url = null; // Ready for the next time.
                        }

                        break;

                    case 'media-browser':
                        var media_type = item[ 'media_type' ] ? item[ 'media_type' ] : 'image';
                        var class_value = item[ 'class' ] ? item[ 'class' ] : '';
                        var container_class = item[ 'container_class' ] ? item[ 'container_class' ] : '';
                        var button_text = item[ 'button_text' ] ? item[ 'button_text' ] : '';
                        var placeholder = item[ 'placeholder' ] ? item[ 'placeholder' ] : '';
                        var name = item[ 'name' ] ? item[ 'name' ] : '';
                        var id = name + '-' + component_key;

                        var value = module.get_meta( name, '' );
                        content += CoursePress.UI.browse_media_field( name, name, {
                            value: value,
                            type: media_type,
                            container_class: container_class,
                            textbox_class: class_value,
                            placeholder: placeholder,
                            button_text: button_text
                        } );

                        component_data.media_url = value;

                        break;

                    case 'checkbox':

                        var name = item[ 'name' ] ? item[ 'name' ] : '';
                        var label = item[ 'label' ] ? item[ 'label' ] : '';
                        var value = module.get_meta( name, '' );

                        option_name = name + '_selected[' + module.cid + ']';
                        content += '<label class="normal"><input type="checkbox" value="1" name="' + name + '" ' + CoursePress.utility.checked( value, 1 ) + ' />' +
                        '<span>' + label + '</span></label>';

                        break;
                }

            } );


            content += '</div>';

        } );


        return content;

    }

    /** Add the CoursePress Unit Builder Views **/
        // Parent View / Models / Collections
    CoursePress.Views.UnitBuilder = Backbone.View.extend( {
        initialize: function () {

            // Setup child views
            //this.tabView = new Backbone.View();
            //this.tabView.parentView = this;

            // Holds all the units for displaying
            this.unit_collection = new CoursePress.Collections.UnitTabs();
            this.unit_collection.url = _coursepress._ajax_url + '?action=unit_builder&task=units&course_id=' + _coursepress.course_id;
            this.unit_collection.fetch();

            // Holds the modules for the current unit
            this.module_collection = new CoursePress.Collections.UnitModules();

            // Displays the tabs
            this.tabViewCollection = new CoursePress.Views.UnitTabViewCollection( {
                model: this.unit_collection,
                tagName: 'ul',
                className: 'sticky-tabs'
            } );
            this.tabViewCollection.parentView = this;

            // Displays the unit information
            this.headerView = new CoursePress.Views.UnitBuilderHeader();
            this.headerView.parentView = this;

            // Displays the content
            this.contentView = new CoursePress.Views.UnitBuilderBody( {
                model: this.module_collection,
                className: 'unit-builder-body'
            } );
            this.contentView.parentView = this;

            this.activePage = 1;
            this.totalPages = 1;
            this.activeUnitStatus = 'draft';

            this.activeModuleRef = '';

            // Render the container
            this.render();
        },
        events: {
            'click .unit-save-button': 'saveUnit',
            'change #unit-live-toggle': 'toggleUnitState',
            'change #unit-live-toggle-2': 'toggleUnitState',
            'click .unit-delete-module-button': 'deleteModule',
            'click .unit-delete-page-button': 'deletePage',
            'click .unit-delete-button': 'deleteUnit',
            'click .button-add-new-unit': 'newUnit',
            'click .unit-builder-tabs ul.sticky-tabs li': 'changeActive'
        },
        render: function () {

            // Get the parent layout rendered first
            var template = _.template( $( '#unit-builder-template' ).html(), {} );
            this.$el.html( template );

            this.$( '.unit-builder-tabs .sticky-wrapper .tabs' )
                .replaceWith( this.tabViewCollection.el );

            this.$( '.unit-builder-header' )
                .append( this.headerView.el );

            this.$( '.unit-builder-body' )
                .replaceWith( this.contentView.el );

            // UI
            $( ".sticky-wrapper-tabs" ).sticky( { topSpacing: 45 } );


            return this;
        },
        fetchModules: function ( unit_id, page ) {
            this.module_collection.url = _coursepress._ajax_url + '?action=unit_builder&task=modules&course_id=' + _coursepress.course_id + '&unit_id=' + unit_id + '&page=' + page;
            this.module_collection.fetch();

            // Get the number of pages
            var meta = this.unit_collection._byId[ this.activeUnitRef ].get( 'meta' );
            this.totalPages = meta[ 'page_title' ] ? meta[ 'page_title' ].length : 1;
            this.totalPages = undefined === this.totalPages ? _.size( meta[ 'page_title' ] ) : 1;
            this.activeModuleRef = '';
        },
        saveUnit: function ( e ) {
            CoursePress.Helpers.Module.save_unit( e );
        },
        toggleUnitState: function ( e ) {
            CoursePress.Helpers.Module.toggle_unit_state( e );
        },
        deleteModule: function ( e ) {
            if ( confirm( _coursepress.unit_builder_delete_module_confirm ) ) {
                var el = e.currentTarget;
                var parent = $( el ).parents( '.module-holder' )[ 0 ];
                var model_ref = $( parent ).attr( 'data-cid' );
                this.module_collection.remove( model_ref );
                $( parent ).parents( '.group' ).detach();
            }
        },
        deletePage: function ( e ) {
            self = this;
            if ( confirm( _coursepress.unit_builder_delete_page_confirm ) ) {
                var page = parseInt( this.activePage );

                var meta = self.unit_collection._byId[ self.activeUnitRef ].get( 'meta' );
                var page_title = meta[ 'page_title' ];
                var show_page_title = meta[ 'show_page_title' ];

                // Update indexes
                var new_array = [];
                var new_object = {};
                var offset = 0;
                $.each( show_page_title, function ( index, value ) {
                    if ( index === ( page - 1) ) {
                        offset = -1;
                    }
                    if ( index !== ( page - 1 ) ) {
                        new_array.push( value );
                        var page_index = index + 1 + offset;
                        new_object[ 'page_' + page_index ] = page_title[ 'page_' + ( index + 1 ) ];
                    }
                } );
                meta[ 'page_title' ] = new_object;
                meta[ 'show_page_title' ] = new_array;
                self.unit_collection._byId[ self.activeUnitRef ].set( 'meta', meta );
                self.unit_collection._byId[ self.activeUnitRef ].trigger( 'change' );

                // Update page count
                self.totalPages = meta[ 'show_page_title' ].length;

                // All modules of this page will now be moved to the first page
                self.module_collection.each( function ( module ) {

                    var mod_meta = module.get( 'meta' );
                    mod_meta[ 'module_page' ] = 1;
                    mod_meta[ 'module_order' ] += 999;
                    module.set( 'meta', mod_meta );
                    module.trigger( 'change' );

                } );

                // Trigger save
                CoursePress.Helpers.Module.save_unit( e );
                // Trigger fetch
                self.fetchModules( self.activeUnitID, 1 );
            }
        },
        deleteUnit: function ( e ) {

            if ( confirm( _coursepress.unit_builder_delete_unit_confirm ) ) {
                this.unit_collection.remove( this.activeUnitRef );

                //UI Update
                $( 'ul li[data-tab="' + this.activeUnitID + '"]' ).detach();
                $( $( '.unit-builder-body' )[ 0 ] ).empty();
                $( '.unit-detail input[type="text"]' ).val( '' );
                $( '.unit-detail input[type="checkbox"]' ).removeAttr( 'checked' );
                $( '#unit-live-toggle' ).removeClass( 'on' ).addClass( 'off' );
                $( '#unit-live-toggle-2' ).removeClass( 'on' ).addClass( 'off' );

                // Update Unit Count
                $( $( 'li[data-tab="units"] a' )[ 0 ] ).html( $( $( 'li[data-tab="units"] a' )[ 0 ] ).html().replace( /\d+/, $( '.unit-builder-tabs ul li' ).length ) );

                CoursePress.Helpers.Module.save_unit( e );
            }

        },
        newUnit: function ( e ) {
            var self = this;
            //Count current elements
            var count = $( '.unit-builder-tabs .sticky-tabs li' ).length;

            var unit = new CoursePress.Models.Unit()
            unit.set_meta( 'unit_order', (count + 1) );
            unit.set_meta( 'page_title', { page_1: '' } );
            unit.set_meta( 'show_page_title', [ true ] );
            unit.set( 'post_title', _coursepress.unit_builder_new_unit_title );

            self.unit_collection.add( unit );
            CoursePress.Helpers.Module.save_unit( e, 'new_success' );

            var unitView = new CoursePress.Views.UnitTabView( { model: unit, tagName: 'li' } );
            var new_html = unitView.render().$el;

            $( '.button-add-new-unit .fa' ).removeClass( 'fa-plus-square' ).addClass( 'fa-spinner' ).addClass( 'fa-spin' );

            self.unit_collection.on( 'new_success', function () {
                self.unit_collection.fetch( {
                    success: function ( collection, response, options ) {
                        $( '.button-add-new-unit .fa' ).removeClass( 'fa-spin' ).removeClass( 'fa-spinner' ).addClass( 'fa-plus-square' );
                        $( '.unit-builder-tabs ul.sticky-tabs' ).append( new_html );
                        CoursePress.Helpers.Module.refresh_ui();
                        // Update Unit Count
                        $( $( 'li[data-tab="units"] a' )[ 0 ] ).html( $( $( 'li[data-tab="units"] a' )[ 0 ] ).html().replace( /\d+/, $( '.unit-builder-tabs ul li' ).length ) );
                    }
                } );

                self.unit_collection.off( 'new_success' );
            } );


        },
        changeActive: function ( e ) {

            $( '#unit-builder .tab-tabs li' ).removeClass( 'active' );
            $( e.currentTarget ).addClass( 'active' );

            var model_id = $( e.currentTarget ).attr( 'data-tab' );
            // Get appropriate model
            var self = this;

            this.unit_collection.each( function ( unit ) {
                if ( parseInt( model_id ) === parseInt( unit.get( 'ID' ) ) ) {
                    CoursePress.Helpers.changeUnit( unit, self );
                    //$( 'body,html' ).animate( {
                    //    scrollTop: $( '.section.unit-builder-header' ).offset().top - 20,
                    //    duration: 200
                    //} );
                }
            } );
        }

    } );

    // Unit Tab View / Models / Collections

    CoursePress.Models.Unit = Backbone.Model.extend( {
        initialize: function () {
            this.url = _coursepress._ajax_url + '?action=unit_builder&task=unit_update&course_id=' + _coursepress.course_id + '&unit_id=' + CoursePress.UnitBuilder.activeUnitID;
            this.on( 'change', this.process_changed, this );
            this.on( 'sync', this.model_saved, this );
        },
        set_meta: function ( key, value ) {

            key = key.replace( 'meta_', '' );

            var meta = this.get( 'meta' ) || {};

            meta[ key ] = value;

            this.set( 'flag', 'dirty' );
            this.set( 'meta', meta );
            this.trigger( 'change' );
        },
        set_page_title: function ( index, title ) {
            var meta = this.get( 'meta' ) || {};
            if ( meta[ 'page_title' ] ) {
                meta[ 'page_title' ][ 'page_' + index ] = title;
                this.set( 'meta', meta );
                this.trigger( 'change' );
            }
        },
        set_page_visibility: function ( index, value ) {
            var meta = this.get( 'meta' ) || {};
            var idx = index - 1;
            if ( meta[ 'show_page_title' ] ) {
                meta[ 'show_page_title' ][ idx ] = value;
                this.set( 'meta', meta );
                this.trigger( 'change' );
            }
        },
        get_page_title: function ( index ) {
            var meta = this.get( 'meta' ) || {};
            if ( meta[ 'page_title' ] ) {
                return meta[ 'page_title' ][ 'page_' + index ];
            } else {
                return '';
            }
        },
        get_page_visibility: function ( index ) {
            var meta = this.get( 'meta' ) || {};
            if ( meta[ 'show_page_title' ] ) {
                return meta[ 'show_page_title' ][ index ];
            } else {
                return true;
            }
        },
        process_changed: function () {
            this.set( 'flag', 'dirty' );
        },
        model_saved: function ( model, response, options ) {

        }

    } );
    CoursePress.Models.Module = Backbone.Model.extend( {
        initialize: function () {
            var nonce = $( '#unit-builder' ).attr( 'data-nonce' );
            this.url = _coursepress._ajax_url + '?action=unit_builder&task=module_add&course_id=' + _coursepress.course_id + '&unit_id=' + CoursePress.UnitBuilder.activeUnitID + +'&wp_nonce=' + nonce;
            this.on( 'change', this.process_changed, this );
            this.on( 'sync', this.model_saved, this );
        },
        get_meta: function ( key, default_value ) {

            key = key.replace( 'meta_', '' );

            if ( undefined === default_value ) {
                default_value = '';
            }

            var meta = this.get( 'meta' ) || {};
            var value = meta[ key ] ? meta[ key ] : default_value;

            var test_value = _.isString( value ) ? value.toLowerCase() : value;
            if ( test_value === 'yes' || test_value === 'on' || test_value === 'no' || test_value === 'off' ) {
                value = this.fix_boolean( value );
            }

            if ( value.length === 0 || value === false || value === 0 ) {
                value = this.get_legacy_meta( key, default_value );
            }

            return value;
        },
        get_legacy_meta: function ( key, default_value ) {

            switch ( key ) {
                case 'duration':
                    key = 'time_estimation';
                    break;
                case 'show_title':
                    key = 'show_title_on_front';
                    break;
                case 'mandatory':
                    key = 'mandatory_answer';
                    break;
                case 'assessable':
                    key = 'gradable_answer';
                    break;
                case 'minimum_grade':
                    key = 'minimum_grade_required';
                    break;
                case 'allow_retries':
                    var meta = this.get( 'meta' );
                    if ( meta[ 'limit_attempts' ] ) {
                        var value = meta[ 'limit_attempts' ][ 0 ];
                        // Invert answer
                        return !this.fix_boolean( value );
                    } else {
                        return default_value;
                    }
                    break;
                case 'retry_attempts':
                    key = 'limit_attempts_value';
                    break;
                case 'order':
                    key = 'module_order';
                    break;
                case 'page':
                    key = 'module_page';
                    break;
                case 'answers_selected':
                    key = 'input-radio' === this.module_type() ? 'checked_answer' : 'checked_answers';
                    break;
            }

            var meta = this.get( 'meta' ) || {};
            var value = meta[ key ] ? meta[ key ] : default_value;

            return value;
        },

        fix_legacy_module: function ( new_mod_type ) {
            var self = this;
            var meta = self.get( 'meta' );
            self.set( 'flag', 'dirty' );

            // Fix meta that needs fixing
            meta.module_type = new_mod_type;

            if ( meta[ 'checked_answer' ] )
                meta.answers_selected = meta[ 'checked_answer' ];
            if ( meta[ 'checked_answers' ] )
                meta.answers_selected = meta[ 'checked_answers' ];

            if ( meta[ 'time_estimation' ] )
                meta.duration = meta[ 'time_estimation' ];

            if ( meta[ 'show_title_on_front' ] )
                meta.show_title = self.fix_boolean( meta[ 'show_title_on_front' ] );

            if ( meta[ 'mandatory_answer' ] )
                meta.mandatory = self.fix_boolean( meta[ 'mandatory_answer' ] );

            if ( meta[ 'gradable_answer' ] )
                meta.assessable = self.fix_boolean( meta[ 'gradable_answer' ] );

            if ( meta[ 'minimum_grade_required' ] )
                meta.minimum_grade = meta[ 'minimum_grade_required' ];

            if ( meta[ 'limit_attempts' ] ) {
                var limited = self.fix_boolean( meta[ 'limit_attempts' ] );
                meta.allow_retries = !limited;
            }
            if ( meta[ 'limit_attempts_value' ] )
                meta.retry_attempts = meta[ 'limit_attempts_value' ];

            self.set( 'meta', meta );

        },

        map_legacy_type: function ( mod_type ) {
            var self = this;
            var legacy = {
                'audio_module': 'audio',
                'chat_module': 'chat',
                'checkbox_input_module': 'input-checkbox',
                'file_module': 'download',
                'file_input_module': 'input-upload',
                'image_module': 'image',
                'page_break_module': 'legacy',
                'radio_input_module': 'input-radio',
                'page_break_module': 'section',
                'section_break_module': 'section',
                'text_module': 'text',
                'text_input_module': 'input-text',
                'textarea_input_module': 'input-textarea',
                'video_module': 'video'
            }

            if ( mod_type in legacy ) {
                // Fix the text input
                if ( 'text_module' && 'single' !== this.get_meta( 'checked_length', 'single' ) ) {
                    mod_type = 'input-textarea';
                } else {
                    mod_type = legacy[ mod_type ];
                }

                self.fix_legacy_module( mod_type );
            }

            return mod_type;
        },
        module_type: function () {
            return this.map_legacy_type( this.get_meta( 'module_type' ) );
        },
        fix_boolean: function ( value ) {
            var test_value = _.isString( value ) ? value.toLowerCase() : value;
            return 1 === parseInt( test_value ) || 'on' === test_value || 'yes' === test_value || true === test_value;
        },
        set_meta: function ( key, value ) {

            key = key.replace( 'meta_', '' );

            var meta = this.get( 'meta' ) || {};
            meta[ key ] = value;

            this.set( 'flag', 'dirty' );
            this.set( 'meta', meta );
            this.trigger( 'change' );
        },
        from_template: function ( template ) {

            data = JSON.parse( _coursepress.unit_builder_templates[ template ] )
            this.set( 'ID', data[ 'id' ] );
            this.set( 'post_title', data[ 'title' ] );
            this.set_meta( 'duration', data[ 'duration' ] || '1:00' );
            this.set_meta( 'module_type', data[ 'type' ] );
            this.set_meta( 'mandatory', data[ 'mandatory' ] );
            this.set_meta( 'show_title', data[ 'show_title' ] || true );
            this.set_meta( 'assessable', data[ 'assessable' ] );
            this.set_meta( 'minimum_grade', data[ 'minimum_grade' ] );
            this.set_meta( 'allow_retries', data[ 'allow_retries' ] );
            this.set_meta( 'retry_attempts', data[ 'retry_attempts' ] );
            this.set( 'post_content', data[ 'content' ] || '' );
            this.set_meta( 'order', data[ 'order' ] );

            var self = this;
            $.each( data[ 'components' ], function ( index, component ) {
                $.each( component[ 'items' ], function ( idx, item ) {
                    self.item_to_meta( item );
                } );
            } );

        },
        item_to_meta: function ( item ) {
            var self = this;
            switch ( item[ 'type' ] ) {

                case 'media-browser':
                case 'text-input':
                    self.set_meta( item[ 'name' ], '' );
                    break;
                case 'checkbox-select':
                case 'select-select':
                case 'radio-select':
                    self.set_meta( item[ 'name' ], item[ 'answers' ] );
                    self.set_meta( item[ 'name' ] + '_selected', item[ 'selected' ] );
                    break;
                case 'media-caption-settings':
                    self.set_meta( item[ 'enable_name' ], false );
                    self.set_meta( item[ 'option_name' ], 'media' );
                    self.set_meta( item[ 'input_name' ], '' );
                    break;
                case 'checkbox':
                    self.set_meta( item[ 'name' ], false );
                    break;
            }

        },
        model_saved: function ( model, response, options ) {

        },
        process_changed: function () {
            CoursePress.UnitBuilder.activeModuleRef = this.cid;
            this.set( 'flag', 'dirty' );
        }


    } );

    CoursePress.Collections.UnitTabs = Backbone.Collection.extend( {
        model: CoursePress.Models.Unit
    } );

    CoursePress.Collections.UnitModules = Backbone.Collection.extend( {
        model: CoursePress.Models.Module
    } );

    // Single Tab View
    CoursePress.Views.UnitTabView = Backbone.View.extend( {

        render: function () {

            var post_status = this.model.get( 'post_status' );

            var meta = this.model.get( 'meta' );

            var variables = {
                unit_id: this.model.get( 'ID' ),
                unit_title: this.model.get( 'post_title' ),
                unit_live_class: 'publish' === post_status ? 'unit-live' : 'unit-draft',
                unit_active_class: this.first ? 'active' : '',
                unit_order: meta[ 'unit_order' ],
                unit_cid: this.model.cid
            };

            var template = _.template( $( "#unit-builder-tab-template" ).html(), variables );

            this.$el = template;

            return this;
        }

    } );

    // Tab Collection View
    CoursePress.Views.UnitTabViewCollection = Backbone.View.extend( {
        initialize: function () {
            this.model.on( 'sync', this.render, this );
        },
        events: {
            //"click li": "changeActive"
        },
        render: function () {

            self = this;

            self.$el.empty();

            var first = true;

            this.model.each( function ( unit ) {
                var unitView = new CoursePress.Views.UnitTabView( { model: unit, tagName: 'li' } );
                unitView.first = first;
                self.$el.append( unitView.render().$el );

                if ( first ) {
                    CoursePress.Helpers.changeUnit( unit, self );
                } else {
                    self.parentView.contentView.initial = true;
                    self.parentView.contentView.render();
                }

                first = false;
            } );

            this.model.trigger( 'rendered' );

            return this;
        },
        changeActive: function ( e ) {

            $( '#unit-builder .tab-tabs li' ).removeClass( 'active' );
            $( e.currentTarget ).addClass( 'active' );

            var model_id = $( e.currentTarget ).attr( 'data-tab' );
            // Get appropriate model
            var self = this;

            this.model.each( function ( unit ) {
                if ( parseInt( model_id ) === parseInt( unit.get( 'ID' ) ) ) {
                    CoursePress.Helpers.changeUnit( unit, self );
                    $( 'body,html' ).animate( {
                        scrollTop: $( '.section.unit-builder-header' ).offset().top - 20,
                        duration: 200
                    } );
                }
            } );

        }

    } );


    CoursePress.Helpers.changeUnit = function ( unit, self, page ) {
        self = CoursePress.UnitBuilder;
        if ( undefined === page ) {
            page = 1;
        }

        self.activePage = page;

        self.headerView.template_variables.unit_id = unit.get( 'ID' );
        self.headerView.template_variables.unit_cid = unit.cid;
        self.headerView.template_variables.unit_title = unit.get( 'post_title' );
        var meta = unit.get( 'meta' );
        self.headerView.template_variables.unit_availability = meta.unit_availability;

        var checked = meta.force_current_unit_completion;
        checked = 'on' === checked || true === checked || 1 === checked ? 'checked="checked"' : '';
        self.headerView.template_variables.unit_force_completion_checked = checked;

        var checked = meta.force_current_unit_successful_completion;
        checked = 'on' === checked || true === checked || 1 === checked ? 'checked="checked"' : '';
        self.headerView.template_variables.unit_force_successful_completion_checked = checked;

        self.headerView.render();

        self.contentView.initial = true;
        self.contentView.render();

        // Trigger Module collection
        self.activeUnitID = unit.get( 'ID' );
        self.activeUnitRef = unit.cid;
        self.activeUnitStatus = unit.get( 'post_status' );
        self.fetchModules( self.activeUnitID, self.activePage );

    }

    // Unit Header View
    CoursePress.Views.UnitBuilderHeader = Backbone.View.extend( {
        initialize: function () {
            this.template_variables = {
                unit_id: '',
                unit_cid: '',
                unit_title: '',
                unit_availability: '',
                unit_force_completion_checked: '',
                unit_force_successful_completion_checked: ''
            }

            this.render();
        },
        events: {
            'change .unit-detail input': 'fieldChanged',
            'keyup .unit-detail input': 'updateTabTitle',
            'change #unit-live-toggle': 'toggleUnitState',
            'change #unit-live-toggle-2': 'toggleUnitState'
        },
        render: function () {
            var template = _.template( $( "#unit-builder-header-template" ).html(), this.template_variables );

            this.$el.html( template );

            return this;
        },
        fieldChanged: function ( e ) {
            var el = $( e.currentTarget );
            var el_name = $( el ).attr( 'name' );
            var el_val = $( el ).val();
            var parent = $( el ).parents( '.unit-detail' )[ 0 ];
            var unit = this.parentView.unit_collection._byId[ $( parent ).attr( 'data-cid' ) ];

            var type = $( el ).attr( 'type' );

            if ( 'checkbox' === type ) {
                el_val = $( el ).is( ':checked' );
            }

            if ( /meta_/.test( el_name ) ) {
                unit.set_meta( el_name, el_val );
            } else {
                unit.set( el_name, el_val );
            }

        },
        updateTabTitle: function ( e ) {

            $( '[data-tab="' + this.parentView.activeUnitID + '"] span' ).html( $( e.currentTarget ).val() );
        },
        toggleUnitState: function ( e ) {
            CoursePress.Helpers.Module.toggle_unit_state( e );
        }

    } );


    CoursePress.Views.UnitBuilderFooter = Backbone.View.extend( {
        render: function () {
            var template = _.template( $( "#unit-builder-footer-template" ).html(), {} );

            this.$el.empty();

            this.$el.html( template );

            return this;
        }
    } );

    // Unit Body View
    CoursePress.Views.UnitBuilderBody = Backbone.View.extend( {
        initialize: function () {
            this.initial = true;

            this.pagerView = new CoursePress.Views.UnitBuilderPager( { className: 'section unit-builder-pager' } );
            this.pagerView.parentView = this;
            this.pagerView.template_variables = {};

            this.pagerViewInfo = new CoursePress.Views.UnitBuilderPagerInfo( { className: 'section unit-builder-pager-info' } );
            this.pagerViewInfo.parentView = this;
            this.pagerViewInfo.template_variables = {};

            this.componentsView = new CoursePress.Views.UnitBuilderComponents( { className: 'section unit-builder-components' } );
            this.componentsView.parentView = this;
            this.componentsView.template_variables = {};

            this.modulesView = new CoursePress.Views.UnitBuilderModules( { className: 'section unit-builder-modules' } );
            this.modulesView.parentView = this;
            this.modulesView.template_variables = {};

            this.footerView = new CoursePress.Views.UnitBuilderFooter( { className: 'unit-buttons' } );
            this.footerView.parentView = this;

            this.model.on( 'sync', this.render, this );

            CoursePress.Events.on( 'editor:keyup', this.editorChanged, this );
        },
        render: function () {
            if ( this.initial ) {
                var template = _.template( $( "#unit-builder-content-placeholder" ).html(), {} );
                this.$el.html( template );
                this.initial = false;
            } else {

                var template = _.template( $( "#unit-builder-content-template" ).html(), {} );
                this.$el.html( template );

                // Set variables first
                var unit = this.parentView.unit_collection._byId[ this.parentView.activeUnitRef ];

                // Always give at least 1 page
                this.pagerView.template_variables.unit_page_count = this.parentView.totalPages;

                this.$( '.unit-builder-pager' )
                    .replaceWith( this.pagerView.render( this.pagerView.template_variables ).el );

                var unit = this.parentView.unit_collection._byId[ this.parentView.activeUnitRef ];
                var show_page = unit.get_page_visibility( this.parentView.activePage );
                // Fix boolean
                if ( show_page ) {
                    show_page = ( _.isString( show_page ) && ( 'yes' === show_page.toLowerCase() || 'on' === show_page.toLowerCase() ) ) || 1 === parseInt( show_page ) || true === show_page;
                } else {
                    show_page = true;
                }

                this.pagerViewInfo.template_variables = {
                    page_label_text: unit.get_page_title( this.parentView.activePage ),
                    page_label_checked: show_page ? 'checked="checked"' : ''
                };

                this.$( '.unit-builder-pager-info' )
                    .replaceWith( this.pagerViewInfo.render( this.pagerViewInfo.template_variables ).el );

                this.componentsView.template_variables = {};

                this.$( '.unit-builder-components' )
                    .replaceWith( this.componentsView.render( this.componentsView.template_variables ).el );

                this.$( '.unit-builder-modules' )
                    .replaceWith( this.modulesView.render( this.parentView.module_collection.models ).el );

                this.$( '.unit-builder-footer' )
                    .append( this.footerView.render().el );

                CoursePress.Helpers.Module.refresh_ui();

            }

            return this;
        },
        events: {
            'click .unit-builder-components .output-element': 'add_element',
            'click .unit-builder-components .input-element': 'add_element',
            'change .button.browse-media-field': 'fieldChanged',
            'change .module-holder input': 'fieldChanged',
            'change textarea': 'fieldChanged',
            'change select': 'selectionChanged',
            'change .page-info-holder input': 'unitPageInfoChanged',
            'keyup .module-title-text': 'updateUIHeading',
            'click .unit-builder-pager ul li': 'changePage',
            'click .module-component .add-item': 'addAnswer',
            'click .module-component .remove-item': 'removeAnswer'
        },
        add_element: function ( e ) {
            var el = e.currentTarget;
            var module_type = $( el ).attr( 'class' ).match( /module-(\w|-)*/g )[ 0 ].trim().replace( 'module-', '' );

            //Count current elements
            var count = $( '.module-holder' ).length;

            var module = new CoursePress.Models.Module();
            module.from_template( module_type );
            module.set_meta( 'module_order', (count + 1) );
            module.set_meta( 'module_page', this.parentView.activePage );

            module.save();
            this.parentView.module_collection.add( module );
            $( '.section.unit-builder-modules' ).append( CoursePress.Helpers.Module.render_module( module, (count + 1) ) );

            CoursePress.Helpers.Module.refresh_ui();

        },
        fieldChanged: function ( e ) {
            var el = $( e.currentTarget );
            var el_name = $( el ).attr( 'name' );
            var el_val = $( el ).val();
            var parent = $( el ).parents( '.module-holder' )[ 0 ];
            var module = this.parentView.module_collection._byId[ $( parent ).attr( 'data-cid' ) ];

            var type = $( el ).attr( 'type' );

            if ( 'checkbox' === type ) {
                var boxes = $( '[name="' + el_name + '"]' );
                el_name = el_name.replace( /\[.*\]/, '' );

                if ( boxes.length > 1 ) {
                    el_val = [];
                    $.each( boxes, function ( i, item ) {
                        if ( $( item ).is( ':checked' ) ) {
                            el_val.push( i );
                        }
                    } );
                } else {
                    el_val = $( el ).is( ':checked' );
                }

            }
            if ( 'radio' === type ) {
                el_name = el_name.replace( /\[.*\]/, '' );
            }

            // Dynamic textboxes
            if ( 'text' === type || 'textbox' === type ) {
                if ( /\[\]/.test( el_name ) ) {


                    // Should only be for answers, else keep going as per usual
                    var component = $( el ).parents( '.module-component' );

                    if( component.length > 0 ) {

                        var boxes = $( component ).find( '[name="' + el_name + '"]' );
                        el_name = el_name.replace( /\[.*\]/, '' );

                        el_val = [];
                        $.each( boxes, function ( i, item ) {
                            el_val.push( $( item ).val() );
                        } );

                        // Change meta now
                        module.set_meta( el_name, el_val );

                        return;
                    }

                }

            }

            // Deal with Browse buttons
            if ( 'button' === type && $( el ).hasClass( 'browse-media-field' ) ) {
                var textbox = $( $( el )[ 0 ] ).siblings( '[type=text]' )[ 0 ];
                el_name = $( textbox ).attr( 'name' );
                el_val = $( textbox ).val();
            }

            if ( /meta_/.test( el_name ) ) {
                module.set_meta( el_name, el_val );
            } else {
                module.set( el_name, el_val );
            }

        },
        selectionChanged: function ( e ) {
            var el = $( e.currentTarget );
            var el_name = $( el ).attr( 'name' );
            var el_val = $( el ).val();
            var parent = $( el ).parents( '.module-holder' )[ 0 ];
            var module = this.parentView.module_collection._byId[ $( parent ).attr( 'data-cid' ) ];

            if ( /meta_/.test( el_name ) ) {
                module.set_meta( el_name, el_val );
            } else {
                module.set( el_name, el_val );
            }
        },
        editorChanged: function ( e ) {
            var el_name = e.id;
            var parent = $( '#' + el_name ).parents( '.module-holder' )[ 0 ];

            var module = this.parentView.module_collection._byId[ $( parent ).attr( 'data-cid' ) ];

            var name = /post_content_/.test( el_name ) ? el_name.replace( /(_\d+)+$/, '' ) : el_name;
            var value = CoursePress.editor.content( el_name );

            if ( /meta_/.test( name ) ) {
                module.set_meta( name, value );
            } else {
                module.set( name, value );
            }

        },
        updateUIHeading: function ( e ) {
            var el = e.currentTarget;
            var header = $( $( el ).parents( '.module-holder' )[ 0 ] ).siblings( 'h3' )[ 0 ];
            $( header ).find( '.label' ).html( $( el ).val() );
        },
        changePage: function ( e ) {
            self = this;
            var the_page = $( e.currentTarget ).attr( 'data-page' );
            var unit = self.parentView.unit_collection._byId[ self.parentView.activeUnitRef ];
            if ( the_page ) {
                self.parentView.activePage = $( e.currentTarget ).attr( 'data-page' );
                self.parentView.fetchModules( self.parentView.activeUnitID, self.parentView.activePage );
            } else {
                self.parentView.activePage = $( '.unit-builder-pager ul li' ).length;
                self.parentView.totalPages = self.parentView.activePage;

                // Update Pager
                unit.set_page_title( self.parentView.activePage, '' );
                unit.set_page_visibility( self.parentView.activePage, true );

                self.parentView.fetchModules( self.parentView.activeUnitID, self.parentView.activePage );
            }

        },
        unitPageInfoChanged: function ( e ) {
            var el = $( e.currentTarget );
            var el_name = $( el ).attr( 'name' );
            var el_val = $( el ).val();
            var unit = this.parentView.unit_collection._byId[ this.parentView.activeUnitRef ];

            var type = $( el ).attr( 'type' );

            if ( 'checkbox' === type ) {
                el_val = $( el ).is( ':checked' );
            }

            switch ( el_name ) {
                case 'page_title':
                    unit.set_page_title( this.parentView.activePage, el_val );
                    break;

                case 'show_page_title':
                    unit.set_page_visibility( this.parentView.activePage, el_val );
                    break;
            }

            unit.trigger( 'change' );

            // A bit of UI help... to be developed further
            //$('.unit-builder-tabs ul li[data-tab="' + this.parentView.parentView.activeUnitID + '"' ).removeClass('unit-live');
            //$('.unit-builder-tabs ul li[data-tab="' + this.parentView.parentView.activeUnitID + '"' ).removeClass('unit-draft');
            //$('.unit-builder-tabs ul li[data-tab="' + this.parentView.parentView.activeUnitID + '"' ).addClass('unit-changed');

        },
        addAnswer: function ( e ) {
            var self = this;
            var el = e.currentTarget;
            var group = $( el ).siblings( '.answer-group' );
            var container = $( el ).parents( '.module-holder' );
            var cid = $( container ).attr( 'data-cid' );

            var boxes = $( container ).find( '.answer' );
            var value = boxes.length;

            $( group ).append( '<div class="answer"><input type="checkbox" value="' + value + '" name="meta_answers_selected[' + cid + '][]">' +
            '<input class="component-checkbox-answer wide" type="text" name="meta_answers[]" value="">' +
            ' <span class="remove-item"><i class="fa fa-trash-o"></i></span></div>' );

        },
        removeAnswer: function ( e ) {
            var self = this;
            var el = e.currentTarget;
            var answer_group = $( el ).parents( '.answer-group' );
            var answer = $( el ).parents( '.answer' );
            var container = $( el ).parents( '.module-holder' );
            var cid = $( container ).attr( 'data-cid' );
            var module = this.parentView.module_collection._byId[ cid ];

            $( el ).parents( '.answer' ).detach();

            // Fix Checkbox Indices
            var boxes = $( answer_group ).find( '[name="meta_answers_selected[' + cid + '][]"]' );

            var el_val = [];
            $.each( boxes, function ( i, item ) {
                if ( $( item ).is( ':checked' ) ) {
                    el_val.push( i );
                }
                $( item ).val( i );
            } );

            module.set_meta( 'meta_answers_selected', el_val );


            // Fix Textboxes
            boxes = $( answer_group ).find( '[name="meta_answers[]"]' );

            el_val = [];
            $.each( boxes, function ( i, item ) {
                el_val.push( $( item ).val() );
            } );

            // Change meta now
            module.set_meta( 'meta_answers', el_val );

        }

    } );

    // Unit Body Pager View
    CoursePress.Views.UnitBuilderPager = Backbone.View.extend( {
        render: function ( options ) {
            var template = _.template( $( "#unit-builder-pager-template" ).html(), options );
            this.$el.html( template );

            return this;
        }
    } );

    // Unit Body Pager Content View
    CoursePress.Views.UnitBuilderPagerInfo = Backbone.View.extend( {
        events: {
            'change .page-info-holder input': 'fieldChanged'
        },
        render: function ( options ) {

            var template = _.template( $( "#unit-builder-pager-info-template" ).html(), options );
            this.$el.html( template );

            return this;
        }
    } );

    // Unit Body Components View
    CoursePress.Views.UnitBuilderComponents = Backbone.View.extend( {
        render: function ( options ) {
            var template = _.template( $( "#unit-builder-components-template" ).html(), options );
            this.$el.html( template );

            return this;
        }
    } );

    // Unit Body Modules
    CoursePress.Views.UnitBuilderModules = Backbone.View.extend( {
        render: function ( modules ) {

            var self = this;

            self.$el.empty();

            this.parentView.model.each( function ( module ) {
                var moduleView = new CoursePress.Views.ModuleView( {
                    model: module,
                    tagName: 'div',
                    className: 'group'
                } );
                var order = module.get_meta( 'module_order' );

                self.$el.append( moduleView.render( module, order ).$el );
            } );

            return this;
        }
    } );

    // View for each module
    CoursePress.Views.ModuleView = Backbone.View.extend( {

        render: function ( module, order ) {

            var self = this;
            self.$el.empty();

            //// Not using a template here
            self.$el.append( CoursePress.Helpers.Module.render_module( module, order ) );
            //
            CoursePress.Helpers.Module.refresh_ui();

            return this;
        }

    } );


    function init_course_builder() {

        CoursePress.UnitBuilder = new CoursePress.Views.UnitBuilder( { el: '#unit-builder' } );

    }

    $( document ).ready( function ( $ ) {

        init_course_builder();

        // Time to attach the dynamic editors
        //$.each( $( '.unit-builder-modules .editor' ), function( index, editor ) {
        //
        //    var id = $(editor).attr('id');
        //    var name = $(editor).attr('name');
        //    var content = $(editor ).val();
        //
        //    CoursePress.editor.create( editor, id, name, content, false );
        //
        //} );

    } );


})( jQuery );