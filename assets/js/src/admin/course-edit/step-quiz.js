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
                this.question = question;
                this.render();
            },

            removeAnswer: function() {
                this.remove();
            }
        });

        Model = CoursePress.Request.extend({
            defaults: {
                title: 'Untitled',
                questions: []
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
                var options, q, type;

                options = this.model.get('options');
                type = this.model.get('type');

                if ( options.answers ) {
                    _.each( options.answers, function( answer, index ) {
                        var checked;
                        checked = options.checked && !!options.checked[index];

                        q = {
                            type: this.model.get('type'),
                            answer: answer,
                            index: index,
                            checked: checked,
                            cid: this.model.cid
                        };
                        q = new Answer(q);
                        q.$el.appendTo(this.$('.question-answers'));
                    }, this );
                }
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
               var self = this, questions;

               this.description = this.$('.cp-step-description');
               this.visualEditor({
                   container: this.description,
                   content: this.model.get( 'post_content' ),
                   callback: function( content ) {
                       self.model.set( 'post_content', content );
                   }
               });

               this.$('select').select2();

               questions = this.model.get('questions');

               if ( questions ) {
                   _.each( questions, function( question ) {
                       this._addQuestion(question);
                   }, this );
                   this.$('.no-content-info').hide();
                   this.$('.cp-questions-container').sortable();
               }
           },

           addQuestion: function(ev) {
               var sender, type, question, questions, data;

               sender = this.$(ev.currentTarget);
               type = sender.val();
               questions = this.model.get('questions');

               if ( ! questions ) {
                   questions = [];
               }

               if ( ! type ) {
                   return;
               }

               data = {type: type};
               question = this._addQuestion(data);
               questions.push(question);

               this.$('.no-content-info').hide();
               this.$('.cp-questions-container').sortable();
           },

           _addQuestion: function( model ) {
               var question;

               question = new Question(model, this);
               question.$el.appendTo(this.$('.cp-questions-container'));

               return question;
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