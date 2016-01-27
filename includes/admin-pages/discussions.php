<?php
if ( ( isset( $_GET['action'] ) && $_GET['action'] == 'add_new' && isset( $_GET['page'] ) && $_GET['page'] == 'discussions' ) || isset( $_GET['action'] ) && $_GET['action'] == 'edit' && isset( $_GET['page'] ) && $_GET['page'] == 'discussions' ) {
	include( 'discussions-details.php' );
} else {
	if ( isset( $_GET['s'] ) ) {
		$s = $_GET['s'];
	} else {
		$s = '';
	}

	$page = $_GET['page'];

	if ( isset( $_POST['action'] ) && isset( $_POST['discussions'] ) ) {
		check_admin_referer( 'bulk-discussions' );

		$action = $_POST['action'];

		foreach ( $_POST['discussions'] as $discussion_value ) {
			if ( is_numeric( $discussion_value ) ) {
				$discussion_id     = ( int ) $discussion_value;
				$discussion        = new Discussion( $discussion_id );
				$discussion_object = $discussion->get_discussion();

				switch ( addslashes( $action ) ) {
					case 'delete':
						if ( current_user_can( 'manage_options' ) || current_user_can( 'coursepress_delete_discussion_cap' ) || ( current_user_can( 'coursepress_delete_my_course_discussion_cap' ) && $discussion_object->post_author == get_current_user_id() ) ) {
							$discussion->delete_discussion();
							$message = __( 'Selected discussions have been deleted successfully.', 'cp' );
						} else {
							$message = __( "You don't have right permissions to delete the discussion.", 'cp' );
						}
						break;
				}
			}
		}
	}

// Query the discussions
	if ( isset( $_GET['page_num'] ) ) {
		$page_num = ( int ) $_GET['page_num'];
	} else {
		$page_num = 1;
	}

	if ( isset( $_GET['s'] ) ) {
		$discussionsearch = $_GET['s'];
	} else {
		$discussionsearch = '';
	}

	$wp_discussion_search = new Discussion_Search( $discussionsearch, $page_num );

	if ( isset( $_GET['discussion_id'] ) ) {
		$discussion = new Discussion( $_GET['discussion_id'] );
	}

	if ( isset( $_GET['action'] ) && $_GET['action'] == 'delete' && isset( $_GET['discussion_id'] ) && is_numeric( $_GET['discussion_id'] ) ) {

		if ( ! isset( $_GET['cp_nonce'] ) || ! wp_verify_nonce( $_GET['cp_nonce'], 'delete_discussion_' . $_GET['discussion_id'] ) ) {
			die( __( 'Cheating huh?', 'cp' ) );
		}

		$discussion_object = $discussion->get_discussion();
		if ( current_user_can( 'manage_options' ) || current_user_can( 'coursepress_delete_discussion_cap' ) || ( current_user_can( 'coursepress_delete_my_course_discussion_cap' ) && $discussion_object->post_author == get_current_user_id() ) ) {
			$discussion->delete_discussion( $force_delete = true );
			$message = __( 'Selected discussion has been deleted successfully.', 'cp' );
		} else {
			$message = __( "You don't have right permissions to delete the discussion.", 'cp' );
		}
	}
	?>
	<div class="wrap nosubsub cp-wrap">
		<div class="icon32" id="icon-themes"><br></div>
		<h2><?php _e( 'Discussions', 'cp' ); ?><?php if ( current_user_can( 'manage_options' ) || current_user_can( 'coursepress_create_discussion_cap' ) || current_user_can( 'coursepress_create_my_discussion_cap' ) || current_user_can( 'coursepress_create_my_assigned_discussion_cap' ) ) { ?>
				<a class="add-new-h2" href="<?php echo admin_url( 'admin.php?page=discussions&action=add_new' ); ?>"><?php _e( 'Add New', 'cp' ); ?></a><?php } ?>
		</h2>

		<?php
		$ms['da'] = __( 'New Discussion added successfully!', 'cp' );
		$ms['du'] = __( 'Discussion updated successfully.', 'cp' );

		if ( isset( $_GET['ms'] ) ) {
			$message = $ms[ $_GET['ms'] ];
		}
		?>

		<?php
		if ( isset( $message ) ) {
			?>
			<div id="message" class="updated fade"><p><?php echo $message; ?></p></div>
		<?php
		}
		?>
		<div class="tablenav">

			<div class="alignright actions new-actions">
				<form method="get" action="<?php echo esc_attr( admin_url( 'admin.php?page=' . $page ) ); ?>" class="search-form">
					<p class="search-box">
						<input type='hidden' name='page' value='<?php echo esc_attr( $page ); ?>'/>
						<label class="screen-reader-text"><?php _e( 'Search Discussions', 'cp' ); ?>:</label>
						<input type="text" value="<?php echo esc_attr( $s ); ?>" name="s">
						<input type="submit" class="button" value="<?php _e( 'Search Discussions', 'cp' ); ?>">
					</p>
				</form>
			</div>
			<!--/alignright-->

			<form method="post" action="<?php echo esc_attr( admin_url( 'admin.php?page=' . $page ) ); ?>" id="posts-filter">

				<?php if ( current_user_can( 'manage_options' ) || current_user_can( 'coursepress_delete_discussion_cap' ) ) { ?>
					<div class="alignleft actions">
						<select name="action">
							<option selected="selected" value=""><?php _e( 'Bulk Actions', 'cp' ); ?></option>
							<option value="delete"><?php _e( 'Delete', 'cp' ); ?></option>
						</select>
						<input type="submit" class="button-secondary action" id="doaction" name="doaction" value="<?php _e( 'Apply', 'cp' ); ?>"/>
					</div>
				<?php } ?>


				<br class="clear">

		</div>
		<!--/tablenav-->


		<?php
		wp_nonce_field( 'bulk-discussions' );

		$columns = array(
			"discussion_title" => __( 'Discussion', 'cp' ),
			"course"           => __( 'Course', 'cp' ),
		);


		$col_sizes = array(
			'3',
			'67',
			'25',
			'5'
		);

		if ( current_user_can( 'manage_options' ) || current_user_can( 'coursepress_delete_discussion_cap' ) || ( current_user_can( 'coursepress_delete_my_course_discussion_cap' ) ) ) {
			$columns["remove"] = __( 'Remove', 'cp' );
			$col_sizes[]       = '7';
		}
		?>

		<table cellspacing="0" class="widefat shadow-table">
			<thead>
			<tr>
				<th style="" class="manage-column column-cb check-column" id="cb" scope="col" width="<?php echo $col_sizes[0] . '%'; ?>">
					<input type="checkbox"></th>
				<?php
				$n = 1;
				foreach ( $columns as $key => $col ) {
					?>
					<th style="" class="manage-column column-<?php echo $key; ?>" width="<?php echo $col_sizes[ $n ] . '%'; ?>" id="<?php echo $key; ?>" scope="col"><?php echo $col; ?></th>
					<?php
					$n ++;
				}
				?>
			</tr>
			</thead>

			<tbody>
			<?php
			$style = '';

			foreach ( $wp_discussion_search->get_results() as $discussion ) {

				$discussion_obj    = new Discussion( $discussion->ID );
				$discussion_object = $discussion_obj->get_discussion();
				$style             = ( ' class="alternate"' == $style ) ? '' : ' class="alternate"';
				?>
				<tr id='user-<?php echo $discussion_object->ID; ?>' <?php echo $style; ?>>
					<th scope='row' class='check-column'>
						<input type='checkbox' name='discussions[]' id='user_<?php echo $discussion_object->ID; ?>' class='' value='<?php echo $discussion_object->ID; ?>'/>
					</th>
					<td <?php echo $style; ?>>
						<a href="<?php echo admin_url( 'admin.php?page=discussions&action=edit&discussion_id=' . $discussion_object->ID ); ?>"><strong><?php echo $discussion_object->post_title; ?></strong></a><br/>

						<div class="course_excerpt"><?php echo cp_get_the_course_excerpt( $discussion_object->ID ); ?></div>
						<div class="row-actions">
							<span class="edit_discussion"><a href="<?php echo admin_url( 'admin.php?page=discussions&action=edit&discussion_id=' . $discussion_object->ID ); ?>"><?php _e( 'Edit', 'cp' ); ?></a> | </span>

							<?php if ( current_user_can( 'manage_options' ) || current_user_can( 'coursepress_delete_discussion_cap' ) || ( current_user_can( 'coursepress_delete_my_course_discussion_cap' ) && $discussion_object->post_author == get_current_user_id() ) ) { ?>
								<span class="course_remove"><a href="<?php echo wp_nonce_url( admin_url( 'admin.php?page=discussions&action=delete&discussion_id=' . $discussion_object->ID ), 'delete_discussion_' . $discussion_object->ID, 'cp_nonce' ); ?>" onClick="return removeDiscussion();"><?php _e( 'Delete', 'cp' ); ?></a></span>
							<?php } ?>
						</div>
					</td>
					<?php
					if ( isset( $discussion_object->course_id ) && $discussion_object->course_id !== '' ) {
						$course      = new Course( $discussion_object->course_id );
						$course_name = $course->details->post_title;
					} else {
						$course_name = __( 'All Courses', 'cp' );
					}
					?>
					<td <?php echo $style; ?>> <?php echo $course_name; ?> </td>

					<?php if ( current_user_can( 'manage_options' ) || current_user_can( 'coursepress_delete_discussion_cap' ) || ( current_user_can( 'coursepress_delete_my_course_discussion_cap' ) ) ) { ?>
						<td <?php echo $style; ?>>
							<?php if ( current_user_can( 'manage_options' ) || current_user_can( 'coursepress_delete_discussion_cap' ) || ( current_user_can( 'coursepress_delete_my_course_discussion_cap' ) && $discussion_object->post_author == get_current_user_id() ) ) { ?>
								<a href="<?php echo wp_nonce_url( admin_url( 'admin.php?page=discussions&action=delete&discussion_id=' . $discussion_object->ID ), 'delete_discussion_' . $discussion_object->ID, 'cp_nonce' ); ?>" onClick="return removeDiscussion();">
									<i class="fa fa-times-circle cp-move-icon remove-btn"></i>
								</a>
							<?php } ?>
						</td>
					<?php } ?>
				</tr>
			<?php
			}
			?>

			<?php
			if ( count( $wp_discussion_search->get_results() ) == 0 ) {
				?>
				<tr>
					<td colspan="6">
						<div class="zero-courses"><?php _e( 'No discussions found.', 'cp' ) ?></div>
					</td>
				</tr>
			<?php
			}
			?>
			</tbody>
		</table>
		<!--/widefat shadow-table-->

		<div class="tablenav">
			<div class="tablenav-pages"><?php $wp_discussion_search->page_links(); ?></div>
		</div>
		<!--/tablenav-->

		</form>

	</div><!--/wrap-->

<?php } ?>