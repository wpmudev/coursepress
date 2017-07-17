/* global CoursePress */

(function() {
    'use strict';

    CoursePress.Define( 'Step_INPUT-QUIZ', function() {
        var Question, Model, Answer;

        Answer = CoursePress.View.extend({
            template_id: 'coursepress-question-answer',
            className: 'cp-box cp-answer-box',
            question: false,
            events: {
                'click .cp-btn-trash': 'removeAnswer'
            },
            initialize: function( model, question ) {
                this.model = model;
                window.console.log(model);
                this.question = question;
                this.render();
            },
            removeAnswer: function() {
                this.remove();
            }
        });

        Model = CoursePress.Request.extend({
            defaults: {
                type: 'checkbox',
                title: 'Untitled',
                questions: [
                    'Question 1',
                    'Question 2',
                    'Question 3'
                ]
            }
        });

        Question = CoursePress.View.extend({
            template_id: 'coursepress-question-tpl',
            className: 'cp-question-box',
            events: {
                'click .cp-btn-active': 'addAnswer',
                'click .question-toggle-button': 'toggleQuestion'
            },
            quizView: false,
            initialize: function( model, quizView ) {
                this.model = new Model(model);
                this.quizView = quizView;
                this.on( 'view_rendered', this.setUI, this );
                this.render();
            },
            setUI: function() {
                var questions;

                questions = this.model.get('questions');

                _.each( questions, function( question ) {
                    question = {question: question};
                    var q = new Answer(question, this);
                    q.$el.appendTo( this.$('.question-answers') );
                }, this );
            },
            addAnswer: function() {
                var answer, options;

                options = {
                    question: ''
                };

                answer = new Answer(options, this );
                answer.$el.appendTo(this.$('.question-answers'));
            },
            toggleQuestion: function() {
                var is_open = this.$el.is('.open');

                if ( is_open ) {
                    this.$el.removeClass('open');
                } else {
                    this.$el.addClass('open');
                }
            }
        });

       return CoursePress.View.extend({
           template_id: 'coursepress-step-quiz',
           events: {
               'change .cp-question-type': 'addQuestion',
               'change [name]': 'updateModel',
               'change [name="meta_show_content"]': 'toggleContent'
           },
           initialize: function( model ) {
               this.model = model;
               this.on( 'view_rendered', this.setUI, this );
               this.render();
           },
           setUI: function() {
               var self = this;

               this.description = this.$('.cp-step-description');
               this.visualEditor({
                   container: this.description,
                   content: this.model.get( 'post_content' ),
                   callback: function( content ) {
                       self.model.set( 'post_content', content );
                   }
               });

               this.$('select').select2();
           },
           addQuestion: function(ev) {
               var sender, type, question, questions;

               sender = this.$(ev.currentTarget);
               type = sender.val();
               questions = this.model.get( 'questions' );

               if ( ! questions ) {
                   questions = [];
               }

               if ( ! type ) {
                   return;
               }

               question = new Question({type: type}, this);
               question.$el.appendTo(this.$('.cp-questions-container'));
               questions.push(question);
               this.$('.no-content-info').hide();

               this.$('.cp-questions-container').sortable();
           },
           toggleContent: function(ev) {
               var sender = this.$(ev.currentTarget),
                   is_checked = sender.is(':checked'),
                   content = this.$('.cp-step-description');

               if ( is_checked ) {
                   content.slideDown();
               } else {
                   content.slideUp();
               }
           }
       });
    });
})();