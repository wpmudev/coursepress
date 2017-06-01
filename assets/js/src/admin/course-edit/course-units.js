/* global CoursePress, Backbone */

(function(){
    'use strict';

    CoursePress.Define('CourseUnits', function($, doc, win){
        var Unit, UnitView, UnitCollection, UnitList;

        Unit = CoursePress.Request.extend();
        UnitView = CoursePress.View.extend({
            className: 'unit-view',
            template_id: 'coursepress-unit-tpl',
            unitsView: false,
            initialize: function( model, unitsView ) {
                this.model = new Unit(model);
                this.unitsView = unitsView;

                this.render();
            },
            render: function() {
                CoursePress.View.prototype.render.apply( this );
                this.$el.appendTo( '#units-container' );
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
                //win.console.log(response.data);
                return response.data;
            },
            serverError: function() {
                // @todo: show server error
            }
        });

        UnitList = CoursePress.View.extend({
            template_id: 'coursepress-unit-list-tpl'
        });


       return CoursePress.View.extend({
           el: $('#course-units'),
           courseModel: false,
           editCourse: false,
           courseId: 0,
           withModules: true,
           units: {},
           initialize: function( courseModel, EditCourse ) {
               this.withModules = courseModel.get('with_modules');
               this.courseId = courseModel.get('ID');
               this.courseModel = courseModel;
               this.editCourse = EditCourse;
               this.unitCollection = new UnitCollection(this.courseId);
               this.unitCollection.on( 'update', this.setUnitsView, this );

               if ( this.withModules ) {
                   this.template_id = 'coursepress-course-units-with-modules-tpl';
               } else {
                   this.template_id = 'coursepress-course-units-tpl';
               }

               this.render();
           },

           setUnitsView: function( collection ) {
               var unitsData = {};

               _.each( collection.models, function( model ) {
                   var id, count;

                   id = model.get('ID');
                   count = this.withModules ? model.get('modules') : model.get('steps');
                   count = _.keys(count);
                   new UnitView( model.toJSON(), this );
                   unitsData[id] = {
                       title: model.get( 'post_title' ),
                       count: count.length
                   };

               }, this );

               this.unitList = new UnitList({units: unitsData});
               this.unitList.$el.appendTo( this.editCourse.current );
           }
       });
    });
})();