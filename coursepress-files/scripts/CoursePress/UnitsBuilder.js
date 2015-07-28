var CoursePress = CoursePress || {};

(function ( $ ) {

    CoursePress.Views = CoursePress.Views || {};
    CoursePress.Models = CoursePress.Models || {};
    CoursePress.Collections = CoursePress.Collections || {};
    CoursePress.Helpers = CoursePress.Helpers || {};


    /** Expand the CoursePress Object for handling Modules **/
    CoursePress.Helpers.Module = CoursePress.Helpers.Module || {};

    // Loop through a Module collection
    CoursePress.Helpers.Module.get_modules_html = function( modules ) {

        var content = '';

        $.each( modules, function( index, module ) {

            content += CoursePress.Helpers.Module.render_module( module );

        } );

        return content;

    }

    // Get the Module Type
    CoursePress.Helpers.Module.module_type = function( module ) {
        var meta = module.get('meta')
        var mod_type = meta['module_type'] ? meta['module_type'][0] : 'legacy';
        return CoursePress.Helpers.Module.map_legacy( mod_type );
    }

    // Map legacy types
    CoursePress.Helpers.Module.map_legacy = function( mod_type ) {

        var legacy = {
            'audio_module' : 'audio',
            'chat_module'  : 'chat',
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

        if( mod_type in legacy ) {
            mod_type = legacy[ mod_type ];
        }

        return mod_type;

    };

    // Start Rendering the Module
    CoursePress.Helpers.Module.render_module = function ( module ) {

        var meta = module.get('meta')
        var module_type = CoursePress.Helpers.Module.module_type( module );

        var content = '';
        content += '*** ' + module_type + ' ***';

        if( false ) {
            // Does the component data exist in the meta?
        } else if ( _coursepress.unit_builder_templates[ module_type ] ) {
            // Or render from template if the template exists
            content += _coursepress.unit_builder_templates[ module_type ];

        } else {
            // Or render legacy object

        }

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
            this.contentView = new CoursePress.Views.UnitBuilderBody( { model: this.module_collection, className: 'unit-builder-body' } );
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
    CoursePress.Models.Module = Backbone.Model.extend( {} );

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
                    $( 'body,html' ).animate( { scrollTop: $('.section.unit-builder-header').offset().top - 20, duration: 200 } );
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
                this.$el.html(template);
                //console.log( this );

                //this.$( '.unit-builder-tabs .sticky-wrapper' )
                //    .append( this.tabViewCollection.el );
                //
                //this.$( '.unit-builder-header' )
                //    .append( this.headerView.el );

                // Set variables first
                var unit = this.parentView.unit_collection._byId[ this.parentView.activeUnitRef ];
                var page_count = unit.get('meta');
                // Always give at least 1 page
                this.pagerView.template_variables.unit_page_count = page_count['unit_page_count'] ? page_count['unit_page_count'][0] : 1;

                this.$( '.unit-builder-pager' )
                    .replaceWith( this.pagerView.render( this.pagerView.template_variables ).el );

                this.pagerViewInfo.template_variables = {
                    page_label_text: 'text',
                    page_label_checked: 'checked="checked"'
                };

                this.$( '.unit-builder-pager-info' )
                    .replaceWith( this.pagerViewInfo.render( this.pagerViewInfo.template_variables ).el );

                this.componentsView.template_variables = {

                };

                this.$( '.unit-builder-components' )
                    .replaceWith( this.componentsView.render( this.componentsView.template_variables ).el );

                this.$( '.unit-builder-modules' )
                    .replaceWith( this.modulesView.render( this.parentView.module_collection.models ).el );

            }

            return this;
        }

    } );

    // Unit Body Pager View
    CoursePress.Views.UnitBuilderPager = Backbone.View.extend( {
        render: function( options ) {
            var template = _.template( $( "#unit-builder-pager-template" ).html(), options );
            this.$el.html( template );

            return this;
        }
    } );

    // Unit Body Pager Content View
    CoursePress.Views.UnitBuilderPagerInfo = Backbone.View.extend( {
        render: function( options ) {

            var template = _.template( $( "#unit-builder-pager-info-template" ).html(), options );
            this.$el.html( template );
            //this.$el.html('info');

            return this;
        }
    } );

    // Unit Body Components View
    CoursePress.Views.UnitBuilderComponents = Backbone.View.extend( {
        render: function( options ) {
            var template = _.template( $( "#unit-builder-components-template" ).html(), options );
            this.$el.html( template );

            return this;
        }
    } );

    // Unit Body Modules... most complex view.
    CoursePress.Views.UnitBuilderModules = Backbone.View.extend( {
        render: function( modules ) {
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

    } );


})( jQuery );