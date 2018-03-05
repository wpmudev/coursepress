/* global CoursePress */

(function() {
    'use strict';

    CoursePress.Define( 'UnitsWithModuleList', function($, doc, win) {
        var UnitView;

        UnitView = CoursePress.View.extend({
            template_id: 'coursepress-unit-tpl',
            className: 'unit-view',
            unitsView: false,
            steps: {},
            with_modules: false,
            events: {
                'click .cp-unit-heading label': 'toggleListing',
                'click .preview-unit': 'previewUnit',
                'click .edit-unit': 'editUnit',
                'click .column-unit': 'editModule',
                'click .column-step': 'editModule',
                'click .delete-unit': 'deleteUnit',
                'change [name]': 'updatePreviewStatus'
            },

            updatePreviewStatus: function (ev) {
                var input, name, value, with_modules;

                input = $(ev.currentTarget);
                name = input.attr('name');
                with_modules = this.modulesActive();
                value = input.is(':checked') ? input.val() : false;

                if (with_modules) {
                    var module_id = input.closest('[data-module]').data('module'),
                        modules = this.model.get('modules');

                    modules[module_id][name] = value;
                }
                else {
                    var step_id = input.closest('[data-step]').data('step'),
                        steps = this.model.get('steps');

                    steps[step_id][name] = value;
                }
            },

            modulesActive: function () {
                return this.unitsView.editCourse.model.get('meta_with_modules');
            },

            initialize: function( model, unitsView ) {
                var with_modules;

                this.model = model;
                this.unitsView = unitsView;

                with_modules = this.modulesActive();

                if ( ! with_modules || ! model.get('modules') ) {
                    model.set('modules', false);
                }
                if ( with_modules || ! model.get('steps' ) ) {
                    model.set('steps', false);
                }

                this.render();
            },

            toggleListing: function( ev ) {
                var sender = this.$(ev.currentTarget),
                    list = sender.parent().next('.cp-unit-content'),
                    is_open = list.is(':visible');

                if ( is_open ) {
                    list.slideUp();
                    sender.addClass('close');
                } else {
                    list.slideDown();
                    sender.removeClass('close');
                }
            },

            getUnit: function( cid ) {
                return this.unitsView.editCourse.unitList.units[cid];
            },

            editUnit: function(ev) {
                var sender, cid;

                sender = this.$(ev.currentTarget);
                cid = sender.data('unit');
                this._editUnit(cid);
            },

            // Unit preview button.
            previewUnit: function (ev) {
                var target = this.$(ev.currentTarget),
                    link = target.data('url');
                if (typeof link !== 'undefined') {
                    window.open(link, '_blank');
                }
            },

            _editUnit: function( cid ) {
                var unit;

                unit = this.getUnit(cid);

                if (unit) {
                    unit.setUnitDetails();
                }

                return unit;
            },

            editModule: function(ev) {
                var sender, module_id, unit_id, unit;

                sender = this.$(ev.currentTarget);
                module_id = sender.data('module');
                unit_id = sender.data('unit');
                unit = this._editUnit(unit_id);

                if ( unit.unitview && unit.unitview.modules ) {
                    unit.unitview.modules.$('.module-item[data-order="' + module_id + '"]').trigger('click');
                }
            },

            deleteUnit: function(ev) {
                var sender, cid;

                sender = this.$(ev.currentTarget);
                cid = sender.data('unit');

                this.unitsView.editCourse.unitList.deleteUnit(cid);
                this.remove();
            }
        });

        return CoursePress.View.extend({
            template_id: 'coursepress-units-tpl',
            className: 'unit-view',
            editCourse: false,
            unitItems: {},

            initialize: function(model, editCourseView) {
                this.with_modules = editCourseView.model.get('meta_with_modules');
                this.model = model;
                this.editCourse = editCourseView;
                this.editCourse.unitCollection.on('add', this.setUnitItem, this);
                this.editCourse.off('coursepress:validate-course-units');
                this.editCourse.on('coursepress:validate-course-units', this.validateUnits, this);
                this.editCourse.unitCollection.on( 'coursepress:unit_collection_loaded', this.maybeSetUnit, this );
                this.on('view_rendered', this.setUI, this);
                this.render();
            },

            validateUnits: function() {
                var units, error, error_msg, popup;

                units = this.editCourse.unitList.units;
                error = 0;
                error_msg = {};


                _.each( units, function( unit ) {
                    var cid, model, modules, steps;

                    cid = unit.model.get('cid');

                    if ( unit.unitDetails ) {
                        // Let's trigger per unit validation first
                        if ( ! unit.unitDetails.validateUnit() ) {
                            error += 1;
                        }
                    } else if ( ! error ) {
                        // Check per model if no errors found
                        model = this.editCourse.unitList.unitModels[cid];

                        if ( ! model.get('post_title') ) {
                            error_msg.no_title = win._coursepress.text.unit.no_title;
                        } else if ( model.get('meta_use_feature_image') &&
                            ! model.get('meta_feature_image') ) {
                            error_msg.no_feature = win._coursepress.text.unit.no_feature_image;
                        } else if ( model.get('meta_use_description') &&
                            ! model.get('post_content') ) {
                            error_msg.no_content = win._coursepress.text.unit.no_content;
                        } else if ( this.with_modules ) {
                            modules = model.get('modules');

                            if ( ! modules || _.keys(modules).length ) {
                                error_msg.no_modules = win._coursepress.text.unit.no_modules;
                            }
                        } else if ( ! this.with_modules ) {
                            steps = model.get('steps');

                            if ( ! steps || _.keys(steps).length ) {
                                error_msg.no_steps = win._coursepress.text.unit.no_steps;
                            }
                        }
                    }
                }, this );


                if ( ! error ) {
                    this.editCourse.unitList.updateUnits();
                } else {
                    if ( error_msg.length ) {
                        popup = new CoursePress.PopUp({
                            type: 'warning',
                            message: error_msg.join('<br/>')
                        });
                    }
                }

                //ev.stopImmediatePropagation();
            },

            maybeSetUnit: function(data) {
                if ( ! data || ! data.length ) {
                    var self = this;
                    _.delay(function () {
                        self.editCourse.unitList.$('.new-unit').trigger('click');
                        self.showUnitHelpOverlays();
                    }, 100);
                }
            },

	        showUnitHelpOverlays: function () {
		        var unitsMenuOverlay, unitTitleOverlay;

		        if (win._coursepress.unit_help_dismissed) {
			        return;
		        }

		        unitsMenuOverlay = new CoursePress.HelpOverlay($('.step-course-units'), {
			        popup_title: win._coursepress.text.units_menu_help_overlay.title,
			        popup_content: win._coursepress.text.units_menu_help_overlay.content
		        });
		        unitsMenuOverlay.on('coursepress:popup_ok', function () {
			        unitTitleOverlay = new CoursePress.HelpOverlay($('.cp-unit-title-box'), {
				        popup_title: win._coursepress.text.unit_title_help_overlay.title,
				        popup_content: win._coursepress.text.unit_title_help_overlay.content
			        });

			        unitTitleOverlay.on('coursepress:popup_ok', function () {
				        new CoursePress.HelpOverlay($('.unit-steps-tools'), {
					        popup_title: win._coursepress.text.unit_steps_help_overlay.title,
					        popup_content: win._coursepress.text.unit_steps_help_overlay.content
				        });
			        });
		        });
	        },

            setUI: function() {
                this.unitsContainer = this.$('#units-container');

                if ( this.editCourse.unitCollection &&
                    this.editCourse.unitCollection.unitsLoaded ) {

                    // Set unit models
                    _.each( this.editCourse.unitCollection.models, function( model ) {
                        this.editCourse.unitList.addUnit(model);
                        this.setUnitItem(model);
                    }, this );
                }
            },

            setUnitItem: function(unitModel){
                var id, unitItem;

                if ( unitModel.get('deleted') ) {
                    return;
                }

                id = unitModel.cid;
                unitItem = new UnitView(unitModel, this);
                unitItem.$el.appendTo(this.unitsContainer);
                this.unitItems[id] = unitItem;
            }
        });
    });
})();