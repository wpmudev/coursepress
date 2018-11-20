/* global CoursePress */

(function() {
    'use strict';

    CoursePress.Define( 'CourseOverview', function( $ ) {
        var Progress;

        Progress = CoursePress.View.extend({
            render: function() {
                var data = _.extend({
                    animation: {duration: 1200}
                }, this.$el.data() );

                this.$el.circleProgress({
                    fill: {
                        color: data.fillColor
                    },
                    emptyFill: data.emptyFill,
                    animation: data.animation
                });

                this.data = data;
                this.$el.on( 'circle-animation-progress', this.animationProgress );
            },

            animationProgress: function( e, v ) {
                var obj = $(this).data( 'circle-progress' ),
                    ctx = obj.ctx,
                    s = obj.size,
                    sv = (100 * v).toFixed(),
                    ov = (100 * obj.value ).toFixed();
                sv = 100 - sv;

                if ( sv < ov ) {
                    sv = ov;
                }
                ctx.save();

                if ( obj.knobTextShow ) {
                    ctx.font = s / obj.knobTextDenominator + 'px sans-serif';
                    ctx.textAlign = obj.knobTextAlign;
                    ctx.textBaseline = 'middle';
                    ctx.fillStyle = obj.knobTextColor;
                    ctx.fillText( sv + '%', s / 2 + s / 80, s / 2 );
                }

                ctx.restore();
            }
        });

        $('.course-progress-disc').each(function() {
            var UnitProgress = Progress.extend({
                el: this
            });
            UnitProgress = new UnitProgress();
        });

        /**
         * Show full notification
         */
        $('.cp-notification-more-link' ).on( 'click', function() {
            $('.cp-screen', $(this).closest('.notification-content') ).removeClass( 'cp-screen' );
            $(this).detach();
            return false;
        });
    });


    /**
     * submenu toggle
     */

    CoursePress.Define( 'CourseSubmenuToggle', function( $ ) {
        var submenu = $('.course-submenu-toggle' );
        if ( submenu.length ) {
            var submenuParent = submenu.parent();
            $('body').on( 'click', '.course-submenu-toggle', function() {
                $( '.course-item-clone', submenuParent ).detach();
                submenuParent.toggleClass( 'toggled-on' );
                if ( submenuParent.hasClass( 'toggled-on' ) ) {
                    var item = $( '.current-menu-item', submenuParent );
                    $(this).html( $(this).data('toggle-on' ) );
                    $( '.submenu', submenuParent ).prepend( '<li class="course-item-clone ' + item.attr('class') + '">'+item.html() + '</li>');
                } else {
                    $(this).html( $(this).data('toggle-off' ) );
                }
            });
        }
    });

})();
