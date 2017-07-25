/* global CoursePress */

(function() {
    'use strict';

    CoursePress.Define( 'CourseUnits', function( $, doc, win ) {
        var UnitCollection, UnitModel, Units, UnitList, UnitItem, UnitView;

        UnitCollection = Backbone.Collection.extend({
            url: win._coursepress.ajaxurl + '?action=coursepress_get_course_units&_wpnonce=' + win._coursepress._wpnonce,
            initialize: function( courseId ) {
                this.url += '&course_id=' + courseId;
                this.on( 'error', this.serverError, this );
                this.fetch();
            },
            parse: function( response ) {
                this.trigger( 'coursepress:unit_collection_loaded', response.data );
                return response.data;
            },
            serverError: function() {
                // @todo: show server error
            }
        });

        UnitModel = CoursePress.Request.extend({
            defaults: {
                ID: 0,
                post_title: 'Untitled',
                post_content: '',
                modules: {
                    1: {
                        id: 1,
                        title: 'Untitled',
                        steps: {},
                        slug: ''
                    }
                },
                meta_use_feature_image: false,
                meta_unit_feature_image: '',
                meta_use_description: false,
                meta_unit_availability: 'instant',
                meta_unit_availability_date: '',
                meta_force_current_unit_completion: false,
                meta_force_current_unit_successful_completion: false,
                count: 0,
                steps: false
            }
        });

        UnitItem = CoursePress.View.extend({
            template_id: 'coursepress-unit-item-tpl',
            className: 'unit-item',
            tagName: 'li',
            unitview: false,
            events: {
                'click': 'setUnitDetails'
            },

            initialize: function( model, listView ) {
                this.model = new UnitModel(model);
                this.listView = listView;

                CoursePress.Events.on( 'coursepress:change_unit_title', this.updateTitle, this );
                this.render();
            },

            setUnitDetails: function() {
                this.listView.unitView.$el.html('');
                this.unitview = new CoursePress.UnitDetails(this.model, this.listView.unitView);
                this.unitview.$el.appendTo(this.listView.unitView.$el);

                this.$el.addClass('active');
                this.$el.siblings().removeClass('active');
            },

            updateTitle: function( title ) {
                this.$el.find('.unit-title').html(title);
            }
        });

        UnitList = CoursePress.View.extend({
            template_id: 'coursepress-unit-list-tpl',
            className: 'unit-list-menu',
            units: {},
            events: {
                'click .new-unit': 'newUnit'
            },
            initialize: function( model, unitView ) {
                this.unitView = unitView;
                this.courseModel = unitView.editCourse;
                this.on( 'view_rendered', this.setUI, this );
                CoursePress.Events.on( 'coursepress:change_unit_title', this.updateTitle, this );
                this.render();
            },
            setUI: function() {
                this.listContainer = this.$('.units-list');
            },
            addList: function( unitModel ) {
                var with_modules, unit, id, count;

                with_modules = this.courseModel.model.get('meta_with_modules');
                count = with_modules ? unitModel.get( 'modules' ) : unitModel.get('steps');
                count = _.keys(count);
                id = unitModel.cid;
                unitModel.set( 'count', count.length);
                unitModel.set( 'cid', id );
                unit = new UnitItem(unitModel.toJSON(), this);
                unit.$el.appendTo(this.listContainer);
                this.units[id] = unit;
            },
            updateTitle: function( title, unit_id ) {
                var item = this.$('[data-unit="' + unit_id + '"] .unit-title');
                item.html(title);
            },
            newUnit: function() {
                var unit = new UnitItem({}, this);
                unit.$el.appendTo(this.listContainer);
                unit.$el.trigger('click');
            }
        });

        UnitView = CoursePress.View.extend({
            template_id: 'coursepress-unit-tpl',
            className: 'unit-view',
            unitsView: false,
            steps: {},
            with_modules: false,
            events: {
                'click .cp-unit-heading label': 'toggleListing',
                'click [data-unit]': 'editUnit',
                'click [data-module]': 'editModule'
            },

            initialize: function( model, unitsView ) {
                var with_modules;
                this.model = model;
                with_modules = win.Course.model.get('with_modules');

                if ( ! with_modules || ! model.get('modules') ) {
                    model.set('modules', false);
                }
                if ( with_modules || ! model.get('steps' ) ) {
                    model.set( 'steps', false );
                }
                this.unitsView = unitsView;
                this.render();
            },

            toggleListing: function( ev ) {
                var sender = this.$(ev.currentTarget),
                    list = sender.parent().next('.cp-unit-content'),
                    is_open = list.is(':visible');

                if ( is_open ) {
                    list.slideUp();
                    sender.addClass('close');
                } else {
                    list.slideDown();
                    sender.removeClass('close');
                }
            },

            getUnit: function( cid ) {
                return this.unitsView.unitList.units[cid];
            },

            editUnit: function(ev) {
                var sender, cid;

                sender = this.$(ev.currentTarget);
                cid = sender.data('unit');
                this._editUnit(cid);
            },

            _editUnit: function( cid ) {
                var unit;

                unit = this.getUnit(cid);

                if ( unit ) {
                    unit.setUnitDetails();
                }

                return unit;
            },

            editModule: function(ev) {
                var sender, module_id, unit_id, unit;

                sender = this.$(ev.currentTarget);
                module_id = sender.data('module');
                unit_id = sender.data('unit');
                unit = this._editUnit(unit_id);

                if ( unit.unitview && unit.unitview.modules ) {
                    unit.unitview.modules.$('.module-item[data-order="' + module_id + '"]').trigger('click');
                }
            }
        });

        return CoursePress.View.extend({
            template_id: 'coursepress-units-tpl',
            el: $('#course-units'),
            screen: 'unit-list',
            initialize: function( courseModel, EditCourse ) {
                this.with_modules = EditCourse.model.get('meta_with_modules');
                this.courseId = courseModel.get('ID');
                this.model = courseModel;
                this.editCourse = EditCourse;

                if ( ! Units ) {
                    Units = new UnitCollection(this.courseId);
                    Units.on( 'add', this.setList, this );
                    Units.on( 'coursepress:unit_collection_loaded', this.maybeSetUnit, this );
                    this.setUI();
                } else {
                    Units.on( 'add', this.setList, this );
                }
                this.render();
            },

            setUI: function() {
                var helptab_seen, self;

                this.unitList = new UnitList({}, this);
                this.unitList.$el.appendTo( this.editCourse.current );

                self = this;
                this.editCourse.current.find('.menu-label').on( 'click', function() {
                    self.setUnitViewList();
                });

                helptab_seen = CoursePress.Cookie('course_unit_helptab').get();

                if ( ! helptab_seen ) {
                    this.help = new CoursePress.UnitHelp();
                }
            },

            setUnitViewList: function() {
                var models;

                models = Units.models;

                this.$el.html('');
                this.render();

                if ( models ) {
                    _.each( models, function( model ) {
                        this.setUnitView(model);
                    }, this );
                }
                this.unitList.$('.unit-item').removeClass('active');
            },

            setList: function(model) {
                this.unitList.addList(model);

                if ( 'unit-list' === this.screen ) {
                    this.setUnitView(model);
                }
            },

            setUnitView: function(model) {
                var view;

                view = new UnitView(model, this);
                view.$el.appendTo(this.$('#units-container'));
            },

            maybeSetUnit: function( data ) {
                if ( ! data || ! data.length ) {
                    this.unitList.newUnit();
                }
            }
        });
    });
})();