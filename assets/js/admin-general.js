/*! CoursePress - v3.0-beta
 * https://premium.wpmudev.org/project/coursepress-pro/
 * Copyright (c) 2017; * Licensed GPLv2+ */
var CoursePress = (function ($, doc, win) {
    var self = {};

    self.Define = function( name, callback ) {

        if ( ! self[name] )
            self[name] = callback.call(null, $, doc, win);
    };

    return self;
}(jQuery, document, window));
CoursePress.Define( 'Request', function($, doc, win) {
    return Backbone.Model.extend({
        url: win.cpVars.ajaxurl + '?action=coursepress_request',
        defaults: {
            _wpnonce: win.cpVars._wpnonce
        },

        initialize: function() {
            this.on( 'error', this.serverError, this );

            Backbone.Model.prototype.initialize.apply( this, arguments );
        },

        parse: function( response ) {
            var action = this.get( 'action' );

            if ( response.success )
                this.trigger( 'coursepress:success_' + action, response.data );
            else
                this.trigger( 'coursepress:error_' + action, response.data );
        },

        serverError: function() {
            // @todo: Show friendly error here
        }
    })
});
CoursePress.Define( 'View', function($, doc, win) {
    _.mixin({
        isTrue: function( value, selected ) {
            if ( _.isArray(selected) )
                return _.contains(selected, value);
            else if ( _.isObject(selected) )
                return selected[value] ? true : false;
            else
                return value === selected;
        },
        checked: function( value, selected ) {
            return _.isTrue( value, selected ) ? 'checked="checked"' : '';
        },
        selected: function( value, selected ) {
            return _.isTrue( value, selected ) ? 'selected="selected"' : '';
        },
        _getTemplate: function( template_id, data ) {
            var settings = {
                    evaluate:    /<#([\s\S]+?)#>/g,
                    interpolate: /\{\{\{([\s\S]+?)\}\}\}/g,
                    escape:      /\{\{([^\}]+?)\}\}(?!\})/g
                },
                tpl = _.template( $('#' + template_id ).html(), null, settings );

            return tpl(data);
        }
    });

    return Backbone.View.extend({
        template_id: false,
        model: CoursePress.Request,
        initialize: function() {
            Backbone.View.prototype.initialize.apply( this, arguments );

            this.render();
        },
        render: function() {
            if ( this.template_id ) {
                this.$el.html( _._getTemplate( this.template_id, this.model.toJSON() ) );
            }
        }
    });
});
CoursePress.Define( 'Toggle', function($) {
    return CoursePress.View.extend({
        events: {
            'change': 'toggleStatus'
        },
        render: function() {
            this.$el.hide();
            $('<span class="coursepress-toggle">YES</span>').insertAfter(this.$el);
        },
        toggleStatus: function(ev) {
            var sender = $(ev.currentTarget),
                is_checked = sender.is(':checked');

            if ( is_checked ) {
                this.trigger('toggle_active');
            } else {
                this.trigger('toggle_inactive');
            }
        }
    });
});
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