/* global CoursePress */

(function(){
    'use strict';

    CoursePress.Define( 'Upgrade', function($) {
        var Upgrade;

        Upgrade = CoursePress.View.extend({
            el: $( '#coursepress-upgrade' ),
            courses: {},
            request: null,
            events: {
                'click #coursepress-upgrade-button': 'ajaxUpgrade',
            },

            // Initialize.
            initialize: function() {
                this.on( 'view_rendered', this.setupUI, this );
                this.request = new CoursePress.Request();
                this.request.on( 'coursepress:error_upgrade_course', this.upgradError, this );
                this.request.on( 'coursepress:success_upgrade_course', this.upgradSuccess, this );
                this.render();
            },

            // Setup UI elements.
            setupUI: function () {
                this.courses = $('#coursepress-upgrade li.course-to-upgrade');
            },

            ajaxUpgrade: function() {
                var processing = false;
                var self = this;
                _.each( this.courses, function( element ) {
                    var $element = $(element);
                    if ( processing ) {
                        return;
                    }
                    if ( ! $element.hasClass( 'status-done' ) ) {
                        self.request.set( {
                            'action' : 'upgrade_course',
                            'course_id' : $element.data('course-id' )
                        } );
                        self.request.save();
                        processing = true;
                    }
                });
            },
            upgradError: function( data ) {
                new CoursePress.PopUp({
                    type: 'error',
                    message: data.message
                });
            },
            upgradSuccess: function( data ) {
                window.console.log( 'upgradSuccess' );
                window.console.log( data );
            }
        });

        Upgrade = new Upgrade();
    });
})();
