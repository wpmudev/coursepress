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

              //  this.$el.on( 'circle-animation-progress', this.animationProgress, this );
            },

            animationProgress: function( e, v ) {
                var obj = $( e.currentTarget ).data( 'circle-progress' ),
                    ctx = obj.ctx,
                    s = obj.size,
                    sv = (100 * v).toFixed(),
                    ov = (100 * obj.value ).toFixed();
                sv = 100 - sv;

                if ( sv < ov ) {
                    sv = ov;
                }
                ctx.save();

                if ( this.data.knobTextShow ) {
                    ctx.font = s / this.data.knobTextDenominator + 'px sans-serif';
                    ctx.textAlign = this.data.knobTextAlign;
                    ctx.textBaseline = 'middle';
                    ctx.fillStyle = this.data.knobTextColor;
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
    });
})();