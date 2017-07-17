/* global CoursePress, _ */

(function(){
    'use strict';

    CoursePress.Define( 'CourseSetUp', function($, doc, win) {
        var EditCourse;

        EditCourse = CoursePress.View.extend({
            steps: [],
            currentStep: false,
            goNextStep: false,
            el: $( '#course-edit-template' ),
            events: {
                'click .step': 'toggleContent',
                'click .step-back': 'getPreviousStep',
                'click .step-next': 'getNextStep',
                'click .step-cancel': 'returnToMainPage',
                'click .step-icon-bars': 'toggleStepList'
            },
            initialize: function(model) {
                model = this.filter_model(model);
                this.model = new CoursePress.CourseModel(model);

                // Load course-type view
                this.once( 'coursepress:load-step-course-type', this.courseTypeView, this);
                // Load course settings view
                this.once('coursepress:load-step-course-settings', this.courseSettingsView, this);
                // Load course completion view
                this.once( 'coursepress:load-step-course-completion', this.courseCompletionView, this );
                // Load course units view
                this.once('coursepress:load-step-course-units', this.courseUnitsView, this);
                // Load course students view
                this.once('coursepress:load-step-course-students', this.courseStudentsView, this);

                // Load templates
                this.render();
            },
            filter_model: function ( model ) {
                var dates = ['course_start_date', 'course_end_date', 'enrollment_start_date', 'enrollment_end_date'];

                _.each( dates, function( d ) {
                    if ( ! model[d] ) {
                        model[d] = '';
                    }
                });

                if ( ! model.class_size ) {
                    model.class_size = 0;
                }

                return model;
            },
            render: function() {
                var step;

                // Get all steps
                _.each( this.$('.cp-menu-item'), this.getSteps, this );

                // Get the buttons and HTML containers
                this.prevButton = this.$('.step-back');
                this.stepListContainer = this.$('.cp-menu-items .course-menu');

                // Setup steps positions
                this.firstStep = _.first(this.steps);
                this.lastStep = _.last(this.steps);

                // Hook into step change event
                this.on('coursepress:step-changed', this.stepChanged, this);

                // Check if the browser remember the last active step and if the course is not new
                if ( !_.isEmpty(this.model.get('post_title')) ) {
                    step = CoursePress.Cookie('course_setup_step_' + this.model.get('ID')).get();
                }

                // If current step is not set, set the first step as current step
                if ( ! step ) {
                    step = _.first(this.steps);
                }

                this.setCurrentStep(step);

                return this;
            },
            courseTypeView: function() {
                var courseType = new CoursePress.CourseType(this.model, this);

                return courseType;
            },
            courseSettingsView: function() {
                var courseSettings = new CoursePress.CourseSettings(this.model, this);

                return courseSettings;
            },
            courseCompletionView: function() {
                var courseCompletion = new CoursePress.CourseCompletion(this.model, this);

                return courseCompletion;
            },
            courseUnitsView: function() {
                var courseUnits = new CoursePress.CourseUnits(this.model, this);

                return courseUnits;
            },
            courseStudentsView: function() {
                this.students = new CoursePress.Course_Students( this.model, this );
            },
            getSteps: function(step) {
                this.steps.push($(step).data('step'));
            },
            getCurrentStep: function() {
                return this.$('[data-step="' + this.currentStep + '"]');
            },
            setCurrentStep: function(step) {
                if ( this.currentStep && step !== this.firstStep ) {
                    /**
                     * Trigger to validate current step and determine to whether
                     * or not to load the next step.
                     */
                    this.trigger('coursepress:validate-' + this.currentStep);

                    if ( false === this.goToNext ) {
                        // One of the validation failed, return!
                        return;
                    }
                }

                /**
                 * Trigger before a step is changed.
                 *
                 * @param string step - Current selected step
                 * @param object ModlaSteps instance
                 */
                this.trigger('coursepress:step-before-change', this.currentStep, this );
                this.currentStep = step;

                /**
                 * Trigger whenever a step is changed.
                 */
                this.trigger( 'coursepress:step-changed', this.currentStep, this );

                /**
                 * Trigger for per step event hook
                 */
                this.trigger( 'coursepress:load-step-' + this.currentStep, this);

                // Let the browser remember this step for a year!
                CoursePress.Cookie('course_setup_step_' + this.model.get('ID')).set(this.currentStep, 86400 * 7);
            },
            getCurrentTab: function() {
                return this.$('#' + this.currentStep);
            },
            stepChanged: function() {
                // Toggle button
                this.prevButton[ this.currentStep === this.firstStep ? 'hide' : 'show']();

                this.current = this.getCurrentStep();
                this.current.siblings().removeClass('active');
                this.current.addClass('active');

                this.currentTab = this.getCurrentTab();
                this.currentTab.siblings().removeClass('tab-active').removeClass('done');
                this.currentTab.addClass('tab-active');
            },
            toggleContent: function(ev) {
                var sender = $(ev.currentTarget),
                    step = sender.data('step');

                if ( step === this.currentStep ) {
                    return;
                }

                this.setCurrentStep(step);
                this.toggleStepList();
            },
            getPreviousStep: function() {
                var stepIndex = _.indexOf(this.steps, this.currentStep);

                if ( stepIndex > 0 ) {
                    stepIndex -= 1;
                    this.setCurrentStep(this.steps[stepIndex]);
                }
            },
            getNextStep: function() {
                var stepIndex, maxStep;

                stepIndex = _.indexOf(this.steps, this.currentStep);
                maxStep = this.steps.length - 1;

                if ( stepIndex < maxStep ) {
                    stepIndex += 1;

                    // Try to load next step
                    this.setCurrentStep(this.steps[stepIndex]);

                    if ( false === this.goToNext ) {
                        // Return if next step is not loaded
                        return;
                    }

                    /**
                     * Trigger whenever next step is activated
                     *
                     * @param string step
                     * @param object StepsModal instance
                     */
                    this.trigger('coursepress:next-step-activated', this.steps[stepIndex], this);

                    if ( this.currentStep === this.lastStep ) {
                        /**
                         * Trigger when the last step is reached.
                         *
                         * @param string step
                         * @param object StepsModal instance
                         */
                        this.trigger('coursepress:lastStepActivated', this.currentStep, this);
                    }
                }

                // Rebirth to loading next step into false to re-apply validation
                this.goNextStep = false;
            },
            returnToMainPage: function() {},

            toggleStepList: function() {
                this.stepListContainer.toggleClass('open', '');
            },
            updateCourse: function() {
                this.model.set( 'action', 'update_course' );
                this.model.off( 'coursepress:success_update_course' );
                this.model.on( 'coursepress:success_update_course', this.courseUpdated, this );
                this.model.save();
            },
            courseUpdated: function() {
                //window.alert(data);
            }
        });

        // Init course edit on first load
        EditCourse = new EditCourse(win._coursepress.course);
        win.Course = EditCourse;
    });
})();