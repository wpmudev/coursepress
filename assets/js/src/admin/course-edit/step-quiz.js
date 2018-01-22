/* global CoursePress */

(function() {
    'use strict';

    CoursePress.Define( 'Step_INPUT-QUIZ', function($, doc, win) {
        var Question, Model, Answer;

        Answer = CoursePress.View.extend({
            template_id: 'coursepress-question-answer',
            className: 'cp-box cp-answer-box',
            question: false,
            events: {
                'click .cp-btn-trash': 'removeAnswer',
                'change [name]': 'updateModel'
            },

            initialize: function( model, question ) {
                this.model = model;
                this.question = question;
                this.render();
            },

            removeAnswer: function() {
                this.remove();
                this.question.updateModel();
            },

            updateModel: function() {
                //this.question.updateModel(ev);
            }
        });

        Model = CoursePress.Request.extend({
            defaults: {
                title: 'Untitled',
                question: '',
                order: 0
            },

	        initialize: function () {
                var options = this.get('options') || {};

                options = _.extend({
	                answers: [
		                win._coursepress.text.step.answer_a,
		                win._coursepress.text.step.answer_b,
		                win._coursepress.text.step.answer_c
	                ],
	                checked: []
                }, options);

                this.set('options', options);
	        }
        });

        Question = CoursePress.View.extend({
            template_id: 'coursepress-question-tpl',
            className: 'cp-question-box',
            events: {
                'click .cp-btn-active': 'addAnswer',
                'click .question-toggle-button': 'toggleQuestion',
                'change [name]': 'updateModel'
            },
            quizView: false,

            initialize: function( model, quizView ) {
                this.model = new Model(!!model.get ? model.toJSON() : model);
                this.quizView = quizView;
                this.on( 'view_rendered', this.setUI, this );
                this.render();
            },

            setUI: function() {
                var options, q, type;
                options = this.model.get('options');
                type = this.model.get('type');

	            if (!this.$el.attr('id')) {
		            this.$el.attr('id', this.cid);
	            }

                if ( options.answers ) {
                    _.each( options.answers, function( answer, index ) {
                        var checked;
                        checked = options.checked && !!options.checked[index];
                        q = {
                            type: type,
                            answer: answer,
                            index: index,
                            checked: checked,
                            cid: this.model.cid
                        };
                        q = new Answer(q, this);
                        q.$el.appendTo(this.$('.question-answers'));
                    }, this );
                }
            },

            addAnswer: function() {
                var answer, options;
                options = {
                    question: '',
                    type: this.model.get('type'),
                    cid: this.model.cid,
                    checked: null,
                    answer: '',
                };
                answer = new Answer(options, this );
                answer.$el.appendTo(this.$('.question-answers'));

                this.updateModel();
            },

            toggleQuestion: function() {
                var is_open = this.$el.is('.open');

                if ( is_open ) {
                    this.$el.removeClass('open');
                } else {
                    this.$el.addClass('open');
                }
            },

            updateModel: function() {
                var cid, title, question, order, options, answers, the_answers, checked, the_checked;

                title = this.$('[name="title"]').val();
                this.model.set('title', title);

                question = this.$('[name="question"]').val();
                this.model.set('question', question);

                order = this.$('[name="order"]').val();
                this.model.set('order', order);

                options = this.model.get('options');

                if ( ! options ) {
                    options = {};
                }

                answers = this.$('[name="answers"]');
                the_answers = [];

                _.each(answers, function(answer) {
                    answer = $(answer);
                    the_answers.push(answer.val());
                }, this );

                checked = this.$('input.coursepress-question-answer-checked');
                the_checked = [];

                _.each(checked, function(check) {
                    check = $(check);

                    if ( check.is(':checked') ) {
                        the_checked.push(1);
                    } else {
                        the_checked.push(false);
                    }
                }, this );

                options.answers = the_answers;
                options.checked = the_checked;

                this.model.set('options', options);

                cid = this.cid;
                this.quizView.questionsModel[cid] = this.model;
                this.quizView.updateQuestions();
            }
        });

       return CoursePress.View.extend({
           template_id: 'coursepress-step-quiz',
           questions: {},
           questionsModel: {},
           events: {
                'click .cp-question-header .cp-btn-trash': 'deleteQuestion',
               'change .cp-question-type': 'addQuestion',
               'change [name="meta_show_content"]': 'toggleContent'
           },

           initialize: function( model, stepView ) {
               this.stepView = stepView;
               this.on( 'view_rendered', this.setUI, this );
               this.questions = {};
               this.questionsModel = {};
               this.render();
           },

           setUI: function() {
               var self;

               self = this;
               this.description = this.$('.cp-step-description');

               this.visualEditor({
                   container: this.description,
                   content: this.model.get('post_content'),
                   callback: function( content ) {
                       self.model.set('post_content', content);
                   }
               });

               this.$('select').select2();

               if ( this.model.get('questions') ) {
                   _.each( this.model.get('questions'), function( question ) {
                       this._addQuestion(question);
                   }, this );
                   this.$('.no-content-info').hide();
                   this.$('.cp-questions-container').sortable({
                       axis: 'y',
                       stop: function () {
                           self.reOrderQuestions();
                       }
                   });
               }
           },

           reOrderQuestions: function () {
               var orderInputs, newOrder;

               newOrder = 0;
               orderInputs = this.$el.find('.question-order');

               _.each(orderInputs, function (orderInput) {
				   var $orderInput = $(orderInput);
                   $orderInput.val(newOrder);
                   $orderInput.change();
                   newOrder++;
               });
           },

           deleteQuestion: function( ev ) {
               var target;
               var confirm = new CoursePress.PopUp({
                   type: 'warning',
                   message: win._coursepress.text.confirm.steps.question_delete
               });
               target = this.$(ev.currentTarget).closest( '.cp-question-box' );
               this.target = target;
               this.cid = target.attr('id');
               confirm.on( 'coursepress:popup_ok', this._deleteQuestion, this );
           },

           _deleteQuestion: function() {
               delete this.questionsModel[this.cid];
               this.updateQuestions();
               this.target.detach();
               delete this.cid;
               delete this.target;
           },

           addQuestion: function(ev) {
               var sender, type, data;

               sender = this.$(ev.currentTarget);
               type = sender.val();

               if ( ! type ) {
                   return;
               }

			   var size = _.size(this.questions);
               data = {
                   type: type,
                   order: size
               };
               this._addQuestion(data);

               this.$('.no-content-info').hide();
               this.$('.cp-questions-container').sortable();

               // Reset the dropdown
               sender.find('option:first-child').prop('selected', true);
               sender.trigger('change');
           },

           _addQuestion: function( model ) {
               var question, cid;

               question = new Question(model, this);
               this.addQuestionMarkup(question.$el);

               cid = question.cid;
               this.questions[cid] = question;
               this.questionsModel[cid] = question.model;
               this.model.questions = this.questionsModel;
	           this.updateQuestions();
           },

           addQuestionMarkup: function (newQuestion) {
               var orderInputs, orderArray = [], newOrder;

               newOrder = newQuestion.find('.question-order').val();
               orderArray.push(newOrder);

               orderInputs = this.$el.find('.question-order');
               orderInputs.each(function (index, orderInput) {
                   orderArray.push(
                       $(orderInput).val()
                   );
               });

               var newQuestionIndex = _.indexOf(_.sortBy(orderArray), newOrder);
               if (newQuestionIndex === 0) {
                   newQuestion.prependTo(this.$('.cp-questions-container'));
               }
               else {
                   newQuestion.insertAfter(this.$('.cp-question-box:nth-child(' + newQuestionIndex + ')'));
               }
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
           },

           updateModel: function(ev) {
               this.stepView.updateModel(ev);
           },

           updateQuestions: function() {
               this.model.meta_questions = this.model.questions = this.questionsModel;
               //this.stepView.set('questions', this.model.questions);
               //this.stepView.set('meta_questions', this.model.meta_questions);
               //this.stepView.trigger('coursepress:model_updated', this.stepView.model, this.stepView );
               this.model.set('questions', this.questionsModel);
               this.model.set('meta_questions', this.questionsModel);
               this.stepView.model.set('questions', this.model.get('questions'));
               this.stepView.model.set('meta_questions', this.model.get('questions'));
               this.stepView.trigger('coursepress:model_updated', this.stepView.model, this.stepView );

           }
       });
    });
})();
