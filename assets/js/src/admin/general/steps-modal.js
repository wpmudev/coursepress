/* global CoursePress */

CoursePress.Define( 'StepsModal', function($) {
   return CoursePress.View.extend({
       steps: [],
       events: {
           'click .step': 'toggleContent',
           'click .step-back': 'getPreviousStep',
           'click .step-next': 'getNextStep',
           'click .step-cancel': 'returnToMainPage',
           'click .step-icon-bars': 'toggleStepList'
       },
       render: function() {
           // Get all steps
           _.each( this.$('.step-list li'), this.getSteps, this );

           this.firstStep = _.first(this.steps);
           this.lastStep = _.last(this.steps);

           // If current step is not set, set the first step
           if ( ! this.currentStep ) {
               this.setCurrentStep(_.first(this.steps));
           }

           // Get the buttons
           this.prevButton = this.$('.step-back');
           this.nextButton = this.$('.step-next');
           this.current = this.getCurrentStep();
           this.stepListContainer = this.$('.step-list');
           this.current.addClass('active');
           this.currentTab = this.getCurrentTab();
           this.currentTab.addClass('tab-active');

           // Hook into step change event
           this.on('coursepress:step-changed', this.stepChanged, this);
       },
       getSteps: function(step) {
           this.steps.push($(step).data('step'));
       },
       getCurrentStep: function() {
           return this.$('[data-step="' + this.currentStep + '"]');
       },
       setCurrentStep: function(step) {
           this.currentStep = step;

           /**
            * Trigger whenever a step is change.
            *
            * @param string step - Current selected step
            * @param object $this
            */
           this.trigger('coursepress:step-changed', step, this );

           /**
            * Trigger for per step event hook
            */
           this.trigger( 'coursepress:step-' + step + '-changed', step, this);
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
          var stepIndex = _.indexOf(this.steps, this.currentStep),
              maxStep = this.steps.length - 1;

          if ( stepIndex < maxStep ) {
              stepIndex += 1;
              this.setCurrentStep(this.steps[stepIndex]);

              /**
               * Trigger whenever next step is activated
               *
               * @param string step
               * @param object instance
               */
              this.trigger('coursepress:next-step-activated', this.steps[stepIndex], this);

              if ( this.currentStep === this.lastStep ) {
                  // Trigger last step event
                  this.trigger('coursepress:lastStep', this.currentStep, this);
              }
          }
       },
       returnToMainPage: function() {},

       toggleStepList: function() {
           this.stepListContainer.toggleClass('open', '');
       }
   });
});