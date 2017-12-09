/* global CoursePress, _coursepress */
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
                'click #bulk-actions .cp-btn': 'bulkAction',
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
            redirect: function() {
                // TODO
                // redirect: function( data ) {
                // TODO
                // window.location.href = data.pdf;
            },
            /**
             * Bulk actions
             */
            bulkAction: function(ev) {
                var target = $( ev.currentTarget );
                var items = $('.check-column input:checked');
                var action = $('select', target.closest('.cp-div') ).val();
                var form = target.closest('form');
                if ( '-1' === action ) {
                    window.alert( _coursepress.text.reports.no_action );
                    return;
                }
                if ( 0 === items.length ) {
                    window.alert( _coursepress.text.reports.no_items );
                    return;
                }
                var ids = [];
                items.each( function() {
                    ids.push( $(this).val() );
                });
                ids = ids.join();
                if ( 'download' === action || 'download_summary' === action ) {
                    var model = new CoursePress.Request( this.getModel() );
                    model.set( 'action', 'get_report_pdf' );
                    model.set( 'which', action );
                    model.set( 'course_id', target.data('course') );
                    model.set( 'students', ids );
                    model.set( 'download_nonce', $('#coursepress-reports-list').data('download_nonce') );
                    model.on( 'coursepress:success_get_report_pdf', this.redirect, this );
                    model.save();
                    return;
                }
                $('<input>').attr({ type: 'hidden', name: 'students', value: ids }).appendTo(form);
                $('<input>').attr({ type: 'hidden', name: 'mode', value: 'html' }).appendTo(form);
                $('<input>').attr({ type: 'hidden', name: 'action', value: action }).appendTo(form);
                form.submit();
            }
        } );
        Report = new Report();
    });
})();
