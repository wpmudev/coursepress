/** global WPMUCoursePress **/
WPMUCoursePress.Define( 'EditUnits', function( $, doc, win, wpmu ) {
	var Unit = wpmu.View.extend({
		template_id: 'coursepress-unit-edit-tpl'
	});

	return new wpmu.View({
		template_id: 'coursepress-unit-list-tpl',
	});
};