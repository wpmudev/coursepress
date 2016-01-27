<?php

class text_input_module extends Unit_Module {

	var $order = 11;
	var $name = 'text_input_module';
	var $label = 'Answer Field';
	var $description = '';
	const FRONT_SAVE = true;
	var $response_type = 'view';

	function __construct() {
		$this->on_create();
	}

	function text_input_module() {
		$this->__construct();
	}

	public static function get_response_form( $user_ID, $response_request_ID, $show_label = true ) {
		$response = text_input_module::get_response( $user_ID, $response_request_ID );

		$answer_length = get_post_meta( $response_request_ID, 'answer_length', false );

		if ( count( (array) $response >= 1 ) ) {
			if ( ( isset( $answer_length ) && $answer_length == 'single' ) || ! isset( $answer_length ) ) {
				?>
				<div class="module_text_response_answer">
					<?php if ( $show_label ) { ?>
						<label><?php _e( 'Response', 'cp' ); ?></label>
					<?php } ?>
					<div class="front_response_content">
						<?php echo nl2br( $response->post_content ); ?>
					</div>
				</div>

			<?php
			} else {
				?>
				<div class="module_textarea_response_answer">
					<?php if ( $show_label ) { ?>
						<label><?php _e( 'Response', 'cp' ); ?></label>
					<?php } ?>
					<div class="front_response_content">
						<?php echo nl2br( $response->post_content ); ?>
					</div>
				</div>
			<?php
			}
		} else {
			_e( 'No answer / response', 'cp' );
		}
		?>
		<div class="full regular-border-divider"></div>
	<?php
	}

	public static function get_response( $user_ID, $response_request_ID, $status = 'publish', $limit = 1, $ids_only = false ) {
		$already_respond_posts_args = array(
			'posts_per_page' => $limit,
			'post_author'    => $user_ID,
			'author'         => $user_ID,
			'post_type'      => 'module_response',
			'post_parent'    => $response_request_ID,
			'post_status'    => 'publish'
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
		$response      = text_input_module::get_response( get_current_user_id(), $data->ID );
		$all_responses = text_input_module::get_response( get_current_user_id(), $data->ID, 'private', - 1 );

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
		<?php if ( ( isset( $data->checked_length ) && $data->checked_length == 'single' ) || ( ! isset( $data->checked_length ) ) ) { ?>
			<div class="<?php echo $data->name; ?> front-single-module<?php echo( text_input_module::FRONT_SAVE == true ? '-save' : '' ); ?>">
				<?php if ( $data->post_title != '' && parent::display_title_on_front( $data ) ) { ?>
					<h2 class="module_title"><?php echo $data->post_title; ?></h2>
				<?php } ?>
				<?php if ( $data->post_content != '' ) { ?>
					<div class="module_description"><?php echo apply_filters( 'element_content_filter', apply_filters( 'the_content', $data->post_content ) ); ?></div>
				<?php } ?>
				<?php if ( is_object( $response ) && count( $response ) >= 1 && trim( $response->post_content ) !== '' ) { ?>
					<div class="front_response_content">
						<?php echo $response->post_content; ?>
					</div>
				<?php } else {
					?>
					<div class="module_textarea_input">
						<input <?php echo ( $data->mandatory_answer == 'yes' ) ? 'data-mandatory="yes"' : 'data-mandatory="no"'; ?> type="text" name="<?php echo $data->name . '_front_' . $data->ID; ?>" id="<?php echo $data->name . '_front_' . $data->ID; ?>" placeholder="<?php echo( isset( $data->placeholder_text ) && $data->placeholder_text !== '' ? esc_attr( $data->placeholder_text ) : ' ' ); ?>" value="<?php echo( is_object( $response ) && count( $response >= 1 ) ? esc_attr( $response->post_content ) : ' ' ); ?>" <?php echo $enabled; ?> />
					</div>
				<?php } ?>

				<?php echo parent::grade_status_and_resubmit( $data, $grade, $all_responses, $response ); ?>

			</div>
		<?php } else {
			?>
			<div class="<?php echo $data->name; ?> front-single-module<?php echo( text_input_module::FRONT_SAVE == true ? '-save' : '' ); ?>">
				<?php if ( $data->post_title != '' && parent::display_title_on_front( $data ) ) { ?>
					<h2 class="module_title"><?php echo $data->post_title; ?></h2>
				<?php } ?>
				<?php if ( $data->post_content != '' ) { ?>
					<div class="module_description"><?php echo apply_filters( 'element_content_filter', $data->post_content ); ?></div>
				<?php } ?>
				<div class="module_textarea_input">
					<?php if ( count( $response ) >= 1 && trim( $response->post_content ) !== '' ) { ?>
						<div class="front_response_content">
							<?php echo $response->post_content; ?>
						</div>
					<?php } else { ?>
						<textarea <?php echo ( $data->mandatory_answer == 'yes' ) ? 'data-mandatory="yes"' : 'data-mandatory="no"'; ?> class="<?php echo $data->name . '_front'; ?>" name="<?php echo $data->name . '_front_' . $data->ID; ?>" id="<?php echo $data->name . '_front_' . $data->ID; ?>" placeholder="<?php echo( isset( $data->placeholder_text ) && esc_attr( $data->placeholder_text ) !== '' ? $data->placeholder_text : ' ' ); ?>" <?php echo $enabled; ?>></textarea>
					<?php } ?>
				</div>

				<?php echo parent::grade_status_and_resubmit( $data, $grade, $all_responses, $response ); ?>

			</div>
		<?php
		}
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
				<input type="hidden" name="<?php echo $this->name; ?>_checked_index[]" class='checked_index' value="0" />
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
			"quicktags"     => true,
			"teeny"         => false,
			"editor_class"  => 'cp-editor cp-unit-element',
		);

		$args = apply_filters( 'coursepress_element_editor_args', $args, $editor_name, $editor_id );

		wp_editor( $editor_content, $editor_id, $args );
		?>
			</div>

			<div class="answer_length">  
				<label class="bold-label"><?php _e( 'Answer Length', 'cp' ); ?></label>
				<input type="radio" name="<?php echo $this->name . '_answer_length[' . ( isset( $data->module_order ) ? $data->module_order : 999 ) . '][]'; ?>" value="single" <?php ?> <?php echo( isset( $data->checked_length ) && $data->checked_length == 'single' ? 'checked' : ( ! isset( $data->checked_length ) ) ? 'checked' : '' ) ?> /> <?php _e( 'Single Line', 'cp' ); ?><br /><br />
				<input type="radio" name="<?php echo $this->name . '_answer_length[' . ( isset( $data->module_order ) ? $data->module_order : 999 ) . '][]'; ?>" value="multi" <?php echo( isset( $data->checked_length ) && $data->checked_length == 'multi' ? 'checked' : '' ); ?> /> <?php _e( 'Multiple Lines', 'cp' ); ?>
			</div>

			<?php echo $this->placeholder_element( $data ); ?>

		<?php
		parent::get_module_delete_link();
		?>
		</div>
		</div>
		<?php
	}

	function on_create() {
		$this->order       = apply_filters( 'coursepress_' . $this->name . '_order', $this->order );
		$this->description = __( 'Allow students to enter a single line of text', 'cp' );
		$this->label       = __( 'Answer Field', 'cp' );
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
							$data->ID                        = $_POST[ $this->name . '_id' ][ $key ];
							$data->unit_id                   = ( ( isset( $_POST['unit_id'] ) and ( isset( $_POST['unit'] ) && $_POST['unit'] != '' ) ) ? $_POST['unit_id'] : $last_inserted_unit_id );
							$data->title                     = $_POST[ $this->name . '_title' ][ $key ];
							$data->content                   = $_POST[ $this->name . '_content' ][ $key ];
							$data->metas['checked_length']   = $_POST[ $this->name . '_checked_index' ][ $key ];
							$data->metas['module_order']     = $_POST[ $this->name . '_module_order' ][ $key ];
							$data->metas['module_page']      = $_POST[ $this->name . '_module_page' ][ $key ];
							$data->metas['placeholder_text'] = $_POST[ $this->name . '_placeholder_text' ][ $key ];
							$data->metas['answer_length']    = $_POST[ $this->name . '_answer_length' ][ $key ];
							$data->metas['time_estimation']  = $_POST[ $this->name . '_time_estimation' ][ $key ];

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
							$data->metas['limit_attempts'] = $_POST[ $this->name . '_limit_attempts_field' ][ $key ];


							parent::update_module( $data );
						}
					}
				}
			}
		}

		if ( isset( $_POST['submit_modules_data_save'] ) || isset( $_POST['submit_modules_data_done'] ) || isset( $_POST['save_student_progress_indication'] ) ) {
			foreach ( $_POST as $response_name => $response_value ) {

				if ( preg_match( '/' . $this->name . '_front_/', $response_name ) ) {
					//echo $response_name . ',' . $response_value . '<br />';

					$response_id = intval( str_replace( $this->name . '_front_', '', $response_name ) );

					if ( $response_value != '' ) {
						$data                   = new stdClass();
						$data->ID               = '';
						$data->title            = '';
						$data->excerpt          = '';
						$data->content          = '';
						$data->metas            = array();
						$data->metas['user_ID'] = get_current_user_id();
						$data->post_type        = 'module_response';
						$data->response_id      = $response_id;
						$data->title            = ''; //__( 'Response to '.$response_id.' module ( Unit '.$_POST['unit_id'].' )' );
						$data->content          = $response_value;

						// Record mandatory question answered
						$mandatory_answer = get_post_meta( $response_id, 'mandatory_answer', true );
						$unit_id          = (int) $_POST['unit_id'];
						if ( ! empty( $mandatory_answer ) && 'yes' == $mandatory_answer ) {
							$course_id = get_post_meta( $unit_id, 'course_id', true );
							Student_Completion::record_mandatory_answer( get_current_user_id(), $course_id, $unit_id, $response_id );
						}
						$data->module_id = $response_id;
						parent::update_module_response( $data );
					}
				}
			}
		}
	}

}

cp_register_module( 'text_input_module', 'text_input_module', 'input' );
?>