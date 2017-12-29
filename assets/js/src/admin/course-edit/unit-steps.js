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

            sortByMenuOrder: function (steps) {
                return _.sortBy(steps, function (step) {
                    return step.get !== undefined ? step.get('menu_order') : step.menu_order;
                });
            },

	        reorderSteps: function() {
		        var steps, menu_order;

		        steps = this.stepContainer.find('[name="menu_order"]');
		        menu_order = 0;

		        _.each( steps, function( step ) {
			        step = $(step);
			        menu_order += 1;
			        step.val(menu_order).trigger('change');
		        }, this );
	        },

            setUI: function() {
                var steps, step_view, unit_steps;

                this.stepContainer = this.$('.unit-steps');

                steps =  this.sortByMenuOrder(this.model.get('steps'));

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
                var sender, type, data;

                sender = this.$(ev.currentTarget);
                type = sender.data('step');
                this.menu_order += 1;
                data = {
                    module_type: type,
                    menu_order: this.menu_order
                };
                this.setStep(data);
            },

            setStep: function(model) {
                var step, cid;

                step = new CoursePress.Step({model: model}, this);
                step.$el.appendTo(this.stepContainer);

                cid = step.model.cid;
                this.steps[cid] = step.model;
                this.updateSteps();

                step.on( 'coursepress:model_updated', this.updateStepsCollection, this );
	            step.on( 'coursepress:step_reordered', this.reorderSteps, this );

	            if (step.model.get('menu_order') > this.menu_order) {
		            this.menu_order = step.model.get('menu_order');
	            }

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
