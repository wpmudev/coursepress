/* global CoursePress */

(function() {
    'use strict';

    CoursePress.Define( 'UnitList', function() {
        var UnitItem, defaults, UnitModel;

        defaults = {
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
        };

        UnitItem = CoursePress.View.extend({
            template_id: 'coursepress-unit-item-tpl',
            className: 'unit-item',
            tagName: 'li',
            unitview: false,
            listView: false,
            unitDetails: false,
            events: {
                'click': 'setUnitDetails'
            },

            initialize: function(model, listView) {
                this.listView = listView;
                this.editCourseView = listView.editCourseView;
                this.render();
            },

            render: function() {
                this.attributes = {};
                CoursePress.View.prototype.render.apply(this);
            },

            setUnitDetails: function() {
                this.editCourseView.unitsview.remove();
                this.unitDetails = this.editCourseView.unitsview = new CoursePress.UnitDetails({model: this.model}, this.listView);
                this.editCourseView.unitsview.$el.appendTo(this.editCourseView.unitsContainer);

                this.$el.addClass('active');
                this.$el.siblings().removeClass('active');
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
                'click .new-unit': 'newUnit'
            },
            initialize: function( model, editCourseView ) {
                this.editCourseView = editCourseView;
                this.with_modules = editCourseView.model.get('meta_with_modules');
                CoursePress.Events.on( 'coursepress:change_unit_title', this.updateTitle, this );
                this.on( 'view_rendered', this.setUI, this );
                this.render();
            },
            initialize333: function( model, unitView ) {
                this.unitView = unitView;
                this.courseModel = unitView.editCourse;
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

                count = this.with_modules ? unitModel.get( 'modules' ) : unitModel.get('steps');
                count = _.keys(count);
                id = unitModel.cid;
                unitModel.set( 'count', count.length);
                unitModel.set( 'cid', id );
                unit = new UnitItem({model: unitModel}, this);
                unit.$el.appendTo(this.listContainer);
                this.units[id] = unit;
                this.unitModels[id] = unitModel;
            },
            updateTitle: function( title, cid ) {
                var unit;

                if ( this.units[cid] ) {
                    unit = this.units[cid];
                    unit.$('.unit-title').html(title);
                }
            },
            newUnit: function() {
                var model, newModel, cid;

                model = new Backbone.Model(defaults);
                newModel =  this.editCourseView.unitCollection.add(model.toJSON());
                cid = newModel.cid;
                this.units[cid].$el.trigger('click');
            },
            getUnitModel: function(cid) {
                return this.unitModels[cid];
            },
            updateUnits: function() {
                this.editCourseView.senderButton.addClass('cp-progress');
                UnitModel.set('action', 'update_units');
                UnitModel.set( 'course_id', this.editCourseView.model.get('ID'));
                UnitModel.set( 'units', this.unitModels);
                UnitModel.off( 'coursepress:success_update_units' );
                UnitModel.on( 'coursepress:success_update_units', this.updateUnitModels, this );
                UnitModel.on( 'coursepress:error_update_units', this.updateError, this );
                UnitModel.save();
            },
            updateUnitModels: function( data ) {
                if ( data.units ) {
                    //this.unitModels = _.extend( this.unitModels, data.units );
                }
                this.editCourseView.after_update();
            },
            updateError: function() {

            }
        });
    });
})();