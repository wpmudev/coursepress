<?php

class CoursePress_Template_Calendar {
	private $days_of_week = array( 0, 1, 2, 3, 4, 5, 6 );

	private $first_day = false;
	private $day_of_week = false;
	private $number_of_days = false;
	private $date = false;
	private $month_name = false;
	private $month = false;
	private $year = false;
	private $course_id = false;
	private $course_start = false;
	private $course_start_day = false;
	private $course_end = false;
	private $course_end_day = false;
	private $course_no_end = false;
	private $previous_month = false;
	private $next_month = false;
	private $date_indicator = false;

	public function __construct( $args = array() ) {
		global $wp_locale;

		extract( wp_parse_args( $args, array(
			'month' => false,
			'year' => false,
			'course_id' => false,
			'date_indicator' => 'indicator_light_block',
		) ) );

		// If month and/or year not specified, fill in the blanks with current date.

		if ( $course_id && 'false' != $course_id ) {

			// Convert the date using / not - to use American m-d-y conversion
			$start_date = get_post_meta( $course_id, 'course_start_date', true );
			$this->course_start_day = $start_date;
			$start_date = getdate( strtotime( str_replace( '-', '/', $start_date ) ) );
			$this->course_start = $start_date;
			$end_date = get_post_meta( $course_id, 'course_end_date', true );
			$this->course_end_day = $end_date;
			$end_date = getdate( strtotime( str_replace( '-', '/', $end_date ) ) );
			$this->course_end = $end_date;
			$this->course_no_end = 'off' == get_post_meta( $course_id, 'open_ended_course', true ) ? false : true;

			// Date provided?
			if ( $month && $year ) {
				$date = $this->get_date_pieces( $month, $year );
				// Use today
			} else {
				$date = getdate();
			}

			$this->previous_month = $this->get_previous_month( $date, $this->course_start );
			$this->next_month = $this->get_next_month( $date, $this->course_end );
			// If today (or given date) is bigger than course end date, then use the course start date
			if ( ( strtotime( $date['year'] . '/' . $date['mon'] . '/' . $date['mday'] ) > strtotime( str_replace( '-', '/', $this->course_end_day ) ) ) &&
				$this->course_start['mon'] != $date['mon'] && ! $this->course_no_end
			) {

				$month = $start_date['mon'];
				$year = $start_date['year'];
				// Else use today's date
			} else {
				$month = $date['mon'];
				$year = $date['year'];
			}
		} else {
			$this->course_id = false;
			$date = getdate();
			$month = $month ? $month : $date['mon'];
			$year = $year ? $year : $date['year'];
			$this->date = ! $month && ! $year ? $date : $this->date;

				// still needs implementing
				// $this->previous_month = $this->get_previous_month( $this->date );
				// $this->next_month = $this->get_next_month( $this->date );
		}

		$this->first_day = $this->first_day_of_month( $month, $year );
		$this->number_of_days = $this->number_of_days_in_month( $month, $year );
		$this->date = $this->date ? $this->date : $this->get_date_pieces( $month, $year );
		$this->day_of_week = $this->date['wday'];
		$this->month_name = $wp_locale->month[ sprintf( '%02s', $this->date['mon'] ) ];
		$this->year = $year;
		$this->month = $month;
		$this->course_id = $course_id ? $course_id : false;
		$this->date_indicator = sanitize_text_field( $date_indicator );
	}

	public function create_calendar( $pre = '&laquo;', $next = '&raquo;' ) {
		global $wp_locale;

		$calendar = '<div class="course-calendar" data-courseid="' . $this->course_id . '">';
		$calendar .= ! empty( $this->previous_month ) ? '<a class="pre-month" data-date="' . $this->previous_month . '">' . $pre . '</a>' : '<a class="pre-month" data-date="empty">' . $pre . '</a>';
		$calendar .= ! empty( $this->next_month ) ? '<a class="next-month" data-date="' . $this->next_month . '">' . $next . '</a>' : '<a class="next-month" data-date="empty">' . $next . '</a>';
		$calendar .= sprintf( '<table class="course-calendar-body %s">', esc_attr( $this->date_indicator ) );
		$calendar .= '<caption>';
		$calendar .= "$this->month_name $this->year";
		$calendar .= '</caption>';
		$calendar .= '<tr>';

		// Headers
		$week_day_names = array_keys( $wp_locale->weekday_initial );

		foreach ( $this->days_of_week as $day ) {
			$calendar .= sprintf( '<th class="week-days">%s</th>', $wp_locale->weekday_initial[ $week_day_names[ $day ] ] );
		}

		$current_day = 1;

		$calendar .= '</tr><tr>';

		if ( $this->day_of_week > 0 ) {
			$calendar .= sprintf( '<td colspan="%s">&nbsp;</td>', $this->day_of_week );
		}

		$month = str_pad( $this->month, 2, '0', STR_PAD_LEFT );
		$day_of_week = $this->day_of_week;

		while ( $current_day <= $this->number_of_days ) {

			// If last day, start again
			if ( 7 == $day_of_week ) {

				$day_of_week = 0;
				$calendar .= '</tr><tr>';

			}

			$rel_day = str_pad( $current_day, 2, '0', STR_PAD_LEFT );

			$date = "$this->year-$month-$rel_day";
			$class = '';

			if ( $this->course_id ) {
				if ( strtotime( str_replace( '-', '/', $date ) ) == strtotime( str_replace( '-', '/', $this->course_start_day ) ) ) {
					$class = 'course-start-date';
				}
				if ( $this->course_no_end &&
					( strtotime( str_replace( '-', '/', $date ) ) > strtotime( str_replace( '-', '/', $this->course_start_day ) ) )
				) {
					$class = 'course-open-date';
				}
				if ( ( strtotime( str_replace( '-', '/', $date ) ) < strtotime( str_replace( '-', '/', $this->course_end_day ) ) ) &&
					( strtotime( str_replace( '-', '/', $date ) ) > strtotime( str_replace( '-', '/', $this->course_start_day ) ) )
				) {
					$class = 'course-active-date';
				}
				if ( strtotime( str_replace( '-', '/', $date ) ) == strtotime( str_replace( '-', '/', $this->course_end_day ) ) &&
					! $this->course_no_end
				) {
					$class = 'course-end-date';
				}
			}

			$today_date = getdate();
			if ( $this->month == $today_date['mon'] && $current_day == $today_date['mday'] ) {
				$class .= ' today';
			}

			$calendar .= sprintf( '<td class="day %s" rel="%s">%s</td>', $class, $date, $current_day );

			$current_day ++;
			$day_of_week ++;
		}

		// Pad the last week if any days remain.
		if ( 7 != $day_of_week ) {

			$remaining = 7 - $day_of_week;
			$calendar .= sprintf( '<td colspan="%s">&nbsp;</td>', $remaining );

		}

		$calendar .= '</tr>';
		$calendar .= '</table>';
		$calendar .= '</div>';
		$calendar .= '<script type="text/javascript">var wpajaxurl = "' . CoursePress_Helper_Utility::get_ajax_url() . '";</script>';

		/**
		 * Add course-calendar.js script and front.css to ensure it works when use outside of coursepress pages
		 * but use the same slug to avoid multiple inclusion.
		 **/
		add_action( 'wp_footer', array( $this, 'enqueue_calendar_script' ) );

		return $calendar;

	}

	public function enqueue_calendar_script() {

		$calendar_js = CoursePress::$url . 'asset/js/coursepress-calendar.js';
		wp_enqueue_script( 'coursepress-calendar', $calendar_js, array(
			'jquery'
		), CoursePress::$version );

		$style = CoursePress::$url . 'asset/css/coursepress_front.css';
		wp_enqueue_style( 'coursepress_general', $style, array( 'dashicons' ), CoursePress::$version );
	}

	public function first_day_of_month( $month, $year ) {
		return mktime( 0, 0, 0, $month, 1, $year );
	}

	public function number_of_days_in_month( $month, $year ) {
		return date( 't', $this->first_day_of_month( $month, $year ) );
	}

	public function get_date_pieces( $month, $year ) {
		return getdate( $this->first_day_of_month( $month, $year ) );
	}

	public function get_previous_month( $date, $start_date = false ) {
		if ( $date['mon'] > $start_date['mon'] || $date['year'] > $start_date['year'] ) {
			$pre_year = $date['year'];
			$pre_month = $date['mon'] - 1;

			if ( $pre_month < 1 ) {
				$pre_year -= 1;
				$pre_month = 12;
			}

			return $pre_year . '-' . $pre_month . '-01';
		} else {
			return false;
		}
	}

	function get_next_month( $date, $end_date = false ) {
		if ( $date['mon'] < $end_date['mon'] || $date['year'] < $end_date['year'] || $this->course_no_end ) {
			$next_year = $date['year'];
			$next_month = $date['mon'] + 1;

			if ( $next_month > 12 ) {
				$next_year += 1;
				$next_month = 1;
			}

			return $next_year . '-' . $next_month . '-01';
		} else {
			return false;
		}
	}
}
