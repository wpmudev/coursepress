<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
} // Exit if accessed directly

if ( !defined( 'CP_GRADABLE_RESULTS_HISTORY_LENGTH' ) ){
    define( 'CP_GRADABLE_RESULTS_HISTORY_LENGTH', 10 );//Define the max amount of answer attempts that we keep on records.
}

if ( ! class_exists( 'Student_Completion' ) ) {

	class Student_Completion {

		const CURRENT_VERSION = 2;

        function __construct() {
            add_action( 'coursepress_module_completion_criteria_change', array( $this, 'on_coursepress_module_completion_criteria_change' ), 10, 4);
            add_action( 'coursepress_unit_updated', array( $this, 'on_coursepress_unit_updated' ), 10, 2);
        }

        function Student_Completion() {
            $this->__construct();
        }

        function on_coursepress_module_completion_criteria_change($unit_id, $module_id, $new_meta, $old_meta){

            if( $new_meta['mandatory_answer'] =='yes' || 'yes' == $new_meta['gradable_answer']){

                $input_modules = Unit_Module::get_input_module_types();
                $module_type     = Unit_Module::get_module_type( $module_id );
                $module_is_input = in_array( $module_type, $input_modules );

                // Only for input modules
                if ( $module_is_input ) {
                    self::refresh_module_completion($unit_id, $module_id, $module_type, $new_meta);
                }
            }
        }

        function on_coursepress_unit_updated( $post_id, $course_id ){

            if( !empty( $_POST['refresh_unit_completion_progress'] )){
                //Refresh the mandatory inputs count in session and post_meta.
                $session_data = CoursePress_Session::session( 'coursepress_unit_completion' );
                unset($session_data[ $post_id ][ 'all_input_ids' ]);
                $input_module_meta = array();
                update_post_meta( $post_id, 'input_modules', $input_module_meta );

                //Refresh the unit completion for each student.
                self::refresh_unit_completion( $post_id );
            }
        }

		/* ----------------------------- GETTING COMPLETION DATA ----------------------------------- */

		public static function get_completion_data( $student_id, $course_id ) {
			$cache_key = __METHOD__ . '-' . $student_id . '-' . $course_id;
			if( CoursePress_Cache::cp_cache_get($cache_key) ){
				return CoursePress_Cache::cp_cache_get($cache_key);
			}

			$current_user_is_student = $student_id == get_current_user_id();

			$session_data = CoursePress_Session::session( 'coursepress_student', null, false, '+10 minutes' ); // Keep completion data for only 10 minutes

			$in_session = $current_user_is_student && isset( $session_data ) && isset( $session_data[ $student_id ]['course_completion'][ $course_id ]['unit'] );
			//$in_session = isset( $_SESSION['coursepress_student'][ $student_id ]['course_completion'][ $course_id ] );

			if ( $in_session && ! empty( $session_data[ $student_id ]['course_completion'][ $course_id ]['unit'] ) ) {
				// Try the session first...
				//$course_progress = $_SESSION['coursepress_student'][ $student_id ]['course_completion'][ $course_id ];
				$course_progress = $session_data[ $student_id ]['course_completion'][ $course_id ];
			} else {
				// Otherwise it should be in user meta
				$course_progress = get_user_option( '_course_' . $course_id . '_progress', $student_id );
				if ( empty( $course_progress ) ) {
                    if( is_array( $session_data ) && !empty($session_data[ $student_id ]['course_completion'][ $course_id ]) ) {
                        //If we are here, there are no unit completion data.
                        //Let's keep basic course information from session.
                        $course_progress = $session_data[ $student_id ]['course_completion'][ $course_id ];
                        $in_session = true;
                    } else {
					$course_progress = array();
    				$in_session = false;
			        }
				}
			}

			if ( ! $in_session ) {
				//$_SESSION['coursepress_student'][ $student_id ]['course_completion'][ $course_id ] = $course_progress;
				if( ! is_array( $session_data ) ) {
					$session_data = array();
				}
				$session_data[ $student_id ]['course_completion'][ $course_id ] = $course_progress;
				CoursePress_Session::session( 'coursepress_student', $session_data );
			}

			// Check that we're on the right version or upgrade
			if ( ! self::_check_version( $student_id, $course_id, $course_progress ) ) {
				$course_progress = self::get_completion_data( $student_id, $course_id );
			};

			CoursePress_Cache::cp_cache_set($cache_key, $course_progress);
			return $course_progress;
		}

		public static function get_visited_pages( $student_id, $course_id, $unit_id ) {
			$data = self::get_completion_data( $student_id, $course_id );

			return isset( $data['unit'][ $unit_id ]['visited_pages'] ) ? $data['unit'][ $unit_id ]['visited_pages'] : array();
		}

		public static function get_last_visited_page( $student_id, $course_id, $unit_id ) {
			$data = self::get_completion_data( $student_id, $course_id );

			return isset( $data['unit'][ $unit_id ]['last_visited_page'] ) ? $data['unit'][ $unit_id ]['last_visited_page'] : false;
		}

		public static function is_course_visited( $student_id, $course_id ) {
			$data = self::get_completion_data( $student_id, $course_id );

			return isset( $data['visited'] ) && ! empty( $data['visited'] ) ? true : false;
		}

		public static function get_remaining_pages( $student_id, $course_id, $unit_id ) {
			$cache_key = __METHOD__ . '-' . $student_id . '-' . $course_id . '-' . $unit_id;
			if( CoursePress_Cache::cp_cache_get($cache_key) ){
				return CoursePress_Cache::cp_cache_get($cache_key);
			}

			$visited = count( self::get_visited_pages( $student_id, $course_id, $unit_id ) );
			$total   = Unit::get_page_count( $unit_id );
			$remaining = $total - $visited;

			if( 0 == $remaining ) {
				do_action( 'coursepress_set_all_unit_pages_viewed', $student_id, $course_id, $unit_id );
			}

			CoursePress_Cache::cp_cache_set($cache_key, $remaining);
			return $remaining;
		}

		public static function get_mandatory_modules_answered( $student_id, $course_id, $unit_id ) {
			$cache_key = __METHOD__ . '-' . $student_id . '-' . $course_id . '-' . $unit_id;
			if( CoursePress_Cache::cp_cache_get($cache_key) ){
				return CoursePress_Cache::cp_cache_get($cache_key);
			}

			$data = self::get_completion_data( $student_id, $course_id, $unit_id );

			if ( isset( $data['unit'][ $unit_id ]['mandatory_answered'] ) ) {
				foreach ( $data['unit'][ $unit_id ]['mandatory_answered'] as $module_id => $value ) {
					if ( $value !== true ) {
						unset( $data['unit'][ $unit_id ]['mandatory_answered'][ $module_id ] );
					}
				}

				$mandatory_modules_answered = array_keys( $data['unit'][ $unit_id ]['mandatory_answered'] );
				CoursePress_Cache::cp_cache_set($cache_key, $mandatory_modules_answered);
				return $mandatory_modules_answered;
			} else {
				CoursePress_Cache::cp_cache_set($cache_key, array());
				return array();
			}
		}

		public static function get_gradable_module_answered( $student_id, $course_id, $unit_id ) {
			$data = self::get_completion_data( $student_id, $course_id );

			if ( isset( $data['unit'][ $unit_id ]['gradable_results'] ) ) {
				return $data['unit'][ $unit_id ]['gradable_results'];
			} else {
				return array();
			}
		}

		public static function get_gradable_modules_passed( $student_id, $course_id, $unit_id ) {
			$cache_key = __METHOD__ . '-' . $student_id . '-' . $course_id . '-' . $unit_id;
			if( CoursePress_Cache::cp_cache_get($cache_key) ){
				return CoursePress_Cache::cp_cache_get($cache_key);
			}

			$criteria = Unit::get_module_completion_data( $unit_id );
			$answers  = self::get_gradable_module_answered( $student_id, $course_id, $unit_id );

			if ( empty( $criteria ) || empty( $answers ) ) {
				CoursePress_Cache::cp_cache_set($cache_key, array());
				return array();
			}

			$passed_array = array();

			foreach ( $criteria['gradable_modules'] as $module_id ) {

				$required = (int) $criteria['minimum_grades'][ $module_id ];
				$passed   = false;

				if ( ! isset( $answers[ $module_id ] ) ) {
					continue;
				}

				foreach ( array_filter( $answers[ $module_id ] ) as $answer ) {
					if ( (int) $answer >= $required ) {
						$passed = true;
						do_action( 'coursepress_set_gradable_question_passed', $student_id, $course_id, $unit_id, $module_id );
					} else {
						// Could not find a result in completion, but lets check the module for an answer and record it.
						$module          = get_post_meta( $module_id, 'module_type', true );
						$response        = call_user_func( $module . '::get_response', $student_id, $module_id );
						$response_result = Unit_Module::get_response_grade( $response->ID );
						$grade           = (int) $response_result['grade'];

						if( 0 < $grade ) { // Avoid repeated recording of 0 values
							self::record_gradable_result( $student_id, $course_id, $unit_id, $module_id, $grade );
							if ( $grade >= $required ) {
								$passed = true;
								do_action( 'coursepress_set_gradable_question_passed', $student_id, $course_id, $unit_id, $module_id );
							}
						}
					}
				}
				if ( $passed ) {
					$passed_array[] = $module_id;
				}
			}

			CoursePress_Cache::cp_cache_set($cache_key, $passed_array);
			return $passed_array;
		}

		public static function get_mandatory_gradable_modules_passed( $student_id, $course_id, $unit_id ) {
			$cache_key = __METHOD__ . '-' . $student_id . '-' . $course_id . '-' . $unit_id;
			if( CoursePress_Cache::cp_cache_get($cache_key) ){
				return CoursePress_Cache::cp_cache_get($cache_key);
			}

			$criteria = Unit::get_module_completion_data( $unit_id );
			if ( empty( $criteria ) ) {
				CoursePress_Cache::cp_cache_set($cache_key, false);
				return false;
			}
			$mandatory  = $criteria['mandatory_modules'];
			$all_passed = self::get_gradable_modules_passed( $student_id, $course_id, $unit_id );

			// Forget about the ones that are not mandatory
			$mandatory_passed = array_intersect( $mandatory, $all_passed );

			CoursePress_Cache::cp_cache_set($cache_key, $mandatory_passed);
			return $mandatory_passed;
		}

		public static function get_remaining_mandatory_answers( $student_id, $course_id, $unit_id ) {
			$cache_key = __METHOD__ . '-' . $student_id . '-' . $course_id . '-' . $unit_id;
			if( CoursePress_Cache::cp_cache_get($cache_key) ){
				return CoursePress_Cache::cp_cache_get($cache_key);
			}

			$criteria = Unit::get_module_completion_data( $unit_id );
			if ( empty( $criteria ) ) {
				CoursePress_Cache::cp_cache_set($cache_key, false);
				return false;
			}
			$mandatory_required = $criteria['mandatory_modules'];
			$mandatory_answered = self::get_mandatory_modules_answered( $student_id, $course_id, $unit_id );

			// Deal with mandatory gradable answers. A mandatory question is not considered done if it is gradable and not passed.
			$mandatory_gradable = $criteria['mandatory_gradable_modules'];
			$mandatory_passed   = self::get_mandatory_gradable_modules_passed( $student_id, $course_id, $unit_id );
			$mandatory_remove   = array_diff( $mandatory_gradable, $mandatory_passed );

			// Some mandatory gradable answers are not yet passed
			if ( ! empty( $mandatory_remove ) ) {
				$mandatory_answered = array_diff( $mandatory_answered, $mandatory_remove );
			}

			$remaining_mandatory_answers = array_diff( $mandatory_required, $mandatory_answered );

			CoursePress_Cache::cp_cache_set($cache_key, $remaining_mandatory_answers);
			return $remaining_mandatory_answers;
		}

		public static function get_remaining_gradable_answers( $student_id, $course_id, $unit_id ) {
			$cache_key = __METHOD__ . '-' . $student_id . '-' . $course_id . '-' . $unit_id;
			if( CoursePress_Cache::cp_cache_get($cache_key) ){
				return CoursePress_Cache::cp_cache_get($cache_key);
			}

			$criteria = Unit::get_module_completion_data( $unit_id );
			if ( empty( $criteria ) ) {
				CoursePress_Cache::cp_cache_set($cache_key, false);
				return false;
			}
			$gradable_required = $criteria['gradable_modules'];
			$gradable_passed   = self::get_gradable_modules_passed( $student_id, $course_id, $unit_id );

			$remaining_gradable_answers = array_diff( $gradable_required, $gradable_passed );

			CoursePress_Cache::cp_cache_set($cache_key, $remaining_gradable_answers);
			return $remaining_gradable_answers;
		}

		public static function get_mandatory_steps_completed( $student_id, $course_id, $unit_id ) {
			$cache_key = __METHOD__ . '-' . $student_id . '-' . $course_id . '-' . $unit_id;
			if( CoursePress_Cache::cp_cache_get($cache_key) ){
				return CoursePress_Cache::cp_cache_get($cache_key);
			}

			$criteria = Unit::get_module_completion_data( $unit_id );
			if ( empty( $criteria ) ) {
				return false;
			}
			$mandatory           = count( $criteria['mandatory_modules'] );
			$mandatory_remaining = count( self::get_remaining_mandatory_answers( $student_id, $course_id, $unit_id ) );

			$steps_completed = $mandatory - $mandatory_remaining;

			CoursePress_Cache::cp_cache_set($cache_key, $steps_completed);
			return $steps_completed;
		}

		/**
		 * Works out steps left in the unit.
		 *
		 * Calculation:
		 *    $total = number_of_pages_in_unit + number_of_mandatory_questions // (includes graded and non-graded marked as mandatory)
		 *    $completed = number_of_pages_visited + number_of_mandatory_questions_completed // (subtract any mandatory gradable questions not passed)
		 *    $answer = $total - $completed
		 *
		 * @param $student_id
		 * @param $course_id
		 * @param $unit_id
		 *
		 * @return array
		 */
		public static function get_remaining_steps( $student_id, $course_id, $unit_id ) {
			$cache_key = __METHOD__ . '-' . $student_id . '-' . $course_id . '-' . $unit_id;
			if( CoursePress_Cache::cp_cache_get($cache_key) ){
				return CoursePress_Cache::cp_cache_get($cache_key);
			}

			$total = self::_total_steps_required( $unit_id );

			$completed = count( self::get_visited_pages( $student_id, $course_id, $unit_id ) ) + self::get_mandatory_steps_completed( $student_id, $course_id, $unit_id );

			$remaining_steps = $total - $completed;

			CoursePress_Cache::cp_cache_set($cache_key, $remaining_steps);
			return $remaining_steps;
		}

		public static function is_unit_complete( $student_id, $course_id, $unit_id ) {
			$progress = self::calculate_unit_completion( $student_id, $course_id, $unit_id, false );

			return ( 100 <= (int) $progress ) ? true : false;
		}

		public static function is_course_complete( $student_id, $course_id ) {
			$progress = self::calculate_course_completion( $student_id, $course_id, false );

			return ( 100 == (int) $progress ) ? true : false;
		}

		public static function get_mandatory_steps_required( $unit_id ) {
			$cache_key = __METHOD__ . '-' . $unit_id;
			if( CoursePress_Cache::cp_cache_get($cache_key) ){
				return CoursePress_Cache::cp_cache_get($cache_key);
			}

			$criteria = Unit::get_module_completion_data( $unit_id );
			if ( empty( $criteria ) ) {
				CoursePress_Cache::cp_cache_set($cache_key, false);
				return false;
			}

			$mandatory_steps = count( $criteria['mandatory_modules'] );
			CoursePress_Cache::cp_cache_set($cache_key, $mandatory_steps);
			return $mandatory_steps;
		}

		public static function is_mandatory_complete( $student_id, $course_id, $unit_id ) {
			$cache_key = __METHOD__ . '-' . $student_id . '-' . $course_id . '-' . $unit_id;
			if( CoursePress_Cache::cp_cache_get($cache_key) ){
				return CoursePress_Cache::cp_cache_get($cache_key);
			}

			$remaining = count( self::get_remaining_mandatory_answers( $student_id, $course_id, $unit_id ) );
			$completed = 0 == $remaining ? true : false;

			CoursePress_Cache::cp_cache_set($cache_key, $completed);
			return $completed;
		}

		/* ----------------------------- CALCULATES AND UPDATES UNIT/COURSE COMPLETION ----------------------------------- */

		public static function calculate_unit_completion( $student_id, $course_id, $unit_id, $update = true, &$data = false ) {

			if ( empty( $unit_id ) ) {
				return false;
			}

			$cache_key = __METHOD__ . '-' . $student_id . '-' . $course_id . '-' . $unit_id;
			if( CoursePress_Cache::cp_cache_get($cache_key) ){
				return CoursePress_Cache::cp_cache_get($cache_key);
			}

			if ( empty( $data ) ) {
				$data = self::get_completion_data( $student_id, $course_id );
				self::_check_unit( $data, $unit_id );
			}

			$total     = self::_total_steps_required( $unit_id );
			$completed = $total - self::get_remaining_steps( $student_id, $course_id, $unit_id );

			$progress = $completed / $total * 100.0;
			$progress = $progress > 100 ? 100 : $progress;

			$data['unit'][ $unit_id ]['unit_progress'] = $progress;

			if ( $update ) {
				self::update_completion_data( $student_id, $course_id, $data );
			}

			if( 100 == (int) $progress ) {
				do_action( 'coursepress_set_unit_completed', $student_id, $course_id, $unit_id );
			}

			CoursePress_Cache::cp_cache_set($cache_key, $progress);
			return $progress;
		}

		public static function calculate_course_completion( $student_id, $course_id, $update = true ) {

			if ( empty( $course_id ) ) {
				return false;
			}

			$cache_key = __METHOD__ . '-' . $student_id . '-' . $course_id;
			if( CoursePress_Cache::cp_cache_get($cache_key) ){
				return CoursePress_Cache::cp_cache_get($cache_key);
			}

			$data        = self::get_completion_data( $student_id, $course_id );
			$course      = new Course( $course_id );
			$total_units = $course->get_units( $course_id, 'publish', true );

			// No units or no units published
			if ( empty( $total_units ) ) {
				CoursePress_Cache::cp_cache_set($cache_key, 0);
				return 0;
			}

			$progress = 0.0;

			if ( isset( $data['unit'] ) && is_array( $data['unit'] ) ) {
				foreach ( $data['unit'] as $unit_id => $unit ) {
					if ( 'publish' == get_post_status( $unit_id ) ) {
						$progress += self::calculate_unit_completion( $student_id, $course_id, $unit_id, $update, $data );
					}
				}

				$progress                = $progress / $total_units;
				$progress = $progress > 100 ? 100 : $progress;
				$data['course_progress'] = $progress;
			}

			if ( $update ) {
				self::update_completion_data( $student_id, $course_id, $data );
			}

			if( 100 == (int) $progress ) {
				do_action( 'coursepress_set_course_completed', $student_id, $course_id );
			}

			CoursePress_Cache::cp_cache_set($cache_key, $progress);
			return $progress;
		}

		/* ----------------------------- RECORDING AND UPDATING COMPLETION DATA ----------------------------------- */

		public static function update_completion_data( $student_id, $course_id, $data, $version = true ) {

			$global_setting = ! is_multisite();

			if ( empty( $data ) ) {
				$data = self::get_completion_data( $student_id, $course_id );
			}

			update_user_option( $student_id, '_course_' . $course_id . '_progress', $data, $global_setting );

            if( $student_id != get_current_user_id()){
                //If we are here, the current user is the admin or an instructor. i.e. when the student is being graded.
                //We should ensure that the course progress in student's session is cleared in order to pick up the fresh data.
                $student_session = WP_Session_Tokens::get_instance( $student_id );
                $student_session->destroy('coursepress_'.$student_id);
            }

            // make sure session data is also up to date
            $session_data[ $student_id ]['course_completion'][ $course_id ] = $data;
            CoursePress_Session::session( 'coursepress_student', $session_data );
			CoursePress_Cache::cp_cache_purge();
		}

		public static function record_mandatory_answer( $student_id, $course_id, $unit_id, $module_id, &$data = false ) {
			if ( $data === false ) {
				$data = self::get_completion_data( $student_id, $course_id );
			}
			self::_check_unit( $data, $unit_id );

			if ( ! isset( $data['unit'][ $unit_id ]['mandatory_answered'] ) ) {
				$data['unit'][ $unit_id ]['mandatory_answered'] = array();
			}

			$data['unit'][ $unit_id ]['mandatory_answered'][ $module_id ] = true;

			do_action( 'coursepress_set_mandatory_question_answered', $student_id, $course_id, $unit_id, $module_id );

			self::update_completion_data( $student_id, $course_id, $data );
		}

		public static function clear_mandatory_answer( $student_id, $course_id, $unit_id, $module_id ) {
			$data = self::get_completion_data( $student_id, $course_id );
			self::_check_unit( $data, $unit_id );

			if ( ! isset( $data['unit'][ $unit_id ]['mandatory_answered'] ) ) {
				$data['unit'][ $unit_id ]['mandatory_answered'] = array();
			}

			$data['unit'][ $unit_id ]['mandatory_answered'][ $module_id ] = false;
			self::update_completion_data( $student_id, $course_id, $data );
		}

		public static function record_gradable_result( $student_id, $course_id, $unit_id, $module_id, $result, &$data = false ) {
			if ( $data === false ) {
				$data = self::get_completion_data( $student_id, $course_id );
			}
			self::_check_unit( $data, $unit_id );

			if ( ! isset( $data['unit'][ $unit_id ]['gradable_results'] ) ) {
				$data['unit'][ $unit_id ]['gradable_results'] = array();
			}

			if ( ! isset( $data['unit'][ $unit_id ]['gradable_results'][ $module_id ] ) ) {
				$data['unit'][ $unit_id ]['gradable_results'][ $module_id ] = array();
			}

            $gradable_results = $data['unit'][ $unit_id ]['gradable_results'][ $module_id ];
			// Keep previous results, so push to the last entry
            $gradable_results[] = $result;
            // Keep only a few previous records to avoid memory issues.
            // The amount of records to be stored will be determined by the value of CP_GRADABLE_RESULTS_HISTORY_LENGTH.
            $data['unit'][ $unit_id ]['gradable_results'][ $module_id ] = array_slice($gradable_results,count($gradable_results)-CP_GRADABLE_RESULTS_HISTORY_LENGTH);

			self::update_completion_data( $student_id, $course_id, $data );
		}

		public static function record_visited_page( $student_id, $course_id, $unit_id, $page_num, &$data = false ) {
			if ( $data === false ) {
				$data = self::get_completion_data( $student_id, $course_id );
			}
			self::_check_unit( $data, $unit_id );

			if ( ! isset( $data['unit'][ $unit_id ]['visited_pages'] ) ) {
				$data['unit'][ $unit_id ]['visited_pages'] = array();
			}

			if ( ! in_array( $page_num, $data['unit'][ $unit_id ]['visited_pages'] ) ) {
				$data['unit'][ $unit_id ]['visited_pages'][] = $page_num;
			}

			self::_record_last_visited_page( $unit_id, $page_num, $data );
			self::_record_visited_course( $student_id, $course_id, $data );
			self::update_completion_data( $student_id, $course_id, $data );
		}

		/* ----------------------------- PRIVATE METHODS FOR THIS CLASS ----------------------------------- */

		private static function _record_last_visited_page( $unit_id, $page_num, &$data ) {
			$data['unit'][ $unit_id ]['last_visited_page'] = $page_num;
		}

		private static function _record_visited_course( $student_id, $course_id, &$data ) {
			if ( ! isset( $data['visited'] ) ) {
				$data['visited'] = 1;
			}
		}

		private static function _check_unit( &$data, $unit_id ) {
			if ( ! isset( $data['unit'] ) ) {
				$data['unit'] = array();
			}
			if ( ! isset( $data['unit'][ $unit_id ] ) ) {
				$data['unit'][ $unit_id ] = array();
			}
		}

		private static function _total_steps_required( $unit_id ) {
			$criteria = Unit::get_module_completion_data( $unit_id );
			if ( empty( $criteria ) ) {
				return false;
			}
			$total_answers = count( $criteria['mandatory_modules'] );
			$total_pages   = Unit::get_page_count( $unit_id );

			return $total_answers + $total_pages;
		}

		/* ----------------------------- PRIVATE MAINTENANCE METHODS FOR THIS CLASS ------------------------------ */

		private static function _check_version( $student_id, $course_id, $data ) {
			if ( ! isset( $data['version'] ) || self::CURRENT_VERSION > $data['version'] ) {
				self::_run_completion_upgrade( $student_id, $course_id, $data );

				return false;
			} else {
				return true;
			}
		}

		// Used to update the completion system
		private static function _update_version( $student_id, $course_id, $data, $version ) {
			$data['version'] = $version;
			self::update_completion_data( $student_id, $course_id, $data );
		}

		private static function _run_completion_upgrade( $student_id, $course_id, $data ) {

			$old_version = isset( $data['version'] ) ? (int) $data['version'] : 0;

			// Upgrade to version 1
			if ( 1 > $old_version ) {
				self::_version_1_upgrade( $student_id, $course_id, $data );
			} else if ( 2 > $old_version ){
                self::_version_2_upgrade( $student_id, $course_id, $data );
            }

		}

		// Upgrade to version 1
		public static function _version_1_upgrade( $student_id, $course_id, $data ) {
			// Get units
			$units = Unit::get_units_from_course( $course_id, 'any', true );

			if ( ! empty( $units ) ) {

				// Traverse units
				foreach ( $units as $unit_id ) {

					// Get visited pages data
					$visited_pages = get_user_option( 'visited_unit_pages_' . $unit_id . '_page', $student_id );
					$visited_pages = explode( ',', $visited_pages );

					if ( ! empty( $visited_pages ) ) {
						foreach ( $visited_pages as $page ) {
							if ( ! empty( $page ) ) {
								self::record_visited_page( $student_id, $course_id, $unit_id, $page, $data );
								//cp_write_log( 'Record visited page: Unit: ' . $unit_id . ' Page: ' . $page );
							}
						}
					}

					// Get modules
					$modules       = Unit_Module::get_modules( $unit_id, 0, true );
					$input_modules = Unit_Module::get_input_module_types();

					if ( ! empty( $modules ) ) {

						// Traverse modules
						foreach ( $modules as $module_id ) {

							$module_type     = Unit_Module::get_module_type( $module_id );
							$module_is_input = in_array( $module_type, $input_modules );

							// Only for input modules
							if ( $module_is_input ) {

								$module_meta = Unit_Module::get_module_meta( $module_id );

								// Did the student answer it?
								$response = call_user_func( $module_type . '::get_response', get_current_user_id(), $module_id, 'inherit', - 1, true );

								// Yes
								if ( ! empty( $response ) ) {

									if ( 'yes' == $module_meta['mandatory_answer'] ) {
										self::record_mandatory_answer( $student_id, $course_id, $unit_id, $module_id, $data );
										//cp_write_log( 'Record mandatory answer: Module: ' . $module_id );
									}

									if ( 'yes' == $module_meta['gradable_answer'] ) {
										foreach ( $response as $answer ) {
											$result = Unit_Module::get_response_grade( $answer );
											if( 0 < $result['grade'] ) {
												self::record_gradable_result( $student_id, $course_id, $unit_id, $module_id, $result['grade'], $data );
											}
											//cp_write_log( 'Record gradable result: Module: ' . $module_id . ' Result: ' . $result['grade'] );
										}
									}

								} // End responses

							} // End input module

						} // End Modules loop

					} // End Modules

				} // End Units loop

			}  // End Units

			// Remove CoursePress transients
			global $wpdb;
			$table = $wpdb->options;
			$sql = $wpdb->prepare( "DELETE FROM {$table} WHERE `option_name` LIKE %s OR `option_name` LIKE %s", '%_transient_coursepress_course%', '%_transient_coursepress_unit%' );
			$wpdb->query( $sql );

			// Record the new version
			self::_update_version( $student_id, $course_id, $data, 1 );
			//cp_write_log( 'Upgraded Course: ' . $course_id . ' to version: ' . 1 );
		}

        // Upgrade to version 2.
        // This upgrade will repair DB records related to gradable results.
        public static function _version_2_upgrade( $student_id, $course_id, $data ) {

            if( !is_user_logged_in()){
                self::_update_version( $student_id, $course_id, $data, 2 );
                return;
            }

            if( !$course_id || !$student_id) return;

            //Get fresh course_progress. $data object might contain out-dated information from session.
            //$course_progress = get_user_option( '_course_' . $course_id . '_progress', $student_id );
            $course_progress = $data;

            if(!empty($course_progress['unit'])){
                foreach($course_progress['unit'] as $unit_key => $unit){
                    if(!empty($unit['gradable_results'])){
                        foreach($unit['gradable_results'] as $result_key => $results){
                            //Remove redundant records. Keep only the amount defined by CP_GRADABLE_RESULTS_HISTORY_LENGTH.
                            $course_progress['unit'][$unit_key]['gradable_results'][$result_key] = array_slice($results,count($results)-CP_GRADABLE_RESULTS_HISTORY_LENGTH);
                        }
                    }
                }
            }

            $global_setting = ! is_multisite();
            update_user_option( $student_id, '_course_' . $course_id . '_progress', $course_progress, $global_setting );
            $session_data[ $student_id ]['course_completion'][ $course_id ] = $course_progress;
            CoursePress_Session::session( 'coursepress_student', $session_data );

            // Record the new version
            self::_update_version( $student_id, $course_id, $course_progress, 2 );
        }

        public static function refresh_unit_completion( $unit_id ){

            $modules       = Unit_Module::get_modules( $unit_id, 0, true );
            $input_modules = Unit_Module::get_input_module_types();

            if ( ! empty( $modules ) ) {

                // Traverse modules
                foreach ($modules as $module_id) {

                    $module_type     = Unit_Module::get_module_type( $module_id );
                    $module_is_input = in_array( $module_type, $input_modules );

					// Only for input modules
					if ( $module_is_input ) {
						$module_meta = Unit_Module::get_module_meta( $module_id );
						self::refresh_module_completion($unit_id, $module_id, $module_type, $module_meta);
					}
				}
			}

        }

        public static function refresh_module_completion( $unit_id, $module_id, $module_type, $meta){
            $unit_object = new Unit( $unit_id );
            $unit = $unit_object->get_unit();
            $course_id = $unit->post_parent;

            $students = Course::get_course_students_ids( $course_id );

            foreach( $students as $idx => $student_id){
                // Did the student answer it?
                $response = call_user_func( $module_type . '::get_response', $student_id, $module_id, 'inherit', - 1, true );

                // Yes
                if ( ! empty( $response ) ) {

                    if ( 'yes' == $meta['mandatory_answer'] ) {
                        self::record_mandatory_answer( $student_id, $course_id, $unit_id, $module_id );
                        //cp_write_log( 'Record mandatory answer: Module: ' . $module_id );
                    }

                    if ( 'yes' == $meta['gradable_answer'] ) {
                        foreach ( $response as $answer ) {
                            $result = Unit_Module::get_response_grade( $answer );
                            if( 0 < $result['grade'] ) {
                                self::record_gradable_result( $student_id, $course_id, $unit_id, $module_id, $result['grade'] );
                            }
                            //cp_write_log( 'Record gradable result: Module: ' . $module_id . ' Result: ' . $result['grade'] );
                        }
                    }

                } // End responses
            }
        }

	}

    $cp_student_completion = new Student_Completion();

}