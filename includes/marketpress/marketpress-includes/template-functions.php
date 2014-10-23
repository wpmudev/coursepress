<?php
/*
	MarketPress Template Functions

	Relevant functions for custom template files:

	mp_product.php
	mp_product_title
	mp_product_description
	mp_product_meta
	mp_product_image
	mp_product_price
	mp_buy_button
	mp_category_list

	mp_cart.php
	mp_show_cart

	mp_orderstatus.php
	mp_order_status

	mp_productlist.php
	mp_products_filter
	mp_list_products
 */

if (!function_exists('mp_tag_cloud')) :
/**
 * Display product tag cloud.
 *
 * The text size is set by the 'smallest' and 'largest' arguments, which will
 * use the 'unit' argument value for the CSS text size unit. The 'format'
 * argument can be 'flat' (default), 'list', or 'array'. The flat value for the
 * 'format' argument will separate tags with spaces. The list value for the
 * 'format' argument will format the tags in a UL HTML list. The array value for
 * the 'format' argument will return in PHP array type format.
 *
 * The 'orderby' argument will accept 'name' or 'count' and defaults to 'name'.
 * The 'order' is the direction to sort, defaults to 'ASC' and can be 'DESC'.
 *
 * The 'number' argument is how many tags to return. By default, the limit will
 * be to return the top 45 tags in the tag cloud list.
 *
 * The 'topic_count_text_callback' argument is a function, which, given the count
 * of the posts	 with that tag, returns a text for the tooltip of the tag link.
 *
 * The 'exclude' and 'include' arguments are used for the {@link get_tags()}
 * function. Only one should be used, because only one will be used and the
 * other ignored, if they are both set.
 *
 * @param bool $echo Optional. Whether or not to echo.
 * @param array|string $args Optional. Override default arguments.
 */
function mp_tag_cloud($echo = true, $args = array()) {

		$args['echo'] = false;
		$args['taxonomy'] = 'product_tag';

		$cloud = '<div id="mp_tag_cloud">' . wp_tag_cloud($args) . '</div>';

		$cloud = apply_filters('mp_tag_cloud', $cloud, $args);

		if ($echo)
				echo $cloud;
		else
				return $cloud;
}
endif;


if (!function_exists('mp_list_categories')) :
/**
 * Display or retrieve the HTML list of product categories.
 *
 * The list of arguments is below:
 *		 'show_option_all' (string) - Text to display for showing all categories.
 *		 'orderby' (string) default is 'ID' - What column to use for ordering the
 * categories.
 *		 'order' (string) default is 'ASC' - What direction to order categories.
 *		 'show_last_update' (bool|int) default is 0 - See {@link
 * walk_category_dropdown_tree()}
 *		 'show_count' (bool|int) default is 0 - Whether to show how many posts are
 * in the category.
 *		 'hide_empty' (bool|int) default is 1 - Whether to hide categories that
 * don't have any posts attached to them.
 *		 'use_desc_for_title' (bool|int) default is 1 - Whether to use the
 * description instead of the category title.
 *		 'feed' - See {@link get_categories()}.
 *		 'feed_type' - See {@link get_categories()}.
 *		 'feed_image' - See {@link get_categories()}.
 *		 'child_of' (int) default is 0 - See {@link get_categories()}.
 *		 'exclude' (string) - See {@link get_categories()}.
 *		 'exclude_tree' (string) - See {@link get_categories()}.
 *		 'current_category' (int) - See {@link get_categories()}.
 *		 'hierarchical' (bool) - See {@link get_categories()}.
 *		 'title_li' (string) - See {@link get_categories()}.
 *		 'depth' (int) - The max depth.
 *
 * @param bool $echo Optional. Whether or not to echo.
 * @param string|array $args Optional. Override default arguments.
 */
function mp_list_categories($echo = true, $args = '') {
		$args['taxonomy'] = 'product_category';
		$args['echo'] = false;

		$list = '<ul id="mp_category_list">' . wp_list_categories($args) . '</ul>';

		$list = apply_filters('mp_list_categories', $list, $args);

		if ($echo)
				echo $list;
		else
				return $list;
}
endif;


if (!function_exists('mp_dropdown_categories')) :
/**
 * Display or retrieve the HTML dropdown list of product categories.
 *
 * The list of arguments is below:
 *		 'show_option_all' (string) - Text to display for showing all categories.
 *		 'show_option_none' (string) - Text to display for showing no categories.
 *		 'orderby' (string) default is 'ID' - What column to use for ordering the
 * categories.
 *		 'order' (string) default is 'ASC' - What direction to order categories.
 *		 'show_last_update' (bool|int) default is 0 - See {@link get_categories()}
 *		 'show_count' (bool|int) default is 0 - Whether to show how many posts are
 * in the category.
 *		 'hide_empty' (bool|int) default is 1 - Whether to hide categories that
 * don't have any posts attached to them.
 *		 'child_of' (int) default is 0 - See {@link get_categories()}.
 *		 'exclude' (string) - See {@link get_categories()}.
 *		 'depth' (int) - The max depth.
 *		 'tab_index' (int) - Tab index for select element.
 *		 'name' (string) - The name attribute value for select element.
 *		 'id' (string) - The ID attribute value for select element. Defaults to name if omitted.
 *		 'class' (string) - The class attribute value for select element.
 *		 'selected' (int) - Which category ID is selected.
 *		 'taxonomy' (string) - The name of the taxonomy to retrieve. Defaults to category.
 *
 * The 'hierarchical' argument, which is disabled by default, will override the
 * depth argument, unless it is true. When the argument is false, it will
 * display all of the categories. When it is enabled it will use the value in
 * the 'depth' argument.
 *
 *
 * @param bool $echo Optional. Whether or not to echo.
 * @param string|array $args Optional. Override default arguments.
 */
function mp_dropdown_categories($echo = true, $args = '') {
		$args['taxonomy'] = 'product_category';
		$args['echo'] = false;
		$args['id'] = 'mp_category_dropdown';

		$dropdown = wp_dropdown_categories($args);
		$dropdown .= '<script type="text/javascript">
/* <![CDATA[ */
	var dropdown = document.getElementById("mp_category_dropdown");
	function onCatChange() {
		if ( dropdown.options[dropdown.selectedIndex].value > 0 ) {
			location.href = "' . get_home_url() . '/?product_category="+dropdown.options[dropdown.selectedIndex].value;
		}
	}
	dropdown.onchange = onCatChange;
/* ]]> */
</script>';

	$dropdown = apply_filters('mp_dropdown_categories', $dropdown, $args );

		if ($echo)
				echo $dropdown;
		else
				return $dropdown;
}
endif;


if (!function_exists('mp_popular_products')) :
/**
 * Displays a list of popular products ordered by sales.
 *
 * @param bool $echo Optional, whether to echo or return
 * @param int $num Optional, max number of products to display. Defaults to 5
 */
function mp_popular_products($echo = true, $num = 5) {
		//The Query
		$custom_query = new WP_Query('post_type=product&post_status=publish&posts_per_page=' . intval($num) . '&meta_key=mp_sales_count&meta_compare=>&meta_value=0&orderby=meta_value_num&order=DESC');

		$content = '<ul id="mp_popular_products">';

		if (count($custom_query->posts)) {
				foreach ($custom_query->posts as $post) {
						$content .= '<li><a href="' . get_permalink($post->ID) . '">' . $post->post_title . '</a></li>';
				}
		} else {
				$content .= '<li>' . __('No Products', 'mp') . '</li>';
		}

		$content .= '</ul>';

		$content = apply_filters('mp_popular_products', $content, $num);

		if ($echo)
				echo $content;
		else
				return $content;
}
endif;


if (!function_exists('mp_related_products')) :
/**
 * Displays related products for the passed product id
 *
 * @param int $product_id.
 * @param bool $in_same_category Optional, whether to limit related to the same category.
 * @param bool $echo. Optional, whether to echo or return the results
 * @param int $limit. Optional The number of products we want to retrieve.
 * @param bool $simple_list Optional, whether to show the related products based on the "list_view" setting or as a simple unordered list
 * @param bool $in_same_tags Optional, whether to limit related to same tags
 */
function mp_related_products() {
	global $mp, $post;

	$output = '';
	$categories = $tag_list = array();

	if( $mp->get_setting('related_products->show') == 0)
		return '';

	$defaults = array_merge($mp->defaults['related_products'], array(
		'simple_list' => $mp->get_setting('related_products->simple_list'),
		'relate_by' => $mp->get_setting('related_products->relate_by'),
		'limit' => $mp->get_setting('related_products->show_limit'),
	));

	$args = $mp->parse_args(func_get_args(), $defaults);

	if( !is_null($args['product_id']) ) {
		$args['product_id'] = ( isset($post) && $post->post_type == 'product' ) ? $post->ID : false;
		$product_details = get_post($args['product_id']);
	}else{
		$product_details = get_post($args['product_id']);
		$args['product_id'] = ( $product_details->post_type == 'product' ) ? $post->ID : false;
	}

	if( is_null($product_details) )
		return '';

	//setup the default args
	$query_args = array(
		'post_type' 	 => 'product',
		'posts_per_page' => intval($args['limit']),
		'post__not_in' 	 => array($args['product_id']),
		'tax_query' 	 => array(), //we'll add these later
	);

	//get the tags for this product
	if ( 'both' == $args['relate_by'] || 'tags' == $args['relate_by'] ) {
		$tags = get_the_terms( $args['product_id'], 'product_tag');

		if ( is_array($tags) ) {
			foreach($tags as $tag) {
				$tag_list[] = $tag->term_id;
			}

			//add the tag taxonomy query
			$query_args['tax_query'][] = array(
					'taxonomy' => 'product_tag',
					'field' => 'id',
					'terms' => $tag_list,
					'operator' => 'IN'
			);
		}
	}

	//are we limiting to only the assigned categories
	if( 'both' == $args['relate_by'] || 'category' == $args['relate_by'] ) {
		$product_cats = get_the_terms( $args['product_id'], 'product_category' );

		if( is_array($product_cats) ) {
			foreach($product_cats as $cat) {
				$categories[] = $cat->term_id;
			}

			$query_args['tax_query'][] = array(
					'taxonomy' => 'product_category',
					'field' => 'id',
					'terms' => $categories,
					'operator' => 'IN'
			);
		}
	}

	//we only want to run the query if we have categories or tags to look for.
	if ( count($tag_list) > 0 || count($categories) > 0 ) {
		//make the query
		$related_query = new WP_Query($query_args);

		//how are we formatting the output
		if( $args['simple_list'] ) {

			$output = '<div id="mp_related_products">';
			$output .= '<div class="mp_related_products_title"><h4>' . apply_filters( 'mp_related_products_title', __('Related Products','mp') ) . '</h4></div>';
			if( $related_query->post_count ) {
				$list = '<ul class="mp_related_products_list">%s</ul>';
				$items = '';
				foreach($related_query->posts as $product) {
					$items .= '<li class="mp_related_products_list_item"><a href="'.get_permalink($product->ID).'">'.$product->post_title.'</a></li>';
				}
				$output .= sprintf($list, $items);
			}else{
				$output .= '<div class="mp_related_products_title"><h4>'. apply_filters( 'mp_related_products_title_none', __('No Related Products','mp') ) . '</h4></div>';
			}

			$output .= '</div>';
		} else {
			//we'll use the $mp settings and functions
			$layout_type = $mp->get_setting('list_view');
			$output = '<div id="mp_related_products" class="mp_' . $layout_type . '">';
			//do we have posts?
			if( $related_query->post_count ) {
				$output .= '<div class="mp_related_products_title"><h4>' . apply_filters( 'mp_related_products_title', __('Related Products','mp') ) . '</h4></div>';
				$output .= $layout_type == 'grid' ? _mp_products_html_grid($related_query) : _mp_products_html_list($related_query);
			}else{
				$output .= '<div class="mp_related_products_title"><h4>'. apply_filters( 'mp_related_products_title_none', __('No Related Products','mp') ) . '</h4></div>';
			}

			$output .= '</div>';
		}
	}

	$output = apply_filters('mp_related_products', $output, $args);

	//how are we sending back the data
	if($args['echo']) {
		echo $output;
	}else{
		return $output;
	}
}
endif;

if (!function_exists('mp_pinit_button')) :
/**
 * Pinterest PinIt button
 */
function mp_pinit_button( $product_id = NULL, $context = 'single_view', $echo = false ) {
	global $mp, $id;

	$post_id = ($product_id === NULL) ? $id : $product_id;
	$setting = $mp->get_setting('social->pinterest->show_pinit_button');

	if( $setting == 'off' || $setting != $context ) {
		return '';
	}

	$url = urlencode( get_permalink( $post_id ) );
	$desc = urlencode( get_the_title( $post_id ) );

	$image_info =	$large_image_url = wp_get_attachment_image_src( get_post_thumbnail_id($post_id), 'large');
	$media = ( $image_info ) ?	 '&media='.urlencode( $image_info[0] ) : '';

	$count_pos = ( $pos = $mp->get_setting('social->pinterest->show_pin_count') ) ? $pos : 'none';

	$snippet = apply_filters('mp_pinit_button_link', '
		<a href="//www.pinterest.com/pin/create/button/?url='.$url . $media .'&description='.$desc.'" data-pin-do="buttonPin" data-pin-config="'. $count_pos.'" target="_blank"><img src="//assets.pinterest.com/images/pidgets/pin_it_button.png" /></a>', $product_id, $context);

	if($echo) {
		echo $snippet;
	}else{
		return $snippet;
	}
}
endif;


if (!function_exists('_mp_cart_table')) :
//Prints cart table, for internal use
function _mp_cart_table($type = 'checkout', $echo = false) {
		global $mp, $blog_id;
		$blog_id = (is_multisite()) ? $blog_id : 1;
		$current_blog_id = $blog_id;

		$global_cart = $mp->get_cart_contents(true);
		if ( ! $mp->global_cart ) {	//get subset if needed
			$selected_cart[$blog_id] = $global_cart[$blog_id];
		} else {
			$selected_cart = $global_cart;
		}

		$content = '';
		if ($type == 'checkout-edit') {
				$content .= apply_filters('mp_cart_updated_msg', '');

				$content .= '<form id="mp_cart_form" method="post" action="">';
				$content .= '<table class="mp_cart_contents"><thead><tr>';
				$content .= '<th class="mp_cart_col_product" colspan="2">' . __('Item:', 'mp') . '</th>';
				$content .= '<th class="mp_cart_col_price">' . __('Price:', 'mp') . '</th>';
				$content .= '<th class="mp_cart_col_quant">' . __('Quantity:', 'mp') . '</th></tr></thead><tbody>';

				$totals = array();
				$shipping_prices = array();
				$shipping_tax_prices = array();
				$tax_prices = array();
				$coupon_code = $mp->get_coupon_code();
				
				foreach ($selected_cart as $bid => $cart) {
					if ( is_multisite() ) {
						switch_to_blog($bid);
					}
								
					foreach ($cart as $product_id => $variations) {
						foreach ($variations as $variation => $data) {
							$price = $data['price'] * $data['quantity'];
							$discount_price = $mp->coupon_value_product($coupon_code, $price, $product_id);
							$totals[] = $discount_price;

							$content .= '<tr>';
							$content .= '	 <td class="mp_cart_col_thumb">' . mp_product_image(false, 'widget', $product_id, 50) . '</td>';
							$content .= '	 <td class="mp_cart_col_product_table"><a href="' . apply_filters('mp_product_url_display_in_cart', $data['url'], $product_id) . '">' . apply_filters('mp_product_name_display_in_cart', $data['name'], $product_id) . '</a>' . '</td>'; // Added WPML
							$content .= '	 <td class="mp_cart_col_price">';

							if ( $discount_price == $price ) {
								$content .= $mp->format_currency('', $price);
							} else {
								$content .= '<del>' . $mp->format_currency('', $price) . '</del><br />';
								$content .= $mp->format_currency('', $discount_price);
							}

							$content .= '	 </td>';
							$content .= '	 <td class="mp_cart_col_quant"><input type="text" size="2" name="quant[' . $bid . ':' . $product_id . ':' . $variation . ']" value="' . $data['quantity'] . '" />&nbsp;<label><input type="checkbox" name="remove[]" value="' . $bid . ':' . $product_id . ':' . $variation . '" /> ' . __('Remove', 'mp') . '</label></td>';
							$content .= '</tr>';
						}
					}

					if ( ($shipping_price = $mp->shipping_price()) !== false ) {
						$shipping_prices[] = $shipping_price;
					}
					
					if ( ($shipping_tax_price = $mp->shipping_tax_price($shipping_price)) !== false ) {
						$shipping_tax_prices[] = $shipping_tax_price;
					}

					$tax_prices[] =  $mp->tax_price();
				}

				//go back to original blog
				if ( is_multisite() ) {
					switch_to_blog($current_blog_id);
				}

				$total = array_sum($totals);

				if ( $mp->get_setting('tax->tax_inclusive') && $mp->get_setting('tax->tax_shipping') ) {
					$total += array_sum($shipping_tax_prices) - array_sum($shipping_prices);
				}

				//coupon line TODO - figure out how to apply them on global checkout
				if ( !empty($coupon_code) ) {
						//dont' show confusing subtotal with tax inclusive pricing on
						if (!$mp->get_setting('tax->tax_inclusive')) {
								$content .= '<tr>';
								$content .= '	 <td class="mp_cart_subtotal_lbl" colspan="2">' . __('Subtotal:', 'mp') . '</td>';
								$content .= '	 <td class="mp_cart_col_subtotal">' . $mp->format_currency('', $total) . '</td>';
								$content .= '	 <td>&nbsp;</td>';
								$content .= '</tr>';
						}

						$content .= '<tr>';
						$content .= '	 <td class="mp_cart_subtotal_lbl" colspan="2">' . __('Coupon:', 'mp') . '</td>';
						$content .= '	 <td class="mp_cart_col_discount">' . $coupon_code . '</td>';
						$content .= '	 <td class="mp_cart_remove_coupon"><a href="?remove_coupon=1">' . __('Remove Coupon &raquo;', 'mp') . '</a></td>';
						$content .= '</tr>';
				} else {
						$content .= '<tr>';
						$content .= '	 <td class="mp_cart_subtotal_lbl" colspan="4">
						<a id="coupon-link" class="alignright" href="#coupon-code">' . __('Have a coupon code?', 'mp') . '</a>
						<div id="coupon-code" class="alignright" style="display: none;">
							<label for="coupon_code">' . __('Enter your code:', 'mp') . '</label>
							<input type="text" name="coupon_code" id="coupon_code" />
							<input type="submit" name="update_cart_submit" value="' . __('Apply &raquo;', 'mp') . '" />
						</div>
				</td>';
						$content .= '</tr>';
				}

				//shipping line
				if ($shipping_price = array_sum($shipping_prices)) {
					$shipping_tax_price = array_sum($shipping_tax_prices);
					if (!$mp->global_cart && apply_filters('mp_shipping_method_lbl', ''))
							$shipping_method = apply_filters('mp_shipping_method_lbl', '');
					else
							$shipping_method = '';
					$content .= '<tr>';
					$content .= '	 <td class="mp_cart_subtotal_lbl" colspan="2">' . __('Shipping:', 'mp') . '</td>';
					$content .= '	 <td class="mp_cart_col_shipping">' . $mp->format_currency('', $shipping_tax_price) . '</td>';
					$content .= '	 <td>' . $shipping_method . '</td>';
					$content .= '</tr>';
					$total = $total + $shipping_price;
				}

				//tax line
				if ($tax_price = array_sum($tax_prices)) {
					$content .= '<tr>';
					$content .= '	 <td class="mp_cart_subtotal_lbl" colspan="2">' . esc_html($mp->get_setting('tax->label', __('Taxes', 'mp'))) . ':</td>';
					$content .= '	 <td class="mp_cart_col_tax">' . $mp->format_currency('', $tax_price) . '</td>';
					$content .= '	 <td>&nbsp;</td>';
					$content .= '</tr>';

					if ( ! $mp->get_setting('tax->tax_inclusive') ) {
						$total = $total + $tax_price;
					}
				}

				$content .= '</tbody><tfoot><tr>';
				$content .= '	 <td class="mp_cart_subtotal_lbl" colspan="2">' . __('Cart Total:', 'mp') . '</td>';
				$content .= '	 <td class="mp_cart_col_total">' . $mp->format_currency('', $total) . '</td>';
				$content .= '	 <td class="mp_cart_col_updatecart"><input type="submit" name="update_cart_submit" value="' . __('Update Cart &raquo;', 'mp') . '" /></td>';
				$content .= '</tr></tfoot>';

				$content .= '</table></form>';
		} else if ($type == 'checkout') {

				$content .= '<table class="mp_cart_contents"><thead><tr>';
				$content .= '<th class="mp_cart_col_product" colspan="2">' . __('Item:', 'mp') . '</th>';
				$content .= '<th class="mp_cart_col_quant">' . __('Qty:', 'mp') . '</th>';
				$content .= '<th class="mp_cart_col_price">' . __('Price:', 'mp') . '</th></tr></thead><tbody>';

				$totals = array();
				$shipping_prices = array();
				$shipping_tax_prices = array();
				$tax_prices = array();
				$coupon_code = $mp->get_coupon_code();

				foreach ( $selected_cart as $bid => $cart ) {

						if ( is_multisite() ) {
							switch_to_blog($bid);
						}
								
						foreach ($cart as $product_id => $variations) {
								foreach ($variations as $variation => $data) {
										$content .= '<tr>';
										$content .= '	 <td class="mp_cart_col_thumb">' . mp_product_image(false, 'widget', $product_id, 75) . '</td>';
										$content .= '	 <td class="mp_cart_col_product_table"><a href="' . apply_filters('mp_product_url_display_in_cart', $data['url'], $product_id) . '">' . apply_filters('mp_product_name_display_in_cart', $data['name'], $product_id) . '</a>';

										// FPM: Output product custom field information
										$cf_key = $bid . ':' . $product_id . ':' . $variation;
										if (isset($_SESSION['mp_shipping_info']['mp_custom_fields'][$cf_key])) {
												$cf_item = $_SESSION['mp_shipping_info']['mp_custom_fields'][$cf_key];

												$mp_custom_field_label = get_post_meta($product_id, 'mp_custom_field_label', true);
												if (isset($mp_custom_field_label[$variation]))
														$label_text = $mp_custom_field_label[$variation];
												else
														$label_text = __('Product Extra Fields:', 'mp');

												$content .= '<div class="mp_cart_custom_fields">' . $label_text . '<br /><ol>';
												foreach ($cf_item as $item) {
														$content .= '<li>' . $item . '</li>';
												}
												$content .= '</ol></div>';
										}
										$content .= '</td>'; // Added WPML

										$content .= '	 <td class="mp_cart_col_quant">' . number_format_i18n($data['quantity']) . '</td>';

										$price = $data['price'] * $data['quantity'];
										$discount_price = $mp->coupon_value_product($coupon_code, $price, $product_id);
										$totals[] = $discount_price;

										$content .= '	 <td class="mp_cart_col_price">';

										if ( $discount_price == $price ) {
											$content .= $mp->format_currency('', $discount_price);
										} else {
											$content .= '<del>' . $mp->format_currency('', $price) . '</del><br />';
											$content .= $mp->format_currency('', $discount_price);
										}

										$content .= '	 </td>';
										$content .= '</tr>';
								}
						}

						if (($shipping_price = $mp->shipping_price()) !== false)
								$shipping_prices[] = $shipping_price;

						if (($shipping_tax_price = $mp->shipping_tax_price($shipping_price)) !== false)
								$shipping_tax_prices[] = $shipping_tax_price;

						$tax_prices[] = $mp->tax_price();
				}
				
				//go back to original blog
				if (is_multisite())
						switch_to_blog($current_blog_id);

				$total = array_sum($totals);

				if ( $mp->get_setting('tax->tax_inclusive') ) {
					$total -= array_sum($tax_prices);
				}

				if ( $mp->get_setting('tax->tax_inclusive') && $mp->get_setting('tax->tax_shipping') ) {
					$total += array_sum($shipping_tax_prices) - array_sum($shipping_prices);
				}

				//coupon line TODO - figure out how to apply them on global checkout
				if ( !empty($coupon_code) ) {

						//dont' show confusing subtotal with tax inclusive pricing on
						if (!$mp->get_setting('tax->tax_inclusive')) {
								$content .= '<tr>';
								$content .= '	 <td class="mp_cart_subtotal_lbl" colspan="3">' . __('Subtotal:', 'mp') . '</td>';
								$content .= '	 <td class="mp_cart_col_subtotal">' . $mp->format_currency('', $total) . '</td>';
								$content .= '</tr>';
						}
						$content .= '<tr>';
						$content .= '	 <td class="mp_cart_subtotal_lbl" colspan="3">' . __('Coupon:', 'mp') . '</td>';
						$content .= '	 <td class="mp_cart_col_discount">' . $coupon_code . '</td>';
						$content .= '</tr>';
				}

				//shipping line
				if ($shipping_price = array_sum($shipping_prices)) {
						$shipping_tax_price = array_sum($shipping_tax_prices);
						if (!$mp->global_cart && apply_filters('mp_shipping_method_lbl', ''))
								$shipping_method = ' (' . apply_filters('mp_shipping_method_lbl', '') . ')';
						else
								$shipping_method = '';
						$content .= '<tr>';
						$content .= '	 <td class="mp_cart_subtotal_lbl" colspan="3">' . __('Shipping:', 'mp') . $shipping_method . '</td>';
						$content .= '	 <td class="mp_cart_col_shipping">' . $mp->format_currency('', $shipping_tax_price) . '</td>';
						$content .= '</tr>';
						$total = $total + $shipping_price;
				}

				//tax line
				if ($tax_price = array_sum($tax_prices)) {
						$content .= '<tr>';
						$content .= '	 <td class="mp_cart_subtotal_lbl" colspan="3">' . esc_html($mp->get_setting('tax->label', __('Taxes', 'mp'))) . ':</td>';
						$content .= '	 <td class="mp_cart_col_tax">' . $mp->format_currency('', $tax_price) . '</td>';
						$content .= '</tr>';
						$total = $total + $tax_price;
				}

				$content .= '<tr>';
				$content .= '	 <td class="mp_cart_subtotal_lbl" colspan="3">' . __('Cart Total:', 'mp') . '</td>';
				$content .= '	 <td class="mp_cart_col_total">' . $mp->format_currency('', $total) . '</td>';
				$content .= '</tr>';

				$content .= '</tbody></table>';
		} else if ($type == 'widget') {

				$content .= '<table class="mp_cart_contents_widget"><thead><tr>';
				$content .= '<th class="mp_cart_col_product" colspan="2">' . __('Item:', 'mp') . '</th>';
				$content .= '<th class="mp_cart_col_quant">' . __('Qty:', 'mp') . '</th>';
				$content .= '<th class="mp_cart_col_price">' . __('Price:', 'mp') . '</th></tr></thead><tbody>';

				$totals = array();
				foreach ($selected_cart as $bid => $cart) {

						if (is_multisite())
								switch_to_blog($bid);

						foreach ($cart as $product_id => $variations) {
								foreach ($variations as $variation => $data) {
										$totals[] = $data['price'] * $data['quantity'];
										$content .= '<tr>';
										$content .= '	 <td class="mp_cart_col_thumb">' . mp_product_image(false, 'widget', $product_id, 25) . '</td>';
										$content .= '	 <td class="mp_cart_col_product_table"><a href="' . apply_filters('mp_product_url_display_in_cart', $data['url'], $product_id) . '">' . apply_filters('mp_product_name_display_in_cart', $data['name'], $product_id) . '</a>' . '</td>'; // Added WPML
										$content .= '	 <td class="mp_cart_col_quant">' . number_format_i18n($data['quantity']) . '</td>';
										$content .= '	 <td class="mp_cart_col_price">' . $mp->format_currency('', $data['price'] * $data['quantity']) . '</td>';
										$content .= '</tr>';
								}
						}
				}

				if (is_multisite())
						switch_to_blog($current_blog_id);

				$total = array_sum($totals);

				$content .= '<tr>';
				$content .= '	 <td class="mp_cart_subtotal_lbl" colspan="3">' . __('Subtotal:', 'mp') . '</td>';
				$content .= '	 <td class="mp_cart_col_total">' . $mp->format_currency('', $total) . '</td>';
				$content .= '</tr>';

				$content .= '</tbody></table>';
		}

		$content = apply_filters('_mp_cart_table', $content, $type);

		if ($echo) {
				echo $content;
		} else {
				return $content;
		}
}
endif;


if (!function_exists('_mp_cart_login')) :
//Prints cart login/register form, for internal use
function _mp_cart_login($echo = false) {
		global $mp;

		$content = '';
		//don't show if logged in
		if (is_user_logged_in() || MP_HIDE_LOGIN_OPTION === true) {
				$content .= '<p class="mp_cart_direct_checkout">';
				$content .= '<a class="mp_cart_direct_checkout_link" href="' . mp_checkout_step_url('shipping') . '">' . __('Checkout Now &raquo;', 'mp') . '</a>';
				$content .= '</p>';
		} else {
				$content .= '<table class="mp_cart_login">';
				$content .= '<thead><tr>';
				$content .= '<th class="mp_cart_login">' . __('Have a User Account?', 'mp') . '</th>';
				$content .= '<th>&nbsp;</th>';
				if ($mp->get_setting('force_login'))
						$content .= '<th>' . __('Register To Checkout', 'mp') . '</th>';
				else
						$content .= '<th>' . __('Checkout Directly', 'mp') . '</th>';
				$content .= '</tr></thead>';
				$content .= '<tbody>';
				$content .= '<tr>';
				$content .= '<td class="mp_cart_login">';
				$content .= '<form name="loginform" id="loginform" action="' . wp_login_url() . '" method="post">';
				$content .= '<label>' . __('Username', 'mp') . '<br />';
				$content .= '<input type="text" name="log" id="user_login" class="input" value="" size="20" /></label>';
				$content .= '<br />';
				$content .= '<label>' . __('Password', 'mp') . '<br />';
				$content .= '<input type="password" name="pwd" id="user_pass" class="input" value="" size="20" /></label>';
				$content .= '<br />';
				$content .= '<input type="submit" name="wp-submit" id="mp_login_submit" value="' . __('Login and Checkout &raquo;', 'mp') . '" />';
				$content .= '<input type="hidden" name="redirect_to" value="' . mp_checkout_step_url('shipping') . '" />';
				$content .= '</form>';
				$content .= '</td>';
				$content .= '<td class="mp_cart_or_label">' . __('or', 'mp') . '</td>';
				$content .= '<td class="mp_cart_checkout">';
				if ($mp->get_setting('force_login'))
						$content .= apply_filters('register', '<a class="mp_cart_direct_checkout_link" href="' . site_url('wp-login.php?action=register', 'login') . '">' . __('Register Now To Checkout &raquo;', 'mp') . '</a>');
				else
						$content .= '<a class="mp_cart_direct_checkout_link" href="' . mp_checkout_step_url('shipping') . '">' . __('Checkout Now &raquo;', 'mp') . '</a>';
				$content .= '</td>';
				$content .= '</tr>';
				$content .= '</tbody>';
				$content .= '</table>';
		}

		$content = apply_filters('_mp_cart_login', $content);
		if ($echo)
				echo $content;
		else
				return $content;
}
endif;


if (!function_exists('_mp_cart_shipping')) :
//Prints cart shipping form, for internal use
function _mp_cart_shipping($editable = false, $echo = false) {
		global $mp, $current_user;

		$meta = wp_parse_args(get_user_meta($current_user->ID, 'mp_shipping_info', true), array(
			'address1' => '',
			'address2' => '',
			'city' => '',
			'state' => '',
			'zip' => '',
			'country' => '',
			'phone' => '',
		));

		//get address
		$email = (!empty($_SESSION['mp_shipping_info']['email'])) ? $_SESSION['mp_shipping_info']['email'] : (isset($meta['email']) ? $meta['email'] : $current_user->user_email);
		$name = (!empty($_SESSION['mp_shipping_info']['name'])) ? $_SESSION['mp_shipping_info']['name'] : (isset($meta['name']) ? $meta['name'] : $current_user->user_firstname . ' ' . $current_user->user_lastname);
		$address1 = (!empty($_SESSION['mp_shipping_info']['address1'])) ? $_SESSION['mp_shipping_info']['address1'] : $meta['address1'];
		$address2 = (!empty($_SESSION['mp_shipping_info']['address2'])) ? $_SESSION['mp_shipping_info']['address2'] : $meta['address2'];
		$city = (!empty($_SESSION['mp_shipping_info']['city'])) ? $_SESSION['mp_shipping_info']['city'] : $meta['city'];
		$state = (!empty($_SESSION['mp_shipping_info']['state'])) ? $_SESSION['mp_shipping_info']['state'] : $meta['state'];
		$zip = (!empty($_SESSION['mp_shipping_info']['zip'])) ? $_SESSION['mp_shipping_info']['zip'] : $meta['zip'];
		$country = (!empty($_SESSION['mp_shipping_info']['country'])) ? $_SESSION['mp_shipping_info']['country'] : $meta['country'];
		if (!$country)
				$country = $mp->get_setting('base_country', 'US');
		$phone = (!empty($_SESSION['mp_shipping_info']['phone'])) ? $_SESSION['mp_shipping_info']['phone'] : $meta['phone'];
		$special_instructions = (!empty($_SESSION['mp_shipping_info']['special_instructions'])) ? $_SESSION['mp_shipping_info']['special_instructions'] : '';

		$content = '';
		//don't show if logged in
		if (!is_user_logged_in() && MP_HIDE_LOGIN_OPTION !== true && $editable) {
				$content .= '<p class="mp_cart_login_msg">';
				$content .= __('Made a purchase here before?', 'mp') . ' <a class="mp_cart_login_link" href="' . wp_login_url(mp_checkout_step_url('shipping')) . '">' . __('Login now to retrieve your saved info &raquo;', 'mp') . '</a>';
				$content .= '</p>';
		}

		if ($editable) {
				$content .= '<form id="mp_shipping_form" method="post" action="">';

				//Flag used by ajax to alert a submit of any errors that may have occured on the fornt end.
				//Set to "1" when the ajax starts so that if the ajax fails the failure will autonmatically be flagged.
				//Left as "1" is ajax returns an error
				$content .= '<input type="hidden" id="mp_no_shipping_options" name="no_shipping_options" value="0" />';

			 $content .= apply_filters('mp_checkout_before_shipping', '');

				$content .= '<table class="mp_cart_shipping">';
				$content .= '<thead><tr>';
				$content .= '<th colspan="2">' . ((($mp->download_only_cart($mp->get_cart_contents()) && !$mp->global_cart) || $mp->get_setting('shipping->method') != 'none') ? __('Enter Your Checkout Information:', 'mp') : __('Enter Your Shipping Information:', 'mp')) . '</th>';
				$content .= '</tr></thead>';
				$content .= '<tbody>';
				$content .= '<tr>';
				$content .= '<td align="right">' . __('Email:', 'mp') . '*</td><td>';
				$content .= apply_filters('mp_checkout_error_email', '');
				$content .= '<input size="35" name="email" type="text" value="' . esc_attr($email) . '" /></td>';
				$content .= '</tr>';

				if ((!$mp->download_only_cart($mp->get_cart_contents()) || $mp->global_cart || $mp->get_setting('tax->downloadable_address')) && $mp->get_setting('shipping->method') != 'none') {
						$content .= '<tr>';
						$content .= '<td align="right">' . __('Full Name:', 'mp') . '*</td><td>';
						$content .= apply_filters('mp_checkout_error_name', '');
						$content .= '<input size="35" name="name" type="text" value="' . esc_attr($name) . '" /> </td>';
						$content .= '</tr>';
						$content .= '<tr>';
						$content .= '<td align="right">' . __('Country:', 'mp') . '*</td><td>';
						$content .= apply_filters('mp_checkout_error_country', '');
						$content .= '<select id="mp_country" name="country" class="mp_shipping_field">';
						foreach ($mp->get_setting('shipping->allowed_countries', array()) as $code) {
								$content .= '<option value="' . $code . '"' . selected($country, $code, false) . '>' . esc_attr($mp->countries[$code]) . '</option>';
						}
						$content .= '</select>';
						$content .= '</td>';
						$content .= '</tr>';
						$content .= '<tr>';
						$content .= '<td align="right">' . __('Address:', 'mp') . '*</td><td>';
						$content .= apply_filters('mp_checkout_error_address1', '');
						$content .= '<input size="45" name="address1" type="text" value="' . esc_attr($address1) . '" /><br />';
						$content .= '<small><em>' . __('Street address, P.O. box, company name, c/o', 'mp') . '</em></small>';
						$content .= '</td>';
						$content .= '</tr>';
						$content .= '<tr>';
						$content .= '<td align="right">' . __('Address 2:', 'mp') . '&nbsp;</td><td>';
						$content .= '<input size="45" name="address2" type="text" value="' . esc_attr($address2) . '" /><br />';
						$content .= '<small><em>' . __('Apartment, suite, unit, building, floor, etc.', 'mp') . '</em></small>';
						$content .= '</td>';
						$content .= '</tr>';
						$content .= '<tr>';
						$content .= '<td align="right">' . __('City:', 'mp') . '*</td><td>';
						$content .= apply_filters('mp_checkout_error_city', '');
						$content .= '<input size="25" id="mp_city" class="mp_shipping_field" name="city" type="text" value="' . esc_attr($city) . '" /></td>';
						$content .= '</tr>';
						$content .= '<tr>';
						$content .= '<td align="right">' . __('State/Province/Region:', 'mp') . (($country == 'US' || $country == 'CA') ? '*' : '') . '</td><td id="mp_province_field">';
						$content .= apply_filters('mp_checkout_error_state', '');
						$content .= mp_province_field($country, $state) . '</td>';
						$content .= '</tr>';
						$content .= '<tr' . (( array_key_exists($country, $mp->countries_no_postcode) ) ? ' style="display:none"' : '') . '>';
						$content .= '<td align="right">' . __('Postal/Zip Code:', 'mp') . '*</td><td>';
						$content .= apply_filters('mp_checkout_error_zip', '');
						$content .= '<input size="10" class="mp_shipping_field" id="mp_zip" name="zip" type="text" value="' . esc_attr($zip) . '" /></td>';
						$content .= '</tr>';
						$content .= '<tr>';
						$content .= '<td align="right">' . __('Phone Number:', 'mp') . '</td><td>';
						$content .= '<input size="20" name="phone" type="text" value="' . esc_attr($phone) . '" /></td>';
						$content .= '</tr>';
				}

				if ($mp->get_setting('special_instructions')) {
						$content .= '<tr>';
						$content .= '<td align="right">' . __('Special Instructions:', 'mp') . '</td><td>';
						$content .= '<textarea name="special_instructions" rows="2" style="width: 100%;">' . esc_textarea($special_instructions) . '</textarea></td>';
						$content .= '</tr>';
				}

				if ( !$mp->download_only_cart($mp->get_cart_contents()) ) {
					$content .= apply_filters('mp_checkout_shipping_field', '');
				}

				$content .= '</tbody>';
				$content .= '</table>';

				$content .= apply_filters('mp_checkout_after_shipping', '');

				$content .= '<p class="mp_cart_direct_checkout">';
				$content .= '<input type="submit" name="mp_shipping_submit" id="mp_shipping_submit" value="' . __('Continue Checkout &raquo;', 'mp') . '" />';
				$content .= '</p>';
				$content .= '</form>';
		} else if (!$mp->download_only_cart($mp->get_cart_contents())) { //is not editable and not download only
				$content .= '<table class="mp_cart_shipping">';
				$content .= '<thead><tr>';
				$content .= '<th>' . __('Shipping Information:', 'mp') . '</th>';
				$content .= '<th align="right"><a href="' . mp_checkout_step_url('shipping') . '">' . __('Edit', 'mp') . '</a></th>';
				$content .= '</tr></thead>';
				$content .= '<tbody>';
				$content .= '<tr>';
				$content .= '<td align="right">' . __('Email:', 'mp') . '</td><td>';
				$content .= esc_attr($email) . ' </td>';
				$content .= '</tr>';
				$content .= '<tr>';
				$content .= '<td align="right">' . __('Full Name:', 'mp') . '</td><td>';
				$content .= esc_attr($name) . '</td>';
				$content .= '</tr>';
				$content .= '<tr>';
				$content .= '<td align="right">' . __('Address:', 'mp') . '</td>';
				$content .= '<td>' . esc_attr($address1) . '</td>';
				$content .= '</tr>';

				if ($address2) {
						$content .= '<tr>';
						$content .= '<td align="right">' . __('Address 2:', 'mp') . '</td>';
						$content .= '<td>' . esc_attr($address2) . '</td>';
						$content .= '</tr>';
				}

				$content .= '<tr>';
				$content .= '<td align="right">' . __('City:', 'mp') . '</td>';
				$content .= '<td>' . esc_attr($city) . '</td>';
				$content .= '</tr>';

				if ($state) {
						$content .= '<tr>';
						$content .= '<td align="right">' . __('State/Province/Region:', 'mp') . '</td>';
						$content .= '<td>' . esc_attr($state) . '</td>';
						$content .= '</tr>';
				}

				$content .= '<tr>';
				$content .= '<td align="right">' . __('Postal/Zip Code:', 'mp') . '</td>';
				$content .= '<td>' . esc_attr($zip) . '</td>';
				$content .= '</tr>';

				$content .= '<tr>';
				$content .= '<td align="right">' . __('Country:', 'mp') . '</td>';
				$content .= '<td>' . $mp->countries[$country] . '</td>';
				$content .= '</tr>';

				if ($phone) {
						$content .= '<tr>';
						$content .= '<td align="right">' . __('Phone Number:', 'mp') . '</td>';
						$content .= '<td>' . esc_attr($phone) . '</td>';
						$content .= '</tr>';
				}

				if ( !$mp->download_only_cart($mp->get_cart_contents()) ) {
					$content .= apply_filters('mp_checkout_shipping_field_readonly', '');
				}

				$content .= '</tbody>';
				$content .= '</table>';
		}

		$content = apply_filters('_mp_cart_shipping', $content, $editable);

		if ($echo) {
				echo $content;
		} else {
				return $content;
		}
}
endif;


if (!function_exists('_mp_cart_payment')) :
//Prints cart payment gateway form, for internal use
function _mp_cart_payment($type, $echo = false) {
		global $mp, $blog_id, $mp_gateway_active_plugins;
		$blog_id = (is_multisite()) ? $blog_id : 1;

		$cart = $mp->get_cart_contents($mp->global_cart);

		$content = '';
		if ($type == 'form') {
				$content = '<form id="mp_payment_form" method="post" action="' . mp_checkout_step_url('checkout') . '">';
				if (count((array) $mp_gateway_active_plugins) == 1) {
						$content .= '<input type="hidden" name="mp_choose_gateway" value="' . $mp_gateway_active_plugins[0]->plugin_name . '" />';
				} else if (count((array) $mp_gateway_active_plugins) > 1) {
						$content .= '<table class="mp_cart_payment_methods">';
						$content .= '<thead><tr>';
						$content .= '<th>' . __('Choose a Payment Method:', 'mp') . '</th>';
						$content .= '</tr></thead>';
						$content .= '<tbody><tr><td>';
						foreach ((array) $mp_gateway_active_plugins as $plugin) {
								$content .= '<label>';
								$content .= '<input type="radio" class="mp_choose_gateway" name="mp_choose_gateway" value="' . $plugin->plugin_name . '" ' . checked($_SESSION['mp_payment_method'], $plugin->plugin_name, false) . '/>';
								if ($plugin->method_img_url) {
										$content .= '<img src="' . $plugin->method_img_url . '" alt="' . $plugin->public_name . '" />';
								}
								$content .= $plugin->public_name;
								$content .= '</label>';
						}
						$content .= '</td>';
						$content .= '</tr>';
						$content .= '</tbody>';
						$content .= '</table>';
				}

				$content .= apply_filters('mp_checkout_payment_form', '', $cart, $_SESSION['mp_shipping_info']);

				$content .= '</form>';
		} else if ($type == 'confirm') {

				//if skipping a step
				if (empty($_SESSION['mp_payment_method'])) {
						$content .= '<div class="mp_checkout_error">' . sprintf(__('Whoops, looks like you skipped a step! Please <a href="%s">go back and try again</a>.', 'mp'), mp_checkout_step_url('checkout')) . '</div>';
						return $content;
				}
				$content .= '<form id="mp_payment_form" method="post" action="' . add_query_arg(array()) . '">';

				$content .= apply_filters('mp_checkout_confirm_payment_' . $_SESSION['mp_payment_method'], $cart, $_SESSION['mp_shipping_info']);

				$content .= '<p class="mp_cart_direct_checkout">';
				$content .= '<input type="submit" name="mp_payment_confirm" id="mp_payment_confirm" value="' . __('Confirm Payment &raquo;', 'mp') . '" />';
				$content .= '</p>';
				$content .= '</form>';
		} else if ($type == 'confirmation') {
				//gateway plugin message hook
				$content .= apply_filters('mp_checkout_payment_confirmation_' . $_SESSION['mp_payment_method'], '', $mp->get_order($_SESSION['mp_order']));

				if (!$mp->global_cart) {
						//tracking information
						$track_link = '<a href="' . mp_orderstatus_link(false, true) . $_SESSION['mp_order'] . '/' . '">' . mp_orderstatus_link(false, true) . $_SESSION['mp_order'] . '/' . '</a>';
						$content .= '<p>' . sprintf(__('You may track the latest status of your order(s) here:<br />%s', 'mp'), $track_link) . '</p>';
				}

				//add ecommerce JS
				$mp->create_ga_ecommerce($mp->get_order($_SESSION['mp_order']));

				//clear cart session vars
				unset($_SESSION['mp_payment_method']);
				unset($_SESSION['mp_order']);
		}

		$content = apply_filters('_mp_cart_payment', $content, $type);

		if ($echo) {
				echo $content;
		} else {
				return $content;
		}
}
endif;


if (!function_exists('mp_show_cart')) :
/**
 * Echos the current shopping cart contents. Use in the cart template.
 *
 * @param string $context Optional. Possible values: widget, checkout
 * @param string $checkoutstep Optional. Possible values: checkout-edit, shipping, checkout, confirm-checkout, confirmation
 * @param bool $echo Optional. default true
 */
function mp_show_cart($context = '', $checkoutstep = null, $echo = true) {
		global $mp, $blog_id;
		$content = '';

		if ($checkoutstep == null)
				$checkoutstep = get_query_var('checkoutstep');

		if (mp_items_in_cart() || $checkoutstep == 'confirmation') {

				if ($context == 'widget') {
						$content .= _mp_cart_table('widget');
						$content .= '<div class="mp_cart_actions_widget">';
						$content .= '<a class="mp_empty_cart" href="' . mp_cart_link(false, true) . '?empty-cart=1" title="' . __('Empty your shopping cart', 'mp') . '">' . __('Empty Cart', 'mp') . '</a>';
						$content .= '<a class="mp_checkout_link" href="' . mp_cart_link(false, true) . '" title="' . __('Go To Checkout Page', 'mp') . '">' . __('Checkout &raquo;', 'mp') . '</a>';
						$content .= '</div>';
				} else if ($context == 'checkout') {

					if ($mp->get_setting('show_purchase_breadcrumbs') == 1) {
							$content .= mp_cart_breadcrumbs($checkoutstep);
					}

					//generic error message context for plugins to hook into
					$content .= apply_filters('mp_checkout_error_checkout', '');


						//handle checkout steps
						switch ($checkoutstep) {

								case 'shipping':
										$content .= do_shortcode($mp->get_setting('msg->shipping'));
										$content .= _mp_cart_shipping(true);
										break;

								case 'checkout':
										$content .= do_shortcode($mp->get_setting('msg->checkout'));
										$content .= _mp_cart_payment('form');
										break;

								case 'confirm-checkout':
										$content .= do_shortcode($mp->get_setting('msg->confirm_checkout'));
										$content .= _mp_cart_table('checkout');
										$content .= _mp_cart_shipping(false);
										$content .= _mp_cart_payment('confirm');
										break;

								case 'confirmation':
										$content .= do_shortcode($mp->get_setting('msg->success'));
										$content .= _mp_cart_payment('confirmation');
										break;

								default:
										$content .= do_shortcode($mp->get_setting('msg->cart'));
										$content .= _mp_cart_table('checkout-edit');
										$content .= _mp_cart_login(false);
										break;
						}
				} else {
						$content .= _mp_cart_table('checkout');
						$content .= '<div class="mp_cart_actions">';
						$content .= '<a class="mp_empty_cart" href="' . mp_cart_link(false, true) . '?empty-cart=1" title="' . __('Empty your shopping cart', 'mp') . '">' . __('Empty Cart', 'mp') . '</a>';
						$content .= '<a class="mp_checkout_link" href="' . mp_cart_link(false, true) . '" title="' . __('Go To Checkout Page', 'mp') . '">' . __('Checkout &raquo;', 'mp') . '</a>';
						$content .= '</div>';
				}
		} else {
				if ($context != 'widget')
						$content .= do_shortcode($mp->get_setting('msg->cart'));

				$content .= '<div class="mp_cart_empty">' . __('There are no items in your cart.', 'mp') . '</div>';
				$content .= '<div id="mp_cart_actions_widget"><a class="mp_store_link" href="' . mp_products_link(false, true) . '">' . __('Browse Products &raquo;', 'mp') . '</a></div>';
		}

		$content = apply_filters('mp_show_cart', $content, $context, $checkoutstep);

		if ($echo) {
				echo $content;
		} else {
				return $content;
		}
}
endif;

if (!function_exists('mp_order_status')) :
/**
 * Echos the order status page. Use in the mp_orderstatus.php template.
 *
 */
function mp_order_status( $echo = true ) {
		global $mp, $wp_query, $blog_id;

		$bid = (is_multisite()) ? $blog_id : 1; // FPM: Used for Custom Field Processing
		$content = do_shortcode($mp->get_setting('msg->order_status'));
		$order_id = isset($wp_query->query_vars['order_id']) ? $wp_query->query_vars['order_id'] : (isset($_GET['order_id']) ? $_GET['order_id'] : '');
		$order = false;

		if (!empty($order_id)) {
				//get order
				$order = $mp->get_order($order_id);

				if ($order) { //valid order
						$content .= '
							<h2><em>' . sprintf(__('Order Details (%s):', 'mp'), esc_html($order_id)) . '</em></h2>
							<h3>' . apply_filters('mp_order_status_section_title', __('Current Status', 'mp'), $order) . '</h3>
							<ul>';

								//get times
								$received = isset($order->mp_received_time) ? $mp->format_date($order->mp_received_time) : '';
								if (!empty($order->mp_paid_time))
										$paid = $mp->format_date($order->mp_paid_time);
								if (!empty($order->mp_shipped_time))
										$shipped = $mp->format_date($order->mp_shipped_time);

								if ($order->post_status == 'order_received') {
										$content .= '<li>' . apply_filters('mp_order_status_label_received', __('Received', 'mp'), $order) . ' <strong>' . $received . '</strong></li>';
								} else if ($order->post_status == 'order_paid') {
										$content .= '<li>' . apply_filters('mp_order_status_label_paid', __('Paid', 'mp'), $order) . ' <strong>' . $paid . '</strong></li>';
										$content .= '<li>' . apply_filters('mp_order_status_label_received', __('Received', 'mp'), $order) . ' <strong>' . $received . '</strong></li>';
								} else if ($order->post_status == 'order_shipped' || $order->post_status == 'order_closed') {
										$content .= '<li>' . apply_filters('mp_order_status_label_shipped', __('Shipped', 'mp'), $order) . ' <strong>' . $shipped . '</strong></li>';
										$content .= '<li>' . apply_filters('mp_order_status_label_paid', __('Paid', 'mp'), $order) . ' <strong>' . $paid . '</strong></li>';
										$content .= '<li>' . apply_filters('mp_order_status_label_received', __('Received', 'mp'), $order) . ' <strong>' . $received . '</strong></li>';
								}

								$order_paid = $order->post_status != 'order_received';
								$max_downloads = $mp->get_setting('max_downloads', 5);

						$content .= '
						</ul>

						<h3>' . apply_filters('mp_order_status_section_title_payment_info', __('Payment Information', 'mp'), $order) . '</h3>
						<ul>
								<li>' .
										apply_filters('mp_order_status_label_payment_method', __('Payment Method', 'mp'), $order) . '
										<strong>' . $order->mp_payment_info['gateway_public_name'] . '</strong>
								</li>
								<li>' .
										apply_filters('mp_order_status_label_payment_type', __('Payment Type', 'mp'), $order) . '
										<strong>' . $order->mp_payment_info['method'] . '</strong>
								</li>
								<li>' .
										apply_filters('mp_order_status_label_trans_id', __('Transaction ID', 'mp'), $order) . '
										<strong>' . $order->mp_payment_info['transaction_id'] . '</strong>
								</li>
								<li>' .
										apply_filters('mp_order_status_label_payment_total', __('Payment Total', 'mp'), $order) . '
										<strong>' . $mp->format_currency($order->mp_payment_info['currency'], $order->mp_payment_info['total']) . ' ' . $order->mp_payment_info['currency'] . '</strong>
								</li>
						</ul>

						<h3>' . apply_filters('mp_order_status_section_title_order_info', __('Order Information', 'mp'), $order) . '</h3>
						<table id="mp-order-product-table" class="mp_cart_contents">
								<thead><tr>
												<th class="mp_cart_col_thumb">&nbsp;</th>
												<th class="mp_cart_col_product">' . apply_filters('mp_order_status_label_item', __('Item', 'mp'), $order) . '</th>
												<th class="mp_cart_col_quant">' . apply_filters('mp_order_status_label_quantity', __('Quantity', 'mp'), $order) . '</th>
												<th class="mp_cart_col_price">' . apply_filters('mp_order_status_label_price', __('Price', 'mp'), $order) . '</th>
												<th class="mp_cart_col_subtotal">' . apply_filters('mp_order_status_label_subtotal', __('Subtotal', 'mp'), $order) . '</th>
												<th class="mp_cart_col_downloads">' . apply_filters('mp_order_status_label_download', __('Download', 'mp'), $order) . '</th>
										</tr></thead>
								<tbody>';

										$coupon_code = is_array($order->mp_discount_info) ? $order->mp_discount_info['code'] : '';

										if (is_array($order->mp_cart_info) && count($order->mp_cart_info)) {
												foreach ($order->mp_cart_info as $product_id => $variations) {
														//for compatibility for old orders from MP 1.x
														if (isset($variations['name'])) {
																$data = $variations;
																$price = $data['price'] * $data['quantity'];
																$discount_price = $mp->coupon_value_product($coupon_code, $price, $product_id);
																$price_text = '';
																$subtotal_text = '';

																//price text
																if ( $price != $discount_price ) {
																	$price_text = '<del>' . $mp->format_currency('', $price / $data['quantity']) . '</del><br />';
																}

																$price_text .= $mp->format_currency('', $discount_price / $data['quantity']);

																//subtotal text
																if ( $price != $discount_price ) {
																	$subtotal_text .= '<del>' . $mp->format_currency('', $price) . '</del><br />';
																}
																$subtotal_text .= $mp->format_currency('', $discount_price);

																$content .= '<tr>';
																$content .= '	<td class="mp_cart_col_thumb">' . mp_product_image(false, 'widget', $product_id) . '</td>';
																$content .= '	<td class="mp_cart_col_product"><a href="' . apply_filters('mp_product_url_display_in_cart', get_permalink($product_id), $product_id) . '">' . apply_filters('mp_product_name_display_in_cart', $data['name'], $product_id) . '</a>' . '</td>'; // Added WPML (This differs than other code)
																$content .= '	<td class="mp_cart_col_quant">' . number_format_i18n($data['quantity']) . '</td>';
																$content .= '	<td class="mp_cart_col_price">' . $price_text . '</td>';
																$content .= '	<td class="mp_cart_col_subtotal">' . $subtotal_text . '</td>';
																$content .= '	<td class="mp_cart_col_downloads"></td>';
																$content .= '</tr>';
														} else {
																foreach ($variations as $variation => $data) {
																		$price = $data['price'] * $data['quantity'];
																		$discount_price = $mp->coupon_value_product($coupon_code, $price, $product_id);
																		$price_text = '';
																		$subtotal_text = '';

																		//price text
																		if ( $price != $discount_price ) {
																			$price_text = '<del>' . $mp->format_currency('', $price / $data['quantity']) . '</del><br />';
																		}

																		$price_text .= $mp->format_currency('', $discount_price / $data['quantity']);

																		//subtotal text
																		if ( $price != $discount_price ) {
																			$subtotal_text .= '<del>' . $mp->format_currency('', $price) . '</del><br />';
																		}
																		$subtotal_text .= $mp->format_currency('', $discount_price);

																		$content .= '<tr>';
																		$content .= '	<td class="mp_cart_col_thumb">' . mp_product_image(false, 'widget', $product_id) . '</td>';
																		$content .= '	<td class="mp_cart_col_product"><a href="' . apply_filters('mp_product_url_display_in_cart', get_permalink($product_id), $product_id) . '">' . apply_filters('mp_product_name_display_in_cart', $data['name'], $product_id) . '</a>';

																		// Output product custom field information
																		$cf_key = $bid . ':' . $product_id . ':' . $variation;
																		if (isset($order->mp_shipping_info['mp_custom_fields'][$cf_key])) {
																				$cf_item = $order->mp_shipping_info['mp_custom_fields'][$cf_key];

																				$mp_custom_field_label = get_post_meta($product_id, 'mp_custom_field_label', true);
																				if (isset($mp_custom_field_label[$variation]))
																						$label_text = $mp_custom_field_label[$variation];
																				else
																						$label_text = __('Product Personalization:', 'mp');

																				$content .= '<div class="mp_cart_custom_fields">' . apply_filters('mp_order_status_label_custom_fields', $label_text, $order) . '<br />';
																				foreach ($cf_item as $item) {
																						$content .= $item;
																				}
																				$content .= '</div>';
																		}

																		$content .= '</td>';
																		$content .= '	<td class="mp_cart_col_quant">' . number_format_i18n($data['quantity']) . '</td>';
																		$content .= '	<td class="mp_cart_col_price">' . $price_text . '</td>';
																		$content .= '	<td class="mp_cart_col_subtotal">' . $subtotal_text . '</td>';
																		if (is_array($data['download']) && $download_url = $mp->get_download_url($product_id, $order->post_title)) {
																				if ($order_paid) {
																						//check for too many downloads
																						if (intval($data['download']['downloaded']) < $max_downloads)
																								$content .= '	<td class="mp_cart_col_downloads"><a href="' . $download_url . '">' . apply_filters('mp_order_status_label_download', __('Download&raquo;', 'mp'), $order) . '</a></td>';
																						else
																								$content .= '	<td class="mp_cart_col_downloads">' . apply_filters('mp_order_status_label_limit_reached', __('Limit Reached', 'mp'), $order) . '</td>';
																				} else {
																						$content .= '	<td class="mp_cart_col_downloads">' . apply_filters('mp_order_status_label_awaiting_payment', __('Awaiting Payment', 'mp'), $order) . '</td>';
																				}
																		} else {
																				$content .= '	<td class="mp_cart_col_downloads"></td>';
																		}
																		$content .= '</tr>';
																}
														}
												}
										} else {
												$content .= '<tr><td colspan="6">' . apply_filters('mp_order_status_label_no_products_found', __('No products could be found for this order', 'mp'), $order) . '</td></tr>';
										}

								$content .= '
								</tbody>
						</table>
						<ul>';

								//coupon line
								if ($order->mp_discount_info) {
									$content .= '<li>' . apply_filters('mp_order_status_label_coupon_discount', __('Coupon', 'mp'), $order) . ': <strong>' . strtoupper($order->mp_discount_info['code']) . '</strong></li>';
								}

								//shipping line
								if ($order->mp_shipping_total) {
									$content .= '<li>' . apply_filters('mp_order_status_label_shipping', __('Shipping', 'mp'), $order) . ': <strong>' . $mp->format_currency('', $mp->get_display_shipping($order)) . '</strong></li>';
								}

								//tax line
								if ($order->mp_tax_total) {
									$content .= '<li>' . esc_html($mp->get_setting('tax->label', __('Taxes', 'mp'))) . ': <strong>' . $mp->format_currency('', $order->mp_tax_total) . '</strong></li>';
								}

								$content .= '<li>' . apply_filters('mp_order_status_label_order_total', __('Order Total', 'mp'), $order) . ': <strong>' . $mp->format_currency('', $order->mp_order_total) . '</strong></li>
						</ul>';

						if ( MP_HIDE_ORDERSTATUS_SHIPPING !== true ) {
							$content .= '
								<h3>' . apply_filters('mp_order_status_section_title_shipping_info', __('Shipping Information', 'mp'), $order) . '</h3>
								<table class="mp_cart_shipping">
										<tr>
												<td align="right">' . apply_filters('mp_order_status_label_full_name', __('Full Name', 'mp'), $order) . '</td>
												<td>' . esc_attr($order->mp_shipping_info['name']) . '</td>
										</tr>

										<tr>
												<td align="right">' . apply_filters('mp_order_status_label_address', __('Address', 'mp'), $order) . '</td>
												<td>' . esc_attr($order->mp_shipping_info['address1']) . '</td>
										</tr>';

										if ($order->mp_shipping_info['address2']) {
												$content .= '
										<tr>
												<td align="right">' . apply_filters('mp_order_status_label_address_2', __('Address 2', 'mp'), $order) . '</td>
												<td>' . esc_attr($order->mp_shipping_info['address2']) . '</td>
										</tr>';
										}

										$content .= '
										<tr>
												<td align="right">' . apply_filters('mp_order_status_label_city', __('City', 'mp'), $order) . '</td>
												<td>' . esc_attr($order->mp_shipping_info['city']) . '</td>
										</tr>';

										if ($order->mp_shipping_info['state']) {
											$content .= '
										<tr>
												<td align="right">' . apply_filters('mp_order_status_label_state', __('State/Province/Region', 'mp'), $order) . '</td>
												<td>' . esc_attr($order->mp_shipping_info['state']) . '</td>
										</tr>';
										}

										$content .= '
										<tr>
												<td align="right">' . apply_filters('mp_order_status_label_zip_code', __('Postal/Zip Code', 'mp'), $order) . '</td>
												<td>' . esc_attr($order->mp_shipping_info['zip']) . '</td>
										</tr>

										<tr>
												<td align="right">' . apply_filters('mp_order_status_label_country', __('Country', 'mp'), $order) . '</td>
												<td>' . $mp->countries[$order->mp_shipping_info['country']] . '</td>
										</tr>';

										if ($order->mp_shipping_info['phone']) {
											$content .= '
										<tr>
												<td align="right">' . apply_filters('mp_order_status_label_phone_number', __('Phone Number', 'mp'), $order) . '</td>
												<td>' . esc_attr($order->mp_shipping_info['phone']) . '</td>
										</tr>';
										}

										if (isset($order->mp_shipping_info['tracking_num'])) {
											$content .= '
										<tr>
												<td align="right">' . apply_filters('mp_order_status_label_tracking_number', __('Tracking Number', 'mp'), $order) . '</td>
												<td>' . mp_tracking_link($order->mp_shipping_info['tracking_num'], $order->mp_shipping_info['method']) . '</td>
										</tr>';
										}

							$content .= '
								</table>';
						}

						if (isset($order->mp_order_notes)) {
							$content .= '
								<h3>' . apply_filters('mp_order_status_section_title_order_notes', __('Order Notes', 'mp'), $order) . '</h3>' .
								wpautop($order->mp_order_notes);
						}

						$content .= mp_orderstatus_link(false, false, __('&laquo; Back', 'mp'));
				} else { //not valid order id
						$content .= '
								<h3>' . apply_filters('mp_order_status_section_title_invalid_order_id', __('Invalid Order ID. Please try again:', 'mp'), $order) . '</h3>
								<form action="' . mp_orderstatus_link(true, true) . '" method="get">
										<label>' . apply_filters('mp_order_status_label_enter_order_number', __('Enter your 12-digit Order ID number:', 'mp'), $order) . '<br />
												<input type="text" name="order_id" id="order_id" class="input" value="" size="20" /></label>
										<input type="submit" id="order-id-submit" value="' . apply_filters('mp_order_status_label_look_up_button', __('Look Up &raquo;', 'mp'), $order) . '" />
								</form>';
				}
		} else {
				//get from usermeta
				$user_id = get_current_user_id();
				if ($user_id) {
						if (is_multisite()) {
								global $blog_id;
								$meta_id = 'mp_order_history_' . $blog_id;
						} else {
								$meta_id = 'mp_order_history';
						}
						$orders = get_user_meta($user_id, $meta_id, true);
				} else {
						//get from cookie
						if (is_multisite()) {
								global $blog_id;
								$cookie_id = 'mp_order_history_' . $blog_id . '_' . COOKIEHASH;
						} else {
								$cookie_id = 'mp_order_history_' . COOKIEHASH;
						}

						if (isset($_COOKIE[$cookie_id]))
								$orders = unserialize($_COOKIE[$cookie_id]);
				}

				if (is_array($orders) && count($orders)) {
					krsort($orders);
					//list orders
					$content .= '<h3>' . apply_filters('mp_order_status_label_recent_orders', __('Your Recent Orders:', 'mp'), $order) . '</h3>';
					$content .= '<ul id="mp-order-list">';

					//need to check for removed orders
					$resave_meta = false;
					foreach ($orders as $timestamp => $order) {
						if( $mp->get_order( $order['id'] ) ) {
							$content .= '	 <li><strong>' . $mp->format_date($timestamp) . ':</strong> ' . mp_orderstatus_link(false, false, $order['id'], $order['id']) . ' - ' . $mp->format_currency('', $order['total']) . '</li>';
						}else{
							unset( $orders[$timestamp] );
							$resave_meta = true;
						}
					}

					$content .= '</ul>';

					//if we need to resave we'll do it here
					if($resave_meta) {
						$_COOKIE[$cookie_id] = serialize($orders);
						update_user_meta($user_id, $meta_id, $orders );
					}

					$content .= '
						<form action="' . mp_orderstatus_link(false, true) . '" method="get">
								<label>' . apply_filters('mp_order_status_label_or_enter_order_number', __('Or enter your 12-digit Order ID number:', 'mp'), $order) . '<br />
										<input type="text" name="order_id" id="order_id" class="input" value="" size="20" /></label>
								<input type="submit" id="order-id-submit" value="' . apply_filters('mp_order_status_label_look_up_button', __('Look Up &raquo;', 'mp'), $order) . '" />
						</form>';
				} else {
						if (!is_user_logged_in()) {
							$content .= '
								<table class="mp_cart_login">
										<thead><tr>
														<th class="mp_cart_login" colspan="2">' . apply_filters('mp_order_status_label_login_to_view_order_history', __('Have a User Account? Login To View Your Order History:', 'mp'), $order) . '</th>
														<th>&nbsp;</th>
												</tr></thead>
										<tbody>
												<tr>
														<td class="mp_cart_login">
																<form name="loginform" id="loginform" action="' . wp_login_url() . '" method="post">
																		<label>' . apply_filters('mp_order_status_label_username', __('Username', 'mp'), $order) . '<br />
																				<input type="text" name="log" id="user_login" class="input" value="" size="20" /></label>
																		<br />
																		<label>' . apply_filters('mp_order_status_label_password', __('Password', 'mp'), $order) . '<br />
																				<input type="password" name="pwd" id="user_pass" class="input" value="" size="20" /></label>
																		<br />
																		<input type="submit" name="wp-submit" id="mp_login_submit" value="' . apply_filters('mp_order_status_label_login_button', __('Login &raquo;', 'mp'), $order) . '" />
																		<input type="hidden" name="redirect_to" value="' . mp_orderstatus_link(false, true) . '" />
																</form>
														</td>
														<td class="mp_cart_or_label">' . apply_filters('mp_order_status_label_or', __('or', 'mp'), $order) . '</td>
														<td class="mp_cart_checkout">
																<form action="' . mp_orderstatus_link(false, true) . '" method="get">
																		<label>' . apply_filters('mp_order_status_label_enter_order_number', __('Enter your 12-digit Order ID number:', 'mp'), $order) . '<br />
																				<input type="text" name="order_id" id="order_id" class="input" value="" size="20" /></label>
																		<input type="submit" id="order-id-submit" value="' . apply_filters('mp_order_status_label_look_up_button', __('Look Up &raquo;', 'mp'), $order) . '" />
																</form>
														</td>
												</tr>
										</tbody>
								</table>';
						} else {
							$content .= '
								<form action="' . mp_orderstatus_link(false, true) . '" method="get">
										<label>' . apply_filters('mp_order_status_label_enter_order_number', __('Enter your 12-digit Order ID number:', 'mp'), $order) . '<br />
												<input type="text" name="order_id" id="order_id" class="input" value="" size="20" /></label>
										<input type="submit" id="order-id-submit" value="' . apply_filters('mp_order_status_label_look_up_button', __('Look Up &raquo;', 'mp'), $order) . '" />
								</form>';
						}
				}
		}

		$content = apply_filters('mp_order_status', $content, $order);

		if ( $echo )
			echo $content;
		else
			return $content;
}
endif;


if (!function_exists('mp_tracking_link')) :
/*
 * function mp_tracking_link
 * @param string $tracking_number The tracking number string to turn into a link
 * @param string $method Shipping method, can be UPS, FedEx, USPS, DHL, or other (default)
 */
function mp_tracking_link($tracking_number, $method = 'other') {
		$tracking_number = esc_attr($tracking_number);
		if ($method == 'UPS')
				return '<a title="' . __('Track your UPS package &raquo;', 'mp') . '" href="http://wwwapps.ups.com/WebTracking/processInputRequest?sort_by=status&tracknums_displayed=1&TypeOfInquiryNumber=T&loc=en_us&InquiryNumber1=' . $tracking_number . '&track.x=0&track.y=0" target="_blank">' . $tracking_number . '</a>';
		else if ($method == 'FedEx')
				return '<a title="' . __('Track your FedEx package &raquo;', 'mp') . '" href="http://www.fedex.com/Tracking?language=english&cntry_code=us&tracknumbers=' . $tracking_number . '" target="_blank">' . $tracking_number . '</a>';
		else if ($method == 'USPS')
				return '<a title="' . __('Track your USPS package &raquo;', 'mp') . '" href="http://trkcnfrm1.smi.usps.com/PTSInternetWeb/InterLabelInquiry.do?origTrackNum=' . $tracking_number . '" target="_blank">' . $tracking_number . '</a>';
		else if ($method == 'DHL')
				return '<a title="' . __('Track your DHL package &raquo;', 'mp') . '" href="http://www.dhl.com/content/g0/en/express/tracking.shtml?brand=DHL&AWB=' . $tracking_number . '" target="_blank">' . $tracking_number . '</a>';
		else
				return apply_filters('mp_shipping_tracking_link', $tracking_number, $method);
}
endif;


if (!function_exists('mp_province_field')) :
/*
 * function mp_province_field
 * @param string $country two-digit country code
 * @param string $selected state code form value to be shown/selected
 */
function mp_province_field($country = 'US', $selected = null) {
		global $mp;

		if (defined('DOING_AJAX') && DOING_AJAX && isset($_POST['country']))
				$country = $_POST['country'];

		$list = false;
		if ($country == 'US')
				$list = $mp->usa_states;
		else if ($country == 'CA')
				$list = $mp->canadian_provinces;
		else if ($country == 'AU')
				$list = $mp->australian_states;

		$content = '';
		if ($list) {
				$content .= '<select id="mp_state" class="mp_shipping_field" name="state">';
				$content .= '<option value="">' . __('Select:', 'mp') . '</option>';
				foreach ($list as $abbr => $label)
						$content .= '<option value="' . $abbr . '"' . selected($selected, $abbr, false) . '>' . esc_attr($label) . '</option>';
				$content .= '</select>';
		} else {
				$content .= '<input size="15" id="mp_state" name="state" type="text" value="' . esc_attr($selected) . '" />';
		}

		$content = apply_filters('mp_province_field', $content, $country, $selected);

		//if ajax
		if (defined('DOING_AJAX') && DOING_AJAX)
				die($content);
		else
				return $content;
}
endif;


if (!function_exists('mp_list_products')) :
/*
 * function mp_list_products
 * Displays a list of products according to preference. Optional values default to the values in Presentation Settings -> Product List
 *
 * @param bool $echo Optional, whether to echo or return
 * @param bool $paginate Optional, whether to paginate
 * @param int $page Optional, The page number to display in the product list if $paginate is set to true.
 * @param int $per_page Optional, How many products to display in the product list if $paginate is set to true.
 * @param string $order_by Optional, What field to order products by. Can be: title, date, ID, author, price, sales, rand
 * @param string $order Optional, Direction to order products by. Can be: DESC, ASC
 * @param string $category Optional, limit to a product category
 * @param string $tag Optional, limit to a product tag
 * @param bool $list_view Optional, show as list. Default to presentation settings
 * @param bool $filters Optional, show filters
 */

function mp_list_products() {
	global $wp_query, $mp;

	$args = $mp->parse_args(func_get_args(), $mp->defaults['list_products']);
	$args['nopaging'] = false;

	$query = array(
		'post_type' => 'product',
		'post_status' => 'publish',
	);
	$tax_query = array();

	//setup taxonomies if possible
	if ( $wp_query->get('taxonomy') == 'product_category' ) {
		$tax_query[] = array(
			'taxonomy' => 'product_category',
			'field' => 'slug',
			'terms' => $wp_query->get('term'),
		);
	} elseif ( $wp_query->get('taxonomy') == 'product_tag' ) {
		$tax_query[] = array(
			'taxonomy' => 'product_tag',
			'field' => 'slug',
			'terms' => $wp_query->get('term'),
		);
	} elseif ( !is_null($args['category']) || !is_null($args['tag']) ) {
		if ( !is_null($args['category']) ) {
			$tax_query[] = array(
				'taxonomy' => 'product_category',
				'field' => 'slug',
				'terms' => sanitize_title($args['category']),
			);
		}

		if ( !is_null($args['tag']) ) {
			$tax_query[] = array(
				'taxonomy' => 'product_tag',
				'field' => 'slug',
				'terms' => sanitize_title($args['tag']),
			);
		}
	}

	if ( count($tax_query) > 1 ) {
		$query['tax_query'] = array_merge(array('relation' => 'AND'), $tax_query);
	} elseif ( count($tax_query) == 1 ) {
		$query['tax_query'] = $tax_query;
	}

	//setup pagination
	if ( (!is_null($args['paginate']) && !$args['paginate']) || (is_null($args['paginate']) && !$mp->get_setting('paginate')) ) {
		$query['nopaging'] = $args['nopaging'] = true;
	} else {
		//figure out perpage
		if ( !is_null($args['per_page']) ) {
			$query['posts_per_page'] = intval($args['per_page']);
		} else {
			$query['posts_per_page'] = intval($mp->get_setting('per_page'));
		}

		//figure out page
		if ( !is_null($args['page']) ) {
			$query['paged'] = intval($args['page']);
		} elseif ( $wp_query->get('paged') != '' ) {
			$query['paged'] = $args['page'] = intval($wp_query->get('paged'));
		}

		//get order by
		if ( is_null($args['order_by']) ) {
			if ( 'price' == $mp->get_setting('order_by') ) {
				$query['meta_key'] = 'mp_price_sort';
				$query['orderby'] = 'meta_value_num';
			} elseif ( 'sales' == $mp->get_setting('order_by') ) {
				$query['meta_key'] = 'mp_sales_count';
				$query['orderby'] = 'meta_value_num';
			} else {
				$query['orderby'] = $mp->get_setting('order_by');
			}
		} else {
			if ( 'price' == $args['order_by'] ) {
				$query['meta_key'] = 'mp_price_sort';
				$query['orderby'] = 'meta_value_num';
			} else if ( 'sales' == $args['order_by'] ) {
				$query['meta_key'] = 'mp_sales_count';
				$query['orderby'] = 'meta_value_num';
			} else {
				$query['orderby'] = $args['order_by'];
			}
		}
	}

	//get order direction
	if ( is_null($args['order']) ) {
		$query['order'] = $mp->get_setting('order');
	} else {
		$query['order'] = $args['order'];
	}

	//The Query
	$custom_query = new WP_Query($query);

	// get layout type for products
	if ( is_null($args['list_view']) ) {
		$layout_type = $mp->get_setting('list_view');
	} else {
		$layout_type = $args['list_view'] ? 'list' : 'grid';
	}

	$content = '';

	if ( defined('DOING_AJAX') && DOING_AJAX ) {
		//do nothing
	} else {
		$per_page = ( is_null($args['per_page']) ) ? null : $args['per_page'];
		$content .= ( (is_null($args['filters']) && 1 == $mp->get_setting('show_filters')) || $args['filters'] ) ? mp_products_filter(false, $per_page, $args['category'], $args['order_by'], $args['order']) : mp_products_filter(true, $per_page, $args['category'], $args['order_by'], $args['order']);
	}

	$content .= '<div id="mp_product_list" class="hfeed mp_' . $layout_type . '">';

	if ( $last = $custom_query->post_count ) {
		$content .= $layout_type == 'grid' ? _mp_products_html_grid($custom_query) : _mp_products_html_list($custom_query);
	} else {
		$content .= '<div id="mp_no_products">' . apply_filters('mp_product_list_none', __('No Products', 'mp')) . '</div>';
	}

	$content .= '</div>';
	$content .= ( ! $args['nopaging'] ) ? mp_products_nav(false, $custom_query) : '';

	$content = apply_filters('mp_list_products', $content, $args);

	if ( $args['echo'] ) {
		echo $content;
	} else {
		return $content;
	}
}
endif;

if (!function_exists('_mp_products_html_list')) :
function _mp_products_html_list( $custom_query ) {
		global $mp,$post;
		$html = '';
		$total = $custom_query->post_count;
		$count = 0;
		$current_post = $post;

		while ( $custom_query->have_posts() ) : $custom_query->the_post();
				$count = $custom_query->current_post + 1;

				//add last css class for styling grids
				if ($count == $total)
						$class = array('mp_product', 'last-product', 'hentry');
				else
						$class = array('mp_product', 'hentry');

				$html .= '
					<div itemscope itemtype="http://schema.org/Product" ' . mp_product_class(false, $class, $post->ID) . '>
						<h3 class="mp_product_name entry-title"><a href="' . get_permalink($post->ID) . '">' . $post->post_title . '</a></h3>
						<div class="entry-content">
							<div class="mp_product_content">';

				$product_content = mp_product_image(false, 'list', $post->ID);
				if ( $mp->get_setting('show_excerpt') ) {
					$product_content .= $mp->product_excerpt($post->post_excerpt, $post->post_content, $post->ID);
				}

				$html .= apply_filters('mp_product_list_content', $product_content, $post->ID);
				$html .= mp_pinit_button($post->ID,'all_view');
				$html .= '
							</div>
							<div class="mp_product_meta">';

				//price
				$meta = mp_product_price(false, $post->ID);
				//button
				$meta .= mp_buy_button(false, 'list', $post->ID);
				$html .= apply_filters('mp_product_list_meta', $meta, $post->ID);
				$html .= '
							</div>
						</div>
						<div style="display:none">
							<time class="updated">' . get_the_time('Y-m-d\TG:i') . '</time> by
							<span class="author vcard"><span class="fn">' . get_the_author_meta('display_name') . '</span></span>
						</div>
					</div>';
		endwhile;

		$post = $current_post; //wp_reset_postdata() doesn't work here for some reason

		return apply_filters('_mp_products_html_list', $html, $custom_query);
}
endif;

if (!function_exists('_mp_products_html_grid')) :
function _mp_products_html_grid( $custom_query ) {
		global $mp,$post;
		$html = '';
		$current_post = $post;

		//get image width
		if ($mp->get_setting('list_img_size') == 'custom') {
				$width = $mp->get_setting('list_img_width');
		} else {
				$size = $mp->get_setting('list_img_size');
				$width = get_option($size . "_size_w");
		}

		$inline_style = !( $mp->get_setting('store_theme') == 'none' || current_theme_supports('mp_style') );

		while ( $custom_query->have_posts() ) : $custom_query->the_post();
			$img = mp_product_image(false, 'list', $post->ID);
			$excerpt = $mp->get_setting('show_excerpt') ?
							'<p class="mp_excerpt">' . $mp->product_excerpt($post->post_excerpt, $post->post_content, $post->ID, '') . '</p>' :
							'';
			$mp_product_list_content = apply_filters('mp_product_list_content', $excerpt, $post->ID);

			$pinit = mp_pinit_button($post->ID, 'all_view');

			$class = array();
			$class[] = strlen($img) > 0 ? 'mp_thumbnail' : '';
			$class[] = strlen($excerpt) > 0 ? 'mp_excerpt' : '';
			$class[] = mp_has_variations($post->ID) ? 'mp_price_variations' : '';

			$html .= '
				<div itemscope itemtype="http://schema.org/Product" class="hentry mp_one_tile ' . implode($class, ' ') . '">
					<div class="mp_one_product"' . ($inline_style ? ' style="width: ' . $width . 'px;"' : '') . '>
						<div class="mp_product_detail"' . ($inline_style ? ' style="width: ' . $width . 'px;"' : '') . '>
							' . $img . '
							' . $pinit .'
							<h3 class="mp_product_name entry-title" itemprop="name">
								<a href="' . get_permalink($post->ID) . '">' . $post->post_title . '</a>
							</h3>

							<div>' . $mp_product_list_content . '</div>
						</div>

						<div class="mp_price_buy"' . ($inline_style ? ' style="width: ' . $width . 'px;"' : '') . '>
							' . mp_product_price(false, $post->ID) . '
							' . mp_buy_button(false, 'list', $post->ID) . '
							' . apply_filters('mp_product_list_meta', '', $post->ID) . '
						</div>

						<div style="display:none" >
							<span class="entry-title">' . get_the_title() . '</span> was last modified:
							<time class="updated">' . get_the_time('Y-m-d\TG:i') . '</time> by
							<span class="author vcard"><span class="fn">' . get_the_author_meta('display_name') . '</span></span>
						</div>
					</div>
				</div>';
		endwhile;

		$html .= ($custom_query->found_posts > 0) ? '<div class="clear"></div>' : '';

		$post = $current_post; //wp_reset_postdata() doesn't work here for some reason

		return apply_filters('_mp_products_html_grid', $html, $custom_query);
}
endif;


if (!function_exists('mp_has_variations')) :
/*
 * function mp_has_variations
 * Checks if a given product has price variations
 *
 * @param $post_id int The product or post id
 * @return bool Whether or not it has variations
 */

function mp_has_variations($post_id) {
		$mp_price = maybe_unserialize(get_post_meta($post_id, 'mp_price', true));
		return (is_array($mp_price) && count($mp_price) > 1);
}
endif;


if (!function_exists('mp_product_title')) :
/*
 * function mp_product_title
 * Displays a title of a single product according to preference
 *
 * @param bool $echo Optional, whether to echo or return
 * @param int $product_id the ID of the product to display
 * @param bool $link Whether to display title with or without a link
 * @param bool $formated Whether to display formated text (i.e h3 with a class) or not (just pure text)
 * @param string $html_tag title surrounding HTML tag (i.e. <h3>title</h3>)
 * @param string $css_class add custom css class to the title
 * @param string $microdata add additional information to HTML content which is more descriptive and suitable for search engines (learn more here http://schema.org/docs/gs.html)
 */

function mp_product_title($product_id, $echo = true, $link = false, $formated = true, $html_tag = 'h3', $css_class = 'mp_product_name', $microdata = 'itemprop="name"') {
		global $mp;

		$post = get_post($product_id);

		if ($link) {
				$title = '<a href="' . get_permalink($post->ID) . '">' . $post->post_title . '</a>';
		} else {
				$title = $post->post_title;
		}

		if ($formated) {
				$before_title = '<' . $html_tag . ' ' . $microdata . ' class="entry-title ' . $css_class . '">';
				$after_title = '</' . $html_tag . '>';
		} else {
				$before_title = '<span class="entry-title">';
				$after_title = '</span>';
		}

		$return = apply_filters('mp_product_title', $before_title . $title . $after_title, $product_id, $link, $formated, $html_tag, $css_class, $microdata);

		if ($echo)
				echo $return;
		else
				return $return;
}
endif;


if (!function_exists('mp_product_description')) :
/*
 * function mp_product_description
 * Displays a title of a single product according to preference
 *
 * @param bool $echo Optional, whether to echo or return
 * @param int $product_id the ID of the product to display
 * @param bool/string $content Whether and what type of content to display. Options are false, 'full', or 'excerpt'. Default 'full'
 * @param string $html_tag title surrounding HTML tag (i.e. <div>title</div>)
 * @param string $css_class add custom css class to the description
 * @param string $microdata add additional information to HTML content which is more descriptive and suitable for search engines (learn more here http://schema.org/docs/gs.html)
 */

function mp_product_description($product_id, $echo = true, $content = 'full', $html_tag = true, $css_class = 'mp_product_content', $microdata = 'itemprop="description"') {
		global $mp;

		$post = get_post($product_id);
		$description = '';

		if ($content == 'excerpt') {
				$description .= $mp->product_excerpt($post->post_excerpt, $post->post_content, $post->ID);
		} else {
				$description .= apply_filters('the_content', $post->post_content);
		}

		if ($html_tag) {
				$before_description = '<div ' . $microdata . ' class="' . $css_class . '">';
				$after_description = '</div>';
		} else {
				$before_description = '';
				$after_description = '';
		}

		$return = apply_filters('mp_product_description', $before_description . $description . $after_description, $product_id, $content, $html_tag, $css_class, $microdata);

		if ($echo)
				echo $return;
		else
				return $return;
}
endif;


if (!function_exists('mp_product_meta')) :
/*
 * function mp_product_meta
 * Displays the product meta box
 *
 * @param bool $echo Optional, whether to echo or return
 * @param string $context Options are list or single
 * @param int $product_id The post_id for the product. Optional if in the loop
 * @param sting $label A label to prepend to the price. Defaults to "Price: "
 * @param string $html_tag title surrounding HTML tag (i.e. <div>title</div>)
 * @param string $css_class add custom css class to the description
 */

function mp_product_meta($echo = true, $context = 'context', $label = true, $product_id = null, $html_tag = true, $css_class = 'mp_product_meta') {

		if ($html_tag) {
				$content = '<div class="'.$css_class.'">';
		}
		$content .= mp_product_price(false, $product_id, $label);
		$content .= mp_buy_button(false, $context, $product_id);
		if ($html_tag) {
				$content .= '</div>';
		}

		$content = apply_filters('mp_product_meta', $content, $context, $label, $product_id, $html_tag, $css_class);

		if ($echo)
				echo $content;
		else
				return $content;
}
endif;


if (!function_exists('mp_product')) :
/*
 * function mp_product
 * Displays a single product according to preference
 *
 * @param bool $echo Optional, whether to echo or return
 * @param int $product_id the ID of the product to display
 * @param bool $title Whether to display the title
 * @param bool/string $content Whether and what type of content to display. Options are false, 'full', or 'excerpt'. Default 'full'
 * @param bool/string $image Whether and what context of image size to display. Options are false, 'single', or 'list'. Default 'single'
 * @param bool $meta Whether to display the product meta
 */

function mp_product($echo = true, $product_id, $title = true, $content = 'full', $image = 'single', $meta = true) {
		global $mp;

		if ( function_exists('icl_object_id') ) {
			$product_id = icl_object_id($product_id, 'product', false);
		}

		$post = get_post($product_id);

		$return = '<div itemscope itemtype="http://schema.org/Product" ' . mp_product_class(false, 'mp_product', $post->ID) . '>';
		$return .= '<span style="display:none" class="date updated">' . get_the_time($product_id) . '</span>';

		if ($title)
				$return .= '<h3 itemprop="name" class="mp_product_name entry-title"><a href="' . get_permalink($post->ID) . '">' . $post->post_title . '</a></h3>';

		if ($content) {
				$return .= '<div itemprop="description" class="mp_product_content">';
				if ($image)
						$return .= mp_product_image(false, $image, $post->ID);
				if ($content == 'excerpt')
						$return .= $mp->product_excerpt($post->post_excerpt, $post->post_content, $post->ID);
				else
						$return .= apply_filters('the_content', $post->post_content);
				$return .= '</div>';
		}

		if ($meta) {
			//price
			$return .= mp_product_price(false, $post->ID);
			//button
			$return .= mp_buy_button(false, 'single', $post->ID);
		}
		$return .= '</div>';

		$return = apply_filters('mp_product', $return, $product_id, $title, $content, $image, $meta);

		if ($echo)
				echo $return;
		else
				return $return;
}
endif;


if (!function_exists('mp_category_list')) :
/**
 * Retrieve product's category list in either HTML list or custom format.
 *
 * @param int $product_id Optional. Post ID to retrieve categories.
 * @param string $before Optional. Before list.
 * @param string $sep Optional. Separate items using this.
 * @param string $after Optional. After list.
 */
function mp_category_list($product_id = false, $before = '', $sep = ', ', $after = '') {
		$terms = get_the_term_list($product_id, 'product_category', $before, $sep, $after);
		if ($terms)
				return $terms;
		else
				$return = __('Uncategorized', 'mp');

		return apply_filters('mp_category_list', $return, $product_id, $before, $sep, $after);
}
endif;


if (!function_exists('mp_tag_list')) :
/**
 * Retrieve product's tag list in either HTML list or custom format.
 *
 * @param int $product_id Optional. Post ID to retrieve categories.
 * @param string $before Optional. Before list.
 * @param string $sep Optional. Separate items using this.
 * @param string $after Optional. After list.
 */
function mp_tag_list($product_id = false, $before = '', $sep = ', ', $after = '') {
		$terms = get_the_term_list($product_id, 'product_tag', $before, $sep, $after);
		if ($terms)
				return $terms;
		else
				$return = __('No Tags', 'mp');

		return apply_filters('mp_tag_list', $return, $product_id, $before, $sep, $after);
}
endif;


if (!function_exists('mp_product_class')) :
/**
 * Display the classes for the product div.
 *
 * @param bool $echo Whether to echo class.
 * @param string|array $class One or more classes to add to the class list.
 * @param int $post_id The post_id for the product. Optional if in the loop
 */
function mp_product_class($echo = true, $class = '', $post_id = null) {
		// Separates classes with a single space, collates classes for post DIV
		$content = 'class="' . join(' ', mp_get_product_class($class, $post_id)) . '"';

		$content = apply_filters('mp_product_class', $content, $class, $post_id);

		if ($echo)
				echo $content;
		else
				return $content;
}
endif;


if (!function_exists('mp_get_product_class')) :
/**
 * Retrieve the list of classes for the product as an array.
 *
 * The class names are add are many. If the post is a sticky, then the 'sticky'
 * class name. The class 'hentry' is always added to each post. For each
 * category, the class will be added with 'category-' with category slug is
 * added. The tags are the same way as the categories with 'tag-' before the tag
 * slug. All classes are passed through the filter, 'post_class' with the list
 * of classes, followed by $class parameter value, with the post ID as the last
 * parameter.
 *
 *
 * @param string|array $class One or more classes to add to the class list.
 * @param int $post_id The post_id for the product. Optional if in the loop
 * @return array Array of classes.
 */
function mp_get_product_class($class = '', $post_id = null) {
		global $id;
		$post_id = ( NULL === $post_id ) ? $id : $post_id;

		$post = get_post($post_id);

		$classes = array();

		if (empty($post))
				return $classes;

		$classes[] = 'product-' . $post->ID;
		$classes[] = $post->post_type;
		$classes[] = 'type-' . $post->post_type;

		// sticky for Sticky Posts
		if (is_sticky($post->ID))
				$classes[] = 'sticky';

		// hentry for hAtom compliace
		$classes[] = 'hentry';

		// Categories
		$categories = get_the_terms($post->ID, "product_category");
		foreach ((array) $categories as $cat) {
				if (empty($cat->slug) || !isset($cat->cat_ID))
						continue;
				$classes[] = 'category-' . sanitize_html_class($cat->slug, $cat->cat_ID);
		}

		// Tags
		$tags = get_the_terms($post->ID, "product_tag");
		foreach ((array) $tags as $tag) {
				if (empty($tag->slug))
						continue;
				$classes[] = 'tag-' . sanitize_html_class($tag->slug, $tag->term_id);
		}

		if (!empty($class)) {
				if (!is_array($class))
						$class = preg_split('#\s+#', $class);
				$classes = array_merge($classes, $class);
		}

		$classes = array_map('esc_attr', $classes);

		return apply_filters('mp_get_product_class', $classes, $class, $post_id);
}
endif;


if (!function_exists('mp_product_price')) :
/*
 * Displays the product price (and sale price)
 *
 * @param bool $echo Optional, whether to echo
 * @param int $post_id The post_id for the product. Optional if in the loop
 * @param sting $label A label to prepend to the price. Defaults to "Price: "
 */

function mp_product_price($echo = true, $post_id = NULL, $label = true) {
		global $id, $mp;
		$post_id = ( NULL === $post_id ) ? $id : $post_id;

		$label = ($label === true) ? __('Price: ', 'mp') : $label;

		$meta = (array) get_post_custom($post_id);
		//unserialize
		foreach ($meta as $key => $val) {
				$meta[$key] = maybe_unserialize($val[0]);
				if (!is_array($meta[$key]) && $key != "mp_is_sale" && $key != "mp_track_inventory" && $key != "mp_product_link" && $key != "mp_file" && $key != "mp_price_sort")
						$meta[$key] = array($meta[$key]);
		}

		if ((is_array($meta["mp_price"]) && count($meta["mp_price"]) == 1) || !empty($meta["mp_file"])) {
				if ($meta["mp_is_sale"]) {
						$price = '<span class="mp_special_price"><del class="mp_old_price">' . $mp->format_currency('', $meta["mp_price"][0]) . '</del>';
						$price .= '<span itemprop="price" class="mp_current_price">' . $mp->format_currency('', $meta["mp_sale_price"][0]) . '</span></span>';
				} else {
						$price = '<span itemprop="price" class="mp_normal_price"><span class="mp_current_price">' . $mp->format_currency('', $meta["mp_price"][0]) . '</span></span>';
				}
		} else if (is_array($meta["mp_price"]) && count($meta["mp_price"])) { //only show from price in lists
				if ($meta["mp_is_sale"]) {
						//do some crazy stuff here to get the lowest price pair ordered by sale prices
						asort($meta["mp_sale_price"], SORT_NUMERIC);
						$lowest = array_slice($meta["mp_sale_price"], 0, 1, true);
						$keys = array_keys($lowest);
						$mp_price = $meta["mp_price"][$keys[0]];
						$mp_sale_price = array_pop($lowest);
						$price = __('from', 'mp') . ' <span class="mp_special_price"><del class="mp_old_price">' . $mp->format_currency('', $mp_price) . '</del>';
						$price .= '<span itemprop="price" class="mp_current_price">' . $mp->format_currency('', $mp_sale_price) . '</span></span>';
				} else {
						sort($meta["mp_price"], SORT_NUMERIC);
						$price = __('from', 'mp') . ' <span itemprop="price" class="mp_normal_price"><span class="mp_current_price">' . $mp->format_currency('', $meta["mp_price"][0]) . '</span></span>';
				}
		} else {
				return '';
		}

		$price_html = _mp_apply_deprecated_filters('mp_product_price_tag', array('<span itemprop="offers" itemscope itemtype="http://schema.org/Offer" class="mp_product_price">' . $label . $price . '</span>', $post_id, $label), '2.9.3.7', 'mp_product_price_html');
		$price_html = apply_filters('mp_product_price_html', $price_html, $post_id, $label, $price);

		if ( $echo )
			echo $price_html;
		else
			return $price_html;
}
endif;

if ( ! function_exists('_mp_apply_deprecated_filters') ) :
/**
 * Fire a deprecated filter. Wraps apply_filters().
 *
 * @since 2.9.3.7
 *
 * @param string $tag The name of the filter hook.
 * @param array $args Array of additional function arguments to be passed to apply_filters().
 * @param string $version The version of WordPress that deprecated the hook
 * @param string $replacement Optional. The hook that should have been used
 * @param string $message Optional. A message regarding the change
 */
function _mp_apply_deprecated_filters( $tag, $args, $version, $replacement = false, $message = null ) {
	if ( ! has_filter( $tag ) )
		return array_shift($args);
	_mp_deprecated_hook( $tag, $version, $replacement, $message );
	array_unshift( $args, $tag );
	return call_user_func_array( 'apply_filters', $args );
}
endif;

if ( ! function_exists('_mp_do_deprecated_action') ) :
/**
 * Fire a deprecated action. Wraps do_action().
 *
 * @since 2.9.3.7
 *
 * @param string $tag The name of the filter hook.
 * @param array $args Array of additional function arguments to be passed to do_action().
 * @param string $version The version of WordPress that deprecated the hook
 * @param string $replacement Optional. The hook that should have been used
 * @param string $message Optional. A message regarding the change
 */
function _mp_do_deprecated_action( $tag, $args, $version, $replacement = false, $message = null ) {
	if ( ! has_action( $tag ) )
		return;
	_mp_deprecated_hook( $tag, $version, $replacement, $message );
	array_unshift( $args, $tag );
	call_user_func_array( 'do_action', $args );
}
endif;

if ( ! function_exists('_mp_deprecated_hook') ) :
/**
 * Marks a hook as deprecated and informs when it has been used.
 *
 * There is a hook deprecated_hook_used that will be called that can be used
 * to get the backtrace up to what file and function was used for the callback.
 *
 * The current behavior is to trigger a user error if WP_DEBUG is true.
 *
 * This function is to be used for every hook that is deprecated, when any callback is
 * attacked to the hook, as determined by has_action() or has_filter(), and shall be
 * called before the hook is fired.
 *
 * @since 2.9.3.6
 *
 * @uses do_action() Calls 'deprecated_hook_used' and passes the hook name, what to use instead,
 *	 the version in which the file was deprecated, and any message regarding the change.
 * @uses apply_filters() Calls 'deprecated_hook_trigger_error' and expects boolean value of true to do
 *	 trigger or false to not trigger error.
 *
 * @param string $hook The hook that was used
 * @param string $version The version of WordPress that deprecated the hook
 * @param string $replacement Optional. The hook that should have been used
 * @param string $message Optional. A message regarding the change
 */
function _mp_deprecated_hook( $hook, $version, $replacement = null, $message = null ) {
	// Allow plugin to filter the output error trigger
	if ( WP_DEBUG && apply_filters( 'deprecated_hook_trigger_error', true ) ) {
		$message = empty( $message ) ? '' : ' ' . $message;
		if ( ! is_null( $replacement ) )
			trigger_error( sprintf( __('%1$s is <strong>deprecated</strong> since version %2$s! Use %3$s instead.'), $hook, $version, $replacement ) . $message );
		else
			trigger_error( sprintf( __('%1$s is <strong>deprecated</strong> since version %2$s with no alternative available.'), $hook, $version ) . $message );
	}
}

endif;

if (!function_exists('mp_buy_button')) :
/*
 * Displays the buy or add to cart button
 *
 * @param bool $echo Optional, whether to echo
 * @param string $context Options are list or single
 * @param int $post_id The post_id for the product. Optional if in the loop
 */

function mp_buy_button($echo = true, $context = 'list', $post_id = NULL) {
		global $id, $mp;
		$post_id = ( NULL === $post_id ) ? $id : $post_id;

		$meta = (array) get_post_custom($post_id);
		//unserialize
		foreach ($meta as $key => $val) {
				$meta[$key] = maybe_unserialize($val[0]);
				if (!is_array($meta[$key]) && $key != "mp_is_sale" && $key != "mp_track_inventory" && $key != "mp_product_link" && $key != "mp_file")
						$meta[$key] = array($meta[$key]);
		}

		//check stock
		$no_inventory = array();
		$all_out = false;
		if ($meta['mp_track_inventory']) {
				$cart = $mp->get_cart_contents();
				if (isset($cart[$post_id]) && is_array($cart[$post_id])) {
						foreach ($cart[$post_id] as $variation => $data) {
								if ($meta['mp_inventory'][$variation] <= $data['quantity'])
										$no_inventory[] = $variation;
						}
						foreach ($meta['mp_inventory'] as $key => $stock) {
								if (!in_array($key, $no_inventory) && $stock <= 0)
										$no_inventory[] = $key;
						}
				}

				//find out of stock items that aren't in the cart
				foreach ($meta['mp_inventory'] as $key => $stock) {
						if (!in_array($key, $no_inventory) && $stock <= 0)
								$no_inventory[] = $key;
				}

				if (count($no_inventory) >= count($meta["mp_price"]))
						$all_out = true;
		}

		//display an external link or form button
		if (isset($meta['mp_product_link']) && $product_link = $meta['mp_product_link']) {

				$button = '<a class="mp_link_buynow" href="' . esc_url($product_link) . '">' . __('Buy Now &raquo;', 'mp') . '</a>';
		} else if ($mp->get_setting('disable_cart')) {

				$button = '';
		} else {
				$variation_select = '';
				$button = '<form class="mp_buy_form" method="post" action="' . mp_cart_link(false, true) . '">';

				if ($all_out) {
						$button .= '<span class="mp_no_stock">' . __('Out of Stock', 'mp') . '</span>';
				} else {

						$button .= '<input type="hidden" name="product_id" value="' . $post_id . '" />';

						//create select list if more than one variation
						if (is_array($meta["mp_price"]) && count($meta["mp_price"]) > 1 && empty($meta["mp_file"])) {
								$variation_select = '<select class="mp_product_variations" name="variation">';
								foreach ($meta["mp_price"] as $key => $value) {
										$disabled = (in_array($key, $no_inventory)) ? ' disabled="disabled"' : '';
										$variation_select .= '<option value="' . $key . '"' . $disabled . '>' . esc_html($meta["mp_var_name"][$key]) . ' - ';
										if ($meta["mp_is_sale"] && $meta["mp_sale_price"][$key]) {
												$variation_select .= $mp->format_currency('', $meta["mp_sale_price"][$key]);
										} else {
												$variation_select .= $mp->format_currency('', $value);
										}
										$variation_select .= "</option>\n";
								}
								$variation_select .= "</select>&nbsp;\n";
						} else {
								$button .= '<input type="hidden" name="variation" value="0" />';
						}

						if ($context == 'list') {
								if ($variation_select) {
										$button .= '<a class="mp_link_buynow" href="' . get_permalink($post_id) . '">' . __('Choose Option &raquo;', 'mp') . '</a>';
								} else if ($mp->get_setting('list_button_type') == 'addcart') {
										$button .= '<input type="hidden" name="action" value="mp-update-cart" />';
										$button .= '<input class="mp_button_addcart" type="submit" name="addcart" value="' . __('Add To Cart &raquo;', 'mp') . '" />';
								} else if ($mp->get_setting('list_button_type') == 'buynow') {
										$button .= '<input class="mp_button_buynow" type="submit" name="buynow" value="' . __('Buy Now &raquo;', 'mp') . '" />';
								}
						} else {

								$button .= $variation_select;

								//add quantity field if not downloadable
								if ($mp->get_setting('show_quantity') && empty($meta["mp_file"])) {
										$button .= '<span class="mp_quantity"><label>' . __('Quantity:', 'mp') . ' <input class="mp_quantity_field" type="text" size="1" name="quantity" value="1" /></label></span>&nbsp;';
								}

								if ($mp->get_setting('product_button_type') == 'addcart') {
										$button .= '<input type="hidden" name="action" value="mp-update-cart" />';
										$button .= '<input class="mp_button_addcart" type="submit" name="addcart" value="' . __('Add To Cart &raquo;', 'mp') . '" />';
								} else if ($mp->get_setting('product_button_type') == 'buynow') {
										$button .= '<input class="mp_button_buynow" type="submit" name="buynow" value="' . __('Buy Now &raquo;', 'mp') . '" />';
								}
						}
				}

				$button .= '</form>';
		}

		$button = apply_filters('mp_buy_button_tag', $button, $post_id, $context);

		if ($echo)
				echo $button;
		else
				return $button;
}
endif;


if (!function_exists('mp_product_sku')) :
/*
 * function mp_product_sku
 *
 * @param bool $echo default true
 * @param int $post_id The post_id of the product. Optional if in the loop
 * @param string $seperator The seperator to put between skus, default ', '
 *
 * Returns or echos html of variation SKUs
 */

function mp_product_sku($echo = true, $post_id = NULL, $seperator = ', ') {
		global $id, $mp;
		$post_id = ( NULL === $post_id ) ? $id : $post_id;

		$list = get_post_meta($post_id, "mp_sku", true);
		$html = '<span class="mp_product_skus">' . implode($seperator, $list) . '</span>';

		$html = apply_filters('mp_product_skus', $html, $post_id, $list, $seperator);

		if ($echo)
				echo $html;
		else
				return $html;
}
endif;


if (!function_exists('mp_product_image')) :
/*
 * Displays the product featured image
 *
 * @param bool $echo Optional, whether to echo
 * @param string $context Options are list, single, or widget
 * @param int $post_id The post_id for the product. Optional if in the loop
 * @param int $size An optional width/height for the image if contect is widget
 * @param string $align The alignment of the image. Defaults to settings.
 */

function mp_product_image($echo = true, $context = 'list', $post_id = NULL, $size = NULL, $align = NULL) {
		global $id, $mp;
		$post_id = ( NULL === $post_id ) ? $id : $post_id;
		// Added WPML
		$post_id = apply_filters('mp_product_image_id', $post_id);

		$post = get_post($post_id);

		$post_thumbnail_id = get_post_thumbnail_id($post_id);
		$class = $title = $link = '';
		$img_classes = array('mp_product_image_' . $context, 'photo');

		if ( !is_null($align) )
			$align = 'align' . $align;

		if ($context == 'list') {
				//quit if no thumbnails on listings
				if (!$mp->get_setting('show_thumbnail'))
						return '';

				//size
				if (intval($size)) {
						$size = array(intval($size), intval($size));
				} else {
						if ($mp->get_setting('list_img_size') == 'custom')
								$size = array($mp->get_setting('list_img_width'), $mp->get_setting('list_img_height'));
						else
								$size = $mp->get_setting('list_img_size');
				}

				//link
				$link = get_permalink($post_id);

				$title = esc_attr($post->post_title);
				$class = ' class="mp_img_link"';
				$img_classes[] = is_null($align) ? $mp->get_setting('image_alignment_list') : $align;
		} else if ($context == 'single') {
				//size
				if ($mp->get_setting('product_img_size') == 'custom')
						$size = array($mp->get_setting('product_img_width'), $mp->get_setting('product_img_height'));
				else
						$size = $mp->get_setting('product_img_size');

				//link
				$temp = wp_get_attachment_image_src($post_thumbnail_id, 'large');
				$link = $temp[0];

				if ($mp->get_setting('disable_large_image')) {
						$link = '';
						$title = esc_attr($post->post_title);
				} else {
						$title = __('View Larger Image &raquo;', 'mp');
				}

				$class = ' class="mp_product_image_link mp_lightbox"';
				$img_classes[] = is_null($align) ? $mp->get_setting('image_alignment_single') : $align;

				//in case another plugin is loading lightbox
				if ($mp->get_setting('show_lightbox')) {
					$class .= ' rel="lightbox"';
					wp_enqueue_script('mp-lightbox');
				}
		} else if ($context == 'widget') {
				//size
				if (intval($size))
						$size = array(intval($size), intval($size));
				else
						$size = array(50, 50);

				//link
				$link = get_permalink($post_id);

				$title = esc_attr($post->post_title);
				$class = ' class="mp_img_link"';
		}

		$image = get_the_post_thumbnail($post_id, $size, array('itemprop' => 'image', 'class' => implode(' ', $img_classes), 'title' => $title));

		if (empty($image) && $context != 'single') {
				if (!is_array($size)) {
						$size = array(get_option($size . "_size_w"), get_option($size . "_size_h"));
				}
				$img_classes[] = 'wp-post-image';
				$image = '
					<div itemscope class="hmedia">
						<div style="display:none"><span class="fn">' . get_the_title(get_post_thumbnail_id()) . '</span></div>
						<img width="' . $size[0] . '" height="' . $size[1] . '" itemprop="image" title="' . esc_attr($title) . '" class="' . implode(' ', $img_classes) . '" src="' . apply_filters('mp_default_product_img', $mp->plugin_url . 'images/default-product.png') . '" />
					</div>';
		}

		//force ssl on images (if applicable) http://wp.mu/8s7
		if ( is_ssl() ) {
			$image = str_replace('http://', 'https://', $image);
		}

		//add the link
		if ($link) {
			$image = '
				<div itemscope class="hmedia">
					<div style="display:none"><span class="fn">' . get_the_title(get_post_thumbnail_id()) . '</span></div>
					<a rel="lightbox enclosure" id="product_image-' . $post_id . '"' . $class . ' href="' . $link . '">' . $image . '</a>
				</div>';
		}

		$image = apply_filters('mp_product_image', $image, $context, $post_id, $size);

		if ($echo)
			echo $image;
		else
			return $image;
}
endif;


if (!function_exists('mp_products_filter')) :
/**
 * Displays the product list filter dropdowns
 *
 * @return string		html for filter/order products select elements.
 */
function mp_products_filter( $hidden = false, $per_page = null, $category = null, $order_by = null, $order = null ) {
		global $wp_query, $mp;

		if ( ! is_null($category) ) {
			if ( is_numeric($category) ) {
				$default = $category;
			} else {
				$term = get_term_by('slug', $category, 'product_category');
				$default = $term->term_id;
			}
		} elseif ( 'product_category' == get_query_var('taxonomy') ) {
				$term = get_queried_object(); //must do this for number tags
				$default = $term->term_id;
		} else {
				$default = '-1';
		}

		$terms = wp_dropdown_categories(array(
				'name' => 'product_category',
				'id' => 'product-category',
				'taxonomy' => 'product_category',
				'show_option_none' => __('Show All', 'mp'),
				'show_count' => 1,
				'orderby' => 'name',
				'selected' => $default,
				'echo' => 0,
				'hierarchical' => true
		));
		
		if ( is_null($order_by) ) {
			$order_by = $mp->get_setting('order_by');
		}
		
		if ( is_null($order) ) {
			$order = $mp->get_setting('order'); 
		}
		
		$current_order = strtolower($order_by . '-' . $order);
		$options = array(
				array('0', '', __('Default', 'mp')),
				array('date', 'desc', __('Release Date', 'mp')),
				array('title', 'asc', __('Name', 'mp')),
				array('price', 'asc', __('Price (Low to High)', 'mp')),
				array('price', 'desc', __('Price (High to Low)', 'mp')),
				array('sales', 'desc', __('Popularity', 'mp'))
		);
		$options_html = '';
		foreach ($options as $k => $t) {
				$value = $t[0] . '-' . $t[1];
				$selected = $current_order == $value ? 'selected' : '';

				$options_html.='<option value="' . $value . '" ' . $selected . '>
							' . $t[2] . '
						</option>';
		}

		$return = '
			<a name="mp-product-list-top"></a>
			<div class="mp_list_filter"' . (( $hidden ) ? ' style="display:none"' : '') . '>
				<form name="mp_product_list_refine" class="mp_product_list_refine" method="get">
						<div class="one_filter">
							<span>' . __('Category', 'mp') . '</span>
							' . $terms . '
						</div>

						<div class="one_filter">
							<span>' . __('Order By', 'mp') . '</span>
							<select name="order">
								' . $options_html . '
							</select>
						</div>' .

						(( is_null($per_page) ) ? '' : '<input type="hidden" name="per_page" value="' . $per_page . '" />') . '
				</form>
			</div>';

		return apply_filters('mp_products_filter', $return);
}
endif;


if (!function_exists('mp_cart_link')) :
/**
 * Echos the current shopping cart link. If global cart is on reflects global location
 * @param bool $echo Optional, whether to echo. Defaults to true
 * @param bool $url Optional, whether to return a link or url. Defaults to show link.
 * @param string $link_text Optional, text to show in link.
 */
function mp_cart_link($echo = true, $url = false, $link_text = '') {
		global $mp, $mp_wpmu;

		if ($mp->global_cart && is_object($mp_wpmu) && !$mp_wpmu->is_main_site() && function_exists('mp_main_site_id')) {
				switch_to_blog(mp_main_site_id());
				$link = home_url($mp->get_setting('slugs->store') . '/' . $mp->get_setting('slugs->cart') . '/');
				restore_current_blog();
		} else {
				$link = home_url($mp->get_setting('slugs->store') . '/' . $mp->get_setting('slugs->cart') . '/');
		}

		if (!$url) {
				$text = ($link_text) ? $link_text : __('Shopping Cart', 'mp');
				$link = '<a href="' . $link . '" class="mp_cart_link">' . $text . '</a>';
		}

		$link = apply_filters('mp_cart_link', $link, $echo, $url, $link_text);

		if ($echo)
				echo $link;
		else
				return $link;
}
endif;


if (!function_exists('mp_store_link')) :
/**
 * Echos the current store link.
 * @param bool $echo Optional, whether to echo. Defaults to true
 * @param bool $url Optional, whether to return a link or url. Defaults to show link.
 * @param string $link_text Optional, text to show in link.
 */
function mp_store_link($echo = true, $url = false, $link_text = '') {
		global $mp;
		$link = home_url(trailingslashit($mp->get_setting('slugs->store')));

		if (!$url) {
				$text = ($link_text) ? $link_text : __('Visit Store', 'mp');
				$link = '<a href="' . $link . '" class="mp_store_link">' . $text . '</a>';
		}

		$link = apply_filters('mp_store_link', $link, $echo, $url, $link_text);

		if ($echo)
				echo $link;
		else
				return $link;
}
endif;


if (!function_exists('mp_product_link')) :
/**
 * Echos the current product list link.
 * @param bool $echo Optional, whether to echo. Defaults to true
 * @param bool $url Optional, whether to return a link or url. Defaults to show link.
 * @param string $link_text Optional, text to show in link.
 */
function mp_products_link($echo = true, $url = false, $link_text = '') {
		global $mp;
		$link = home_url($mp->get_setting('slugs->store') . '/' . $mp->get_setting('slugs->products') . '/');

		if (!$url) {
				$text = ($link_text) ? $link_text : __('View Products', 'mp');
				$link = '<a href="' . $link . '" class="mp_products_link">' . $text . '</a>';
		}

		$link = apply_filters('mp_products_link', $link, $echo, $url, $link_text);

		if ($echo)
				echo $link;
		else
				return $link;
}
endif;

if ( !function_exists('mp_products_nav') ) :
/**
 * Echos the current product list/grid navigation
 * @param bool $echo Optional, whether to echo. Defaults to true
 * @param WP_Query object $custom_query
 */
function mp_products_nav($echo = true, $custom_query) {
	$page = max(1, $custom_query->get('paged'));
	$max = $custom_query->max_num_pages;
	$prev=$next=$html='';

	if ($max > 1) {
		if ( $page != $max ) {
			$next='<a href="#page='.($page+1).'">'.__('Next Page &raquo;').'</a>';
		}
		if ($page != 1) {
			$prev='<a href="#page='.($page-1).'">'.__('&laquo; Previous Page').'</a>';
		}
		$html = '<div id="mp_product_nav">' . $prev . (strlen($prev)>0 && strlen($next)>0?' &#8212; ':'') . $next . '</div>';
	}

	$html = apply_filters('mp_products_nav', $html, $custom_query);

	if ($echo)
		echo $html;
	else
		return $html;
}
endif;

if (!function_exists('mp_orderstatus_link')) :
/**
 * Echos the current order status link.
 * @param bool $echo Optional, whether to echo. Defaults to true
 * @param bool $url Optional, whether to return a link or url. Defaults to show link.
 * @param string $link_text Optional, text to show in link.
 * @param string $order Optional, the order id to append to the link
 */
function mp_orderstatus_link( $echo = true, $url = false, $link_text = '', $order_id = '' ) {
		global $mp;
		$link = home_url($mp->get_setting('slugs->store') . '/' . $mp->get_setting('slugs->orderstatus') . '/' . $order_id);

		if (!$url) {
				$text = ($link_text) ? $link_text : __('Check Order Status', 'mp');
				$link = '<a href="' . $link . '" class="mp_orderstatus_link">' . $text . '</a>';
		}

		$link = apply_filters('mp_orderstatus_link', $link, $echo, $url, $link_text);

		if ($echo)
				echo $link;
		else
				return $link;
}
endif;


if (!function_exists('mp_checkout_step_url')) :
/**
 * Returns the current shopping cart link with checkout step.
 *
 * @param string $checkoutstep. Possible values: checkout-edit, shipping, checkout, confirm-checkout, confirmation
 */
function mp_checkout_step_url($checkout_step) {
		return apply_filters('mp_checkout_step_url', mp_cart_link(false, true) . trailingslashit($checkout_step), $checkout_step);
}
endif;


if (!function_exists('mp_cart_breadcrumbs')) :
/**
 * @return string HTML that shows the user their current position in the purchase process.
 */
function mp_cart_breadcrumbs($current_step) {
		$steps = array(
				'checkout-edit' => __('Review Cart', 'mp'),
				'shipping' => __('Shipping', 'mp'),
				'checkout' => __('Checkout', 'mp'),
				'confirm-checkout' => __('Confirm', 'mp'),
				'confirmation' => __('Order Complete', 'mp')
		);

		$order = array_keys($steps);
		$current = array_search($current_step, $order);
		$all = array();

		foreach ($steps as $str => $human) {
				$i = array_search($str, $order);

				if ($i >= $current) {
						// incomplete
						$all[] = '<span class="incomplete ' . ($i == $current ? 'current' : '') . '">' . $human . '</span>';
				} else {
						// done
						$all[] = '<a class="done" href="' . mp_checkout_step_url($str) . '">' . $human . '</a>';
				}
		}

		$return = '<div class="mp_cart_breadcrumbs">
				' . implode(
										'<span class="sep">' . apply_filters('mp_cart_breadcrumbs_seperator', '&raquo;') . '</span>', $all) . '
			</div>';

	return apply_filters('mp_cart_breadcrumbs', $return, $current_step);
}
endif;


if (!function_exists('mp_store_navigation')) :
/**
 * Echos the current store navigation links.
 *
 * @param bool $echo Optional, whether to echo. Defaults to true
 */
function mp_store_navigation($echo = true) {
		global $mp;

		//navigation
		if (!$mp->get_setting('disable_cart')) {
				$nav = '<ul class="mp_store_navigation"><li class="page_item"><a href="' . mp_products_link(false, true) . '" title="' . __('Products', 'mp') . '">' . __('Products', 'mp') . '</a></li>';
				$nav .= '<li class="page_item"><a href="' . mp_cart_link(false, true) . '" title="' . __('Shopping Cart', 'mp') . '">' . __('Shopping Cart', 'mp') . '</a></li>';
				$nav .= '<li class="page_item"><a href="' . mp_orderstatus_link(false, true) . '" title="' . __('Order Status', 'mp') . '">' . __('Order Status', 'mp') . '</a></li>
</ul>';
		} else {
				$nav = '<ul class="mp_store_navigation">
<li class="page_item"><a href="' . mp_products_link(false, true) . '" title="' . __('Products', 'mp') . '">' . __('Products', 'mp') . '</a></li>
</ul>';
		}

		$nav = apply_filters('mp_store_navigation', $nav);

		if ($echo)
				echo $nav;
		else
				return $nav;
}
endif;


if (!function_exists('mp_is_shop_page')) :
/**
 * Determine if on a MarketPress shop page
 *
 * @retuns bool whether current page is a MarketPress store page.
 */
function mp_is_shop_page() {
		global $mp;
		return $mp->is_shop_page;
}
endif;


if (!function_exists('mp_items_in_cart')) :
/**
 * Determine if there are any items in the cart
 *
 * @retuns bool whether items are in the cart for the current user.
 */
function mp_items_in_cart() {
		if (mp_items_count_in_cart())
				return true;
		else
				return false;
}
endif;


if (!function_exists('mp_items_count_in_cart')) :
/**
 * Determine count of any items in the cart
 *
 * @retuns int number of items that are in the cart for the current user.
 */
function mp_items_count_in_cart() {
		global $mp, $blog_id;
		$blog_id = (is_multisite()) ? $blog_id : 1;

		$global_cart = $mp->get_cart_contents(true);
		if (!$mp->global_cart)
				$selected_cart[$blog_id] = $global_cart[$blog_id];
		else
				$selected_cart = $global_cart;

		if (is_array($selected_cart) && count($selected_cart)) {
				$count = 0;
				foreach ($selected_cart as $cart) {
						if (is_array($cart) && count($cart)) {
								foreach ($cart as $variations) {
										if (is_array($variations) && count($variations)) {
												foreach ($variations as $item) {
														$count += $item['quantity'];
												}
										}
								}
						}
				}
				return $count;
		} else {
				return 0;
		}
}
endif;


if (!function_exists('mp_products_count')) :
/**
 * Determine the number of published products
 *
 * @retuns int number of published products.
 */
function mp_products_count() {
		$custom_query = new WP_Query('post_type=product&post_status=publish');
		return $custom_query->post_count;
}
endif;


/**
 * This function hook into the shipping filter to add any product custom fields. Checks the cart items
 * If any cart items have associated custom fields then they will be displayed in a new section 'Product extra fields'
 * shown below the shipping form inputs. The custom fields will be one for each quantity. Via the product admin each
 * custom field can be made required or optional. Standard error handling is provided per Market Press standard processing.
 *
 * @since 2.6.0
 * @see
 *
 * @param $content - output content passed from caller (_mp_cart_shipping)
 * @return $content - Revised content with added information
 */
function mp_custom_fields_checkout_after_shipping($content = '') {
		global $mp, $blog_id, $current_user;

		if (isset($_SESSION['mp_shipping_info']['mp_custom_fields'])) {
				$mp_custom_fields = $_SESSION['mp_shipping_info']['mp_custom_fields'];
		} else {
				$mp_custom_fields = array();
		}

		$blog_id = (is_multisite()) ? $blog_id : 1;

		$current_blog_id = $blog_id;

		$global_cart = $mp->get_cart_contents(true);
		if (!$mp->global_cart)	//get subset if needed
				$selected_cart[$blog_id] = $global_cart[$blog_id];
		else
				$selected_cart = $global_cart;

		$content_product = '';

		foreach ($selected_cart as $bid => $cart) {

				if (is_multisite())
						switch_to_blog($bid);

				foreach ($cart as $product_id => $variations) {

						// Load the meta info for the custom fields for this product
						$mp_has_custom_field = get_post_meta($product_id, 'mp_has_custom_field', true);
						$mp_custom_field_required = get_post_meta($product_id, 'mp_custom_field_required', true);
						$mp_custom_field_per = get_post_meta($product_id, 'mp_custom_field_per', true);
						$mp_custom_field_label = get_post_meta($product_id, 'mp_custom_field_label', true);

						foreach ($variations as $variation => $data) {

								if (isset($mp_has_custom_field[$variation]) && $mp_has_custom_field[$variation]) {

										if (!empty($mp_custom_field_label[$variation]))
												$label_text = esc_attr($mp_custom_field_label[$variation]);
										else
												$label_text = "";

										if (isset($mp_custom_field_required[$variation]) && $mp_custom_field_required[$variation])
												$required_text = __('required', 'mp');
										else
												$required_text = __('optional', 'mp');

										$content_product .= '<tr class="mp_product_name"><td align="right" colspan="2">';
										$content_product .= apply_filters('mp_checkout_error_custom_fields_' . $product_id . '_' . $variation, '');
										$content_product .= $data['name'];
										$content_product .= '</td></tr>';
										$content_product .= '<tr class="mp_product_custom_fields" style="border-width: 0px">';
										$content_product .= '<td style="border-width: 0px">';
										$content_product .= $label_text . ' (' . $required_text . ')<br />';
										//$content_product .=	 '</td></tr>';
										//$content_product .= '<tr><td style="border-width: 0px">';
										// If the mp_custom_field_per is set to 'line' we only show one input field per item in the cart.
										// This input field will be a simply unordered list (<ul>). However, if the mp_custom_field_per
										// Then we need to show an input field per the quantity items. In this case we use an ordered list
										// to show the numbers to the user. 0-based.
										if ($mp_custom_field_per[$variation] == "line") {
												//$content_product .= '<ul>';
												$cf_limit = 1;
										} else if ($mp_custom_field_per[$variation] == "quantity") {
												//$content_product .= '<ol>';
												$cf_limit = $data['quantity'];
										}

										$output_cnt = 0;
										while ($output_cnt < $cf_limit) {

												$cf_key = $bid . ':' . $product_id . ':' . $variation;
												if (isset($mp_custom_fields[$cf_key][$output_cnt]))
														$output_value = $mp_custom_fields[$cf_key][$output_cnt];
												else
														$output_value = '';

												$content_product .= '<input type="text" style="width: 90%;" value="' . $output_value . '" name="mp_custom_fields[' . $bid . ':' . $product_id . ':' . $variation . '][' . $output_cnt . ']" />';
												$output_cnt += 1;
										}
										/*
											if ($mp_custom_field_per[$variation] == "line")
											$content_product .= '<ul>';
											else if ($mp_custom_field_per[$variation] == "quantity")
											$content_product .= '<ol>';
										 */
										$content_product .= '</td>';
										$content_product .= '</tr>';
								}
						}
				}

				//go back to original blog
				if (is_multisite())
						switch_to_blog($current_blog_id);
		}

		if (strlen($content_product)) {

				$content .= '<table class="mp_product_shipping_custom_fields">';
				$content .= '<thead><tr><th colspan="2">' . __('Product Personalization:', 'mp') . '</th></tr></thead>';
				$content .= '<tbody>';
				$content .= $content_product;
				$content .= '</tbody>';
				$content .= '</table>';
		}

		$content = apply_filters('mp_custom_fields_checkout_after_shipping', $content);

		return $content;
}

add_filter('mp_checkout_after_shipping', 'mp_custom_fields_checkout_after_shipping');

/* Not used. This code will show the custom fields input at the view cart page instead of shipping */

function mp_custom_fields_single_order_display_box($order) {
		global $blog_id;

		// If this order doesn't have custom fields then return...
		if (!isset($order->mp_shipping_info['mp_custom_fields']))
				return;

		// IF no order items. Not sure this can happend but just in case.
		if (!isset($order->mp_cart_info))
				return;

		//echo "order<pre>"; print_r($order); echo "</pre>";

		$content_product = '';

		$bid = (is_multisite()) ? $blog_id : 1;
		foreach ($order->mp_cart_info as $product_id => $variations) {
				foreach ($variations as $variation => $data) {
						$content_product .= '<h3>' . $data['name'] . '</h3>';
						$cf_key = $bid . ':' . $product_id . ':' . $variation;
						if (isset($order->mp_shipping_info['mp_custom_fields'][$cf_key])) {
								$cf_items = $order->mp_shipping_info['mp_custom_fields'][$cf_key];
								$content_product .= '<ol>';
								foreach ($cf_items as $cf_item) {
										$content_product .= '<li>' . $cf_item . '</li>';
								}
								$content_product .= '</ol>';
						}
				}
		}
		if (strlen($content_product)) {
				?>
				<div id="mp-order-custom-fields-info" class="postbox">
						<h3 class='hndle'><span><?php _e('Product Extra Fields', 'mp'); ?></span></h3>
						<div class="inside">
								<?php echo $content_product; ?>
						</div>
				</div>
				<script type="text/javascript">
						jQuery('table#mp-order-product-table').after('<p><a href="#mp-order-custom-fields-info">View Product Extra Fields</a></p>');
				</script>
				<?php
		}
}

//add_action('mp_single_order_display_box', 'mp_custom_fields_single_order_display_box');