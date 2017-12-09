/* global CoursePress */

(function() {
    'use strict';

    CoursePress.Define( 'Step_INPUT-WRITTEN', function( $, doc, win ) {
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
            view: false,
            events: {
                'click .add-question': 'addQuestion',
                'change [name="meta_show_content"]': 'toggleContent'
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

                questions = this.model.get('questions');
                if ( questions ) {
                    _.each( questions, function( question ) {
                        this._addQuestion(question);
                    }, this );
                    this.$('.no-content-info').hide();
                    this.$('.cp-questions-container').sortable();
                }
            },

            addQuestion: function() {
                var question, questions, data;

                questions = this.model.get( 'questions' );

                if ( ! questions ) {
                    questions = [];
                }
                data = {
                    type: 'written',
                    title: win._coursepress.text.untitled,
                    question: '',
                    placeholder_text: '',
                    word_limit: 0
                };

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
