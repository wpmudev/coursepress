/* global CoursePress */

(function() {
    'use strict';

    CoursePress.Define( 'Step_INPUT-WRITTEN', function( $, doc, win ) {
        var Question;

        Question = CoursePress.View.extend({
            template_id: 'coursepress-written-tpl',
            className: 'cp-question-box',
            events: {
                'click .question-toggle-button': 'toggleQuestion',
                'change [name]': 'updateModel'
            },

            updateModel: function () {
                var title, question, placeholder_text, word_limit, order;

                title = this.$('[name="title"]').val();
                this.model.set('title', title);

                question = this.$('[name="question"]').val();
                this.model.set('question', question);

                placeholder_text = this.$('[name="placeholder_text"]').val();
                this.model.set('placeholder_text', placeholder_text);

                word_limit = this.$('[name="word_limit"]').val();
                this.model.set('word_limit', word_limit);

                order = this.$('[name="order"]').val();
                this.model.set('order', order);
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

        return CoursePress['Step_INPUT-QUIZ'].extend({
            template_id: 'coursepress-step-written',
            view: false,
            events: {
                'click .add-question': 'addQuestion',
                'change [name="meta_show_content"]': 'toggleContent',
                'click .cp-btn-trash': 'removeQuestion'
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

                questions = this.model.get('meta_questions');

                if ( questions ) {
                    _.each( questions, function( question, index ) {
                        var questionData = !!question.get ? question.toJSON() : question,
                            addedQuestion;

                        addedQuestion = this._addQuestion(questionData);
                        questions[index] = addedQuestion.model;
                    }, this );
                }
            },
            
            reorderQuestions: function() {
                var questions = this.model.get('meta_questions');

                questions.sort(function (q1, q2) {
                    q1 = !!q1.get ? q1.toJSON() : q1;
                    q2 = !!q2.get ? q2.toJSON() : q2;

                    return q1.order - q2.order;
                });
            },

            removeQuestion: function (ev) {
                var target, questions, self = this;
                var confirm = new CoursePress.PopUp({
                    type: 'warning',
                    message: win._coursepress.text.confirm.steps.question_delete
                });

                questions = this.model.get('meta_questions');

                target = this.$(ev.currentTarget).closest('.cp-question-box');
                confirm.on('coursepress:popup_ok', function () {
                    var index = self.$el.find('.cp-question-box').index(target);
                    questions.splice(index, 1);
                    target.remove();
                });
            },

            addQuestion: function() {
                var question, questions, data;

                questions = this.model.get( 'meta_questions' );

                data = {
                    type: 'written',
                    title: win._coursepress.text.untitled,
                    question: '',
                    placeholder_text: '',
                    word_limit: 0,
                    order: _.size(questions)
                };

                question = this._addQuestion(data);
                questions.push(question.model);
            },

            _addQuestion: function (model) {
                var question, self = this;

                question = new Question(model, this);
                question.$el.appendTo(this.$('.cp-questions-container'));

                this.$('.no-content-info').hide();
                this.$('.cp-questions-container').sortable({
                    axis: 'y',
                    stop: function () {
                        self.questionsReordered();
                    }
                });

                return question;
            },

            questionsReordered: function () {
                var orderInputs, newOrder;

                newOrder = 0;
                orderInputs = this.$el.find('.question-order');

                _.each(orderInputs, function (orderInput) {
                    var $orderInput = $(orderInput);
                    $orderInput.val(newOrder);
                    $orderInput.change();
                    newOrder++;
                });

                this.reorderQuestions();
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
