<?php
if ( ! class_exists( 'Unit_Module' ) ) {

	class Unit_Module extends CoursePress_Object {

		var $data;
		var $name = 'none';
		var $label = 'None Set';
		var $description = '';
		const FRONT_SAVE = false;
		var $response_type = '';
		var $details;
		var $parent_unit = '';
		var $unit_id = 0;

		private static $auto_grade_modules = array( 'checkbox_input_module_X', 'radio_input_module' );

		function __construct() {
			add_filter( 'element_content_filter', array( $this, 'add_oembeds' ) );
			$this->on_create();
		}

		function Unit_Module() {
			$this->__construct();
		}

		function admin_sidebar( $data ) {
			?>
			<li class='draggable-module' id='<?php echo $this->name; ?>' <?php if ( $data === true ) {
				echo "style='display:none;'";
			} ?>>
				<div class='action action-draggable'>
					<div class='action-top closed'>
						<a href="#available-actions" class="action-button hide-if-no-js"></a>
						<?php echo $this->label; ?>
					</div>
					<div class='action-body closed'>
						<?php if ( ! empty( $this->description ) ) { ?>
							<p>
								<?php _e( $this->description, 'cp' ); ?>
							</p>
						<?php } ?>

					</div>
				</div>
			</li>
		<?php
		}

		function update_module( $data ) {
			global $user_id, $wpdb; //$last_inserted_module_id

			$post = array(
				'post_author'  => $user_id,
				'post_parent'  => $data->unit_id,
				'post_excerpt' => cp_filter_content( isset( $data->excerpt ) ? $data->excerpt : '' ),
				'post_content' => cp_filter_content( isset( $data->content ) ? $data->content : '' ),
				'post_status'  => 'publish',
				'post_title'   => cp_filter_content( ( isset( $data->title ) ? $data->title : '' ), true ),
				'post_type'    => ( isset( $data->post_type ) ? $data->post_type : 'module' ),
			);

			$new_module = true;
			if ( isset( $data->ID ) && $data->ID != '' && $data->ID != 0 ) {
				$post['ID'] = $data->ID; //If ID is set, wp_insert_post will do the UPDATE instead of insert
				$new_module = false;
			}

			//require( ABSPATH . WPINC . '/pluggable.php' );
			$post_id = wp_insert_post( $post );

			self::kill( self::TYPE_MODULE, $post_id );
			self::kill( self::TYPE_UNIT_MODULES, $data->unit_id );

			//Update post meta
			if ( $post_id != 0 ) {
				/* if( !$update ) {
				  $last_inserted_module_id = $post_id;
				  } */
				if ( isset( $data->metas ) ) {
					foreach ( $data->metas as $key => $value ) {
						update_post_meta( $post_id, $key, cp_filter_content( $value ) );
					}
				}
			}

			// Set input module meta
			if ( isset( $data->metas ) ) {
				$input_module_types = self::get_input_module_types();
				$module_type        = self::get_module_type( $post_id );
				if ( in_array( $module_type, $input_module_types ) ) {

					$unit_id     = $data->unit_id;
					$module_id   = $post_id;
					$module_meta = array(
						'mandatory_answer'       => isset( $data->metas['mandatory_answer'] ) ? $data->metas['mandatory_answer'] : false,
						'gradable_answer'        => isset( $data->metas['gradable_answer'] ) ? $data->metas['gradable_answer'] : false,
						'minimum_grade_required' => isset( $data->metas['minimum_grade_required'] ) ? $data->metas['minimum_grade_required'] : false,
						'limit_attempts'         => isset( $data->metas['limit_attempts'] ) ? $data->metas['limit_attempts'] : false,
						'limit_attempts_value'   => isset( $data->metas['limit_attempts_value'] ) ? $data->metas['limit_attempts_value'] : false,
					);

					Unit::update_input_module_meta( $unit_id, $module_id, $module_meta );
				}
			}

			if ( $new_module ) {

				/**
				 * Perform action after module has been created.
				 *
				 * @since 1.2.2
				 */
				do_action( 'coursepress_unit_module_created', $post_id, $data->unit_id );
			} else {

				/**
				 * Perform action after module has been updated.
				 *
				 * @since 1.2.2
				 */
				do_action( 'coursepress_unit_module_updated', $post_id, $data->unit_id );
			}

			return $post_id;
		}

		public static function delete_module( $id, $force_delete = true ) {
			global $wpdb;

			$unit_id = self::get_module_unit_id( $id );

			/**
			 * Allow Unit Module deletion to be cancelled when filter returns true.
			 *
			 * @since 1.2.2
			 */
			if ( apply_filters( 'coursepress_unit_module_cancel_delete', false, $id, $unit_id ) ) {

				/**
				 * Perform actions if the deletion was cancelled.
				 *
				 * @since 1.2.2
				 */
				do_action( 'coursepress_unit_module_delete_cancelled', $id, $unit_id );

				return false;
			}

			$the_module = self::get_module( $id );

			self::kill( self::TYPE_MODULE, $id );
			self::kill( self::TYPE_UNIT_MODULES, $unit_id );

			if ( get_post_type( $id ) == 'module' ) {
				wp_delete_post( $id, $force_delete ); //Whether to bypass trash and force deletion
			}
			//Delete unit module responses

			$args = array(
				'posts_per_page' => - 1,
				'post_parent'    => $id,
				'post_type'      => array( 'module_response' ),
				'post_status'    => 'any',
			);

			$units_module_responses = get_posts( $args );

			foreach ( $units_module_responses as $units_module_response ) {
				if ( get_post_type( $units_module_response->ID ) == 'module_response' ) {
					wp_delete_post( $units_module_response->ID, true );
				}
			}

			// Remove input module meta
			Unit::delete_input_module_meta( $unit_id, $id );

			/**
			 * Perform actions after a Unit Module is deleted.
			 *
			 * @var $the_module  The Unit Module object
			 *
			 * @since 1.2.2
			 */
			do_action( 'coursepress_unit_module_deleted', $the_module, $unit_id );
		}

		public static function check_for_modules_to_delete() {

			if ( is_admin() ) {
				if ( isset( $_POST['modules_to_execute'] ) ) {
					$modules_to_delete = $_POST['modules_to_execute'];
					foreach ( $modules_to_delete as $module_to_delete ) {
						//echo 'Module to delete:' . $module_to_delete . '<br />';

						Unit_Module::delete_module( $module_to_delete, true );
						//wp_delete_post( $module_to_delete, true );
					}
				}
			}
		}

		public static function did_student_respond( $unit_module_id, $student_id ) {
			//Check if response already exists ( from the user. Only one response is allowed per response request / module per user )
			$already_respond_posts_args = array(
				'posts_per_page' => 1,
				'meta_key'       => 'user_ID',
				'meta_value'     => $student_id,
				'post_type'      => array( 'module_response' ),
				'post_parent'    => $unit_module_id,
				'post_status'    => array( 'publish', 'inherit' )
			);

			$already_respond_posts = get_posts( $already_respond_posts_args );

			if ( count( $already_respond_posts ) > 0 ) {
				return true;
			} else {
				return false;
			}
		}

		public static function delete_module_response( $response_id, $force_delete = true ) {
			if ( wp_delete_post( (int) $response_id, $force_delete ) ) {
				return true;
			} else {
				return false;
			}
		}

		function update_module_response( $data ) {
			global $user_id, $wpdb, $coursepress;

			$unit_id   = get_post_ancestors( $data->response_id );
			$course_id = get_post_meta( $unit_id[0], 'course_id', true );

			$post = array(
				'post_author'  => $user_id,
				'post_parent'  => $data->response_id,
				'post_excerpt' => ( isset( $data->excerpt ) ? $data->excerpt : '' ),
				'post_content' => ( isset( $data->content ) ? $data->content : '' ),
				'post_status'  => 'publish',
				'post_title'   => ( isset( $data->title ) ? $data->title : '' ),
				'post_type'    => ( isset( $data->post_type ) ? $data->post_type : 'module_response' ),
			);

			if ( isset( $data->ID ) && $data->ID != '' && $data->ID != 0 ) {
				$post['ID'] = $data->ID; //If ID is set, wp_insert_post will do the UPDATE instead of insert
			}

			//Check if response already exists ( from the user. Only one response is allowed per persponse request / module per user )
			$already_respond_posts_args = array(
				'posts_per_page' => 1,
				'meta_key'       => 'user_ID',
				'meta_value'     => get_current_user_id(),
				'post_type'      => ( isset( $data->post_type ) ? $data->post_type : 'module_response' ),
				'post_parent'    => $data->response_id,
				'post_status'    => 'publish'
			);

			$already_respond_posts = get_posts( $already_respond_posts_args );

			if ( count( $already_respond_posts ) == 0 ) {

				$post_id = wp_insert_post( $post );

				//Update post meta
				$data->metas['course_id'] = $course_id;

				if ( $post_id != 0 ) {
					if ( isset( $data->metas ) ) {
						foreach ( $data->metas as $key => $value ) {
							update_post_meta( $post_id, $key, cp_filter_content( $value ) );
						}
					}
				}

				$instructors = Course::get_course_instructors_ids( $course_id );

				//SET AUTO GRADE IF REQUESTED BY A MODULE
				if ( isset( $data->auto_grade ) && is_numeric( $data->auto_grade ) ) {
					Unit_Module::save_response_grade( $post_id, $data->auto_grade, get_current_user_id(), $course_id, $unit_id[0], $data->module_id );
					do_action( 'student_response_not_required_grade_instructor_notification', get_current_user_id(), $course_id, $instructors );
				} else {
					do_action( 'student_response_required_grade_instructor_notification', get_current_user_id(), $course_id, $instructors );
				}

				//$coursepress->set_latest_activity( get_current_user_id() );
				return $post_id;
			} else {
				return false;
			}
		}

		public static function get_module( $module_id ) {
			$module = false;

			// Attempt to load from cache or create new cache object
			if ( ! self::load( self::TYPE_MODULE, $module_id, $module ) ) {

				// Get the module
				$module = get_post( $module_id );

				// Cache the course object
				self::cache( self::TYPE_MODULE, $module_id, $module );

				// cp_write_log( 'Module[' . $module_id . ']: Saved to cache..');
			} else {
				// cp_write_log( 'Module[' . $module_id . ']: Loaded from cache...');
			};

			return $module;
		}

		public static function get_module_unit_id( $module_id ) {
			global $post;
			$parents = get_post_ancestors( $module_id );
			$id      = ( $parents ) ? $parents[0] : $post->ID;

			return $id;
		}

		function order_modules( $modules ) {
			$ordered_modules = array();

			foreach ( $modules as $module ) {
				$order                     = get_post_meta( $module->ID, 'module_order', true );
				$ordered_modules[ $order ] = $module;
			}

			return $ordered_modules;
		}

		public static function get_modules( $unit_id, $unit_page = 0, $ids_only = false ) {

			$unit_pagination = cp_unit_uses_new_pagination( (int) $unit_id );

			$modules = false;

			// Attempt to load from cache or create new cache object
			if ( $ids_only ) {
				$cache_id = $unit_id . '-' . $unit_page . '-ids';
			} else {
				$cache_id = $unit_id . '-' . $unit_page;
			}
			if ( ! self::load( self::TYPE_UNIT_MODULES, $cache_id, $modules ) ) {

				// Get the modules
				if ( $unit_pagination && $unit_page > 0 ) {

					$args = array(
						'post_type'      => 'module',
						'post_status'    => 'any',
						'posts_per_page' => - 1,
						'post_parent'    => $unit_id,
						'meta_query'     => array(
							array(
								'key'   => 'module_page',
								'value' => $unit_page,
							)
						),
						//'meta_key'		 => 'module_page',
						//'meta_value'	 => $unit_page,
						'meta_key'       => 'module_order',
						'orderby'        => 'meta_value_num',
						'order'          => 'ASC',
					);
				} else {
					$args = array(
						'post_type'      => 'module',
						'post_status'    => 'any',
						'posts_per_page' => - 1,
						'post_parent'    => $unit_id,
						'meta_key'       => 'module_order',
						'orderby'        => 'meta_value_num',
						'order'          => 'ASC',
					);
				}

				if ( $ids_only ) {
					$args['fields'] = 'ids';
				}

				$modules = get_posts( $args );

				// Cache the course object
				self::cache( self::TYPE_UNIT_MODULES, $cache_id, $modules );

				// cp_write_log( 'Unit Modules[' . $unit_id . ']: Saved to cache..');
			} else {
				// cp_write_log( 'Unit Modules[' . $unit_id . ']: Loaded from cache...');
			};

			return $modules;
		}

		function get_modules_admin_forms( $unit_id = 0 ) {
			global $coursepress_modules;

			$modules = self::get_modules( $unit_id );

			foreach ( $modules as $mod ) {
				$class_name = $mod->module_type;
				if ( class_exists( $class_name ) ) {
					$module = new $class_name();
					$module->admin_main( $mod );
				}
			}
		}

		public static function get_modules_front( $unit_id = 0 ) {
			global $coursepress, $coursepress_modules, $wp, $paged, $_POST;

			if ( isset( $_GET['resubmit_nonce'] ) || ( isset( $_GET['resubmit_nonce'] ) && wp_verify_nonce( $_GET['resubmit_nonce'], 'resubmit_answer' ) ) ) {
				if ( isset( $_GET['resubmit_answer'] ) ) {
					$user_id   = get_current_user_id();
					$course_id = (int) $_GET['c'];
					$unit_id   = (int) $_GET['u'];
					$module_id = (int) $_GET['m'];
					$response  = get_post( (int) $_GET['resubmit_answer'] );
					if ( isset( $response ) && isset( $response->post_author ) && $response->post_author == get_current_user_ID() ) {
						$resubmitted_response = array(
							'ID'          => $response->ID,
							'post_status' => 'private'
						);
						wp_update_post( $resubmitted_response );
					}
					Student_Completion::clear_mandatory_answer( $user_id, $course_id, $unit_id, $module_id );
					wp_redirect( $_GET['resubmit_redirect_to'] );
					exit;
				}
			}


			$front_save    = false;
			$responses     = 0;
			$input_modules = 0;

			$paged = isset( $wp->query_vars['paged'] ) ? absint( $wp->query_vars['paged'] ) : 1;

			$unit_pagination = cp_unit_uses_new_pagination( (int) $unit_id );
			$modules         = self::get_modules( $unit_id, $paged );

			$course_id = do_shortcode( '[get_parent_course_id]' );

			/**
			 * @todo: replace with Student_Completion function soon
			 */
			cp_set_visited_unit_page( $unit_id, $paged, get_current_user_ID(), $course_id );

			//$unit_module_page_number = isset( $_GET['to_elements_page'] ) ? $_GET['to_elements_page'] : 1;

			if ( isset( $_POST['submit_modules_data_done'] ) || isset( $_POST['submit_modules_data_no_save_done'] ) ) {
				// if( defined('DOING_AJAX') && DOING_AJAX ) { cp_write_log('doing ajax'); }
				if ( isset( $_POST['submit_modules_data_done'] ) ) {
					//wp_redirect( cp_full_url( $_SERVER ). '?saved=ok' );
					if ( $_POST['event_origin'] == 'button' ) {
						wp_redirect( get_permalink( $course_id ) . trailingslashit( $coursepress->get_units_slug() ) . '?saved=ok' );
						exit;
					} else {
						wp_redirect( cp_full_url( $_SERVER ) ) . '?saved=ok';
						exit;
					}
				} else {
					if ( $_POST['event_origin'] == 'button' ) {
						wp_redirect( trailingslashit( get_permalink( $course_id ) ) . trailingslashit( $coursepress->get_units_slug() ) );
						exit;
					} else {
						wp_redirect( cp_full_url( $_SERVER ) );
						exit;
					}
					/* if ( $paged != 1 ) {
					  //wp_redirect( cp_full_url( $_SERVER ) );
					  wp_redirect(get_permalink($course_id) . trailingslashit($coursepress->get_units_slug()));
					  } else {
					  wp_redirect(cp_full_url($_SERVER));
					  } */
				}

				exit;
			}

			if ( isset( $_POST['submit_modules_data_save'] ) || isset( $_POST['submit_modules_data_no_save_save'] ) ) {
				// if( defined('DOING_AJAX') && DOING_AJAX ) { cp_write_log('doing ajax'); }
				if ( isset( $_POST['submit_modules_data_save'] ) ) {
					//wp_redirect( $_SERVER['REQUEST_URI'] . '?saved=ok' );
					wp_redirect( cp_full_url( $_SERVER ) . '?saved=ok' );
					exit;
					//exit;
				} else {
					//wp_redirect( get_permalink( $unit_id ) . trailingslashit( 'page' ) . trailingslashit( $unit_module_page_number ) );
				}
			}

			if ( isset( $_POST['save_student_progress_indication'] ) ) {
				wp_redirect( get_permalink( $course_id ) . trailingslashit( $coursepress->get_units_slug() ) . '?saved=progress_ok' );
				exit;
			}
			?>

			<form name="modules_form" id="modules_form" enctype="multipart/form-data" method="post" action="<?php echo trailingslashit( get_permalink( $unit_id ) ); //strtok( $_SERVER["REQUEST_URI"], '?' ); ?>" onSubmit="return check_for_mandatory_answers();">
				<!--#submit_bottom-->
				<input type="hidden" id="go_to_page" value=""/>

				<?php
				if ( $unit_pagination ) {
					foreach ( $modules as $mod ) {
						$class_name = $mod->module_type;
						if ( class_exists( $class_name ) ) {
							call_user_func( $class_name . '::front_main', $mod );
							if ( constant( $class_name . '::FRONT_SAVE' ) ) {
								$front_save = true;
								if ( method_exists( $class_name, 'get_response' ) ) {
									$response = call_user_func( $class_name . '::get_response', get_current_user_id(), $mod->ID );
									if ( count( $response ) > 0 ) {
										$responses ++;
									}
									$input_modules ++;
								}
							}
						}
					}
				} else {
					$pages_num = 1;
					foreach ( $modules as $mod ) {
						$class_name = $mod->module_type;
						if ( class_exists( $class_name ) ) {
							if ( $class_name == 'page_break_module' ) {
								$pages_num ++;
							} else {
								if ( $pages_num == $paged ) {
									call_user_func( $class_name . '::front_main', $mod );
									if ( constant( $class_name . '::FRONT_SAVE' ) ) {
										$front_save = true;
										if ( method_exists( $class_name, 'get_response' ) ) {
											$response = call_user_func( $class_name . '::get_response', get_current_user_id(), $mod->ID );
											if ( count( $response ) > 0 ) {
												$responses ++;
											}
											$input_modules ++;
										}
									}
								}
							}
						}
					}
				}
				wp_nonce_field( 'modules_nonce' );

				if ( $unit_pagination ) {
					$pages_num = coursepress_unit_pages( $unit_id, $unit_pagination );
				}

				$is_last_page = coursepress_unit_module_pagination( $unit_id, $pages_num, true ); //check if current unit page is last page
				if ( ! $coursepress->is_preview( $unit_id ) ) {
					if ( $front_save ) {
						if ( $input_modules !== $responses ) {
							?>
							<div class="mandatory_message"><?php _e( 'All questions marked with "* Mandatory" require your input.', 'cp' ); ?></div>
							<div class="clearf"></div>
							<input type="hidden" name="unit_id" value="<?php echo $unit_id; ?>"/>
							<a id="submit_bottom"></a>
							<?php
							if ( isset( $_POST['submit_modules_data'] ) ) {
								$form_message = __( 'The module data has been submitted successfully.', 'coursepress' );
							}
							if ( isset( $form_message ) ) {
								?><p class="form-info-regular"><?php echo $form_message; ?></p>
							<?php } ?>
							<input type="submit" class="apply-button-enrolled submit-elements-data-button" name="submit_modules_data_<?php echo( $is_last_page ? 'done' : 'save' ); ?>" value="<?php echo( $is_last_page ? __( 'Done', 'cp' ) : __( 'Next', 'cp' ) ); ?>">
						<?php
						} else {
							?>
							<input type="submit" class="apply-button-enrolled submit-elements-data-button" name="submit_modules_data_no_save_<?php echo( $is_last_page ? 'done' : 'save' ); ?>" value="<?php echo( $is_last_page ? __( 'Done', 'cp' ) : __( 'Next', 'cp' ) ); ?>">
						<?php
						}
					} else {
						?>
						<input type="submit" class="apply-button-enrolled submit-elements-data-button" name="submit_modules_data_no_save_<?php echo( $is_last_page ? 'done' : 'save' ); ?>" value="<?php echo( $is_last_page ? __( 'Done', 'cp' ) : __( 'Next', 'cp' ) ); ?>">
					<?php
					}
				}

				coursepress_unit_module_pagination( $unit_id, $pages_num );
				?>
				<div class="fullbox"></div>
				<?php if ( ! isset( $_GET['try'] ) ) : ?>
					<a href="" id="save_student_progress" class="save_progress"><?php _e( 'Save Progress & Exit', 'cp' ); ?></a>
				<?php endif; ?>
			</form>

		<?php
		}


		public static function get_module_response_comment_form( $post_id ) {
			$post = get_post( $post_id );

			$editor_name    = "response_comment";
			$editor_id      = "response_comment";
			$editor_content = $post->response_comment;


			$args = array(
				'textarea_name' => $editor_name,
				'media_buttons' => false,
				'textarea_rows' => 2,
				'editor_class'  => 'response_comment'
			);
			?>
			<label><?php _e( 'Comment', 'cp' ); ?></label>
			<?php
			// Filter $args before showing editor
			$args = apply_filters( 'coursepress_element_editor_args', $args, $editor_name, $editor_id );

			return wp_editor( $editor_content, $editor_id, $args );
		}

		public static function get_module_type( $post_id ) {
			return get_post_meta( $post_id, 'module_type', true );
		}

		function additional_module_actions() {
			$this->save_response_comment();
			Unit_Module::save_response_grade();
		}

		function save_response_comment() {
			if ( isset( $_POST['response_id'] ) && isset( $_POST['response_comment'] ) && is_admin() ) {
				update_post_meta( $_POST['response_id'], 'response_comment', cp_filter_content( $_POST['response_comment'] ) );
			}
		}

		public static function save_response_grade( $response_id = '', $response_grade = '', $user_id = false, $course_id = false, $unit_id = false, $module_id = false ) {
			if ( ( isset( $_POST['response_id'] ) || $response_id !== '' ) && ( isset( $_POST['response_grade'] ) || $response_grade !== '' ) ) {

				$grade_data = array(
					'grade'      => ( $response_grade !== '' && is_numeric( $response_grade ) ? $response_grade : $_POST['response_grade'] ),
					'instructor' => get_current_user_ID(),
					'time'       => current_time( 'timestamp' )
				);

				update_post_meta( ( $response_id !== '' && is_numeric( $response_id ) ? $response_id : $_POST['response_id'] ), 'response_grade', $grade_data );

				if ( ! $user_id ) {
					$user_id   = isset( $_POST['student_id'] ) ? (int) $_POST['student_id'] : false;
					$course_id = isset( $_POST['course_id'] ) ? (int) $_POST['course_id'] : false;
					$unit_id   = isset( $_POST['unit_id'] ) ? (int) $_POST['unit_id'] : false;
					$module_id = isset( $_POST['module_id'] ) ? (int) $_POST['module_id'] : false;
				}

				if ( ! empty( $user_id ) && ! empty( $course_id ) && ! empty( $unit_id ) && ! empty( $module_id ) ) {
					$mandatory_answer = get_post_meta( $module_id, 'mandatory_answer', true );
					if ( ! empty( $mandatory_answer ) && 'yes' == $mandatory_answer ) {
						Student_Completion::record_mandatory_answer( $user_id, $course_id, $unit_id, $module_id );
					}
					Student_Completion::record_gradable_result( $user_id, $course_id, $unit_id, $module_id, floatval( $response_grade ) );
				}

				return true;
			} else {
				return false;
			}
		}

		public static function get_response_grade( $response_id, $data = '' ) {
			$grade_data = get_post_meta( $response_id, 'response_grade' );
			$module_id = wp_get_post_parent_id( $response_id );

			$autograde_modules = Unit_Module::auto_grade_modules();
			$module_type = get_post_meta( $module_id, 'module_type', true );

			// Check if this needs to be auto graded
			if( in_array( $module_type, $autograde_modules ) && 100 > $grade_data[0]['grade'] ) {

				$grade = $grade_data[0]['grade'];
				$response = get_post( $response_id );
				$student_id = $response->post_author;
				$unit_id   = get_post_ancestors( $module_id );
				$unit_id   = $unit_id[0];
				$course_id = get_post_meta( $unit_id, 'course_id', true );

				// Multiple or single correct answer?
				$correct_answers = get_post_meta( $module_id, 'checked_answer', true );
				$correct_answers = empty( $correct_answers ) ? get_post_meta( $module_id, 'checked_answers', true ) : $correct_answers;

				if( ! is_array( $correct_answers ) ) {
					if( trim( $response->post_content ) == trim( $correct_answers ) ) {
						$grade = 100;
					}
				} else {
					$student_answers = get_post_meta( $response_id, 'student_checked_answers' );

					if ( count( $student_answers ) !== 0 ) {
						$cleaned_answers = array();
						foreach ( $correct_answers as $answer ) {
							$value =  stripslashes( $answer );
							$value = strip_tags( $value );
							$value = htmlentities( $value );
							$cleaned_answers[] = $value;
						}
						$right_answers = $cleaned_answers;

						$cleaned_response = array();
						foreach( $student_answers as $answer ) {
							$value =  stripslashes( $answer );
							$value = strip_tags( $value );
							$value = htmlentities( $value );
							$cleaned_response[] = $value;
						}
						$chosen_answers = $cleaned_response;

						$grade = 0;

						foreach ( $chosen_answers as $chosen_answer ) {
							if ( in_array( $chosen_answer, $right_answers ) ) {
								$grade = $grade + 100;
							} else {
								//$response_grade = $response_grade + 0;//this line can be empty as well : )
							}
						}

						if ( count( $chosen_answers ) >= count( $right_answers ) ) {
							$grade_cnt = count( $chosen_answers );
						} else {
							$grade_cnt = count( $right_answers );
						}

						$grade   = round( ( $grade / $grade_cnt ), 0 );
					}

				}

				Unit_Module::save_response_grade( $response_id, $grade, $student_id, $course_id, $unit_id, $module_id );
			}

			if ( $grade_data ) {
				if ( $data !== '' ) {
					return $grade_data[0][ $data ];
				} else {
					return $grade_data[0];
				}
			} else {

			}
		}

		public static function get_ungraded_response_count( $course_id = '' ) {

			if ( $course_id == '' ) {

				$args = array(
					'post_type'      => array( 'module_response', 'attachment' ),
					'post_status'    => array( 'publish', 'inherit' ),
					'posts_per_page' => - 1,
					'meta_key'       => 'course_id',
					'meta_value'     => $course_id,
					'meta_query'     => array(
						'relation' => 'AND',
						array(
							'key'     => 'response_grade',
							'compare' => 'NOT EXISTS',
							'value'   => ''
						)
					)
				);

				$ungraded_responses = get_posts( $args );

				$array_order_num = 0;

				//Count only ungraded responses from STUDENTS!
				foreach ( $ungraded_responses as $ungraded_response ) {

					if ( get_post_meta( $ungraded_response->post_parent, 'gradable_answer', true ) == 'no' ) {
						unset( $ungraded_responses[ $array_order_num ] );
					}

					if ( get_user_option( 'role', $ungraded_response->post_author ) !== 'student' ) {
						unset( $ungraded_responses[ $array_order_num ] );
					}
					$array_order_num ++;
				}

				/* $admins_responses = 0;

				  foreach ( $ungraded_responses as $ungraded_responses ) {
				  if( user_can( $ungraded_responses->post_author, 'administrator' ) ) {
				  $admins_responses++;
				  }
				  } */

				return count( $ungraded_responses ); // - $admins_responses;
			} else {

				$args = array(
					'post_type'      => array( 'module_response', 'attachment' ),
					'post_status'    => array( 'publish', 'inherit' ),
					'posts_per_page' => - 1,
					'meta_query'     => array(
						'relation' => 'AND',
						array(
							'key'     => 'response_grade',
							'compare' => 'NOT EXISTS',
							'value'   => ''
						),
						array(
							'key'   => 'course_id',
							'value' => $course_id
						)
					)
				);

				$ungraded_responses = get_posts( $args );


				$array_order_num = 0;

				//Count only ungraded responses from STUDENTS!
				foreach ( $ungraded_responses as $ungraded_response ) {

					if ( get_post_meta( $ungraded_response->post_parent, 'gradable_answer', true ) == 'no' ) {
						unset( $ungraded_responses[ $array_order_num ] );
					}

					if ( get_user_option( 'role', $ungraded_response->post_author ) !== 'student' ) {
						unset( $ungraded_responses[ $array_order_num ] );
					}

					$array_order_num ++;
				}

				return count( $ungraded_responses );
			}
		}

		function element_title_description() {
			?>
			<span class="element_title_description"><?php _e( 'The title is used to identify this module element and is useful for assessment.', 'cp' ); ?></span>
		<?php
		}

		function mandatory_answer_element( $data ) {
			?>
			<label class="mandatory_answer">
				<input type="checkbox" name="<?php echo $this->name; ?>_mandatory_answer[]" value="yes" <?php echo( isset( $data->mandatory_answer ) && $data->mandatory_answer == 'yes' ? 'checked' : ( ! isset( $data->mandatory_answer ) ) ? 'checked' : '' ) ?> />
				<input type="hidden" name="<?php echo $this->name; ?>_mandatory_answer_field[]" value="<?php echo( ( isset( $data->mandatory_answer ) && $data->mandatory_answer == 'yes' ) || ! isset( $data->mandatory_answer ) ? 'yes' : 'no' ) ?>"/>
				<?php _e( 'Mandatory Answer', 'cp' ); ?><br/>
				<span class="element_title_description"><?php _e( 'A response is required to continue', 'cp' ); ?></span>
			</label>
		<?php
		}

		function assessable_answer_element( $data ) {
			?>
			<label class="mandatory_answer">
				<input type="checkbox" class="assessable_checkbox" name="<?php echo $this->name; ?>_gradable_answer[]" value="yes" <?php echo( isset( $data->gradable_answer ) && $data->gradable_answer == 'yes' ? 'checked' : ( ! isset( $data->gradable_answer ) ) ? 'checked' : '' ) ?> />
				<input type="hidden" name="<?php echo $this->name; ?>_gradable_answer_field[]" value="<?php echo( ( isset( $data->gradable_answer ) && $data->gradable_answer == 'yes' ) || ! isset( $data->gradable_answer ) ? 'yes' : 'no' ) ?>"/>
				<?php _e( 'Assessable', 'cp' ); ?><br/>
				<span class="element_title_description"><?php _e( 'The answer will be graded', 'cp' ); ?></span>
			</label>
		<?php
		}

		function placeholder_element( $data ) {
			?>
			<div class="placeholder_holder">
				<label><?php _e( 'Placeholder Text', 'cp' ) ?><br/>
					<span class="element_title_description"><?php _e( 'Additional instructions visible in the input field as a placeholder', 'cp' ); ?></span>
				</label>
				<input type="text" class="placeholder_text" name="<?php echo $this->name; ?>_placeholder_text[]" value="<?php echo esc_attr( isset( $data->placeholder_text ) ? $data->placeholder_text : '' ); ?>"/>
			</div>
		<?php
		}

		function show_title_on_front_element( $data ) {
			?>
			<label class="show_title_on_front">
				<input type="checkbox" name="<?php echo $this->name; ?>_show_title_on_front[]" value="yes" <?php echo( isset( $data->show_title_on_front ) && $data->show_title_on_front == 'yes' ? 'checked' : ( ! isset( $data->show_title_on_front ) ) ? 'checked' : '' ) ?> />
				<input type="hidden" name="<?php echo $this->name; ?>_show_title_field[]" value="<?php echo( ( isset( $data->show_title_on_front ) && $data->show_title_on_front == 'yes' ) || ! isset( $data->show_title_on_front ) ? 'yes' : 'no' ) ?>"/>
				<?php _e( 'Show Title', 'cp' ); ?><br/>
				<span class="element_title_description"><?php _e( 'The title is displayed as a heading', 'cp' ); ?></span>
			</label>
		<?php
		}

		function minimum_grade_element( $data ) {
			?>
			<label class="minimum_grade_required_label">
				<?php _e( 'Minimum grade required', 'cp' ); ?>
				<input type="text" class="grade_spinner" name="<?php echo $this->name; ?>_minimum_grade_required[]" value="<?php echo( isset( $data->minimum_grade_required ) ? $data->minimum_grade_required : 100 ); ?>"/><br/>
				<span class="element_title_description"><?php _e( 'Set the minimum grade (%) required to pass the task', 'cp' ); ?></span>
			</label>
		<?php
		}

		function limit_attempts_element( $data ) {
			?>
			<label class="limit_attampts_label">
				<input type="checkbox" class="limit_attempts_checkbox" name="<?php echo $this->name; ?>_limit_attempts[]" value="yes" <?php echo( isset( $data->limit_attempts ) && $data->limit_attempts == 'yes' ? 'checked' : ( ! isset( $data->limit_attempts ) ) ? 'checked' : '' ) ?> />
				<input type="hidden" name="<?php echo $this->name; ?>_limit_attempts_field[]" value="<?php echo( ( isset( $data->limit_attempts ) && $data->limit_attempts == 'yes' ) || ! isset( $data->limit_attempts ) ? 'yes' : 'no' ) ?>"/>
				<?php _e( 'Limit Attempts', 'cp' ); ?>
				<input type="text" class="attempts_spinner" name="<?php echo $this->name; ?>_limit_attempts_value[]" value="<?php echo( isset( $data->limit_attempts_value ) ? $data->limit_attempts_value : 1 ); ?>"/><br>
				<span class="element_title_description"><?php _e( 'Limit attempts of this task', 'cp' ); ?></span>
			</label>
		<?php
		}

		public static function mandatory_message( $data ) {
			if ( 'yes' == $data->mandatory_answer ) {

				$message = __( '* Mandatory', 'cp' );
				if ( 'yes' == $data->gradable_answer ) {
					$message = __( '* Mandatory', 'cp' );
				}
				?>
				<div class="module_mandatory">
					<?php echo $message; ?>
				</div>
			<?php
			}
		}

		public static function grade_status_and_resubmit(
			$data, $grade, $responses, $last_public_response = false, $show_grade = true,
			$total_correct = false, $total_answers = false
		) {
			$number_of_answers = (int) count( $responses ) + (int) count( $last_public_response );

			$limit_attempts       = $data->limit_attempts; //yes or no
			$limit_attempts_value = $data->limit_attempts_value;
			$attempts_remaining   = $limit_attempts_value - $number_of_answers;

			if ( isset( $limit_attempts ) && $limit_attempts == 'yes' && 'yes' == $data->gradable_answer ) {
				$limit_attempts_value = $limit_attempts_value;
			} else {
				$limit_attempts_value = - 1; //unlimited
			}

			if ( $grade && $data->gradable_answer ) {

				if ( $grade['grade'] < $data->minimum_grade_required && $data->mandatory_answer ) {
					self::mandatory_message( $data );
				}
				?>
				<div class="module_grade">
					<div class="module_grade_left">
						<?php
						if ( $grade['grade'] < 100 ) {
							if ( ( $number_of_answers < $limit_attempts_value ) || $limit_attempts_value == - 1 ) {
								global $wp;
//								$class_name = get_class( $this );
//								$response     = call_user_func( $class_name.'::get_response', get_current_user_id(), $data->ID );
								$unit_id      = wp_get_post_parent_id( $data->ID );
								$course_id    = get_post_meta( $unit_id, 'course_id', true );
								$module_id    = $data->ID;
								$paged        = isset( $wp->query_vars['paged'] ) ? absint( $wp->query_vars['paged'] ) : 1;
								$permalink    = trailingslashit( trailingslashit( get_permalink( $unit_id ) ) . 'page/' . trailingslashit( $paged ) );
								$resubmit_url = $permalink . '?resubmit_answer=' . $last_public_response->ID . '&resubmit_redirect_to=' . $permalink . '&m=' . $module_id . '&c=' . $course_id . '&u=' . $unit_id;
								?>
								<a href="<?php echo wp_nonce_url( $resubmit_url, 'resubmit_answer', 'resubmit_nonce' ); ?>" class="resubmit_response"><?php _e( 'Submit different answer', 'cp' ); ?></a>
								<?php
								if ( $attempts_remaining > 0 ) {
									if ( $attempts_remaining == 1 ) {
										_e( '(1 attempt remaining)', 'cp' );
									} else {
										printf( __( '(%d attempts remaining)', 'cp' ), $attempts_remaining );
									}
								}
							}
						}
						?>
					</div>
					<div class="module_grade_right">
						<?php if ( $show_grade ) : ?>
							<?php
							echo __( 'Graded: ', 'cp' ) . $grade['grade'] . '%';
							if ( isset( $data->minimum_grade_required ) && is_numeric( $data->minimum_grade_required ) ) {
								if ( $grade['grade'] >= $data->minimum_grade_required ) {
									?>
									<span class="passed_element">(<?php _e( 'Passed', 'cp' ); ?>)</span>
								<?php
								} else {
									if ( $attempts_remaining > 0 ) {
										?>
										<span class="failed_element">(<?php _e( 'Not yet passed', 'cp' ); ?>)</span>
									<?php
									} else {
										?>
										<span class="failed_element">(<?php _e( 'Not Passed', 'cp' ); ?>)</span>
									<?php
									}
								}
							}
							?>
						<?php endif; ?>
						<?php
						if ( ( ! empty( $total_correct ) || 0 == $total_correct ) && ! empty( $total_answers ) ) {
							printf( __( '%d of %d correct', 'cp' ), $total_correct, $total_answers );
						}
						?>
					</div>
				</div>
			<?php
			} else {
				// if ( $data->gradable_answer && 'enabled' != $enabled ) {
				if ( $data->gradable_answer ) {
					if ( $data->mandatory_answer ) {
						self::mandatory_message( $data );
					}
					if ( (int) count( $responses ) > 1 ) {
						?>
						<div class="module_grade"><?php echo __( 'Grade Pending.', 'cp' ); ?></div>
					<?php
					}
				}
			}
		}

		function time_estimation( $data ) {
			// var_dump($data->time_estimation);
			?>
			<div class="module_time_estimation"><?php _e( 'Time Estimation (mins)', 'cp' ); ?>
				<input type="text" name="<?php echo $this->name; ?>_time_estimation[]" value="<?php echo esc_attr( isset( $data->time_estimation ) ? $data->time_estimation : '1:00' ); ?>"/>
			</div>
		<?php
		}

		function get_module_move_link() {
			?>
			<span class="module_move"><i class="fa fa-th cp-move-icon"></i></span>
		<?php
		}

		public static function create_auto_draft( $unit_id ) {
			global $user_id;

			$post = array(
				'post_author'  => $user_id,
				'post_content' => '',
				'post_status'  => 'auto-draft',
				'post_type'    => 'module',
				'post_parent'  => $unit_id
			);

			$post_id = wp_insert_post( $post );

			self::kill( self::TYPE_MODULE, $post_id );
			self::kill( self::TYPE_UNIT_MODULES, $unit_id );

			return $post_id;
		}

		function duplicate( $module_id = '', $unit_id = '' ) {
			global $wpdb;

			if ( $module_id == '' ) {
				$module_id = $this->id;
			}

			/* Duplicate course and change some data */

			$new_module    = Unit_Module::get_module( $module_id );
			$old_module_id = $new_module->ID;

			unset( $new_module->ID );
			unset( $new_module->guid );

			$new_module->post_author = get_current_user_id();
			$new_module->post_status = 'publish';
			$new_module->post_parent = $unit_id;

			$new_module_id = wp_insert_post( $new_module );


			/*
			 * Duplicate module post meta
			 */

			if ( ! empty( $new_module_id ) ) {
				$post_metas = get_post_meta( $old_module_id );
				foreach ( $post_metas as $key => $meta_value ) {
					$value = array_pop( $meta_value );
					$value = maybe_unserialize( $value );
					update_post_meta( $new_module_id, $key, $value );
				}
			}

			// Set input module meta
			if ( isset( $post_metas ) ) {
				$input_module_types = self::get_input_module_types();
				$module_type        = $post_metas['module_type'];
				if ( in_array( $module_type, $input_module_types ) ) {

					$module_id   = $new_module_id;
					$module_meta = array(
						'mandatory_answer'       => isset( $post_metas['mandatory_answer'] ) ? $post_metas['mandatory_answer'] : false,
						'gradable_answer'        => isset( $post_metas['gradable_answer'] ) ? $post_metas['gradable_answer'] : false,
						'minimum_grade_required' => isset( $post_metas['minimum_grade_required'] ) ? $post_metas['minimum_grade_required'] : false,
						'limit_attempts'         => isset( $post_metas['limit_attempts'] ) ? $post_metas['limit_attempts'] : false,
						'limit_attempts_value'   => isset( $post_metas['limit_attempts_value'] ) ? $post_metas['limit_attempts_value'] : false,
					);

					Unit::update_input_module_meta( $unit_id, $module_id, $module_meta );
				}
			}

		}

		function get_module_delete_link() {
			?>
			<a class="delete_module_link" onclick="if ( deleteModule( jQuery( this ).parent().find( '.element_id' ).val() ) ) {jQuery( this ).parent().parent().remove(); update_sortable_module_indexes(); };"><i class="fa fa-trash-o"></i> <?php _e( 'Delete', 'cp' ); ?></a>
		<?php
		}

		public static function display_title_on_front( $data ) {
			$to_display = isset( $data->show_title_on_front ) && $data->show_title_on_front == 'yes' ? true : ( ! isset( $data->show_title_on_front ) ) ? true : false;

			return $to_display;
		}

		public static function get_response_comment( $response_id, $count = false ) {
			return get_post_meta( $response_id, 'response_comment', true );
		}

		public static function get_response_form( $user_ID, $response_request_ID, $show_label = true ) {
			//module does not overwrite this method message?
		}

		public static function get_response( $user_ID, $response_request_ID ) {

		}

		function on_create() {

		}

		function save_module_data() {

		}

		function admin_main( $data ) {

		}

		function add_oembeds( $html ) {

			$matches     = array();
			$new_content = '';
			$pre_half    = '';
			$post_half   = '';
			$p_offset    = 0;
			$p_length    = 0;
			$o_length    = 0;

			$content = str_replace( '</p>', '</p> ', $html );
			preg_match_all( "/(?<!href|src='|\")(https?:\/\/\S*)/i", $content, $matches, PREG_OFFSET_CAPTURE );

			if ( ! empty( $matches[0] ) ) {
				foreach ( $matches[0] as $match ) {
					$url    = str_replace( '</p>', '', $match[0] );
					$offset = $match[1];
					$length = strlen( $url );

					$embed = wp_oembed_get( $url );
					if ( ! empty( $embed ) ) {
						$new_offset = ( $offset - ( $p_offset + $o_length ) ) + ( $p_offset + $p_length );
						$pre_half   = substr( $content, 0, $new_offset );
						$post_half  = substr( $content, $new_offset + $length, strlen( $content ) - ( $new_offset + $length ) );
						$content    = $pre_half . $embed . $post_half;
						$p_offset   = $offset;
						$o_length   = $length;
						$p_length   = strlen( $embed );
					}
				}
			}

			return $content;
		}

		public static function get_input_module_types() {
			return array( 'checkbox_input_module', 'file_input_module', 'radio_input_module', 'text_input_module' );
		}

		public static function get_module_meta( $module_id ) {

			$input_modules = self::get_input_module_types();
			$module_type   = self::get_module_type( $module_id );

			// If not an input module, return something else. False in this case.
			if ( ! in_array( $module_type, $input_modules ) ) {
				return false;
			}

			$mandatory_answer       = get_post_meta( $module_id, 'mandatory_answer', true );
			$gradable_answer        = get_post_meta( $module_id, 'gradable_answer', true );
			$minimum_grade_required = get_post_meta( $module_id, 'minimum_grade_required', true );
			$limit_attempts         = get_post_meta( $module_id, 'limit_attempts', true );
			$limit_attempts_value   = get_post_meta( $module_id, 'limit_attempts_value', true );

			$module_meta = array(
				'mandatory_answer'       => ! empty( $mandatory_answer ) ? is_array( $mandatory_answer ) ? $mandatory_answer[0] : $mandatory_answer : array(),
				'gradable_answer'        => ! empty( $gradable_answer ) ? is_array( $gradable_answer ) ? $gradable_answer[0] : $gradable_answer : array(),
				'minimum_grade_required' => ! empty( $minimum_grade_required ) ? is_array( $minimum_grade_required ) ? $minimum_grade_required[0] : $minimum_grade_required : false,
				'limit_attempts'         => ! empty( $limit_attempts ) ? is_array( $limit_attempts ) ? $limit_attempts[0] : $limit_attempts : false,
				'limit_attempts_value'   => ! empty( $limit_attempts_value ) ? is_array( $limit_attempts_value ) ? $limit_attempts_value[0] : $limit_attempts_value : false,
			);

			return $module_meta;
		}

		public static function auto_grade_modules() {
			return self::$auto_grade_modules;
		}

	}

}
?>