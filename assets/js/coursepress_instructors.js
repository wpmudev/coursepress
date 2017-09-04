/*! CoursePress - v3.0.0
 * https://premium.wpmudev.org/project/coursepress-pro/
 * Copyright (c) 2017; * Licensed GPLv2+ */
(function( $ ) {
	'use strict';

	$( document ).ready( function() {
		$(document).on( 'change', '#select_course_id', function() {
			$(this).closest('form').submit();
		})
		.on ( 'click', '#cp-search-clear', function() {
			var s = $('[name=s]', $(this).parent());
			if ( '' !== s.val() ) {
				s.val('');
				$(this).closest('form').submit();
			}
		});
	});
})( jQuery );

(function() {
    'use strict';

    CoursePress.Define( 'Instructors', function($) {
        var Instructors;

       Instructors =  CoursePress.View.extend({
           el: $('#coursepress-instructors'),
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

       Instructors = new Instructors();
    });
})();