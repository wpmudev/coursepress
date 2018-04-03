<?php
/**
 * @var $columns
 * @var $hidden_columns
 * @var $courses
 * @var $course_edit_link
 * @var $course CoursePress_Course
 */
?>
	<div class="wrap coursepress-wrap coursepress-instructors" id="coursepress-instructors">
		<h1 class="wp-heading-inline"><?php _e( 'Instructors', 'cp' ); ?></h1>
		<div class="coursepress-page">
			<form method="get" class="cp-action-form" id="cp-search-form">
				<div class="cp-flex">
					<div class="cp-div">
						<label class="label"><?php _e( 'Filter by course', 'cp' ); ?></label>
						<div class="cp-input-clear">
							<select name="course_id">
								<option value=""><?php _e( 'Any course', 'cp' ); ?></option>
								<?php
								$current = isset( $_REQUEST['course_id'] ) ? $_REQUEST['course_id'] : 0;
								foreach ( $courses as $course_id => $course ) {
									printf(
										'<option value="%d" %s>%s</option>',
										esc_attr( $course_id ),
										selected( $current, $course_id ),
										esc_html( $course->post_title . $course->get_numeric_identifier_to_course_name( $course->ID ) )
									);
								}
								?>
							</select>
						</div>
					</div>

					<div class="cp-div">
						<label
							class="label"><?php _e( 'Search instructors by name, username or email', 'cp' ); ?></label>
						<div class="cp-input-clear">
							<input type="hidden" name="page" value="<?php echo esc_attr( $page ); ?>"/>
							<input type="text" name="instructor_search"
							       placeholder="<?php _e( 'Type here...', 'cp' ); ?>" value="<?php echo $search; ?>"/>
							<button type="button" id="cp-search-clear"
							        class="cp-btn-clear"><?php _e( 'Clear', 'cp' ); ?></button>
						</div>
						<button type="submit" class="cp-btn cp-btn-active"><?php _e( 'Search', 'cp' ); ?></button>
					</div>
				</div>
			</form>

			<table class="coursepress-table" cellspacing="0">
				<thead>
				<tr>
					<?php foreach ( $columns as $column_id => $column_label ) { ?>
						<th class="manage-column column-<?php echo $column_id;
						echo in_array( $column_id, $hidden_columns ) ? ' hidden' : ''; ?>"
						    id="<?php echo $column_id; ?>">
							<?php echo $column_label; ?>
						</th>
					<?php } ?>
				</tr>
				</thead>
				<tbody>
				<?php
				$i = 0;
				if ( ! empty( $instructors ) ) {
					$date_format = get_option( 'date_format' );
					foreach ( $instructors as $instructor ) {
						$i ++;
						$edit_link = add_query_arg( 'cid', $instructor->ID, $instructor_edit_link );
						?>
						<tr class="<?php echo $i % 2 ? 'odd' : 'even'; ?>">

							<?php foreach ( array_keys( $columns ) as $column_id ) { ?>
								<td class="column-<?php echo $column_id;
								echo in_array( $column_id, $hidden_columns ) ? ' hidden' : ''; ?>">
									<?php
									switch ( $column_id ) :
										case 'id' :
											echo $instructor->ID;
											break;
										case 'instructor':
											echo '<div class="cp-flex cp-user">';
											echo '<span class="gravatar">';
											echo get_avatar( $instructor->email, 30 );
											echo '</span>';
											echo ' ';
											echo '<span class="user_login">';
											echo $instructor->user_login;
											echo '</span>';
											echo ' ';
											echo '<span class="display_name">(';
											echo $instructor->display_name;
											echo ')</span>';
											echo '</div>';
											break;
										case 'registered':
											echo date_i18n( $date_format, strtotime( $instructor->user_registered ) );
											break;
										case 'courses':
											printf(
												'<a href="%s">%d</a>',
												esc_url( $instructor->courses_link ),
												esc_html( $instructor->count_courses )
											);
											break;

										default :
											echo $column_id;
											/**
											 * Trigger to allow custom column value
											 *
											 * @since 3.0
											 *
											 * @param string $column_id
											 * @param CoursePress_Course object $instructor
											 */
											do_action( 'coursepress_courselist_column', $column_id, $instructor );
											break;
									endswitch;
									?>
								</td>
							<?php } ?>
						</tr>
						<tr class="<?php echo $i % 2 ? 'odd' : 'even'; ?> column-actions hidden">
							<td colspan="<?php echo count( $columns ) + 2; ?>" data-id="<?php echo $instructor->ID; ?>">
								<div class="cp-row-actions">
									<a href="<?php echo $edit_link; ?>" data-step="course-type"
									   class="cp-reset-step cp-edit-overview"><?php _e( 'Course Overview', 'cp' ); ?></a>
									|
									<a href="<?php echo $edit_link; ?>" data-step="course-units"
									   class="cp-reset-step cp-edit-units"><?php _e( 'Units', 'cp' ); ?></a> |
									<a href="<?php echo $edit_link; ?>" data-step="course-settings"
									   class="cp-reset-step cp-edit-settings"><?php _e( 'Display Settings', 'cp' ); ?></a>

									<div class="cp-dropdown">
										<button type="button" class="cp-btn-xs cp-dropdown-btn">
											<?php _e( 'More', 'cp' ); ?>
										</button>
										<ul class="cp-dropdown-menu">
											<li class="menu-item-students">
												<a href="<?php echo $edit_link; ?>" data-step="course-students"
												   class="cp-reset-step"><?php _e( 'Students', 'cp' ); ?></a>
											</li>
											<li class="menu-item-duplicate-course">
												<a href="<?php echo add_query_arg( array(
													'course_id' => $instructor->ID,
													'_wpnonce'  => wp_create_nonce( 'duplicate_course' ),
													'cp_action' => 'duplicate_course'
												) ); ?>"><?php _e( 'Duplicate Course', 'cp' ); ?></a>
											</li>
											<li class="menu-item-export">
												<a href=""><?php _e( 'Export', 'cp' ); ?></a>
											</li>
											<li class="menu-item-view-course">
												<a href="<?php echo esc_url( $instructor->get_permalink() ); ?>"
												   target="_blank"><?php _e( 'View Course', 'cp' ); ?></a>
											</li>
											<li class="menu-item-delete cp-delete">
												<a href=""><?php _e( 'Delete Course', 'cp' ); ?></a>
											</li>
										</ul>
									</div>
								</div>
							</td>
						</tr>
						<?php
					}
				} else {
					?>
					<tr class="odd">
						<td colspan="<?php echo count( $columns ); ?>">
							<?php _e( 'No instructors found.', 'cp' ); ?>
						</td>
					</tr>
				<?php } ?>
				</tbody>
			</table>
			<?php if ( ! empty( $pagination ) ) : ?>
				<div class="tablenav cp-admin-pagination">
					<?php $pagination->pagination( 'bottom' ); ?>
				</div>
			<?php endif; ?>
		</div>
	</div>
<?php

