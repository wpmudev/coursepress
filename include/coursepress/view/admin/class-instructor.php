<?php

class CoursePress_View_Admin_Instructor {
	public static $slug = 'coursepress_instructors';
	private static $title = '';
	private static $menu_title = '';

	private static $table_manager = null;

	public static function init() {
		self::$title = __( 'Courses/Instructors', 'coursepress' );
		self::$menu_title = __( 'Instructors', 'coursepress' );

		add_filter(
			'coursepress_admin_valid_pages',
			array( __CLASS__, 'add_valid' )
		);
		add_filter(
			'coursepress_admin_pages',
			array( __CLASS__, 'add_page' )
		);

		add_filter(
			'coursepress_admin_valid_pages',
			array( __CLASS__, 'add_valid' )
		);
		add_action(
			'coursepress_admin_' . self::$slug,
			array( __CLASS__, 'render_page' )
		);

		add_action(
			'coursepress_settings_page_pre_render_' . self::$slug,
			array( __CLASS__, 'pre_process' )
		);

		add_action(
			'coursepress_course_deleted',
			array( __CLASS__, 'update_instructor_meta' )
		);
	}

	public static function add_valid( $valid_pages ) {
		$valid_pages[] = self::$slug;

		return $valid_pages;
	}

	public static function add_page( $pages ) {
		$pages[ self::$slug ] = array(
			'title' => self::$title,
			'menu_title' => self::$menu_title,
			'cap' => self::$slug . '_cap',
			'order' => 25,
		);

		return $pages;
	}

	public static function pre_process() {
		if ( empty( $_GET['action'] ) ) {
			self::$table_manager = new CoursePress_Helper_Table_Instructor;
			self::$table_manager->prepare_items();
		} elseif ( 'delete' == $_GET['action'] && isset( $_GET['instructor_id'] ) ) {
			$instructor_id = (int) $_GET['instructor_id'];
			self::remove_instructor( $instructor_id );

			$query_arg = array(
				'action',
				'instructor_id',
				'nonce',
			);
			$redirect = remove_query_arg( $query_arg );
			wp_safe_redirect( $redirect ); exit;
		}

		if ( ! empty( $_POST['action'] )
			&& 'remove' == $_POST['action']
			&& ! empty( $_POST['users'] )
		) {
			$instructor_ids = (array) $_POST['users'];
			$instructor_ids = array_filter( $instructor_ids );
			array_map(
				array( __CLASS__, 'remove_instructor' ),
				$instructor_ids
			);
		}
	}

	public static function remove_instructor( $instructor_id ) {
		$instructor = get_userdata( $instructor_id );
		$assigned_courses = CoursePress_Data_Instructor::get_assigned_courses_ids( $instructor );

		if ( ! empty( $assigned_courses ) ) {
			foreach ( $assigned_courses as $course_id ) {
				CoursePress_Data_Course::remove_instructor( $course_id, $instructor_id );
			}
		}
	}

	public static function render_page() {
		if ( empty( $_GET['action'] ) ) {
			self::$table_manager->display();
		} elseif ( 'view' == $_GET['action'] ) {
			self::instructor_profile();
		}
	}

	public static function instructor_profile() {
		$instructor_id = (int) $_GET['instructor_id'];
		$instructor = get_userdata( $instructor_id );
		$page = get_query_var( 'paged' );
		?>
		<div class="wrap nocoursesub cp-wrap">
			<div class="course-liquid-left">
				<div id="course-left">
					<div class="course-holder-wrap" id="edit-sub">
						<div class="sidebar-name no-movecursor">
							<h3><?php esc_html_e( 'Courses', 'coursepress' ); ?></h3>
						</div>

						<table cellspacing="0" class="widefat shadow-table">
							<?php
								$style = '';
								$date_format = get_option( 'date_format' );
								$assigned_courses = CoursePress_Data_Instructor::get_assigned_courses_ids( $instructor );
								$assigned_courses = array_filter( $assigned_courses );

								$args = array(
									'post_type' => CoursePress_Data_Course::get_post_type_name(),
									'post_status' => array( 'publish', 'draft' ),
									'post__in' => $assigned_courses,
								);
								$query = new WP_Query( $args );

								if ( $query->have_posts() ) :
									while ( $query->have_posts() ) :
										$query->the_post();
										$style = ( ' class="alternate"' == $style ) ? '' : ' class="alternate"';
										$course = CoursePress_Data_Course::get_course( get_the_ID() )
										?>
										<tr <?php echo $style; ?>>
											<td>
												<a href="<?php echo $course->edit_link; ?>" class="course-title"><?php the_title(); ?></a>
												<?php the_excerpt(); ?>
											</td>
											<td style="width:25%;">
												<div class="course_additional_info">
													<div>
														<span class="info_caption"><?php esc_html_e( 'Start', 'coursepress' ); ?></span>
														<span class="info">
															<?php echo $course->start_date; ?>
														</span>
													</div>
													<div>
														<span class="info_caption"><?php esc_html_e( 'End', 'coursepress' ); ?></span>
														<span class="info"><?php echo $course->end_date; ?></span>
													</div>
													<div>
														<span class="info_caption"><?php esc_html_e( 'Duration', 'coursepress' ); ?></span>
														<span class="info"><?php echo $course->duration; ?></span>
													</div>
												</div>
											</td>
										</tr>
										<?php
									endwhile;
								endif;

								wp_reset_postdata();
							?>
						</table>
					</div>
				</div>
			</div>

			<div class="course-liquid-right">
				<div class="course-holder-wrap">
					<div class="sidebar-name no-movecursor">
						<h3><?php esc_html_e( 'Profile', 'coursepress' ); ?></h3>
					</div>
					<div class="instructor-profile-holder" id="sidebar-levels">
						<div class="sidebar-inner">
							<div class="instructors-info" id="instructors-info">
								<table cellspacing="0" class="widefat instructor-profile">
									<tbody>
										<tr>
											<td><?php echo get_avatar( $instructor, 80 ); ?></td>
											<td>
												<div class="instructor_additional_info">
													<div>
														<span class="info_caption"><?php esc_html_e( 'First Name', 'coursepress' ); ?></span>
														<span class="info"><?php echo $instructor->first_name; ?></span>
													</div>
													<div>
														<span class="info_caption"><?php esc_html_e( 'Last Name', 'coursepress' ); ?></span>
														<span class="info"><?php echo $instructor->last_name; ?></span>
													</div>
													<div>
														<span class="info_caption"><?php esc_html_e( 'Email', 'coursepress' ); ?></span>
														<span class="info">
															<a href="mailto:<?php echo $instructor->user_email; ?>"><?php echo $instructor->user_email; ?></a>
														</span>
													</div>
												</div>
											</td>
										</tr>
									</tbody>
								</table>

								<div class="edit-profile-link">
									<a href="<?php echo get_edit_user_link( $instructor_id ); ?>"><?php esc_html_e( 'Edit Profile', 'coursepress' ); ?></a>
								</div>
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>
		<?php
	}

	public static function update_instructor_meta( $course_id ) {
		CoursePress_Helper_Utility::delete_user_meta_by_key( 'course_' . $course_id );
	}
}
