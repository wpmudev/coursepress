/* global CoursePress,videojs */

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

        $('.video-js').each(function () {
            var mediaEl, media, attempts, allowedAttempts;

            mediaEl = $(this);
            attempts = mediaEl.data('attempts') || 0;
            allowedAttempts = mediaEl.data('allowedAttempts');
            media = videojs(mediaEl.get(0));
            media.on('play', function () {
                if (attempts >= allowedAttempts) {
                    media.pause();
                }
            });

            media.on('ended', function (event) {
                var form, request, formValues = {};

                attempts++;
                form = $(event.target).closest('form');
                $.each(form.serializeArray(), function (i, field) {
                    formValues[field.name] = field.value || '';
                });

                request = new CoursePress.Request();
                request.set(_.extend({'action': 'record_media_response'}, formValues));
                request.save();
            });
        });

        $(doc).on( 'click', '.cp-button-retry', Steps.toggleRetry );
    });
})();