/* global CoursePress */

(function() {
    'use strict';

    CoursePress.Define( 'UnitDetails', function($, doc, win) {
        return CoursePress.View.extend({
            template_id: 'coursepress-unit-details',
            controller: false,
            with_modules: false,
            events: {
                'change [name="meta_use_feature_image"]': 'toggleFeatureImage',
                'change [name="meta_use_description"]': 'toggleDescription',
                'change [name="meta_unit_availability"]': 'toggleAvailability',
                'keyup .unit-title-input': 'updateUnitTitle',
                'change [name]': 'updateModel',
                'focus [name]': 'removeErrorMarker'
            },

            initialize: function( model, controller ) {
                this.controller = controller;
                this.editCourseView = controller.editCourseView;
                this.with_modules = this.editCourseView.model.get('meta_with_modules');

                if ( ! this.model.get('post_status') ) {
                    this.model.set('post_status', 'pending');
                }

                this.model.set('with_modules', this.with_modules);
                this.on('coursepress:model_updated', this.updateUnitCollection, this);
                this.on('view_rendered', this.setUpUI, this);
                this.render();
            },

            validateUnit: function() {
                var proceed, title, use_feature_img, modules,
                    errors = {}, error_count, popup, steps, steps_count;

                proceed = true;
                title = this.model.get('post_title');
                use_feature_img = this.model.get('meta_use_feature_image');

                if ( ! title || 'Untitled' === title ) {
                    proceed = this.setErrorMarker( this.$('[name="post_title"]'), proceed );
                }

                if ( use_feature_img && ! this.model.get('meta_unit_feature_image') ) {
                    proceed = this.setErrorMarker( this.$('[name="meta_unit_feature_image"]'), proceed );
                }

                if ( this.with_modules ) {
                    modules = this.model.get('modules');

                    if ( modules ) {
                        _.each( modules, function( module, index ) {
                            if ( module && index > 0 ) {
                                if ( ! module.title || 'Untitled' === module.title ) {
                                    errors.noname_module = win._coursepress.text.noname_module;
                                }

                                steps_count = _.keys( module.steps );

                                if ( steps_count <= 0 ) {
                                    errors.no_steps = win._coursepress.text.nosteps;
                                }
                            }
                        }, this );
                    }
                } else {
                    steps = this.model.get('steps');
                    steps_count = _.keys(steps);
                    if ( steps_count <= 0 ) {
                        errors.no_steps = win._coursepress.text.nosteps;
                    }
                }
                error_count = _.keys(errors);

                if ( proceed && error_count.length > 0 ) {
                    proceed = false;
                    errors = _.values(errors);
                    popup = new CoursePress.PopUp({
                        type: 'warning',
                        message: errors.join('')
                    });
                }

                return proceed;
            },

            setUpUI: function() {
                var self;

                self = this;
                this.feature_image = new CoursePress.AddImage( this.$('#unit-feature-image') );
                this.$('select').select2();

                this.visualEditor({
                    content: this.model.get('post_content'),
                    container: this.$('.cp-unit-description'),
                    callback: function(content) {
                        self.model.set( 'post_content', content );
                    }
                });

                this.container = this.$('#unit-steps-container');

                if ( this.with_modules ) {
                    this.modules = new CoursePress.UnitModules({model:this.model}, this);
                    this.modules.$el.appendTo(this.container);
                } else {
                    this.steps = new CoursePress.Unit_Steps({model: this.model}, this );
                    this.steps.$el.appendTo(this.container);
                }

                this.$('.datepicker').datepicker({
                    dateFormat: 'MM dd, yy',
                    showOtherMonths: true,
                    selectOtherMonths: true
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
            },

            updateUnitTitle: function( ev ) {
                var sender = this.$(ev.currentTarget),
                    value = sender.val();

                CoursePress.Events.trigger( 'coursepress:change_unit_title', value, this.model.cid );
            },

            updateUnitCollection: function () {
                // Set the model back to the collection
                this.editCourseView.unitList.unitModels[this.model.cid] = this.model;
                this.editCourseView.unitList.units[this.model.cid].unitDetails.model = this.model;
            }
        });
    });
})();
