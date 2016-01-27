<?php
global $cp_template_elements;

$templates             = new CP_Certificate_Templates();
$template_elements     = new CP_Certificate_Template_Elements();
$template_elements_set = array();
$page                  = $_GET['page'];

if ( isset( $_POST['add_new_template'] ) ) {
	if ( check_admin_referer( 'save_template' ) ) {
		if ( current_user_can( 'coursepress_create_certificates_cap' ) || current_user_can( 'manage_options' ) ) {
			$templates->add_new_template();
			$message = __( 'Certificate Template data has been successfully saved.', 'cp' );
		} else {
			$message = __( 'You do not have required permissions for this action.', 'cp' );
		}
	}
}

if ( isset( $_GET['action'] ) && $_GET['action'] == 'edit' && ( current_user_can( 'coursepress_update_certificates_cap' ) || current_user_can( 'manage_options' ) ) ) {
	$post_id               = (int) $_GET['ID'];
	$template              = new CP_Certificate_Template( $post_id );
	$template_elements     = new CP_Certificate_Template_Elements( $post_id );
	$template_elements_set = $template_elements->get_all_set_elements();
}

if ( isset( $_GET['action'] ) && $_GET['action'] == 'delete' ) {
	if ( ! isset( $_POST['_wpnonce'] ) ) {
		check_admin_referer( 'delete_' . $_GET['ID'] );
		if ( current_user_can( 'coursepress_delete_certificates_cap' ) || current_user_can( 'manage_options' ) ) {
			$template = new CP_Certificate_Template( (int) $_GET['ID'] );
			$template->delete_template();
			$message = __( 'Certificate Template has been successfully deleted.', 'cp' );
		} else {
			$message = __( 'You do not have required permissions for this action.', 'cp' );
		}
	}
}

if ( isset( $_GET['page_num'] ) ) {
	$page_num = (int) $_GET['page_num'];
} else {
	$page_num = 1;
}

if ( isset( $_GET['s'] ) ) {
	$templatessearch = $_GET['s'];
} else {
	$templatessearch = '';
}

$wp_templates_search = new CP_Certificate_Templates_Search( $templatessearch, $page_num );
$fields              = $templates->get_template_col_fields();
$columns             = $templates->get_columns();
?>
<div class="wrap cp-wrap certificates-wrap">
	<h2><?php _e( 'Certificate Templates', 'cp' ); ?><?php if ( isset( $_GET['action'] ) && ( $_GET['action'] == 'edit' || $_GET['action'] == 'add_new' ) ) { ?>
			<a href="admin.php?page=<?php echo $_GET['page']; ?>" class="add-new-h2"><?php _e( 'Back', 'cp' ); ?></a><?php } else { ?>
			<a href="<?php echo admin_url( 'admin.php?page=' . $_GET['page'] . '&action=add_new' ); ?>" class="add-new-h2"><?php _e( 'Add New', 'cp' ); ?></a><?php } ?>
	</h2>

<?php
if ( isset( $message ) ) {
	?>
	<div id="message" class="updated fade"><p><?php echo $message; ?></p></div>
<?php
}
?>

<?php if ( ! isset( $_GET['action'] ) || isset( $_POST['template_id'] ) ) { ?>
	<div class="tablenav">
		<div class="alignright actions new-actions">
			<form method="get" action="?page=<?php echo esc_attr( $page ); ?>" class="search-form">
				<p class="search-box">
					<input type='hidden' name='page' value='<?php echo esc_attr( $page ); ?>'/>
					<label class="screen-reader-text"><?php _e( 'Search Templates', 'cp' ); ?>:</label>
					<input type="text" value="<?php echo esc_attr( $templatessearch ); ?>" name="s">
					<input type="submit" class="button" value="<?php _e( 'Search Templates', 'cp' ); ?>">
				</p>
			</form>
		</div>
		<!--/alignright-->

	</div><!--/tablenav-->

	<table cellspacing="0" class="widefat shadow-table">
		<thead>
		<tr>
			<?php
			$n = 1;
			foreach ( $columns as $key => $col ) {
				?>
				<th style="" class="manage-column column-<?php echo $key; ?>" width="<?php echo( isset( $col_sizes[ $n ] ) ? $col_sizes[ $n ] . '%' : '' ); ?>" id="<?php echo $key; ?>" scope="col"><?php echo $col; ?></th>
				<?php
				$n ++;
			}
			?>
		</tr>
		</thead>

		<tbody>
		<?php
		$style = '';

		foreach ( $wp_templates_search->get_results() as $template ) {

			$template_obj    = new CP_Certificate_Template( $template->ID );
			$template_object = apply_filters( 'coursepress_template_object_details', $template_obj->details );

			$style = ( ' class="alternate"' == $style ) ? '' : ' class="alternate"';
			?>
			<tr id='user-<?php echo $template_object->ID; ?>' <?php echo $style; ?>>
				<?php
				$n = 1;
				foreach ( $columns as $key => $col ) {
					if ( $key == 'edit' ) {
						?>
						<td>
							<a class="templates_edit_link" href="<?php echo admin_url( 'admin.php?page=' . $page . '&action=' . $key . '&ID=' . (int) $template_object->ID, 'save_template' ); ?>"><?php _e( 'Edit', 'cp' ); ?></a>
						</td>
					<?php } elseif ( $key == 'delete' ) {
						?>
						<td>
							<a class="templates_edit_link cp_delete_link" href="<?php echo wp_nonce_url( 'admin.php?page=' . $page . '&action=' . $key . '&ID=' . (int) $template_object->ID, 'delete_' . (int) $template_object->ID ); ?>"><?php _e( 'Delete', 'cp' ); ?></a>
						</td>
					<?php
					} else {
						?>
						<td>
							<?php echo apply_filters( 'coursepress_template_field_value', $template_object->$key ); ?>
						</td>
					<?php
					}
				}
				?>
			</tr>
		<?php
		}
		?>

		<?php
		if ( count( $wp_templates_search->get_results() ) == 0 ) {
			?>
			<tr>
				<td colspan="6">
					<div class="zero-records"><?php _e( 'No templates found.', 'cp' ) ?></div>
				</td>
			</tr>
		<?php
		}
		?>
		</tbody>
	</table><!--/widefat shadow-table-->

	<div class="tablenav">
		<div class="tablenav-pages"><?php $wp_templates_search->page_links(); ?></div>
	</div><!--/tablenav-->

<?php } else { ?>

	<form action="" method="post" enctype="multipart/form-data">
		<input type="hidden" name="template_id" value="<?php echo esc_attr( isset( $_GET['ID'] ) ? (int) $_GET['ID'] : '' ); ?>"/>
		<?php wp_nonce_field( 'save_template' ); ?>
		<?php
		if ( isset( $post_id ) ) {
			?>
			<input type="hidden" name="post_id" value="<?php echo $post_id; ?>"/>
		<?php
		}
		?>

		<div id="poststuff">
			<div id="post-body" class="metabox-holder columns-2">
				<div id="post-body-content">

					<div id="titlediv">
						<div id="titlewrap">
							<label class="" id="title-prompt-text" for="title"></label>
							<input type="text" name="template_title" size="30" value="<?php echo esc_attr( isset( $template->details->post_title ) ? $template->details->post_title : '' ); ?>" id="title" placeholder="<?php _e( 'Certificate Template Title', 'cp' ); ?>" autocomplete="off">
						</div>
					</div>

					<div id="wp-content-wrap" class="wp-core-ui wp-editor-wrap tmce-active has-dfw certificate-layout">
						<h2><?php _e( 'Certificate Layout', 'cp' ); ?></h2>

						<div class="rows">
							<?php for ( $i = 1; $i <= apply_filters( 'coursepress_certificate_template_row_number', 20 ); $i ++ ) { ?>
								<ul id="row_<?php echo $i; ?>" class="sortables droptrue">
									<!--<span class="row_num_info"><?php _e( 'Row', 'cp' ); ?> <?php echo $i; ?></span>--><input type="hidden" class="rows_classes" name="rows_<?php echo $i; ?>_post_meta" value=""/>
									<i class="fa fa-arrows-v cp-move-icon"></i>
									<?php
									if ( isset( $post_id ) ) {
										$rows_elements = get_post_meta( $post_id, 'rows_' . $i, true );
										if ( isset( $rows_elements ) && $rows_elements !== '' ) {
											$element_class_names = explode( ',', $rows_elements );
											foreach ( $element_class_names as $element_class_name ) {
												if ( class_exists( $element_class_name ) ) {
													if ( isset( $post_id ) ) {
														$element = new $element_class_name( $post_id );
													} else {
														$element = new $element_class_name;
													}
													?>
													<li class="ui-state-default cols" data-class="<?php echo $element_class_name; ?>">
														<div class="element_title"><?php echo $element->element_title; ?></div>
														<div class="element_content"><?php $element->admin_content(); ?></div>
													</li>
												<?php
												}
											}
										}
									}
									?>
								</ul>
							<?php } ?>
						</div>
						<input type="hidden" name="rows_number_post_meta" value="<?php echo apply_filters( 'coursepress_certificate_template_row_number', 20 ); ?>"/>
					</div>
					<!--wp-content-wrap-->
				</div>
				<!--post-body-content-->

				<div id="postbox-container-1" class="postbox-container">
					<div id="side-sortables" class="meta-box-sortables ui-sortable" style="">
						<div id="submitdiv" class="postbox ">
							<h3 class="hndle"><span><?php _e( 'Certificate Elements', 'cp' ); ?></span></h3>

							<div class="inside">
								<div class="submitbox" id="submitpost">

									<div id="minor-publishing">

										<div id="minor-publishing-actions">
											<div class="misc-pub-section">
												<ul class="draggable droptrue sortables" id="certificate_elements">
													<!-- droptrue sortables-->
													<?php
													foreach ( $cp_template_elements as $element ) {
														$element_class = new $element[0];

														//if ( !in_array( $element[ 0 ], $template_elements_set ) ) {
														?>
														<li class="ui-state-default" data-class="<?php echo $element[0]; ?>">
															<div class="element_title"><?php echo $element[1]; ?></div>
															<div class="element_content">
																<?php echo $element_class->admin_content(); ?>
															</div>
														</li>
														<?php
														//}
													}
													?>
												</ul>
											</div>

										</div>
										<div class="clear"></div>
									</div>
								</div>

							</div>
						</div>

						<!--document settings-->

						<div id="submitdiv" class="postbox ">
							<h3 class="hndle"><span><?php _e( 'Certificate PDF Settings', 'cp' ); ?></span></h3>

							<div class="inside">
								<div class="submitbox" id="submitpost">

									<div id="minor-publishing">

										<div id="minor-publishing-actions">
											<div class="misc-pub-section">
												<?php $template_elements->tcpdf_get_fonts(); ?>
											</div>
											<div class="misc-pub-section">
												<?php $template_elements->get_document_sizes(); ?>
											</div>
											<div class="misc-pub-section">
												<?php $template_elements->get_document_orientation(); ?>
											</div>
											<div class="misc-pub-section">
												<?php $template_elements->get_document_margins( 10, 10, 10 ); ?>
											</div>
											<div class="misc-pub-section">
												<?php $template_elements->get_full_background_image(); ?>
											</div>
											<?php
											do_action( 'coursepress_template_document_settings' );
											?>
										</div>
										<div class="clear"></div>
									</div>

									<div id="major-publishing-actions">
										<div id="delete-action">
											<a class="preview button" href="" target="wp-preview-695" id="post-preview"><?php _e( 'Preview', 'cp' ) ?></a>
										</div>

										<div id="publishing-action">
											<?php submit_button( __( 'Save', 'cp' ), 'primary', 'add_new_template', false ); ?>
										</div>
										<div class="clear"></div>
									</div>
								</div>

							</div>
						</div>

					</div>
				</div>


			</div>
			<!--post-body-->

		</div>
		<!--post stuff-->
	</form>
	</div><!--wrap-->
<?php } ?>