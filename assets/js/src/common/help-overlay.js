/* global CoursePress,window */

(function () {
	'use strict';

	CoursePress.Define('HelpOverlay', function ($) {
		return CoursePress.View.extend({
			topPane: false,
			rightPane: false,
			bottomPane: false,
			leftPane: false,
			targetEl: false,
			container: false,
			options: false,

			initialize: function (targetEl, container, options) {
				this.targetEl = targetEl;
				this.container = container;
				this.options = _.extend(options || {}, {
					padding: 20
				});

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
				this.scrollToTargetEl();
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
				var topPaneHeight, leftPaneWidth, targetElWidth, targetElHeight, padding = this.options.padding;

				topPaneHeight = this.targetEl.offset().top;
				leftPaneWidth = this.targetEl.offset().left;
				targetElWidth = this.targetEl.width();
				targetElHeight = this.targetEl.height();

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