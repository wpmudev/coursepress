/* global CoursePress */

(function(){
    'use strict';

    CoursePress.Define( 'AssesmentsList', function($) {
        var AssesmentsList;

        AssesmentsList = CoursePress.View.extend({
            el: $( '#coursepress-assessments' ),
            events: {
                'change [name="graded_ungraded"]': 'changeGradedUngraded',
                'change [name="course_id"], [name="student_progress"]': 'submitForm',
                'click .cp-plus-icon, .cp-minus-icon': 'unitsExpandHide',
                'click .cp-expand-collapse': 'studentExpandHide',
            },

            // FIlter by graded or ungraded.
            changeGradedUngraded: function( ev ) {

                var type = $( ev.currentTarget ),
                    value = type.val(),
                    tbody = this.$( '#cp-assessments-table tbody' );

                type.parents('li').siblings().removeClass('active');
                type.parents('li').addClass('active');

                if ( value === 'all' ) {
                    tbody.find( 'tr:not(.cp-assessments-details)' ).removeClass('inactive');
                } else {
                    tbody.find( 'tr:not(.cp-assessments-details)' ).addClass('inactive');
                    tbody.find( '.cp-' + value ).removeClass('inactive');
                }
            },

            // Expand/hide student details.
            studentExpandHide: function ( ev ) {

                var selector = $( ev.currentTarget ),
                    tr = selector.closest( 'tr' );

                tr.find( '.cp-assessment-progress-hidden' ).toggleClass( 'inactive' );
                tr.find( '.cp-assessment-progress-expand' ).toggleClass( 'inactive' );
                tr.next( 'tr' ).fadeToggle( 200 );
            },

            // Expand/hide questions.
            unitsExpandHide: function ( ev ) {

                var selector = $( ev.currentTarget );

                selector.toggleClass('cp-plus-icon')
                    .toggleClass('cp-minus-icon');
                selector.closest( 'li' ).toggleClass('cp-expanded')
                    .find( '.cp-assessments-table-container' ).fadeToggle( 200 );
            },

            // Submit filter form.
            submitForm: function () {

                this.$('#cp-search-form').submit();
            }
        });

        AssesmentsList = new AssesmentsList();
    });
})();