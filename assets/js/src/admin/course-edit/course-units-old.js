/* global CoursePress, Backbone */

(function(){
    'use strict';

    CoursePress.Define('CourseUnits', function($, doc, win){
        var Unit, UnitView, UnitSteps, UnitCollection, UnitList;

        Unit = CoursePress.Request.extend();
        UnitView = CoursePress.View.extend({
            className: 'unit-view',
            template_id: 'coursepress-unit-details',
            unitsView: false,
            events: {
                'change [name="meta_use_feature_image"]': 'toggleFeatureImage',
                'change [name="meta_use_description"]': 'toggleDescription',
                'change [name="meta_unit_availability"]': 'toggleAvailability'
            },
            initialize: function( model, unitsView ) {
                this.model = new Unit(model);
                this.unitsView = unitsView;

                this.on( 'view_rendered', this.setUI, this );
                this.render();
            },
            setUI: function() {
                var self = this;

                this.feature_image = new CoursePress.AddImage( this.$('#unit-feature-image') );
                this.$('select').select2();

                this.visualEditor({
                    id: 'post_content',
                    content: this.model.get('post_content'),
                    container: this.$('.cp-unit-description'),
                    callback: function( content ) {
                        self.model.set( 'post_content', content );
                    }
                });
            },
            toggleFeatureImage: function(ev) {
                var sender = this.$(ev.currentTarget),
                    is_checked = sender.is(':checked'),
                    feature = this.$('.cp-unit-feature-image');

                feature[ is_checked ? 'slideDown' : 'slideUp']();
            },
            toggleDescription: function( ev ) {
                var sender = this.$(ev.currentTarget),
                    is_checked = sender.is(':checked'),
                    desc = this.$('.cp-unit-description');

                desc[ is_checked ? 'slideDown' : 'slideUp']();
            },
            toggleAvailability: function( ev ) {
                var sender = this.$(ev.currentTarget),
                    value = sender.val(),
                    divs = this.$('.cp-on_date, .cp-after_delay');

                divs.slideUp();

                if ( 'instant' !== value ) {
                    this.$('.cp-' + value).slideDown();
                }
            }
        });

        UnitCollection = Backbone.Collection.extend({
            url: win._coursepress.ajaxurl + '?action=coursepress_get_course_units&_wpnonce=' + win._coursepress._wpnonce,
            initialize: function( courseId ) {
                this.url += '&course_id=' + courseId;
                this.on( 'error', this.serverError, this );
                this.fetch();
            },
            parse: function( response ) {
                return response.data;
            },
            serverError: function() {
                // @todo: show server error
            }
        });

        UnitList = CoursePress.View.extend({
            template_id: 'coursepress-unit-list-tpl',
            className: 'unit-list-menu',
            events: {
                'click li': 'setActiveUnit'
            },
            setActiveUnit: function(ev) {
                var sender, units, unit, model, unit_id, controller;

                sender = this.$(ev.currentTarget);
                unit_id = sender.data('unit');
                units = this.model.get('units');
                model = units[unit_id].model;
                controller = units[unit_id].controller;
                unit = new UnitView(model);
                controller.$el.html( unit.$el );

                //var unit = new UnitView(this.model);
                //unit.$el.appendTo( this.controller.$el );
            }
        });

        UnitSteps = CoursePress.View.extend({
            template_id: 'coursepress-unit-steps-tpl',
            steps: [],
            events: {
                'click .unit-step': 'addNewStep'
            },
            initialize: function() {
                this.on( 'view_rendered', this.getContainers, this );
                this.render();
            },
            getContainers: function() {
                this.stepContainer = this.$('.unit-steps');
            },
            addNewStep: function( ev ) {
                var sender, type, step, menu_order, data;
                menu_order = this.steps.length + 1;
                sender = this.$(ev.currentTarget);
                type = sender.data( 'step' );
                data = {type: type, menu_order: menu_order};
                step = new CoursePress.Step(data);
                step.$el.appendTo( this.stepContainer );
                this.steps.push(step);
            }
        });


       return CoursePress.View.extend({
           //template_id: 'coursepress-course-units-tpl',
           el: $('#course-units'),
           courseModel: false,
           editCourse: false,
           courseId: 0,
           withModules: true,
           active: 'unit-details',
           units: {},
           initialize: function( courseModel, EditCourse ) {
               this.withModules = courseModel.get('with_modules');
               this.courseId = courseModel.get('ID');
               this.courseModel = courseModel;
               this.editCourse = EditCourse;
               this.unitCollection = new UnitCollection(this.courseId);
               this.unitCollection.on( 'update', this.setUnitsView, this );

               //this.on( 'view_rendered', this.getViews, this );
               this.render();
           },

           setUnitsView: function( collection ) {
               var unitsData = {};

               _.each( collection.models, function( model ) {
                   var id, count;

                   id = model.get('ID');
                   count = this.withModules ? model.get('modules') : model.get('steps');
                   count = _.keys(count);
                   unitsData[id] = {
                       title: model.get( 'post_title' ),
                       count: count.length,
                       model: model.toJSON(),
                       controller: this
                   };

               }, this );

               this.unitList = new UnitList({units: unitsData});
               this.unitList.$el.appendTo( this.editCourse.current );
           },

           getViews: function() {
               this.unitDetails = new UnitView();
               this.unitDetails.$el.appendTo( this.$('#unit-details-container' ) );

               this.unitSteps = new UnitSteps();
               this.unitSteps.$el.appendTo( this.$('#unit-steps-container') );
           }
       });
    });
})();