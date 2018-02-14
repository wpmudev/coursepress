/* global CoursePress */

(function() {
    'use strict';

    CoursePress.Define( 'UnitHelp', function( $, doc, win ) {

        return CoursePress.View.extend({
            template_id: 'coursepress-unit-help-1-tpl',
            className: 'coursepress-modal unit-help',
            current: 1,
            events: {
                'click .cp-close': 'remove',
                'click .cp-btn-got-it-1': 'goToNext',
                'click .cp-btn-got-it-2': 'hideHelpTab'
            },

            initialize: function() {
                this.render();
            },

            render: function() {
                var self = this;

                this.$el.html('');
                CoursePress.View.prototype.render.apply(this);
                this.$el.appendTo('body');
                this.body = this.$('.coursepress-popup-body');
                this.menuContainer = $('.step-course-units');

                $(win).on( 'resize', function() {
                    self.fitToScreen();
                });
            },

            goToNext: function() {
                if ( this.current >= 6 ) {
                    this.remove();
                    return false;
                }

                this.$el.removeClass('unit-help' + this.current);
                this.current += 1;
                this.template_id = 'coursepress-unit-help-' + this.current + '-tpl';
                this.render();
                this.$el.addClass('unit-help' + this.current);

                this.fitToScreen();
            },

            hideHelpTab: function() {
                // Let the browser remember this step for a year!
                CoursePress.Cookie('course_unit_helptab').set(1, 86400 * 365);
                this.goToNext();
            },

            fitToScreen: function() {
                var left, top;

                if ( 3 === this.current ) {
                    left = this.menuContainer.offset().left;
                    top = this.menuContainer.offset().top;
                    this.body.css({
                        'margin-left': left + 260,
                        'margin-top': top
                    });
                } else {
                    this.body.css('');
                }
            }
        });
    });
})();