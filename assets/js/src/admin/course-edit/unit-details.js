/* global CoursePress */

(function() {
    'use strict';

    CoursePress.Define( 'UnitDetails', function($, doc, win) {
        return CoursePress.View.extend({
            template_id: 'coursepress-unit-details',
            controller: false,
            events: {
                'change [name="meta_use_feature_image"]': 'toggleFeatureImage',
                'change [name="meta_use_description"]': 'toggleDescription',
                'change [name="meta_unit_availability"]': 'toggleAvailability',
                'keyup [name="post_title"]': 'updateUnitTitle',
                'change [name]': 'updateModel'
            },

            initialize: function( model, controller ) {
                this.model = model;
                this.model.set( 'with_modules', controller.editCourse.model.get('with_modules') );
                this.controller = controller;
                this.on( 'view_rendered', this.setUpUI, this );
                this.render();
            },

            setUpUI: function() {
                var self, with_modules;

                self = this;
                with_modules = win.Course.model.get('with_modules');
                this.feature_image = new CoursePress.AddImage( this.$('#unit-feature-image') );
                this.$('select').select2();

                this.visualEditor({
                    content: this.model.get('post_content'),
                    container: this.$('.cp-unit-description'),
                    callback: function( content ) {
                        self.model.set( 'post_content', content );
                    }
                });

                this.container = this.$('#unit-steps-container');

                if ( with_modules ) {
                    this.modules = new CoursePress.UnitModules(this.model, this);
                    this.modules.$el.appendTo(this.container);
                } else {
                    this.steps = new CoursePress.Unit_Steps( this.model, this );
                    this.steps.$el.appendTo(this.container);
                }
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
            },

            updateUnitTitle: function( ev ) {
                var sender = this.$(ev.currentTarget),
                    value = sender.val();

                CoursePress.Events.trigger( 'coursepress:change_unit_title', value, this.model.get('ID') );
            }
        });
    });
})();