var CoursePress = CoursePress || {};

(function ( $ ) {

    CoursePress.Views = CoursePress.Views || {};
    CoursePress.Models = CoursePress.Models || {};
    CoursePress.Collections = CoursePress.Collections || {};
    CoursePress.Helpers = CoursePress.Helpers || {};


    /** Expand the CoursePress Object for handling Modules **/
    CoursePress.Helpers.Module = CoursePress.Helpers.Module || {};

    // Loop through a Module collection
    CoursePress.Helpers.Module.get_modules_html = function ( modules ) {

        var content = '';

        $.each( modules, function ( index, module ) {

            content += CoursePress.Helpers.Module.render_module( module );

        } );

        return content;

    }

    // Get the Module Type
    CoursePress.Helpers.Module.module_type = function ( module ) {
        var meta = module.get( 'meta' )
        var mod_type = meta[ 'module_type' ] ? meta[ 'module_type' ][ 0 ] : 'legacy';
        return module.map_legacy_type( mod_type );
    }

    //// Map legacy types
    //CoursePress.Helpers.Module.map_legacy = function ( mod_type ) {
    //
    //    var legacy = {
    //        'audio_module': 'audio',
    //        'chat_module': 'chat',
    //        'checkbox_input_module': 'input-checkbox',
    //        'file_module': 'download',
    //        'file_input_module': 'input-upload',
    //        'image_module': 'image',
    //        'page_break_module': 'legacy',
    //        'radio_input_module': 'input-radio',
    //        'section_break_module': 'section',
    //        'text_module': 'input-text',
    //        'text_input_module': 'input-text',
    //        'video_module': 'video'
    //    }
    //
    //    if ( mod_type in legacy ) {
    //        mod_type = legacy[ mod_type ];
    //    }
    //
    //    return mod_type;
    //
    //};


    // Start Rendering the Module
    CoursePress.Helpers.Module.render_module = function ( module ) {

        var types = _coursepress.unit_builder_module_types;
        var labels = _coursepress.unit_builder_module_labels;
        var content;
        var data;

        if ( false ) {
            // Does the component data exist in the meta?
        } else if ( _coursepress.unit_builder_templates[ module.module_type() ] ) {
            // Or render from template if the template exists
            if ( _coursepress.unit_builder_templates[ module.module_type() ].trim().length > 0 ) {
                data = JSON.parse( _coursepress.unit_builder_templates[ module.module_type() ] );
            }

        } else {
            // Or render legacy object

        }

        if ( undefined === data || undefined === _coursepress.unit_builder_module_types[ data[ 'type' ] ] ) {
            return '';
        }

        // Replace template data
        data[ 'id' ] = module.get( 'ID' );
        data[ 'title' ] = module.get( 'post_title' );
        data[ 'duration' ] = module.get_meta( 'duration', '1:00' );
        data[ 'type' ] = module.module_type();
        data[ 'mode' ] = types[ data[ 'type' ] ][ 'mode' ];
        data[ 'show_title' ] = module.fix_boolean( module.get_meta( 'mandatory', true ) );
        data[ 'mandatory' ] = module.fix_boolean( module.get_meta( 'mandatory', true ) );
        data[ 'assessable' ] = module.fix_boolean( module.get_meta( 'assessable', false ) );
        data[ 'minimum_grade' ] = module.get_meta( 'minimum_grade', 100 );
        data[ 'allow_retries' ] = module.fix_boolean( module.get_meta( 'allow_retries', true ) );
        data[ 'retry_attempts' ] = module.get_meta( 'retry_attempts', 0 );
        var post_content = module.get( 'post_excerpt' );
        post_content = post_content.length > 0 ? post_content : module.get( 'post_content' );
        data[ 'content' ] = post_content;
        data[ 'order' ] = module.get_meta( 'order', 0 );
        data[ 'page' ] = module.get_meta( 'page', 1 );
        console.log(data);

        content = '<h3 class="module-holder-title"><span class="label">' + data[ 'title' ] + '</span><span class="module-type">' + types[ data[ 'type' ] ][ 'title' ] + '</span></h3>' +
        '<div class="module-holder ' + data[ 'type' ] + ' mode-' + data[ 'mode' ] + '" data-id="' + data[ 'id' ] + '" data-type="' + data[ 'type' ] + '">';

        // Display the body of the module?
        if ( ( types[ data[ 'type' ] ][ 'body' ] && 'hidden' !== types[ data[ 'type' ] ][ 'body' ] ) || !types[ data[ 'type' ] ][ 'body' ] ) {

            content += '<div class="module-header">' +
            '<label class="module-title"><span class="label">' + labels[ 'module_title' ] + '</span>' +
            '<span class="description">' + labels[ 'module_title_desc' ] + '</span>' +
            '<input type="text" name="title" value="' + data[ 'title' ] + '" /></label>';

            content += '<input type="hidden" name="meta_module_type" value="' + data[ 'type' ] + '" />';

            content +=
                '<label class="module-duration"><span class="label">' + labels[ 'module_duration' ] + '</span>' +
                '<input type="text" name="meta_duration" value="' + data[ 'duration' ] + '" /></label>';

            // Show Title
            content += '<label class="module-show-title">' +
            '<input type="checkbox" name="meta_show_title" value="1" ' + CoursePress.utility.checked( data[ 'show_title' ], 1 ) + ' />' +
            '<span class="label">' + labels[ 'module_show_title' ] + '</span>' +
            '<span class="description">' + labels[ 'module_show_title_desc' ] + '</span>' +
            '</label>';


            // Only for user inputs
            if ( 'input' === data[ 'mode' ] ) {

                // Mandatory
                content += '<label class="module-mandatory">' +
                '<input type="checkbox" name="meta_mandatory" value="1" ' + CoursePress.utility.checked( data[ 'mandatory' ], 1 ) + ' />' +
                '<span class="label">' + labels[ 'module_mandatory' ] + '</span>' +
                '<span class="description">' + labels[ 'module_mandatory_desc' ] + '</span>' +
                '</label>';

                // Assessable
                content += '<label class="module-assessable">' +
                '<input type="checkbox" name="meta_assessable" value="1" ' + CoursePress.utility.checked( data[ 'assessable' ], 1 ) + ' />' +
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
                '<input type="checkbox" name="meta_allow_retries" value="1" ' + CoursePress.utility.checked( data[ 'allow_retries' ], 1 ) + ' />' +
                '<span class="label">' + labels[ 'module_allow_retries' ] + '</span>' +
                '<input type="text" name="meta_retry_attempts" value="' + data[ 'retry_attempts' ] + '" />' +
                '<span class="description">' + labels[ 'module_allow_retries_desc' ] + '</span>' +
                '</label>';
            }
            //
            //    // Excerpt
            if ( ( types[ data[ 'type' ] ][ 'excerpt' ] && 'hidden' !== types[ data[ 'type' ] ][ 'excerpt' ] ) || !types[ data[ 'type' ] ][ 'excerpt' ] ) {

                var textarea_name = 'module_excerpt_' + data[ 'id' ];
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
        content += '</div>';


        return content;

    }

    CoursePress.Helpers.Module.render_components = function ( module, data ) {
        var types = _coursepress.unit_builder_module_types;
        var labels = _coursepress.unit_builder_module_labels;

        var content = '';

        var components = _.isArray( data[ 'components' ] ) ? data[ 'components' ] : [];

        //console.log( components );

        // Deal with each components...
        $.each( components, function ( key, component ) {

            var label = component[ 'label' ] ? component[ 'label' ] : '';
            var description = component[ 'description' ] ? component[ 'description' ] : '';
            var label_class = component[ 'class' ] ? 'class="' + component[ 'class' ] + '"' : '';

            content += '<div class="module-component module-component-' + key + '">' +
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
                        content += '<input type="text"' + attr + ' value="' + meta_value + '" />';
                        break;

                    case 'text':
                        var attr = item[ 'name' ] ? ' name="' + item[ 'name' ] + '"' : '';
                        attr += item[ 'class' ] ? ' class="' + item[ 'class' ] + '"' : '';
                        var text = item[ 'text' ] ? item[ 'text' ] : '';
                        content += '<span' + attr + '>' + text + '</span>';
                        break;

                    case 'radio-select':
                        //var attr = item[ 'name' ] ? ' name="' + item[ 'name' ] + '[]"' : '';
                        //attr += item[ 'class' ] ? ' class="' + item[ 'class' ] + '"' : '';
                        var name = item[ 'name' ] ? item[ 'name' ] : '';
                        var attr = item[ 'class' ] ? ' class="' + item[ 'class' ] + '"' : '';

                        var answers = module.get_meta('answers');
                        answers = answers.length > 0 ? CoursePress.utility.unserialize( answers ) : item['answers'];

                        var selected = module.get_meta('answers_selected', parseInt( item['selected'] ) );

                        $.each( answers, function( index, answer ) {

                            // Legacy answers
                            if( _.isNaN( parseInt( selected ) ) ) {
                                selected = selected == answer ? index : -1;
                            }

                            content += '<input type="radio" name="' + name + '_selected" value="' + index + '" ' + CoursePress.utility.checked( parseInt( selected ), index ) + ' />';
                            content += '<input type="text" ' + attr + ' value="' + answer + '" name="' + name + '[]" /><br />'
                        } );
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

            // Render the container
            this.render();
        },
        render: function () {

            // Get the parent layout rendered first
            var template = _.template( $( '#unit-builder-template' ).html(), {} );
            this.$el.html( template );

            this.$( '.unit-builder-tabs .sticky-wrapper' )
                .append( this.tabViewCollection.el );

            this.$( '.unit-builder-header' )
                .append( this.headerView.el );

            this.$( '.unit-builder-body' )
                .replaceWith( this.contentView.el );

            // UI
            $( ".sticky-tabs" ).sticky( { topSpacing: 45 } );


            return this;
        },
        fetchModules: function ( unit_id ) {
            //console.log( unit_id );
            this.module_collection.url = _coursepress._ajax_url + '?action=unit_builder&task=modules&course_id=' + _coursepress.course_id + '&unit_id=' + unit_id;
            this.module_collection.fetch();
        }

    } );

    // Unit Tab View / Models / Collections

    CoursePress.Models.Unit = Backbone.Model.extend( {} );
    CoursePress.Models.Module = Backbone.Model.extend( {

        get_meta: function ( key, default_value ) {

            if ( undefined === default_value ) {
                default_value = '';
            }

            var meta = this.get( 'meta' );
            var value = meta[ key ] ? meta[ key ][ 0 ] : default_value;

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
                    var value = meta[ 'limit_attempts' ] ? meta[ 'limit_attempts' ][ 0 ] : 0;
                    // Invert answer
                    return !this.fix_boolean( value );
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
                    key = 'checked_answer';
                    break;
            }

            var meta = this.get( 'meta' );
            var value = meta[ key ] ? meta[ key ][ 0 ] : default_value;

            return value;
        },

        map_legacy_type: function ( mod_type ) {
            var legacy = {
                'audio_module': 'audio',
                'chat_module': 'chat',
                'checkbox_input_module': 'input-checkbox',
                'file_module': 'download',
                'file_input_module': 'input-upload',
                'image_module': 'image',
                'page_break_module': 'legacy',
                'radio_input_module': 'input-radio',
                'section_break_module': 'section',
                'text_module': 'text',
                'text_input_module': 'input-text',
                'video_module': 'video'
            }

            if ( mod_type in legacy ) {
                // Fix the text input
                if ( 'text_module' && 'single' !== this.get_meta( 'checked_length', 'single' ) ) {
                    mod_type = 'input-textarea';
                } else {
                    mod_type = legacy[ mod_type ];
                }
            }

            return mod_type;
        },
        module_type: function () {
            return this.map_legacy_type( this.get_meta( 'module_type' ) );
        },
        fix_boolean: function ( value ) {
            return 1 === value || 'on' === value || 'yes' === value || true === value;
        }


    } );

    CoursePress.Collections.UnitTabs = Backbone.Collection.extend( {
        model: CoursePress.Models.Unit,
        url: _coursepress._ajax_url + '?action=unit_builder&task=units&course_id=' + _coursepress.course_id
    } );

    CoursePress.Collections.UnitModules = Backbone.Collection.extend( {
        model: CoursePress.Models.Module
    } );

    // Single Tab View
    CoursePress.Views.UnitTabView = Backbone.View.extend( {

        render: function () {

            var post_status = this.model.get( 'post_status' );
            var variables = {
                unit_id: this.model.get( 'ID' ),
                unit_title: this.model.get( 'post_title' ),
                unit_live_class: 'publish' === post_status ? 'unit-live' : 'unit-draft',
                unit_active_class: this.first ? 'active' : ''
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
            "click li": "changeActive"
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


    CoursePress.Helpers.changeUnit = function ( unit, self ) {

        self.parentView.headerView.template_variables.unit_title = unit.get( 'post_title' );
        var meta = unit.get( 'meta' );
        self.parentView.headerView.template_variables.unit_availability = meta.unit_availability;

        var checked = meta.force_current_unit_completion;
        checked = 'on' === checked || true === checked || 1 === checked ? 'checked="checked"' : '';
        self.parentView.headerView.template_variables.unit_force_completion_checked = checked;

        var checked = meta.force_current_unit_successful_completion;
        checked = 'on' === checked || true === checked || 1 === checked ? 'checked="checked"' : '';
        self.parentView.headerView.template_variables.unit_force_successful_completion_checked = checked;

        self.parentView.headerView.render();

        self.parentView.contentView.initial = true;
        self.parentView.contentView.render();

        // Trigger Module collection
        self.parentView.activeUnitID = unit.get( 'ID' );
        self.parentView.activeUnitRef = unit.cid;
        self.parentView.fetchModules( self.parentView.activeUnitID );

    }

    // Unit Header View
    CoursePress.Views.UnitBuilderHeader = Backbone.View.extend( {
        initialize: function () {
            this.template_variables = {
                unit_title: '',
                unit_availability: '',
                unit_force_completion_checked: '',
                unit_force_successful_completion_checked: ''
            }

            this.render();
        },
        render: function () {

            var template = _.template( $( "#unit-builder-header-template" ).html(), this.template_variables );

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

            this.model.on( 'sync', this.render, this );
            //this.render();
        },
        render: function () {
            if ( this.initial ) {
                var template = _.template( $( "#unit-builder-content-placeholder" ).html(), {} );
                this.$el.html( template );
                this.initial = false;
            } else {


                var template = _.template( $( "#unit-builder-content-template" ).html(), {} );
                this.$el.html( template );
                //console.log( this );

                //this.$( '.unit-builder-tabs .sticky-wrapper' )
                //    .append( this.tabViewCollection.el );
                //
                //this.$( '.unit-builder-header' )
                //    .append( this.headerView.el );

                // Set variables first
                var unit = this.parentView.unit_collection._byId[ this.parentView.activeUnitRef ];
                var page_count = unit.get( 'meta' );
                // Always give at least 1 page
                this.pagerView.template_variables.unit_page_count = page_count[ 'unit_page_count' ] ? page_count[ 'unit_page_count' ][ 0 ] : 1;

                this.$( '.unit-builder-pager' )
                    .replaceWith( this.pagerView.render( this.pagerView.template_variables ).el );

                this.pagerViewInfo.template_variables = {
                    page_label_text: 'text',
                    page_label_checked: 'checked="checked"'
                };

                this.$( '.unit-builder-pager-info' )
                    .replaceWith( this.pagerViewInfo.render( this.pagerViewInfo.template_variables ).el );

                this.componentsView.template_variables = {};

                this.$( '.unit-builder-components' )
                    .replaceWith( this.componentsView.render( this.componentsView.template_variables ).el );

                this.$( '.unit-builder-modules' )
                    .replaceWith( this.modulesView.render( this.parentView.module_collection.models ).el );


                // Bring on the Visual Editor
                $.each( $( '.unit-builder-modules .editor' ), function ( index, editor ) {
                    var id = $( editor ).attr( 'id' );
                    var name = $( editor ).attr( 'name' );
                    var height = $( editor ).attr( 'data-height' ) ? $( editor ).attr( 'data-height' ) : 400;

                    var content = $( editor ).val();
                    CoursePress.editor.create( editor, id, name, content, false, height );
                } );

                // Fix Accordion
                if ( $( '.unit-builder-modules' ).hasClass( 'ui-accordion' ) ) {
                    $( '.unit-builder-modules' ).accordion( 'destroy' );
                }
                // Pass in heightStyle or it chops off the bottom of modules.
                $( '.unit-builder-modules' ).accordion( { heightStyle: "content", collapsible: true } );


            }

            return this;
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
        render: function ( options ) {

            var template = _.template( $( "#unit-builder-pager-info-template" ).html(), options );
            this.$el.html( template );
            //this.$el.html('info');

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

    // Unit Body Modules... most complex view.
    CoursePress.Views.UnitBuilderModules = Backbone.View.extend( {
        render: function ( modules ) {
            //var template = _.template( $( "#unit-builder-modules-template" ).html(), options );

            var html = CoursePress.Helpers.Module.get_modules_html( modules );

            this.$el.html( html );

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