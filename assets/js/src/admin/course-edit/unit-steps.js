/* global CoursePress */

(function() {
    'use strict';

    CoursePress.Define( 'Unit_Steps', function($) {
        return CoursePress.View.extend({
            template_id: 'coursepress-unit-steps-tpl',
            unitModel: false,
            steps: {},
            menu_order: 0,
            events: {
                'click .unit-step': 'addNewStep'
            },

            initialize: function( model, unitModel ) {
                this.steps = {};
                this.unitModel = unitModel;
                this.on( 'view_rendered', this.setUI, this );
                this.render();
            },

            setUI: function() {
                var steps, step_view, unit_steps;

                this.stepContainer = this.$('.unit-steps');

                steps =  this.model.get('steps');

                if ( steps ) {
                    _.each(steps, function (step) {
                        step_view = this.setStep(step);
                        step_view.toggleContents();
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

            addNewStep: function(ev) {
                var sender, type, menu_order, data;

                menu_order = this.steps.length + 1;
                sender = this.$(ev.currentTarget);
                type = sender.data('step');
                data = {module_type: type, menu_order: menu_order};
                this.setStep(data);
            },

            setStep: function(model) {
                var step, cid;

                this.menu_order += 1;
                model.menu_order = this.menu_order;
                step = new CoursePress.Step({model: model}, this);
                step.$el.appendTo(this.stepContainer);

                cid = step.model.cid;
                this.steps[cid] = step.model;
                this.updateSteps();

                step.on( 'coursepress:model_updated', this.updateStepsCollection, this );

                return step;
            },

            updateStepsCollection: function(stepModel) {
                var cid;

                cid = stepModel.cid;
                this.steps[cid] = stepModel;
                this.updateSteps();
            },

            updateSteps: function() {
                this.unitModel.model.set('steps', this.steps);
            }
        });
    });
})();
