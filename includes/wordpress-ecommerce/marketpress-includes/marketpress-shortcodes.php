<?php
/*
MarketPress Shortcode Support
*/

class MarketPress_Shortcodes {

  function __construct() {

    //register our shortcodes
    add_shortcode( 'mp_tag_cloud', array(&$this, 'mp_tag_cloud_sc') );
    add_shortcode( 'mp_list_categories', array(&$this, 'mp_list_categories_sc') );
    add_shortcode( 'mp_dropdown_categories', array(&$this, 'mp_dropdown_categories_sc') );
    add_shortcode( 'mp_popular_products', array(&$this, 'mp_popular_products_sc') );
		add_shortcode( 'mp_related_products', array(&$this, 'mp_related_products_sc') );
    add_shortcode( 'mp_list_products', array(&$this, 'mp_list_products_sc') );
    add_shortcode( 'mp_product', array(&$this, 'mp_product_sc') );
    add_shortcode( 'mp_product_image', array(&$this, 'mp_product_image_sc') );
    add_shortcode( 'mp_buy_button', array(&$this, 'mp_buy_button_sc') );
    add_shortcode( 'mp_product_price', array(&$this, 'mp_product_price_sc') );
    add_shortcode( 'mp_product_meta', array(&$this, 'mp_product_meta_sc') );
    add_shortcode( 'mp_product_sku', array(&$this, 'mp_product_sku_sc') );

    //store links
    add_shortcode( 'mp_cart_link', array(&$this, 'mp_cart_link_sc') );
    add_shortcode( 'mp_store_link', array(&$this, 'mp_store_link_sc') );
    add_shortcode( 'mp_products_link', array(&$this, 'mp_products_link_sc') );
    add_shortcode( 'mp_orderstatus_link', array(&$this, 'mp_orderstatus_link_sc') );
    add_shortcode( 'mp_store_navigation', array(&$this, 'mp_store_navigation_sc') );

	}


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
   * of the posts  with that tag, returns a text for the tooltip of the tag link.
   *
   * The 'exclude' and 'include' arguments are used for the {@link get_tags()}
   * function. Only one should be used, because only one will be used and the
   * other ignored, if they are both set.
   *
   */
  function mp_tag_cloud_sc($atts) {
    return mp_tag_cloud(false, $atts);
  }

  /**
   * Display or retrieve the HTML list of product categories.
   *
   * The list of arguments is below:
   *     "show_option_all" (string) - Text to display for showing all categories.
   *     "orderby" (string) default is "ID" - What column to use for ordering the
   * categories.
   *     "order" (string) default is "ASC" - What direction to order categories.
   *     "show_last_update" (bool|int) default is 0 - See {@link
   * walk_category_dropdown_tree()}
   *     "show_count" (bool|int) default is 0 - Whether to show how many posts are
   * in the category.
   *     "hide_empty" (bool|int) default is 1 - Whether to hide categories that
   * don"t have any posts attached to them.
   *     "use_desc_for_title" (bool|int) default is 1 - Whether to use the
   * description instead of the category title.
   *     "feed" - See {@link get_categories()}.
   *     "feed_type" - See {@link get_categories()}.
   *     "feed_image" - See {@link get_categories()}.
   *     "child_of" (int) default is 0 - See {@link get_categories()}.
   *     "exclude" (string) - See {@link get_categories()}.
   *     "exclude_tree" (string) - See {@link get_categories()}.
   *     "current_category" (int) - See {@link get_categories()}.
   *     "hierarchical" (bool) - See {@link get_categories()}.
   *     "title_li" (string) - See {@link get_categories()}.
   *     "depth" (int) - The max depth.
   *
   */
  function mp_list_categories_sc($atts) {
    return mp_list_categories(false, $atts);
  }

  /**
   * Display or retrieve the HTML dropdown list of product categories.
   *
   * The list of arguments is below:
   *     "show_option_all" (string) - Text to display for showing all categories.
   *     "show_option_none" (string) - Text to display for showing no categories.
   *     "orderby" (string) default is "ID" - What column to use for ordering the
   * categories.
   *     "order" (string) default is "ASC" - What direction to order categories.
   *     "show_last_update" (bool|int) default is 0 - See {@link get_categories()}
   *     "show_count" (bool|int) default is 0 - Whether to show how many posts are
   * in the category.
   *     "hide_empty" (bool|int) default is 1 - Whether to hide categories that
   * don"t have any posts attached to them.
   *     "child_of" (int) default is 0 - See {@link get_categories()}.
   *     "exclude" (string) - See {@link get_categories()}.
   *     "depth" (int) - The max depth.
   *     "tab_index" (int) - Tab index for select element.
   *     "name" (string) - The name attribute value for select element.
   *     "id" (string) - The ID attribute value for select element. Defaults to name if omitted.
   *     "class" (string) - The class attribute value for select element.
   *     "selected" (int) - Which category ID is selected.
   *     "taxonomy" (string) - The name of the taxonomy to retrieve. Defaults to category.
   *
   * The "hierarchical" argument, which is disabled by default, will override the
   * depth argument, unless it is true. When the argument is false, it will
   * display all of the categories. When it is enabled it will use the value in
   * the "depth" argument.
   *
   */
  function mp_dropdown_categories_sc($atts) {
    return mp_dropdown_categories(false, $atts);
  }

  /**
   * Displays a list of popular products ordered by sales.
   *
   * @param int num Optional, max number of products to display. Defaults to 5
   */
  function mp_popular_products_sc($atts) {
    extract(shortcode_atts(array(
  		'number' => 5,
  	), $atts));

    return mp_popular_products(false, $number);
  }
  
  /**
   * Displays related products for the passed product id
   *
   * @param int $product_id. 
   * @param string $relate_by Optional, whether to limit related to the same category, tags or both.
   * @param bool $echo. Optional, whether to echo or return the results
   * @param int $limit. Optional The number of products we want to retrieve.
   * @param bool $simple_list Optional, whether to show the related products based on the "list_view" setting or as a simple unordered list
   */
  function mp_related_products_sc($atts) {
  	global $mp;
  	$args = shortcode_atts($mp->defaults['related_products'], $atts);
		return mp_related_products($args);
  }

  /*
   * Displays a list of products according to preference. Optional values default to the values in Presentation Settings -> Product List
   *
   * @param bool paginate Optional, whether to paginate
   * @param int page Optional, The page number to display in the product list if paginate is set to true.
   * @param int per_page Optional, How many products to display in the product list if $paginate is set to true.
   * @param string order_by Optional, What field to order products by. Can be: title, date, ID, author, price, sales, rand
   * @param string order Optional, Direction to order products by. Can be: DESC, ASC
   * @param string category Optional, limit to a product category
   * @param string tag Optional, limit to a product tag
   * @param bool list_view Optional, show as list. Grid default
   * @param bool filters Optional, show filters
   */
  function mp_list_products_sc($atts) {
  	global $mp;
  	
    $args = shortcode_atts($mp->defaults['list_products'], $atts);
    $args['echo'] = false;
    
    return mp_list_products($args);
  }
	
	/*
	* Displays a single product according to preference
	* 
	* @param int $product_id the ID of the product to display
	* @param bool $title Whether to display the title
	* @param bool/string $content Whether and what type of content to display. Options are false, 'full', or 'excerpt'. Default 'full'
	* @param bool/string $image Whether and what context of image size to display. Options are false, 'single', or 'list'. Default 'single'
	* @param bool $meta Whether to display the product meta
  */
  function mp_product_sc($atts) {
    extract(shortcode_atts(array(
  		'product_id' => false,
  		'title' => true,
  		'content' => 'full',
  		'image' => 'single',
  		'meta' => true
  	), $atts));

    return mp_product(false, $product_id, $title, $content, $image, $meta);
  }
	
	/**
		* Displays the product featured image
		*
		* @param string $context Options are list, single, or widget
		* @param int $product_id The post_id for the product. Optional if in the loop
		* @param int $size An optional width/height for the image if contect is widget
		* @param string $align An option alignment of the image
	 */
  function mp_product_image_sc($atts) {
    extract(shortcode_atts(array(
  		'context' => 'single',
			'product_id' => NULL,
			'size' => NULL,
			'align' => NULL,
  	), $atts));

    return mp_product_image(false, $context, $product_id, $size, $align);
  }
	
	/*
	 * Displays the buy or add to cart button
	 *
	 * @param string $context Options are list or single
	 * @param int $post_id The post_id for the product. Optional if in the loop
	 */
  function mp_buy_button_sc($atts) {
    extract(shortcode_atts(array(
  		'context' => 'single',
			'product_id' => NULL
  	), $atts));

    return mp_buy_button(false, $context, $product_id);
  }

	/*
	 * Displays the product price (and sale price)
	 *
	 * @param int $product_id The post_id for the product. Optional if in the loop
	 * @param sting $label A label to prepend to the price. Defaults to "Price: "
	 */
  function mp_product_price_sc($atts) {
    extract(shortcode_atts(array(
  		'label' => true,
			'product_id' => NULL
  	), $atts));

    return mp_product_price(false, $product_id, $label);
  }
	
	/*
	 * Displays the product meta box
	 *
	 * @param string $context Options are list or single
	 * @param int $product_id The post_id for the product. Optional if in the loop
	 * @param sting $label A label to prepend to the price. Defaults to "Price: "
	 */
  function mp_product_meta_sc($atts) {
    extract(shortcode_atts(array(
			'context' => 'single',
  		'label' => true,
			'product_id' => NULL
  	), $atts));
		
		$content = '<div class="mp_product_meta">';
		$content .= mp_product_price(false, $product_id, $label);
		$content .= mp_buy_button(false, $context, $product_id);
		$content .= '</div>';
    return $content;
  }
	
	/*
	 * Displays the product SKU
	 *
	 * @param int $product_id The post_id for the product. Optional if in the loop
	 * @param string $seperator The seperator to put between skus, default ', '
	 */
  function mp_product_sku_sc($atts) {
    extract(shortcode_atts(array(
  		'seperator' => false,
			'product_id' => NULL
  	), $atts));
		
		return mp_product_sku( false, $product_id, $seperator );
  }
	
  /**
   * Returns the current shopping cart link.
   * @param bool url Optional, whether to return a link or url. Defaults to show link.
   * @param string link_text Optional, text to show in link.
   */
  function mp_cart_link_sc($atts) {
    extract(shortcode_atts(array(
  		'url' => false,
  		'link_text' => '',
  	), $atts));

    return mp_cart_link(false, $url, $link_text);
  }

  /**
   * Returns the current store link.
   * @param bool url Optional, whether to return a link or url. Defaults to show link.
   * @param string link_text Optional, text to show in link.
   */
  function mp_store_link_sc($atts) {
    extract(shortcode_atts(array(
  		'url' => false,
  		'link_text' => '',
  	), $atts));

    return mp_store_link(false, $url, $link_text);
  }

  /**
   * Returns the current product list link.
   * @param bool url Optional, whether to return a link or url. Defaults to show link.
   * @param string link_text Optional, text to show in link.
   */
  function mp_products_link_sc($atts) {
    extract(shortcode_atts(array(
  		'url' => false,
  		'link_text' => '',
  	), $atts));

    return mp_products_link(false, $url, $link_text);
  }

  /**
   * Returns the current order status link.
   * @param bool url Optional, whether to return a link or url. Defaults to show link.
   * @param string link_text Optional, text to show in link.
   */
  function mp_orderstatus_link_sc($atts) {
    extract(shortcode_atts(array(
  		'url' => false,
  		'link_text' => '',
  	), $atts));

    return mp_orderstatus_link(false, $url, $link_text);
  }

/**
 * Returns the current store navigation links.
 *
 */
  function mp_store_navigation_sc($atts) {
    return mp_store_navigation(false);
  }

}
$mp_shortcodes = new MarketPress_Shortcodes();
