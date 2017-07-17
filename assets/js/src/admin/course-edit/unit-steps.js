/* global CoursePress */

(function() {
    'use strict';

    CoursePress.Define( 'Unit_Steps', function() {
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
                window.console.log(unitModel);
                this.on( 'view_rendered', this.setUI, this );
                this.render();
            },
            setUI: function() {
                this.stepContainer = this.$('.unit-steps');
            },
            addNewStep: function( ev ) {
                var sender, type, step, menu_order, data;

                menu_order = this.steps.length + 1;
                sender = this.$(ev.currentTarget);
                type = sender.data('step');
                data = {type: type, menu_order: menu_order};
                step = new CoursePress.Step(data, this);
                step.$el.appendTo(this.stepContainer);
            },
        });
    });
})();