<?php

class checkbox_input_module extends Unit_Module {

	var $order = 8;
	var $name = 'checkbox_input_module';
	var $label = 'Multiple Choice';
	var $description = '';
	const FRONT_SAVE = true;
	var $response_type = 'view';

	function __construct() {
		$this->on_create();
	}

	function checkbox_input_module() {
		$this->__construct();
	}

	public static function get_response_form( $user_ID, $response_request_ID, $show_label = true ) {
		$response = checkbox_input_module::get_response( $user_ID, $response_request_ID );
		if ( $response ) {
			$student_checked_answers = get_post_meta( $response->ID, 'student_checked_answers', true );
			?>
			<div class="module_text_response_answer">
				<?php if ( $show_label ) { ?>
					<label><?php _e( 'Response', 'cp' ); ?></label>
				<?php } ?>
				<div class="front_response_content radio_input_module">
					<ul class='radio_answer_check_li'>
						<?php
						$answers         = get_post_meta( $response_request_ID, 'answers', true );
						$checked_answers = get_post_meta( $response_request_ID, 'checked_answers', true );

						foreach ( $answers as $answer ) {
							?>
							<li>
								<input class="radio_answer_check" type="checkbox" value='<?php echo esc_attr( $answer ); ?>' disabled <?php echo( isset( $student_checked_answers ) && in_array( $answer, $student_checked_answers ) ? 'checked' : '' ); ?> /><?php echo $answer; ?><?php
								if ( isset( $student_checked_answers ) && in_array( $answer, $student_checked_answers ) ) {
									echo( in_array( $answer, $checked_answers ) ? '<span class="correct_answer">✓</span>' : '<span class="not_correct_answer">✘</span>' );
								};
								?>
							</li>
						<?php
						}
						?>
					</ul>
				</div>
			</div>

		<?php
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
			'post_parent'    => $response_request_ID, //aka module id
			'post_status'    => $status
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

		if ( $limit == - 1 ) {
			$response = $already_respond_posts;
		}

		return $response;
	}

	public static function front_main( $data ) {
		$data->name    = __CLASS__;
		$response      = checkbox_input_module::get_response( get_current_user_id(), $data->ID );
		$all_responses = checkbox_input_module::get_response( get_current_user_id(), $data->ID, 'private', - 1 );

		if ( is_object( $response ) ) {
			$student_checked_answers = get_post_meta( $response->ID, 'student_checked_answers', true );
		}

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
// $data->gradable_answer
		?>
		<div class="<?php echo $data->name; ?> front-single-module<?php echo( checkbox_input_module::FRONT_SAVE == true ? '-save' : '' ); ?>">
			<?php if ( $data->post_title != '' && parent::display_title_on_front( $data ) ) { ?>
				<h2 class="module_title"><?php echo $data->post_title; ?></h2>
			<?php } ?>

			<?php if ( $data->post_content != '' ) { ?>
				<div class="module_description"><?php echo apply_filters( 'element_content_filter', apply_filters( 'the_content', $data->post_content ) ); ?></div>
			<?php } ?>

			<ul class='radio_answer_check_li checkbox_answer_group' <?php echo ( $data->mandatory_answer == 'yes' ) ? 'data-mandatory="yes"' : 'data-mandatory="no"'; ?>>
				<?php
				$total_correct = 0;
				$total_answers = 0;
				if ( isset( $data->answers ) && ! empty( $data->answers ) ) {
					foreach ( $data->answers as $answer ) {

						$correct        = 'unanswered';
						$student_answer = false;
						if ( ! empty( $student_checked_answers ) ) {
							$student_answer = in_array( $answer, $student_checked_answers );
							$correct_answer = in_array( $answer, $data->checked_answers );
							$total_answers  = count( $data->checked_answers );

							if ( $student_answer && $correct_answer ) {
								$correct = 'correct';
								$total_correct += 1;
							} else if ( $student_answer ) {
								$correct = 'incorrect';
							}
						}

						?>
						<li>
							<div class="<?php echo $correct; ?>">
								<input class="checkbox_answer_check" type="checkbox" name="<?php echo $data->name . '_front_' . $data->ID; ?>[]" value='<?php echo esc_attr( $answer ); ?>' <?php echo $enabled; ?> <?php echo( isset( $student_checked_answers ) && in_array( $answer, ( is_array( $student_checked_answers ) ? $student_checked_answers : array() ) ) ? 'checked' : '' ); ?> /><?php echo $answer; ?>
							</div>
						</li>
					<?php
					}
				}
				?>
			</ul>
			<?php echo parent::grade_status_and_resubmit( $data, $grade, $all_responses, $response, false, $total_correct, $total_answers ); ?>

		</div>
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
                <!--<input type="hidden" name="<?php echo $this->name; ?>_checked_index[]" class='checked_index' value="0" />-->

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

                <label class="bold-label"><?php _e( 'Question', 'cp' ); ?></label>

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

                <div class="checkbox-editor">
                    <table class="form-table">
                        <tbody class="ci_items">
                            <tr>

                                <th width="96%">
                        <div class="checkbox_answer_check"><?php _e( 'Answers', 'cp' ); ?></div>
                        <div class="checkbox_answer"></div>
                        </th>

                        <th width="3%">
                            <!--<a class="checkbox_new_link"><?php _e( 'Add New', 'cp' ); ?></a>-->
                        </th>

                        </tr>

                        <tr>
                            <td class="label" colspan="2"><?php _e( 'Set the correct answer', 'cp' ); ?></td>
                        </tr>

                        <?php
		$i = 1;
		?>

		<?php
		if ( isset( $data->ID ) ) {

			$answer_cnt = 0;

			if ( isset( $data->answers ) ) {
				foreach ( $data->answers as $answer ) {
					?>
					<tr>
						<td width="96%">
							<input class="checkbox_answer_check" type="checkbox" name="<?php echo $this->name . '_checkbox_check[' . ( isset( $data->module_order ) ? $data->module_order : 999 ) . '][]'; ?>" value='<?php echo esc_attr( ( isset( $answer ) ? $answer : '' ) ); ?>' <?php
							if ( is_array( $data->checked_answers ) && in_array( $answer, $data->checked_answers ) ) {
								echo 'checked';
							}
							?> />
							<input class="checkbox_answer" type="text" name="<?php echo $this->name . '_checkbox_answers[' . ( isset( $data->module_order ) ? $data->module_order : 999 ) . '][]'; ?>" value='<?php echo esc_attr( ( isset( $answer ) ? $answer : '' ) ); ?>'/>
						</td>
						<?php if ( $answer_cnt >= 2 ) { ?>
							<td width="3%">
								<a class="checkbox_remove" onclick="jQuery(this).parent().parent().remove();"><i class="fa fa-trash-o"></i></a>
							</td>
						<?php } else { ?>
							<td width="3%">&nbsp;</td>
						<?php } ?>
					</tr>
					<?php
					$answer_cnt ++;
				}
			}
		} else {
			?>
			<tr>
				<td width="96%">
					<input class="checkbox_answer_check" type="checkbox" name="<?php echo $this->name . '_checkbox_check[' . ( isset( $data->module_order ) ? $data->module_order : 999 ) . '][]'; ?>" checked/>
					<input class="checkbox_answer" type="text" name="<?php echo $this->name . '_checkbox_answers[' . ( isset( $data->module_order ) ? $data->module_order : 999 ) . '][]'; ?>"/>
				</td>
				<td width="3%">&nbsp;</td>
			</tr>

			<tr>
				<td width="96%">
					<input class="checkbox_answer_check" type="checkbox" name="<?php echo $this->name . '_checkbox_check[' . ( isset( $data->module_order ) ? $data->module_order : 999 ) . '][]'; ?>"/>
					<input class="checkbox_answer" type="text" name="<?php echo $this->name . '_checkbox_answers[' . ( isset( $data->module_order ) ? $data->module_order : 999 ) . '][]'; ?>"/>
				</td>
				<td width="3%">&nbsp;</td>
			</tr>
		<?php
		}
		?>
                        </tbody>
                    </table>

                    <a class="checkbox_new_link button-secondary"><?php _e( 'Add New', 'cp' ); ?></a>

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
		$this->description = __( 'Multiple choice question where multiple options can be selected', 'cp' );
		$this->label       = __( 'Multiple Choice', 'cp' );
		$this->save_module_data();
		parent::additional_module_actions();
	}

	function save_module_data() {
		global $wpdb, $last_inserted_unit_id, $save_elements;

		if ( isset( $_POST['module_type'] ) && ( $save_elements == true ) ) {

			$answers         = array();
			$checked_answers = array();

			if ( isset( $_POST[ $this->name . '_checkbox_answers' ] ) ) {

				foreach ( $_POST[ $this->name . '_checkbox_answers' ] as $post_answers ) {
					$answers[] = $post_answers;
				}

				foreach ( $_POST[ $this->name . '_checkbox_check' ] as $post_checked_answers ) {
					$checked_answers[] = $post_checked_answers;
				}

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
							$data->metas['limit_attempts'] = $_POST[ $this->name . '_limit_attempts_field' ][ $key ];

							$data->metas['time_estimation'] = $_POST[ $this->name . '_time_estimation' ][ $key ];

							// $data->metas['answers'] = $answers[$key];
							$data->metas['answers'] = $_POST[ $this->name . '_checkbox_answers' ][ $_POST[ $this->name . '_module_order' ][ $key ] ];

							// $data->metas['checked_answers'] = $checked_answers[$key];
							$data->metas['checked_answers'] = $_POST[ $this->name . '_checkbox_check' ][ $_POST[ $this->name . '_module_order' ][ $key ] ];

							parent::update_module( $data );
						}
					}
				}
			}
		}

		if ( isset( $_POST['submit_modules_data_save'] ) || isset( $_POST['submit_modules_data_done'] ) || isset( $_POST['save_student_progress_indication'] ) ) {

			foreach ( $_POST as $response_name => $response_value ) {


				if ( preg_match( '/' . $this->name . '_front_/', $response_name ) ) {

					$response_id = intval( str_replace( $this->name . '_front_', '', $response_name ) );

					if ( $response_value != '' ) {
						$data                                   = new stdClass();
						$data->ID                               = '';
						$data->title                            = '';
						$data->excerpt                          = '';
						$data->content                          = '';
						$data->metas                            = array();
						$data->metas['user_ID']                 = get_current_user_id();
						$data->post_type                        = 'module_response';
						$data->response_id                      = $response_id;
						$data->title                            = ''; //__( 'Response to '.$response_id.' module ( Unit '.$_POST['unit_id'].' )' );
						$data->content                          = '';

						/* CHECK AND SET THE GRADE AUTOMATICALLY */

						$chosen_answers = array();

						foreach ( $response_value as $value ) {
							$chosen_answers[] = $value;
						}

						$data->metas['student_checked_answers'] = $chosen_answers;

						if ( count( $chosen_answers ) !== 0 ) {
							$right_answers  = get_post_meta( $response_id, 'checked_answers', true );
							$cleaned_answers = array();
							foreach ( $right_answers as $answer ) {
								$value =  stripslashes( $answer );
								$value = strip_tags( $value );
								$value = htmlentities( $value );
								$cleaned_answers[] = $value;
							}
							$right_answers = $cleaned_answers;

							$cleaned_response = array();
							foreach( $chosen_answers as $answer ) {
								$value =  stripslashes( $answer );
								$value = strip_tags( $value );
								$value = htmlentities( $value );
								$cleaned_response[] = $value;
							}
							$chosen_answers = $cleaned_response;

							$response_grade = 0;

							foreach ( $chosen_answers as $chosen_answer ) {
								if ( in_array( $chosen_answer, $right_answers ) ) {
									$response_grade = $response_grade + 100;
								} else {
//$response_grade = $response_grade + 0;//this line can be empty as well : )
								}
							}

							if ( count( $chosen_answers ) >= count( $right_answers ) ) {
								$grade_cnt = count( $chosen_answers );
							} else {
								$grade_cnt = count( $right_answers );
							}

							$response_grade   = round( ( $response_grade / $grade_cnt ), 0 );
							$data->auto_grade = $response_grade;
						}

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

cp_register_module( 'checkbox_input_module', 'checkbox_input_module', 'input' );
?>