/* global CoursePress */

(function() {
    'use strict';

    CoursePress.Define( 'Step_INPUT-WRITTEN', function() {
        var Question;

        Question = CoursePress.View.extend({
            template_id: 'coursepress-written-tpl',
            className: 'cp-question-box',
            events: {
                'click .question-toggle-button': 'toggleQuestion'
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
            events: {
                'click .add-question': 'addQuestion',
                'change [name="meta_show_content"]': 'toggleContent'
            },
            addQuestion: function() {
                var question, questions, data;

                questions = this.model.get( 'questions' );

                if ( ! questions ) {
                    questions = [];
                }
                data = {
                    type: 'written',
                    title: 'Untitled',
                    question: '',
                    meta_placeholder_text: '',
                    meta_word_limit: 0
                };

                question = new Question(data, this);
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