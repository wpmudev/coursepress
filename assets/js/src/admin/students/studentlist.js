/* global CoursePress */

(function(){
    'use strict';

    CoursePress.Define( 'StudentList', function($) {
        var StudentList;

        StudentList = CoursePress.View.extend({
            el: $('#coursepress-students'),
            events: {
                'click #cp-search-clear': 'clearSearch'
            },

            initialize: function() {
                this.on( 'view_rendered', this.setUI, this );
                this.render();
            },

            setUI: function() {
                this.$('select').select2();
            },

            /**
             * Clear search form and submit.
             */
            clearSearch: function() {
                // Removing name will exclude this field from form values.
                this.$('input[name="s"]','#cp-search-form').removeAttr('name');
                this.$('#cp-search-form').submit();
            }
        });

        StudentList = new StudentList();
    });
})();