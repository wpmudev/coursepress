/* global CoursePress */

(function() {
    'use strict';

    CoursePress.Define( 'UnitModules', function( $, doc, win ) {
        var ModuleList, ModuleSteps, ModulesPopup;

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
            steps: {},
            stepsModel: {},
            menu_order: 0,
            views: {},

            events: {
                'click .unit-step': 'addNewStep',
                'keyup .module-title': 'updateModuleTitle',
                'change [name="show_description"]': 'toggleDescription'
            },

            initialize: function( model, unitModulesModel ) {
                this.steps = {};
                this.stepsModel = {};
                this.model = model;
                this.unitModel = unitModulesModel.unitModel;
                this.moduleView = unitModulesModel;
	            this.model.steps = this.sortByMenuOrder(this.model.steps);
                this.on( 'view_rendered', this.setUI, this );

                this.render();
            },

	        sortByMenuOrder: function (steps) {
		        return _.sortBy(steps, function (step) {
			        return step.get !== undefined ? step.get('menu_order') : step.menu_order;
		        });
	        },

            setUI: function() {
                var self;

                this.stepContainer = this.$('.unit-steps');
                self = this;

                this.visualEditor({
                    content: this.model.description,
                    container: this.$('.cp-module-description'),
                    callback: function(content) {
                        self.model.description = content;
	                    self.trigger('coursepress:update_module_description', content, this);
                    }
                });

                if ( this.model.steps ) {
                    _.each( this.model.steps, function( step ) {
                        step = this.setStep(step);
                        step.toggleContents();
                    }, this );
                }
            },

            addNewStep: function( ev ) {
                var sender, type, data, step;

                sender = this.$(ev.currentTarget);
                type = sender.data('step');
	            this.menu_order += 1;
                data = {
                    module_type: type,
                    meta_module_type: type,
	                menu_order: this.menu_order
                };
                step = this.setStep(data);
            },

            setStep: function( model ) {
                var step, cid;

                step = new CoursePress.Step(model, this);
                if (!step.model.get('deleted')) {
                    step.$el.appendTo(this.stepContainer);
                }

                cid = model.cid ? model.cid : step.model.cid;
                this.steps[cid] = step;
	            this.stepsModel[cid] = step.model;
                this.updateModuleSteps(step.model);

	            step.on('coursepress:step_reordered', this.reorderSteps, this);

	            if (step.model.get('menu_order') > this.menu_order) {
		            this.menu_order = step.model.get('menu_order');
	            }

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
                stepModel.module_page = this.model.id;
                stepModel.set('meta_module_page', this.model.id);
                this.model.steps = this.stepsModel;
                this.moduleView.modules[this.moduleView.current] = this.model;
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

            setStepIcons: function() {
                var icons = {};

                this.$('.step-icon-container').empty();

                _.each( this.steps, function(step) {
                    var type = step.module_type;

                    if ( ! icons[type] ) {
                        icons[type] = type;
                    }
                });
            },

            toggleDescription: function(ev) {
                var sender, checked, content;

                sender = this.$(ev.currentTarget);
                checked = sender.is(':checked');
                content = this.$('.cp-module-description');

                content[ checked ? 'slideDown' : 'slideUp']();
                this.model.show_description = checked ? true : false;
            }
        });

        ModulesPopup = CoursePress.PopUp.extend({
            template_id: 'coursepress-move-to-module-popup-tpl',
            events: {
                'click .btn-ok': 'Ok',
                'click .cp-btn-cancel': 'Cancel',
                'change [name]': 'updateModel'
            },
            render: function() {
                // Call the parent render method
                CoursePress.PopUp.prototype.render.apply(this, arguments);

                this.$('select').select2({
                    placeholder: win._coursepress.text.select_module,
                    minimumResultsForSearch: 10,
                    width: '50%'
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
                'click .add-module': 'addModule',
                'change [name]': 'updateModel',
                'click .menu-item-move': 'moveStep',
                'click .cp-delete-module': 'deleteModule'
            },

            initialize: function( model, unitModel ) {
                this.modules = this.model.get('modules');
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
                var model, length, modules, new_index;
                modules = _.toArray(this.modules);
                length = modules.length;
                new_index = length + 1;
                model = {
                    id: new_index,
                    title: win._coursepress.text.untitled,
                    show_description: true,
                    description: '',
                    steps: {}
                };
                this.modules[new_index] = model;
                this.model.set('modules', this.modules);
                this.current = new_index;
                this._setActiveModule(model);
                this.setModuleList();
                // Set the first module as active
                this.$('[data-order]').last().trigger('click');
                this.updateCounter( 1 );
            },

            setModuleList: function() {
                var self = this;
                this.moduleListContainer.html('');
                this.moduleList = new ModuleList(this.modules);
                this.moduleList.$el.appendTo(this.moduleListContainer);
                this.moduleListContainer.find('.cp-select-list').sortable({
                    stop: function() {
                        self.reOrderModules();
                    }
                });
            },

            /**
             * update counter
             */
            updateCounter: function( val ) {
                var counter = $('.course-menu-list .unit-item.active .cp-count');
                var value = parseInt( counter.data('count' ) ) + val;
                counter.data('count', value).html( value );
            },

            moveStep: function (ev) {
                var moduleId, stepID, stepElement, step, stepModel, modulesPopup, self = this;
                stepElement = $(ev.currentTarget).closest('.unit-step-module');
                modulesPopup = new ModulesPopup({
                    modules: _.omit(this.modules, function (module) {
                        return module.id === self.moduleView.id;
                    })
                });
                modulesPopup.on('coursepress:popup_ok', function (popup) {
                    moduleId = popup.model.get('target_module');
                    stepID = stepElement.find('[name="menu_order"]').data('cid');
                    if (!moduleId || !stepID) {
                        return;
                    }
                    step = _.find(this.moduleView.steps, function (step) {
                        return step.model.cid === stepID;
                    });
                    stepModel = JSON.parse(JSON.stringify(step.model));
                    stepModel = _.omit(stepModel, ['ID', 'cid']);
                    // Remove the old version of the step
                    step.removeStep();
                    // Switch to the target module
                    this.$('.module-item[data-id="' + moduleId + '"]').trigger('click');
                    // Add the step to target module
                    this.moduleView.setStep(stepModel);
                }, this);
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
                if ( this.moduleView ) {
                    this.moduleView.remove();
                }
                model = _.extend({
                    show_description: true,
                    description: ''
                }, model );

                this.moduleView = new ModuleSteps(model, this);
                this.moduleView.$el.appendTo( this.stepsContainer );
                this.moduleView.on('coursepress:update_module_title', this.updateActiveTitle, this);
	            this.moduleView.on('coursepress:update_module_description', this.updateActiveDescription, this);
            },

            updateActiveTitle: function( title ) {
                this.active.find('.module-title').html(title);
                this.updateModuleModel();
            },

	        updateActiveDescription: function ( description ) {
		        this.active.find('.module-description').html( description );
	        },

            updateModuleModel: function() {
                if ( ! this.moduleView ) {
                    return;
                }
                this.modules[this.current] = this.moduleView.model;
                this.unitModel.model.set('modules', this.modules);
            },

            updateModel: function(ev) {
                ev.stopImmediatePropagation();
            },

            reOrderModules: function() {
                var x, modules;
                x = 0;
                modules = {};
                _.each( this.moduleListContainer.find('.cp-select-list li'), function(module) {
                    var order, _module;
                    module = $(module);
                    order = module.data('order');
                    x += 1;
                    _module = this.modules[order];
                    if ( _module.steps ) {
                        _.each( _module.steps, function(step, pos){
                            if(!!step.get) {
                                step.set('meta_module_page', x);
                                step.set('module_page', x);
                            }
                            else {
                                step.meta_module_page = step.module_page = x;
                            }

                            _module.steps[pos] = step;
                        }, this );
                    }
                    modules[x] = _module;
                    module.attr('data-order', x);
                    module.data('order', x);
                }, this );
                // At this point the deleted modules have been removed from the 'modules' array because their markup doesn't exist on the page.
                // Let's put them back so that modules can be deleted properly server side.
                _.each(this.modules, function (module) {
                    if(module.deleted) {
                        x += 1;
                        modules[x] = module;
                    }
                });
                this.modules = modules;
                this.unitModel.model.set('modules', this.modules);
            },

            deleteModule: function() {
                if ( this.moduleView ) {
                    this.modules[this.current].deleted = true;
                    this.moduleView.remove();
                    this.setModuleList();
                    this.reOrderModules();
                    this.$('[data-order]').last().trigger('click');
                    this.updateCounter( -1 );
                }
            }
        });
    });
})();
