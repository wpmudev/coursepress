/* global CoursePress */

(function(){
    'use strict';

    CoursePress.Define( 'StudentList', function($, doc, win) {
        var StudentList;

        StudentList = CoursePress.View.extend({
            el: $('#coursepress-students'),
            events: {
                'click #cp-search-clear': 'clearSearch',
                'click #bulk-actions .cp-btn': 'bulkAction',
            },
            // Initializing functions.
            initialize: function() {
                this.on( 'view_rendered', this.setUI, this );
                this.render();
            },
            // Setup UI.
            setUI: function() {
                this.$('select').select2();
            },
            // Clear search form and submit.
            clearSearch: function() {
                // Removing name will exclude this field from form values.
                this.$('input[name="s"]','#cp-search-form').removeAttr('name');
                this.$('#cp-search-form').submit();
            },
            // Process bulk actions.
            bulkAction: function() {
                var items = $('.check-column-value input:checked');
                var action = $('#bulk-action-selector-top').val();
                // Process withdraw action.
                if ( 'withdraw' === action ) {
                    var ids = [];
                    items.each(function () {
                        ids.push($(this).val());
                    });
                    var request = new CoursePress.Request();
                    request.set('action', 'withdraw_students_from_all');
                    request.set('students', ids);
                    request.on('coursepress:success_withdraw_students_from_all', this.reloadStudents, this);
                    request.save();
                }
            },
            // Reload the students list page.
            reloadStudents: function() {
                win.location = win.self.location;
            },
        });

        StudentList = new StudentList();
    });
})();