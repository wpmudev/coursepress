/* global CoursePress,videojs */

(function() {
    'use strict';

    CoursePress.Define( 'Steps', function( $, doc, win ) {
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
			var mediaEl, media, totalAttemptsConsumed, retries, totalAttemptsAllowed, retriesAllowed, unlimitedAttemptsAllowed, stopIfAttemptsConsumed, stopIfAttemptsConsumedDeBounced, updateAttempts, updateAttemptsDeBounced, onTimeUpdate, showError;

			mediaEl = $(this);
			totalAttemptsConsumed = mediaEl.data('attempts') || 0;
			retries = mediaEl.data('retries') || 0;
			retriesAllowed = mediaEl.data('retriesAllowed');
			unlimitedAttemptsAllowed = retries === 0;
			totalAttemptsAllowed = retries + 1;
			media = videojs(mediaEl.get(0));
			showError = function () {
				$('.cp-error').remove();
				$('<p></p>').addClass('error cp-error').html(win._coursepress.text.attempts_consumed).prependTo('.course-module-step-template');
			};
			stopIfAttemptsConsumed = function () {
				if (retriesAllowed) {
					if (!unlimitedAttemptsAllowed && totalAttemptsConsumed >= totalAttemptsAllowed) {
						media.pause();
						showError();
					}
				}
				else if (totalAttemptsConsumed >= 1) {
					media.pause();
					showError();
				}
			};
			stopIfAttemptsConsumedDeBounced = _.debounce(stopIfAttemptsConsumed, 1000);
			updateAttempts = function (event) {
				var form, request, formValues = {};
				totalAttemptsConsumed++;
				form = $(event.target).closest('form');
				$.each(form.serializeArray(), function (i, field) {
					formValues[field.name] = field.value || '';
				});

				request = new CoursePress.Request();
				request.set(_.extend({'action': 'record_media_response'}, formValues));
				request.save();
			};
			updateAttemptsDeBounced = _.debounce(updateAttempts, 1000);
			onTimeUpdate = function (event) {
				if (parseInt(media.currentTime()) === 0) {
					stopIfAttemptsConsumedDeBounced(event);
				}

				if (parseInt(media.remainingTime()) === 0) {
					updateAttemptsDeBounced(event);
				}
			};

			media.on('play', stopIfAttemptsConsumed);
			if (media.loop()) {
				media.on('timeupdate', onTimeUpdate);
			}
			else {
				media.on('ended', updateAttempts);
			}
		});

        $(doc).on( 'click', '.cp-button-retry', Steps.toggleRetry );
    });
})();
