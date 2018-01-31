/* global CoursePress,window */

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

			initialize: function (targetEl, model, options) {
				this.targetEl = targetEl;
				this.model = model;
				this.options = _.extend(options || {}, {
					padding: 15
				});
				this.container = $('body');

				$(window).off('resize').on('resize', _.bind(this.readjust, this));

				this.render();
			},

			readjust: function () {
				this.render();
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

				this.topPane.remove();
				this.rightPane.remove();
				this.bottomPane.remove();
				this.leftPane.remove();
			},

			createPanes: function () {
				this.container.addClass('cp-help-overlay-body');

				this.topPane = $('<div></div>').addClass('cp-help-overlay-top').appendTo(this.container);
				this.rightPane = $('<div></div>').addClass('cp-help-overlay-right').appendTo(this.container);
				this.bottomPane = $('<div></div>').addClass('cp-help-overlay-bottom').appendTo(this.container);
				this.leftPane = $('<div></div>').addClass('cp-help-overlay-left').appendTo(this.container);
			},

			setPaneDimensions: function () {
				var topPaneHeight, leftPaneWidth, targetElWidth, targetElHeight, padding = this.options.padding;

				topPaneHeight = this.targetEl.offset().top;
				leftPaneWidth = this.targetEl.offset().left;
				targetElWidth = this.targetEl.outerWidth();
				targetElHeight = this.targetEl.outerHeight();

				// Adjust top pane height for admin bar
				topPaneHeight = topPaneHeight - $('#wpadminbar').height();

				this.topPane.css('width', targetElWidth + padding + padding);
				this.topPane.css('left', leftPaneWidth - padding);
				this.topPane.css('height', topPaneHeight - padding);

				this.rightPane.css('width', this.container.width() - (leftPaneWidth + targetElWidth) - padding);

				this.bottomPane.css('width', targetElWidth + padding + padding);
				this.bottomPane.css('left', leftPaneWidth - padding);
				this.bottomPane.css('height', this.container.height() - (topPaneHeight + targetElHeight) - padding);

				this.leftPane.css('width', leftPaneWidth - padding);
			},

			scrollToTargetEl: function () {
				$('html, body').animate({
					scrollTop: this.targetEl.offset().top - 100
				}, 500);
			},

			remove: function () {
				this.removePanes();
				$(window).off('resize');
				Backbone.View.prototype.remove.apply(this, arguments);
			}
		});
	});
})();