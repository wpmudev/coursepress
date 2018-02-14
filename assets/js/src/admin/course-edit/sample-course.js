/* global CoursePress */

(function() {
    'use strict';

    CoursePress.Define( 'SampleCourse', function( $, doc, win ) {
        return CoursePress.View.extend({
            template_id: 'coursepress-sample-course-tpl',
            className: 'coursepress-wrap coursepress-modal',
            events: {
                'click .cp-close': 'remove',
                'change [name]': 'updateModel',
                'click .cp-btn-active': 'getSampleCourse'
            },

            initialize: function( model, setupModel) {
                this.setupModel = setupModel;
                this.model = new CoursePress.Request();
                this.on( 'view_rendered', this.setUI, this );

                this.model.set( 'action', 'import_sample_course' );
                this.model.on( 'coursepress:success_import_sample_course', this.setSelected, this );
                this.model.on( 'coursepress:success_import_course', this.setImportedCourse, this );
                this.render();
            },

            setUI: function() {
                var first;

                // Set the first sample course as primary selection
                first = this.$('[type="radio"]').first();
                first.prop('checked', true ).trigger('change');
            },

            render: function() {
                CoursePress.View.prototype.render.apply(this);
                this.$el.appendTo('body');
            },

            getSampleCourse: function() {
                this.model.save();

                this.$el.find('.cp-btn-active').attr('disabled', true);
            },

            setSelected: function( data ) {
                if ( data.import_id ) {
                    data = _.extend({
                        replace: false,
                        with_students: false,
                        with_comments: false,
                        old_course_id: this.setupModel.model.get('ID'),
                        action: 'import_course'
                    }, data );

                    this.model.set( data );
                    this.model.save();
                }
            },

            setImportedCourse: function( data ) {
                if ( data.course ) {
                    var url = win._coursepress.pagenow + '&cid=' + data.course.ID;
                    win.location = url;
                }
            }
        });
    });
})();
