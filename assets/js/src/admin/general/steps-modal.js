/* global CoursePress */

CoursePress.Define( 'StepsModal', function($) {
   return CoursePress.View.extend({
       steps: [],
       events: {
           'click .step': 'toggleContent',
           'click .step-back': 'getPreviousStep',
           'click .step-next': 'getNextStep',
           'click .step-cancel': 'returnToMainPage'
       },
       render: function() {
           // Get all steps
           _.each( this.$('.step-list li'), this.getSteps, this );

           // If current step is not set, set the first step
           if ( ! this.currentStep ) {
               this.currentStep = _.first(this.steps);
           }

           this.firstStep = _.first(this.steps);
           this.lastStep = _.last(this.steps);

           // Get the buttons
           this.prevButton = this.$('.step-back');
           this.nextButton = this.$('.step-next');
       },
       getSteps: function(step) {
           this.steps.push($(step).data('step'));
       },
       toggleContent: function(ev) {
           var sender = $(ev.currentTarget),
               step = sender.data('step');

           // Set current open step
           this.currentStep = step;
       },
       getPreviousStep: function() {
          // var sender = $(ev.currentTarget),
               var step = _.indexOf(this.steps, this.currentStep);

           window.alert(step);

       },
       getNextStep: function() {
          // var sender = $(ev.currentTarget);

           if ( this.currentStep !== this.firstStep ) {
               this.prevButton.show();
           }
       },
       returnToMainPage: function() {
       }
   });
});