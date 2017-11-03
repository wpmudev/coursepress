<?php
/**
 * CoursePress Export
 *
 * Exports courses in json format.
 *
 * @since 2.0
 **/
class CoursePress_Admin_Export extends CoursePress_Admin_Controller_Menu {
	var $parent_slug = 'coursepress';
	var $slug = 'coursepress_export';
	protected $cap = 'coursepress_settings_cap';

	public function get_labels() {
		return array(
			'title' => __( 'CoursePress Export', 'CP_TD' ),
			'menu_title' => __( 'Export', 'CP_TD' ),
		);
	}

	public function process_form() {
		if ( $this->is_valid_page() ) {
			$req = $_REQUEST['coursepress'];
			$with_students = ! empty( $req['students'] ) && $req['students'];
			$with_comments = ! empty( $req['comments'] ) && $req['comments'];
			$status = array( 'publish', 'draft', 'private' );

			if ( ! empty( $req['all'] ) ) {
				$course_ids = get_posts(
					array(
						'post_type' => CoursePress_Data_Course::get_post_type_name(),
						'post_status' => $status,
						'posts_per_page' => -1,
						'fields' => 'ids',
						'suppress_filters' => true,
					)
				);
			} else {
				$course_ids = $req['courses'];
			}

			$courses = array();

			foreach ( $course_ids as $course_id ) {
				// Get all course details
				$course = get_post( $course_id );

				$courses[ $course_id ]['course'] = $course;
				$courses[ $course_id ]['meta'] = self::unique_meta( get_post_meta( $course_id ) );
				$courses[ $course_id ]['author'] = array();

				/**
				 * Check that user still exists
				 */
				$user = get_userdata( $course->post_author );
				if ( is_a( $user, 'WP_User' ) ) {
					$courses[ $course_id ]['author'] = $user->data;
				}

				// Export instructors
				$course_instructors = array();
				$instructors = (array) CoursePress_Data_Course::get_setting( $course_id, 'instructors', array() );
				$instructors = array_filter( $instructors );

				if ( ! empty( $instructors ) ) {
					foreach ( $instructors as $instructor_id ) {
						$instructor = get_userdata( $instructor_id );
						/**
						 * Check that user still exists
						 */
						if ( ! is_a( $instructor, 'WP_User' ) ) {
							continue;
						}
						$course_instructors[ $instructor_id ] = $instructor->data;
					}
				}
				$courses[ $course_id ]['instructors'] = $course_instructors;

				// Export facilitators
				$facilitators = CoursePress_Data_Facilitator::get_course_facilitators( $course_id, false );
				$course_facilitators = array();

				if ( ! empty( $facilitators ) ) {
					foreach ( $facilitators as $facilitator_id => $facilitator ) {
						$course_facilitators[ $facilitator_id ] = $facilitator->data;
					}
				}
				$courses[ $course_id ]['facilitators'] = $course_facilitators;

				// @todo: Export categories
				// @todo: Export discussions/forum

				$units = CoursePress_Data_Course::get_units_with_modules( $course_id, $status );

				foreach ( $units as $unit_id => $unit ) {
					// Get unit metas
					$unit_metas = self::unique_meta( get_post_meta( $unit_id ) );
					$units[ $unit_id ]['meta'] = $unit_metas;

					if ( ! empty( $unit['pages'] ) ) {
						foreach ( $unit['pages'] as $page_number => $modules ) {
							if ( ! empty( $modules['modules'] ) ) {
								foreach ( $modules['modules'] as $module_id => $module ) {
									$module_meta = self::unique_meta( get_post_meta( $module_id ) );
									$module->meta = $module_meta;
									$units[ $unit_id ]['pages'][ $page_number ]['modules'][ $module_id ] = $module;

									if ( $with_comments ) {
										// Get module comments
										$comments = get_comments( 'post_id=' . $module_id );
										foreach ( $comments as $comment_id => $comment ) {
											$comment->user = get_userdata( $comment->user_id )->data;
											$comment->unit_id = $unit_id;
											$comment->module_id = $module_id;
											$courses[ $course_id ]['comments']['modules'][ $module_id ][] = $comment;
										}
									}
								}
							}
						}
					}
				}

				$courses[ $course_id ]['units'] = $units;

				if ( $with_students ) {
					// Include students
					$course_students = array();
					$students = CoursePress_Data_Course::get_students( $course_id, -1, 0 );

					foreach ( $students as $student ) {
						// Get student progress
						$student_progress = CoursePress_Data_Student::get_completion_data( $student->ID, $course_id );
						$student->data->progress = $student_progress;
						$course_students[ $student->ID ] = $student->data;
					}

					$courses[ $course_id ]['students'] = $course_students;
				}

				if ( $with_comments ) {
					// Get course comments
					$comments = get_comments( 'post_id=' . $course_id );
					foreach ( $comments as $comment_id => $comment ) {
						$comment->user = get_userdata( $comment->user_id )->data;
						$courses[ $course_id ]['comments']['course'][] = $comment;
					}
				}
			}

			$sitename = sanitize_key( get_bloginfo( 'name' ) );
			if ( ! empty( $sitename ) ) {
				$sitename .= '.';
			}
			$date = date( 'Y-m-d' );
			$wp_filename = $sitename . 'coursepress.' . $date;
			if ( 1 == count( $course_ids ) ) {
				$post = get_post( array_shift( $course_ids ) );
				if ( $post ) {
					$wp_filename .= '.'.$post->post_name;
				}
			}
			$wp_filename .= '.json';

			header( 'Content-Description: File Transfer' );
			header( 'Content-Disposition: attachment; filename=' . $wp_filename );
			header( 'Content-Type: text/json; charset=' . get_option( 'blog_charset' ), true );

			/**
			 * Check PHP version, for PHP < 3 do not add options
			 */
			$version = phpversion();
			$compare = version_compare( $version, '5.3', '<' );
			if ( $compare ) {
				echo json_encode( $courses );
				exit;
			}
			$option = defined( 'JSON_PRETTY_PRINT' )? JSON_PRETTY_PRINT : null;
			echo json_encode( $courses, $option );
			exit;
		}
	}

	/**
	 * Helper function to export only unique meta values
	 *
	 * @since 2.0
	 *
	 * @param (array)	$metas			An array of post metas.
	 **/
	public static function unique_meta( $metas = array() ) {
		$excludes = array(
			'course_facilitator',
			'coursepress_student_enrolled_id',
			'coursepress_student_enrolled',
		);

		foreach ( $metas as $key => $value ) {
			if ( is_array( $value ) ) {
				$value = array_unique( $value );
			}

			if ( in_array( $key, $excludes ) ) {
				unset( $metas[ $key ] );
				continue;
			}

			if ( 'course_settings' == $key ) {

				foreach ( $value as $k => $v ) {
					$v = maybe_unserialize( $v );
					// Remove instructors
					if ( ! empty( $v['instructors'] ) ) {
						unset( $v['instructors'] );
					}
					// Remove invited students
					if ( ! empty( $v['invited_students'] ) ) {
						unset( $v['invited_students'] );
					}
					$value[ $k ] = $v;
				}
			}

			$metas[ $key ] = $value;
		}

		return $metas;
	}
}
