<?php
/*
MarketPress Statistics
*/

class MarketPress_Stats {
	
	var $mp;
	
  function __construct() {
		global $mp;

		add_action( 'wp_dashboard_setup', array(&$this, 'register_dashboard_widget') );
		add_action( 'right_now_table_end', array(&$this, 'rightnow_dashboard_widget') );
		
	}

  function install() {

  }
	
	function register_dashboard_widget() {
		if ( !current_user_can('manage_options') )
			return;
		
		$screen = get_current_screen();
		add_meta_box( 'mp_stats_widget', (is_multisite() ? __( 'Store Statistics', 'mp' ) : __( 'MarketPress Statistics', 'mp' )), array(&$this, 'dashboard_widget'), $screen->id, 'normal', 'core' );
	}
	
	function dashboard_widget() {
		global $wpdb, $mp;
		$year = date('Y');
		$month = date('m');
		$this_month = $wpdb->get_row("SELECT count(p.ID) as count, sum(m.meta_value) as total, avg(m.meta_value) as average FROM $wpdb->posts p JOIN $wpdb->postmeta m ON p.ID = m.post_id WHERE p.post_type = 'mp_order' AND m.meta_key = 'mp_order_total' AND YEAR(p.post_date) = $year AND MONTH(p.post_date) = $month AND p.post_status != 'trash'");
		
		$year = date('Y', strtotime('-1 month'));
		$month = date('m', strtotime('-1 month'));
		$last_month = $wpdb->get_row("SELECT count(p.ID) as count, sum(m.meta_value) as total, avg(m.meta_value) as average FROM $wpdb->posts p JOIN $wpdb->postmeta m ON p.ID = m.post_id WHERE p.post_type = 'mp_order' AND m.meta_key = 'mp_order_total' AND YEAR(p.post_date) = $year AND MONTH(p.post_date) = $month AND p.post_status != 'trash'");	
		
		//later get full stats and graph
		//$stats = $wpdb->get_results("SELECT DATE_FORMAT(p.post_date, '%Y-%m') as date, count(p.ID) as count, sum(m.meta_value) as total, avg(m.meta_value) as average FROM $wpdb->posts p JOIN $wpdb->postmeta m ON p.ID = m.post_id WHERE p.post_type = 'mp_order' AND m.meta_key = 'mp_order_total' GROUP BY YEAR(p.post_date), MONTH(p.post_date) ORDER BY date DESC");
		?>
		<div class="table table_content">
			<p class="sub"><?php printf(__('This Month (%s)', 'mp'), date_i18n('M, Y')); ?></p>
			<table>
				<tbody>
					<tr class="first">
						<td class="first b<?php echo ($this_month->count >= $last_month->count) ? ' green' : ' red'; ?>"><?php echo number_format_i18n($this_month->count); ?></td>
						<td class="t"><?php _e('Orders', 'mp'); ?></td>
					</tr>	
					<tr>
						<td class="first b<?php echo ($this_month->total >= $last_month->total) ? ' green' : ' red'; ?>"><?php echo $mp->format_currency(false, $this_month->total); ?></td>
						<td class="t"><?php _e('Orders Total', 'mp'); ?></td>
					</tr>
					<tr>
						<td class="first b<?php echo ($this_month->average >= $last_month->average) ? ' green' : ' red'; ?>"><?php echo $mp->format_currency(false, $this_month->average); ?></td>
						<td class="t"><?php _e('Average Order', 'mp'); ?></td>
					</tr>
				</tbody>
			</table>
		</div>
	
		<div class="table table_discussion">
			<p class="sub"><?php printf(__('Last Month (%s)', 'mp'), date_i18n('M, Y', strtotime('-1 month'))); ?></p>
			<table>
				<tbody>
					<tr class="first">
						<td class="first b"><?php echo intval($last_month->count); ?></td>
						<td class="t"><?php _e('Orders', 'mp'); ?></td>
					</tr>	
					<tr>
						<td class="first b"><?php echo $mp->format_currency(false, $last_month->total); ?></td>
						<td class="t"><?php _e('Orders Total', 'mp'); ?></td>
					</tr>
					<tr>
						<td class="first b"><?php echo $mp->format_currency(false, $last_month->average); ?></td>
						<td class="t"><?php _e('Average Order', 'mp'); ?></td>
					</tr>
				</tbody>
			</table>
		</div>
		<br class="clear"/>
		<?php
	}
	
	function rightnow_dashboard_widget() {
		echo "\n\t</table>\n\t</div><br class='clear' /><br /><br />";
		
		echo "\n\t".'<div class="table table_content">';
		echo "\n\t".'<p class="sub">' . __('Store Content', 'mp') . '</p>'."\n\t".'<table>';
		echo "\n\t".'<tr class="first">';
		
		$num_posts = wp_count_posts( 'product' );
		$num_cats  = wp_count_terms('product_category');
		$num_tags = wp_count_terms('product_tag');
	
		// Posts
		$num = number_format_i18n( $num_posts->publish );
		$text = _n( 'Product', 'Products', intval($num_posts->publish), 'mp' );
		if ( current_user_can( 'edit_pages' ) ) {
			$num = "<a href='edit.php?post_type=product'>$num</a>";
			$text = "<a href='edit.php?post_type=product''>$text</a>";
		}
		echo '<td class="first b b-posts">' . $num . '</td>';
		echo '<td class="t posts">' . $text . '</td>';
		echo '</tr><tr>';
	
		// Categories
		$num = number_format_i18n( $num_cats );
		$text = _n( 'Product Category', 'Product Categories', $num_cats, 'mp' );
		if ( current_user_can( 'manage_categories' ) ) {
			$num = "<a href='edit-tags.php?taxonomy=product_category&post_type=product'>$num</a>";
			$text = "<a href='edit-tags.php?taxonomy=product_category&post_type=product'>$text</a>";
		}
		echo '<td class="first b b-cats">' . $num . '</td>';
		echo '<td class="t cats">' . $text . '</td>';
	
		echo '</tr><tr>';
	
		// Tags
		$num = number_format_i18n( $num_tags );
		$text = _n( 'Product Tag', 'Product Tags', $num_tags );
		if ( current_user_can( 'manage_categories' ) ) {
			$num = "<a href='edit-tags.php?taxonomy=product_tag&post_type=product'>$num</a>";
			$text = "<a href='edit-tags.php?taxonomy=product_tag&post_type=product'>$text</a>";
		}
		echo '<td class="first b b-tags">' . $num . '</td>';
		echo '<td class="t tags">' . $text . '</td>';
	
		echo "</tr>";
		echo "\n\t</table>\n\t</div>";
	
	
		echo "\n\t".'<div class="table table_discussion">';
		echo "\n\t".'<p class="sub">' . __('Orders', 'mp') . '</p>'."\n\t".'<table>';
		echo "\n\t".'<tr class="first">';
		
		$num_posts = wp_count_posts( 'mp_order' );

		$num = '<span class="total-count">' . number_format_i18n($num_posts->order_received) . '</span>';
		$text = __( 'Received (Awaiting Payment)', 'mp' );
		if ( current_user_can( 'manage_options' ) ) {
			$num = '<a href="edit.php?page=marketpress-orders&post_status=order_received&post_type=product">' . $num . '</a>';
			$text = '<a class="spam" href="edit.php?page=marketpress-orders&post_status=order_received&post_type=product">' . $text . '</a>';
		}
		echo '<td class="b">' . $num . '</td>';
		echo '<td class="last t">' . $text . '</td>';
	
		echo '</tr><tr>';
	
		$num = '<span class="approved-count">' . number_format_i18n($num_posts->order_paid) . '</span>';
		$text = __( 'Paid (Awaiting Shipping)', 'mp' );
		if ( current_user_can( 'manage_options' ) ) {
			$num = "<a href='edit.php?page=marketpress-orders&post_status=order_paid&post_type=product'>$num</a>";
			$text = "<a class='waiting' href='edit.php?page=marketpress-orders&post_status=order_paid&post_type=product'>$text</a>";
		}
		echo '<td class="b">' . $num . '</td>';
		echo '<td class="last t">' . $text . '</td>';
	
		echo "</tr>\n\t<tr>";
	
		$num = '<span class="pending-count">' . number_format_i18n($num_posts->order_shipped) . '</span>';
		$text = __( 'Shipped', 'mp' );
		if ( current_user_can( 'manage_options' ) ) {
			$num = "<a href='edit.php?page=marketpress-orders&post_status=order_shipped&post_type=product'>$num</a>";
			$text = "<a class='approved' href='edit.php?page=marketpress-orders&post_status=order_shipped&post_type=product'>$text</a>";
		}
		echo '<td class="b">' . $num . '</td>';
		echo '<td class="last t">' . $text . '</td>';
	
		echo "</tr>\n\t<tr>";
	
		$num = number_format_i18n($num_posts->order_closed);
		$text = __( 'Closed', 'mp' );
		if ( current_user_can( 'manage_options' ) ) {
			$num = "<a href='edit.php?page=marketpress-orders&post_status=order_closed&post_type=product'>$num</a>";
			$text = "<a href='edit.php?page=marketpress-orders&post_status=order_closed&post_type=product'>$text</a>";
		}
		echo '<td class="b">' . $num . '</td>';
		echo '<td class="last t">' . $text . '</td>';
	
		echo "</tr>";
	}
}
$mp_stats = new MarketPress_Stats();