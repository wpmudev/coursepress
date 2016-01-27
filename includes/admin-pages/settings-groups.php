<?php
if ( isset( $_POST['item_title'] ) ) {
	sort( $_POST['item_title'] );
	$groups = $_POST['item_title'];
	update_option( 'course_groups', $groups );
}
?>

<?php $page = $_GET['page']; ?>

<div id="poststuff" class="metabox-holder m-settings cp-wrap">
	<form action='' method='post'>

		<input type='hidden' name='page' value='<?php echo esc_attr( $page ); ?>'/>
		<input type='hidden' name='action' value='updateoptions'/>

		<?php
		wp_nonce_field( 'update-coursepress-options' );
		?>
		<div class="postbox">
			<h3 class="hndle" style='cursor:auto;'><span><?php _e( 'Groups', 'cp' ); ?></span></h3>

			<div class="inside">
				<p class='description'><?php _e( 'Manage default course Groups', 'cp' ); ?></p>
				<table class="form-table">
					<tbody id="items">
					<tr>
						<th><strong><?php _e( 'Group Name', 'cp' ); ?></strong></th>
						<th><a href="javascript:new_link();"><?php _e( 'Add New', 'cp' ); ?></a></th>
					</tr>

					<?php
					$i = 1;

					$groups = get_option( 'course_groups' );
					if ( count( $groups ) >= 1 && $groups != '' ) {
						foreach ( $groups as $group ) {
							?>
							<tr id="r<?php echo $i; ?>">
								<td width="20%">
									<input type="text" style="width:100%;" value="<?php echo $group; ?>" name="item_title[]">
								</td>
								<td width="10%"><?php //if ( $i != 1 ) {
									?>
									<a href="javascript:removeElement( 'items','r<?php echo $i; ?>' );"><?php _e( 'Remove', 'cp' ); ?></a>
									<?php
									//}
									?></td>
							</tr>
							<?php
							$i ++;
						}
					}

					/* } else {
					  ?>
					  <tr id="r1">
					  <td width="20%"><input type="text" style="width:100%;" value="" name="item_title[]"></td>
					  </tr>
					  <?php } */
					?>
					</tbody>
				</table>
			</div>
			<!--/inside-->

		</div>
		<!--/postbox-->

		<p class="save-shanges">
			<?php submit_button( __( 'Save Changes', 'cp' ) ); ?>
		</p>

	</form>
</div><!--/poststuff-->