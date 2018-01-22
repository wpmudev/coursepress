<?php
/**
 * Class CoursePress_Step_FileUpload
 *
 * @since 3.0
 * @package CoursePress
 */
class CoursePress_Step_FileUpload extends CoursePress_Step {
	protected $type = 'fileupload';

	public function allowed_student_mimes() {
		$mimes = array(
			'txt' => 'text/plain',
			'jpeg' => 'image/jpeg',
			'jpg' => 'image/jpeg',
			'pdf' => 'application/pdf',
			'zip' => 'application/zip',
		);

		$allowed = $this->__get( 'allowed_file_types' );

		if ( ! empty( $allowed ) ) {
			foreach ( $mimes as $type => $label ) {
				if ( ! in_array( $type, $allowed ) ) {
					unset( $mimes[ $type ] );
				}
			}
		}

		return apply_filters(
			'coursepress_allowed_student_mimes',
			$mimes
		);
	}

	function get_answer_template( $user_id = 0 ) {
		$response = $this->get_user_response( $user_id );
		$template = '';

		if ( ! empty( $response ) ) {
			$template = parent::get_answer_template( $user_id );

			$file = $this->create_html(
				'a',
				array(
					'href' => $response['url'],
					'rel' => 'nofollow',
				),
				basename( $response['url'] )
			);
			$template .= $this->create_html( 'p', array( 'class' => 'chosen-answer correct' ), $file );
		}

		return $template;
	}

	function validate_response( $response = array() ) {
		if ( ! function_exists( 'wp_handle_upload' ) ) {
			require_once ABSPATH . 'wp-admin/includes/file.php';
		}

		if ( ! function_exists( 'get_userdata' ) ) {
			require_once ABSPATH . 'wp-includes/pluggable.php';
		}

		if ( ! empty( $_FILES ) ) {
			$course_id = filter_input( INPUT_POST, 'course_id', FILTER_VALIDATE_INT );
			$unit_id = filter_input( INPUT_POST, 'unit_id', FILTER_VALIDATE_INT );
			$file = $_FILES['module'];
			$keys = array_keys( $file );
			$upload_overrides = array(
				'test_form' => false,
				'mimes' => $this->allowed_student_mimes(),
				'action' => remove_query_arg( 'dummy' ),
			);
			$data = array(
				'grade' => 0,
				'assessable' => $this->is_assessable(),
			);
			$user = coursepress_get_user();
			$previous_response = $this->get_user_response( $user->ID );

			foreach ( $file['name'] as $step_id => $_file ) {
				$upload_file = array();

				foreach ( $keys as $key ) {
					$upload_file[ $key ] = $file[ $key ][ $step_id ];
				}

				$response = wp_handle_upload( $upload_file, $upload_overrides );
				$response['size'] = $file[$step_id]['size'];
				$data['response'] = $response;

				if ( empty( $response['error'] ) ) {
					$user->record_response( $course_id, $unit_id, $step_id, $data );
				} else {
					if ( $this->is_required() && empty( $previous_response ) ) {
						// Redirect back
						$referer = filter_input( INPUT_POST, 'referer_url' );
						coursepress_set_cookie( 'cp_step_error', $response['error'], time() + 120 );

						wp_safe_redirect( $referer );
						exit;
					}
				}
			}
		}
	}

	function get_question() {
		$step_id = $this->__get( 'ID' );
		$types = $this->__get( 'allowed_file_types' );
		$name = sprintf( 'module[%d]', $step_id );

		$attr = array(
			'type' => 'file',
			'name' => $name,
			'data-types' => implode(',', $types ),
		);
		if ( $this->is_preview() ) {
			$attr['readonly'] = 'readonly';
			$attr['disabled'] = 'disabled';
		}

		$input = coursepress_create_html( 'input', $attr );

		return $input;
	}
}