<?php

class CoursePress_View_Front_Calendar {

	public static function init() {
		add_action( 'wp_ajax_refresh_course_calendar', array( __CLASS__, 'refresh_course_calendar' ) );
		add_action( 'wp_ajax_nopriv_refresh_course_calendar', array( __CLASS__, 'refresh_course_calendar' ) );
	}

	public static function refresh_course_calendar() {
		$ajax_response = array();
		$ajax_status   = 1; //success

		if ( ! empty( $_POST['date'] ) && ! empty( $_POST['course_id'] ) ) {

			$date = getdate( strtotime( str_replace( '-', '/', $_POST['date'] ) ) );
			$pre  = ! empty( $_POST['pre_text'] ) ? $_POST['pre_text'] : false;
			$next = ! empty( $_POST['next_text'] ) ? $_POST['next_text'] : false;

			$calendar = new CoursePress_Template_Calendar( array(
				'course_id' => $_POST['course_id'],
				'month'     => $date['mon'],
				'year'      => $date['year'],
			) );

			$html = '';

			if ( $pre && $next ) {
				$html = $calendar->create_calendar( $pre, $next );
			} else {
				$html = $calendar->create_calendar();
			}

			$ajax_response['calendar'] = $html;
		}

		$response = array(
			'what'   => 'refresh_course_calendar',
			'action' => 'refresh_course_calendar',
			'id'     => $ajax_status,
			'data'   => json_encode( $ajax_response ),
		);

		ob_end_clean();
		ob_start();
		$xmlresponse = new WP_Ajax_Response( $response );
		$xmlresponse->send();
		ob_end_flush();

		exit;
	}
}
