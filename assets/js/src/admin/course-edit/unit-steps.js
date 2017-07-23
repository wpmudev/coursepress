/* global CoursePress */

(function() {
    'use strict';

    CoursePress.Define( 'Unit_Steps', function($) {
        return CoursePress.View.extend({
            template_id: 'coursepress-unit-steps-tpl',
            unitModel: false,
            steps: [],
            events: {
                'click .unit-step': 'addNewStep',
            },

            initialize: function( model, unitModel ) {
                this.model = model;
                this.unitModel = unitModel;
                this.on( 'view_rendered', this.setUI, this );
                this.render();
            },

            setUI: function() {
                var steps, step_view, unit_steps;

                this.stepContainer = this.$('.unit-steps');

                steps = this.model.get('steps');

                if ( steps ) {

                    CoursePress.Events.on( 'coursepress:step_rendered', this.toggleStep, this );

                    _.each(steps, function (step) {
                        step_view = this.setStep(step);
                    }, this);

                    _.delay(function() {
                        unit_steps = $('.unit-steps');
                        unit_steps.sortable({
                            axis: 'y',
                            step: function() {
                                step_view.reOrderSequence();
                            }
                        });
                    }, 200 );
                }
            },

            toggleStep: function( stepModel ) {
                stepModel.toggleContents();
            },

            addNewStep: function( ev ) {
                var sender, type, menu_order, data;

                menu_order = this.steps.length + 1;
                sender = this.$(ev.currentTarget);
                type = sender.data('step');
                data = {module_type: type, menu_order: menu_order};
                this.setStep(data);
            },

            setStep: function( model ) {
                var step;

                step = new CoursePress.Step(model, this);
                step.$el.appendTo(this.stepContainer);
            }
        });
    });
})();