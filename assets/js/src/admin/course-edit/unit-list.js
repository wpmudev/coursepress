/* global CoursePress */

(function() {
    'use strict';

    CoursePress.Define( 'UnitList', function() {
        var UnitItem, defaults, UnitModel;

        defaults = {
            ID: 0,
            post_title: 'Untitled',
            post_content: '',
            post_status: 'pending',
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
            steps: false,
            unit_permalink: ''
        };

        UnitItem = CoursePress.View.extend({
            template_id: 'coursepress-unit-item-tpl',
            className: 'unit-item',
            tagName: 'li',
            unitview: false,
            listView: false,
            unitDetails: false,
            events: {
                'click': '_setUnitDetails'
            },

            initialize: function(model, listView) {
                this.listView = listView;
                this.editCourseView = listView.editCourseView;
                this.render();
            },

            setUnitDetails: function() {
                this.editCourseView.unitsview.remove();
                this.unitDetails = this.editCourseView.unitsview = new CoursePress.UnitDetails({model: this.model}, this.listView);
                this.editCourseView.unitsview.$el.appendTo(this.editCourseView.unitsContainer);
                this.editCourseView.unitsview = this.unitDetails;

                this.$el.addClass('active');
                this.$el.siblings().removeClass('active');
            },

            _setUnitDetails: function(ev) {
                this.$el.addClass('active');
                this.$el.siblings().removeClass('active');
                this.listView.setUnitDetails(this.model.cid);
                ev.stopImmediatePropagation();
            }
        });

        UnitModel = new CoursePress.Request();

        return CoursePress.View.extend({
            template_id: 'coursepress-unit-list-tpl',
            className: 'unit-list-menu',
            units: {},
            unitModels: {},
            with_modules: true,
            events: {
                'click .new-unit': 'newUnit',
                'click .unit-item': 'setUnitDetails'
            },

            initialize: function( model, editCourseView ) {
                this.editCourseView = editCourseView;
	            this.editCourseView.setSaveMode('save');
                this.with_modules = editCourseView.model.get('meta_with_modules');
                CoursePress.Events.on( 'coursepress:change_unit_title', this.updateTitle, this );
                this.on( 'view_rendered', this.setUI, this );
                this.render();
            },

            setUI: function() {
                // Add unit list menu
                this.$el.appendTo( this.editCourseView.current );
                this.listContainer = this.$('.units-list');
            },

            addUnit: function( unitModel ) {
                var unit, id, count;

                if ( unitModel.get('deleted') ) {
                    return false;
                }

                count = this.with_modules ? unitModel.get( 'modules' ) : unitModel.get('steps');
                count = _.keys(count);
                id = this.getNewUnitId(unitModel);
                unitModel.set( 'count', count.length);
                unitModel.cid = id;
                unitModel.set( 'cid', id );
                unit = new UnitItem({model: unitModel}, this);
                unit.$el.appendTo(this.listContainer);
                this.units[id] = unit;
                this.unitModels[id] = unitModel;
            },

            getNewUnitId: function (unitModel) {
                var result = _.find(this.unitModels, function (unit) {
                    var currentItemId = unit.get === undefined ? unit.ID : unit.get('ID');
                    var needleId = unitModel.get === undefined ? unitModel.ID : unitModel.get('ID');

                    return currentItemId === needleId;
                });

                if (result === undefined) {
                    return unitModel.cid;
                }

                return result.cid;
            },

            updateTitle: function( title, cid ) {
                var unit;

                if ( this.units[cid] ) {
                    unit = this.units[cid];
                    unit.$('.unit-title').html(title);
                }
            },

            newUnit: function(ev) {
                var model, newModel, cid;

                model = new Backbone.Model(defaults);
                newModel =  this.editCourseView.unitCollection.add(model.toJSON());
                cid = newModel.cid;
                this.units[cid].$el.trigger('click');

                ev.stopImmediatePropagation();
            },

            getUnitModel: function(cid) {
                return this.unitModels[cid];
            },

            updateUnits: function() {
                var units;

                units = {};

                _.each( this.unitModels, function(unitModel, cid) {
                    if ( unitModel.get ) {
                        units[cid] = unitModel.toJSON();
                    } else {
                        units[cid] = unitModel;
                    }
                }, this );

                if ( this.editCourseView.senderButton ) {
                    this.editCourseView.senderButton.addClass('cp-progress');
                }
                UnitModel.set('action', 'update_units');
                UnitModel.set( 'course_id', this.editCourseView.model.get('ID'));
                UnitModel.set( 'units', units);

                UnitModel.off( 'coursepress:success_update_units' );
                UnitModel.on( 'coursepress:success_update_units', this.updateUnitModels, this );
                UnitModel.on( 'coursepress:error_update_units', this.updateError, this );
                UnitModel.save();

            },

            updateUnitModels: function( data ) {
                var nextStep, self;

                if ( data.units ) {
                    self = this;
                    _.each(data.units, function (unitData, cid) {
                        self.unitModels[cid] = new Backbone.Model(unitData);
                    });
                }

                this.editCourseView.after_update();

                // Go to next step.
                if ( 'continue' === this.editCourseView.getSaveMode() ) {
                    nextStep = this.editCourseView._getNextStep();
                    this.editCourseView.loadCurrentStep( nextStep );
                }
            },

            updateCollection: function() {
                this.editCourseView.unitCollection.add(this.unitModels);
            },

            updateError: function() {
                window.alert('error');
                this.editCourseView.after_update();
                // @todo: Add error message
            },

            deleteUnit: function(cid) {
                var unit, model;

                unit = this.units[cid];
                model = this.unitModels[cid];
                model.set('deleted', true);
                this.editCourseView.unitCollection.set(model);

                unit.remove();
            },

            setUnitDetails: function(cid) {
                var unitModel;

                unitModel = this.getUnitModel(cid);

                if ( this.editCourseView.unitsview ) {
                    this.editCourseView.unitsview.remove();
                }

                this.editCourseView.unitsview = new CoursePress.UnitDetails({model: unitModel}, this);
                this.editCourseView.unitsview.$el.appendTo(this.editCourseView.unitsContainer);
            }
        });
    });
})();