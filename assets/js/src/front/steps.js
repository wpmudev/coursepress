/* global CoursePress */

(function() {
    'use strict';

    CoursePress.Define( 'Steps', function( $, doc ) {
        var Steps = {};

        Steps.toggleRetry = function( ev ) {
            var sender, answer_box, question_box;

            sender = $(ev.currentTarget);
            answer_box = sender.parents('.course-module-answer').first();
            question_box = answer_box.prev( '.course-module-step-question' );

            answer_box.slideUp();
            question_box.slideDown();
        };

        $(doc).on( 'click', '.cp-button-retry', Steps.toggleRetry );
    });
})();