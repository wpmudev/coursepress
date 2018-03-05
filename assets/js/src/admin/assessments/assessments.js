/* global CoursePress */

(function(){
    'use strict';

    CoursePress.Define( 'AssesmentsList', function($) {
        var AssesmentsList;

        AssesmentsList = CoursePress.View.extend({
            el: $( '#coursepress-assessments' ),
            events: {
                'change [name="graded_ungraded"]': 'submitForm',
                'change [name="course_id"], [name="student_progress"], [name="display"]': 'submitForm',
                'click .cp-plus-icon, .cp-minus-icon': 'unitsExpandHide',
                'click .cp-expand-collapse': 'studentExpandHide'
            },

            // Initialize.
            initialize: function() {
                this.on( 'view_rendered', this.setupUI, this );
                this.render();
            },

            // Setup UI elements.
            setupUI: function () {
                this.$('select').select2();
            },

            // Expand/hide student details.
            studentExpandHide: function ( ev ) {
                var selector = $( ev.currentTarget ),
                    tr = selector.closest( 'tr' );
                tr.find( '.cp-assessment-progress-hidden' ).toggleClass( 'inactive' );
                tr.find( '.cp-assessment-progress-expand' ).toggleClass( 'inactive' );
                tr.next( 'tr:not(.cp-assessment-main)' ).fadeToggle( 200 );
            },

            // Expand/hide questions.
            unitsExpandHide: function ( ev ) {
                var selector = $( ev.currentTarget ),
                    li = selector.closest( 'li' ),
                    table = li.find( '.cp-assessments-table-container' );
                selector.toggleClass('cp-plus-icon')
                    .toggleClass('cp-minus-icon');
                if ( table.length ) {
                    li.toggleClass('cp-assessments-units-expanded');
                    table.fadeToggle( 200 );
                }
            },

            // Submit filter form.
            submitForm: function () {
                this.$('#cp-search-form').submit();
            }
        });

        AssesmentsList = new AssesmentsList();
    });
})();
