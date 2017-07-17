/* global CoursePress */

(function() {
    'use strict';

    CoursePress.Define( 'UnitModules', function() {
        var ModuleSteps;

        ModuleSteps = CoursePress.View.extend({
            template_id: 'coursepress-unit-module-steps-tpl',
            unitModel: false,
            stepsView: false,
            steps: [],
            events: {
                'click .unit-step': 'addNewStep',
                'keyup .module-title': 'updateModuleTitle'
            },
            initialize: function( model, unitModel ) {
                this.model = model;
                this.steps = model.steps;
                this.unitModel = unitModel;
                this.on( 'view_rendered', this.setSteps, this );
                this.render();
            },
            setSteps: function() {
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
            updateModuleTitle: function(ev) {
                var sender = this.$(ev.currentTarget),
                    title = sender.val();
                this.trigger('coursepress:update_module_title', title, this);
            }
        });

        return CoursePress.View.extend({
            template_id: 'coursepress-unit-modules-tpl',
            current: 1,
            modules: false,
            moduleView: false,
            active: false,
            events: {
                'click .module-item': 'setActiveModule'
            },
            initialize: function( model, unitModel ) {
                this.model = model;
                this.modules = model.get('modules');
                this.unitModel = unitModel;
                this.on( 'view_rendered', this.setSteps, this );
                this.render();
            },
            setSteps: function() {
                this.stepsContainer = this.$('#cp-module-steps');

                // Set the first module
                this.$('[data-order="1"]').trigger('click');
            },
            setActiveModule: function( ev ) {
                var sender, item, model;

                this.active = sender = this.$(ev.currentTarget);
                item = sender.data('order');

                this.current = parseInt(item);
                sender.siblings().removeClass('active');
                sender.addClass('active');

                this.stepsContainer.html('');
                model = this.modules[this.current];
                this.moduleView = new ModuleSteps(model, this.unitModel);
                this.moduleView.$el.appendTo( this.stepsContainer );
                this.moduleView.off( 'coursepress:update_module_title' );
                this.moduleView.on( 'coursepress:update_module_title', this.updateActiveTitle, this );
            },
            updateActiveTitle: function( title ) {
                this.active.find('span').html(title);
            }
        });
    });
})();