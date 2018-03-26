/* global CoursePress, _coursepress */

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
            },

            ajaxUpgrade: function() {
                var processing = false;
                var self = this;
                var courses = $('#coursepress-upgrade li.course-to-upgrade');
                $('#coursepress-upgrade-button').attr('disabled', 'disabled' );
                _.each( courses, function( element ) {
                    var $element = $(element);
                    if ( processing ) {
                        return;
                    }
                    if ( ! $element.hasClass( 'status-done' ) ) {
                        self.request.set( {
                            'action' : 'upgrade_course',
                            'course_id' : $element.data('course-id' )
                        } );
                        processing = true;
                        $('.status', $element ).html( _coursepress.text.upgrade.status.in_progress );
                        $element.addClass( 'status-in-progress' );
                        self.request.save();
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
                this.setStatus( data.course_id, _coursepress.text.upgrade.status.upgraded, 'status-done' );
                this.ajaxUpgrade();
            },

            setStatus: function( course_id, message, css_class ) {
                var el = $('#coursepress-upgrade #course-id-'+course_id );
                el.addClass( css_class );
                $('.status', el ).html( message );
            }
        });

        Upgrade = new Upgrade();
    });
})();
