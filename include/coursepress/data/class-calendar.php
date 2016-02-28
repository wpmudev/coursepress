<?php
class CoursePress_Data_Calendar {

	// Example:
	// $cal = new Course_Calendar( array( 'course_id'=>4 ) );
	// echo $cal->create_calendar("< Previous", "Next >");
	//private $days_of_week = array( 'S', 'M', 'T', 'W', 'T', 'F', 'S' );
	private static $days_of_week = array( 0, 1, 2, 3, 4, 5, 6 );

	private static $first_day = false;
	private static $day_of_week = false;
	private static $number_of_days = false;
	private static $date = false;
	private static $month_name = false;
	private static $month = false;
	private static $year = false;
	private static $course_id = false;
	private static $course_start = false;
	private static $course_start_day = false;
	private static $course_end = false;
	private static $course_end_day = false;
	private static $course_no_end = false;
	private static $previous_month = false;
	private static $next_month = false;
	private static $date_indicator = false;

	public static function init() {
	}

	private static function set( $args ) {
		global $wp_locale;

		extract( cp_default_args( array(
			'month' => false,
			'year' => false,
			'course_id' => false,
			'date_indicator' => 'indicator_light_block',
		), $args ) );

		// If month and/or year not specified, fill in the blanks with current date.

		if ( $course_id && 'false' != $course_id ) {

			// Convert the date using / not - to use American m-d-y conversion
			$start_date = get_post_meta( $course_id, 'course_start_date', true );
			self::$course_start_day = $start_date;
			$start_date = getdate( strtotime( str_replace( '-', '/', $start_date ) ) );
			self::$course_start = $start_date;
			$end_date = get_post_meta( $course_id, 'course_end_date', true );
			self::$course_end_day = $end_date;
			$end_date = getdate( strtotime( str_replace( '-', '/', $end_date ) ) );
			self::$course_end = $end_date;
			self::$course_no_end = 'off' == get_post_meta( $course_id, 'open_ended_course', true ) ? false : true;

			// Date provided?
			if ( $month && $year ) {
				$date = self::$get_date_pieces( $month, $year );
				// Use today
			} else {
				$date = getdate();
			}

			self::$previous_month = self::get_previous_month( $date, self::$course_start );
			self::$next_month = self::get_next_month( $date, self::$course_end );
			// If today (or given date) is bigger than course end date, then use the course start date
			if ( ( strtotime( $date['year'] . '/' . $date['mon'] . '/' . $date['mday'] ) > strtotime( str_replace( '-', '/', self::$course_end_day ) ) ) &&
				self::$course_start['mon'] != $date['mon'] && ! self::$course_no_end
			) {

				$month = $start_date['mon'];
				$year = $start_date['year'];
				// Else use today's date
			} else {
				$month = $date['mon'];
				$year = $date['year'];
			}

		} else {
			self::$course_id = false;
			$date = getdate();
			$month = $month ? $month : $date['mon'];
			$year = $year ? $year : $date['year'];
			self::$date = ! $month && ! $year ? $date : self::$date;

			// still needs implementing
			// self::$previous_month = self::$get_previous_month( self::$date );
			// self::$next_month = self::$get_next_month( self::$date );
		}

		self::$first_day = self::first_day_of_month( $month, $year );
		self::$number_of_days = self::number_of_days_in_month( $month, $year );
		self::$date = self::$date ? self::$date : self::get_date_pieces( $month, $year );
		self::$day_of_week = self::$date['wday'];
		self::$month_name = $wp_locale->month[ sprintf( "%02s", self::$date['mon'] ) ];
		self::$year = $year;
		self::$month = $month;
		self::$course_id = $course_id ? $course_id : false;
		self::$date_indicator = sanitize_text_field( $date_indicator );
	}

	public static function create_calendar( $pre = '&laquo;', $next = '&raquo;' ) {
		global $wp_locale;
		$calendar = '<div class="course-calendar" data-courseid="' . self::$course_id . '">';
		$calendar .= ! empty( self::$previous_month ) ? '<a class="pre-month" data-date="' . self::$previous_month . '">' . $pre . '</a>' : '<a class="pre-month" data-date="empty">' . $pre . '</a>';
		$calendar .= ! empty( self::$next_month ) ? '<a class="next-month" data-date="' . self::$next_month . '">' . $next . '</a>' : '<a class="next-month" data-date="empty">' . $next . '</a>';
		$calendar .= "<table class='course-calendar-body " . self::$date_indicator . "'>";
		$calendar .= "<caption>";
		$calendar .= self::$month_name . ' ' . self::$year;
		$calendar .= "</caption>";
		$calendar .= "<tr>";
		// Headers
		$week_day_names = array_keys( $wp_locale->weekday_initial );

		foreach ( self::$days_of_week as $day ) {
			$calendar .= "<th class='week-days'>" . $wp_locale->weekday_initial[ $week_day_names[ $day ] ] . "</th>";
		}

		$current_day = 1;

		$calendar .= '</tr><tr>';

		if ( self::$day_of_week > 0 ) {
			$calendar .= '<td colspan="' . self::$day_of_week . '">&nbsp;</td>';
		}

		$month = str_pad( self::$month, 2, "0", STR_PAD_LEFT );
		$day_of_week = self::$day_of_week;

		while ( $current_day <= self::$number_of_days ) {

			// If last day, start again
			if ( $day_of_week == 7 ) {

				$day_of_week = 0;
				$calendar .= "</tr><tr>";

			}

			$rel_day = str_pad( $current_day, 2, "0", STR_PAD_LEFT );

			$date = sprintf( '%s-%s-%s', self::$year, $month, $rel_day );
			$class = '';

			if ( self::$course_id ) {
				if ( strtotime( str_replace( '-', '/', $date ) ) == strtotime( str_replace( '-', '/', self::$course_start_day ) ) ) {
					$class = 'course-start-date';
				}
				if ( self::$course_no_end &&
					( strtotime( str_replace( '-', '/', $date ) ) > strtotime( str_replace( '-', '/', self::$course_start_day ) ) )
				) {
					$class = 'course-open-date';
				}
				if ( ( strtotime( str_replace( '-', '/', $date ) ) < strtotime( str_replace( '-', '/', self::$course_end_day ) ) ) &&
					( strtotime( str_replace( '-', '/', $date ) ) > strtotime( str_replace( '-', '/', self::$course_start_day ) ) )
				) {
					$class = 'course-active-date';
				}
				if ( strtotime( str_replace( '-', '/', $date ) ) == strtotime( str_replace( '-', '/', self::$course_end_day ) ) &&
					! self::$course_no_end
				) {
					$class = 'course-end-date';
				}
			}

			$today_date = getdate();
			if ( self::$month == $today_date['mon'] && $current_day == $today_date['mday'] ) {
				$class .= ' today';
			}

			$calendar .= "<td class='day $class' rel='$date'>$current_day</td>";

			$current_day ++;
			$day_of_week ++;
		}


		// Pad the last week if any days remain.
		if ( $day_of_week != 7 ) {

			$remaining = 7 - $day_of_week;
			$calendar .= "<td colspan='$remaining'>&nbsp;</td>";

		}

		$calendar .= "</tr>";
		$calendar .= "</table>";
		$calendar .= "</div>";
		$calendar .= '<script type="text/javascript">var wpajaxurl = "' . CoursePress_Helper_Utility::get_ajax_url()  . '";</script>';

		return $calendar;

	}

	function first_day_of_month( $month, $year ) {
		return mktime( 0, 0, 0, $month, 1, $year );
	}

	function number_of_days_in_month( $month, $year ) {
		return date( 't', self::first_day_of_month( $month, $year ) );
	}

	function get_date_pieces( $month, $year ) {
		return getdate( self::first_day_of_month( $month, $year ) );
	}

	function get_previous_month( $date, $start_date = false ) {
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
		if ( $date['mon'] < $end_date['mon'] || $date['year'] < $end_date['year'] || self::$course_no_end ) {
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

	public static function get_calendar( $args, $pre, $next ) {
		self::set( $args );
		return self::create_calendar( $pre, $next );
	}

}
