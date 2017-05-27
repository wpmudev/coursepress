/* global CoursePress */

(function(){
    'use strict';

    CoursePress.Define( 'CourseSetUp', function($, doc, win) {
        var EditCourse;

        EditCourse = CoursePress.View.extend({
            steps: [],
            currentStep: false,
            goNextStep: false,
            el: $('#course-edit-template'),
            events: {
                'click .step': 'toggleContent',
                'click .step-back': 'getPreviousStep',
                'click .step-next': 'getNextStep',
                'click .step-cancel': 'returnToMainPage',
                'click .step-icon-bars': 'toggleStepList'
            },
            initialize: function(model) {
                this.model = new CoursePress.CourseModel(model);

                // Load course-type view
                this.once( 'coursepress:load-step-course-type', this.courseTypeView, this);
                // Load course settings view
                this.once('coursepress:load-step-course-settings', this.courseSettingsView, this);
                // Load course units view
                this.once('coursepress:load-step-course-units', this.courseUnitsView, this);
                // Load course students view
                this.once('coursepress:load-step-course-students', this.courseStudents, this);

                // Load templates
                this.render();
            },
            render: function() {
                // Get all steps
                _.each( this.$('.step-list li'), this.getSteps, this );

                // Get the buttons and HTML containers
                this.prevButton = this.$('.step-back');
                this.stepListContainer = this.$('.step-list');

                // Setup steps positions
                this.firstStep = _.first(this.steps);
                this.lastStep = _.last(this.steps);

                // Hook into step change event
                this.on('coursepress:step-changed', this.stepChanged, this);

                // Check if the browser remember the last active step and if the course is not new
                if ( !_.isEmpty(this.model.get('post_title')) ) {
                    this.currentStep = CoursePress.Cookie('course_setup_step_' + this.model.get('ID')).get();
                }

                // If current step is not set, set the first step as current step
                if ( ! this.currentStep ) {
                    this.currentStep = _.first(this.steps);
                }
                this.setCurrentStep(this.currentStep);
            },
            courseTypeView: function() {
                new CoursePress.CourseType(this.model, this);
            },
            courseSettingsView: function() {
                new CoursePress.CourseSettings(this.model, this);
            },
            courseUnitsView: function() {
                new CoursePress.CourseUnits(this.model, this);
            },
            courseStudentsView: function() {},
            getSteps: function(step) {
                this.steps.push($(step).data('step'));
            },
            getCurrentStep: function() {
                return this.$('[data-step="' + this.currentStep + '"]');
            },
            setCurrentStep: function(step) {
                if ( step !== this.firstStep ) {
                    /**
                     * Trigger to validate current step and determine to whether
                     * or not to load the next step.
                     */
                    this.trigger('coursepress:validate-' + this.currentStep);

                    if (false === this.goToNext) {
                        // One of the validation failed, return!
                        return;
                    }
                }

                this.currentStep = step;

                /**
                 * Trigger whenever a step is changed.
                 *
                 * @param string step - Current selected step
                 * @param object ModlaSteps instance
                 */
                this.trigger('coursepress:step-changed', this.currentStep, this );

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
                this.currentTab.siblings().removeClass('tab-active');
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
            }
        });

        // Init course edit on first load
        new EditCourse(win._coursepress.course);
    });
})();