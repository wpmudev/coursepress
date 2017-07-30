/* global CoursePress */

(function() {
    'use strict';

    CoursePress.Define( 'UnitModules', function( $, doc, win ) {
        var ModuleList, ModuleSteps;

        ModuleList = CoursePress.View.extend({
            template_id: 'coursepress-unit-module-list-tpl',
            initialize: function(modules) {
                this.model = {modules: modules};
                this.render();
            }
        });

        ModuleSteps = CoursePress.View.extend({
            template_id: 'coursepress-unit-module-steps-tpl',
            unitModel: false,
            stepsView: false,
            moduleView: false,
            steps: [],
            menu_order: 0,
            views: {},

            events: {
                'click .unit-step': 'addNewStep',
                'keyup .module-title': 'updateModuleTitle'
            },

            initialize: function( model, unitModel, moduleView ) {
                var keys;

                this.model = model;
                keys = _.keys( model.steps );

                if ( ! keys.length ) {
                    this.steps = {};
                } else {
                    this.steps = model.steps;
                }

                this.unitModel = unitModel;
                this.moduleView = moduleView;
                this.on( 'view_rendered', this.setUI, this );

                this.render();
            },

            setUI: function() {
                this.stepContainer = this.$('.unit-steps');

                if ( this.steps ) {
                    _.each( this.steps, function( step ) {
                        step = this.setStep(step);
                        step.toggleContents();
                    }, this );
                }
            },

            addNewStep: function( ev ) {
                var sender, type, data, step;

                sender = this.$(ev.currentTarget);
                type = sender.data('step');
                data = {
                    module_type: type,
                    meta_module_type: type
                };
                step = this.setStep(data);
                this.updateModuleSteps(step.model);
            },

            setStep: function( model ) {
                var step;

                this.menu_order += 1;
                model.menu_order = this.menu_order;
                step = new CoursePress.Step(model, this);
                step.on( 'coursepress:model_updated', this.updateModuleSteps, this );
                step.trigger( 'coursepress:model_updated', step.model, step );
                step.on( 'coursepress:step_reordered', this.reorderSteps, this );
                step.$el.appendTo(this.stepContainer);
                this.views[step.cid] = step;

                return step;
            },

            updateModuleTitle: function(ev) {
                var sender, title;

                sender = this.$(ev.currentTarget);
                title = sender.val();
                this.trigger('coursepress:update_module_title', title, this);
                this.model.title = title;
            },

            updateModuleSteps: function( stepModel ) {
                var stepId;

                stepId = stepModel.cid;
                stepModel.set('module_page', this.model.id);
                stepModel.set('meta_module_page', this.model.id);
                this.steps[stepId] = stepModel.toJSON();
                this.model.steps = this.steps;
                this.moduleView.updateModuleModel();
            },

            reorderSteps: function() {
                var steps, menu_order, newSteps;

                steps = this.stepContainer.find('[name="menu_order"]');
                newSteps = {};
                menu_order = 0;

                _.each( steps, function( step ) {
                    step = $(step);
                    menu_order += 1;
                    step.val(menu_order).trigger('change');

                    var cid = step.data('cid');
                    newSteps[cid] = this.steps[cid];
                }, this );

                this.model.steps = this.steps;
                this.moduleView.updateModuleModel();
            },

            setStepIcons: function() {
                var icons = {};

                this.stepIconContainer = this.$('.step-icon-container').empty();

                _.each( this.steps, function(step) {
                    var type = step.module_type;

                    if ( ! icons[type] ) {
                        icons[type] = type;
                    }
                });
            }
        });

        return CoursePress.View.extend({
            template_id: 'coursepress-unit-modules-tpl',
            current: 1,
            modules: false,
            moduleView: false,
            active: false,
            events: {
                'click .module-item': 'setActiveModule',
                'click .add-module': 'addModule'
            },

            initialize: function( model, unitModel ) {
                this.model = model;
                this.modules = model.get('modules');
                this.unitModel = unitModel;
                this.on( 'view_rendered', this.setUI, this );
                this.render();
            },

            setUI: function() {
                this.moduleListContainer = this.$('#unit-module-list');
                this.stepsContainer = this.$('#cp-module-steps');

                this.setModuleList();

                // Set the first module as active
                this.$('[data-order]').first().trigger('click');
            },

            addModule: function() {
                var model, keys, length;

                keys = _.keys(this.modules);
                length = keys.length + 1;
                model = {
                    id: length,
                    title: win._coursepress.text.untitled,
                    steps: {}
                };
                this.modules[length] = model;
                this.model.set('modules', this.modules);
                this.current = length;
                this._setActiveModule(model);
                this.setModuleList();
            },

            setModuleList: function() {
                this.moduleListContainer.html('');
                this.moduleList = new ModuleList(this.modules);
                this.moduleList.$el.appendTo(this.moduleListContainer);
            },

            setActiveModule: function( ev ) {
                var sender, item, model;

                this.active = sender = this.$(ev.currentTarget);
                item = sender.data('order');

                this.current = parseInt(item);
                sender.siblings().removeClass('active');
                sender.addClass('active');
                model = this.modules[this.current];
                this._setActiveModule(model);
            },

            _setActiveModule: function( model ) {
                this.stepsContainer.html('');
                this.moduleView = new ModuleSteps(model, this.unitModel, this );
                this.moduleView.$el.appendTo( this.stepsContainer );
                this.moduleView.on( 'coursepress:update_module_title', this.updateActiveTitle, this );
            },

            updateActiveTitle: function( title ) {
                this.active.find('span').html(title);
                this.updateModuleModel();
            },

            updateModuleModel: function() {
                this.modules[this.current] = this.moduleView.model;
                this.unitModel.model.set('modules', this.modules);
            }
        });
    });
})();