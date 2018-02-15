/* global CoursePress */

(function(){
    'use strict';

    CoursePress.Define( 'Upgrade', function($) {
        var Upgrade;

        Upgrade = CoursePress.View.extend({
            el: $( '#coursepress-upgrade' ),
            events: {
            },

            // Initialize.
            initialize: function() {
                this.on( 'view_rendered', this.setupUI, this );
                this.render();
            },

            // Setup UI elements.
            setupUI: function () {
            }
        });

        Upgrade = new Upgrade();
    });
})();
