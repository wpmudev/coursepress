/* global CoursePress */

(function() {
    'use strict';

    CoursePress.Define( 'UnitsWithModuleList', function() {
        var UnitView;

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
                with_modules = unitsView.editCourse.model.get('meta_with_modules');

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
            className: 'unit-view',
            editCourse: false,
            unitItems: {},

            initialize: function(model, editCourseView) {
                this.with_modules = editCourseView.model.get('meta_with_modules');
                this.model = model;
                this.editCourse = editCourseView;
                this.editCourse.unitCollection.on( 'add', this.setUnitItem, this );
                this.on( 'view_rendered', this.setUI, this );
                this.render();
            },

            setUI: function() {
                this.unitsContainer = this.$('#units-container');

                if ( this.editCourse.unitCollection &&
                    this.editCourse.unitCollection.unitsLoaded ) {

                    // Set unit models
                    _.each( this.editCourse.unitCollection.models, function( model ) {
                        this.editCourse.unitList.addUnit(model);
                        this.setUnitItem(model);
                    }, this );
                }
            },

            setUnitItem: function(unitModel){
                var id, unitItem;

                id = unitModel.cid;
                unitItem = new UnitView(unitModel, this);
                unitItem.$el.appendTo(this.unitsContainer);
                this.unitItems[id] = unitItem;
            }
        });
    });
})();