<?php

class file_input_module extends Unit_Module {

	var $order = 9;
	var $name = 'file_input_module';
	var $label = 'File Upload';
	var $description = '';
	const FRONT_SAVE = true;
	var $response_type = 'file';

	function __construct() {
		$this->on_create();
	}

	function file_input_module() {
		$this->__construct();
	}

	public static function get_response_form( $user_ID, $response_request_ID, $show_label = true ) {
		global $coursepress;

		$response = file_input_module::get_response( $user_ID, $response_request_ID );
		if ( count( (array) $response >= 1 ) ) {
			require_once( $coursepress->plugin_dir . 'includes/classes/class.encryption.php' );
			$encryption = new CP_Encryption();

			$file_extension = strtoupper( pathinfo( $response->guid, PATHINFO_EXTENSION ) );

			$response->guid = $encryption->encode( $response->guid );
			?>
			<div class="module_file_response_answer">
				<?php if ( $show_label ) { ?>
					<label><?php _e( 'Uploaded File', 'cp' ); ?></label>
				<?php } ?>
				<div class="front_response_content">
					<a href="<?php echo trailingslashit( home_url() ) . '?fdcpf=' . $response->guid; ?>"><?php
						_e( 'Download file ', 'cp' );
						echo ' ( ' . $file_extension . ' )';
						?></a>
				</div>
			</div>

		<?php
		} else {
			_e( 'File not uploaded yet.', 'cp' );
		}
		?>
		<div class="full regular-border-divider"></div>
	<?php
	}

	public static function get_response( $user_ID, $response_request_ID, $status = 'inherit', $limit = 1, $ids_only = false ) {
		$already_respond_posts_args = array(
			'posts_per_page' => $limit,
			'post_author'    => $user_ID,
			'author'         => $user_ID,
			'post_type'      => 'attachment',
			'post_parent'    => $response_request_ID,
			'post_status'    => $status//inherit
		);

		if ( $ids_only ) {
			$already_respond_posts_args['fields'] = 'ids';
		}

		$already_respond_posts = get_posts( $already_respond_posts_args );

		if ( isset( $already_respond_posts[0] ) && is_object( $already_respond_posts[0] ) ) {
			$response = $already_respond_posts[0];
		} else {
			$response = $already_respond_posts;
		}

		return $response;
	}

	public static function front_main( $data ) {
		$data->name    = __CLASS__;
		$response      = file_input_module::get_response( get_current_user_id(), $data->ID );
		$all_responses = file_input_module::get_response( get_current_user_id(), $data->ID, 'private', - 1 );

		$grade = false;
		if ( count( $response ) == 0 ) {
			global $coursepress;
			if ( $coursepress->is_preview( Unit_Module::get_module_unit_id( $data->ID ) ) ) {
				$enabled = 'disabled';
			} else {
				$enabled = 'enabled';
			}
		} else {
			$enabled = 'disabled';
			$grade   = Unit_Module::get_response_grade( $response->ID );
		}
		?>
		<div class="<?php echo $data->name; ?> front-single-module<?php echo( file_input_module::FRONT_SAVE == true ? '-save' : '' ); ?>">
			<?php if ( $data->post_title != '' && parent::display_title_on_front( $data ) ) { ?>
				<h2 class="module_title"><?php echo $data->post_title; ?></h2>
			<?php } ?>

			<?php if ( $data->post_content != '' ) { ?>
				<div class="module_description"><?php echo apply_filters( 'element_content_filter', apply_filters( 'the_content', $data->post_content ) ); ?></div>
			<?php } ?>

			<div class="module_file_input">
				<?php if ( count( $response ) == 0 ) { ?>
					<input type="file" <?php echo ( $data->mandatory_answer == 'yes' ) ? 'data-mandatory="yes"' : 'data-mandatory="no"'; ?> name="<?php echo $data->name . '_front_' . $data->ID; ?>" id="<?php echo $data->name . '_front_' . $data->ID; ?>" <?php echo $enabled; ?> />
				<?php
				} else {
					_e( 'File successfully uploaded. ', 'cp' );
					// printf( '<a target="_blank" href="%s" style="padding-left: 20px">%s</a>', $response->guid, __( 'View/Download File', 'cp' ) );
				}
				?>
			</div>

			<?php echo parent::grade_status_and_resubmit( $data, $grade, $all_responses, $response ); ?>

		</div>

		<?php
		/* $unit_module_main = new Unit_Module();

		  if ( is_object( $response ) && !empty( $response ) ) {

		  $comment = Unit_Module::get_response_comment( $response->ID );
		  if ( !empty( $comment ) ) {
		  ?>
		  <div class="response_comment_front"><?php echo $comment; ?></div>
		  <?php
		  }
		  } */
		?>

	<?php
	}

	function admin_main( $data ) {
		?>

		<div class="<?php if ( empty( $data ) ) { ?>draggable-<?php } ?>module-holder-<?php echo $this->name; ?> module-holder-title" <?php if ( empty( $data ) ) { ?>style="display:none;"<?php } ?>>

			<h3 class="module-title sidebar-name <?php echo( ! empty( $data->active_module ) ? 'is_active_module' : '' ); ?>" data-panel="<?php echo( ! empty( $data->panel ) ? $data->panel : '' ); ?>" data-id="<?php echo( ! empty( $data->ID ) ? $data->ID : '' ); ?>">
				<span class="h3-label">
					<span class="h3-label-left"><?php echo( isset( $data->post_title ) && $data->post_title !== '' ? $data->post_title : __( 'Untitled', 'cp' ) ); ?></span>
					<span class="h3-label-right"><?php echo $this->label; ?></span>
					<?php
		parent::get_module_move_link();
		?>
				</span>
			</h3>

			<div class="module-content">
				<input type="hidden" name="<?php echo $this->name; ?>_module_page[]" class="module_page" value="<?php echo( isset( $data->module_page ) ? $data->module_page : '' ); ?>" />
				<input type="hidden" name="<?php echo $this->name; ?>_module_order[]" class="module_order" value="<?php echo( isset( $data->module_order ) ? $data->module_order : 999 ); ?>" />
				<input type="hidden" name="module_type[]" value="<?php echo $this->name; ?>" />
				<input type="hidden" name="<?php echo $this->name; ?>_id[]" class="unit_element_id" value="<?php echo esc_attr( isset( $data->ID ) ? $data->ID : '' ); ?>" />

				<input type="hidden" class="element_id" value="<?php echo esc_attr( isset( $data->ID ) ? $data->ID : '' ); ?>" />

				<label class="bold-label"><?php
		_e( 'Element Title', 'cp' );
		$this->time_estimation( $data );
		?></label>
				<?php echo $this->element_title_description(); ?>
				<input type="text" class="element_title" name="<?php echo $this->name; ?>_title[]" value="<?php echo esc_attr( isset( $data->post_title ) ? $data->post_title : '' ); ?>" />

				<div class="group-check">
					<?php echo $this->show_title_on_front_element( $data ); ?>
		<?php echo $this->mandatory_answer_element( $data ); ?>
		<?php echo $this->assessable_answer_element( $data ); ?>
				</div>

				<div class="group-check second-group-check" <?php echo ( isset( $data->gradable_answer ) && $data->gradable_answer == 'no' ) ? 'style="display:none;"' : ''; ?> />
				<?php echo $this->minimum_grade_element( $data ); ?>
		<?php echo $this->limit_attempts_element( $data ); ?>
			</div>

			<label class="bold-label"><?php _e( 'Content', 'cp' ); ?></label>

			<div class="editor_in_place">
				<?php
		$editor_name    = $this->name . "_content[]";
		$editor_id      = ( esc_attr( isset( $data->ID ) ? 'editor_' . $data->ID : rand( 1, 9999 ) ) );
		$editor_content = htmlspecialchars_decode( ( isset( $data->post_content ) ? $data->post_content : '' ) );

		$args = array(
			"textarea_name" => $editor_name,
			"textarea_rows" => 5,
			"quicktags"     => false,
			"teeny"         => false,
			"editor_class"  => 'cp-editor cp-unit-element',
		);

		$args = apply_filters( 'coursepress_element_editor_args', $args, $editor_name, $editor_id );

		wp_editor( $editor_content, $editor_id, $args );
		?>
			</div>
			<?php
		parent::get_module_delete_link();
		?>
		</div>

		</div>

		<?php
	}

	function on_create() {
		$this->order       = apply_filters( 'coursepress_' . $this->name . '_order', $this->order );
		$this->description = __( 'Add file upload blocks to the unit. Useful if students need to send you various files like essay, homework etc.', 'cp' );
		$this->label       = __( 'File Upload', 'cp' );
		$this->save_module_data();
		parent::additional_module_actions();
	}

	function save_module_data() {
		global $wpdb, $last_inserted_unit_id, $save_elements;

		if ( isset( $_POST['module_type'] ) && ( $save_elements == true ) ) {

			foreach ( array_keys( $_POST['module_type'] ) as $module_type => $module_value ) {

				if ( $module_value == $this->name ) {
					$data                       = new stdClass();
					$data->ID                   = '';
					$data->unit_id              = '';
					$data->title                = '';
					$data->excerpt              = '';
					$data->content              = '';
					$data->metas                = array();
					$data->metas['module_type'] = $this->name;
					$data->post_type            = 'module';

					if ( isset( $_POST[ $this->name . '_id' ] ) ) {
						foreach ( $_POST[ $this->name . '_id' ] as $key => $value ) {
							$data->ID                              = $_POST[ $this->name . '_id' ][ $key ];
							$data->unit_id                         = ( ( isset( $_POST['unit_id'] ) and ( isset( $_POST['unit'] ) && $_POST['unit'] != '' ) ) ? $_POST['unit_id'] : $last_inserted_unit_id );
							$data->title                           = $_POST[ $this->name . '_title' ][ $key ];
							$data->content                         = $_POST[ $this->name . '_content' ][ $key ];
							$data->metas['module_order']           = $_POST[ $this->name . '_module_order' ][ $key ];
							$data->metas['module_page']            = $_POST[ $this->name . '_module_page' ][ $key ];
							$data->metas['limit_attempts_value']   = $_POST[ $this->name . '_limit_attempts_value' ][ $key ];
							$data->metas['minimum_grade_required'] = $_POST[ $this->name . '_minimum_grade_required' ][ $key ];

							// if ( isset($_POST[$this->name . '_show_title_on_front'][$key]) ) {
							//     $data->metas['show_title_on_front'] = $_POST[$this->name . '_show_title_on_front'][$key];
							// } else {
							//     $data->metas['show_title_on_front'] = 'no';
							// }
							$data->metas['show_title_on_front'] = $_POST[ $this->name . '_show_title_field' ][ $key ];

							// if ( isset($_POST[$this->name . '_mandatory_answer'][$key]) ) {
							//     $data->metas['mandatory_answer'] = $_POST[$this->name . '_mandatory_answer'][$key];
							// } else {
							//     $data->metas['mandatory_answer'] = 'no';
							// }
							$data->metas['mandatory_answer'] = $_POST[ $this->name . '_mandatory_answer_field' ][ $key ];

							// if ( isset($_POST[$this->name . '_gradable_answer'][$key]) ) {
							//     $data->metas['gradable_answer'] = $_POST[$this->name . '_gradable_answer'][$key];
							// } else {
							//     $data->metas['gradable_answer'] = 'no';
							// }
							$data->metas['gradable_answer'] = $_POST[ $this->name . '_gradable_answer_field' ][ $key ];

							// if ( isset($_POST[$this->name . '_limit_attempts'][$key]) ) {
							//     $data->metas['limit_attempts'] = $_POST[$this->name . '_limit_attempts'][$key];
							// } else {
							//     $data->metas['limit_attempts'] = 'no';
							// }
							$data->metas['limit_attempts']  = $_POST[ $this->name . '_limit_attempts_field' ][ $key ];
							$data->metas['time_estimation'] = $_POST[ $this->name . '_time_estimation' ][ $key ];

							parent::update_module( $data );
						}
					}
				}
			}
		}

		if ( isset( $_POST['submit_modules_data_save'] ) || isset( $_POST['submit_modules_data_done'] ) || isset( $_POST['save_student_progress_indication'] ) ) {

			if ( $_FILES ) {

				// Record mandatory question answered
				$course_id = get_post_meta( $data->unit_id, 'course_id', true );
				if ( isset( $data->metas ) && isset( $data->metas['mandatory_answer'] ) && 'yes' == $data->metas['mandatory_answer'] ) {
					Student_Completion::record_mandatory_answer( get_current_user_id(), $course_id, $data->unit_id, $data->ID );
				}

				foreach ( $_FILES as $file => $array ) {

					$response_id = intval( str_replace( $this->name . '_front_', '', $file ) );

					if ( ! function_exists( 'wp_handle_upload' ) ) {
						require_once( ABSPATH . 'wp-includes/pluggable.php' );
						require_once( ABSPATH . 'wp-admin/includes/file.php' );
					}

					$uploadedfile     = $_FILES[ $file ];
					$upload_overrides = array( 'test_form' => false );

					$movefile = wp_handle_upload( $uploadedfile, $upload_overrides );

					if ( $movefile ) {
						//var_dump( $movefile );

						if ( ! isset( $movefile['error'] ) ) {

							$filename = $movefile['file'];

							$wp_upload_dir = wp_upload_dir();

							$attachment = array(
								'guid'           => $movefile['url'],
								'post_mime_type' => $movefile['type'],
								'post_title'     => basename( $movefile['url'] ),
								'post_content'   => '',
								'post_status'    => 'inherit'
							);

							$attach_id = wp_insert_attachment( $attachment, $filename, $response_id );

							$unit_id   = get_post_ancestors( $response_id );
							$course_id = get_post_meta( $unit_id[0], 'course_id', true );

							update_post_meta( $attach_id, 'user_ID', get_current_user_ID() );
							update_post_meta( $attach_id, 'course_id', $course_id );
						} else {
							?>
							<p class="form-info-red"><?php echo $movefile['error']; ?></p>
						<?php
						}
					} else {

					}
				}
			}
		}
	}

}

cp_register_module( 'file_input_module', 'file_input_module', 'input' );
?>