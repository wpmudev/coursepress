<?php
/*
MarketPress Importers
*/

/*
 * Parent class to extend
 */
class MarketPress_Importer {
  var $importer_name = '';
	var $results;
  
  function __construct() {
    global $mp;
		
		$priority = isset($_POST['mp_import-'.sanitize_title($this->importer_name)]) ? 1 : 10;
		
    add_action( 'marketpress_add_importer', array(&$this, '_html'), $priority );
		
		$this->on_creation();
	}

	function _html() {
	  global $mp;
		
		if (isset($_POST['mp_import-'.sanitize_title($this->importer_name)])) {
			$this->process();
			remove_all_actions('marketpress_add_importer', 10);
		}
		?>
		<div class="postbox">
			<h3 class='hndle'><span><?php printf(__('Import From %s', 'mp'), $this->importer_name); ?></span></h3>
			<div class="inside">
			<?php $this->display(); ?>
			</div>
		</div>
		<?php
	}
	
	/* Public methods */
	
	function import_button($label = '') {
		$label = !empty($label) ? $label : __('Import Now &raquo;', 'mp');
		?>
		<p class="submit">
			<input class="button-primary" type="submit" name="mp_import-<?php echo sanitize_title($this->importer_name); ?>" value="<?php echo $label; ?>" />
		</p>
		<?php
	}
	
	function on_creation() {

	}
	
	function display() {

	}
	
	function process() {
		
	}

}

/* ------------------------------------------------------------------ */
/* ----------------------- Begin Importers -------------------------- */
/* ------------------------------------------------------------------ */

/*
 * WP e-Commerce Plugin Importer
 */
class WP_eCommerceImporter extends MarketPress_Importer {

	var $importer_name = 'WP e-Commerce';

	function display() {
	  global $wpdb;

		if ($this->results) {
			?>
			<p><?php printf( __('Successfully imported %s products from WP e-Commerce. The old products were not deleted, so running the importer again will just create copies of the products in MarketPress.', 'mp'), number_format_i18n($this->results) ); ?></p>
      
			<?php if ( class_exists('WP_eCommerce') ) { ?>
			<p><?php printf( __('You should <a href="%s">deactivate the WP e-Commerce plugin now</a>. Have fun!', 'mp'), wp_nonce_url(admin_url('plugins.php?action=deactivate&plugin=wp-e-commerce%2Fwp-shopping-cart.php'), 'deactivate-plugin_wp-e-commerce/wp-shopping-cart.php') ); ?></p>
			<?php
			}
		} else {
			$num_products = $wpdb->get_var("SELECT count(*) FROM $wpdb->posts WHERE post_type = 'wpsc-product'");
			if ($num_products) {
				?>
				<span class="description"><?php _e('This will allow you to import your products and most of their attributes from the WP e-Commerce plugin.', 'mp'); ?></span>
	
				<p><?php printf( __('It appears that you have %s products from WP e-Commerce. Click below to begin your import!', 'mp'), number_format_i18n($num_products) ); ?></p>
				<?php
				if ( class_exists('WP_eCommerce') ) {
					$this->import_button();
				} else {
					?>
					<p><?php _e('Please activate the WP e-Commerce plugin to import these products.', 'mp'); ?></p>
					<?php
				}
	    } else { //no products
        ?>
				<p><?php printf( __('It appears you have no products from WP e-Commerce to import. Check that the plugin is updated to the latest version (the importer only works with >3.8).', 'mp'), wp_nonce_url(admin_url('plugins.php?action=deactivate&plugin=wp-e-commerce%2Fwp-shopping-cart.php'), 'deactivate-plugin_wp-e-commerce/wp-shopping-cart.php') ); ?></p>
				<?php
			}
		}
	}
	
	function process() {
	  global $wpdb;

		set_time_limit(90); //this can take a while
		$this->results = 0;
		$products = $wpdb->get_results("SELECT * FROM $wpdb->posts WHERE post_type = 'wpsc-product'", ARRAY_A);
		foreach ($products as $product) {
			//import product
			$old_id = $product['ID'];
			unset($product['ID']); //clear id so it inserts as new
			$product['post_type'] = 'product';
			$product['comment_status'] = 'closed';
			$product['comment_count'] = 0;
			
			//add tags
			$tags = wp_get_object_terms($old_id, 'product_tag');
			if (is_array($tags) && count($tags)) {
				$product_tags = array();
				foreach ($tags as $tag) {
					$product_tags[] = $tag->name;
				}
				$product_tags = join(", ", $product_tags);
				$product['tax_input']['product_tag'] = $product_tags;
			}

			$new_id = wp_insert_post($product); //create the post
			
			//insert categories
			$cats = wp_get_object_terms($old_id, 'wpsc_product_category');
			if (is_array($cats) && count($cats)) {
				$product_cats = array();
				foreach ($cats as $cat) {
					$product_cats[] = $cat->name;
				}
				wp_set_object_terms($new_id, $product_cats, 'product_category');
			}
			
			//add product meta
			$meta = get_post_custom($old_id);
			$meta_data = unserialize($meta['_wpsc_product_metadata'][0]);

			update_post_meta($new_id, 'mp_sku', $meta['_wpsc_sku']); //add sku
			update_post_meta($new_id, 'mp_price', $meta['_wpsc_price']); //add price
			
			//add sale price only if set and different than reg price
			if (isset($meta['_wpsc_special_price'][0]) && $meta['_wpsc_special_price'][0] && $meta['_wpsc_special_price'][0] != $meta['_wpsc_price'][0]) {
				update_post_meta($new_id, 'mp_is_sale', 1);
				update_post_meta($new_id, 'mp_sale_price', $meta['_wpsc_special_price']);
			}

			//add stock count
			if (isset($meta['_wpsc_stock'][0]) && $meta['_wpsc_stock'][0]) {
				update_post_meta($new_id, 'mp_track_inventory', 1);
				update_post_meta($new_id, 'mp_inventory', $meta['_wpsc_stock']);
			}

			//add external link
			if (!empty($meta_data['external_link']))
				update_post_meta($new_id, 'mp_product_link', esc_url_raw($meta_data['external_link']));

			//add shipping info
			$shipping = array();
			if (!empty($meta_data['shipping']['local']))
				$shipping['extra_cost'] = round( (float)preg_replace('/[^0-9.]/', '', $meta_data['shipping']['local']), 2 );
			if (!empty($meta_data['weight']))
				$shipping['weight'] = round( (float)preg_replace('/[^0-9.]/', '', $meta_data['weight']), 2 );
			update_post_meta($new_id, 'mp_shipping', $shipping);
			
			//add thumbnail
			if (isset($meta['_thumbnail_id'][0])) { //if featured image is set
				update_post_meta($new_id, '_thumbnail_id', $meta['_thumbnail_id'][0]);
			} else { //grab first attachment as there is no featured image
				$images =& get_children( "post_type=attachment&post_mime_type=image&post_parent=$old_id" );
				$thumbnail_id = false;
				foreach ( (array) $images as $attachment_id => $attachment ) {
					$thumbnail_id = $attachment_id;
					break; //only grab the first attachment
				}
				if ($thumbnail_id)
					update_post_meta($new_id, '_thumbnail_id', $thumbnail_id);
			}
				
			//get first downloadable product url
			$args = array(
				'post_type' => 'wpsc-product-file',
				'post_parent' => $old_id,
				'numberposts' => 1,
				'post_status' => 'any'
			);
			$attached_files = (array)get_posts($args);
			if (count($attached_files))
				update_post_meta($new_id, 'mp_file', esc_url_raw($attached_files[0]->guid));
			
			//inc count
			$this->results++;
		}	
	}
}
//only load if the plugin is active and installed
$mp_wpecommerce = new WP_eCommerceImporter();


/*
 * CSV Importer
 */
class CsvImporter extends MarketPress_Importer {

	var $importer_name = 'CSV';

	function display() {
		global $mp;
		
		$file_path = $this->file_path();
		
		//delete file
		if (isset($_GET['csv_del']) && file_exists($file_path)) {
			@unlink($file_path);
			echo '<div class="updated fade"><p>'.__('Import file successfully deleted.', 'mp').'</p></div>';
		}

		//if uploaded file
		if ( isset($_FILES['csv_file']['name']) && current_user_can('upload_files') ) {
		
			//make sure directory exists
			wp_mkdir_p( $this->file_path(true) );
			
			//check extension
			if ( preg_match( '!\.(csv)$!i', strtolower($_FILES['csv_file']['name']) ) ) {
			
				//attempt to move uploaded file
				if (!move_uploaded_file($_FILES['csv_file']['tmp_name'], $file_path)) {
					@unlink($_FILES['csv_file']['tmp_name']);
					echo '<div class="error"><p>'.__('There was a problem uploading your file. Please check permissions or use FTP.', 'mp').'</p></div>';
				} else {
					//check for required fields
					$headers = $this->get_csv_headers();
					if ( !in_array('title', $headers) || !in_array('price', $headers) ) {
						@unlink($file_path);
						echo '<div class="error"><p>'.__('The CSV file must contain at a minimum the "title" and "price" columns. Please fix and upload again.', 'mp').'</p></div>';
					}
				}
			} else {
				@unlink($_FILES['csv_file']['tmp_name']);
				echo '<div class="error"><p>'.__('Invalid file format. Please upload your import file ending in ".csv".', 'mp').'</p></div>';
			}
			
		}
		
		if ($this->results) {
			@unlink($file_path);
			?>
			<p><?php printf( __('Successfully imported %s products from your CSV file. Products were created in draft status, so you will need to review them then publish (bulk or one-by-one). Importing the CSV file again will just create copies of the products in MarketPress.', 'mp'), number_format_i18n($this->results) ); ?></p>
			<p><a class="button" href="<?php echo admin_url('edit.php?post_status=draft&post_type=product'); ?>"><?php _e('Review Now &raquo;', 'mp'); ?></a></p>
			<?php
		} else {
		
			//if file has been uploaded
			if ( file_exists($file_path) ) {
				$total = count($this->get_csv_array());
				?>
				<h4><?php echo sprintf(__('A CSV import file was detected with %d products to process.', 'mp'), $total); ?> <a class="button" href="<?php echo admin_url('edit.php?post_type=product&page=marketpress&tab=importers&csv_del=1'); ?>" title="<?php _e("Delete the current CSV import file", 'mp'); ?>"><?php _e("Re-upload", 'mp'); ?></a></h4>
	
				<p><?php _e('Please be patient while products are being imported. Ready?', 'mp') ?></p>
				
				<?php
				$this->import_button();
				
			} else { //file does not exist, show upload form
				$dirs = wp_upload_dir();
				?>
				<span class="description"><?php _e('This will allow you to import products and most of their attributes from a CSV file.', 'mp'); ?></span>
				<p><span class="description"><?php _e('Your CSV file must be comma (,) delimited and fields with commas or quotes in them must be surrounded by parenthesis (") as per the CSV standard. Columns in the CSV file can be in any order, provided that they have the correct headings from the example file. "title" and "price" are the only required columns, all others can be left blank.', 'mp'); ?></span></p>
				<p><span class="description"><?php printf(__('To import featured images, it is preferable to upload them via FTP to a directory in your WP uploads folder: %s. Image paths should be relative to the uploads folder, like "/myimport/myimage.png". You may also include full external image urls and the importer will download them. This is not a preferable though because it can be too slow for a large import, and you may need to split your CSV file into multple imports to avoid timeouts. A spreadsheet program like Excel or Numbers can be used to easily manipulate your import file, and their save as CSV option will create a correctly formatted file.', 'mp'), $dirs['basedir']); ?></span></p>
				
				<p><?php _e('Please select and upload your CSV file below.', 'mp'); ?> 
				<a href="<?php echo $mp->plugin_url; ?>sample-marketpress-import.csv" target="_blank"><?php _e('Use this example file &raquo;', 'mp'); ?></a>
				</p>
				
				<p>
					<input name="csv_file" id="csv_file" size="20" type="file" /> 
					<input class="button-secondary" name="Submit" value="<?php _e('Upload &raquo;', 'mp') ?>" type="submit"><br />
					<small><?php echo __('Maximum file size: ', 'mp') . ini_get('upload_max_filesize'); ?></small>
				</p>
				<?php
				
			} //end file exists
		}
	}
	
	function process() {
	  global $wpdb;

		set_time_limit(120); //this can take a while
		$this->results = 0;
		$products = $this->get_csv_array();
		$dirs = wp_upload_dir();
		
		foreach ($products as $row) {
			$product = array();
			
			if (empty($row['title']) || (empty($row['price']) && MP_IMPORT_ALLOW_NO_PRICE === false) )
				continue;
				
			//import product
			$product['post_title'] = $row['title'];
			$product['post_content'] = $row['description'];
			$product['post_status'] = 'draft';
			$product['post_type'] = 'product';
			$product['comment_status'] = 'closed';
			$product['comment_count'] = 0;
			
			//add tags
			if (!empty($row['tags']))
				$product['tax_input']['product_tag'] = trim($row['tags']);

			$new_id = wp_insert_post($product); //create the post
			
			//insert categories
			if (!empty($row['categories'])) {
				$product_cats = explode(',', trim($row['categories']));
				$product_cats = array_map('trim', $product_cats);
				wp_set_object_terms($new_id, $product_cats, 'product_category');
			}
			
			//add product meta
			update_post_meta($new_id, 'mp_sku', array( preg_replace("/[^a-zA-Z0-9_-]/", "", trim(@$row['sku'])) ) ); //add sku
			update_post_meta($new_id, 'mp_price', array( round( (float)preg_replace('/[^0-9.]/', '', $row['price']), 2 ) ) ); //add price
			update_post_meta($new_id, 'mp_var_name', array('') ); //add blank var name
			
			//add sale price only if set
			if (!empty($row['sale_price'])) {
				update_post_meta($new_id, 'mp_is_sale', 1);
				update_post_meta($new_id, 'mp_sale_price', array( round( (float)preg_replace('/[^0-9.]/', '', $row['sale_price']), 2 ) ) );
				update_post_meta($new_id, 'mp_price_sort', round( (float)preg_replace('/[^0-9.]/', '', $row['sale_price']), 2 ));
			} else {
				update_post_meta($new_id, 'mp_is_sale', 0);
				update_post_meta($new_id, 'mp_price_sort', round( (float)preg_replace('/[^0-9.]/', '', $row['price']), 2 ));
			}

			//add stock count if set
			if ( is_numeric($row['stock']) ) {
				update_post_meta($new_id, 'mp_track_inventory', 1);
				update_post_meta($new_id, 'mp_inventory', array( intval($row['stock']) ));
			} else {
				update_post_meta($new_id, 'mp_track_inventory', 0);
			}

			//add external link
			if (!empty($row['external_link']))
				update_post_meta($new_id, 'mp_product_link', esc_url_raw($row['external_link']));

			//add shipping info
			$shipping = array();
			if (!empty($row['extra_shipping']))
				$shipping['extra_cost'] = round( (float)preg_replace('/[^0-9.]/', '', $row['extra_shipping']), 2 );
			if (!empty($row['weight']))
				$shipping['weight'] = round( (float)preg_replace('/[^0-9.]/', '', $row['weight']), 2 );
			update_post_meta($new_id, 'mp_shipping', $shipping);
				
			//download
			if (!empty($row['download_url']))
				update_post_meta($new_id, 'mp_file', esc_url_raw($row['download_url']));
			
			//download
			if (isset($row['sales_count']))
				update_post_meta($new_id, 'mp_sales_count', intval($row['sales_count']));
			
			//add featured images
			if (isset($row['image']) && !empty($row['image'])) {
				
				// Determine if this file is in our server
				$local = false;
				$img_location = str_replace($dirs['baseurl'], $dirs['basedir'], $row['image']);
				if ( file_exists($img_location) ) {
					$local = true;
				} else if ( file_exists($dirs['basedir'] . '/' . ltrim($row['image'], '/')) ) {
					$local = true;
					$img_location = $dirs['basedir'] . '/' . ltrim($row['image'], '/');
				}
				if ( $local ) { //just resize without downloading as it's on our server
					preg_match( '/[^\?]+\.(jpe?g|jpe|gif|png)\b/i', $img_location, $matches );
					$file_array = array();
					$file_array['name'] = basename($matches[0]);
					$file_array['tmp_name'] = $img_location;
					
					// do the validation and storage stuff
					$id = media_handle_sideload( $file_array, $new_id, $row['title'] );
					// If error storing permanently, unlink
					if ( !is_wp_error($id) ) {
						// add featured image to post
						add_post_meta($new_id, '_thumbnail_id', $id);
					}
				} else { //download the image and attach
					require_once(ABSPATH . '/wp-admin/includes/file.php');
					$img_html = media_sideload_image( $row['image'], $new_id, $row['title'] );
					if ( !is_wp_error($img_html) ) {
						//figure out the id
						$args = array(
							'numberposts' => 1,
							'order'=> 'DESC',
							'post_mime_type' => 'image',
							'post_parent' => $new_id,
							'post_type' => 'attachment'
							);
						
						$get_children_array = get_children($args, ARRAY_A);  //returns Array ( [$image_ID]... 
						$rekeyed_array = array_values($get_children_array);
						$child_image = $rekeyed_array[0];  
						
						// add featured image to post
						add_post_meta($new_id, '_thumbnail_id', $child_image['ID']);
					}
				}
				
	    }

			//inc count
			$this->results++;
		}	
	}
	
	function get_csv_headers() {
		$file_path = $this->file_path();

		@ini_set('auto_detect_line_endings', true); //so it doesn't choke on mac CR line endings
		$fh = @fopen($file_path, 'r');
		if ($fh) {
			$temp_fields = fgetcsv($fh, 5120); // 5KB
			
			if (is_array($temp_fields))
				$headers = array_map('strtolower', $temp_fields);
	
			fclose($fh);
			
			return $headers;
		} else {
			return false;
		}
	}
	
	function get_csv_array() {
		$file_path = $this->file_path();
		$i = 0;
		@ini_set('auto_detect_line_endings', true); //so it doesn't choke on mac CR line endings
		$fh = @fopen($file_path, 'r');
		if ($fh) {
			while (!feof($fh)) {
				//parse csv line
				$temp_fields = fgetcsv($fh, 5120); // 5KB
				
				if (is_array($temp_fields)) {
					if (!isset($titles))
						$titles = array_map('strtolower', $temp_fields);
					
					//switch keys out for titles
					$new_fields = array();
					foreach ($temp_fields as $key => $value) {
						$new_fields[$titles[$key]] = $value;
					}
					$fields[] = $new_fields;
				}
			}
	
	
			fclose($fh);
			
			//remove header row
			array_shift($fields);
			
			return $fields;
		} else {
			return false;
		}
	}
	
	function file_path($dir = false) {
		$target_path = wp_upload_dir();
		if ($dir)
			return trailingslashit($target_path['basedir']);
		else
			return trailingslashit($target_path['basedir']) . 'marketpress-import.csv';
	}
	
}
//only load if the plugin is active and installed
$mp_csv = new CsvImporter();