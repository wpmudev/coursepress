/* global CoursePress */
(function() {
    'use strict';
    /**
     * Reports pages including sub pages.
     */
    CoursePress.Define( 'Report', function( $ ) {
        var Report;
        Report = CoursePress.View.extend( {
            el: $( '#coursepress-reports-list' ),
            events: {
                'click .column-download a': 'getPdf',
            },
            initialize: function( model ) {
                this.model = model;
            },
            getModel: function() {
                return this.model;
            },
            /**
             * get PDF
             */
            getPdf: function(ev) {
                var target = $( ev.currentTarget );
                var model = new CoursePress.Request( this.getModel() );
                model.set( 'action', 'get_report_pdf' );
                model.set( 'course_id', target.data('course') );
                model.set( 'student_id', target.data('student') );
                model.set( 'download_nonce', $('#coursepress-reports-list').data('download_nonce') );
                model.on( 'coursepress:success_get_report_pdf', this.redirect, this );
                model.save();
                return false;
            },
            redirect: function( data ) {
                window.location.href = data.pdf;
            }
        } );
        Report = new Report();
    });
})();
