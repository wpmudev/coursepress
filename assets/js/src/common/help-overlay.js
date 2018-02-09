/* global CoursePress,ResizeSensor */

(function () {
	'use strict';

	CoursePress.Define('HelpOverlay', function ($) {
		return CoursePress.View.extend({
			template_id: 'coursepress-help-overlay-tpl',
			className: 'coursepress-modal cp-help-overlay-content',
			topPane: false,
			rightPane: false,
			bottomPane: false,
			leftPane: false,
			targetEl: false,
			container: false,
			options: false,
			events: {
                'click .btn-ok': 'Ok',
				'click .cp-modal-close': 'dismiss'
            },

			initialize: function (targetEl, model, options) {
				this.targetEl = targetEl;
				this.model = model;
				this.options = _.extend(options || {}, {
					padding: 15
				});
				this.container = $('body');

				var readjust = _.bind(this.readjust, this);
				new ResizeSensor(this.container, readjust);
				new ResizeSensor(this.targetEl, readjust);

				this.render();
			},

			readjust: function () {
				this.setPaneDimensions();
				this.displayPopup();
			},

			render: function () {
				this.removePanes();
				this.createPanes();
				this.setPaneDimensions();
				this.displayPopup();
				this.scrollToTargetEl();
			},

			displayPopup: function () {
				var insideRightPane, popupBody;

				this.$el.html('');
				CoursePress.View.prototype.render.apply(this);
				insideRightPane = this.rightPaneHasRoom();
				this.$el.appendTo(insideRightPane ? this.rightPane : this.bottomPane);

				popupBody = this.$el.find('.coursepress-popup-body');
				popupBody.addClass(insideRightPane ? 'position-right' : 'position-bottom');
				if (insideRightPane) {
					popupBody.css('margin-top', this.topPane.height());
				}
			},

			rightPaneHasRoom: function () {
				var rightPaneWidth, bottomPaneWidth;

				rightPaneWidth = this.rightPane.width();
				bottomPaneWidth = this.bottomPane.width();

				return rightPaneWidth > bottomPaneWidth;
			},

			removePanes: function () {
				this.container.removeClass('cp-help-overlay-body');

				this.container.find('.cp-help-overlay-top').remove();
				this.container.find('.cp-help-overlay-right').remove();
				this.container.find('.cp-help-overlay-bottom').remove();
				this.container.find('.cp-help-overlay-left').remove();
			},

			createPanes: function () {
				this.container.addClass('cp-help-overlay-body');

				this.topPane = $('<div></div>').addClass('cp-help-overlay-top').appendTo(this.container);
				this.rightPane = $('<div></div>').addClass('cp-help-overlay-right').appendTo(this.container);
				this.bottomPane = $('<div></div>').addClass('cp-help-overlay-bottom').appendTo(this.container);
				this.leftPane = $('<div></div>').addClass('cp-help-overlay-left').appendTo(this.container);
			},

			setPaneDimensions: function () {
				var topPaneHeight, leftPaneWidth, targetElWidth, targetElHeight, containerDimensions, padding = this.options.padding;

				topPaneHeight = this.targetEl.offset().top;
				leftPaneWidth = this.targetEl.offset().left;
				targetElWidth = this.targetEl.outerWidth();
				targetElHeight = this.targetEl.outerHeight();

				// Adjust top pane height for admin bar
				topPaneHeight = topPaneHeight - $('#wpadminbar').height();
				containerDimensions = this.container.get(0).getBoundingClientRect();

				this.topPane.css('width', targetElWidth + padding + padding);
				this.topPane.css('left', leftPaneWidth - padding);
				this.topPane.css('height', topPaneHeight - padding);

				this.rightPane.css('width', containerDimensions.width - (leftPaneWidth + targetElWidth) - padding);

				this.bottomPane.css('width', targetElWidth + padding + padding);
				this.bottomPane.css('left', leftPaneWidth - padding);
				this.bottomPane.css('height', containerDimensions.height - (topPaneHeight + targetElHeight) - padding);

				this.leftPane.css('width', leftPaneWidth - padding);
			},

			scrollToTargetEl: function () {
				$('html, body').animate({
					scrollTop: this.targetEl.offset().top - 100
				}, 500);
			},

			remove: function () {
				this.removePanes();
				ResizeSensor.detach(this.container);
				ResizeSensor.detach(this.targetEl);
				Backbone.View.prototype.remove.apply(this, arguments);
			},

            Ok: function() {
	            this.remove();
                /**
                 * Trigger whenever OK button is clicked.
                 */
                this.trigger( 'coursepress:popup_ok', this );
            },

			dismiss: function () {
				var request;

				this.remove();

				request = new CoursePress.Request();
				request.set({'action': 'dismiss_unit_help'});
				request.save();
			}
		});
	});
})();