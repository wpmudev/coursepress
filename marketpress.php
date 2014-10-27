<?php
/*
Plugin Name: MarketPress (CoursePress Pro Bundle)  
Version: 2.9.5.4
Plugin URI: https://premium.wpmudev.org/project/e-commerce/
Description: The complete WordPress ecommerce plugin - works perfectly with BuddyPress and Multisite too to create a social marketplace, where you can take a percentage! Activate the plugin, adjust your settings then add some products to your store.
Author: WPMU DEV
Author URI: http://premium.wpmudev.org/
Text Domain: mp

Copyright 2009-2014 Incsub (http://incsub.com)
Author - Aaron Edwards
Contributors - Arnold Bailey, Jonathan Cowher, Ryan Welcher, Marko Miljus, Aristeides Stathopoulos, Jeffri H, Coleman Stevenson, Enzo Maddalena

This program is free software; you can redistribute it and/or modify
it under the terms of the GNU General Public License (Version 2 - GPLv2) as published by
the Free Software Foundation.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.	See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program; if not, write to the Free Software
Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA	 02111-1307	 USA
*/

class MarketPress {
	var $version = '2.9.5.4';
	var $location;
	var $plugin_dir = '';
	var $plugin_url = '';
	var $plugin_file = '';
	var $product_template;
	var $product_taxonomy_template;
	var $product_list_template;
	var $store_template;
	var $checkout_template;
	var $orderstatus_template;
	var $language = '';
	var $checkout_error = false;
	var $cart_cache = false;
	var $is_shop_page = false;
	var $global_cart = false;
	var $skip_shipping_notice = false;
	var $weight_printed = false;
	var $defaults = array(
		/*	IMPORTANT! DO NOT CHANGE THE ORDER OF THESE ARGUMENTS!
				REQUIRED FOR BACKWARDS COMPATIBILITY. IF YOU NEED TO
				ADD AN ADDITIONAL DEFAULT ADD IT TO THE END OF EACH ARRAY */
		'list_products' => array(
			'echo' => true,
			'paginate' => NULL,
			'page' => NULL,
			'per_page' => NULL,
			'order_by' => NULL,
			'order' => NULL,
			'category' => NULL,
			'tag' => NULL,
			'list_view'=> NULL,
			'filters' => NULL,
		),
		'related_products' => array(
			'product_id' => NULL,
			'relate_by' => 'both',
			'echo' => false,
			'limit' => NULL,
			'simple_list' => NULL,
		),
	);

	function __construct() {
	 //setup our variables
	 $this->init_vars();
	 
	 //maybe install
	 add_action('plugins_loaded', array(&$this, 'install'));
	 add_action('wpmu_new_blog', array(&$this, 'setup_new_blog'), 10, 6);
	 
	if ( MP_LITE === false ) {
		//load dashboard notice
		global $wpmudev_notices;
		$wpmudev_notices[] = array( 'id'=> 144,'name'=> 'MarketPress', 'screens' => array( 'edit-product', 'product', 'edit-product_category', 'edit-product_tag', 'product_page_marketpress-orders', 'product_page_marketpress', 'settings_page_marketpress-ms-network' ) );
		include_once( $this->plugin_dir . 'dash-notice/wpmudev-dash-notification.php' );
	}

	 //load template functions
	 require_once( $this->plugin_dir . 'template-functions.php' );

	 //load shortcodes
	 include_once( $this->plugin_dir . 'marketpress-shortcodes.php' );

	//load oembed
	include_once( $this->plugin_dir . 'marketpress-oembed.php');

		//load stats
	 include_once( $this->plugin_dir . 'marketpress-stats.php' );

	 //load sitewide features if WPMU
	 if ( is_multisite() ) {
		include_once( $this->plugin_dir . 'marketpress-ms.php' );
		$network_settings = get_site_option( 'mp_network_settings' );
			if ( isset($network_settings['global_cart']) && $network_settings['global_cart'] )
				$this->global_cart = true;
	 }
	 	
	 	//initialize the session for admin screens that need it
	 	add_action('current_screen', array(&$this, 'admin_start_session'));
	 
	 	//make sure admin_url() returns proper scheme - set to super low priority to make sure this is run last
	 	add_filter('admin_url', array(&$this, 'filter_admin_url'), 999);

		//localize the plugin
		add_action( 'plugins_loaded', array(&$this, 'localization'), 9 );

		//load APIs and plugins
		add_action( 'plugins_loaded', array(&$this, 'load_plugins') );

		//load importers
		add_action( 'plugins_loaded', array(&$this, 'load_importers') );

		//custom post type
		add_action( 'init', array(&$this, 'register_custom_posts'), 0 ); //super high priority
		add_filter( 'request', array(&$this, 'handle_edit_screen_filter') );
		add_filter( 'post_updated_messages', array(&$this, 'post_updated_messages') );

		//edit products page
		add_filter( 'manage_product_posts_columns', array(&$this, 'edit_products_columns') );
		add_action( 'manage_product_posts_custom_column', array(&$this, 'edit_products_custom_columns') );
		add_action( 'restrict_manage_posts', array(&$this, 'edit_products_filter') );

		add_filter( 'post_row_actions', array(&$this, 'edit_products_custom_row_actions'), 10, 2);
		add_filter( 'admin_action_copy-product', array(&$this, 'edit_products_copy_action') );

		//manage orders page
		add_filter( 'manage_product_page_marketpress-orders_columns', array(&$this, 'manage_orders_columns') );
		add_action( 'manage_mp_order_posts_custom_column', array(&$this, 'manage_orders_custom_columns') );

		//Plug admin pages
		add_action( 'admin_menu', array(&$this, 'add_menu_items') );
		add_action( 'admin_print_styles', array(&$this, 'admin_css') );
		add_action( 'admin_print_scripts', array(&$this, 'admin_script_post') );
		add_action( 'admin_notices', array(&$this, 'admin_nopermalink_warning') );
		add_filter( 'plugin_action_links_' . plugin_basename(__FILE__), array(&$this, 'plugin_action_link'), 10, 2);
		add_action( 'wp_ajax_mp-hide-help', array(&$this, 'hide_help') );

		//Meta boxes
		add_action( 'add_meta_boxes_product', array(&$this, 'meta_boxes') );
		add_action( 'wp_insert_post', array(&$this, 'save_product_meta'), 10, 2 );
		add_filter( 'enter_title_here', array(&$this, 'filter_title') );

		//Templates and Rewrites
		add_action( 'wp', array(&$this, 'load_store_templates') );
		add_action( 'template_redirect', array(&$this, 'load_store_theme') );
		add_action( 'pre_get_posts', array(&$this, 'remove_canonical') );
		add_filter( 'rewrite_rules_array', array(&$this, 'add_rewrite_rules') );
		add_filter( 'query_vars', array(&$this, 'add_queryvars') );
		add_action( 'option_rewrite_rules', array(&$this, 'check_rewrite_rules') );
		add_action( 'init', array(&$this, 'flush_rewrite_check'), 99 );
		
		if ( MP_HIDE_MENUS === false ) { //allows you to hide MP menus
			add_filter( 'wp_list_pages', array(&$this, 'filter_list_pages'), 10, 2 );
		}
		
		/* enqueue lightbox - this will just register the styles/scripts
			 the won't actually be output unless they're needed */
		add_action('wp_enqueue_scripts', array(&$this, 'enqueue_lightbox'));

		//Payment gateway returns
		add_action( 'pre_get_posts', array(&$this, 'handle_gateway_returns'), 1 );

		//Store cart handling
		add_action( 'template_redirect', array(&$this, 'store_script') ); //only on front pages
		/* use both actions so logged in and not logged in users can send this AJAX request */
		add_action( 'wp_ajax_nopriv_mp-update-cart', array(&$this, 'update_cart') );
		add_action( 'wp_ajax_mp-update-cart', array(&$this, 'update_cart') );
		add_action( 'wp_ajax_mp-province-field', 'mp_province_field' ); //province field callback for shipping form
		add_action( 'wp_ajax_nopriv_mp-province-field', 'mp_province_field' );
		add_action( 'wp_ajax_mp-orders-export', array(&$this, 'export_orders_csv') );
		add_action( 'wp_logout', array(&$this, 'logout_clear_session') ); //see http://premium.wpmudev.org/forums/topic/security-issue-with-marketpress

		add_action( 'wp_ajax_nopriv_get_products_list', array(&$this, 'get_products_list') );
		add_action( 'wp_ajax_get_products_list', array(&$this, 'get_products_list') );

		//Relies on post thumbnails for products
		add_action( 'after_setup_theme', array(&$this, 'post_thumbnails'), 9999 );

		//Add widgets
		if ( ! $this->get_setting('disable_cart', 0) )
			add_action( 'widgets_init', create_function('', 'return register_widget("MarketPress_Shopping_Cart");') );

		add_action( 'widgets_init', create_function('', 'return register_widget("MarketPress_Product_List");') );
		add_action( 'widgets_init', create_function('', 'return register_widget("MarketPress_Categories_Widget");') );
		add_action( 'widgets_init', create_function('', 'return register_widget("MarketPress_Tag_Cloud_Widget");') );

		// Edit profile
		add_action( 'edit_user_profile_update', array(&$this, 'user_profile_update') );
		add_action( 'personal_options_update', array(&$this, 'user_profile_update') );
		add_action( 'edit_user_profile', array(&$this, 'user_profile_fields') );
		add_action( 'show_user_profile', array(&$this, 'user_profile_fields') );
	}
	
	function post_updated_messages( $messages ) {
		global $post, $post_ID;
		
		$post_type = get_post_type($post_ID);
		
		if ( $post_type != 'mp_order' && $post_type != 'product' ) { return $messages; }
		
		$obj = get_post_type_object($post_type);
		$singular = $obj->labels->singular_name;
		
		$messages[$post_type] = array(
			0 => '', // Unused. Messages start at index 1.
			1 => sprintf(__($singular.' updated. <a href="%s">View ' . strtolower($singular) . '</a>'), esc_url(get_permalink($post_ID))),
			2 => __('Custom field updated.'),
			3 => __('Custom field deleted.'),
			4 => __($singular.' updated.'),
			5 => isset($_GET['revision']) ? sprintf(__($singular . ' restored to revision from %s'), wp_post_revision_title((int) $_GET['revision'], false)) : false,
			6 => sprintf(__($singular.' published. <a href="%s">View ' . strtolower($singular).'</a>'), esc_url(get_permalink($post_ID))),
			7 => __('Page saved.'),
			8 => sprintf(__($singular . ' submitted. <a target="_blank" href="%s">Preview ' . strtolower($singular).'</a>'), esc_url(add_query_arg('preview', 'true', get_permalink($post_ID)))),
			9 => sprintf(__($singular . ' scheduled for: <strong>%1$s</strong>. <a target="_blank" href="%2$s">Preview ' . strtolower($singular).'</a>'), date_i18n(__('M j, Y @ G:i'), strtotime($post->post_date)), esc_url(get_permalink($post_ID))),
			10 => sprintf(__($singular . ' draft updated. <a target="_blank" href="%s">Preview ' . strtolower($singular).'</a>'), esc_url(add_query_arg( 'preview', 'true', get_permalink($post_ID)))),
		);
		
		return $messages;
	}
	
	function admin_start_session() {
		$screen = get_current_screen();
		if ( $screen->id == 'profile' || $screen->id == 'user-edit' || $screen->id == 'user-new' ) {
			$this->start_session();
		}
	}
	
	function setup_new_blog( $blog_id, $user_id, $domain, $path, $site_id, $meta ) {
		//flag to run on next visit to blog
		update_blog_option($blog_id, 'mp_do_install', 1);
	}

	function install() {
		if ( !get_option('mp_do_install') ) {
			if ( get_option('mp_version') == $this->version ) {
				return;
			}
		}
			
		$old_settings = get_option('mp_settings');
		$old_version = get_option('mp_version');

	 //our default settings
	 $default_settings = array (
		'base_country' => 'US',
		'tax' => array (
			'rate' => 0,
				'label' => __('Taxes', 'mp'),
			'tax_shipping' => 1,
			'tax_inclusive' => 0,
				'tax_digital' => 1,
				'downloadable_address' => 0
		),
		'currency' => 'USD',
		'curr_symbol_position' => 1,
		'curr_decimal' => 1,
		'disable_cart' => 0,
		'hide_popup' => 0,
		'inventory_threshhold' => 3,
		'inventory_remove' => 0,
		'max_downloads' => 5,
			'download_order_limit' => 1,
		'force_login' => 0,
		'ga_ecommerce' => 'none',
			'special_instructions' => 0,
		'store_theme' => 'icons',
			'show_img' => 1,
		'product_img_height' => 150,
		'product_img_width' => 150,
		'list_img_height' => 150,
		'list_img_width' => 150,
			'show_excerpt' => 1,
		'per_page' => 20,
		'order_by' => 'title',
		/* Translators: change default slugs here */
		'slugs' => array (
			'store' => __('store', 'mp'),
			'products' => __('products', 'mp'),
			'cart' => __('shopping-cart', 'mp'),
			'orderstatus' => __('order-status', 'mp'),
			'category' => __('category', 'mp'),
			'tag' => __('tag', 'mp')
		),
		'product_button_type' => 'addcart',
		'show_quantity' => 1,
		'product_img_size' => 'medium',
		'show_lightbox' => 1,
			'disable_large_image' => 0,
		'list_view' => 'grid',
		'list_button_type' => 'addcart',
		'show_thumbnail' => 1,
		'list_img_size' => 'thumbnail',
		'paginate' => 1,
		'show_filters' => 1,
		'order' => 'DESC',
		'show_purchase_breadcrumbs' => 1,
		'shipping' => array (
			'allowed_countries' => array ('CA', 'US'),
			'method' => 'flat-rate',
				'system' => 'english'
		),
		'gateways' => array (
			'paypal-express' => array (
			 'locale' => 'US',
			 'currency' => 'USD',
			 'mode' => 'sandbox'
			),
			'paypal-chained' => array (
			 'currency' => 'USD',
			 'mode' => 'sandbox'
			)
		),
		'msg' => array (
			'product_list' => '',
			'order_status' => __('<p>If you have any questions about your order please do not hesitate to contact us.</p>', 'mp'),
			'cart' => '',
			'shipping' => __('<p>Please enter your shipping information in the form below to proceed with your order.</p>', 'mp'),
			'checkout' => '',
			'confirm_checkout' => __('<p>You are almost done! Please do a final review of your order to make sure everything is correct then click the "Confirm Payment" button.</p>', 'mp'),
			'success' => __('<p>Thank you for your order! We appreciate your business, and please come back often to check out our new products.</p>', 'mp')
		),
		'store_email' => get_option("admin_email"),
		'email' => array (
			'new_order_subject' => __('Your Order Confirmation (ORDERID)', 'mp'),
			'new_order_txt' => __("Thank you for your order CUSTOMERNAME!

Your order has been received, and any items to be shipped will be processed as soon as possible. Please refer to your Order ID (ORDERID) whenever contacting us.
Here is a confirmation of your order details:

Order Information:
ORDERINFO

Shipping Information:
SHIPPINGINFO

Payment Information:
PAYMENTINFO

ORDERNOTES

You can track the latest status of your order here: TRACKINGURL

Thanks again!", 'mp'),
			'shipped_order_subject' => __('Your Order Has Been Shipped! (ORDERID)', 'mp'),
			'shipped_order_txt' => __("Dear CUSTOMERNAME,

Your order has been shipped! Depending on the shipping method and your location it should be arriving shortly. Please refer to your Order ID (ORDERID) whenever contacting us.
Here is a confirmation of your order details:

Order Information:
ORDERINFO

Shipping Information:
SHIPPINGINFO

Payment Information:
PAYMENTINFO

ORDERNOTES

You can track the latest status of your order here: TRACKINGURL

Thanks again!", 'mp')
		),
		'social' => array(
			 'pinterest' => array(
			'show_pinit_button' => 'off',
			'show_pin_count' => 'none'
		 ),
		),
		'related_products' => array(
			 'show' => 1,
		 'relate_by' => 'both',
		 'simple_list' => 0,
		 'show_limit' => 3
		),
		'image_alignment_single' => 'alignleft',
		'image_alignment_list' => 'alignleft',
	 );

	 //filter default settings
	 $default_settings = apply_filters( 'mp_default_settings', $default_settings );
	 $settings = wp_parse_args( (array)$old_settings, $default_settings );
	 update_option( 'mp_settings', $settings );

		//2.1.4 update
		if ( version_compare($old_version, '2.1.4', '<') )
			$this->update_214();
			
		//2.9.2.3 update
		if ( version_compare($old_version, '2.9.2.3', '<') )
			$this->update_2923();
			
	 //only run these on first install
	 if ( empty($old_settings) ) {
			//define settings that don't need to autoload for efficiency
			add_option( 'mp_coupons', '', '', 'no' );
			add_option( 'mp_store_page', '', '', 'no' );

			//create store page
			add_action('admin_init', array(&$this, 'create_store_page'));

			//add cart widget to first sidebar
			add_action( 'widgets_init', array(&$this, 'add_default_widget'), 11 );
			
			// create store menu items
			$this->create_store_menu_items();
		}

		//add action to flush rewrite rules after we've added them for the first time
		update_option('mp_flush_rewrite', 1);
		
		update_option('mp_version', $this->version);
		delete_option('mp_do_install');
	}
	
	// create store menu items
	function create_store_menu_items() {
		global $wpdb;
		
		$menu_id = $wpdb->get_var("
			SELECT t1.term_id
			FROM $wpdb->term_taxonomy AS t1
			INNER JOIN $wpdb->terms AS t2 ON t1.term_id = t2.term_id
			WHERE t1.taxonomy = 'nav_menu'
			LIMIT 1
		");
		
		if ( empty($menu_id) ) {
			return; // bail - no menus have been created
		}
				
		// add store menu
		$top_menu = wp_update_nav_menu_item($menu_id, 0, array(
			'menu-item-title' => __('Store', 'mp'),
			'menu-item-url' => home_url('store/'),
			'menu-item-status' => 'publish',
			'menu-item-parent-id' => 0
		));
		
		// add products menu
		wp_update_nav_menu_item($menu_id, 0, array(
			'menu-item-title' => __('Products', 'mp'),
			'menu-item-url' => home_url('store/products/'),
			'menu-item-status' => 'publish',
			'menu-item-position' => 1,
			'menu-item-parent-id' => $top_menu
		));
		
		// add shopping cart menu
		wp_update_nav_menu_item($menu_id, 0, array(
			'menu-item-title' => __('Shopping Cart', 'mp'),
			'menu-item-url' => home_url('store/shopping-cart/'),
			'menu-item-status' => 'publish',
			'menu-item-position' => 2,
			'menu-item-parent-id' => $top_menu
		));
		
		// add order status menu
		wp_update_nav_menu_item($menu_id, 0, array(
			'menu-item-title' => __('Order Status', 'mp'),
			'menu-item-url' => home_url('store/order-status/'),
			'menu-item-status' => 'publish',
			'menu-item-position' => 3,
			'menu-item-parent-id' => $top_menu
		));
	}
	
	//run on 2.9.2.3 update to fix low inventory emails not being sent
	function update_2923() {
		global $wpdb;
		$wpdb->delete($wpdb->postmeta, array('meta_key' => 'mp_stock_email_sent'), array('%s'));
	}

	//run on 2.1.4 update to fix price sorts
	function update_214() {
		global $wpdb;

		$posts = $wpdb->get_col("SELECT ID FROM {$wpdb->posts} WHERE post_type = 'product'");

		foreach ($posts as $post_id) {
			$meta = get_post_custom($post_id);
			//unserialize
			foreach ($meta as $key => $val) {
				$meta[$key] = maybe_unserialize($val[0]);
				if (!is_array($meta[$key]) && $key != "mp_is_sale" && $key != "mp_track_inventory" && $key != "mp_product_link" && $key != "mp_file" && $key != "mp_price_sort")
					$meta[$key] = array($meta[$key]);
			}

			//fix price sort field if missing
			if ( empty($meta["mp_price_sort"]) && is_array($meta["mp_price"]) ) {
				if ( $meta["mp_is_sale"] && $meta["mp_sale_price"][0] )
					$sort_price = $meta["mp_sale_price"][0];
				else
					$sort_price = $meta["mp_price"][0];
				update_post_meta($post_id, 'mp_price_sort', $sort_price);
			}
		}
	}
	
	function localization() {
		// Load up the localization file if we're using WordPress in a different language
		// Place it in this plugin's "languages" folder and name it "mp-[value in wp-config].mo"
		$mu_plugins = wp_get_mu_plugins();
		$lang_dir = dirname(plugin_basename($this->plugin_file)) . 'includes/marketpress/marketpress-includes/languages/';
		$custom_path = WP_LANG_DIR . '/marketpress/mp-' . get_locale() . '.mo';
		
		if ( file_exists($custom_path) ) {
			load_textdomain('mp', $custom_path);
		} elseif ( in_array($this->plugin_file, $mu_plugins) ) {
			load_muplugin_textdomain('mp', $lang_dir);
		} else {
			load_plugin_textdomain('mp', false, $lang_dir);
		}

		//setup language code for jquery datepicker translation
		$temp_locales = explode('_', get_locale());
		$this->language = ($temp_locales[0]) ? $temp_locales[0] : 'en';
	}

	function init_vars() {
		//setup proper directories
		$this->plugin_file = __FILE__;
		$this->plugin_dir = plugin_dir_path(__FILE__) . 'includes/marketpress/marketpress-includes/';
		$this->plugin_url = plugin_dir_url(__FILE__) . 'includes/marketpress/marketpress-includes/';

		//load constants
		require_once( $this->plugin_dir . 'constants.php' );
		
		//load data structures
		require_once( $this->plugin_dir . 'marketpress-data.php' );
	}

	/* Only load code that needs BuddyPress to run once BP is loaded and initialized. */
	function load_bp_features() {
	 include_once( $this->plugin_dir . 'marketpress-bp.php' );
	}

	function load_importers() {
	 include_once( $this->plugin_dir . 'marketpress-importers.php' );
	}

	function load_plugins() {
	 if ( is_network_admin() || !$this->get_setting('disable_cart') ) {
		//load shipping plugin API
		require_once( $this->plugin_dir . 'marketpress-shipping.php' );
		$this->load_shipping_plugins();

		//load gateway plugin API
		require_once( $this->plugin_dir . 'marketpress-gateways.php' );
		$this->load_gateway_plugins();
	 }
	}

	function load_shipping_plugins() {
		//save shipping method. Put here to be before plugin is loaded
		if (isset($_POST['shipping_settings'])) {
		 	$settings = get_option('mp_settings');
			$settings['shipping']['method'] = $_POST['mp']['shipping']['method'];
			$settings['shipping']['calc_methods'] = isset($_POST['mp']['shipping']['calc_methods']) ? $_POST['mp']['shipping']['calc_methods'] : array();
			update_option('mp_settings', $settings);
		}

		//get shipping plugins dir
		$dir = $this->plugin_dir . 'plugins-shipping/';

		//search the dir for files
		$shipping_plugins = array();
		if ( ! is_dir($dir) ) {
			return;
		}
		
		if ( ! $dh = opendir($dir) ) {
			return;
		}
		
		while ( ($plugin = readdir($dh)) !== false ) {
			if ( substr( $plugin, -4 ) == '.php' ) {
				$shipping_plugins[] = $dir . $plugin;
			}
		}
		
		closedir($dh);
		sort($shipping_plugins);

		//include them suppressing errors
		foreach ( $shipping_plugins as $file ) {
			@include_once($file);
		}

		//allow plugins from an external location to register themselves
		do_action('mp_load_shipping_plugins');

		//load chosen plugin class
		global $mp_shipping_plugins, $mp_shipping_active_plugins;
		
		if ( ! is_admin() && $this->global_cart ) {
			/* global cart is being used so we need to go through the cart contents and
			get the active shipping method for each blog in the cart */
			
			$cart = $this->get_cart_contents(true);
			$blogs = array_keys($cart);
			$current_blog_id = get_current_blog_id();
			$methods = array();
			
			foreach ( $blogs as $blog_id ) {
				switch_to_blog($blog_id);
				
				$shipping = $this->get_setting('shipping');
				
				//load only selected shipping method
				$class = isset($mp_shipping_plugins[$shipping['method']][0]) ? $mp_shipping_plugins[$shipping['method']][0] : '';
				if ( class_exists($class) ) {
					$mp_shipping_active_plugins[$shipping['method']] = new $class;
				}
			}
			
			switch_to_blog($current_blog_id);
		} else {
			$shipping = $this->get_setting('shipping');
			if ( $this->get_setting('shipping->method') == 'calculated' ) {
				//load just the calculated ones
				foreach ( (array) $mp_shipping_plugins as $code => $plugin ) {
					if ( $plugin[2] ) {
						if ( isset($shipping['calc_methods'][$code]) && class_exists($plugin[0]) && !$plugin[3] ) {
							$mp_shipping_active_plugins[$code] = new $plugin[0];
						}
					}
				}
			} else {			
				//load only selected shipping method
				$class = isset($mp_shipping_plugins[$shipping['method']][0]) ? $mp_shipping_plugins[$shipping['method']][0] : '';
				if ( class_exists($class) ) {
					$mp_shipping_active_plugins[$shipping['method']] = new $class;
				}
			}
		}
	}

	function load_gateway_plugins() {

	 //save settings from screen. Put here to be before plugin is loaded
	 if (isset($_POST['gateway_settings'])) {
		$settings = get_option('mp_settings');

		//see if there are checkboxes checked
		if ( isset( $_POST['mp']['gateways']['allowed'] ) ) {
				$settings['gateways']['allowed'] = $_POST['mp']['gateways']['allowed'];
		} else {
			//blank array if no checkboxes
			$settings['gateways']['allowed'] = array();
		}

		update_option('mp_settings', $settings);
	 }

	 //get gateway plugins dir
	 $dir = $this->plugin_dir . 'plugins-gateway/';

	 //search the dir for files
	 $gateway_plugins = array();
		if ( !is_dir( $dir ) )
			return;
		if ( ! $dh = opendir( $dir ) )
			return;
		while ( ( $plugin = readdir( $dh ) ) !== false ) {
			if ( substr( $plugin, -4 ) == '.php' )
				$gateway_plugins[] = $dir . '/' . $plugin;
		}
		closedir( $dh );
		sort( $gateway_plugins );

		//include them suppressing errors
		foreach ($gateway_plugins as $file)
		include( $file );

	 //allow plugins from an external location to register themselves
		do_action('mp_load_gateway_plugins');

	 //load chosen plugin classes
	 global $mp_gateway_plugins, $mp_gateway_active_plugins;
	 $gateways = $this->get_setting('gateways');
	 $network_settings = get_site_option( 'mp_network_settings' );

	 foreach ((array)$mp_gateway_plugins as $code => $plugin) {
		$class = $plugin[0];
			//if global cart is enabled force it
		if ( $this->global_cart ) {
			if ( $code == $network_settings['global_gateway'] && class_exists($class) ) {
			 $mp_gateway_active_plugins[] = new $class;
			 break;
				}
		} elseif ( !is_network_admin() ) {
			 if ( isset( $gateways['allowed'] ) && in_array($code, (array)$gateways['allowed']) && class_exists($class) && !$plugin[3] )
				$mp_gateway_active_plugins[] = new $class;
			}
	 }
	}
	
	function parse_args_r( $array, $array1 ) {
		function recurse($array, $array1) {
			foreach ( $array1 as $key => $value ) {
				// create new key in $array, if it is empty or not an array
				if ( ! isset($array[$key]) || (isset($array[$key]) && ! is_array($array[$key])) )
				{
					$array[$key] = array();
				}
 
				// overwrite the value in the base array
				if ( is_array($value) ) {
					$value = recurse($array[$key], $value);
				}
				
				$array[$key] = $value;
			}
			return $array;
		}
 
		// handle the arguments, merge one by one
		$args = func_get_args();
		$array = $args[0];
		if ( ! is_array($array) ) {
			return $array;
		}
		
		for ( $i = 1; $i < count($args); $i++ ) {
			if ( is_array($args[$i]) ) {
				$array = recurse($array, $args[$i]);
			}
		}
		
		return $array;
	}
	
	/*
	 * function get_setting
	 * @param string $key A setting key, or -> separated list of keys to go multiple levels into an array
	 * @param mixed $default Returns when setting is not set
	 *
	 * an easy way to get to our settings array without undefined indexes
	 */
	function get_setting($key, $default = null) {
	 	$settings = get_option('mp_settings');
		$keys = explode('->', $key);
		array_map('trim', $keys);
		if (count($keys) == 1)
			$setting = isset($settings[$keys[0]]) ? $settings[$keys[0]] : $default;
		else if (count($keys) == 2)
			$setting = isset($settings[$keys[0]][$keys[1]]) ? $settings[$keys[0]][$keys[1]] : $default;
		else if (count($keys) == 3)
			$setting = isset($settings[$keys[0]][$keys[1]][$keys[2]]) ? $settings[$keys[0]][$keys[1]][$keys[2]] : $default;
		else if (count($keys) == 4)
			$setting = isset($settings[$keys[0]][$keys[1]][$keys[2]][$keys[3]]) ? $settings[$keys[0]][$keys[1]][$keys[2]][$keys[3]] : $default;

		return apply_filters( "mp_setting_".implode('', $keys), $setting, $default );
	}

	function update_setting($key, $value) {
	 $settings = get_option( 'mp_settings' );
	 $settings[$key] = $value;
		return update_option('mp_settings', $settings);
	}

	function handle_gateway_returns($wp_query) {
		if ( is_admin() ) return;

	 //listen for gateway IPN returns and tie them in to proper gateway plugin
		if(!empty($wp_query->query_vars['paymentgateway'])) {
			do_action( 'mp_handle_payment_return_' . $wp_query->query_vars['paymentgateway'] );
			// exit();
		}
	}

	function remove_canonical($wp_query) {
		if ( is_admin() ) return;

		//stop canonical problems with virtual pages redirecting
		$page = get_query_var('pagename');
		if ($page == 'cart' || $page == 'orderstatus' || $page == 'product_list') {
			remove_action('template_redirect', 'redirect_canonical');
		}
	}

	function admin_nopermalink_warning() {
	 //warns admins if permalinks are not enabled on the blog
	 if ( current_user_can('manage_options') && !get_option('permalink_structure') )
		echo '<div class="error"><p>'.__('You must enable Pretty Permalinks</a> to use MarketPress - <a href="options-permalink.php">Enable now &raquo;</a>', 'mp').'</p></div>';
	}

	function plugin_action_link($links, $file) {
		// the anchor tag and href to the URL we want. For a "Settings" link, this needs to be the url of your settings page
		$settings_link = '<a href="' . admin_url('edit.php?post_type=product&page=marketpress') . '">' . __('Settings', 'mp') . '</a>';
		// add the link to the list
		array_unshift($links, $settings_link);
	 return $links;
	}

	function add_menu_items() {
	 //only process the manage orders page for editors and above and if orders hasn't been disabled
	 $order_cap = apply_filters('mp_orders_cap', 'edit_others_posts');
	 
	 if (current_user_can($order_cap) && !$this->get_setting('disable_cart')) {
		$num_posts = wp_count_posts('mp_order'); //get pending order count
		$count = $num_posts->order_received + $num_posts->order_paid;
		if ( $count > 0 )
				$count_output = '&nbsp;<span class="update-plugins"><span class="updates-count count-' . $count . '">' . $count . '</span></span>';
			else
				$count_output = '';
		$orders_page = add_submenu_page('edit.php?post_type=product', __('Manage Orders', 'mp'), __('Manage Orders', 'mp') . $count_output, $order_cap, 'marketpress-orders', array(&$this, 'orders_page'));
	 }
	
	 $page = add_submenu_page('edit.php?post_type=product', __('Store Settings', 'mp'), __('Store Settings', 'mp'), 'manage_options', 'marketpress', array(&$this, 'admin_page'));
	 add_action( 'admin_print_scripts-' . $page, array(&$this, 'admin_script_settings') );
	 add_action( 'admin_print_styles-' . $page, array(&$this, 'admin_css_settings') );
	
		if ( WPMUDEV_REMOVE_BRANDING === false ) {
			add_action( "load-{$page}", array( &$this, 'add_help_tab' ) );
		}
	}

	function add_help_tab() {
		get_current_screen()->add_help_tab( array(
			'id' => 'marketpress-help',
			'title' => __('MarketPress Instructions', 'mp'),
			'content' => '<iframe src="//premium.wpmudev.org/wdp-un.php?action=help&id=144" width="100%" height="600px"></iframe>'
		) );
	}

	function admin_css() {
	 wp_enqueue_style( 'mp-admin-css', $this->plugin_url . 'css/marketpress.css', false, $this->version);
	}

	//enqeue js on custom post edit screen
	function admin_script_post() {
	 global $current_screen;
	 if ($current_screen->id == 'product')
		wp_enqueue_script( 'mp-post', $this->plugin_url . 'js/post-screen.js', array('jquery'), $this->version);
	}

	//enqeue css on product settings screen
	function admin_css_settings() {
	 wp_enqueue_style( 'jquery-datepicker-css', $this->plugin_url . 'datepicker/css/smoothness/jquery-ui-1.10.3.custom.min.css', false, $this->version);
	 wp_enqueue_style( 'jquery-colorpicker-css', $this->plugin_url . 'colorpicker/css/colorpicker.css', false, $this->version);
	}

	//enqeue js on product settings screen
	function admin_script_settings() {
	 wp_enqueue_script( 'jquery-colorpicker', $this->plugin_url . 'colorpicker/js/colorpicker.js', array('jquery'), $this->version);
	 wp_enqueue_script( 'jquery-ui-datepicker');//use built in version

	 //only load languages for datepicker if not english (or it will show Chinese!)
	 if ($this->language != 'en')
		wp_enqueue_script( 'jquery-datepicker-i18n', $this->plugin_url . 'datepicker/js/jquery-ui-i18n.min.js', array('jquery', 'jquery-ui-core', 'jquery-datepicker'), $this->version);

		if ( WPMUDEV_REMOVE_BRANDING === false && intval($this->get_setting('hide_popup')) < 3) {
			wp_enqueue_script( 'mp-need-help', $this->plugin_url . 'js/need-help.js', array('jquery'), $this->version);
			$new_count = intval($this->get_setting('hide_popup')) + 1;
			$this->update_setting('hide_popup', $new_count);
		}
	}

	//ties into the ajax request to disable help popup if clicked
	function hide_help() {
		$this->update_setting('hide_popup', 3);
	}
	
	/**
	 * Ensures that admin_url() uses the correct URL scheme when WordPress HTTPS
	 * plugin is enabled
	 *
	 * @since 2.9.2.4
	 *
	 * @param string $url
	 * @return string
	 */
	
	function filter_admin_url( $url ) {
		if ( class_exists('WordPressHTTPS') )
			return is_ssl() ? str_replace('http://', 'https://', $url) : str_replace('https://', 'http://', $url);
		
		return $url;
	}
	
	//ajax cart handling for store frontend
	function store_script() {
	 //setup ajax cart javascript
	 wp_enqueue_script( 'mp-ajax-js', $this->plugin_url . 'js/ajax-cart.js', array('jquery'), $this->version );

	 //get all product category links for access in js
	 $vars = array(
	 	'ajaxUrl' => admin_url('admin-ajax.php', (( is_ssl() ) ? 'https' : 'http')),
	 	'emptyCartMsg' => __('Are you sure you want to remove all items from your cart?', 'mp'),
	 	'successMsg' => __('Item(s) Added!', 'mp'),
	 	'imgUrl' => $this->plugin_url.'images/loading.gif',
	 	'addingMsg' => __('Adding to your cart...', 'mp'),
	 	'outMsg' => __('In Your Cart', 'mp'),
	 	'addToCartErrorMsg' => __('Oops... it looks like something went wrong and we couldn\'t add an item to your cart. Please check your cart for any missing items and try again.', 'mp'),
	 	'showFilters' => $this->get_setting('show_filters'),
	 	'links' => array('-1' => home_url($this->get_setting('slugs->store') . '/' . $this->get_setting('slugs->products'))),
	 	'countriesNoPostCode' => $this->countries_no_postcode,
	 );
	 
	 if ( 'product_category' == get_query_var('taxonomy') && '' != get_query_var('term') ) {
		 $cat = get_term_by('slug', get_query_var('term'), 'product_category');
		 $vars['productCategory'] = $cat->term_id;
	 } else {
		 $vars['productCategory'] = '-1';
	 }
	 
	 $terms = get_terms('product_category');
	 
	 if ( is_array($terms) ) {
	 	foreach ( $terms as $term ) {
		 	$vars['links'][$term->term_id] = get_term_link($term);
	 	}
	 }
	 
	 // declare the variables we need to access in js
	 wp_localize_script('mp-ajax-js', 'MP_Ajax', $vars);
	}

	//loads the jquery lightbox plugin
	function enqueue_lightbox() {
	 if ( !$this->get_setting('show_lightbox') )
		return;

	 wp_enqueue_script('jquery');
	 wp_enqueue_style('mp-lightbox', $this->plugin_url . 'lightbox/style/lumebox.css', false, $this->version);	//we enqueue styles on every page just in case of shortcodes http://wp.mu/8ou
	 wp_register_script('mp-lightbox', $this->plugin_url . 'lightbox/js/jquery.lumebox.min.js', array('jquery'), $this->version, true);	//we just register the script here - we can output selectively later

	 // declare the variables we need to access in js
	 $js_vars = array( 'graphicsDir' => $this->plugin_url . 'lightbox/style/' );
	 wp_localize_script('mp-lightbox', 'lumeboxOptions', $js_vars);
	}
	
	//if cart widget is not in a sidebar, add it to the top of the first sidebar. Only runs at initial install
	function add_default_widget() {
	 if (!is_active_widget(false, false, 'mp_cart_widget')) {
		$sidebars_widgets = wp_get_sidebars_widgets();
		if ( is_array($sidebars_widgets) ) {
				foreach ( $sidebars_widgets as $sidebar => $widgets ) {
					if ( 'wp_inactive_widgets' == $sidebar )
						continue;

					if ( is_array($widgets) ) {
						 array_unshift($widgets, 'mp_cart_widget-1');
						 $sidebars_widgets[$sidebar] = $widgets;
						wp_set_sidebars_widgets( $sidebars_widgets );
				$settings = array();
						$settings[1] = array( 'title' => __('Shopping Cart', 'mp'), 'custom_text' => '', 'show_thumbnail' => 1, 'size' => 25 );
						$settings['_multiwidget'] = 1;
						update_option( 'widget_mp_cart_widget', $settings );
				return true;
					}
				}
			}
	 }
	}

	//creates the store page on install and updates
	function create_store_page($old_slug = false) {
		global $wpdb;
		
	 //remove old page if updating
	 if ($old_slug && $old_slug != $this->get_setting('slugs->store')) {
		$old_post_id = $wpdb->get_var( $wpdb->prepare("SELECT ID FROM " . $wpdb->posts . " WHERE post_name = %s AND post_type = 'page'", $old_slug) );
		$old_post = get_post($old_post_id);

		$old_post->post_name = $this->get_setting('slugs->store');
		wp_update_post($old_post);
	 }

	 //insert new page if not existing
		$page_count = $wpdb->get_var( $wpdb->prepare("SELECT COUNT(*) FROM " . $wpdb->posts . " WHERE post_name = %s AND post_type = 'page'", $this->get_setting('slugs->store')) );
		if ( !$page_count ) {

			 //default page content
		$content	 = '<p>' . __('Welcome to our online store! Feel free to browse around:', 'mp') . '</p>';
		$content .= '[mp_store_navigation]';
		$content .= '<p>' . __('Check out our most popular products:', 'mp') . '</p>';
		$content .= '[mp_popular_products]';
		$content .= '<p>' . __('Browse by category:', 'mp') . '</p>';
		$content .= '[mp_list_categories]';
		$content .= '<p>' . __('Browse by tag:', 'mp') . '</p>';
		$content .= '[mp_tag_cloud]';

		$id = wp_insert_post( array('post_title' => __('Store', 'mp'), 'post_name' => $this->get_setting('slugs->store'), 'post_status' => 'publish', 'post_type' => 'page', 'post_content' => $content ) );
			update_option('mp_store_page', $id);
	 }
	}

	//MP3
	function register_custom_posts() {
		global $wp_version;
		
		// Register product categories
		register_taxonomy('product_category', 'product', apply_filters('mp_register_product_category', array(
			'hierarchical' => true,
			'label' => __('Product Categories', 'mp'),
			'singular_label' => __('Product Category', 'mp'),
			'rewrite' => array(
				'with_front' => false,
				'slug' => $this->get_setting('slugs->store') . '/' . $this->get_setting('slugs->products') . '/' . $this->get_setting('slugs->category')
			),
		)));
		
		// Register product tags
		register_taxonomy('product_tag', 'product', apply_filters('mp_register_product_tag', array(
			'hierarchical' => false,
			'label' => __('Product Tags', 'mp'),
			'singular_label' => __('Product Tag', 'mp'),
			'rewrite' => array(
				'with_front' => false,
				'slug' => $this->get_setting('slugs->store') . '/' . $this->get_setting('slugs->products') . '/' . $this->get_setting('slugs->tag')
			),
		)));
		
		//get proper icon format
		$icon = version_compare($wp_version, '3.8', '>=') ? 'dashicons-cart' : $this->plugin_url . 'images/marketpress-icon.png';
		
		// Register custom product post type
		register_post_type('product' , apply_filters('mp_register_post_type', array(
				'labels' => array(
					'name' => __('Products', 'mp'),
					'singular_name' => __('Product', 'mp'),
					'menu_name' => __('Products', 'mp'),
					'all_items' => __('Products', 'mp'),
					'add_new' => __('Create New', 'mp'),
					'add_new_item' => __('Create New Product', 'mp'),
					'edit_item' => __('Edit Product', 'mp'),
					'edit' => __('Edit', 'mp'),
					'new_item' => __('New Product', 'mp'),
					'view_item' => __('View Product', 'mp'),
					'search_items' => __('Search Products', 'mp'),
					'not_found' => __('No Products Found', 'mp'),
					'not_found_in_trash' => __('No Products found in Trash', 'mp'),
					'view' => __('View Product', 'mp')
				),
				'description' => __('Products for your e-commerce store.', 'mp'),
				'public' => true,
				'show_ui' => true,
				'publicly_queryable' => true,
				'capability_type' => 'page',
				'hierarchical' => false,
				'menu_icon' => $icon, 
				'rewrite' => array(
					'slug' => $this->get_setting('slugs->store') . '/' . $this->get_setting('slugs->products'),
					'with_front' => false
				),
				'query_var' => true,
				'supports' => array(
					'title',
					'editor',
					'author',
					'excerpt',
					'revisions',
					'thumbnail',
				),
				'taxonomies' => array(
					'product_category',
					'product_tag',
				),
		)));

		//register the orders post type
		register_post_type('mp_order', apply_filters('mp_register_post_type_mp_order', array(
			'labels' => array('name' => __('Orders', 'mp'),
				'singular_name' => __('Order', 'mp'),
				'edit' => __('Edit', 'mp'),
				'view_item' => __('View Order', 'mp'),
				'search_items' => __('Search Orders', 'mp'),
				'not_found' => __('No Orders Found', 'mp')
			),
			'description' => __('Orders from your e-commerce store.', 'mp'),
			'public' => false,
			'show_ui' => false,
			'capability_type' => apply_filters('mp_orders_capability', 'page'),
			'hierarchical' => false,
			'rewrite' => false,
			'query_var' => false,
			'supports' => array(),
		)));

		//register custom post statuses for our orders
		register_post_status( 'order_received', array(
			'label'				=> __('Received', 'mp'),
			'label_count' => array( __('Received <span class="count">(%s)</span>', 'mp'), __('Received <span class="count">(%s)</span>', 'mp') ),
			'post_type'		=> 'mp_order',
			'public'			=> false
		) );
		register_post_status( 'order_paid', array(
			'label'				=> __('Paid', 'mp'),
			'label_count' => array( __('Paid <span class="count">(%s)</span>', 'mp'), __('Paid <span class="count">(%s)</span>', 'mp') ),
			'post_type'		=> 'mp_order',
			'public'			=> false
		) );
		register_post_status( 'order_shipped', array(
			'label'				=> __('Shipped', 'mp'),
			'label_count' => array( __('Shipped <span class="count">(%s)</span>', 'mp'), __('Shipped <span class="count">(%s)</span>', 'mp') ),
			'post_type'		=> 'mp_order',
			'public'			=> false
		) );
		register_post_status( 'order_closed', array(
			'label'				=> __('Closed', 'mp'),
			'label_count' => array( __('Closed <span class="count">(%s)</span>', 'mp'), __('Closed <span class="count">(%s)</span>', 'mp') ),
			'post_type'		=> 'mp_order',
			'public'			=> false
		) );
		register_post_status( 'trash', array(
			'label'			 => _x( 'Trash', 'post' ),
			'label_count' => _n_noop( 'Trash <span class="count">(%s)</span>', 'Trash <span class="count">(%s)</span>' ),
			'show_in_admin_status_list' => true,
			'post_type'	 => 'mp_order',
			'public'			=> false
		) );
	}

	//necessary to mod array directly rather than with add_theme_support() to play nice with other themes. See http://www.wptavern.com/forum/plugins-hacks/1751-need-help-enabling-post-thumbnails-custom-post-type.html
	function post_thumbnails() {
	 global $_wp_theme_features;

	 if( !isset( $_wp_theme_features['post-thumbnails'] ) )
			$_wp_theme_features['post-thumbnails'] = array( array( 'product' ) );
	 else if ( is_array( $_wp_theme_features['post-thumbnails'] ) )
			$_wp_theme_features['post-thumbnails'][0][] = 'product';
	}

	// This function clears the rewrite rules and forces them to be regenerated
	// MP3
	function flush_rewrite_check() {
		if ( get_option('mp_flush_rewrite') ) {
			flush_rewrite_rules();
			delete_option('mp_flush_rewrite');
		}
	}

	function add_rewrite_rules($rules) {
	 $new_rules = array();

	 //product list
	 $new_rules[$this->get_setting('slugs->store') . '/' . $this->get_setting('slugs->products') . '/?$'] = 'index.php?pagename=product_list';
		$new_rules[$this->get_setting('slugs->store') . '/' . $this->get_setting('slugs->products') . '/page/?([0-9]{1,})/?$'] = 'index.php?pagename=product_list&paged=$matches[1]';

	 //checkout page
	 $new_rules[$this->get_setting('slugs->store') . '/' . $this->get_setting('slugs->cart') . '/?$'] = 'index.php?pagename=cart';
	 $new_rules[$this->get_setting('slugs->store') . '/' . $this->get_setting('slugs->cart') . '/([^/]+)/?$'] = 'index.php?pagename=cart&checkoutstep=$matches[1]';

	 //order status page
	 $new_rules[$this->get_setting('slugs->store') . '/' . $this->get_setting('slugs->orderstatus') . '/?$'] = 'index.php?pagename=orderstatus';
	 $new_rules[$this->get_setting('slugs->store') . '/' . $this->get_setting('slugs->orderstatus') . '/([^/]+)/?$'] = 'index.php?pagename=orderstatus&order_id=$matches[1]';

	 //ipn handling for payment gateways
	 $new_rules[$this->get_setting('slugs->store') . '/payment-return/(.+)'] = 'index.php?paymentgateway=$matches[1]';

		return array_merge($new_rules, $rules);
	}

	//unfortunately some plugins flush rewrites before the init hook so they kill custom post type rewrites. This function verifies they are in the final array and flushes if not
	function check_rewrite_rules($value) {

	 //prevent an infinite loop by only
	 if ( ! post_type_exists( 'product' ) )
		return $value;

		if ( is_array($value) && !in_array('index.php?product=$matches[1]&paged=$matches[2]', $value) ) {
			update_option('mp_flush_rewrite', 1);
	 } else {
			return $value;
	 }
	}

	function add_queryvars($vars) {
		// This function add the checkout queryvars to the list that WordPress is looking for.
		if(!in_array('checkoutstep', $vars))
		$vars[] = 'checkoutstep';

	 if(!in_array('order_id', $vars))
		$vars[] = 'order_id';

	 if(!in_array('paymentgateway', $vars))
		$vars[] = 'paymentgateway';

		return $vars;
	}

	/**
	 * Securely starts our session for handling cart info, etc
	 */
	function start_session() {
		$sess_id = session_id();
		
		if ( empty($sess_id) ) {
			session_start();
		}
	}

	function logout_clear_session() {
		//clear personal info
		if ( isset($_SESSION['mp_shipping_info']) ) {
			unset($_SESSION['mp_shipping_info']);
		}
		
		if ( isset($_SESSION['mp_billing_info']) ) {
			unset($_SESSION['mp_billing_info']);			
		}

		//remove coupon code
		if ( is_multisite() ) {
			global $blog_id;
			
			if ( isset($_SESSION['mp_cart_coupon_' . $blog_id]) ) {
				unset($_SESSION['mp_cart_coupon_' . $blog_id]);
			}
		} else {
			if ( isset($_SESSION['mp_cart_coupon']) ) {
				unset($_SESSION['mp_cart_coupon']);
			}
		}
	}

	//scans post type at template_redirect to apply custom themeing to products
	function load_store_templates() {
	 global $wp_query, $mp_wpmu, $mp_gateway_active_plugins;
	 
		//only filter public side
		if (is_admin()) return;

	 //load proper theme for single product page display
	 if ($wp_query->is_single && $wp_query->query_vars['post_type'] == 'product') {
		//check for custom theme templates
		$product_name = get_query_var('product');
		$product_id = (int) $wp_query->get_queried_object_id();

		//serve download if it exists
		$this->serve_download($product_id);

		$templates = array();
	 	if ( $product_name )
	 		$templates[] = "mp_product-$product_name.php";
	 	if ( $product_id )
	 		$templates[] = "mp_product-$product_id.php";
	 	$templates[] = "mp_product.php";

		//if custom template exists load it
		if ($this->product_template = locate_template($templates)) {
			add_filter( 'template_include', array(&$this, 'custom_product_template') );
		} else {
			//otherwise load the page template and use our own theme
			$wp_query->is_single = null;
			$wp_query->is_page = 1;
			add_filter( 'the_content', array(&$this, 'product_theme'), 99 );

			//genesis fixes
			remove_action( 'genesis_entry_content', 'genesis_do_post_image' );
			remove_action( 'genesis_entry_content', 'genesis_do_post_content' );
			remove_action( 'genesis_entry_header', 'genesis_post_info', 12 );
			add_action('genesis_entry_content', 'the_content');
			// ultimatum fixes Run this action before loop starts to print
			add_action('ultimatum_before_post_title','ultimatum_fixes_forMP',1);
		}

		$this->is_shop_page = true;
		wp_enqueue_script('mp-lightbox');
	 }

	 //load proper theme for main store page
		$slugs = $this->get_setting('slugs');
	 if ($wp_query->query_vars['pagename'] == $this->get_setting('slugs->store')) {

		//check for custom theme template
		$templates = array("mp_store.php");

		//if custom template exists load it
		if ($this->store_template = locate_template($templates)) {
			add_filter( 'template_include', array(&$this, 'custom_store_template') );
		} else {
			//otherwise load the page template and use our own theme
			add_filter( 'the_content', array(&$this, 'store_theme'), 99 );
		}

		$this->is_shop_page = true;
	 }

	 //load proper theme for checkout page
	 if ($wp_query->query_vars['pagename'] == 'cart') {
	 	//start the session
	 	$this->start_session();
	 	
		//process cart updates
		$this->update_cart();
		
		//check cart cookie to make applicable items haven't been removed
		$valid = false;
		$coupon_code = $this->get_coupon_code();
		
		if ( ! empty($coupon_code) ) {
			foreach ( $this->get_cart_contents() as $product_id => $product ) {
			 	if ( $this->coupon_applicable($coupon_code, $product_id) ) {
				 	$valid = true;
				 	break;
				}
			}
			
			if ( ! $valid ) {
				$this->remove_coupon();
				$this->cart_checkout_error(__('The coupon previously applied to your cart has been removed because it is no longer applicable', 'mp'));
			}
		}

			//if global cart is on forward to main site checkout
			if ( $this->global_cart && is_object($mp_wpmu) && !$mp_wpmu->is_main_site() ) {
				wp_redirect( mp_cart_link(false, true) );
				exit;
			}

			// Redirect to https if forced to use SSL by a payment gateway
			if (get_query_var('checkoutstep')) {
				foreach ((array)$mp_gateway_active_plugins as $plugin) {
					if ($plugin->force_ssl) {
						 if ( !is_ssl() ) {
							wp_redirect('https://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI']);
							exit();
						 }
					}
				}
			}

			//force login if required
			if (!is_user_logged_in() && $this->get_setting('force_login') && get_query_var('checkoutstep')) {
			wp_redirect( wp_login_url( mp_checkout_step_url( get_query_var('checkoutstep') ) ) );
				exit();
			}

			//setup shopping cart javascript
			wp_enqueue_script( 'mp-store-js', $this->plugin_url . 'js/store.js', array('jquery'), $this->version );

		//check for custom theme template
		$templates = array("mp_cart.php");

		//if custom template exists load it
		if ($this->checkout_template = locate_template($templates)) {
			add_filter( 'template_include', array(&$this, 'custom_checkout_template') );
			add_filter( 'single_post_title', array(&$this, 'page_title_output'), 99 );
				add_filter( 'bp_page_title', array(&$this, 'page_title_output'), 99 );
				add_filter( 'wp_title', array(&$this, 'wp_title_output'), 19, 3 );
		} else {
			//otherwise load the page template and use our own theme
			add_filter( 'single_post_title', array(&$this, 'page_title_output'), 99 );
			add_filter( 'the_title', array(&$this, 'page_title_output'), 99 );
				add_filter( 'bp_page_title', array(&$this, 'page_title_output'), 99 );
				add_filter( 'wp_title', array(&$this, 'wp_title_output'), 19, 3 );
			add_filter( 'the_content', array(&$this, 'checkout_theme'), 99 );
			// ultimatum fixes Run this action before loop starts to print
			add_action('ultimatum_before_post_title','ultimatum_fixes_forMP',1);			
		}

		$wp_query->is_page = 1;
		$wp_query->is_singular = 1;
		$wp_query->is_404 = null;
		$wp_query->post_count = 1;

		$this->is_shop_page = true;
	 }

	 //load proper theme for order status page
	 if ($wp_query->query_vars['pagename'] == 'orderstatus') {

		//check for custom theme template
		$templates = array("mp_orderstatus.php");

		//if custom template exists load it
		if ($this->orderstatus_template = locate_template($templates)) {
			add_filter( 'template_include', array(&$this, 'custom_orderstatus_template') );
			add_filter( 'single_post_title', array(&$this, 'page_title_output'), 99 );
			add_filter( 'bp_page_title', array(&$this, 'page_title_output'), 99 );
			add_filter( 'wp_title', array(&$this, 'wp_title_output'), 19, 3 );
		} else {
			//otherwise load the page template and use our own theme
			add_filter( 'single_post_title', array(&$this, 'page_title_output'), 99 );
			add_filter( 'the_title', array(&$this, 'page_title_output'), 99 );
			add_filter( 'bp_page_title', array(&$this, 'page_title_output'), 99 );
			add_filter( 'wp_title', array(&$this, 'wp_title_output'), 19, 3 );
			add_filter( 'the_content', array(&$this, 'orderstatus_theme'), 99 );
			// ultimatum fixes Run this action before loop starts to print
			add_action('ultimatum_before_post_title','ultimatum_fixes_forMP',1);			
		}

		$wp_query->is_page = 1;
		$wp_query->is_singular = 1;
		$wp_query->is_404 = null;
		$wp_query->post_count = 1;

		$this->is_shop_page = true;
	 }

	 //load proper theme for product listings
	 if ($wp_query->query_vars['pagename'] == 'product_list') {
		//check for custom theme template
		$templates = array("mp_productlist.php");

		//if custom template exists load it
		if ($this->product_list_template = locate_template($templates)) {

			//call a custom query posts for this listing
			//setup pagination
			if ($this->get_setting('paginate')) {
			 //figure out perpage
			 $paginate_query = '&posts_per_page='.$this->get_setting('per_page');

			 //figure out page
			 if ($wp_query->query_vars['paged'])
				$paginate_query .= '&paged='.intval($wp_query->query_vars['paged']);
			} else {
			 $paginate_query = '&nopaging=true';
			}

			//get order by
			if ($this->get_setting('order_by') == 'price')
			 $order_by_query = '&meta_key=mp_price&orderby=mp_price';
			else if ($this->get_setting('order_by') == 'sales')
			 $order_by_query = '&meta_key=mp_sales_count&orderby=mp_sales_count';
			else
			 $order_by_query = '&orderby='.$this->get_setting('order_by');

			//get order direction
			$order_query = '&order='.$this->get_setting('order');

			//The Query
			query_posts('post_type=product' . $paginate_query . $order_by_query . $order_query);

			add_filter( 'template_include', array(&$this, 'custom_product_list_template') );
			add_filter( 'single_post_title', array(&$this, 'page_title_output'), 99 );
			add_filter( 'bp_page_title', array(&$this, 'page_title_output'), 99 );
			add_filter( 'wp_title', array(&$this, 'wp_title_output'), 19, 3 );
		} else {
			//otherwise load the page template and use our own theme
			add_filter( 'single_post_title', array(&$this, 'page_title_output'), 99 );
			add_filter( 'the_title', array(&$this, 'page_title_output'), 99 );
			add_filter( 'bp_page_title', array(&$this, 'page_title_output'), 99 );
			add_filter( 'wp_title', array(&$this, 'wp_title_output'), 19, 3 );
			add_filter( 'the_content', array(&$this, 'product_list_theme'), 99 );
			add_filter( 'the_excerpt', array(&$this, 'product_list_theme'), 99 );

			//genesis fixes
			remove_action( 'genesis_entry_content', 'genesis_do_post_image' );
			remove_action( 'genesis_entry_content', 'genesis_do_post_content' );
			remove_action( 'genesis_entry_header', 'genesis_post_info', 12 );
			add_action('genesis_entry_content', 'the_content');
			// ultimatum fixes Run this action before loop starts to print
			add_action('ultimatum_before_post_title','ultimatum_fixes_forMP',1);			
		}

		$wp_query->is_page = 1;
		$wp_query->is_404 = null;
		$wp_query->post_count = 1;
		$this->is_shop_page = true;
		// ultimatum fixes Ultimatum loop checks status with if is_singular() which was missed here
		$wp_query->is_singular = 1;		
	 }
	 
	 //load proper theme for product category or tag listings
	 if ( isset( $wp_query->query_vars['taxonomy'] ) && ( $wp_query->query_vars['taxonomy'] == 'product_category' || $wp_query->query_vars['taxonomy'] == 'product_tag' ) ) {
		$templates = array();

		if ($wp_query->query_vars['taxonomy'] == 'product_category') {

			$cat_name = get_query_var('product_category');
			$cat_id = absint( $wp_query->get_queried_object_id() );
			if ( $cat_name )
				$templates[] = "mp_category-$cat_name.php";
			if ( $cat_id )
				$templates[] = "mp_category-$cat_id.php";
			$templates[] = "mp_category.php";
		} else if ($wp_query->query_vars['taxonomy'] == 'product_tag') {
			$tag_name = get_query_var('product_tag');
			$tag_id = absint( $wp_query->get_queried_object_id() );
			if ( $tag_name )
				$templates[] = "mp_tag-$tag_name.php";
			if ( $tag_id )
				$templates[] = "mp_tag-$tag_id.php";
			$templates[] = "mp_tag.php";
		}

		//defaults
		$templates[] = "mp_taxonomy.php";
		$templates[] = "mp_productlist.php";

		if ( !is_admin() && isset($_GET['product_category']) && is_numeric($_GET['product_category']) ) {
			$link = get_term_link( (int)get_query_var($wp_query->query_vars['taxonomy']), $wp_query->query_vars['taxonomy'] );
			wp_redirect($link);
			exit;
		}

		//if custom template exists load it
		if ($this->product_taxonomy_template = locate_template($templates)) {
			//call a custom query posts for this listing
			$taxonomy_query = '&' . $wp_query->query_vars['taxonomy'] . '=' . get_query_var($wp_query->query_vars['taxonomy']);

			//setup pagination
			if ($this->get_setting('paginate')) {
			 //figure out perpage
			 $paginate_query = '&posts_per_page='.$this->get_setting('per_page');

			 //figure out page
			 if ($wp_query->query_vars['paged'])
				$paginate_query .= '&paged='.intval($wp_query->query_vars['paged']);
			} else {
			 $paginate_query = '&nopaging=true';
			}

			//get order by
			if ($this->get_setting('order_by') == 'price')
			 $order_by_query = '&meta_key=mp_price&orderby=mp_price';
			else if ($this->get_setting('order_by') == 'sales')
			 $order_by_query = '&meta_key=mp_sales_count&orderby=mp_sales_count';
			else
			 $order_by_query = '&orderby='.$this->get_setting('order_by');

			//get order direction
			$order_query = '&order='.$this->get_setting('order');

			//The Query
			query_posts('post_type=product' . $taxonomy_query . $paginate_query . $order_by_query . $order_query);

			add_filter( 'template_include', array(&$this, 'custom_product_taxonomy_template'));
			add_filter( 'single_post_title', array(&$this, 'page_title_output'), 99 );
			add_filter( 'bp_page_title', array(&$this, 'page_title_output'), 99 );
			add_filter( 'wp_title', array(&$this, 'wp_title_output'), 19, 3 );
		} else {
			$wp_query->post_count = 1;
			// Ultimatum fix for making page singular so that loop decides it as singular and acts so.
			$wp_query->is_singular = true;
			
			//load theme's page.php template
			$this->product_taxonomy_template = locate_template(array('page.php', 'index.php'));
			add_filter( 'template_include', array(&$this, 'custom_product_taxonomy_template'));
			
			//otherwise load the page template and use our own list theme. We don't use theme's taxonomy as not enough control
			add_filter( 'single_post_title', array(&$this, 'page_title_output'), 99 );
			add_filter( 'bp_page_title', array(&$this, 'page_title_output'), 99 );
			add_filter( 'wp_title', array(&$this, 'wp_title_output'), 19, 3 );
			add_filter( 'the_title', array(&$this, 'page_title_output'), 99, 2 );
			
			add_filter( 'the_content', array(&$this, 'product_taxonomy_list_theme'), 99 );
			add_filter( 'the_excerpt', array(&$this, 'product_taxonomy_list_theme'), 99 );

			//genesis fixes
			remove_action( 'genesis_entry_content', 'genesis_do_post_image' );
			remove_action( 'genesis_entry_content', 'genesis_do_post_content' );
			remove_action( 'genesis_entry_header', 'genesis_post_info', 12 );
			add_action('genesis_entry_content', 'the_content');
			// ultimatum fixes Run this action before loop starts to print
			add_action('ultimatum_before_post_title','ultimatum_fixes_forMP',1);			
		}

		$this->is_shop_page = true;
	 }

	 //load shop specific items
	 if ($this->is_shop_page) {
		//fixes a nasty bug in BP theme's functions.php file which always loads the activity stream if not a normal page
		remove_all_filters('page_template');

		//prevents 404 for virtual pages
		status_header( 200 );
	 }
	}

	//loads the selected theme css files
	function load_store_theme() {
	 if ( $this->get_setting('store_theme') == 'none' || current_theme_supports('mp_style') ) {
		return;
		} else if (file_exists($this->plugin_dir . 'themes/' . $this->get_setting('store_theme') . '.css')) {
		wp_enqueue_style( 'mp-store-theme', $this->plugin_url . 'themes/' . $this->get_setting('store_theme') . '.css', false, $this->version );
		} else if (file_exists(WP_CONTENT_DIR . '/marketpress-styles/' . $this->get_setting('store_theme') . '.css')) {
		wp_enqueue_style( 'mp-store-theme', content_url( '/marketpress-styles/' . $this->get_setting('store_theme') . '.css' ), false, $this->version );
		}
	}

	//list store themes in dropdown
	function store_themes_select() {

	 //get theme dir
	 $theme_dir = $this->plugin_dir . 'themes/';

	 //scan directory for theme css files
	 $theme_list = array();
	 if ($handle = @opendir($theme_dir)) {
		while (false !== ($file = readdir($handle))) {
			if (($pos = strrpos($file, '.css')) !== false) {
			 $value = substr($file, 0, $pos);
			 if (is_readable("$theme_dir/$file")) {
				$theme_data = get_file_data( "$theme_dir/$file", array('name' => 'MarketPress Style') );
				if (is_array($theme_data))
					$theme_list[$value] = $theme_data['name'];
			 }
			}
		}

		@closedir($handle);
	 }

	 //scan wp-content/marketpress-styles/ directory for theme css files
	 $theme_dir = WP_CONTENT_DIR . '/marketpress-styles/';
	 if ($handle = @opendir($theme_dir)) {
		while (false !== ($file = readdir($handle))) {
			if (($pos = strrpos($file, '.css')) !== false) {
			 $value = substr($file, 0, $pos);
			 if (is_readable("$theme_dir/$file")) {
				$theme_data = get_file_data( "$theme_dir/$file", array('name' => 'MarketPress Style') );
				if (is_array($theme_data))
					$theme_list[$value] = $theme_data['name'];
			 }
			}
		}

		@closedir($handle);
	 }

	 //sort the themes
	 asort($theme_list);

	 //check network permissions
	 if (is_multisite()) {
		$allowed_list = array();
		$network_settings = get_site_option( 'mp_network_settings' );

		foreach ($theme_list as $value => $name) {
			if ($network_settings['allowed_themes'][$value] == 'full')
			 $allowed_list[$value] = $name;
			else if ($network_settings['allowed_themes'][$value] == 'supporter' && function_exists('is_pro_site') && is_pro_site(false, $network_settings['themes_pro_level'][$value]))
			 $allowed_list[$value] = $name;
			else if (is_super_admin()) //super admins can access all installed themes
			 $allowed_list[$value] = $name;
		}
		$theme_list = $allowed_list;
	 }

	 echo '<select name="mp[store_theme]">';
	 foreach ($theme_list as $value => $name) {
		$disabled = (MP_LITE === true && $value != 'icons') ? ' disabled="disabled"' : '';
		?><option value="<?php echo $value ?>"<?php selected($this->get_setting('store_theme'), $value); echo $disabled; ?>><?php echo $name ?></option><?php
	}
	 ?>
		<option value="none"<?php selected($this->get_setting('store_theme'), 'none') ?>><?php _e('None - Custom theme template', 'mp') ?></option>
	 </select>
	 <?php
	}

	//filter the custom single product template
	function custom_product_template($template) {
	 return $this->product_template;
	}

	//filter the custom store template
	function custom_store_template($template) {
	 return $this->store_template;
	}

	//filter the custom checkout template
	function custom_checkout_template($template) {
	 return $this->checkout_template;
	}

	//filter the custom orderstatus template
	function custom_orderstatus_template($template) {
	 return $this->orderstatus_template;
	}

	//filter the custom product taxonomy template
	function custom_product_taxonomy_template($template) {
	 return $this->product_taxonomy_template;
	}

	//filter the custom product list template
	function custom_product_list_template($template) {
	 return $this->product_list_template;
	}

	//adds our links to theme nav menus using wp_list_pages()
	function filter_list_pages($list, $args) {

	 if ($args['depth'] == 1)
		return $list;

	 $temp_break = strpos($list, mp_store_link(false, true) . '"');

	 //if we can't find the page for some reason skip
	 if ($temp_break === false)
		return $list;

	 $break = strpos($list, '</a>', $temp_break) + 4;

	 $nav = substr($list, 0, $break);

	 if ( !$this->get_setting('disable_cart') ) {
		$nav .= '<ul class="children"><li class="page_item'. ((get_query_var('pagename') == 'product_list') ? ' current_page_item' : '') . '"><a href="' . mp_products_link(false, true) . '" title="' . __('Products', 'mp') . '">' . __('Products', 'mp') . '</a></li>';
			$nav .= '<li class="page_item'. ((get_query_var('pagename') == 'cart') ? ' current_page_item' : '') . '"><a href="' . mp_cart_link(false, true) . '" title="' . __('Shopping Cart', 'mp') . '">' . __('Shopping Cart', 'mp') . '</a></li>';
		$nav .= '<li class="page_item'. ((get_query_var('pagename') == 'orderstatus') ? ' current_page_item' : '') . '"><a href="' . mp_orderstatus_link(false, true) . '" title="' . __('Order Status', 'mp') . '">' . __('Order Status', 'mp') . '</a></li>
</ul>
';
	 } else {
		$nav .= '
<ul>
	<li class="page_item'. ((get_query_var('pagename') == 'product_list') ? ' current_page_item' : '') . '"><a href="' . mp_products_link(false, true) . '" title="' . __('Products', 'mp') . '">' . __('Products', 'mp') . '</a></li>
</ul>
';
	 }
	 $nav .= substr($list, $break);

	 return $nav;
	}

	function wp_title_output( $title = '', $sep = '', $seplocation = 'left' ) {
	 // Determines position of the separator and direction of the breadcrumb
		if ( 'right' == $seplocation )
			return $this->page_title_output($title, true) . " $sep ";
		else
			return " $sep " . $this->page_title_output($title, true);
	}

	//filters the titles for our custom pages
	function page_title_output($title, $id = false) {
	 global $wp_query;

	 //filter out nav titles
	 if (!empty($title) && $id === false)
		return $title;

	 //taxonomy pages
	 if (isset($wp_query->query_vars['taxonomy']) && ($wp_query->query_vars['taxonomy'] == 'product_category' || $wp_query->query_vars['taxonomy'] == 'product_tag') && $wp_query->post->ID == $id) {
		if ($wp_query->query_vars['taxonomy'] == 'product_category') {
			$term = get_term_by('slug', get_query_var('product_category'), 'product_category');
			$title = sprintf( __('Product Category: %s', 'mp'), $term->name );
		} else if ($wp_query->query_vars['taxonomy'] == 'product_tag') {
			$term = get_term_by('slug', get_query_var('product_tag'), 'product_tag');
			$title = sprintf( __('Product Tag: %s', 'mp'), $term->name );
		}
		
		return ( $id !== true ) ? '<span class="mp-page-title">' . $title . '</span>' : $title;
	 }

	 switch ($wp_query->query_vars['pagename']) {
		case 'cart':
				if ( isset($wp_query->query_vars['checkoutstep']) ) {
					if ($wp_query->query_vars['checkoutstep'] == 'shipping')
						$title = $this->download_only_cart($this->get_cart_contents()) ? __('Checkout Information', 'mp') : __('Shipping Information', 'mp');
					else if ($wp_query->query_vars['checkoutstep'] == 'checkout')
						$title = __('Payment Information', 'mp');
					else if ($wp_query->query_vars['checkoutstep'] == 'confirm-checkout')
						$title = __('Confirm Your Purchase', 'mp');
					else if ($wp_query->query_vars['checkoutstep'] == 'confirmation')
						$title = __('Order Confirmation', 'mp');
					else
						$title = __('Your Shopping Cart', 'mp');
				} else {
					$title = __('Your Shopping Cart', 'mp');
				}
			break;

		case 'orderstatus':
			$title = __('Track Your Order', 'mp');
			break;

		case 'product_list':
			$title = __('Products', 'mp');
			break;

		default:
			$title = $title;
	 }
	 
	 return $title; 
	}

	//this is the default theme added to single product listings
	function product_theme($content) {
	 global $post;

	 //don't filter outside of the loop
		if ( ! in_the_loop() )
			 return $content;

	 //add thumbnail
		if ($this->get_setting('show_img')) {
			$content = mp_product_image( false, 'single' ) . $content;
		}

	 $content .= '<div class="mp_product_meta">';
	 $content .= mp_product_price(false);
	 $content .= mp_buy_button(false, 'single');
	 $content .= '<span style="display:none" class="date updated">' .	get_the_time('Y-m-d\TG:i') . '</span>';
	 $content .= '<span style="display:none" class="vcard author"><span class="fn">' . get_the_author_meta('display_name') . '</span></span>';	 
	 $content .= '</div>';

		$content .= mp_category_list($post->ID, '<div class="mp_product_categories">' . __( 'Categorized in ', 'mp' ), ', ', '</div>');
		$content .= mp_pinit_button($post->ID);

		$content .= mp_related_products( $post->ID );


	 return $content;
	}

	//this is the default theme added to the checkout page
	function store_theme($content) {
	 //don't filter outside of the loop
		if ( ! in_the_loop() )
			 return $content;

	 return $content;
	}

	//this is the default theme added to the checkout page
	function checkout_theme($content) {
	 global $wp_query;

		//don't filter outside of the loop
		if ( ! in_the_loop() )
			 return $content;

	 $content = mp_show_cart('checkout', null, false);

	 return $content;
	}

	//this is the default theme added to the order status page
	function orderstatus_theme($content) {
	 //don't filter outside of the loop
		if ( ! in_the_loop() )
			 return $content;

	 mp_order_status();
	 return $content;
	}

	//this is the default theme added to product listings
	function product_list_theme($content) {
		//don't filter outside of the loop
		if ( ! in_the_loop() )
			 return $content;

		$msgs = $this->get_setting('msg');
		$content .= do_shortcode($msgs['product_list']);
		$content .= mp_list_products(array('echo' => false));

		return $content;
	}

	//this is the default theme added to product taxonomies
	function product_taxonomy_list_theme( $content ) {
		if ( ! is_main_query() || ! in_the_loop() ) {
			return $content;
		}
		
		//don't filter outside of the loop
		$msgs = $this->get_setting('msg');
		$content = do_shortcode($msgs['product_list']);
		$content .= mp_list_products(array('echo' => false));
		
		echo $content;
	}

	/**
	* ajax handler
	* @return string html of products list, and optionally pagination
	*/
	function get_products_list(){
		global $wp_query;

		$ret = array('products'=>false, 'pagination'=>false);
		$args = wp_parse_args(array(
			'echo' => false,
			'filters' => false,
		), $this->defaults['list_products']);

		if ( isset($_POST['order']) ) {
			$o = explode('-',$_POST['order']);

			// column
			if(isset($o[0]) && in_array($o[0], array('date','title','price','sales'))) {
				$args['order_by'] = $o[0];
			}

			// direction
			if(isset($o[1]) && in_array($o[1], array('asc','desc'))) {
				$args['order'] = strtoupper($o[1]);
			}
		}
		
		if ( isset($_POST['per_page']) ) {
			$args['per_page'] = intval($_POST['per_page']);
		}

		if ( isset($_POST['product_category']) && is_numeric($_POST['product_category']) ) {
			$term = get_term_by( 'id', $_POST['product_category'], 'product_category' );
			$args['category'] = $term->slug;
		}

		if ( isset($_POST['page']) && is_numeric($_POST['page']) ) {
			$args['page'] = $_POST['page'];
		}
		
		$ret['products'] = mp_list_products($args);
		
		wp_send_json($ret);
	}

	//adds the "filter by product category" to the edit products screen
	function edit_products_filter() {
	 global $current_screen;

	 if ( $current_screen->id == 'edit-product' ) {
	 	$selected_category = !empty( $_GET['product_category'] ) ? $_GET['product_category'] : null;
	 	$dropdown_options = array('taxonomy' => 'product_category', 'show_option_all' => __('View all categories'), 'hide_empty' => 0, 'hierarchical' => 1,
	 		'show_count' => 0, 'orderby' => 'name', 'name' => 'product_category', 'selected' => $selected_category );
	 	wp_dropdown_categories($dropdown_options);
	 }
	}

	//adjusts the query vars on the products/order management screens.
	function handle_edit_screen_filter($request) {
		if ( is_admin() ) {
			global $current_screen;
	
			if ( $current_screen->id == 'edit-product' ) {
				//Switches the product_category ids to slugs as you can't query custom taxonomys with ids
				if ( !empty( $request['product_category'] ) ) {
					$cat = get_term_by('id', $request['product_category'], 'product_category');
					$request['product_category'] = $cat->slug;
				}
			} else if ( $current_screen->id == 'product_page_marketpress-orders' && !isset($_GET['post_status']) ) {
				//set the post status when on "All" to everything but closed
				$request['post_status'] = 'order_received,order_paid,order_shipped';
			}
		}
	
		return $request;
	}

	//adds our custom column headers to edit products screen
	function edit_products_columns($old_columns)	{
	 global $post_status;

		$columns['cb'] = '<input type="checkbox" />';
		$columns['thumbnail'] = __('Thumbnail', 'mp');
		$columns['title'] = __('Product Name', 'mp');
		$columns['variations'] = __('Variations', 'mp');
		$columns['sku'] = __('SKU', 'mp');
		$columns['pricing'] = __('Price', 'mp');
		if ( !$this->get_setting('disable_cart') ) {
			$columns['stock'] = __('Stock', 'mp');
			$columns['sales'] = __('Sales', 'mp');
	 }
		$columns['product_categories'] = __('Product Categories', 'mp');
		$columns['product_tags'] = __('Product Tags', 'mp');


	 /*
	 if ( !in_array( $post_status, array('pending', 'draft', 'future') ) )
			 $columns['reviews'] = __('Reviews', 'mp');
	 //*/

		return $columns;
	}

	//adds our custom column content
	function edit_products_custom_columns($column) {
		global $post;
		
		//$screen = get_current_screen();
		//echo "screen->id=[". $screen->id ."]<br />";
		//apply_filters( 'bulk_actions-' . $screen->id, $this->_actions );
		
		$meta = get_post_custom();
	 //unserialize
	 foreach ($meta as $key => $val) {
			 $meta[$key] = maybe_unserialize($val[0]);
			 if (!is_array($meta[$key]) && $key != "mp_is_sale" && $key != "mp_track_inventory" && $key != "mp_product_link")
				$meta[$key] = array($meta[$key]);
		}

		switch ($column) {
			case "thumbnail":
				echo '<a href="' . get_edit_post_link() . '" title="' . __('Edit &raquo;') . '">';
			if (has_post_thumbnail()) {
					the_post_thumbnail(array(50,50), array('title' => ''));
				} else {
					echo '<img width="50" height="50" src="'.apply_filters('mp_default_product_img', $this->plugin_url.'images/default-product.png').'">';
				}
				echo '</a>';
				break;

			case "variations":
				 if (isset($meta["mp_var_name"]) && is_array($meta["mp_var_name"]) && count($meta["mp_var_name"]) > 1) {
					foreach ($meta["mp_var_name"] as $value) {
				echo esc_attr($value) . '<br />';
					}
				} else {
					_e('N/A', 'mp');
				}
				 break;

		case "sku":
				 if (isset($meta["mp_var_name"]) && is_array($meta["mp_var_name"])) {
					foreach ((array)$meta["mp_sku"] as $value) {
					echo esc_attr($value) . '<br />';
					}
			} else {
					_e('N/A', 'mp');
				}
				break;

		case "pricing":
			if (isset($meta["mp_price"]) && is_array($meta["mp_price"])) {
				foreach ($meta["mp_price"] as $key => $value) {
						if (isset($meta["mp_is_sale"]) && $meta["mp_is_sale"] && isset($meta["mp_sale_price"][$key])) {
						echo '<del>'.$this->format_currency('', $value).'</del> ';
						echo $this->format_currency('', $meta["mp_sale_price"][$key]) . '<br />';
					 } else {
						echo $this->format_currency('', $value) . '<br />';
					 }
				}
			} else {
					echo $this->format_currency('', 0);
				}
				break;

		case "sales":
				echo number_format_i18n(isset($meta["mp_sales_count"][0]) ? $meta["mp_sales_count"][0] : 0);
				break;

		case "stock":
				if (isset($meta["mp_track_inventory"]) && $meta["mp_track_inventory"]) {
					foreach ((array)$meta["mp_inventory"] as $value) {
					$inventory = ($value) ? $value : 0;
					if ($inventory == 0)
					 $class = 'mp-inv-out';
					else if ($inventory <= $this->get_setting('inventory_threshhold'))
					 $class = 'mp-inv-warn';
					else
					 $class = 'mp-inv-full';

					echo '<span class="' . $class . '">' . number_format_i18n($inventory) . '</span><br />';
			 }
			} else {
			 _e('N/A', 'mp');
			}
				break;

			case "product_categories":
			echo mp_category_list();
				break;

		case "product_tags":
			echo mp_tag_list();
				break;

		case "reviews":
			echo '<div class="post-com-count-wrapper">
						<a href="edit-comments.php?p=913" title="0 pending" class="post-com-count"><span class="comment-count">0</span></a>
					</div>';
			break;
		}
	}

	// Adds a custom row action for Copying/Cloning a Product
	function edit_products_custom_row_actions($actions, $post) {
		$action = 'copy-product';

		if ( ($post->post_type == "product") && (!isset($actions[$action])) ) {
			
			$post_type_object = get_post_type_object( $post->post_type );
			if ( $post_type_object ) {
				if ( current_user_can('edit_pages') ) {
					$copy_link = add_query_arg( 'action', $action );
					$copy_link = add_query_arg( 'post', $post->ID, $copy_link );
					$copy_link = wp_nonce_url( $copy_link, "{$action}-{$post->post_type}_{$post->ID}" );
					$actions[$action] = '<a href="'. $copy_link .'">'. __('Copy', 'mp') .'</a>';
				}
			}
		}
		return $actions;
	}

	function edit_products_copy_action() {
		
		$action = 'copy-product';
		if ((isset($_GET['action'])) && ($_GET['action'] == "copy-product")) {

			$sendback_href = remove_query_arg( array('_wpnonce', 'mp-action', 'post', 'trashed', 'untrashed', 'deleted', 'ids'), wp_get_referer() );

			if (isset($_GET['post']))
				$product_id = intval($_GET['post']);
			else
				wp_redirect($sendback_href);

			if (isset($_GET['post_type']))
				$post_type = esc_attr($_GET['post_type']);
			else
				wp_redirect($sendback_href);

			if ( (!isset($_GET['_wpnonce'])) || !wp_verify_nonce($_GET['_wpnonce'], "{$action}-{$post_type}_{$product_id}") )
				wp_redirect($sendback_href);
							
			$product = (array)get_post_to_edit( $product_id );
			$product['ID'] = 0;	// Zero out the Product ID to force insert of new item
			$product['post_status'] = 'draft';
			$product['post_author'] = get_current_user_id();
				
			$new_product_id = wp_insert_post($product);
			if (($new_product_id) && (!is_wp_error($$new_product_id))) {

				//If we have the a valid new product ID we copy the product meta...
				$product_meta_keys = get_post_custom_keys($product_id);
				if (!empty($product_meta_keys)) {
					foreach ($product_meta_keys as $meta_key) {
						$meta_values = get_post_custom_values($meta_key, $product_id);

						foreach ($meta_values as $meta_value) {
							$meta_value = maybe_unserialize($meta_value);
							add_post_meta($new_product_id, $meta_key, $meta_value);
						}
					}
				}
				
				// ... thne we copy the product taxonomy terms
				$product_taxonomies = get_object_taxonomies($post_type);
				if (!empty($product_taxonomies)) {
					foreach ($product_taxonomies as $product_taxonomy) {
						$product_terms = wp_get_object_terms($product_id, $product_taxonomy, array( 'orderby' => 'term_order' ));
						if (($product_terms) && (count($product_terms))) {
							$terms = array();
							foreach($product_terms as $product_term)
								$terms[] = $product_term->slug;
						}
						wp_set_object_terms($new_product_id, $terms, $product_taxonomy);
					}
				}
			}
		}
		wp_redirect($sendback_href);
		die();		
	}

	//adds our custom column headers
	function manage_orders_columns($old_columns)	{
	 global $post_status;

		$columns['cb'] = '<input type="checkbox" />';

		$columns['mp_orders_status'] = __('Status', 'mp');
		$columns['mp_orders_id'] = __('Order ID', 'mp');
		$columns['mp_orders_date'] = __('Order Date', 'mp');
		$columns['mp_orders_name'] = __('From', 'mp');
		$columns['mp_orders_items'] = __('Items', 'mp');
		$columns['mp_orders_shipping'] = __('Shipping', 'mp');
		$columns['mp_orders_tax'] = __('Tax', 'mp');
		$columns['mp_orders_discount'] = __('Discount', 'mp');
		$columns['mp_orders_total'] = __('Total', 'mp');

		return $columns;
	}

	//adds our custom column content
	function manage_orders_custom_columns($column) {
		global $post;
		$meta = get_post_custom();
	 //unserialize
	 foreach ($meta as $key => $val)
			 $meta[$key] = array_map('maybe_unserialize', $val);

		switch ($column) {

		case "mp_orders_status":
				if ($post->post_status == 'order_received')
			 $text = __('Received', 'mp');
			else if ($post->post_status == 'order_paid')
			 $text = __('Paid', 'mp');
			else if ($post->post_status == 'order_shipped')
			 $text = __('Shipped', 'mp');
			else if ($post->post_status == 'order_closed')
			 $text = __('Closed', 'mp');
			else if ($post->post_status == 'trash')
			 $text = __('Trashed', 'mp');

			?><a class="mp_order_status" href="edit.php?post_type=product&page=marketpress-orders&order_id=<?php echo $post->ID; ?>" title="<?php echo __('View Order Details', 'mp'); ?>"><?php echo $text ?></a><?php
				break;

		case "mp_orders_date":
			$t_time = get_the_time(__('Y/m/d g:i:s A'));
				$m_time = $post->post_date;
				$time = get_post_time('G', true, $post);

				$time_diff = time() - $time;

				if ( $time_diff > 0 && $time_diff < 24*60*60 )
					$h_time = sprintf( __('%s ago'), human_time_diff( $time ) );
				else
					$h_time = mysql2date(__('Y/m/d'), $m_time);
			echo '<abbr title="' . $t_time . '">' . $h_time . '</abbr>';
				break;

		case "mp_orders_id":
			$title = _draft_or_post_title();
			?>
			<strong><a class="row-title" href="edit.php?post_type=product&page=marketpress-orders&order_id=<?php echo $post->ID; ?>" title="<?php echo esc_attr(sprintf(__('View &#8220;%s&#8221;', 'mp'), $title)); ?>"><?php echo $title ?></a></strong>
			<?php
			$actions = array();
			if ($post->post_status == 'order_received') {
			 $actions['paid'] = "<a title='" . esc_attr(__('Mark as Paid', 'mp')) . "' href='" . wp_nonce_url( admin_url( 'edit.php?post_type=product&amp;page=marketpress-orders&amp;action=paid&amp;post=' . $post->ID), 'update-order-status' ) . "'>" . __('Paid', 'mp') . "</a>";
			 $actions['shipped'] = "<a title='" . esc_attr(__('Mark as Shipped', 'mp')) . "' href='" . wp_nonce_url( admin_url( 'edit.php?post_type=product&amp;page=marketpress-orders&amp;action=shipped&amp;post=' . $post->ID), 'update-order-status' ) . "'>" . __('Shipped', 'mp') . "</a>";
			 $actions['closed'] = "<a title='" . esc_attr(__('Mark as Closed', 'mp')) . "' href='" . wp_nonce_url( admin_url( 'edit.php?post_type=product&amp;page=marketpress-orders&amp;action=closed&amp;post=' . $post->ID), 'update-order-status' ) . "'>" . __('Closed', 'mp') . "</a>";
			} else if ($post->post_status == 'order_paid') {
			 $actions['shipped'] = "<a title='" . esc_attr(__('Mark as Shipped', 'mp')) . "' href='" . wp_nonce_url( admin_url( 'edit.php?post_type=product&amp;page=marketpress-orders&amp;action=shipped&amp;post=' . $post->ID), 'update-order-status' ) . "'>" . __('Shipped', 'mp') . "</a>";
			 $actions['closed'] = "<a title='" . esc_attr(__('Mark as Closed', 'mp')) . "' href='" . wp_nonce_url( admin_url( 'edit.php?post_type=product&amp;page=marketpress-orders&amp;action=closed&amp;post=' . $post->ID), 'update-order-status' ) . "'>" . __('Closed', 'mp') . "</a>";
			} else if ($post->post_status == 'order_shipped') {
			 $actions['closed'] = "<a title='" . esc_attr(__('Mark as Closed', 'mp')) . "' href='" . wp_nonce_url( admin_url( 'edit.php?post_type=product&amp;page=marketpress-orders&amp;action=closed&amp;post=' . $post->ID), 'update-order-status' ) . "'>" . __('Closed', 'mp') . "</a>";
			} else if ($post->post_status == 'order_closed') {
			 $actions['received'] = "<a title='" . esc_attr(__('Mark as Received', 'mp')) . "' href='" . wp_nonce_url( admin_url( 'edit.php?post_type=product&amp;page=marketpress-orders&amp;action=received&amp;post=' . $post->ID), 'update-order-status' ) . "'>" . __('Received', 'mp') . "</a>";
			 $actions['paid'] = "<a title='" . esc_attr(__('Mark as Paid', 'mp')) . "' href='" . wp_nonce_url( admin_url( 'edit.php?post_type=product&amp;page=marketpress-orders&amp;action=paid&amp;post=' . $post->ID), 'update-order-status' ) . "'>" . __('Paid', 'mp') . "</a>";
			 $actions['shipped'] = "<a title='" . esc_attr(__('Mark as Shipped', 'mp')) . "' href='" . wp_nonce_url( admin_url( 'edit.php?post_type=product&amp;page=marketpress-orders&amp;action=shipped&amp;post=' . $post->ID), 'update-order-status' ) . "'>" . __('Shipped', 'mp') . "</a>";
			}
		
				if ((isset($_GET['post_status'])) && ($_GET['post_status'] == "trash")) {
							$actions['delete'] = "<a title='" . esc_attr(__('Delete', 'mp')) . "' href='" . wp_nonce_url( admin_url( 'edit.php?post_type=product&amp;page=marketpress-orders&amp;action=delete&amp;post=' . $post->ID), 'update-order-status' ) . "'>" . __('Delete Permanently', 'mp') . "</a>";
				} else	{
							$actions['trash'] = "<a title='" . esc_attr(__('Trash', 'mp')) . "' href='" . wp_nonce_url( admin_url( 'edit.php?post_type=product&amp;page=marketpress-orders&amp;action=trash&amp;post=' . $post->ID), 'update-order-status' ) . "'>" . __('Trash', 'mp') . "</a>";
				}
		
			$action_count = count($actions);
				$i = 0;
				echo '<div class="row-actions">';
				foreach ( $actions as $action => $link ) {
					++$i;
					( $i == $action_count ) ? $sep = '' : $sep = ' | ';
					echo "<span class='$action'>$link$sep</span>";
				}
				echo '</div>';
			break;

		case "mp_orders_name":
				echo esc_attr($meta["mp_shipping_info"][0]['name']) . ' (<a href="mailto:' . urlencode($meta["mp_shipping_info"][0]['name']) . ' &lt;' . esc_attr($meta["mp_shipping_info"][0]['email']) . '&gt;?subject=' . urlencode(sprintf(__('Regarding Your Order (%s)', 'mp'), $post->post_title)) . '">' . esc_attr($meta["mp_shipping_info"][0]['email']) . '</a>)';
				break;

		case "mp_orders_items":
				echo number_format_i18n($meta["mp_order_items"][0]);
				break;

		case "mp_orders_shipping":
				echo $this->format_currency('', $meta["mp_shipping_total"][0]);
				break;

		case "mp_orders_tax":
				echo $this->format_currency('', $meta["mp_tax_total"][0]);
				break;

		case "mp_orders_discount":
			if (isset($meta["mp_discount_info"][0]) && $meta["mp_discount_info"][0]) {
				echo $meta["mp_discount_info"][0]['discount'] . ' (' . strtoupper($meta["mp_discount_info"][0]['code']) . ')';
			} else {
				_e('N/A', 'mp');
			}
				break;

		case "mp_orders_total":
				echo $this->format_currency('', $meta["mp_order_total"][0]);
				break;

		}
	}

	//filters label in new product title field
	function filter_title($post) {
		global $post_type;

		if ($post_type != 'product')
			return $post;

		return __( 'Enter Product title here', 'mp' );
	}

	//adds our custom meta boxes the the product edit screen
	function meta_boxes() {
		global $wp_meta_boxes;

	 add_meta_box('mp-meta-details', __('Product Details', 'mp'), array(&$this, 'meta_details'), 'product', 'normal', 'high');

	 //only add these boxes if orders are enabled
	 if (!$this->get_setting('disable_cart')) {

		//only display metabox if shipping plugin ties into it
		if ( has_action('mp_shipping_metabox') && 'none' != $this->get_setting('shipping->method') )
			add_meta_box('mp-meta-shipping', __('Shipping', 'mp'), array(&$this, 'meta_shipping'), 'product', 'normal', 'high');

			//for product downloads
		add_meta_box('mp-meta-download', __('Product Download', 'mp'), array(&$this, 'meta_download'), 'product', 'normal', 'high');
	 }

		//all this junk is to reorder the metabox array to move the featured image box to the top right below submit box. User order will override
		if ( isset( $wp_meta_boxes['product']['side']['low']['postimagediv'] ) ) {
			$imagediv = $wp_meta_boxes['product']['side']['low']['postimagediv'];
			unset( $wp_meta_boxes['product']['side']['low']['postimagediv'] );
			$submitdiv = $wp_meta_boxes['product']['side']['core']['submitdiv'];
			unset( $wp_meta_boxes['product']['side']['core']['submitdiv'] );
			$new_core['submitdiv'] = $submitdiv;
			$new_core['postimagediv'] = $imagediv;
			$wp_meta_boxes['product']['side']['core'] = array_merge( $new_core, $wp_meta_boxes['product']['side']['core']) ;
			//filter title
			$wp_meta_boxes['product']['side']['core']['postimagediv']['title'] = __('Product Image', 'mp');
		}
	}

	//Save our post meta when a product is created or updated
	function save_product_meta($post_id, $post = null) {
	 //skip quick edit
	 if ( defined('DOING_AJAX') )
		return;

		if ( $post->post_type == "product" && isset( $_POST['mp_product_meta'] ) ) {
		$meta = get_post_custom($post_id);
		foreach ($meta as $key => $val) {
				 $meta[$key] = maybe_unserialize($val[0]);
				 if (!is_array($meta[$key]) && $key != "mp_is_sale" && $key != "mp_track_inventory" && $key != "mp_product_link")
				 $meta[$key] = array($meta[$key]);
			}

		//price function
		$func_curr = '$price = round(preg_replace("/[^0-9.]/", "", $price), 2);return ($price) ? $price : 0;';

		//sku function
		$func_sku = 'return preg_replace("/[^a-zA-Z0-9_-]/", "", $value);';

		update_post_meta($post_id, 'mp_var_name', $_POST['mp_var_name']);
		update_post_meta($post_id, 'mp_sku', array_map(create_function('$value', $func_sku), (array)$_POST['mp_sku']));
		update_post_meta($post_id, 'mp_price', array_map(create_function('$price', $func_curr), (array)$_POST['mp_price']));
		update_post_meta($post_id, 'mp_is_sale', isset($_POST['mp_is_sale']) ? 1 : 0);
		update_post_meta($post_id, 'mp_sale_price', array_map(create_function('$price', $func_curr), (array)$_POST['mp_sale_price']));
		update_post_meta($post_id, 'mp_track_inventory', isset($_POST['mp_track_inventory']) ? 1 : 0);
		update_post_meta($post_id, 'mp_inventory', array_map('intval', (array)$_POST['mp_inventory']));
		//track limit
		update_post_meta($post_id, 'mp_track_limit', isset($_POST['mp_track_limit']) ? 1 : 0);
		if( isset($_POST['mp_track_limit']) ){
			 //fill the array with the value of mp_limit
			 update_post_meta($post_id, 'mp_limit', array_fill(0, count((array)$_POST['mp_price']) , isset( $_POST['mp_limit'] ) ? $_POST['mp_limit'] : 1	) );
		}else{
			 //empty the array
			 update_post_meta($post_id, 'mp_limit',array());
		}

			//personalization fields
			$mp_has_custom_field = $mp_custom_field_required = array();
			foreach ((array)$_POST['mp_price'] as $key => $null) {
				$mp_has_custom_field[$key] = isset($_POST['mp_has_custom_field'][$key]) ? 1 : 0;
				$mp_custom_field_required[$key] = isset($_POST['mp_custom_field_required'][$key]) ? 1 : 0;
			}
			update_post_meta($post_id, 'mp_has_custom_field', $mp_has_custom_field);
			update_post_meta($post_id, 'mp_custom_field_required', $mp_custom_field_required);
			
			if (isset($_POST['mp_custom_field_per'])) {
				update_post_meta($post_id, 'mp_custom_field_per', (array)$_POST['mp_custom_field_per']);
			}

			if (isset($_POST['mp_custom_field_label'])) {
				update_post_meta($post_id, 'mp_custom_field_label', array_map('trim', (array)$_POST['mp_custom_field_label']));
			}


			//save true first variation price for sorting
			if ( isset($_POST['mp_is_sale']) )
				$sort_price = round($_POST['mp_sale_price'][0], 2);
			else
				$sort_price = round($_POST['mp_price'][0], 2);
		update_post_meta($post_id, 'mp_price_sort', $sort_price);

			//if changing delete flag so emails will be sent again
		if ( $_POST['mp_inventory'] != $meta['mp_inventory'] )
			delete_post_meta($post_id, 'mp_stock_email_sent');

		update_post_meta( $post_id, 'mp_product_link', esc_url_raw($_POST['mp_product_link']) );

			update_post_meta($post_id, 'mp_is_special_tax', isset($_POST['mp_is_special_tax']) ? 1 : 0);
			$tax_rate = round(preg_replace("/[^0-9.]/", "", $_POST['mp_special_tax']), 3) * .01;
			update_post_meta($post_id, 'mp_special_tax', $tax_rate);

		//set sales count to zero if none set
		$sale_count = ($meta["mp_sales_count"][0]) ? $meta["mp_sales_count"][0] : 0;
		update_post_meta($post_id, 'mp_sales_count', $sale_count);

		//for shipping plugins to save their meta values
		$mp_shipping = maybe_unserialize($meta["mp_shipping"][0]);
		if ( !is_array($mp_shipping) )
			$mp_shipping = array();

		update_post_meta( $post_id, 'mp_shipping', apply_filters('mp_save_shipping_meta', $mp_shipping) );

		//download url
		update_post_meta( $post_id, 'mp_file', esc_url_raw($_POST['mp_file']) );

		//for any other plugin to hook into
		do_action( 'mp_save_product_meta', $post_id, $meta );
		}
	}

	// Make sure that product meta keys are present, set to sensible defaults
	function get_meta_details( $post_id ) {
		$meta = get_post_custom($post_id);
		//unserialize
	 foreach ($meta as $key => $val) {
			 $meta[$key] = maybe_unserialize($val[0]);
			 if (!is_array($meta[$key]) && $key != "mp_is_sale" && $key != "mp_track_inventory" && $key != "mp_product_link" && $key != "mp_file" && $key != "mp_is_special_tax" && $key != "mp_special_tax" && $key != 'mp_track_limit')
				$meta[$key] = array($meta[$key]);
		}
		
		$defaults = array(
			'mp_is_sale' => '',
			'mp_track_inventory' => '',
			'mp_price' => array(),
			'mp_product_link' => '',
			'mp_is_special_tax' => '',
			'mp_file' => '',
			'mp_shipping' => array(''),
			'mp_track_limit' => '',
			'mp_limit' => array(),
		);
	
		//Set default value if key is not already set.
		return wp_parse_args($meta, $defaults);
	}

	//The Product Details meta box
	function meta_details() {
	 global $post;
		$meta = $this->get_meta_details( $post->ID );
	 ?>
	 <?php if ( MP_LITE === true ) { ?>
	 <a class="mp-pro-update" href="http://premium.wpmudev.org/project/e-commerce/" title="<?php _e('Upgrade Now', 'mp'); ?> &raquo;"><?php _e('Upgrade to enable item personalization &raquo;', 'mp'); ?></a><br />
	 <?php } ?>
	 <input type="hidden" name="mp_product_meta" value="1" />
	 <table class="widefat" id="mp_product_variations_table">
			<thead>
				<tr>
					<th scope="col" class="mp_var_col"><?php _e('Variation Name', 'mp') ?></th>
					<th scope="col" class="mp_sku_col" title="<?php _e('Stock Keeping Unit - Your custom Product ID number', 'mp'); ?>"><?php _e('SKU', 'mp') ?></th>
					<th scope="col" class="mp_price_col"><?php _e('Price', 'mp') ?></th>
					<th scope="col" class="mp_sale_col"><label title="<?php _e('When checked these override the normal price.', 'mp'); ?>"><input type="checkbox" id="mp_is_sale" name="mp_is_sale" value="1"<?php checked($meta["mp_is_sale"], '1'); ?> /> <?php _e('Sale Price', 'mp') ?></label></th>
					<th scope="col" class="mp_inv_col"><label title="<?php _e('When checked inventory tracking will be enabled.', 'mp'); ?>"><input type="checkbox" id="mp_track_inventory" name="mp_track_inventory" value="1"<?php checked($meta["mp_track_inventory"], '1'); ?> /> <?php _e('Inventory', 'mp') ?></label></th>
					<th scope="col" class="mp_custom_field_col" title="<?php _e('Should this product be personalizable by the customer?', 'mp'); ?>"><?php _e('Personalize', 'mp') ?></th>
					<th scope="col" class="mp_var_remove"></th>
				</tr>
			</thead>
			<tbody>
			<?php
				 if (isset($meta["mp_price"]) && $meta["mp_price"]) {
				 //if download enabled only show first variation
				 $meta["mp_price"] = (empty($meta["mp_file"]) && empty($meta["mp_product_link"])) ? $meta["mp_price"] : array($meta["mp_price"][0]);
				 $count = 1;
				 $last = count($meta["mp_price"]);
				foreach ($meta["mp_price"] as $key => $price) {
					 ?>
						<tr class="variation">
							<td class="mp_var_col"><input type="text" name="mp_var_name[]" value="<?php echo esc_attr($meta["mp_var_name"][$key]); ?>" /></td>
							<td class="mp_sku_col"><input type="text" name="mp_sku[]" value="<?php echo esc_attr($meta["mp_sku"][$key]); ?>" /></td>
							<td class="mp_price_col"><?php echo $this->format_currency(); ?><input type="text" name="mp_price[]" value="<?php echo isset($meta["mp_price"][$key]) ? $this->display_currency($meta["mp_price"][$key]) : '0.00'; ?>" /></td>
							<td class="mp_sale_col"><?php echo $this->format_currency(); ?><input type="text" name="mp_sale_price[]" value="<?php echo isset($meta["mp_sale_price"][$key]) ? $this->display_currency($meta["mp_sale_price"][$key]) : $this->display_currency($meta["mp_price"][$key]); ?>" disabled="disabled" /></td>
							<td class="mp_inv_col"><input type="text" name="mp_inventory[]" value="<?php echo isset($meta["mp_inventory"][$key]) ? intval($meta["mp_inventory"][$key]) : 0; ?>" disabled="disabled" /></td>
							<td class="mp_custom_field_col"><input type="checkbox" class="mp_has_custom_field" name="mp_has_custom_field[<?php echo $key; ?>]" value="1" <?php checked(MP_LITE === false && isset($meta['mp_has_custom_field'][$key]) && $meta['mp_has_custom_field'][$key]); echo MP_LITE === true ? ' disabled="disabled"' : ''; ?> /></td>
							<td class="mp_var_remove">
							<?php if ($count == $last) { ?><a href="#mp_product_variations_table" title="<?php _e('Remove Variation', 'mp'); ?>">x</a><?php } ?>
							</td>
						</tr>
						<tr class="variation-custom-field <?php echo (MP_LITE === true || !isset($meta['mp_has_custom_field'][$key]) || !$meta['mp_has_custom_field'][$key]) ? ' variation-custom-field-hidden' : ''; ?>">
							<td class="mp_custom_label_col" colspan="1">
								<input type="hidden" class="mp_custom_field_type" name="mp_custom_field_type[<?php echo $key; ?>]" value="input" />
								<input type="hidden" class="mp_custom_field_per" name="mp_custom_field_per[<?php echo $key; ?>]" value="quantity" />
								<label class="mp_custom_field_label"><?php _e('Description:', 'mp'); ?></label>
								<input type="text" class="mp_custom_field_value" name="mp_custom_field_label[<?php echo $key; ?>]" value="<?php echo isset($meta['mp_custom_field_label'][$key]) ? esc_attr($meta['mp_custom_field_label'][$key]) : ''; ?>" />							
								<label class="mp_custom_field_required_label">
								<input type="checkbox" class="mp_custom_field_required" name="mp_custom_field_required[<?php echo $key; ?>]" value="1" <?php echo checked(isset($meta['mp_custom_field_required'][$key]) && $meta['mp_custom_field_required'][$key]); ?> /> <?php _e('Required', 'mp'); ?></label>
							</td>
							<td>&nbsp;</td>
						</tr>
						<?php
						$count++;
					}
			 } else {
		 		?>
					<tr class="variation">
						<td class="mp_var_col"><input type="text" name="mp_var_name[]" value="" /></td>
						<td class="mp_sku_col"><input type="text" name="mp_sku[]" value="" /></td>
						<td class="mp_price_col"><?php echo $this->format_currency(); ?><input type="text" name="mp_price[]" value="0.00" /></td>
						<td class="mp_sale_col"><?php echo $this->format_currency(); ?><input type="text" name="mp_sale_price[]" value="0.00" disabled="disabled" /></td>
				<td class="mp_inv_col"><input type="text" name="mp_inventory[]" value="0" disabled="disabled" /></td>
						<td class="mp_custom_field_col"><input type="checkbox" class="mp_has_custom_field" name="mp_has_custom_field[]" value="1"<?php echo MP_LITE === true ? ' disabled="disabled"' : ''; ?> /></td>
						<td class="mp_var_remove"><a href="#mp_product_variations_table" title="<?php _e('Remove Variation', 'mp'); ?>">x</a></td>
					</tr>
					<tr class="variation-custom-field variation-custom-field-hidden">
						<td class="mp_custom_label_col" colspan="5">
							<input type="hidden" class="mp_custom_field_type" name="mp_custom_field_type[]" value="input" />
								<input type="hidden" class="mp_custom_field_per" name="mp_custom_field_per[]" value="quantity" />
							<label class="mp_custom_field_label"><?php _e('Description:', 'mp'); ?></label> <input type="text" class="mp_custom_field_value" name="mp_custom_field_label[]" value="" />
							<input type="checkbox" class="mp_custom_field_required" name="mp_custom_field_required[]" value="1" /> <label class="mp_custom_field_required_label"><?php _e('Required:', 'mp'); ?></label>
						</td>
						<td>&nbsp;</td>
					</tr>
					<?php
			 }
			?>
			</tbody>
		</table>
		<?php if (empty($meta["mp_file"]) && empty($meta["mp_product_link"])) { ?>
		<div id="mp_add_vars"><a href="#mp_product_variations_table"><?php _e('Add Variation', 'mp'); ?></a></div>
		<?php } else { ?>
	 <span class="description" id="mp_variation_message"><?php _e('Product variations are not allowed for Downloadable or Externally Linked products.', 'mp') ?></span>
	 <?php } ?>

	 <div id="mp_product_link_div">
		<label title="<?php _e('Some examples are linking to a song/album in iTunes, or linking to a product on another site with your own affiliate link.', 'mp'); ?>"><?php _e('External Link', 'mp'); ?>:<br /><small><?php _e('When set this overrides the purchase button with a link to this URL.', 'mp'); ?></small><br />
		<input type="text" style="width: 100%;" id="mp_product_link" name="mp_product_link" value="<?php echo esc_url($meta["mp_product_link"]); ?>" /></label>
	 </div>

		<div id="mp_tax_rate_div">
			<label title="<?php esc_attr_e('Depending on local tax laws, some items are tax-free or a have different sales tax rate. You can set that here.', 'mp'); ?>"><input type="checkbox" id="mp_is_special_tax" name="mp_is_special_tax" value="1" <?php checked($meta["mp_is_special_tax"]); ?>/> <?php _e('Special Tax Rate?', 'mp'); ?></label>
			<label id="mp_special_tax"<?php echo ($meta["mp_is_special_tax"]) ? '' : ' style="display:none;"'; ?>><?php _e('Rate:', 'mp'); ?> <input type="text" size="2" name="mp_special_tax" value="<?php echo isset($meta["mp_special_tax"]) ? round($meta["mp_special_tax"] * 100, 3) : 0; ?>" />%</label>
		</div>
			
		<div id="mp_cart_limit_div">
			<label title="<?php esc_attr_e('Limit the order to a certain amount of this product.', 'mp'); ?>">
			<input type="checkbox" id="mp_track_limit" name="mp_track_limit" value="1" <?php checked($meta['mp_track_limit']); ?> /> <?php _e('Limit Per Order?','mp');?>
			</label>
			
			<label id="mp_limit"<?php echo ($meta['mp_track_limit']) ? '' :' style="display:none;"';?> ><?php _e('Limit:', 'mp');?>
			<input type="text" size="2" name="mp_limit" value="<?php echo isset( $meta['mp_limit'][0]) ? intval($meta['mp_limit'][0]) : '1' ;?>" /></label>
			
		</div>

	 <?php do_action( 'mp_details_metabox' ); ?>
	 <div class="clear"></div>
	 <?php
	}

	//The Shipping meta box
	function meta_shipping() {
	 global $post;
	 $settings = get_option('mp_settings');
		$mp_shipping = get_post_meta($post->ID, 'mp_shipping', true);
		$mp_shipping = $mp_shipping ? maybe_unserialize($mp_shipping) : array();

		//tie in for shipping plugins
	 do_action( 'mp_shipping_metabox', $mp_shipping, $settings );
	}

	//The Product Download meta box
	function meta_download() {
	 global $post;
		$file = get_post_meta($post->ID, 'mp_file', true);
	 ?>
	 <label><?php _e('File URL', 'mp'); ?>:<br /><input type="text" size="50" id="mp_file" class="mp_file" name="mp_file" value="<?php echo esc_attr($file); ?>" /></label>
	 <input id="mp_upload_button" class="button-secondary" type="button" value="<?php _e('Upload File', 'mp'); ?>" /><br />
	 <?php
	 //display allowed filetypes if WPMU
	 if (is_multisite()) {
		echo '<span class="description">Allowed Filetypes: '.implode(', ', explode(' ', get_site_option('upload_filetypes'))).'</span>';
		if (is_super_admin()) {
			echo '<p>Super Admin: You can change allowed filetypes for your network <a href="' . network_admin_url('settings.php#upload_filetypes') . '">here &raquo;</a></p>';
		}
	 }

	 do_action( 'mp_download_metabox' );
	}

	//returns the calculated price adjusted for sales, formatted or not
	function product_price($product_id, $variation = 0, $format = false) {

		$meta = get_post_custom($product_id);
	 //unserialize
	 foreach ($meta as $key => $val) {
			 $meta[$key] = maybe_unserialize($val[0]);
			 if (!is_array($meta[$key]) && $key != "mp_is_sale" && $key != "mp_track_inventory" && $key != "mp_product_link")
				$meta[$key] = array($meta[$key]);
		}

	 if (is_array($meta["mp_price"])) {
			if ($meta["mp_is_sale"]) {
			$price = $meta["mp_sale_price"][$variation];
		} else {
			$price = $meta["mp_price"][$variation];
		}
	 }

	 $price = ($price) ? $price : 0;
	 $price = $this->display_currency($price);

	 $price = apply_filters( 'mp_product_price', $price, $product_id );

	 if ($format)
		return $this->format_currency('', $price);
	 else
		return $price;
	}

	function is_valid_zip( $zip, $country ) {
		if ( array_key_exists($country, $this->countries_no_postcode) )
			//given country doesn't use post codes so zip is always valid
			return true;
		
		if ( empty($zip) )
			//no post code provided
			return false;
			
		if ( strlen($zip) < 3 )
			//post code is too short - see http://wp.mu/8wg
			return false;
			
		return true;
	}

	//returns the calculated price for shipping. Returns False if shipping address is not available
	function shipping_price($format = false, $cart = false) {
		global $mp_shipping_active_plugins;

		//grab cart for just this blog
		if ( ! $cart ) {
			$cart = $this->get_cart_contents();
		}
		
	 //get total after any coupons
	 $coupon_code = $this->get_coupon_code();
	 $totals = array();
	 foreach ($cart as $product_id => $variations) {
			foreach ($variations as $variation => $data) {
				 $totals[] = $this->coupon_value_product($coupon_code, $data['price'] * $data['quantity'], $product_id);
			}
	 }

	 $total = array_sum($totals);

	 //get address
	 $meta = get_user_meta(get_current_user_id(), 'mp_shipping_info', true);
	 $address1 = isset($_SESSION['mp_shipping_info']['address1']) ? $_SESSION['mp_shipping_info']['address1'] : (isset($meta['address1']) ? $meta['address1'] : '');
	 $address2 = isset($_SESSION['mp_shipping_info']['address2']) ? $_SESSION['mp_shipping_info']['address2'] : (isset($meta['address2']) ? $meta['address2'] : '');
	 $city = isset($_SESSION['mp_shipping_info']['city']) ? $_SESSION['mp_shipping_info']['city'] : (isset($meta['city']) ? $meta['city'] : '');
	 $state = isset($_SESSION['mp_shipping_info']['state']) ? $_SESSION['mp_shipping_info']['state'] : (isset($meta['state']) ? $meta['state'] : '');
	 $zip = isset($_SESSION['mp_shipping_info']['zip']) ? $_SESSION['mp_shipping_info']['zip'] : (isset($meta['zip']) ? $meta['zip'] : '');
	 $country = isset($_SESSION['mp_shipping_info']['country']) ? $_SESSION['mp_shipping_info']['country'] : (isset($meta['country']) ? $meta['country'] : '');
	 $selected_option = isset($_SESSION['mp_shipping_info']['shipping_sub_option']) ? $_SESSION['mp_shipping_info']['shipping_sub_option'] : null;

	 //check required fields
	 if ( empty($address1) || empty($city) || !$this->is_valid_zip($zip, $country) || empty($country) || !(is_array($cart) && count($cart)) )
		return false;

		//don't charge shipping if only digital products
	 if ( $this->download_only_cart($cart) ) {
		$price = 0;
	 } else if ( $this->get_setting('shipping->method') == 'calculated' && isset($_SESSION['mp_shipping_info']['shipping_option']) && isset($mp_shipping_active_plugins[$_SESSION['mp_shipping_info']['shipping_option']]) ) {
			//shipping plugins tie into this to calculate their shipping cost
			$price = apply_filters( 'mp_calculate_shipping_'.$_SESSION['mp_shipping_info']['shipping_option'], 0, $total, $cart, $address1, $address2, $city, $state, $zip, $country, $selected_option );
		} else {
			//shipping plugins tie into this to calculate their shipping cost
			$price = apply_filters( 'mp_calculate_shipping_'.$this->get_setting('shipping->method'), 0, $total, $cart, $address1, $address2, $city, $state, $zip, $country, $selected_option );
		}
		
		//calculate extra shipping
	 $extras = array();
	 foreach ($cart as $product_id => $variations) {
			$shipping_meta = get_post_meta($product_id, 'mp_shipping', true);
			foreach ($variations as $variation => $data) {
				 if (!$data['download'])
			 	$extras[] = $shipping_meta['extra_cost'] * $data['quantity'];
			}
	 }
	 $extra = array_sum($extras);

	 //merge
	 $price = round($price + $extra, 2);
	 
		//boot if shipping plugin didn't return at least 0
		if (empty($price))
			return false;
		
		if ($format)
			return $this->format_currency('', $price);
		else
			return round($price, 2);
	}
	
	//returns the calculated price for shipping after tax. For display only.
	function shipping_tax_price( $shipping_price ) {
			return $shipping_price;
		
	 //get address
	 $meta = get_user_meta(get_current_user_id(), 'mp_shipping_info', true);

	 if (!isset($meta['state'])) {
		$meta['state'] = '';
	 }
	 if (!isset($meta['country'])) {
		$meta['country'] = '';
	 }

	 $state = isset($_SESSION['mp_shipping_info']['state']) ? $_SESSION['mp_shipping_info']['state'] : $meta['state'];
	 $country = isset($_SESSION['mp_shipping_info']['country']) ? $_SESSION['mp_shipping_info']['country'] : $meta['country'];

		//if we've skipped the shipping page and no address is set, use base for tax calculation
		if ($this->get_setting('tax->tax_inclusive') || $this->get_setting('shipping->method') == 'none') {
			if (empty($country))
				$country = $this->get_setting('base_country');
			if (empty($state))
				$state = $this->get_setting('base_province');
		}

	 //check required fields
	 if ( empty($country) || $shipping_price <= 0 ) {
		return false;
	 }

	 switch ($this->get_setting('base_country')) {
			case 'US':
				 //USA taxes are only for orders delivered inside the state
				 if ($country == 'US' && $state == $this->get_setting('base_province'))
				 $price = round(($shipping_price * $this->get_setting('tax->rate')), 2);
				 break;

			case 'CA':
				 //Canada tax is for all orders in country, based on province shipped to. We're assuming the rate is a combination of GST/PST/etc.
				if ( $country == 'CA' && array_key_exists($state, $this->canadian_provinces) ) {
					if (!is_null($this->get_setting("tax->canada_rate->$state")))
						$price = round(($shipping_price * $this->get_setting("tax->canada_rate->$state")), 2);
					else //backwards compat with pre 2.2 if per province rates are not set
						$price = round(($shipping_price * $this->get_setting('tax->rate')), 2);
				}
				 break;

			case 'AU':
				 //Australia taxes orders in country
				 if ($country == 'AU')
				 $price = round(($shipping_price * $this->get_setting('tax->rate')), 2);
				 break;

			default:
				 //EU countries charge VAT within the EU
				 if ( in_array($this->get_setting('base_country'), $this->eu_countries) ) {
				 if (in_array($country, $this->eu_countries))
					$price = round(($shipping_price * $this->get_setting('tax->rate')), 2);
				 } else {
				 //all other countries use the tax outside preference
				 if ($this->get_setting('tax->tax_outside') || (!$this->get_setting('tax->tax_outside') && $country == $this->get_setting('base_country')))
					$price = round(($shipping_price * $this->get_setting('tax->rate')), 2);
				 }
				 break;
	 }
	 
	 if (empty($price))
			$price = 0;
			
	 $price = apply_filters( 'mp_shipping_tax_price', $price, $shipping_price, $country, $state );
	 $price += $shipping_price;
		
	 return round($price, 2);
	}
	
	function get_display_shipping($order) {
		return (float)(isset($order->mp_shipping_with_tax) ? $order->mp_shipping_with_tax : $order->mp_tax_shipping);
	}
	
	/**
	 * Gets the calculated price for taxes based on a bunch of foreign tax laws.
	 *
	 * @access public
	 * @param bool $format (optional) Format number as currency when returned
	 * @param array $cart (optional) The cart array to use for calculations
	 * @return string/float 
	 */
	function tax_price( $format = false, $cart = false ) {

	 //grab cart for just this blog
	 if (!$cart)
			$cart = $this->get_cart_contents();

	 //get address
	 $meta = get_user_meta(get_current_user_id(), 'mp_shipping_info', true);

	 if (!isset($meta['state'])) {
		$meta['state'] = '';
	 }
	 if (!isset($meta['country'])) {
		$meta['country'] = '';
	 }

	 $state = isset($_SESSION['mp_shipping_info']['state']) ? $_SESSION['mp_shipping_info']['state'] : $meta['state'];
	 $country = isset($_SESSION['mp_shipping_info']['country']) ? $_SESSION['mp_shipping_info']['country'] : $meta['country'];

		//if we've skipped the shipping page and no address is set, use base for tax calculation
		if ($this->download_only_cart($cart) || $this->get_setting('tax->tax_inclusive') || $this->get_setting('shipping->method') == 'none') {
			if (empty($country))
				$country = $this->get_setting('base_country');
			if (empty($state))
				$state = $this->get_setting('base_province');
		}

	 //get total after any coupons
	 $totals = array();
	 $special_totals = array();
	 $coupon_code = $this->get_coupon_code();
	 
	 foreach ($cart as $product_id => $variations) {
		//check for special rate
		$special = (bool)get_post_meta($product_id, 'mp_is_special_tax', true);
		if ($special)
			$special_rate = get_post_meta($product_id, 'mp_special_tax', true);
			
		foreach ($variations as $variation => $data) {
			//if not taxing digital goods, skip them completely
			if ( !$this->get_setting('tax->tax_digital') && isset($data['download']) && is_array($data['download']) )
				continue;

			$product_price = $this->coupon_value_product($coupon_code, $data['price'] * $data['quantity'], $product_id);
			
			if ( $this->get_setting('tax->tax_inclusive') ) {
				$product_price = $product_price / (1 + (float) $this->get_setting('tax->rate'));
			}
			
			if ($special)
				$special_totals[] = $product_price * $special_rate;
			else
				$totals[] = $product_price;
		}
	}	
		
	$total = array_sum($totals);
	$special_total = array_sum($special_totals);
	
	//add in shipping?
	$shipping_tax = 0;
	if ( $this->get_setting('tax->tax_shipping') && ($shipping_price = $this->shipping_price()) ) {
		if ( $this->get_setting('tax->tax_inclusive') ) {
			$shipping_tax = $shipping_price - $this->before_tax_price($shipping_price);
		} else {
			$shipping_tax = $shipping_price * (float) $this->get_setting('tax->rate');
		}
	}
	
	//check required fields
	if ( empty($country) || !(is_array($cart) && count($cart)) || ($total + $special_total) <= 0 ) {
		return false;
	}

	switch ($this->get_setting('base_country')) {
		case 'US':
		 //USA taxes are only for orders delivered inside the state
		 if ($country == 'US' && $state == $this->get_setting('base_province'))
		 $price = round(($total * $this->get_setting('tax->rate')) + $special_total, 2);
		 break;

		case 'CA':
			 //Canada tax is for all orders in country, based on province shipped to. We're assuming the rate is a combination of GST/PST/etc.
			if ( $country == 'CA' && array_key_exists($state, $this->canadian_provinces) ) {
				if (!is_null($this->get_setting("tax->canada_rate->$state")))
					$price = round(($total * $this->get_setting("tax->canada_rate->$state")) + $special_total, 2);
				else //backwards compat with pre 2.2 if per province rates are not set
					$price = round(($total * $this->get_setting('tax->rate')) + $special_total, 2);
			}
			 break;

		case 'AU':
			 //Australia taxes orders in country
			 if ($country == 'AU')
			 $price = round(($total * $this->get_setting('tax->rate')) + $special_total, 2);
			 break;

		default:
			 //EU countries charge VAT within the EU
			 if ( in_array($this->get_setting('base_country'), $this->eu_countries) ) {
			 if (in_array($country, $this->eu_countries))
				$price = round(($total * $this->get_setting('tax->rate')) + $special_total, 2);
			 } else {
			 //all other countries use the tax outside preference
			 if ($this->get_setting('tax->tax_outside') || (!$this->get_setting('tax->tax_outside') && $country == $this->get_setting('base_country')))
				$price = round(($total * $this->get_setting('tax->rate')) + $special_total, 2);
			 }
			 break;
	}
	
		if ( empty($price) ) {
			$price = 0;
		}
	
		$price += $shipping_tax;
		$price = apply_filters( 'mp_tax_price', $price, $total, $cart, $country, $state );
		 
		if ( $format ) {
			return $this->format_currency('', $price);
		} else {
			return $price;
		}
	}

	//returns the before tax price for a given amount based on a bunch of foreign tax laws.
	function before_tax_price($tax_price, $product_id = false) {

		//if tax inclusve pricing is turned off just return given price
		if (!$this->get_setting('tax->tax_inclusive'))
			return $tax_price;

		if ($product_id && get_post_meta($product_id, 'mp_is_special_tax', true)) {
			$rate = get_post_meta($product_id, 'mp_special_tax', true);
		} else {
			//figure out rate in case its based on a canadian base province
			$rate =	('CA' == $this->get_setting('tax->base_country')) ? $this->get_setting('tax->canada_rate'.$this->get_setting('base_province')) : $this->get_setting('tax->rate');
		}

		//return round($tax_price / ($rate + 1), 2);
		return $tax_price / ($rate + 1); //do not round this to avoid rounding errors in tax calculation
	}

	//returns contents of shopping cart cookie
	//MP3
	function get_cart_cookie($global = false) {
	 global $blog_id;
	 $blog_id = (is_multisite()) ? $blog_id : 1;

	 $cookie_id = 'mp_globalcart_' . COOKIEHASH;

	 if (isset($_COOKIE[$cookie_id])) {
		$global_cart = unserialize(stripslashes($_COOKIE[$cookie_id]));
	 } else {
		$global_cart = array($blog_id => array());
	 }

	 if ($global) {
		return $global_cart;
	 } else {
			if (isset($global_cart[$blog_id])) {
			 return $global_cart[$blog_id];
			} else {
			 return array();
			}
		}
	}

	//saves global cart array to cookie
	function set_global_cart_cookie($global_cart) {
	 $cookie_id = 'mp_globalcart_' . COOKIEHASH;

	 //set cookie
	 $expire = time() + 2592000; //1 month expire
	 setcookie($cookie_id, serialize($global_cart), $expire, COOKIEPATH, COOKIE_DOMAIN);

	 // Set the cookie variable as well, sometimes updating the cache doesn't work
	 $_COOKIE[$cookie_id] = serialize($global_cart);

	 //mark cache for updating
	 $this->cart_cache = false;
	}

	//saves cart array to cookie
	function set_cart_cookie($cart) {
	 global $blog_id, $mp_gateway_active_plugins;
	 $blog_id = (is_multisite()) ? $blog_id : 1;

	 $global_cart = $this->get_cart_cookie(true);

	 if ($this->global_cart && count($global_cart = $this->get_cart_cookie(true)) >= $mp_gateway_active_plugins[0]->max_stores && !isset($global_cart[$blog_id])) {
		$this->cart_checkout_error(sprintf(__("Sorry, currently it's not possible to checkout with items from more than %s stores.", 'mp'), $mp_gateway_active_plugins[0]->max_stores));
	 } else {
		$global_cart[$blog_id] = $cart;
		}

	 //update cache
	 $this->set_global_cart_cookie($global_cart);
	}

	//returns the full array of cart contents
	function get_cart_contents($global = false) {
	 global $blog_id;
	 $blog_id = (is_multisite()) ? $blog_id : 1;
	 $current_blog_id = $blog_id;
	 
	 //check cache
	 if ($this->cart_cache) {
		if ( $global ) {
			 return $this->cart_cache;
		} else {
			if (isset($this->cart_cache[$blog_id])) {
				return $this->cart_cache[$blog_id];
			} else {
				return array();
			}
		}
	 }

	 $global_cart = $this->get_cart_cookie(true);
	 if (!is_array($global_cart))
		return array();

	 $full_cart = array();
	 foreach ($global_cart as $bid => $cart) {
			if ( is_multisite() && $bid != get_current_blog_id() )
				switch_to_blog($bid);

			$full_cart[$bid] = array();
			
			foreach ($cart as $product_id => $variations) {
				$product = get_post($product_id);

				if ( empty($product) )
					continue;

				$full_cart[$bid][$product_id] = array();
				
				$var_names = maybe_unserialize(get_post_meta($product_id, 'mp_var_name', true));
				
				foreach ( (array) $variations as $variation => $quantity ) {
					if ( is_array($var_names) && count($var_names) > 1 ) {
						$name = get_the_title($product_id) . ': ' . $var_names[$variation];
					} else {
						$name = get_the_title($product_id);
					}

					//check stock
					if ( get_post_meta($product_id, 'mp_track_inventory', true) ) {
						$stock = maybe_unserialize(get_post_meta($product_id, 'mp_inventory', true));
						
						if ( ! is_array($stock) )
						 	$stock[0] = $stock;
						 	
					 	if ( $stock[$variation] < $quantity ) {
							$this->cart_checkout_error( sprintf(__('Sorry, we don\'t have enough of <strong>%1$s</strong> in stock. Your cart quantity has been changed to <strong>%2$s</strong>.', 'mp'), $name, number_format_i18n($stock[$variation])) );
							$quantity = $stock[$variation];
						}
					}

					//check limit if tracking on or is downloadable
					if ( get_post_meta($product_id, 'mp_track_limit', true) || ($this->get_setting('download_order_limit', 1) && $file = get_post_meta($product_id, 'mp_file', true)) ) {
						if ( get_post_meta($product_id, 'mp_track_limit', true) )
							//limit tracking is on
							$limit = maybe_unserialize(get_post_meta($product_id, 'mp_limit', true));
						elseif ( $this->get_setting('download_order_limit', 1) )
							//limit digital products per order is on
							$limit = array($variation => 1);
					
						if ( isset($limit) && $limit[$variation] && $limit[$variation] < $quantity) {
				 			$this->cart_checkout_error( sprintf(__('Sorry, there is a per order limit of %1$s for "%2$s". Your cart quantity has been changed to %3$s.', 'mp'), number_format_i18n($limit[$variation]), $product->post_title, number_format_i18n($limit[$variation])) );
				 			$quantity = $limit[$variation];
				 		}
				 	}

				 	$skus = maybe_unserialize(get_post_meta($product_id, 'mp_sku', true));
				 	if ( !is_array($skus) )
						$skus[0] = $skus;
						
					//get if downloadable
					if ( $download_url = get_post_meta($product_id, 'mp_file', true) )
						$download = array('url' => $download_url, 'downloaded' => 0);
					else
						$download = false;

					$full_cart[$bid][$product_id][$variation] = array('SKU' => $skus[$variation], 'name' => $name, 'url' => get_permalink($product_id), 'price' => $this->product_price($product_id, $variation), 'quantity' => $quantity, 'download' => $download);
				}
			}
		}

		if (is_multisite())
			switch_to_blog($current_blog_id);
	
		//save to cache
		$this->cart_cache = $full_cart;
		
		if ( $global ) {
			return $full_cart;
		} else {
			if (isset($full_cart[$blog_id])) {
				return $full_cart[$blog_id];
			} else {
				return array();
			}
		}
	}

	//receives a post and updates cookie variables for cart
	function update_cart() {
		global $blog_id, $mp_gateway_active_plugins;
		$blog_id = (is_multisite()) ? $blog_id : 1;
		$current_blog_id = $blog_id;

	 $cart = $this->get_cart_cookie();

	 if (isset($_POST['empty_cart'])) { //empty cart contents

			//clear all blog products only if global checkout enabled
			if ($this->global_cart)
				$this->set_global_cart_cookie(array());
			else
				 $this->set_cart_cookie(array());

		if (defined('DOING_AJAX') && DOING_AJAX) {
			?>
				<div class="mp_cart_empty">
					<?php _e('There are no items in your cart.', 'mp') ?>
				</div>
				<div id="mp_cart_actions_widget">
					<a class="mp_store_link" href="<?php mp_products_link(true, true); ?>"><?php _e('Browse Products &raquo;', 'mp') ?></a>
				</div>
			<?php
			exit;
		}

	 } else if (isset($_POST['product_id'])) { //add a product to cart

			//if not valid product_id return
		$product_id = apply_filters('mp_product_id_add_to_cart', intval($_POST['product_id']));
		$product = get_post($product_id);
		if (!$product || $product->post_type != 'product' || $product->post_status != 'publish')
			return false;
	
			//get quantity
		$quantity = (isset($_POST['quantity'])) ? intval(abs($_POST['quantity'])) : 1;

		//get variation
		$variation = (isset($_POST['variation'])) ? intval(abs($_POST['variation'])) : 0;

		//check max stores
		if ($this->global_cart && count($global_cart = $this->get_cart_cookie(true)) >= $mp_gateway_active_plugins[0]->max_stores && !isset($global_cart[$blog_id])) {
			if (defined('DOING_AJAX') && DOING_AJAX) {
					echo 'error||' . sprintf(__("Sorry, currently it's not possible to checkout with items from more than %s stores.", 'mp'), $mp_gateway_active_plugins[0]->max_stores);
			 exit;
			} else {
			 $this->cart_checkout_error(sprintf(__("Sorry, currently it's not possible to checkout with items from more than %s stores.", 'mp'), $mp_gateway_active_plugins[0]->max_stores));
			 return false;
			}
	 	}

		//calculate new quantity
		$new_quantity = $cart[$product_id][$variation] + $quantity;
	 
		//check stock
		if (get_post_meta($product_id, 'mp_track_inventory', true)) {
			$stock = maybe_unserialize(get_post_meta($product_id, 'mp_inventory', true));
			if (!is_array($stock))
					$stock[0] = $stock;
			if ($stock[$variation] < $new_quantity) {
			 if (defined('DOING_AJAX') && DOING_AJAX) {
				echo 'error||' . sprintf(__("Sorry, we don't have enough of this item in stock. (%s remaining)", 'mp'), number_format_i18n($stock[$variation]-$cart[$product_id][$variation]));
				exit;
			 } else {
				$this->cart_checkout_error( sprintf(__("Sorry, we don't have enough of this item in stock. (%s remaining)", 'mp'), number_format_i18n($stock[$variation]-$cart[$product_id][$variation])) );
				return false;
			 }
			}
			//send ajax leftover stock
			if (defined('DOING_AJAX') && DOING_AJAX) {
			 $return = array_sum($stock)-$new_quantity . '||';
			}
		} else {
			//send ajax always stock if stock checking turned off
			if (defined('DOING_AJAX') && DOING_AJAX) {
			 $return = 1 . '||';
			}
		}
		
		//check limit if tracking on or downloadable
	 	if ( get_post_meta($product_id, 'mp_track_limit', true) || ($this->get_setting('download_order_limit', 1) && $file = get_post_meta($product_id, 'mp_file', true)) ) {
			if ( get_post_meta($product_id, 'mp_track_limit', true) )
				//limit tracking is on
				$limit = maybe_unserialize(get_post_meta($product_id, 'mp_limit', true));
			elseif ( $this->get_setting('download_order_limit', 1) )
				//limit digital products per order is on
				$limit = array($variation => 1);
				
			if ( isset($limit) && $limit[$variation] && $limit[$variation] < $new_quantity ) {
				if (defined('DOING_AJAX') && DOING_AJAX) {
			 		echo 'error||' . sprintf(__('Sorry, there is a per order limit of %1$s for "%2$s".', 'mp'), number_format_i18n($limit[$variation]), $product->post_title);
					exit;
				} else {
					$this->cart_checkout_error( sprintf(__('Sorry, there is a per order limit of %1$s for "%2$s".', 'mp'), number_format_i18n($limit[$variation]), $product->post_title) );
					return false;
				}
			}
		}

		$cart[$product_id][$variation] = $new_quantity;

		//save items to cookie
		$this->set_cart_cookie($cart);

		//if running via ajax return updated cart and die
		if (defined('DOING_AJAX') && DOING_AJAX) {
			$return .= mp_show_cart('widget', false, false);
			echo $return;
				exit;
		}
	 } else if (isset($_POST['update_cart_submit'])) { //update cart contents
		$global_cart = $this->get_cart_cookie(true);
		
		
		//process quantity updates
		if (is_array($_POST['quant'])) {
			foreach ($_POST['quant'] as $pbid => $quant) {
					list($bid, $product_id, $variation) = explode(':', $pbid);

					if (is_multisite())
						 switch_to_blog($bid);

					$quant = intval(abs($quant));

			 if ($quant) {
				//check stock
				if (get_post_meta($product_id, 'mp_track_inventory', true)) {
					$stock = maybe_unserialize(get_post_meta($product_id, 'mp_inventory', true));
					if (!is_array($stock))
								$stock[0] = $stock;
					if ($stock[$variation] < $quant) {
					 $left = (($stock[$variation]-intval($global_cart[$bid][$product_id][$variation])) < 0) ? 0 : ($stock[$variation]-intval($global_cart[$bid][$product_id][$variation]));
					 $this->cart_checkout_error( sprintf(__('Sorry, there is not enough stock for "%s". (%s remaining)', 'mp'), get_the_title($product_id), number_format_i18n($left)) );
					 continue;
					}
				}
				
			 	//check limit if tracking on or downloadable
 				if ( get_post_meta($product_id, 'mp_track_limit', true) || ($this->get_setting('download_order_limit', 1) && $file = get_post_meta($product_id, 'mp_file', true)) ) {
					if ( get_post_meta($product_id, 'mp_track_limit', true) )
						//limit tracking is on
						$limit = maybe_unserialize(get_post_meta($product_id, 'mp_limit', true));
					elseif ( $this->get_setting('download_order_limit', 1) )
						//limit digital products per order is on
						$limit = array($variation => 1);
						
					if ( isset($limit) && $limit[$variation] && $limit[$variation] < $quant ) {
						$this->cart_checkout_error( sprintf(__('Sorry, there is a per order limit of %1$s for "%2$s".', 'mp'), number_format_i18n($limit[$variation]), get_the_title($product_id)) );
						continue;
					}
				}

				$global_cart[$bid][$product_id][$variation] = $quant;
			} else {
				unset($global_cart[$bid][$product_id][$variation]);
			}
		}

		if ( is_multisite() )
	 		switch_to_blog($current_blog_id);
		}

		//remove items
		if (isset($_POST['remove']) && is_array($_POST['remove'])) {
			foreach ($_POST['remove'] as $pbid) {
					list($bid, $product_id, $variation) = explode(':', $pbid);
			 unset($global_cart[$bid][$product_id][$variation]);
			}

			$this->cart_update_message( __('Item(s) Removed', 'mp') );
		}
			
		//check for empty blogid carts and unset them to avoid errors on global cart
		foreach ($global_cart as $bid => $data) {
			
			foreach ($data as $product_id => $product) {
				if (!count($product))
					unset($global_cart[$bid][$product_id]);
			}
			
			if (!count($global_cart[$bid]))
				unset($global_cart[$bid]);
		}

		//save items to cookie
		$this->set_global_cart_cookie($global_cart);

		//add coupon code
		if (!empty($_POST['coupon_code'])) {
			 if ($this->check_coupon($_POST['coupon_code'])) {
				 //set a flag so all other coupons will be processed
				 $cart = $this->get_cart_contents($this->global_cart);
				 $can_apply = false;
				 
				 foreach ( $cart as $product_id => $product ) {
				 	//loop through the cart and check each product - just need one "true" response
					 if ( $this->coupon_applicable($_POST['coupon_code'], $product_id) ) {
						 $can_apply = true;
						 break;
					 }
				 }
				 
				 //check the flag before applying the coupon
				 if( $can_apply ) {
					
					if (is_multisite()) {
						 global $blog_id;
						 $_SESSION['mp_cart_coupon_' . $blog_id] = $_POST['coupon_code'];
					} else {
						$_SESSION['mp_cart_coupon'] = $_POST['coupon_code'];
				 	}
					
				 	$this->cart_update_message( __('Coupon Successfully Applied', 'mp') );
			 	}else{
					$this->cart_checkout_error( __('Coupon Was Not Applied', 'mp') );
				}
			} else {
				$this->cart_checkout_error( __('Invalid Coupon Code', 'mp') );
			}
		}

	 } else if (isset($_GET['remove_coupon'])) {
		$this->remove_coupon();
		$this->cart_update_message( __('Coupon Removed', 'mp') );

	 } else if (isset($_POST['mp_shipping_submit'])) { //save shipping info

		//check checkout info
		if (!is_email($_POST['email']))
	 		$this->cart_checkout_error( __('Please enter a valid Email Address.', 'mp'), 'email');

			//only require these fields if not a download only cart
			if ((!$this->download_only_cart($this->get_cart_contents()) || $this->global_cart || $this->get_setting('tax->downloadable_address')) && $this->get_setting('shipping->method') != 'none') {
				$name = trim($_POST['name']);
				$name_parts = explode(' ', $name);
				if (empty($name) || count($name_parts) == 1) {
					$this->cart_checkout_error( __('Please enter your Full Name.', 'mp'), 'name');
				}

				if (empty($_POST['address1']))
					$this->cart_checkout_error( __('Please enter your Street Address.', 'mp'), 'address1');

				if (empty($_POST['city']))
					$this->cart_checkout_error( __('Please enter your City.', 'mp'), 'city');

				if (($_POST['country'] == 'US' || $_POST['country'] == 'CA') && empty($_POST['state']))
					$this->cart_checkout_error( __('Please enter your State/Province/Region.', 'mp'), 'state');

				if ($_POST['country'] == 'US' && !array_key_exists(strtoupper($_POST['state']), $this->usa_states))
					$this->cart_checkout_error( __('Please enter a valid two-letter State abbreviation.', 'mp'), 'state');
				else if ($_POST['country'] == 'CA' && !array_key_exists(strtoupper($_POST['state']), $this->canadian_provinces))
					$this->cart_checkout_error( __('Please enter a valid two-letter Canadian Province abbreviation.', 'mp'), 'state');
				else
					$_POST['state'] = strtoupper($_POST['state']);

				if (!$this->is_valid_zip($_POST['zip'], $_POST['country'])) //no postal code for country
					$this->cart_checkout_error( __('Please enter a valid Zip/Postal Code.', 'mp'), 'zip');

				if (empty($_POST['country']) || strlen($_POST['country']) != 2)
					$this->cart_checkout_error( __('Please enter your Country.', 'mp'), 'country');

				if ($_POST['no_shipping_options'] == '1') {
					$this->cart_checkout_error( __('No valid shipping options found. Please check your address carefully.', 'mp' ), 'no_shipping_options');
				}
			}

			// Process Personalization
			if (isset($_POST['mp_custom_fields']) && count($_POST['mp_custom_fields'])) {
				foreach($_POST['mp_custom_fields'] as $cf_key => $cf_items) {

					list($bid, $product_id, $variation) = split(':', $cf_key);

					if (!isset($product_id)) continue;
					if (!isset($variation)) continue;

					$mp_has_custom_field = get_post_meta(intval($product_id), 'mp_has_custom_field', true);

					if (isset($mp_has_custom_field) && isset($mp_has_custom_field[intval($variation)]) && $mp_has_custom_field[intval($variation)]) {
						$mp_custom_field_required = get_post_meta(intval($product_id), 'mp_custom_field_required', true);

						if (isset($mp_custom_field_required) && isset($mp_custom_field_required[intval($variation)]) && $mp_custom_field_required[intval($variation)]) {

							foreach($cf_items as $idx => $cf_item) {
								if (empty($cf_item)) {
									$this->cart_checkout_error( __('Required product extra information.', 'mp' ), 'custom_fields_'. $product_id .'_'. $variation);
									break;
								} else {
									$cf_items[$idx] = trim(strip_tags(stripslashes($cf_item)));
								}
							}
							$_POST['mp_custom_fields'][$cf_key] = $cf_items;
						}
					}
				}
			}

		//save to session
		global $current_user;
		$meta = get_user_meta($current_user->ID, 'mp_shipping_info', true);
		$_SESSION['mp_shipping_info']['email'] = isset($_POST['email']) ? trim(stripslashes($_POST['email'])) : (isset($meta['email']) ? $meta['email']: $current_user->user_email);
		$_SESSION['mp_shipping_info']['name'] = isset($_POST['name']) ? trim(stripslashes($_POST['name'])) : (isset($meta['name']) ? $meta['name'] : $current_user->user_firstname . ' ' . $current_user->user_lastname);
		$_SESSION['mp_shipping_info']['address1'] = isset($_POST['address1']) ? trim(stripslashes($_POST['address1'])) : $meta['address1'];
		$_SESSION['mp_shipping_info']['address2'] = isset($_POST['address2']) ? trim(stripslashes($_POST['address2'])) : $meta['address2'];
		$_SESSION['mp_shipping_info']['city'] = isset($_POST['city']) ? trim(stripslashes($_POST['city'])) : $meta['city'];
		$_SESSION['mp_shipping_info']['state'] = isset($_POST['state']) ? trim(stripslashes($_POST['state'])) : $meta['state'];
		$_SESSION['mp_shipping_info']['zip'] = isset($_POST['zip']) ? trim(stripslashes($_POST['zip'])) : $meta['zip'];
		$_SESSION['mp_shipping_info']['country'] = isset($_POST['country']) ? trim($_POST['country']) : $meta['country'];
		$_SESSION['mp_shipping_info']['phone'] = isset($_POST['phone']) ? preg_replace('/[^0-9-\(\) ]/', '', trim($_POST['phone'])) : $meta['phone'];
			if (isset($_POST['special_instructions']))
				$_SESSION['mp_shipping_info']['special_instructions'] = trim(stripslashes($_POST['special_instructions']));

			//Handle and store Product Custom field data
			if (isset($_POST['mp_custom_fields']))
				$_SESSION['mp_shipping_info']['mp_custom_fields'] = $_POST['mp_custom_fields'];

		//for checkout plugins
		do_action( 'mp_shipping_process' );

		//save to user meta
		if ($current_user->ID)
			update_user_meta($current_user->ID, 'mp_shipping_info', $_SESSION['mp_shipping_info']);

		//if no errors send to next checkout step
		if ($this->checkout_error == false) {

			//check for $0 checkout to skip gateways

			//loop through cart items
			$global_cart = $this->get_cart_contents(true);
				 if (!$this->global_cart)	 //get subset if needed
				 	$selected_cart[$blog_id] = $global_cart[$blog_id];
				 else
				 $selected_cart = $global_cart;

				$totals = array();
				$shipping_prices = array();
				$tax_prices = array();
			foreach ($selected_cart as $bid => $cart) {

			 if (is_multisite())
					switch_to_blog($bid);

					foreach ($cart as $product_id => $variations) {
						 foreach ($variations as $data) {
							$totals[] = $data['price'] * $data['quantity'];
						 }
					}
					if ( ($shipping_price = $this->shipping_price()) !== false )
					 $shipping_prices[] = $shipping_price;

					if ( ($tax_price = $this->tax_price()) !== false )
					 $tax_prices[] = $tax_price;
			}

			//go back to original blog
				if (is_multisite())
					switch_to_blog($current_blog_id);

				$total = array_sum($totals);

			//coupon line
			if ( $coupon = $this->coupon_value($this->get_coupon_code(), $total) )
			 $total = $coupon['new_total'];

				//shipping
			if ( $shipping_price = array_sum($shipping_prices) )
					$total = $total + $shipping_price;

				//tax line
				if ( $tax_price = array_sum($tax_prices) )
					$total = $total + $tax_price;

			if ($total > 0) {
					$network_settings = get_site_option( 'mp_network_settings' );
			 //can we skip the payment form page?
					if ( $this->global_cart ) {
					$skip = apply_filters('mp_payment_form_skip_' . $network_settings['global_gateway'], false);
					} else {
						 $skip = apply_filters('mp_payment_form_skip_' . $this->get_setting('gateways->allowed->0'), false);
					}
				 if ( (!$this->global_cart && count($this->get_setting('gateways->allowed', array())) > 1) || !$skip ) {
					wp_safe_redirect(mp_checkout_step_url('checkout'));
					exit;
				 } else {
					if ( $this->global_cart )
						$_SESSION['mp_payment_method'] = $network_settings['global_gateway'];
						else
						$_SESSION['mp_payment_method'] = $this->get_setting('gateways->allowed->0');
					do_action( 'mp_payment_submit_' . $_SESSION['mp_payment_method'], $this->get_cart_contents($this->global_cart), $_SESSION['mp_shipping_info'] );
					//if no errors send to next checkout step
					if ($this->checkout_error == false) {
							wp_safe_redirect(mp_checkout_step_url('confirm-checkout'));
							exit;
					} else {
					wp_safe_redirect(mp_checkout_step_url('checkout'));
						exit;
						}
				 }
			} else { //empty price, create order already
					//loop through and create orders
				foreach ($selected_cart as $bid => $cart) {
				$totals = array();
					if (is_multisite())
						switch_to_blog($bid);

						 foreach ($cart as $product_id => $variations) {
							foreach ($variations as $data) {
								$totals[] = $data['price'] * $data['quantity'];
							}
						 }
				$total = array_sum($totals);

					 //coupon line
					 if ( $coupon = $this->coupon_value($this->get_coupon_code(), $total) )
						$total = $coupon['new_total'];

						//shipping
			 	if ( ($shipping_price = $this->shipping_price()) !== false )
						 $total = $total + $shipping_price;

						 //tax line
					if ( ($tax_price = $this->tax_price()) !== false )
						 $total = $total + $tax_price;

				//setup our payment details
					$timestamp = time();
					 	$payment_info['gateway_public_name'] = __('Manual Checkout', 'mp');
					$payment_info['gateway_private_name'] = __('Manual Checkout', 'mp');
			 		 $payment_info['method'] = __('N/A - Free order', 'mp');
			 		 $payment_info['transaction_id'] = __('N/A', 'mp');
			 		 $payment_info['status'][$timestamp] = __('Completed', 'mp');
			 		 $payment_info['total'] = $total;
			 		 $payment_info['currency'] = $this->get_setting('currency');
			 			$this->create_order(false, $cart, $_SESSION['mp_shipping_info'], $payment_info, true);
				}

				//go back to original blog
				 if (is_multisite())
					switch_to_blog($current_blog_id);

				$_SESSION['mp_payment_method'] = 'manual'; //so we don't get an error message on confirmation page

			 //redirect to final page
					wp_safe_redirect(mp_checkout_step_url('confirmation'));
			 exit;
			}
		}

	 } else if (isset($_POST['mp_choose_gateway'])) { //check and save payment info
		$_SESSION['mp_payment_method'] = $_POST['mp_choose_gateway'];
		//processing script is only for selected gateway plugin
		do_action( 'mp_payment_submit_' . $_SESSION['mp_payment_method'], $this->get_cart_contents($this->global_cart), $_SESSION['mp_shipping_info'] );
		//if no errors send to next checkout step
		if ($this->checkout_error == false) {
			wp_safe_redirect(mp_checkout_step_url('confirm-checkout'));
			exit;
		}
	 } else if (isset($_POST['mp_payment_confirm'])) { //create order and process payment

	//check to be sure each product is still available
	$final_cart = $this->get_cart_contents( $this->global_cart );
	if ( is_array( $final_cart ) ) {
		foreach ( $final_cart as $prod_id => $details ) {
			if ( get_post_meta( $prod_id, 'mp_track_inventory', true ) ) {
				$stock = get_post_meta($prod_id, 'mp_inventory', true);
				
				if ( ! is_array($stock) ) {
					$stock = array($stock);
				}
				
				foreach ( $details as $variation => $data ) {
					if ( $data['quantity'] > $stock[$variation] ) {
						$this->cart_checkout_error( __("Sorry, one or more products are no longer available. Please review your cart.", 'mp') );
					}
				}
			}
		}
	}

	//wrap the action as it may trigger errors as well
	if($this->checkout_error == false) {
		do_action( 'mp_payment_confirm_' . $_SESSION['mp_payment_method'], $this->get_cart_contents($this->global_cart), $_SESSION['mp_shipping_info'] );
	}

	//if no errors send to next checkout step
	 if ($this->checkout_error == false) {
	 	wp_safe_redirect(mp_checkout_step_url('confirmation'));
	 	exit;
		}
	 }
	}

	function cart_update_message($msg) {
	 $content = 'return "<div id=\"mp_cart_updated_msg\">' . $msg . '</div>";';
	 add_filter( 'mp_cart_updated_msg', create_function('', $content) );
	}

	function cart_checkout_error($msg, $context = 'checkout') {
	 $msg = str_replace('"', '\"', $msg); //prevent double quotes from causing errors.
	 $content = 'return "<div class=\"mp_checkout_error\">' . $msg . '</div>";';
	 add_action( 'mp_checkout_error_' . $context, create_function('', $content) );
	 $this->checkout_error = true;
	}

	//returns any coupon code saved in $_SESSION. Will only reliably work on checkout pages
	function get_coupon_code() {
	 //get coupon code
	 if ( is_multisite() ) {
		global $blog_id;
		$coupon_code = isset($_SESSION['mp_cart_coupon_' . $blog_id]) ? $_SESSION['mp_cart_coupon_' . $blog_id] : false;
	 } else {
		$coupon_code = isset($_SESSION['mp_cart_coupon']) ? $_SESSION['mp_cart_coupon'] : false;
	 }
	 
	 if ( empty($coupon_code) ) {
		 return false;
	 }
	 
	 return $coupon_code;
	}
	
	//removes a coupon (e.g. a product was removed after coupon was applied)
	function remove_coupon() {
		if ( is_multisite() ) {
			global $blog_id;
			unset($_SESSION['mp_cart_coupon_' . $blog_id]);
		} else {
			unset($_SESSION['mp_cart_coupon']);
		}		
	}

	//checks a coupon code for validity. Return boolean
	function check_coupon($code) {
	 $coupon_code = preg_replace('/[^A-Z0-9_-]/', '', strtoupper($code));

	 //empty code
	 if (!$coupon_code)
		return false;

	 $coupons = get_option('mp_coupons');
		
		//allow short circuit of coupon codes
		$return = apply_filters('mp_coupon_check', null, $coupon_code, $coupons);
		if ( !is_null($return) )
			return $return;
		
	 //no record for code
	 if (!isset($coupons[$coupon_code]) || !is_array($coupons[$coupon_code]))
		return false;

	 //start date not valid yet
	 if (time() < $coupons[$coupon_code]['start'])
		return false;

	 //if end date and expired
	 if ($coupons[$coupon_code]['end'] && time() > $coupons[$coupon_code]['end'])
		return false;

	 //check remaining uses
	 if ($coupons[$coupon_code]['uses'] && (intval($coupons[$coupon_code]['uses']) - intval(@$coupons[$coupon_code]['used'])) <= 0)
		return false;
		
	 //everything passed so it's valid
	 return true;
	}
	
	
	/**
	* Checks a coupon to see if it can be applied to a product.
	*
	* @param string The coupon code
	* @param int The product we are checking
	* @return bool
	*/
	function coupon_applicable( $code, $product_id ) {
		$can_apply = true;
		$coupons = get_option('mp_coupons');
		$coupon_code = preg_replace('/[^A-Z0-9_-]/', '', strtoupper($code));
		$applies_to = isset($coupons[$coupon_code]['applies_to']) ? $coupons[$coupon_code]['applies_to'] : false;
		
		if ( isset($applies_to['type']) && isset($applies_to['id']) ) {
			$what = $applies_to['type']; // the type will be 'product', 'category'
			$item_id	= $applies_to['id']; // the is is either id post ID or the term ID depending on the above
			 
			switch( $what ) {
				case 'product':
				 	$can_apply = ( $product_id == $item_id ) ? true : false;
					break;
				 
				case 'category':
				 	$terms = get_the_terms($product_id, 'product_category');
				 	$can_apply = false;
				 	
				 	if ( is_array($terms) ) {
						foreach ( $terms as $term) {
							if ( $term->term_id == $item_id ) {
								$can_apply = true;
								break;
							}
						}
					}
				break;
			}
		}
		
		return $can_apply;
	}
	

	//get coupon value. Returns array(discount, new_total) or false for invalid code
	function coupon_value($code, $total) {
	 if ($this->check_coupon($code)) {
		$coupons = get_option('mp_coupons');
		$coupon_code = preg_replace('/[^A-Z0-9_-]/', '', strtoupper($code));
		if ($coupons[$coupon_code]['discount_type'] == 'amt') {
			$new_total = round($total - $coupons[$coupon_code]['discount'], 2);
			$new_total = ($new_total < 0) ? 0.00 : $new_total;
			$discount = '-' . $this->format_currency('', $coupons[$coupon_code]['discount']);
			$return = array('discount' => $discount, 'new_total' => $new_total);
		} else {
			$new_total = round($total - ($total * ($coupons[$coupon_code]['discount'] * 0.01)), 2);
			$new_total = ($new_total < 0) ? 0.00 : $new_total;
			$discount = '-' . $coupons[$coupon_code]['discount'] . '%';
			$return = array('discount' => $discount, 'new_total' => $new_total);
		}
			return apply_filters('mp_coupon_value', $return, $code, $total);
	 } else {
		return false;
	 }
	}
	
	//get the price for a product with coupon applied (if applicable)
	function coupon_value_product($code, $price, $product_id) {
		if ( $this->coupon_applicable($code, $product_id) ) {
			$discount = $this->coupon_value($code, $price);
			
			return ( $discount === false ) ? $price : $discount['new_total'];
		}
		
		return $price;
	}

	//record coupon use. Returns boolean successful
	function use_coupon($code) {
	 if ($this->check_coupon($code)) {
		$coupons = get_option('mp_coupons');
		$coupon_code = preg_replace('/[^A-Z0-9_-]/', '', strtoupper($code));

		//increment count
			if ( isset($coupons[$coupon_code]) ) {
				$coupons[$coupon_code]['used']++;
				update_option('mp_coupons', $coupons);
			}
			do_action('mp_coupon_use', $coupon_code);
			
		return true;
	 } else {
		return false;
	 }
	}

	//returns a new unique order id.
	function generate_order_id() {
	 global $wpdb;

	 $count = true;
	 while ($count) { //make sure it's unique
		$order_id = substr(sha1(uniqid('')), rand(1, 24), 12);
		$count = $wpdb->get_var( $wpdb->prepare("SELECT COUNT(*) FROM " . $wpdb->posts . " WHERE post_title = %s AND post_type = 'mp_order'", $order_id) );
	 }

	 $order_id = apply_filters( 'mp_order_id', $order_id ); //Very important to make sure order numbers are unique and not sequential if filtering

	 //save it to session
	 $_SESSION['mp_order'] = $order_id;

	 return $order_id;
	}

	//called on checkout to create a new order
	function create_order($order_id, $cart, $shipping_info, $payment_info, $paid, $user_id = false, $shipping_total = false, $tax_total = false, $coupon_code = false) {
		global $wpdb;
		
	 //order id can be null
	 if (empty($order_id))
		$order_id = $this->generate_order_id();
		else if ($this->get_order($order_id)) //don't continue if the order exists
			 return false;

	 //insert post type
	 $order = array();
	 $order['post_title'] = $order_id;
	 $order['post_name'] = $order_id;
	 $order['post_content'] = serialize($cart).serialize($shipping_info); //this is purely so you can search by cart contents
	 $order['post_status'] = ($paid) ? 'order_paid' : 'order_received';
	 $order['post_type'] = 'mp_order';
	 $post_id = wp_insert_post($order);

	 /* add post meta */

		//filter tax included products in cart
		$filtered_cart = $cart;
	 foreach ($cart as $product_id => $variations) {
			foreach ($variations as $variation => $data) { 
			// store before tax price 
			// if tax_inclusive==true, before_tax_price() rounds to two decimal places
			// the original price cannot be accurately calculated on the order tracking page with just the before_tax_price value
			$filtered_cart[$product_id][$variation]['before_tax_price'] = $this->before_tax_price($data['price'], $product_id);
			}
		}

	 //cart info
	 add_post_meta($post_id, 'mp_cart_info', $filtered_cart, true);
	 //shipping info
	 add_post_meta($post_id, 'mp_shipping_info', $shipping_info, true);
	 //payment info
	 add_post_meta($post_id, 'mp_payment_info', $payment_info, true);

	//loop through cart items
	foreach ($cart as $product_id => $variations) {
		foreach ($variations as $variation => $data) {
			$items[] = $data['quantity'];
	
			/*** adjust product stock quantities ***/
			
			//check if inventory tracking is enabled
			//returned value could be 0, 1 or an empty string so casting as boolean
			if ( ((bool) get_post_meta($product_id, 'mp_track_inventory', true)) === true ) {
				$stock = maybe_unserialize(get_post_meta($product_id, 'mp_inventory', true));
				
				if (!is_array($stock))
					 $stock[0] = $stock;
					 
				$stock[$variation] = $stock[$variation] - $data['quantity'];
					
				update_post_meta($product_id, 'mp_inventory', $stock);
				
				//send low stock notification if needed
				if ($stock[$variation] <= $this->get_setting('inventory_threshhold')) {
					$this->low_stock_notification($product_id, $variation, $stock[$variation]);
				}
			}
			
			//update sales count
			$count = get_post_meta($product_id, 'mp_sales_count', true);
			$count = $count + $data['quantity'];
			update_post_meta($product_id, 'mp_sales_count', $count);
			
			//for plugins into product sales
			do_action( 'mp_product_sale', $product_id, $variation, $data, $paid );
			
			if ( $this->get_setting('inventory_remove') && $stock[$variation] <= 0 ) {		
				$post = get_post( $product_id );
				$wpdb->update( $wpdb->posts, array( 'post_status' => 'draft' ), array( 'ID' => $post->ID ) );
				clean_post_cache( $post->ID );
				$old_status = $post->post_status;
				$post->post_status = 'draft';
				wp_transition_post_status( 'draft', $old_status, $post );
				
				do_action( 'edit_post', $post->ID, $post );
				do_action( 'save_post', $post->ID, $post );
				do_action( 'wp_insert_post', $post->ID, $post );
			}
		}
	}
		$item_count = array_sum($items);

	 //coupon info
	 $code = $coupon_code ? $coupon_code : $this->get_coupon_code();
	 if ( $coupon = $this->coupon_value($code, 9999999999) ) {
		add_post_meta($post_id, 'mp_discount_info', array('code' => $code, 'discount' => $coupon['discount']), true);

		//mark coupon as used
		$this->use_coupon($code);
	 }

	 //payment info
	 add_post_meta($post_id, 'mp_order_total', $payment_info['total'], true);

	 $mp_shipping_total = ($shipping_total ? $shipping_total : $this->shipping_price(false, $cart));
	 add_post_meta($post_id, 'mp_shipping_total', $mp_shipping_total, true);
	 add_post_meta($post_id, 'mp_shipping_with_tax', $this->shipping_tax_price($mp_shipping_total), true);

	 add_post_meta($post_id, 'mp_tax_total', ($tax_total ? $tax_total : $this->tax_price(false, $cart)), true);
	 add_post_meta($post_id, 'mp_order_items', $item_count, true);

	 add_post_meta($post_id, 'mp_tax_inclusive', $this->get_setting('tax->tax_inclusive'), true);
	 add_post_meta($post_id, 'mp_tax_shipping', $this->get_setting('tax->tax_shipping'), true);
	 
	 $timestamp = time();
	 add_post_meta($post_id, 'mp_received_time', $timestamp, true);

	 //set paid time if we already have a confirmed payment
	 if ($paid) {
		add_post_meta($post_id, 'mp_paid_time', $timestamp, true);
		do_action( 'mp_order_paid', $this->get_order($order_id) );
		}

	 //empty cart cookie
	 $this->set_cart_cookie(array());

	 //clear coupon code
	 if (is_multisite()) {
		global $blog_id;
		unset($_SESSION['mp_cart_coupon_' . $blog_id]);
	 } else {
		unset($_SESSION['mp_cart_coupon']);
	 }

	 //save order history
	 if (!$user_id)
	 	$user_id = get_current_user_id();

	 if ($user_id) { //save to user_meta if logged in

		if (is_multisite()) {
			global $blog_id;
			$meta_id = 'mp_order_history_' . $blog_id;
		} else {
			$meta_id = 'mp_order_history';
		}

		$orders = get_user_meta($user_id, $meta_id, true);
		$timestamp = time();
		$orders[$timestamp] = array('id' => $order_id, 'total' => $payment_info['total']);
		update_user_meta($user_id, $meta_id, $orders);

	 } else { //save to cookie instead

		if (is_multisite()) {
			global $blog_id;
			$cookie_id = 'mp_order_history_' . $blog_id . '_' . COOKIEHASH;
		} else {
			$cookie_id = 'mp_order_history_' . COOKIEHASH;
		}

		if (isset($_COOKIE[$cookie_id]))
			$orders = unserialize($_COOKIE[$cookie_id]);

		$timestamp = time();
		$orders[$timestamp] = array('id' => $order_id, 'total' => $payment_info['total']);

		//set cookie
		$expire = time() + 31536000; //1 year expire
		setcookie($cookie_id, serialize($orders), $expire, COOKIEPATH, COOKIEDOMAIN);
	 }

	 //hook for new orders
	 do_action( 'mp_new_order', $this->get_order($order_id) );

	 //send new order email
	 $this->order_notification($order_id);

		//if paid and the cart is only digital products mark it shipped
		if ($paid && $this->download_only_cart($cart)) {
			$this->skip_shipping_notice = true;
			 $this->update_order_status($order_id, 'shipped');
		}

	 return $order_id;
	}

	//returns the full order details as an object
	function get_order($order_id) {
	 $id = (is_int($order_id)) ? $order_id : $this->order_to_post_id($order_id);

	 if (empty($id))
		return false;



		$order = get_post($id);
	 if (!$order)
		return false;

	 $meta = get_post_custom($id);

		//unserialize a and add to object
		foreach ($meta as $key => $val)
		$order->$key = maybe_unserialize($meta[$key][0]);
	 
	 return $order;
	}

	//serves the 'order_paid' : 'order_received'
	function export_orders_csv() {
		global $wpdb;

		//check permissions
		$post_type_object = get_post_type_object('mp_order');
		if ( !current_user_can($post_type_object->cap->edit_posts) )
			wp_die(__('Cheatin&#8217; uh?'));

		$query = "SELECT ID, post_title, post_date, post_status FROM {$wpdb->posts} WHERE post_type = 'mp_order'";

		if (isset($_POST['order_status']) && $_POST['order_status'] != 'all')
			$query .= $wpdb->prepare(' AND post_status = %s', $_POST['order_status']);

		// If a month is specified in the querystring, load that month
		if ( isset($_POST['m']) && $_POST['m'] > 0 ) {
			$_POST['m'] = '' . preg_replace('|[^0-9]|', '', $_POST['m']);
			$query .= " AND YEAR($wpdb->posts.post_date)=" . substr($_POST['m'], 0, 4);
			if ( strlen($_POST['m']) > 5 )
				$query .= " AND MONTH($wpdb->posts.post_date)=" . substr($_POST['m'], 4, 2);
			if ( strlen($_POST['m']) > 7 )
				$query .= " AND DAYOFMONTH($wpdb->posts.post_date)=" . substr($_POST['m'], 6, 2);
			if ( strlen($_POST['m']) > 9 )
				$query .= " AND HOUR($wpdb->posts.post_date)=" . substr($_POST['m'], 8, 2);
			if ( strlen($_POST['m']) > 11 )
				$query .= " AND MINUTE($wpdb->posts.post_date)=" . substr($_POST['m'], 10, 2);
			if ( strlen($_POST['m']) > 13 )
				$query .= " AND SECOND($wpdb->posts.post_date)=" . substr($_POST['m'], 12, 2);
		}

		$query .= " ORDER BY post_date DESC";

		$orders = $wpdb->get_results($query);

		// Keep up to 12MB in memory, if becomes bigger write to temp file
		$file = fopen('php://temp/maxmemory:'. (12*1024*1024), 'r+');
		fputcsv( $file, array('order_id', 'status', 'received_date', 'paid_date', 'shipped_date', 'tax', 'shipping', 'total', 'coupon_discount', 'coupon_code', 'item_count', 'items', 'email', 'name', 'address1', 'address2', 'city', 'state', 'zipcode', 'country', 'phone', 'shipping_method', 'shipping_method_option', 'special_instructions', 'gateway', 'gateway_method', 'payment_currency', 'transaction_id' ) );

		//loop through orders and add rows
		foreach ($orders as $order) {
			$meta = get_post_custom($order->ID);

			//unserialize a and add to object
			foreach ($meta as $key => $val)
				$order->$key = maybe_unserialize($meta[$key][0]);

			$fields = array();
			$fields['order_id'] = $order->post_title;
			$fields['status'] = $order->post_status;
			$fields['received_date'] = $order->post_date;
			$fields['paid_date'] = isset($order->mp_paid_time) ? date('Y-m-d H:i:s', $order->mp_paid_time) : null;
			$fields['shipped_date'] = isset($order->mp_shipped_time) ? date('Y-m-d H:i:s', $order->mp_paid_time) : null;
			$fields['tax'] = $order->mp_tax_total;
			$fields['shipping'] = $order->mp_shipping_total;
			$fields['total'] = $order->mp_order_total;
			$fields['coupon_discount'] = @$order->mp_discount_info['discount'];
			$fields['coupon_code'] = @$order->mp_discount_info['code'];
			$fields['item_count'] = $order->mp_order_items;
			//items
			if (is_array($order->mp_cart_info) && count($order->mp_cart_info)) {
				foreach ($order->mp_cart_info as $product_id => $variations) {
					foreach ($variations as $variation => $data) {
						if (!empty($fields['items']))
							$fields['items'] .= "\r\n";
							
						if (!empty($data['SKU']))
							$fields['items'] .= '[' . $data['SKU'] . '] ';
						
						$price = $this->coupon_value_product($fields['coupon_code'], $data['price'] * $data['quantity'], $product_id);
						
						$fields['items'] .= $data['name'] . ': ' . number_format_i18n($data['quantity']) . ' * ' . number_format_i18n($price / $data['quantity'], 2) . ' ' . $order->mp_payment_info['currency'];
					}
				}
			} else {
				$fields['items'] = 'N/A';
			}

			$fields['email'] = @$order->mp_shipping_info['email'];
			$fields['name'] = @$order->mp_shipping_info['name'];
			$fields['address1'] = @$order->mp_shipping_info['address1'];
			$fields['address2'] = @$order->mp_shipping_info['address2'];
			$fields['city'] = @$order->mp_shipping_info['city'];
			$fields['state'] = @$order->mp_shipping_info['state'];
			$fields['zipcode'] = @$order->mp_shipping_info['zip'];
			$fields['country'] = @$order->mp_shipping_info['country'];
			$fields['phone'] = @$order->mp_shipping_info['phone'];
			$fields['shipping_method'] = @$order->mp_shipping_info['shipping_option'];
			$fields['shipping_method_option'] = @$order->mp_shipping_info['shipping_sub_option'];
			$fields['special_instructions'] = @$order->mp_shipping_info['special_instructions'];
			$fields['gateway'] = @$order->mp_payment_info['gateway_private_name'];
			$fields['gateway_method'] = @$order->mp_payment_info['method'];
			$fields['payment_currency'] = @$order->mp_payment_info['currency'];
			$fields['transaction_id'] = @$order->mp_payment_info['transaction_id'];

			fputcsv( $file, $fields );
		}

		//create our filename
		$filename = 'orders_export';
		$filename .= isset($_POST['m']) ? '_' . $_POST['m'] : '';
		$filename .= '_' . time() . '.csv';

		//serve the file
		rewind($file);
		ob_end_clean(); //kills any buffers set by other plugins
		header('Content-Description: File Transfer');
		header('Content-Type: text/csv');
		header('Content-Disposition: attachment; filename="'.$filename.'"');
		header('Content-Transfer-Encoding: binary');
		header('Expires: 0');
		header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
		header('Pragma: public');
		$output = stream_get_contents($file);
		$output = "\xEF\xBB\xBF" . $output; // UTF-8 BOM
		header('Content-Length: ' . strlen($output));
		fclose($file);
		die($output);
	}

	//converts the pretty order id to an actual post ID
	function order_to_post_id($order_id) {
	 global $wpdb;
	 return $wpdb->get_var($wpdb->prepare("SELECT ID FROM {$wpdb->posts} WHERE post_name = %s AND post_type = 'mp_order'", $order_id));
	}

	//$new_status can be 'received', 'paid', 'shipped', 'closed'
	function update_order_status($order_id, $new_status) {
	 global $wpdb;

	 $statuses = array('received' => 'order_received', 'paid' => 'order_paid', 'shipped' => 'order_shipped', 'closed' => 'order_closed', 'trash' => 'trash', 'delete' => 'delete');
	 if (!array_key_exists($new_status, $statuses))
		return false;

	 //get the order
	 $order = $this->get_order($order_id);
	 if (!$order)
		return false;

		// If we are transitioning the status from 'trash' to some other vlaue we want to decrement the product variation quantities. 
		if (($order->post_status == "trash") && ($new_status != "delete") && ($new_status != 'trash')) {
			if (is_array($order->mp_cart_info) && count($order->mp_cart_info)) {
				foreach ($order->mp_cart_info as $product_id => $variations) {
				
					if (!get_post_meta($product_id, 'mp_track_inventory', true))
						continue;
	
					$mp_inventory = get_post_meta($product_id, 'mp_inventory', true);
					if (!$mp_inventory)
						continue;
				
					$_PRODUCT_INVENTORY_CHANGED = false;
					foreach ($variations as $variation => $data) {
						if (array_key_exists($variation, $mp_inventory)) {
							$mp_inventory[$variation] -= $data['quantity'];
							$_PRODUCT_INVENTORY_CHANGED = true;
						}
					}
					if ($_PRODUCT_INVENTORY_CHANGED) {
						update_post_meta($product_id, 'mp_inventory', $mp_inventory);					
					}
				}
			}
		}
	 switch ($new_status) {

		case 'paid':
			//update paid time, can't be adjusted as we don't want to loose gateway info
			if (!get_post_meta($order->ID, 'mp_paid_time', true)) {
			 update_post_meta($order->ID, 'mp_paid_time', time());
			 do_action( 'mp_order_paid', $order );
			}
			break;

		case 'shipped':
			//update paid time if paid step was skipped
			if (!get_post_meta($order->ID, 'mp_paid_time', true)) {
			 update_post_meta($order->ID, 'mp_paid_time', time());
			 do_action( 'mp_order_paid', $order );
			}

			//update shipped time, can be adjusted
			update_post_meta($order->ID, 'mp_shipped_time', time());
			do_action( 'mp_order_shipped', $order );

			//send email
				$this->order_shipped_notification($order->ID);
			break;

		case 'closed':
			//update paid time if paid step was skipped
			if (!get_post_meta($order->ID, 'mp_paid_time', true)) {
			 update_post_meta($order->ID, 'mp_paid_time', time());
			 do_action( 'mp_order_paid', $order );
			}

			//update shipped time if shipped step was skipped
			if (!get_post_meta($order->ID, 'mp_shipped_time', true)) {
			 update_post_meta($order->ID, 'mp_shipped_time', time());
			 do_action( 'mp_order_shipped', $order );
			}

			//update closed
			update_post_meta($order->ID, 'mp_closed_time', time());
			do_action( 'mp_order_closed', $order );
			break;

	 case 'trash':
		if (is_array($order->mp_cart_info) && count($order->mp_cart_info)) {
			foreach ($order->mp_cart_info as $product_id => $variations) {
				
				if (!get_post_meta($product_id, 'mp_track_inventory', true))
				 	continue;

				$mp_inventory = get_post_meta($product_id, 'mp_inventory', true);
				if (!$mp_inventory)
					continue;
				
				$_PRODUCT_INVENTORY_CHANGED = false;
				foreach ($variations as $variation => $data) {
					if (array_key_exists($variation, $mp_inventory)) {
						$mp_inventory[$variation] += $data['quantity'];
						$_PRODUCT_INVENTORY_CHANGED = true;
					}
				}
				
				if ($_PRODUCT_INVENTORY_CHANGED) {
					update_post_meta($product_id, 'mp_inventory', $mp_inventory);					
				}
				
			}
		}
		break;
		
	 case 'delete':
		wp_delete_post( $order_id );
		break;

	 }

	 if ( $statuses[$new_status] == $order->post_status )
	 	return;

	 $wpdb->update( $wpdb->posts, array( 'post_status' => $statuses[$new_status] ), array( 'ID' => $order->ID ) );

	 $old_status = $order->post_status;
	 $order->post_status = $statuses[$new_status];
	 wp_transition_post_status($statuses[$new_status], $old_status, $order);
	}

	//checks if a given cart is only downloadable products
	function download_only_cart($cart) {
		foreach ((array)$cart as $product_id => $variations) {
			foreach ((array)$variations as $variation => $data) {
				if (!is_array($data['download']))
				return false;
			}
		}
		return true;
	}

	//returns formatted download url for a given product. Returns false if no download
	function get_download_url($product_id, $order_id) {
	 $url = get_post_meta($product_id, 'mp_file', true);
	 if (!$url)
		return false;

		return get_permalink($product_id) . "?orderid=$order_id";
	}

	//serves a downloadble product file
	function serve_download($product_id) {

		if (!isset($_GET['orderid']))
		return false;

	 //get the order
	 $order = $this->get_order($_GET['orderid']);
		if (!$order)
		wp_die( __('Sorry, the link is invalid for this download.', 'mp') );

		//check that order is paid
	 if ($order->post_status == 'order_received')
		wp_die( __('Sorry, your order has been marked as unpaid.', 'mp') );

		$url = get_post_meta($product_id, 'mp_file', true);

		//get cart count
		if (isset($order->mp_cart_info[$product_id][0]['download']))
			 $download = $order->mp_cart_info[$product_id][0]['download'];

		//if new url is not set try to grab it from the order history
	 if (!$url && isset($download['url']))
		$url = $download['url'];
		else if (!$url)
			wp_die( __('Whoops, we were unable to find the file for this download. Please contact us for help.', 'mp') );

		//check for too many downloads
		$max_downloads = $this->get_setting('max_downloads', 5);
		if (intval($download['downloaded']) >= $max_downloads)
			 wp_die( sprintf( __("Sorry, our records show you've downloaded this file %d out of %d times allowed. Please contact us if you still need help.", 'mp'), intval($download['downloaded']), $max_downloads ) );

		//for plugins to hook into the download script. Don't forget to increment the download count, then exit!
		do_action('mp_serve_download', $url, $order, $download);

		//allows you to simply filter the url
		$url = apply_filters('mp_download_url', $url, $order, $download);

		//if your getting out of memory errors with large downloads, you can use a redirect instead, it's not so secure though
		if ( MP_LARGE_DOWNLOADS === true ) {
			 //attempt to record a download attempt
			if (isset($download['downloaded'])) {
				$order->mp_cart_info[$product_id][0]['download']['downloaded'] = $download['downloaded'] + 1;
				update_post_meta($order->ID, 'mp_cart_info', $order->mp_cart_info);
			}
			
			wp_redirect($url);
			exit;
		}
		
		set_time_limit(0); //try to prevent script from timing out

		//create unique filename
		$ext = ltrim(strrchr(basename($url), '.'), '.');
		$filename = sanitize_file_name( strtolower( get_the_title($product_id) ) . '.' . $ext );

		$dirs = wp_upload_dir();
		$location = str_replace($dirs['baseurl'], $dirs['basedir'], $url);
		if ( file_exists($location) ) {
			// File is in our server
			$tmp = $location;
			$not_delete = true;
		} else {
			// File is remote so we need to download it first
			require_once(ABSPATH . '/wp-admin/includes/file.php');

			$tmp = download_url($url); //we download the url so we can serve it via php, completely obfuscating original source

			if ( is_wp_error($tmp) ) {
				@unlink($tmp);
				trigger_error("MarketPress was unable to download the file $url for serving as download: " . $tmp->get_error_message(), E_USER_WARNING);
				wp_die(__('Whoops, there was a problem loading up this file for your download. Please contact us for help.', 'mp'));
			}
		}

		if ( file_exists($tmp) ) {
		 	$chunksize = (8 * 1024); //number of bytes per chunk
			$buffer = '';
			$filesize = filesize($tmp);
			$length = $filesize;
			list($fileext, $filetype) = wp_check_filetype($tmp);
			
			if ( empty($filetype) ) {
				$filetype = 'application/octet-stream';
			}
			
			ob_clean(); //kills any buffers set by other plugins
			
			if( isset($_SERVER['HTTP_RANGE']) ) {
				//partial download headers
				preg_match('/bytes=(\d+)-(\d+)?/', $_SERVER['HTTP_RANGE'], $matches);
				$offset = intval($matches[1]);
				$length = intval($matches[2]) - $offset;
				$fhandle = fopen($filePath, 'r');
				fseek($fhandle, $offset); // seek to the requested offset, this is 0 if it's not a partial content request
				$data = fread($fhandle, $length);
				fclose($fhandle);
				header('HTTP/1.1 206 Partial Content');
				header('Content-Range: bytes ' . $offset . '-' . ($offset + $length) . '/' . $filesize);
			}
			
			header('Accept-Ranges: bytes');
			header('Content-Description: File Transfer');
			header('Content-Type: ' . $filetype);
			header('Content-Disposition: attachment;filename="' . $filename . '"');
			header('Expires: -1');
			header('Cache-Control: public, must-revalidate, post-check=0, pre-check=0');
			header('Pragma: public');
			header('Content-Length: ' . $filesize);
			
			if ( $filesize > $chunksize ) {
				$handle = fopen($tmp, 'rb');
				
				if ( $handle === false ) {
					trigger_error("MarketPress was unable to read the file $tmp for serving as download.", E_USER_WARNING);
					return false;
				}
				
				while ( ! feof($handle) && ( connection_status() === CONNECTION_NORMAL ) ) {
					$buffer = fread( $handle, $chunksize );
					echo $buffer;
				}
				
				ob_end_flush();
				fclose($handle);
			} else {
				ob_clean();
				flush();
				readfile($tmp);
			}

			if ( ! $not_delete ) {
				@unlink($tmp);
			}
		}

		//attempt to record a download attempt
		if ( isset($download['downloaded']) ) {
			$order->mp_cart_info[$product_id][0]['download']['downloaded'] = $download['downloaded'] + 1;
			update_post_meta($order->ID, 'mp_cart_info', $order->mp_cart_info);
		}
		
		exit;
	}

	/**
	 * Update user fields
	 *
	 * @access public
	 * @param int $user_id
	 */
	function user_profile_update( $user_id ) {
		$fields = array( 'email', 'name', 'address1', 'address2', 'city', 'state', 'zip', 'country', 'phone' );
		
		//shipping fields
		$meta = array();
		foreach ( $fields as $field ) {
			if ( isset($_POST['mp_shipping_info'][$field]) ) {
				$value = $_POST['mp_shipping_info'][$field];
			} else {
				$value = '';
			}
			
			$meta[$field] = $_SESSION['mp_shipping_info'][$field] = $value;
		}
		update_user_meta($user_id, 'mp_shipping_info', $meta);
		
		// Billing Info
		$meta = array();
		foreach ( $fields as $field ) {
			if ( isset($_POST['mp_billing_info'][$field]) ) {
				$value = $_POST['mp_billing_info'][$field];
			} else {
				$value = '';
			}
			
			$meta[$field] = $_SESSION['mp_billing_info'][$field] = $value;
		}
		update_user_meta($user_id, 'mp_billing_info', $meta);
	}

	function user_profile_fields() {
		global $current_user;
		$fields = array( 'email', 'name', 'address1', 'address2', 'city', 'state', 'zip', 'country', 'phone' );
		
		if ( isset($_REQUEST['user_id'])) {
			$user_id = $_REQUEST['user_id'];
		} else {
			$user_id = $current_user->ID;
		}
		
		//initialize variables
		$meta = get_user_meta($user_id, 'mp_shipping_info', true);
		foreach ( $fields as $field ) {
			if ( ! empty($meta[$field]) ) {
				$$field = $meta[$field];
			} else {
				$$field = '';
			}
		}
		
		if ( empty($country) )
			$country = $this->get_setting('base_country');
	 ?>
	 <h3><?php _e('Shipping Info', 'mp'); ?></h3>
	 <table class="form-table">
			<tr>
			<th align="right"><label for="mp_shipping_info_email"><?php _e('Email:', 'mp'); ?>&nbsp;</label></th><td>
			<?php echo apply_filters( 'mp_shipping_info_error_email', ''); ?>
			<input size="35" id="mp_shipping_info_email" name="mp_shipping_info[email]" type="text" value="<?php echo esc_attr($email); ?>" /></td>
		</tr>
		<tr>
			<th align="right"><label for="mp_shipping_info_name"><?php _e('Full Name:', 'mp'); ?>&nbsp;</label></th><td>
			<?php echo apply_filters( 'mp_checkout_error_name', ''); ?>
			<input size="35" id="mp_shipping_info_name" name="mp_shipping_info[name]" type="text" value="<?php echo esc_attr($name); ?>" /> </td>
		</tr>
		<tr>
			<th align="right"><label for="mp_shipping_info_address1"><?php _e('Address:', 'mp'); ?>&nbsp;</label></th><td>
			<?php echo apply_filters( 'mp_shipping_info_error_address1', ''); ?>
			<input size="45" id="mp_shipping_info_address1" name="mp_shipping_info[address1]" type="text" value="<?php echo esc_attr($address1); ?>" /><br />
			<small><em><?php _e('Street address, P.O. box, company name, c/o', 'mp'); ?></em></small>
			</td>
		</tr>
		<tr>
			<th align="right"><label for="mp_shipping_info_address2"><?php _e('Address 2:', 'mp'); ?>&nbsp;</label></th><td>
				<?php echo apply_filters( 'mp_shipping_info_error_address2', ''); ?>
			<input size="45" id="mp_shipping_info_address2" name="mp_shipping_info[address2]" type="text" value="<?php echo esc_attr($address2); ?>" /><br />
			<small><em><?php _e('Apartment, suite, unit, building, floor, etc.', 'mp'); ?></em></small>
			</td>
		</tr>
		<tr>
			<th align="right"><label for="mp_shipping_info_city"><?php _e('City:', 'mp'); ?>&nbsp;</label></th><td>
			<?php echo apply_filters( 'mp_shipping_info_error_city', ''); ?>
			<input size="25" id="mp_shipping_info_city" name="mp_shipping_info[city]" type="text" value="<?php echo esc_attr($city); ?>" /></td>
		</tr>
		<tr>
			<th align="right"><label for="mp_shipping_info_state"><?php _e('State/Province/Region:', 'mp'); ?>&nbsp;</label></th><td>
			<?php echo apply_filters( 'mp_shipping_info_error_state', ''); ?>
			<input size="15" id="mp_shipping_info_state" name="mp_shipping_info[state]" type="text" value="<?php echo esc_attr($state); ?>" /></td>
		</tr>
		<tr>
			<th align="right"><label for="mp_shipping_info_zip"><?php _e('Postal/Zip Code:', 'mp'); ?>&nbsp;</label></th><td>
			<?php echo apply_filters( 'mp_shipping_info_error_zip', ''); ?>
			<input size="10" id="mp_shipping_info_zip" name="mp_shipping_info[zip]" type="text" value="<?php echo esc_attr($zip); ?>" /></td>
		</tr>
		<tr>
			<th align="right"><label for="mp_shipping_info_country"><?php _e('Country:', 'mp'); ?>&nbsp;</label></th><td>
			<?php echo apply_filters( 'mp_shipping_info_error_country', ''); ?>
			<select id="mp_shipping_info_country" name="mp_shipping_info[country]">
			 <?php
			 foreach ($this->get_setting('shipping->allowed_countries') as $code) {
				?><option value="<?php echo $code; ?>"<?php selected($country, $code); ?>><?php echo esc_attr($this->countries[$code]); ?></option><?php
			 }
			 ?>
			</select>
			</td>
		</tr>
		<tr>
			<th align="right"><label for="mp_shipping_info_phone"><?php _e('Phone Number:', 'mp'); ?>&nbsp;</label></th><td>
			<?php echo apply_filters( 'mp_shipping_info_error_phone', ''); ?>
				<input size="20" id="mp_shipping_info_phone" name="mp_shipping_info[phone]" type="text" value="<?php echo esc_attr($phone); ?>" /></td>
		</tr>
	 </table>
	 <?php

		//initialize variables
	 	$meta = get_user_meta($user_id, 'mp_billing_info', true);
		foreach ( $fields as $field ) {
			if ( !empty($_SESSION['mp_billing_info'][$field]) ) {
				$$field = $_SESSION['mp_billing_info'][$field];
			} elseif ( !empty($meta[$field]) ) {
				$$field = $meta[$field];
			} else {
				$$field = '';
			}
		}
		
		if ( empty($country) )
			$country = $this->get_setting('base_country');
	 ?>
	 <h3><?php _e('Billing Info', 'mp'); ?> <a class="add-new-h2" href="javascript:mp_copy_billing('mp_billing_info');"><?php _e('Same as Shipping', 'mp'); ?></a></h3>
	 <table class="form-table">
		<tr>
			<th align="right"><label for="mp_billing_info_email"><?php _e('Email:', 'mp'); ?>&nbsp;</label></th><td>
			<?php echo apply_filters( 'mp_billing_info_error_email', ''); ?>
			<input size="35" id="mp_billing_info_email" name="mp_billing_info[email]" type="text" value="<?php echo esc_attr($email); ?>" /></td>
		</tr>
		<tr>
			<th align="right"><label for="mp_billing_info_name"><?php _e('Full Name:', 'mp'); ?>&nbsp;</label></th><td>
			<?php echo apply_filters( 'mp_billing_info_error_name', ''); ?>
			<input size="35" id="mp_billing_info_name" name="mp_billing_info[name]" type="text" value="<?php echo esc_attr($name); ?>" /> </td>
		</tr>
		<tr>
			<th align="right"><label for="mp_billing_info_address1"><?php _e('Address:', 'mp'); ?>&nbsp;</label></th><td>
			<?php echo apply_filters( 'mp_billing_info_error_address1', ''); ?>
			<input size="45" id="mp_billing_info_address1" name="mp_billing_info[address1]" type="text" value="<?php echo esc_attr($address1); ?>" /><br />
			<small><em><?php _e('Street address, P.O. box, company name, c/o', 'mp'); ?></em></small>
			</td>
		</tr>
		<tr>
			<th align="right"><label for="mp_billing_info_address2"><?php _e('Address 2:', 'mp'); ?>&nbsp;</label></th><td>
				<?php echo apply_filters( 'mp_billing_info_error_address2', ''); ?>
			<input size="45" id="mp_billing_info_address2" name="mp_billing_info[address2]" type="text" value="<?php echo esc_attr($address2); ?>" /><br />
			<small><em><?php _e('Apartment, suite, unit, building, floor, etc.', 'mp'); ?></em></small>
			</td>
		</tr>
		<tr>
			<th align="right"><label for="mp_billing_info_city"><?php _e('City:', 'mp'); ?>&nbsp;</label></th><td>
			<?php echo apply_filters( 'mp_billing_info_error_city', ''); ?>
			<input size="25" id="mp_billing_info_city" name="mp_billing_info[city]" type="text" value="<?php echo esc_attr($city); ?>" /></td>
		</tr>
		<tr>
			<th align="right"><label for="mp_billing_info_state"><?php _e('State/Province/Region:', 'mp'); ?>&nbsp;</label></th><td>
			<?php echo apply_filters( 'mp_billing_info_error_state', ''); ?>
			<input size="15" id="mp_billing_info_state" name="mp_billing_info[state]" type="text" value="<?php echo esc_attr($state); ?>" /></td>
		</tr>
		<tr>
			<th align="right"><label for="mp_billing_info_zip"><?php _e('Postal/Zip Code:', 'mp'); ?>&nbsp;</label></th><td>
			<?php echo apply_filters( 'mp_billing_info_error_zip', ''); ?>
			<input size="10" id="mp_billing_info_zip" name="mp_billing_info[zip]" type="text" value="<?php echo esc_attr($zip); ?>" /></td>
		</tr>
		<tr>
			<th align="right"><label for="mp_billing_info_country"><?php _e('Country:', 'mp'); ?>&nbsp;</label></th><td>
			<?php echo apply_filters( 'mp_billing_info_error_country', ''); ?>
			<select id="mp_billing_info_country" name="mp_billing_info[country]">
			 <?php
			 foreach ($this->get_setting('shipping->allowed_countries') as $code) {
				?><option value="<?php echo $code; ?>"<?php selected($country, $code); ?>><?php echo esc_attr($this->countries[$code]); ?></option><?php
			 }
			 ?>
			</select>
			</td>
		</tr>
		<tr>
			<th align="right"><label for="mp_billing_info_phone"><?php _e('Phone Number:', 'mp'); ?>&nbsp;</label></th><td>
			<?php echo apply_filters( 'mp_billing_info_error_phone', ''); ?>
				<input size="20" id="mp_billing_info_phone" name="mp_billing_info[phone]" type="text" value="<?php echo esc_attr($phone); ?>" /></td>
		</tr>
	 </table>
	 <script type="text/javascript">
	 function mp_copy_billing(prefix) {
		_mp_profile_billing_fields = ['email', 'name', 'address1', 'address2', 'city', 'state', 'zip', 'country', 'phone'];

		for (_i=0; _i<_mp_profile_billing_fields.length; _i++) {
			jQuery('form #'+prefix+'_'+_mp_profile_billing_fields[_i]).val(jQuery('form #mp_shipping_info_'+_mp_profile_billing_fields[_i]).val());
		}
	 }
	 </script>
	 <?php
	}

	//called by payment gateways to update order statuses
	function update_order_payment_status($order_id, $status, $paid) {
	 //get the order
	 $order = $this->get_order($order_id);
	 if (!$order)
		return false;

	 //get old status
	 $payment_info = $order->mp_payment_info;
	 $timestamp = time();
	 $payment_info['status'][$timestamp] = $status;
	 //update post meta
	 update_post_meta($order->ID, 'mp_payment_info', $payment_info);

	 if ($paid) {
		if ($order->post_status == 'order_received') {
			$this->update_order_status($order->ID, 'paid');

			//if paid and the cart is only digital products mark it shipped
				if (is_array($order->mp_cart_info) && $this->download_only_cart($order->mp_cart_info))
					$this->update_order_status($order->ID, 'shipped');
		} else {
			//update payment time if somehow it was skipped
			if (!get_post_meta($order->ID, 'mp_paid_time', true))
			 update_post_meta($order->ID, 'mp_paid_time', time());
		}
	 } else {
		$this->update_order_status($order->ID, 'received');
	 }

	 //return merged payment info
	 return $payment_info;
	}

	//filters wp_mail headers
	function mail($to, $subject, $msg) {

		//remove any other filters
		remove_all_filters( 'wp_mail_from' );
		remove_all_filters( 'wp_mail_from_name' );

		//add our own filters
		add_filter( 'wp_mail_from_name', create_function('', 'return wp_specialchars_decode(get_bloginfo("name"), ENT_QUOTES);') );
		add_filter( 'wp_mail_from', create_function('', '$settings = get_option("mp_settings");return isset($settings["store_email"]) ? $settings["store_email"] : get_option("admin_email");') );

		//convert all newlines and tabs to their approriate html markup
		$msg = str_replace(array("\r\n", "\n", "\t"), array('<br />', '<br />', '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;'), $msg);
		
		return wp_mail($to, $subject, $msg, "Content-Type: text/html; charset=UTF-8");
	}

	//replaces shortcodes in email msgs with dynamic content
	function filter_email($order, $text, $escape = false) {
		global $blog_id; 
		$bid = (is_multisite()) ? $blog_id : 1;

	 // order info
	 if (is_array($order->mp_cart_info) && count($order->mp_cart_info)) {
		$order_info = $order_info_sku = __('Items:', 'mp') . "\n";
		foreach ($order->mp_cart_info as $product_id => $variations) {
			foreach ($variations as $variation => $data) {
				$price = $data['price'] * $data['quantity'];
				if ( $order->mp_discount_info )
					$price = $this->coupon_value_product($order->mp_discount_info['code'], $price, $product_id);
					
				$order_info .= "\t" . $data['name'] . ': ' . number_format_i18n($data['quantity']) . ' * ' . number_format_i18n($price / $data['quantity'], 2) . ' = '. number_format_i18n($price, 2) . ' ' . $order->mp_payment_info['currency'] . "\n";
				$order_info_sku .= "\t" . $data['name'] . ' - ' . $data['sku'] . ': ' . number_format_i18n($data['quantity']) . ' * ' . number_format_i18n($price / $data['quantity'] , 2) . ' = '. number_format_i18n($price, 2) . ' ' . $order->mp_payment_info['currency'] . "\n";

				//show download link if set
				if ($order->post_status != 'order_received' && $download_url = $this->get_download_url($product_id, $order->post_title)) {
				$order_info .= "\t\t" . __('Download: ', 'mp') . $download_url . "\n";
					$order_info_sku .= "\t\t" . __('Download: ', 'mp') . $download_url . "\n";
				}

				// FPM: Product Custom Fields
				$cf_key = $bid .':'. $product_id .':'. $variation;
				if (isset($order->mp_shipping_info['mp_custom_fields'][$cf_key])) {
					$cf_items = $order->mp_shipping_info['mp_custom_fields'][$cf_key];

					$mp_custom_field_label = get_post_meta($product_id, 'mp_custom_field_label', true);
					if (isset($mp_custom_field_label[$variation]))
						$label_text = esc_attr($mp_custom_field_label[$variation]);
					else
						$label_text = __('Product Personalization: ', 'mp');

					$order_info .= "\t\t" . $label_text	 ."\n";
					$order_info_sku .= "\t\t" . $label_text	 ."\n";
					foreach($cf_items as $idx => $cf_item) {
						$item_cnt = intval($idx)+1;
						$order_info .= "\t\t\t" . $item_cnt .". ". $cf_item	."\n";
						$order_info_sku .= "\t\t\t" . $item_cnt .". ". $cf_item	 ."\n";
					}
				}
				$order_info .= "\n";
				$order_info_sku .= "\n";
			}
		}
		
		$order_info .= "\n";
		$order_info_sku .= "\n";
	}
	//coupon line
	if ( $order->mp_discount_info ) {
		$discount = $order->mp_discount_info['code'];
		$order_info .= "\n" . __('Coupon Code:', 'mp') . ' ' . $discount;
		$order_info_sku .= "\n" . __('Coupon Code:', 'mp') . ' ' . $discount;
	}
	
	//shipping line
	if ( $order->mp_shipping_total ) {
		$order_info .= "\n" . __('Shipping:', 'mp') . ' ' . number_format_i18n($this->get_display_shipping($order), 2) . ' ' . $order->mp_payment_info['currency'];
		$order_info_sku .= "\n" . __('Shipping:', 'mp') . ' ' . number_format_i18n($this->get_display_shipping($order), 2) . ' ' . $order->mp_payment_info['currency'];
	}
	
	//tax line
	if ( $order->mp_tax_total ) {
		$order_info .= "\n" . esc_html($this->get_setting('tax->label', __('Taxes', 'mp'))) . ': ' . number_format_i18n((float)$order->mp_tax_total, 2) . ' ' . $order->mp_payment_info['currency'];
		$order_info_sku .= "\n" . esc_html($this->get_setting('tax->label', __('Taxes', 'mp'))) . ': ' . number_format_i18n((float)$order->mp_tax_total, 2) . ' ' . $order->mp_payment_info['currency'];
	}
	
	 //total line
	 $order_info .= "\n" . __('Order Total:', 'mp') . ' ' . number_format_i18n((float)$order->mp_order_total, 2) . ' ' . $order->mp_payment_info['currency'];
		$order_info_sku .= "\n" . __('Order Total:', 'mp') . ' ' . number_format_i18n((float)$order->mp_order_total, 2) . ' ' . $order->mp_payment_info['currency'];

	 //// Shipping Info

		if ((is_array($order->mp_cart_info) && $this->download_only_cart($order->mp_cart_info)) || $this->get_setting('shipping->method') == 'none') { //if the cart is only digital products
			$shipping_info = __('No shipping required for this order.', 'mp');
		} else {
			$shipping_info = __('Full Name:', 'mp') . ' ' . $order->mp_shipping_info['name'];
			$shipping_info .= "\n" . __('Address:', 'mp') . ' ' . $order->mp_shipping_info['address1'];
			if ($order->mp_shipping_info['address2'])
				$shipping_info .= "\n" . __('Address 2:', 'mp') . ' ' . $order->mp_shipping_info['address2'];
			$shipping_info .= "\n" . __('City:', 'mp') . ' ' . $order->mp_shipping_info['city'];
			if (!empty($order->mp_shipping_info['state']))
				$shipping_info .= "\n" . __('State/Province/Region:', 'mp') . ' ' . $order->mp_shipping_info['state'];
			$shipping_info .= "\n" . __('Postal/Zip Code:', 'mp') . ' ' . $order->mp_shipping_info['zip'];
			$shipping_info .= "\n" . __('Country:', 'mp') . ' ' . $order->mp_shipping_info['country'];
			if (!empty($order->mp_shipping_info['phone']))
				$shipping_info .= "\n" . __('Phone Number:', 'mp') . ' ' . $order->mp_shipping_info['phone'];

			// If actually shipped show method, else customer's shipping choice.
			if (isset($order->mp_shipping_info['method']) && $order->mp_shipping_info['method'] != 'other')
				$shipping_info .= "\n" . __('Shipping Method:', 'mp') . ' ' . $order->mp_shipping_info['method'];
			elseif (! empty($order->mp_shipping_info['shipping_option']) )
				$shipping_info .= "\n" . __('Shipping Method:', 'mp') . ' ' . strtoupper($order->mp_shipping_info['shipping_option']) . ' ' .$order->mp_shipping_info['shipping_sub_option'] ;

			if (!empty($order->mp_shipping_info['tracking_num']))
				$shipping_info .= "\n" . __('Tracking Number:', 'mp') . ' ' . $order->mp_shipping_info['tracking_num'];
		}
		
		if (!empty($order->mp_shipping_info['special_instructions']))
			$shipping_info .= "\n" . __('Special Instructions:', 'mp') . ' ' . $order->mp_shipping_info['special_instructions'];
		
		$order_notes = '';
		if (!empty($order->mp_order_notes))
		$order_notes = __('Order Notes:', 'mp') . "\n" . $order->mp_order_notes;

	 //// Payment Info
	 $payment_info = __('Payment Method:', 'mp') . ' ' . $order->mp_payment_info['gateway_public_name'];

		if ($order->mp_payment_info['method'])
	 	$payment_info .= "\n" . __('Payment Type:', 'mp') . ' ' . $order->mp_payment_info['method'];

		if ($order->mp_payment_info['transaction_id'])
			$payment_info .= "\n" . __('Transaction ID:', 'mp') . ' ' . $order->mp_payment_info['transaction_id'];

		$payment_info .= "\n" . __('Payment Total:', 'mp') . ' ' . number_format_i18n((float)$order->mp_payment_info['total'], 2) . ' ' . $order->mp_payment_info['currency'];
	 $payment_info .= "\n\n";
	 if ($order->post_status == 'order_received') {
		$payment_info .= __('Your payment for this order is not yet complete. Here is the latest status:', 'mp') . "\n";
		$statuses = $order->mp_payment_info['status'];
		krsort($statuses); //sort with latest status at the top
		$status = reset($statuses);
		$timestamp = key($statuses);
		$payment_info .= $this->format_date($timestamp) . ': ' . $status;
	 } else {
		$payment_info .= __('Your payment for this order is complete.', 'mp');
	 }

		//total
		$order_total = number_format_i18n((float)$order->mp_payment_info['total'], 2) . ' ' . $order->mp_payment_info['currency'];

	 //tracking URL
		$tracking_url = apply_filters('wpml_marketpress_tracking_url', mp_orderstatus_link(false, true) . $order->post_title . '/');

	 //setup filters
	 $search = array('CUSTOMERNAME', 'ORDERID', 'ORDERINFOSKU', 'ORDERINFO', 'SHIPPINGINFO', 'PAYMENTINFO', 'TOTAL', 'TRACKINGURL', 'ORDERNOTES');
	 $replace = array($order->mp_shipping_info['name'], $order->post_title, $order_info_sku, $order_info, $shipping_info, $payment_info, $order_total, $tracking_url, $order_notes);
		
		//escape for sprintf() if required
		if ($escape) {
			$replace = array_map( create_function('$a', 'return str_replace("%","%%",$a);'), $replace );
		}
		
	 //replace
	 $text = str_replace($search, $replace, $text);

	 return $text;
	}

	//sends email for new orders
	function order_notification($order_id) {

	 //get the order
	 $order = $this->get_order($order_id);
	 if (!$order)
		return false;

		$subject = apply_filters('mp_order_notification_subject', $this->filter_email($order, stripslashes($this->get_setting('email->new_order_subject'))), $order);
		$msg = apply_filters('mp_order_notification_body', $this->filter_email($order, stripslashes($this->get_setting('email->new_order_txt'))), $order);
		$msg = apply_filters('mp_order_notification_' . $_SESSION['mp_payment_method'], $msg, $order );

	 $this->mail($order->mp_shipping_info['email'], $subject, $msg);

	 //send message to admin
	 $subject = __('New Order Notification: ORDERID', 'mp');
	 $msg = __("A new order (ORDERID) was created in your store:

Order Information:
ORDERINFOSKU

Shipping Information:
SHIPPINGINFO

Email: %s

Payment Information:
PAYMENTINFO

You can manage this order here: %s", 'mp');

	 $subject = $this->filter_email($order, $subject);
		$subject = apply_filters( 'mp_order_notification_admin_subject', $subject, $order );
	 $msg = $this->filter_email($order, $msg, true);
		$msg = sprintf($msg, $order->mp_shipping_info['email'], admin_url('edit.php?post_type=product&page=marketpress-orders&order_id=') . $order->ID);
		$msg = apply_filters( 'mp_order_notification_admin_msg', $msg, $order );
	 $store_email = $this->get_setting('store_email') ? $this->get_setting('store_email') : get_option("admin_email");
	 $this->mail($store_email, $subject, $msg);
	}

	//sends email for orders marked as shipped
	function order_shipped_notification($order_id) {

	 //get the order
	 $order = $this->get_order($order_id);
	 if (!$order)
		return false;

		//skip notice for paid download only carts
		if ($this->skip_shipping_notice)
			return false;

	 $subject = apply_filters('mp_shipped_order_notification_subject', stripslashes($this->get_setting('email->shipped_order_subject')), $order);
	 $subject = $this->filter_email($order, $subject);
	 $msg = apply_filters( 'mp_shipped_order_notification_body', stripslashes($this->get_setting('email->shipped_order_txt')), $order );
	 $msg = $this->filter_email($order, $msg);
	 $msg = apply_filters( 'mp_shipped_order_notification', $msg, $order );

	 $this->mail($order->mp_shipping_info['email'], $subject, $msg);

	}

	//sends email to admin for low stock notification
	function low_stock_notification($product_id, $variation, $stock) {

	 //skip if sent already
	 if ( get_post_meta($product_id, 'mp_stock_email_sent', true) != '' && $stock > 0 ) return;
		
	 //don't send an email every time - we set this before doing anything else to avoid race conditions
	 update_post_meta($product_id, 'mp_stock_email_sent', 1);

	 $var_names = maybe_unserialize(get_post_meta($product_id, 'mp_var_name', true));
		if (is_array($var_names) && count($var_names) > 1)
			$name = get_the_title($product_id) . ': ' . $var_names[$variation];
		else
			 $name = get_the_title($product_id);

	 $subject = __('Low Product Inventory Notification', 'mp');
	 $msg = __('This message is being sent to notify you of low stock of a product in your online store according to your preferences.

Product: %s
Current Inventory: %s
Link: %s

Edit Product: %s
Notification Preferences: %s', 'mp');
	 $msg = sprintf($msg, $name, number_format_i18n($stock), get_permalink($product_id), get_edit_post_link($product_id), admin_url('edit.php?post_type=product&page=marketpress#mp-inventory-setting'));
	 $msg = apply_filters( 'mp_low_stock_notification', $msg, $product_id );
	 $store_email = $this->get_setting('store_email') ? $this->get_setting('store_email') : get_option("admin_email");
	 $this->mail($store_email, $subject, $msg);
	}

	//round and display currency with padded zeros
	function display_currency( $amount ) {

	 if ( $this->get_setting('curr_decimal') === '0' )
		return number_format( round( $amount ), 0, '.', '');
	 else
		return number_format( round( $amount, 2 ), 2, '.', '');
	}

	//display currency symbol
	//MP3
	function format_currency($currency = '', $amount = false) {

	 if (!$currency)
		$currency = $this->get_setting('currency', 'USD');

	 // get the currency symbol
	 $symbol = $this->currencies[$currency][1];
	 // if many symbols are found, rebuild the full symbol
	 $symbols = explode(', ', $symbol);
	 if (is_array($symbols)) {
		$symbol = "";
		foreach ($symbols as $temp) {
			$symbol .= '&#x'.$temp.';';
		}
	 } else {
		$symbol = '&#x'.$symbol.';';
	 }

		//check decimal option
	 if ( $this->get_setting('curr_decimal') === '0' ) {
		$decimal_place = 0;
		$zero = '0';
		} else {
		$decimal_place = 2;
		$zero = '0.00';
		}

	 //format currency amount according to preference
	 if ($amount) {

		if ($this->get_setting('curr_symbol_position') == 1 || !$this->get_setting('curr_symbol_position'))
			return $symbol . number_format_i18n($amount, $decimal_place);
		else if ($this->get_setting('curr_symbol_position') == 2)
			return $symbol . ' ' . number_format_i18n($amount, $decimal_place);
		else if ($this->get_setting('curr_symbol_position') == 3)
			return number_format_i18n($amount, $decimal_place) . $symbol;
		else if ($this->get_setting('curr_symbol_position') == 4)
			return number_format_i18n($amount, $decimal_place) . ' ' . $symbol;

	 } else if ($amount === false) {
		return $symbol;
	 } else {
		if ($this->get_setting('curr_symbol_position') == 1 || !$this->get_setting('curr_symbol_position'))
			return $symbol . $zero;
		else if ($this->get_setting('curr_symbol_position') == 2)
			return $symbol . ' ' . $zero;
		else if ($this->get_setting('curr_symbol_position') == 3)
			return $zero . $symbol;
		else if ($this->get_setting('curr_symbol_position') == 4)
			return $zero . ' ' . $symbol;
	 }
	}
	
	//translates a gmt timestamp into local timezone for display
	function format_date($gmt_timestamp) {
		return date_i18n( get_option('date_format') . ' - ' . get_option('time_format'), $gmt_timestamp + ( get_option( 'gmt_offset' ) * HOUR_IN_SECONDS ) );
	}
	
	//replaces wp_trim_excerpt in our custom loops
	//MP3
	function product_excerpt($excerpt, $content, $product_id, $excerpt_more = null) {
	 if (is_null($excerpt_more))
			$excerpt_more = ' <a class="mp_product_more_link" href="' . get_permalink($product_id) . '">' .	 __('More Info &raquo;', 'mp') . '</a>';
	 if ($excerpt) {
		return apply_filters('get_the_excerpt', $excerpt) . $excerpt_more;
	 } else {
			$text = strip_shortcodes( $content );
			//$text = apply_filters('the_content', $text);
			$text = str_replace(']]>', ']]&gt;', $text);
			$text = strip_tags($text);
			$excerpt_length = apply_filters('excerpt_length', 55);
			$words = preg_split("/[\n\r\t ]+/", $text, $excerpt_length + 1, PREG_SPLIT_NO_EMPTY);
			if ( count($words) > $excerpt_length ) {
				array_pop($words);
				$text = implode(' ', $words);
				$text = $text . $excerpt_more;
			} else {
				$text = implode(' ', $words);
			}
		}
		return $text;
	}

	//returns the js needed to record ecommerce transactions. $project should be an array of id, title
	function create_ga_ecommerce($order) {

		if (!is_object($order))
			 return false;

		//so that certain products can be excluded from tracking
		$order = apply_filters( 'mp_ga_ecommerce', $order );

	 if ($this->get_setting('ga_ecommerce') == 'old') {

			$js = '<script type="text/javascript">
			try{
			 pageTracker._addTrans(
					"'.esc_js($order->post_title).'",							 // order ID - required
					"'.esc_js(get_bloginfo('blogname')).'",					 // affiliation or store name
					"'.$order->mp_order_total.'",								 // total - required
					"'.$order->mp_tax_total.'",									 // tax
					"'.$order->mp_shipping_total.'",							 // shipping
					"'.esc_js($order->mp_shipping_info['city']).'",		// city
					"'.esc_js($order->mp_shipping_info['state']).'",		 // state or province
					"'.esc_js($order->mp_shipping_info['country']).'"	 // country
				);';

			if (is_array($order->mp_cart_info) && count($order->mp_cart_info)) {
				foreach ($order->mp_cart_info as $product_id => $variations) {
					foreach ($variations as $variation => $data) {
						$sku = !empty($data['SKU']) ? esc_js($data['SKU']) : $product_id;
						$js .= 'pageTracker._addItem(
							"'.esc_js($order->post_title).'", // order ID - necessary to associate item with transaction
							"'.$sku.'",									 // SKU/code - required
							"'.esc_js($data['name']).'",		// product name
							"'.$data['price'].'",						// unit price - required
							"'.$data['quantity'].'"					 // quantity - required
						);';
					}
				}
			}
			 $js .= 'pageTracker._trackTrans(); //submits transaction to the Analytics servers
			} catch(err) {}
			</script>
			';

		} else if ($this->get_setting('ga_ecommerce') == 'new') {

		$js = '<script type="text/javascript">
				_gaq.push(["_addTrans",
					"'.esc_attr($order->post_title).'",						 // order ID - required
					"'.esc_attr(get_bloginfo('blogname')).'",				 // affiliation or store name
					"'.$order->mp_order_total.'",								 // total - required
					"'.$order->mp_tax_total.'",									 // tax
					"'.$order->mp_shipping_total.'",							 // shipping
					"'.esc_attr($order->mp_shipping_info['city']).'",	 // city
					"'.esc_attr($order->mp_shipping_info['state']).'",	 // state or province
					"'.esc_attr($order->mp_shipping_info['country']).'"	 // country
				]);';

			if (is_array($order->mp_cart_info) && count($order->mp_cart_info)) {
				foreach ($order->mp_cart_info as $product_id => $variations) {
					foreach ($variations as $variation => $data) {
						$sku = !empty($data['SKU']) ? esc_attr($data['SKU']) : $product_id;
						$js .= '_gaq.push(["_addItem",
							"'.esc_attr($order->post_title).'", // order ID - necessary to associate item with transaction
							"'.$sku.'",									 // SKU/code - required
							"'.esc_attr($data['name']).'",			// product name
							"",												// category
							"'.$data['price'].'",						// unit price - required
							"'.$data['quantity'].'"					 // quantity - required
						]);';
					}
				}
			}
			 $js .= '_gaq.push(["_trackTrans"]);
			</script>
			';
			
			//add info for subblog if our GA plugin is installed
			if (class_exists('Google_Analytics_Async')) {
				
				$js = '<script type="text/javascript">
					_gaq.push(["b._addTrans",
						"'.esc_attr($order->post_title).'",							 // order ID - required
						"'.esc_attr(get_bloginfo('blogname')).'",					 // affiliation or store name
						"'.$order->mp_order_total.'",									 // total - required
						"'.$order->mp_tax_total.'",									 // tax
						"'.$order->mp_shipping_total.'",								 // shipping
						"'.esc_attr($order->mp_shipping_info['city']).'",		// city
						"'.esc_attr($order->mp_shipping_info['state']).'",		 // state or province
						"'.esc_attr($order->mp_shipping_info['country']).'"	 // country
					]);';

				if (is_array($order->mp_cart_info) && count($order->mp_cart_info)) {
					foreach ($order->mp_cart_info as $product_id => $variations) {
						foreach ($variations as $variation => $data) {
							$sku = !empty($data['SKU']) ? esc_attr($data['SKU']) : $product_id;
							$js .= '_gaq.push(["b._addItem",
								"'.esc_attr($order->post_title).'", // order ID - necessary to associate item with transaction
								"'.$sku.'",									 // SKU/code - required
								"'.esc_attr($data['name']).'",			// product name
								"",												// category
								"'.$data['price'].'",						// unit price - required
								"'.$data['quantity'].'"					 // quantity - required
							]);';
						}
					}
				}
				$js .= '_gaq.push(["b._trackTrans"]);
				</script>
				';
			}

		} else if( $this->get_setting('ga_ecommerce') == 'universal' ) {
			// add the UA code
			
			$js = '<script type="text/javascript">
					ga("require", "ecommerce", "ecommerce.js");
					ga("ecommerce:addTransaction", {
							"id": "'.esc_attr($order->post_title).'",						// Transaction ID. Required.
							"affiliation": "'.esc_attr(get_bloginfo('blogname')).'",	// Affiliation or store name.
							"revenue": "'.$order->mp_order_total.'",						// Grand Total.
							"shipping": "'.$order->mp_shipping_total.'",					// Shipping.
							"tax": "'.$order->mp_tax_total.'"							 		// Tax.
						});';
					//loop the items
					if (is_array($order->mp_cart_info) && count($order->mp_cart_info)) {
						foreach ($order->mp_cart_info as $product_id => $variations) {
							foreach ($variations as $variation => $data) {
								$sku = !empty($data['SKU']) ? esc_attr($data['SKU']) : $product_id;
								$js .= 'ga("ecommerce:addItem", {
												 "id": "'.esc_attr($order->post_title).'", // Transaction ID. Required.
												 "name": "'.esc_attr($data['name']).'",	 // Product name. Required.
												 "sku": "'.$sku.'",								// SKU/code.
												 "category": "",			 					// Category or variation.
												 "price": "'.$data['price'].'",				 // Unit price.
												 "quantity": "'.$data['quantity'].'"		 // Quantity.
											});';
							}
						}
					}
			
			$js .='ga("ecommerce:send");</script>';
		}

		//add to footer
		if ( !empty($js) ) {
			 $function = "echo '$js';";
		add_action( 'wp_footer', create_function('', $function), 99999 );
		}
	}

	//displays the detail page of an order
	function single_order_page() {
	 $order = $this->get_order((int)$_GET['order_id']);

	 if ( !$order )
		wp_die(__('Invalid Order ID', 'mp'));

		$max_downloads = $this->get_setting('max_downloads', 5);

		//save tracking number
		if (isset($_POST['mp_tracking_number'])) {
			$order->mp_shipping_info['tracking_num'] = stripslashes(trim($_POST['mp_tracking_number']));
			$order->mp_shipping_info['method'] = stripslashes(trim($_POST['mp_shipping_method']));
			update_post_meta($order->ID, 'mp_shipping_info', $order->mp_shipping_info);

			if (isset($_POST['add-tracking-shipped'])) {
				$this->update_order_status($order->ID, 'shipped');
				$order->post_status = 'order_shipped';
				?><div class="updated fade"><p><?php _e('This order has been marked as Shipped.', 'mp'); ?></p></div><?php
			}

			if (!current_user_can('unfiltered_html'))
				$_POST['mp_order_notes'] = wp_filter_post_kses(trim(stripslashes($_POST['mp_order_notes'])));

			$order->mp_order_notes = stripslashes($_POST['mp_order_notes']);
			update_post_meta($order->ID, 'mp_order_notes', $_POST['mp_order_notes']);
			?><div class="updated fade"><p><?php _e('Order details have been saved!', 'mp'); ?></p></div><?php
		}
	 ?>
	 <div class="wrap">
	 <div class="icon32"><img src="<?php echo $this->plugin_url . 'images/shopping-cart.png'; ?>" /></div>
	 <h2><?php echo sprintf(__('Order Details (%s)', 'mp'), esc_attr($order->post_title)); ?></h2>

	 <div id="poststuff" class="metabox-holder mp-settings has-right-sidebar">

	 <div id="side-info-column" class="inner-sidebar">
	 <div id='side-sortables' class='meta-box-sortables'>

		<div id="submitdiv" class="postbox mp-order-actions">
			<h3 class='hndle'><span><?php _e('Order Actions', 'mp'); ?></span></h3>
			<div class="inside">
			<div id="submitpost" class="submitbox">
			<div class="misc-pub-section"><strong><?php _e('Change Order Status:', 'mp'); ?></strong></div>
			<?php
			$actions = array();
			if ($order->post_status == 'order_received') {
			 $actions['received current'] = __('Received', 'mp');
			 $actions['paid'] = "<a title='" . esc_attr(__('Mark as Paid', 'mp')) . "' href='" . wp_nonce_url( admin_url( 'edit.php?post_type=product&amp;page=marketpress-orders&amp;action=paid&amp;post=' . $order->ID), 'update-order-status' ) . "'>" . __('Paid', 'mp') . "</a>";
			 $actions['shipped'] = "<a title='" . esc_attr(__('Mark as Shipped', 'mp')) . "' href='" . wp_nonce_url( admin_url( 'edit.php?post_type=product&amp;page=marketpress-orders&amp;action=shipped&amp;post=' . $order->ID), 'update-order-status' ) . "'>" . __('Shipped', 'mp') . "</a>";
			 $actions['closed'] = "<a title='" . esc_attr(__('Mark as Closed', 'mp')) . "' href='" . wp_nonce_url( admin_url( 'edit.php?post_type=product&amp;page=marketpress-orders&amp;action=closed&amp;post=' . $order->ID), 'update-order-status' ) . "'>" . __('Closed', 'mp') . "</a>";
			 $actions['trash'] = "<a title='" . esc_attr(__('Trash', 'mp')) . "' href='" . wp_nonce_url( admin_url( 'edit.php?post_type=product&amp;page=marketpress-orders&amp;action=trash&amp;post=' . $order->ID), 'update-order-status' ) . "'>" . __('Trash', 'mp') . "</a>";
			} else if ($order->post_status == 'order_paid') {
			 $actions['received'] = __('Received', 'mp');
			 $actions['paid current'] = __('Paid', 'mp');
			 $actions['shipped'] = "<a title='" . esc_attr(__('Mark as Shipped', 'mp')) . "' href='" . wp_nonce_url( admin_url( 'edit.php?post_type=product&amp;page=marketpress-orders&amp;action=shipped&amp;post=' . $order->ID), 'update-order-status' ) . "'>" . __('Shipped', 'mp') . "</a>";
			 $actions['closed'] = "<a title='" . esc_attr(__('Mark as Closed', 'mp')) . "' href='" . wp_nonce_url( admin_url( 'edit.php?post_type=product&amp;page=marketpress-orders&amp;action=closed&amp;post=' . $order->ID), 'update-order-status' ) . "'>" . __('Closed', 'mp') . "</a>";
			 $actions['trash'] = "<a title='" . esc_attr(__('Trash', 'mp')) . "' href='" . wp_nonce_url( admin_url( 'edit.php?post_type=product&amp;page=marketpress-orders&amp;action=trash&amp;post=' . $order->ID), 'update-order-status' ) . "'>" . __('Trash', 'mp') . "</a>";
			} else if ($order->post_status == 'order_shipped') {
			 $actions['received'] = __('Received', 'mp');
			 $actions['paid'] = __('Paid', 'mp');
			 $actions['shipped current'] = __('Shipped', 'mp');
			 $actions['closed'] = "<a title='" . esc_attr(__('Mark as Closed', 'mp')) . "' href='" . wp_nonce_url( admin_url( 'edit.php?post_type=product&amp;page=marketpress-orders&amp;action=closed&amp;post=' . $order->ID), 'update-order-status' ) . "'>" . __('Closed', 'mp') . "</a>";
			 $actions['trash'] = "<a title='" . esc_attr(__('Trash', 'mp')) . "' href='" . wp_nonce_url( admin_url( 'edit.php?post_type=product&amp;page=marketpress-orders&amp;action=trash&amp;post=' . $order->ID), 'update-order-status' ) . "'>" . __('Trash', 'mp') . "</a>";
			} else if ($order->post_status == 'order_closed') {
			 $actions['received'] = "<a title='" . esc_attr(__('Mark as Received', 'mp')) . "' href='" . wp_nonce_url( admin_url( 'edit.php?post_type=product&amp;page=marketpress-orders&amp;action=received&amp;post=' . $order->ID), 'update-order-status' ) . "'>" . __('Received', 'mp') . "</a>";
			 $actions['paid'] = "<a title='" . esc_attr(__('Mark as Paid', 'mp')) . "' href='" . wp_nonce_url( admin_url( 'edit.php?post_type=product&amp;page=marketpress-orders&amp;action=paid&amp;post=' . $order->ID), 'update-order-status' ) . "'>" . __('Paid', 'mp') . "</a>";
			 $actions['shipped'] = "<a title='" . esc_attr(__('Mark as Shipped', 'mp')) . "' href='" . wp_nonce_url( admin_url( 'edit.php?post_type=product&amp;page=marketpress-orders&amp;action=shipped&amp;post=' . $order->ID), 'update-order-status' ) . "'>" . __('Shipped', 'mp') . "</a>";
			 $actions['closed current'] = __('Closed', 'mp');
			 $actions['trash'] = "<a title='" . esc_attr(__('Trash', 'mp')) . "' href='" . wp_nonce_url( admin_url( 'edit.php?post_type=product&amp;page=marketpress-orders&amp;action=trash&amp;post=' . $order->ID), 'update-order-status' ) . "'>" . __('Trash', 'mp') . "</a>";
			} else if ($order->post_status == "trash") {
			 $actions['received'] = "<a title='" . esc_attr(__('Mark as Received', 'mp')) . "' href='" . wp_nonce_url( admin_url( 'edit.php?post_type=product&amp;page=marketpress-orders&amp;action=received&amp;post=' . $order->ID), 'update-order-status' ) . "'>" . __('Received', 'mp') . "</a>";
			 $actions['paid'] = "<a title='" . esc_attr(__('Mark as Paid', 'mp')) . "' href='" . wp_nonce_url( admin_url( 'edit.php?post_type=product&amp;page=marketpress-orders&amp;action=paid&amp;post=' . $order->ID), 'update-order-status' ) . "'>" . __('Paid', 'mp') . "</a>";
			 $actions['shipped'] = "<a title='" . esc_attr(__('Mark as Shipped', 'mp')) . "' href='" . wp_nonce_url( admin_url( 'edit.php?post_type=product&amp;page=marketpress-orders&amp;action=shipped&amp;post=' . $order->ID), 'update-order-status' ) . "'>" . __('Shipped', 'mp') . "</a>";
			 $actions['closed'] = "<a title='" . esc_attr(__('Mark as Closed', 'mp')) . "' href='" . wp_nonce_url( admin_url( 'edit.php?post_type=product&amp;page=marketpress-orders&amp;action=closed&amp;post=' . $order->ID), 'update-order-status' ) . "'>" . __('Closed', 'mp') . "</a>";
			 $actions['delete'] = "<a title='" . esc_attr(__('Delete', 'mp')) . "' href='" . wp_nonce_url( admin_url( 'edit.php?post_type=product&amp;page=marketpress-orders&amp;action=delete&amp;post=' . $order->ID), 'update-order-status' ) . "'>" . __('Delete', 'mp') . "</a>";
		
		}

			$action_count = count($actions);
			$i = 0;
				echo '<div id="mp-single-statuses" class="misc-pub-section">';
				foreach ( $actions as $action => $link ) {
					++$i;
					( $i == $action_count ) ? $sep = '' : $sep = ' &raquo; ';
					echo "<span class='$action'>$link</span>$sep";
				}
				echo '</div>';
			?>

			 <div id="major-publishing-actions">
						<form id="mp-single-order-form" action="<?php echo admin_url('edit.php'); ?>" method="get">
				<div id="mp-single-order-buttons">
					<input type="hidden" name="post_type" class="post_status_page" value="product" />
					<input type="hidden" name="page" class="post_status_page" value="marketpress-orders" />
					<input name="save" class="button-primary" id="publish" tabindex="1" value="<?php _e('&laquo; Back', 'mp'); ?>" type="submit" />
				</div>
						</form>
				<div class="clear"></div>
			 </div>
			</div>
			</div>
		</div>

		<div id="mp-order-status" class="postbox">
			<h3 class='hndle'><span><?php _e('Current Status', 'mp'); ?></span></h3>
			<div class="inside">
			 <?php
			 //get times
			 $received = $this->format_date($order->mp_received_time);
			 if (isset($order->mp_paid_time) && $order->mp_paid_time)
				$paid = $this->format_date($order->mp_paid_time);
			 if (isset($order->mp_shipped_time) && $order->mp_shipped_time)
				$shipped = $this->format_date($order->mp_shipped_time);
			 if (isset($order->mp_closed_time) && $order->mp_closed_time)
				$closed = $this->format_date($order->mp_closed_time);

			 if ($order->post_status == 'order_received') {
				echo '<div id="major-publishing-actions" class="misc-pub-section">' . __('Received:', 'mp') . ' <strong>' . $received . '</strong></div>';
			 } else if ($order->post_status == 'order_paid') {
				echo '<div id="major-publishing-actions" class="misc-pub-section">' . __('Paid:', 'mp') . ' <strong>' . $paid . '</strong></div>';
				echo '<div class="misc-pub-section">' . __('Received:', 'mp') . ' <strong>' . $received . '</strong></div>';
			 } else if ($order->post_status == 'order_shipped') {
				echo '<div id="major-publishing-actions" class="misc-pub-section">' . __('Shipped:', 'mp') . ' <strong>' . $shipped . '</strong></div>';
				echo '<div class="misc-pub-section">' . __('Paid:', 'mp') . ' <strong>' . $paid . '</strong></div>';
				echo '<div class="misc-pub-section">' . __('Received:', 'mp') . ' <strong>' . $received . '</strong></div>';
			 } else if ($order->post_status == 'order_closed') {
				echo '<div id="major-publishing-actions" class="misc-pub-section">' . __('Closed:', 'mp') . ' <strong>' . $closed . '</strong></div>';
				echo '<div class="misc-pub-section">' . __('Shipped:', 'mp') . ' <strong>' . $shipped . '</strong></div>';
				echo '<div class="misc-pub-section">' . __('Paid:', 'mp') . ' <strong>' . $paid . '</strong></div>';
				echo '<div class="misc-pub-section">' . __('Received:', 'mp') . ' <strong>' . $received . '</strong></div>';
				} else if ($order->post_status == 'trash') {
				echo '<div id="major-publishing-actions" class="misc-pub-section">' . __('Trashed', 'mp') . '</div>';
			 }

			 ?>
			</div>
		</div>

		<div id="mp-order-payment" class="postbox">
			<h3 class='hndle'><span><?php _e('Payment Information', 'mp'); ?></span></h3>
			<div class="inside">
			 <div id="mp_payment_gateway" class="misc-pub-section">
				<?php _e('Payment Gateway:', 'mp'); ?>
				<strong><?php echo $order->mp_payment_info['gateway_private_name']; ?></strong>
			 </div>
					<?php if ($order->mp_payment_info['method']) { ?>
			 <div id="mp_payment_method" class="misc-pub-section">
				<?php _e('Payment Type:', 'mp'); ?>
				<strong><?php echo $order->mp_payment_info['method']; ?></strong>
			 </div>
			 <?php } ?>
			 <?php if ($order->mp_payment_info['transaction_id']) { ?>
			 <div id="mp_transaction" class="misc-pub-section">
				<?php _e('Transaction ID:', 'mp'); ?>
				<strong><?php echo $order->mp_payment_info['transaction_id']; ?></strong>
			 </div>
			 <?php } ?>
			 <div id="major-publishing-actions" class="misc-pub-section">
				<?php _e('Payment Total:', 'mp'); ?>
				<strong><?php echo $this->format_currency($order->mp_payment_info['currency'], $order->mp_payment_info['total']) . ' ' . $order->mp_payment_info['currency']; ?></strong>
			 </div>
			</div>
		</div>

		<?php if (is_array($order->mp_payment_info['status']) && count($order->mp_payment_info['status'])) { ?>
		<div id="mp-order-payment-history" class="postbox">
			<h3 class='hndle'><span><?php _e('Payment Transaction History', 'mp'); ?></span></h3>
			<div class="inside">
			<?php
			$statuses = $order->mp_payment_info['status'];
			krsort($statuses); //sort with latest status at the top
			$first = true;
			foreach ($statuses as $timestamp => $status) {
			 if ($first) {
				echo '<div id="major-publishing-actions" class="misc-pub-section">';
				$first = false;
			 } else {
				echo '<div id="mp_payment_gateway" class="misc-pub-section">';
			 }
			 ?>
				<strong><?php echo $this->format_date($timestamp); ?>:</strong>
				<?php echo esc_html($status); ?>
			 </div>
			<?php } ?>

			</div>
		</div>
		<?php } ?>

	 </div></div>

	 <div id="post-body">
	 <div id="post-body-content">

	 <div id='normal-sortables' class='meta-box-sortables'>

		<div id="mp-order-products" class="postbox">
			<h3 class='hndle'><span><?php _e('Order Information', 'mp'); ?></span></h3>
			<div class="inside">

			<table id="mp-order-product-table" class="widefat">
			 <thead><tr>
				<th class="mp_cart_col_thumb">&nbsp;</th>
				<th class="mp_cart_col_sku"><?php _e('SKU', 'mp'); ?></th>
				<th class="mp_cart_col_product"><?php _e('Item', 'mp'); ?></th>
				<th class="mp_cart_col_quant"><?php _e('Quantity', 'mp'); ?></th>
				<th class="mp_cart_col_price"><?php _e('Price', 'mp'); ?></th>
				<th class="mp_cart_col_subtotal"><?php _e('Subtotal', 'mp'); ?></th>
				<th class="mp_cart_col_downloads"><?php _e('Downloads', 'mp'); ?></th>
			 </tr></thead>
			 <tbody>
			 <?php
					global $blog_id;
					$bid = (is_multisite()) ? $blog_id : 1; // FPM
					$coupon_code = is_array($order->mp_discount_info) ? $order->mp_discount_info['code'] : '';
										

			 if (is_array($order->mp_cart_info) && count($order->mp_cart_info)) {
				foreach ($order->mp_cart_info as $product_id => $variations) {
							//for compatibility for old orders from MP 1.0
							if (isset($variations['name'])) {
								$data = $variations;
								$price = $data['price'] * $data['quantity'];
								$discount_price = $this->coupon_value_product($coupon_code, $price, $product_id);
								$price_text = '';
								$subtotal_text = '';
								
								//price text
								if ( $price != $discount_price ) {
									$price_text = '<del>' . $this->format_currency('', $price / $data['quantity']) . '</del><br />';
								}
								$price_text .= $this->format_currency('', $discount_price / $data['quantity']);
	
								//subtotal text
								if ( $price != $discount_price ) {
									$subtotal_text .= '<del>' . $this->format_currency('', $price) . '</del><br />';
								}
								$subtotal_text .= $this->format_currency('', $discount_price);
						
					 echo '<tr>';
						echo '	<td class="mp_cart_col_thumb">' . mp_product_image( false, 'widget', $product_id ) . '</td>';
						echo '	<td class="mp_cart_col_sku">' . esc_attr($data['SKU']) . '</td>';
						echo '	<td class="mp_cart_col_product"><a href="' . get_permalink($product_id) . '">' . esc_attr($data['name']) . '</a></td>';
						echo '	<td class="mp_cart_col_quant">' . number_format_i18n($data['quantity']) . '</td>';
						echo '	<td class="mp_cart_col_price">' . $price_text . '</td>';
						echo '	<td class="mp_cart_col_subtotal">' . $subtotal_text . '</td>';
						echo '	<td class="mp_cart_col_downloads">' . __('N/A', 'mp') . '</td>';
						echo '</tr>';
							} else {
								foreach ($variations as $variation => $data) {
									$price = $data['price'] * $data['quantity'];
									$discount_price = $this->coupon_value_product($coupon_code, $price, $product_id);
									$price_text = '';
									$subtotal_text = '';
									
									//price text
									if ( $price != $discount_price ) {
										$price_text = '<del>' . $this->format_currency('', $price / $data['quantity']) . '</del><br />';
									}
									$price_text .= $this->format_currency('', $discount_price / $data['quantity']);
		
									//subtotal text
									if ( $price != $discount_price ) {
										$subtotal_text .= '<del>' . $this->format_currency('', $price) . '</del><br />';
									}
									$subtotal_text .= $this->format_currency('', $discount_price);
								
							 echo '<tr>';
							 echo '	<td class="mp_cart_col_thumb">' . mp_product_image( false, 'widget', $product_id ) . '</td>';
							 echo '	<td class="mp_cart_col_sku">' . esc_attr($data['SKU']) . '</td>';
							 echo '	<td class="mp_cart_col_product"><a href="' . get_permalink($product_id) . '">' . esc_attr($data['name']) . '</a>';

									//Output product custom field information
									$cf_key = $bid .':'. $product_id .':'. $variation;
									if (isset($order->mp_shipping_info['mp_custom_fields'][$cf_key])) {
										$cf_item = $order->mp_shipping_info['mp_custom_fields'][$cf_key];
			
										$mp_custom_field_label = get_post_meta($product_id, 'mp_custom_field_label', true);
										if (isset($mp_custom_field_label[$variation]))
											$label_text = esc_attr($mp_custom_field_label[$variation]);
										else
											$label_text = __('Product Personalization:', 'mp');
			
										echo '<div class="mp_cart_custom_fields">'. $label_text .'<ol>';
										foreach ($cf_item as $item) {
											echo '<li>'. $item .'</li>';
										}
										echo '</ol></div>';
									}
			
									echo '</td>';
							 echo '	<td class="mp_cart_col_quant">' . number_format_i18n($data['quantity']) . '</td>';
							 echo '	<td class="mp_cart_col_price">' . $price_text . '</td>';
							 echo '	<td class="mp_cart_col_subtotal">' . $subtotal_text . '</td>';
									if (is_array($data['download']))
										 echo '	 <td class="mp_cart_col_downloads">' . number_format_i18n($data['download']['downloaded']) . (($data['download']['downloaded'] >= $max_downloads) ? __(' (Limit Reached)', 'mp') : '')	 . '</td>';
									else
										echo '	<td class="mp_cart_col_downloads">' . __('N/A', 'mp') . '</td>';
							 echo '</tr>';
								}
							}
				}
			 } else {
				echo '<tr><td colspan="7">' . __('No products could be found for this order', 'mp') . '</td></tr>';
			 }
			 ?>
			 </tbody>
			</table><br />

			<?php //coupon line
			if ( isset($order->mp_discount_info) ) { ?>
			<h3><?php _e('Coupon Discount:', 'mp'); ?></h3>
			<p><?php echo $order->mp_discount_info['discount']; ?> (<?php echo $order->mp_discount_info['code']; ?>)</p>
			<?php } ?>

			<?php //shipping line
			if ( $order->mp_shipping_total ) { ?>
			<h3><?php _e('Shipping:', 'mp'); ?></h3>
			<p><?php echo $this->format_currency('', $order->mp_shipping_total) . ' ( ' . strtoupper(isset($order->mp_shipping_info['shipping_option']) ? $order->mp_shipping_info['shipping_option'] : '') . ' ' .	 (isset($order->mp_shipping_info['shipping_sub_option']) ? $order->mp_shipping_info['shipping_sub_option'] : '') . ' )'; ?></p>
			<?php } ?>

			<?php //tax line
			if ( $order->mp_tax_total || $this->get_setting('tax->tax_inclusive') ) { ?>
			<h3><?php echo esc_html($this->get_setting('tax->label', __('Taxes', 'mp'))); ?>:</h3>
			<p><?php echo !$this->get_setting('tax->tax_inclusive') ? $this->format_currency('', $order->mp_tax_total) : $this->format_currency('', $order->mp_order_total * $this->get_setting('tax->rate')) . ' ' . __('(inclusive)', 'mp'); ?></p>
			<?php } ?>

			<h3><?php _e('Cart Total:', 'mp'); ?></h3>
			<p><?php echo $this->format_currency('', $order->mp_order_total); ?></p>

				<?php //special instructions line
			if ( !empty($order->mp_shipping_info['special_instructions']) ) { ?>
			<h3><?php _e('Special Instructions:', 'mp'); ?></h3>
			<p><?php echo wpautop(esc_html($order->mp_shipping_info['special_instructions'])); ?></p>
			<?php } ?>

			</div>
		</div>

			<form id="mp-shipping-form" action="" method="post">
		<div id="mp-order-shipping-info" class="postbox">
			<h3 class='hndle'><span><?php _e('Shipping Information', 'mp'); ?></span></h3>
			<div class="inside">
			 <h3><?php _e('Address:', 'mp'); ?></h3>
			 <table>
				<tr>
			 	<td align="right"><?php _e('Full Name:', 'mp'); ?></td><td>
				<?php esc_attr_e($order->mp_shipping_info['name']); ?></td>
			 	</tr>

				<tr>
			 	<td align="right"><?php _e('Email:', 'mp'); ?></td><td>
				<?php esc_attr_e($order->mp_shipping_info['email']); ?></td>
			 	</tr>

			 	<tr>
			 	<td align="right"><?php _e('Address:', 'mp'); ?></td>
				<td><?php esc_attr_e($order->mp_shipping_info['address1']); ?></td>
			 	</tr>

				<?php if ($order->mp_shipping_info['address2']) { ?>
			 	<tr>
			 	<td align="right"><?php _e('Address 2:', 'mp'); ?></td>
				<td><?php esc_attr_e($order->mp_shipping_info['address2']); ?></td>
			 	</tr>
				<?php } ?>

			 	<tr>
			 	<td align="right"><?php _e('City:', 'mp'); ?></td>
				<td><?php esc_attr_e($order->mp_shipping_info['city']); ?></td>
			 	</tr>

			 	<?php if ($order->mp_shipping_info['state']) { ?>
			 	<tr>
			 	<td align="right"><?php _e('State/Province/Region:', 'mp'); ?></td>
				<td><?php esc_attr_e($order->mp_shipping_info['state']); ?></td>
			 	</tr>
				<?php } ?>

			 	<tr>
			 	<td align="right"><?php _e('Postal/Zip Code:', 'mp'); ?></td>
				<td><?php esc_attr_e($order->mp_shipping_info['zip']); ?></td>
			 	</tr>

			 	<tr>
			 	<td align="right"><?php _e('Country:', 'mp'); ?></td>
				<td><?php echo $this->countries[$order->mp_shipping_info['country']]; ?></td>
			 	</tr>

				<?php if ($order->mp_shipping_info['phone']) { ?>
			 	<tr>
			 	<td align="right"><?php _e('Phone Number:', 'mp'); ?></td>
				<td><?php esc_attr_e($order->mp_shipping_info['phone']); ?></td>
			 	</tr>
				<?php } ?>
			 </table>

			 <h3><?php _e('Cost:', 'mp'); ?></h3>
			 <p><?php echo $this->format_currency('', $order->mp_shipping_total) . ' ( ' . strtoupper(isset($order->mp_shipping_info['shipping_option']) ? $order->mp_shipping_info['shipping_option'] : '') . ' ' .	 (isset($order->mp_shipping_info['shipping_sub_option']) ? $order->mp_shipping_info['shipping_sub_option'] : '') . ' )'; ?></p>

			 <h3><?php _e('Shipping Method & Tracking Number:', 'mp'); ?></h3>
			 <p>
					<select name="mp_shipping_method">
						<option value="other"><?php _e('Choose Method:', 'mp'); ?></option>
						<option value="UPS"<?php selected(@$order->mp_shipping_info['method'], 'UPS'); ?>>UPS</option>
						<option value="FedEx"<?php selected(@$order->mp_shipping_info['method'], 'FedEx'); ?>>FedEx</option>
						<option value="USPS"<?php selected(@$order->mp_shipping_info['method'], 'USPS'); ?>>USPS</option>
						<option value="DHL"<?php selected(@$order->mp_shipping_info['method'], 'DHL'); ?>>DHL</option>
						<option value="other"<?php selected(@$order->mp_shipping_info['method'], 'other'); ?>><?php _e('Other', 'mp'); ?></option>
						<?php do_action('mp_shipping_tracking_select', @$order->mp_shipping_info['method']); ?>
					</select>
					<input type="text" name="mp_tracking_number" value="<?php esc_attr(isset($order->mp_shipping_info['tracking_num']) ? $order->mp_shipping_info['tracking_num'] : ''); ?>" size="25" />
					<input type="submit" class="button-secondary" name="add-tracking" value="<?php _e('Save &raquo;', 'mp'); ?>" /><?php if ($order->post_status == 'order_received' ||$order->post_status == 'order_paid') { ?> <input type="submit" class="button-secondary" name="add-tracking-shipped" value="<?php _e('Save & Mark as Shipped &raquo;', 'mp'); ?>" /><?php } ?>
					</p>

			 <?php //note line if set by gateway
			 if ( $order->mp_payment_info['note'] ) { ?>
			 <h3><?php _e('Special Note:', 'mp'); ?></h3>
			 <p><?php esc_html_e($order->mp_payment_info['note']); ?></p>
			 <?php } ?>

			 <?php do_action('mp_single_order_display_shipping', $order); ?>

			</div>
		</div>

		<div id="mp-order-notes" class="postbox">
			<h3 class='hndle'><span><?php _e('Order Notes', 'mp'); ?></span> - <span class="description"><?php _e('These notes will be displayed on the order status page', 'mp'); ?></span></h3>
			<div class="inside">
					<p>
					<textarea name="mp_order_notes" rows="5" style="width: 100%;"><?php echo esc_textarea(isset($order->mp_order_notes) ? $order->mp_order_notes : ''); ?></textarea><br />
					<input type="submit" class="button-secondary" name="save-note" value="<?php _e('Save &raquo;', 'mp'); ?>" />
					</p>
				</div>
		</div>
			</form>

		<?php do_action('mp_single_order_display_box', $order); ?>

	 </div>

	 <div id='advanced-sortables' class='meta-box-sortables'>
	 </div>

	 </div>
	 </div>
	 <br class="clear" />
	 </div><!-- /poststuff -->

	 </div><!-- /wrap -->
	 <?php
	}

	function orders_page() {

	 //load single order view if id is set
	 if (isset($_GET['order_id'])) {
		$this->single_order_page();
		return;
	 }

	 //force post type
	 global $wpdb, $post_type, $wp_query, $wp_locale, $current_screen;
	 $post_type = 'mp_order';
	 $_GET['post_type'] = $post_type;

	 $post_type_object = get_post_type_object($post_type);

	 if ( !current_user_can($post_type_object->cap->edit_posts) )
	 	wp_die(__('Cheatin&#8217; uh?'));

	 $pagenum = isset( $_GET['paged'] ) ? absint( $_GET['paged'] ) : 0;
	 if ( empty($pagenum) )
	 	$pagenum = 1;
	 $per_page = 'edit_' . $post_type . '_per_page';
	 $per_page = (int) get_user_option( $per_page );
	 if ( empty( $per_page ) || $per_page < 1 )
	 	$per_page = 20;
	 // @todo filter based on type
	 $per_page = apply_filters( 'edit_' . $post_type . '_per_page', $per_page );

	 // Handle bulk actions
	 if ( isset($_GET['doaction']) || isset($_GET['doaction2']) || isset($_GET['bulk_edit']) || isset($_GET['action']) 
		|| (isset($_GET['delete_all'])) || (isset($_GET['delete_all2'])) ) {
	 	check_admin_referer('update-order-status');
	 	$sendback = remove_query_arg( array('received', 'paid', 'shipped', 'closed', 'trash', 'delete', 'ids', 'delete_all', 'delete_all2'), wp_get_referer() );

	 	if ( ( $_GET['action'] != -1 || $_GET['action2'] != -1 ) && ( isset($_GET['post']) || isset($_GET['ids']) ) ) {
	 		$post_ids = isset($_GET['post']) ? array_map( 'intval', (array) $_GET['post'] ) : explode(',', $_GET['ids']);
	 		$doaction = ($_GET['action'] != -1) ? $_GET['action'] : $_GET['action2'];
	 	} else if ( isset( $_GET['delete_all'] ) || isset( $_GET['delete_all2'] ) )
			$doaction = 'delete_all';
		
	 	switch ( $doaction ) {
	 		case 'received':
	 			$received = 0;
	 			foreach( (array) $post_ids as $post_id ) {
	 				$this->update_order_status($post_id, 'received');
	 				$received++;
	 			}
	 			$msg = sprintf( _n( '%s order marked as Received.', '%s orders marked as Received.', $received, 'mp' ), number_format_i18n( $received ) );
	 			break;
			case 'paid':
	 			$paid = 0;
	 			foreach( (array) $post_ids as $post_id ) {
	 				$this->update_order_status($post_id, 'paid');
	 				$paid++;
	 			}
	 			$msg = sprintf( _n( '%s order marked as Paid.', '%s orders marked as Paid.', $paid, 'mp' ), number_format_i18n( $paid ) );
	 			break;
			case 'shipped':
	 			$shipped = 0;
	 			foreach( (array) $post_ids as $post_id ) {
	 				$this->update_order_status($post_id, 'shipped');
	 				$shipped++;
	 			}
	 			$msg = sprintf( _n( '%s order marked as Shipped.', '%s orders marked as Shipped.', $shipped, 'mp' ), number_format_i18n( $shipped ) );
			 break;
			case 'closed':
	 			$closed = 0;
	 			foreach( (array) $post_ids as $post_id ) {
	 				$this->update_order_status($post_id, 'closed');
	 				$closed++;
	 			}
	 			$msg = sprintf( _n( '%s order Closed.', '%s orders Closed.', $closed, 'mp' ), number_format_i18n( $closed ) );
	 			break;

			case 'trash':
	 			$trashed = 0;
	 			foreach( (array) $post_ids as $post_id ) {
	 				$this->update_order_status($post_id, 'trash');
	 				$trashed++;
	 			}
	 			$msg = sprintf( _n( '%s order moved to Trash.', '%s orders moved to Trash.', $trashed, 'mp' ), number_format_i18n( $trashed ) );
	 			break;

			case 'delete':
	 			$deleted = 0;
	 			foreach( (array) $post_ids as $post_id ) {
	 				$this->update_order_status($post_id, 'delete');
	 				$deleted++;
	 			}
	 			$msg = sprintf( _n( '%s order Deleted.', '%s orders Deleted.', $deleted, 'mp' ), number_format_i18n( $deleted ) );
	 			break;

		case 'delete_all':
				$mp_orders = get_posts('post_type=mp_order&post_status=trash&numberposts=-1');
				if ($mp_orders) {
						$deleted = 0;
					foreach($mp_orders as $mp_order) {
							$this->update_order_status($mp_order->ID, 'delete');						
							$deleted++;
					}
						$msg = sprintf( _n( '%s order Deleted.', '%s orders Deleted.', $deleted, 'mp' ), number_format_i18n( $deleted ) );
				}
				break;
	 	}

	 }

	 $avail_post_stati = wp_edit_posts_query();

	 $num_pages = $wp_query->max_num_pages;

	 $mode = 'list';
	 ?>

	 <div class="wrap">
	 <div class="icon32"><img src="<?php echo $this->plugin_url . 'images/shopping-cart.png'; ?>" /></div>
	 <h2><?php _e('Manage Orders', 'mp');
	 if ( isset($_GET['s']) && $_GET['s'] )
	 	printf( '<span class="subtitle">' . __('Search results for &#8220;%s&#8221;') . '</span>', get_search_query() ); ?>
	 </h2>

	 <?php if ( isset($msg) ) { ?>
	 <div class="updated fade"><p>
	 <?php echo $msg; ?>
	 </p></div>
	 <?php } ?>

	 <form id="posts-filter" action="<?php echo admin_url('edit.php'); ?>" method="get">

	 <ul class="subsubsub">
	 <?php
	 if ( empty($locked_post_status) ) :
		$status_links = array();
		$num_posts = wp_count_posts( $post_type, 'readable' );
		$class = '';
		$allposts = '';

		$total_posts = array_sum( (array) $num_posts );

		// Subtract post types that are not included in the admin all list.
		foreach ( get_post_stati( array('show_in_admin_all_list' => false) ) as $state )
			$total_posts -= $num_posts->$state;

		$class = empty($class) && empty($_GET['post_status']) ? ' class="current"' : '';
		$status_links[] = "<li><a href='edit.php?page=marketpress-orders&post_type=product{$allposts}'$class>" . sprintf( _nx( 'All <span class="count">(%s)</span>', 'All <span class="count">(%s)</span>', $total_posts, 'posts' ), number_format_i18n( $total_posts ) ) . '</a>';

		foreach ( get_post_stati(array(), 'objects') as $status_key => $status ) {
			$class = '';

			$status_name = $status->name;

			if ( !in_array( $status_name, $avail_post_stati ) )
				continue;

			if ( empty( $num_posts->$status_name ) )
				continue;

			if ( isset($_GET['post_status']) && $status_name == $_GET['post_status'] )
				$class = ' class="current"';

			$status_links[$status_key] = "<li><a href='edit.php?page=marketpress-orders&amp;post_status=$status_name&amp;post_type=product'$class>" . sprintf( _n( $status->label_count[0], $status->label_count[1], $num_posts->$status_name ), number_format_i18n( $num_posts->$status_name ) ) . '</a>';
		}

		// Kludge. There has to be a better way to order stati. If present we want to 'trash' key always at the end. 
		// Maybe if we were properly inheriting WP_List_Table.
		if (isset($status_links['trash'])) {
			$trash_item = $status_links['trash'];
			unset($status_links['trash']);
			$status_links['trash'] = $trash_item;
		}
		echo implode( " |</li>\n", $status_links ) . '</li>';
		unset( $status_links );
	 endif;
	 ?>
	 </ul>

		<p class="search-box">
			<label class="screen-reader-text" for="post-search-input"><?php _e('Search Orders', 'mp'); ?>:</label>
			<input type="text" id="post-search-input" name="s" value="<?php the_search_query(); ?>" />
			<input type="submit" value="<?php _e('Search Orders', 'mp'); ?>" class="button" />
		</p>

		<input type="hidden" name="post_type" class="post_status_page" value="product" />
		<input type="hidden" name="page" class="post_status_page" value="marketpress-orders" />
		<?php if (!empty($_GET['post_status'])) { ?>
		<input type="hidden" name="post_status" class="post_status_page" value="<?php echo esc_attr($_GET['post_status']); ?>" />
		<?php } ?>

		<?php if ( have_posts() ) { ?>

		<div class="tablenav">
		<?php
		$page_links = paginate_links( array(
			'base' => add_query_arg( 'paged', '%#%' ),
			'format' => '',
			'prev_text' => __('&laquo;'),
			'next_text' => __('&raquo;'),
			'total' => $num_pages,
			'current' => $pagenum
		));

		?>

		<div class="alignleft actions">
		<select name="action">
		<option value="-1" selected="selected"><?php _e('Change Status', 'mp'); ?></option>
		<option value="received"><?php _e('Received', 'mp'); ?></option>
		<option value="paid"><?php _e('Paid', 'mp'); ?></option>
		<option value="shipped"><?php _e('Shipped', 'mp'); ?></option>
		<option value="closed"><?php _e('Closed', 'mp'); ?></option>
		<?php if ((isset($_GET['post_status'])) && ($_GET['post_status'] == 'trash')) { ?>
			<option value="delete"><?php _e('Delete', 'mp'); ?></option>
		<?php } else { ?>
			<option value="trash"><?php _e('Trash', 'mp'); ?></option>			
		<?php } ?>
		</select>
		<input type="submit" value="<?php esc_attr_e('Apply'); ?>" name="doaction" id="doaction" class="button-secondary action" />
		<?php wp_nonce_field('update-order-status'); ?>

		<?php // view filters
		if ( !is_singular() ) {
				$arc_query = $wpdb->prepare("SELECT DISTINCT YEAR(post_date) AS yyear, MONTH(post_date) AS mmonth FROM $wpdb->posts WHERE post_type = %s ORDER BY post_date DESC", $post_type);
	
				$arc_result = $wpdb->get_results( $arc_query );
	
				$month_count = count($arc_result);
	
				if ( $month_count && !( 1 == $month_count && 0 == $arc_result[0]->mmonth ) ) {
				$m = isset($_GET['m']) ? (int)$_GET['m'] : 0;
				?>
				<select name='m'>
				<option<?php selected( $m, 0 ); ?> value='0'><?php _e('Show all dates'); ?></option>
				<?php
				foreach ($arc_result as $arc_row) {
					if ( $arc_row->yyear == 0 )
						continue;
					$arc_row->mmonth = zeroise( $arc_row->mmonth, 2 );
	
					if ( $arc_row->yyear . $arc_row->mmonth == $m )
						$default = ' selected="selected"';
					else
						$default = '';
	
					echo "<option$default value='" . esc_attr("$arc_row->yyear$arc_row->mmonth") . "'>";
					echo $wp_locale->get_month($arc_row->mmonth) . " $arc_row->yyear";
					echo "</option>\n";
				}
				?>
				</select>
				<?php } ?>
	
				<input type="submit" id="post-query-submit" value="<?php esc_attr_e('Filter'); ?>" class="button-secondary" />
		<?php } ?>

		<?php 
		if ((isset($_GET['post_status'])) && ($_GET['post_status'] == 'trash')) {
			submit_button( __( 'Empty Trash' ), 'button-secondary apply', 'delete_all', false );
		} 
		?>
		</div>

		<?php if ( $page_links ) { ?>
		<div class="tablenav-pages"><?php
			$count_posts = $post_type_object->hierarchical ? $wp_query->post_count : $wp_query->found_posts;
			$page_links_text = sprintf( '<span class="displaying-num">' . __( 'Displaying %s&#8211;%s of %s' ) . '</span>%s',
								number_format_i18n( ( $pagenum - 1 ) * $per_page + 1 ),
								number_format_i18n( min( $pagenum * $per_page, $count_posts ) ),
								number_format_i18n( $count_posts ),
								$page_links
								);
			echo $page_links_text;
			?></div>
		<?php } ?>

		<div class="clear"></div>
		</div>

		<div class="clear"></div>

		<table class="widefat <?php echo $post_type_object->hierarchical ? 'page' : 'post'; ?> fixed" cellspacing="0">
			<thead>
			<tr>
		<?php print_column_headers( $current_screen ); ?>
			</tr>
			</thead>

			<tfoot>
			<tr>
		<?php print_column_headers($current_screen, false); ?>
			</tr>
			</tfoot>

			<tbody>
		<?php
			if ( function_exists('post_rows') ) {
			 post_rows();
			} else {
			 $wp_list_table = _get_list_table('WP_Posts_List_Table');
			 $wp_list_table->display_rows();
			}
		 ?>
			</tbody>
		</table>

		<div class="tablenav">

		<?php
		if ( $page_links )
			echo "<div class='tablenav-pages'>$page_links_text</div>";
		?>

		<div class="alignleft actions">
		<select name="action2">
		<option value="-1" selected="selected"><?php _e('Change Status', 'mp'); ?></option>
		<option value="received"><?php _e('Received', 'mp'); ?></option>
		<option value="paid"><?php _e('Paid', 'mp'); ?></option>
		<option value="shipped"><?php _e('Shipped', 'mp'); ?></option>
		<option value="closed"><?php _e('Closed', 'mp'); ?></option>
		<?php if ((isset($_GET['post_status'])) && ($_GET['post_status'] == 'trash')) { ?>
			<option value="delete"><?php _e('Delete', 'mp'); ?></option>
		<?php } else { ?>
			<option value="trash"><?php _e('Trash', 'mp'); ?></option>			
		<?php } ?>
		</select>
		<input type="submit" value="<?php esc_attr_e('Apply'); ?>" name="doaction2" id="doaction2" class="button-secondary action" />
		<?php 
		if ((isset($_GET['post_status'])) && ($_GET['post_status'] == 'trash')) {
			submit_button( __( 'Empty Trash' ), 'button-secondary apply', 'delete_all2', false );
		} 
		?>

		<br class="clear" />
		</div>
		<br class="clear" />
		</div>

		<?php } else { // have_posts() ?>
		<div class="clear"></div>
		<p><?php _e('No Orders Yet', 'mp'); ?></p>
		<?php } ?>

		</form>

		<?php if (!isset($_GET['post_status']) || $_GET['post_status'] != 'trash') { ?>
			<div class="icon32"><img src="<?php echo $this->plugin_url . 'images/download.png'; ?>" /></div>
			<h2><?php _e('Export Orders', 'mp'); ?></h2>
			<?php if ( MP_LITE === true ) { ?>
			<a class="mp-pro-update" href="https://premium.wpmudev.org/project/e-commerce/" title="<?php _e('Upgrade Now', 'mp'); ?> &raquo;"><?php _e('Upgrade to enable CSV order exports &raquo;', 'mp'); ?></a><br />
			<?php } ?>
			<form action="<?php echo admin_url('admin-ajax.php?action=mp-orders-export'); ?>" method="post">
				<?php
				$months = $wpdb->get_results( $wpdb->prepare( "
				SELECT DISTINCT YEAR( post_date ) AS year, MONTH( post_date ) AS month
				FROM $wpdb->posts
				WHERE post_type = %s
				ORDER BY post_date DESC
			", 'mp_order' ) );

			$month_count = count( $months );

			if ( !$month_count || ( 1 == $month_count && 0 == $months[0]->month ) )
				return;

			$m = isset( $_GET['m'] ) ? (int) $_GET['m'] : 0;
	?>
			<select name='m'>
				<option<?php selected( $m, 0 ); ?> value='0'><?php _e( 'Show all dates' ); ?></option>
	<?php
			foreach ( $months as $arc_row ) {
				if ( 0 == $arc_row->year )
					continue;

				$month = zeroise( $arc_row->month, 2 );
				$year = $arc_row->year;

				printf( "<option %s value='%s'>%s</option>\n",
					selected( $m, $year . $month, false ),
					esc_attr( $arc_row->year . $month ),
					$wp_locale->get_month( $month ) . " $year"
				);
			}

			$status = isset( $_GET['post_status'] ) ? $_GET['post_status'] : 'all';
	?>
			</select>
			<select name="order_status">
				<option<?php selected( $status, 'all' ); ?> value="all" selected="selected"><?php _e('All Statuses', 'mp'); ?></option>
				<option<?php selected( $status, 'order_received' ); ?> value="order_received"><?php _e('Received', 'mp'); ?></option>
				<option<?php selected( $status, 'order_paid' ); ?> value="order_paid"><?php _e('Paid', 'mp'); ?></option>
				<option<?php selected( $status, 'order_shipped' ); ?> value="order_shipped"><?php _e('Shipped', 'mp'); ?></option>
				<option<?php selected( $status, 'order_closed' ); ?> value="order_closed"><?php _e('Closed', 'mp'); ?></option>
			</select>
			<input type="submit" value="<?php _e('Download &raquo;', 'mp'); ?>" name="export_orders" class="button-secondary"<?php echo MP_LITE === true ? ' disabled="disabled"' : ''; ?> />
			</form>


		<br class="clear">
	<?php } ?>
	 </div>
	 <?php
	}

	function admin_page() {
	 global $wpdb;

	 //double-check rights
	 if(!current_user_can('manage_options')) {
			echo "<p>" . __('Nice Try...', 'mp') . "</p>";	 //If accessed properly, this message doesn't appear.
			return;
		}

	 $settings = get_option('mp_settings');
	 ?>
	 <div class="wrap">
	 <h3 class="nav-tab-wrapper">
	 <?php
	 $tab = ( !empty($_GET['tab']) ) ? $_GET['tab'] : 'main';

	 if (!$this->get_setting('disable_cart')) {
	 	$tabs = array(
			'coupons'			=> __('Coupons', 'mp'),
	 		'presentation'	 => __('Presentation', 'mp'),
	 		'messages'		 => __('Messages', 'mp'),
	 		'shipping'		 => __('Shipping', 'mp'),
	 		'gateways'		 => __('Payments', 'mp'),
	 		'shortcodes'	 => __('Shortcodes', 'mp'),
	 		'importers'		 => __('Importers', 'mp')
	 	);
	 } else {
		$tabs = array(
				'presentation'	=> __('Presentation', 'mp'),
	 		'shortcodes'	 => __('Shortcodes', 'mp'),
	 		'importers'		 => __('Importers', 'mp')
			);
	 }
		$tabhtml = array();

	 // If someone wants to remove or add a tab
		$tabs = apply_filters( 'marketpress_tabs', $tabs );

		$class = ( 'main' == $tab ) ? ' nav-tab-active' : '';
		$tabhtml[] = '	<a href="' . admin_url( 'edit.php?post_type=product&amp;page=marketpress' ) . '" class="nav-tab'.$class.'">' . __('General', 'mp') . '</a>';

		foreach ( $tabs as $stub => $title ) {
			$class = ( $stub == $tab ) ? ' nav-tab-active' : '';
			$tabhtml[] = '	<a href="' . admin_url( 'edit.php?post_type=product&amp;page=marketpress&amp;tab=' . $stub ) . '" class="nav-tab'.$class.'">'.$title.'</a>';
		}

		echo implode( "\n", $tabhtml );
	 ?>
		</h3>
		<div class="clear"></div>

		<?php
		switch( $tab ) {
			//---------------------------------------------------//
			case "main":

			//save settings
			if (isset($_POST['marketplace_settings'])) {

			 //allow plugins to verify settings before saving
					if (isset($_POST['mp']['tax']['canada_rate'])) {
						foreach	($_POST['mp']['tax']['canada_rate'] as $key => $rate) {
							$tax_rate = $rate * .01;
							$_POST['mp']['tax']['canada_rate'][$key] = ($tax_rate < 1 && $tax_rate >= 0) ? $tax_rate : 0;
						}
					} else {
						$tax_rate = $_POST['mp']['tax']['rate'] * .01;
						$_POST['mp']['tax']['rate'] = ($tax_rate < 1 && $tax_rate >= 0) ? $tax_rate : 0;
					}
			 $settings = array_merge($settings, apply_filters('mp_main_settings_filter', $_POST['mp']));
			 update_option('mp_settings', $settings);

			 echo '<div class="updated fade"><p>'.__('Settings saved.', 'mp').'</p></div>';
			}
			?>
			<script type="text/javascript">
				jQuery(document).ready(function($) {
				$("#mp-country-select, #mp-currency-select").change(function() {
					$("#mp-main-form").submit();
					});
			 });
			</script>
			<div class="icon32"><img src="<?php echo $this->plugin_url . 'images/settings.png'; ?>" /></div>
			<h2><?php _e('General Settings', 'mp'); ?></h2>
			<div id="poststuff" class="metabox-holder mp-settings">

			<form id="mp-main-form" method="post" action="edit.php?post_type=product&amp;page=marketpress&amp;tab=main">
			 <input type="hidden" name="marketplace_settings" value="1" />

			 <div class="postbox location-settings">
				<h3 class='hndle'><span><?php _e('Location Settings', 'mp') ?></span></h3>
				<div class="inside">
					<span class="description"><?php _e('This is the base location that shipping and tax rates will be calculated from.', 'mp') ?></span>
					<table class="form-table">
					 <tr>
							<th scope="row"><?php _e('Base Country', 'mp') ?></th>
							<td>
						<select id="mp-country-select" name="mp[base_country]">
							<?php
							foreach ($this->countries as $key => $value) {
							 ?><option value="<?php echo $key; ?>"<?php selected($this->get_setting('base_country'), $key); ?>><?php echo esc_attr($value); ?></option><?php
							}
							?>
						</select>
			 				</td>
					 </tr>

					<?php
					switch ($this->get_setting('base_country')) {
					 case 'US':
						$list = $this->usa_states;
						break;

					 case 'CA':
						$list = $this->canadian_provinces;
						break;

					 case 'GB':
						$list = $this->uk_counties;
						break;

					 case 'AU':
						$list = $this->australian_states;
						break;

					 default:
						$list = false;
					}

					//only show if correct country
					if (is_array($list)) {
					?>
					 <tr>
							<th scope="row"><?php _e('Base State/Province/Region', 'mp') ?></th>
							<td>
						<select name="mp[base_province]">
							<?php
							foreach ($list as $key => $value) {
							 ?><option value="<?php echo esc_attr($key); ?>"<?php selected($this->get_setting('base_province'), $key); ?>><?php echo esc_attr($value); ?></option><?php
							}
							?>
						</select>
			 				</td>
					 </tr>
					<?php }
							//only show if correct country or US province
					if ( is_array($list) || in_array( $this->get_setting('base_country'), array('UM','AS','FM','GU','MH','MP','PW','PR','PI') )	) {
					?>
					 <tr>
							<th scope="row"><?php _e('Base Zip/Postal Code', 'mp') ?></th>
							<td>
						<input value="<?php echo esc_attr($this->get_setting('base_zip')); ?>" size="10" name="mp[base_zip]" type="text" />
			 			</td>
					 </tr>
					<?php } ?>
					</table>
				</div>
			 </div>

			 <div class="postbox tax-settings">
				<h3 class='hndle'><span><?php _e('Tax Settings', 'mp') ?></span></h3>
				<div class="inside">
					<table class="form-table">
					<?php
					switch ($this->get_setting('base_country')) {
					 case 'US':
						?>
						<tr>
			 				<th scope="row"><?php echo sprintf(__('%s Tax Rate', 'mp'), esc_attr($this->usa_states[$this->get_setting('base_province', 'CA')])); ?></th>
			 				<td>
						<input value="<?php echo $this->get_setting('tax->rate') * 100; ?>" size="3" name="mp[tax][rate]" type="text" style="text-align:right;" />%
							</td>
						</tr>
						<?php
						break;

					 case 'CA':
						?>
						<tr>
			 				<th scope="row"><a href="http://sbinfocanada.about.com/od/pst/a/PSTecommerce.htm" target="_blank"><?php _e('Total Tax Rates (VAT,GST,PST,HST)', 'mp'); ?></a></th>
			 				<td>
										<span class="description"><a href="http://en.wikipedia.org/wiki/Sales_taxes_in_Canada" target="_blank"><?php _e('Current Rates &raquo;', 'mp'); ?></a></span>
										<table cellspacing="0" cellpadding="0">
										<?php foreach ($this->canadian_provinces as $key => $label) { ?>
										<tr>
											<td style="padding: 0 5px;"><label for="mp_tax_<?php echo $key; ?>"><?php echo esc_attr($label); ?></label></td>
											<td style="padding: 0 5px;"><input value="<?php echo $this->get_setting("tax->canada_rate->$key") * 100; ?>" size="3" id="mp_tax_<?php echo $key; ?>" name="mp[tax][canada_rate][<?php echo $key; ?>]" type="text" style="text-align:right;" />%</td>
										</tr>
										<?php	} ?>
										</table>
									</td>
						</tr>
						<?php
						break;

					 case 'GB':
						?>
						<tr>
			 				<th scope="row"><?php _e('VAT Tax Rate', 'mp') ?></th>
			 				<td>
						<input value="<?php echo $this->get_setting('tax->rate') * 100; ?>" size="3" name="mp[tax][rate]" type="text" style="text-align:right;" />%
							</td>
						</tr>
						<?php
						break;

					 case 'AU':
						?>
						<tr>
			 				<th scope="row"><?php _e('GST Tax Rate', 'mp') ?></th>
			 				<td>
						<input value="<?php echo $this->get_setting('tax->rate') * 100; ?>" size="3" name="mp[tax][rate]" type="text" style="text-align:right;" />%
							</td>
						</tr>
						<?php
						break;

					 default:
						//in european union
						if ( in_array($this->get_setting('base_country'), $this->eu_countries) ) {
							?>
							<tr>
								<th scope="row"><?php _e('VAT Tax Rate', 'mp') ?></th>
								<td>
							<input value="<?php echo $this->get_setting('tax->rate') * 100; ?>" size="3" name="mp[tax][rate]" type="text" style="text-align:right;" />%
							</td>
							</tr>
							<?php
						} else { //all other countries
							?>
							<tr>
								<th scope="row"><?php _e('Country Total Tax Rate (VAT, GST, Etc.)', 'mp') ?></th>
								<td>
							<input value="<?php echo $this->get_setting('tax->rate') * 100; ?>" size="3" name="mp[tax][rate]" type="text" style="text-align:right;" />%
							</td>
							</tr>
							<tr>
								<th scope="row"><?php _e('Tax Orders Outside Your Base Country?', 'mp'); ?></th>
								<td>
								<label><input value="1" name="mp[tax][tax_outside]" type="radio"<?php checked($this->get_setting('tax->tax_outside'), 1) ?> /> <?php _e('Yes', 'mp') ?></label>
							<label><input value="0" name="mp[tax][tax_outside]" type="radio"<?php checked($this->get_setting('tax->tax_outside'), 0) ?> /> <?php _e('No', 'mp') ?></label>
								</td>
							</tr>
							<?php
						}
						break;
					}
					?>
								<tr>
								<th scope="row"><?php _e('Tax Label', 'mp') ?></th>
								<td>
								<input value="<?php echo esc_attr($this->get_setting('tax->label', __('Taxes', 'mp'))); ?>" size="10" name="mp[tax][label]" type="text" />
								<br /><span class="description"><?php _e('The label shown for the tax line item in the cart. Taxes, VAT, GST, etc.', 'mp') ?></span>
								</td>
								</tr>
					 <tr>
							<th scope="row"><?php _e('Apply Tax To Shipping Fees?', 'mp') ?></th>
					 <td>
							<label><input value="1" name="mp[tax][tax_shipping]" type="radio"<?php checked($this->get_setting('tax->tax_shipping'), 1) ?> /> <?php _e('Yes', 'mp') ?></label>
					 <label><input value="0" name="mp[tax][tax_shipping]" type="radio"<?php checked($this->get_setting('tax->tax_shipping'), 0) ?> /> <?php _e('No', 'mp') ?></label>
					 <br /><span class="description"><?php _e('Please see your local tax laws. Most areas charge tax on shipping fees.', 'mp') ?></span>
			 			</td>
					 </tr>
					 <tr>
							<th scope="row"><?php _e('Enter Prices Inclusive of Tax?', 'mp') ?></th>
					 <td>
							<label><input value="1" name="mp[tax][tax_inclusive]" type="radio"<?php checked($this->get_setting('tax->tax_inclusive'), 1) ?> /> <?php _e('Yes', 'mp') ?></label>
					 <label><input value="0" name="mp[tax][tax_inclusive]" type="radio"<?php checked($this->get_setting('tax->tax_inclusive'), 0) ?> /> <?php _e('No', 'mp') ?></label>
					 <br /><span class="description"><?php _e('Enabling this option allows you to enter and show all prices inclusive of tax, while still listing the tax total as a line item in shopping carts. Please see your local tax laws.', 'mp') ?></span>
			 			</td>
					 </tr>
					 <tr>
							<th scope="row"><?php _e("Apply Tax to Downloadable Products?", 'mp') ?></th>
					 <td>
							<label><input value="1" name="mp[tax][tax_digital]" type="radio"<?php checked($this->get_setting('tax->tax_digital'), 1) ?> /> <?php _e('Yes', 'mp') ?></label>
					 <label><input value="0" name="mp[tax][tax_digital]" type="radio"<?php checked($this->get_setting('tax->tax_digital'), 0) ?> /> <?php _e('No', 'mp') ?></label>
					 <br /><span class="description"><?php _e('Please see your local tax laws. Note if this is enabled and a downloadable only cart, rates will be the default for your base location.', 'mp') ?></span>
			 			</td>
					 </tr>
					 <tr>
							<th scope="row"><?php _e("Collect Address on Downloadable Only Cart?", 'mp') ?></th>
					 <td>
							<label><input value="1" name="mp[tax][downloadable_address]" type="radio"<?php checked($this->get_setting('tax->downloadable_address'), 1) ?> /> <?php _e('Yes', 'mp') ?></label>
					 <label><input value="0" name="mp[tax][downloadable_address]" type="radio"<?php checked($this->get_setting('tax->downloadable_address', 0), 0) ?> /> <?php _e('No', 'mp') ?></label>
					 <br /><span class="description"><?php _e("If you need to tax downloadable products and don't want to default to the rates to your base location, enable this to always collect the shipping address.", 'mp') ?></span>
			 			</td>
					 </tr>
					</table>
				</div>
			 </div>

			 <div class="postbox">
				<h3 class='hndle'><span><?php _e('Currency Settings', 'mp') ?></span></h3>
				<div class="inside">
					<span class="description"><?php _e('These preferences affect display only. Your payment gateway of choice may not support every currency listed here.', 'mp') ?></span>
					<table class="form-table">
							<tr valign="top">
					 <th scope="row"><?php _e('Store Currency', 'mp') ?></th>
							<td>
						<select id="mp-currency-select" name="mp[currency]">
							<?php
							foreach ($this->currencies as $key => $value) {
							 ?><option value="<?php echo $key; ?>"<?php selected($this->get_setting('currency'), $key); ?>><?php echo esc_attr($value[0]) . ' - ' . $this->format_currency($key); ?></option><?php
							}
							?>
						</select>
			 				</td>
					 </tr>
					 <tr valign="top">
					 <th scope="row"><?php _e('Currency Symbol Position', 'mp') ?></th>
					 <td>
					 <label><input value="1" name="mp[curr_symbol_position]" type="radio"<?php checked($this->get_setting('curr_symbol_position'), 1); ?>>
							<?php echo $this->format_currency($this->get_setting('currency')); ?>100</label><br />
							<label><input value="2" name="mp[curr_symbol_position]" type="radio"<?php checked($this->get_setting('curr_symbol_position'), 2); ?>>
							<?php echo $this->format_currency($this->get_setting('currency')); ?> 100</label><br />
							<label><input value="3" name="mp[curr_symbol_position]" type="radio"<?php checked($this->get_setting('curr_symbol_position'), 3); ?>>
							100<?php echo $this->format_currency($this->get_setting('currency')); ?></label><br />
							<label><input value="4" name="mp[curr_symbol_position]" type="radio"<?php checked($this->get_setting('curr_symbol_position'), 4); ?>>
							100 <?php echo $this->format_currency($this->get_setting('currency')); ?></label>
					 </td>
					 </tr>
					 <tr valign="top">
					 <th scope="row"><?php _e('Show Decimal in Prices', 'mp') ?></th>
					 <td>
					 <label><input value="1" name="mp[curr_decimal]" type="radio"<?php checked( ( ($this->get_setting('curr_decimal') !== 0) ? 1 : 0 ), 1); ?>>
							<?php _e('Yes', 'mp') ?></label>
							<label><input value="0" name="mp[curr_decimal]" type="radio"<?php checked($this->get_setting('curr_decimal'), 0); ?>>
							<?php _e('No', 'mp') ?></label>
					 </td>
					 </tr>
					</table>
				</div>
			 </div>

			 <div class="postbox">
				<h3 class='hndle'><span><?php _e('Miscellaneous Settings', 'mp') ?></span></h3>
				<div class="inside">
					<table class="form-table">
								<tr id="mp-inventory-setting">
					 <th scope="row"><?php _e('Inventory Warning Threshold', 'mp') ?></th>
							<td>
							<span class="description"><?php _e('At what low stock count do you want to be warned for products you have enabled inventory tracking for?', 'mp') ?></span><br />
								<select name="mp[inventory_threshhold]">
								<?php
								$inventory_threshhold = $this->get_setting('inventory_threshhold', 3);
								for ($i=0; $i<=100; $i++) {
						$selected = ($inventory_threshhold == $i) ? ' selected="selected"' : '';
							echo '<option value="' . $i . '"' . $selected . '">' . $i . '</option>';
				 			}
								?>
								</select>
					 </td>
					 </tr>
					 <tr valign="top">
					 <th scope="row"><?php _e('Hide Out of Stock Products', 'mp') ?></th>
					 <td>
					 <label><input value="1" name="mp[inventory_remove]" type="radio"<?php checked($this->get_setting('inventory_remove'), 1); ?>> <?php _e('Yes', 'mp') ?></label>
							<label><input value="0" name="mp[inventory_remove]" type="radio"<?php checked($this->get_setting('inventory_remove'), 0); ?>> <?php _e('No', 'mp') ?></label>
					 <br /><span class="description"><?php _e('This will set the product to draft if inventory of all variations is gone.', 'mp') ?></span>
					 </td>
					 </tr>
					 <tr id="mp-downloads-setting">
					 <th scope="row"><?php _e('Maximum Downloads', 'mp') ?></th>
							<td>
							<span class="description"><?php _e('How many times may a customer download a file they have purchased? (It\'s best to set this higher than one in case they have any problems downloading)', 'mp') ?></span><br />
					 <select name="mp[max_downloads]">
								<?php
								$max_downloads = $this->get_setting('max_downloads', 5);
								for ($i=1; $i<=100; $i++) {
						$selected = ($max_downloads == $i) ? ' selected="selected"' : '';
							echo '<option value="' . $i . '"' . $selected . '">' . $i . '</option>';
				 			}
								?>
								</select>
								</td>
					 </tr>
								<tr valign="top">
					 <th scope="row"><?php _e('Limit Digital Products Per-order', 'mp') ?></th>
					 <td>
					 <label><input value="1" name="mp[download_order_limit]" type="radio"<?php checked($this->get_setting('download_order_limit', 1), 1); ?>> <?php _e('Yes', 'mp') ?></label>
							<label><input value="0" name="mp[download_order_limit]" type="radio"<?php checked($this->get_setting('download_order_limit'), 0); ?>> <?php _e('No', 'mp') ?></label>
					 <br /><span class="description"><?php _e('This will prevent multiples of the same downloadable product form being added to the cart. Per-product custom limits will override this.', 'mp') ?></span>
					 </td>
					 </tr>
					 <tr>
					 <th scope="row"><?php _e('Force Login', 'mp') ?></th>
							<td>
							<?php $force_login = ($this->get_setting('force_login')) ? 1 : 0; ?>
							<label><input value="1" name="mp[force_login]" type="radio"<?php checked($force_login, 1) ?> /> <?php _e('Yes', 'mp') ?></label>
					 <label><input value="0" name="mp[force_login]" type="radio"<?php checked($force_login, 0) ?> /> <?php _e('No', 'mp') ?></label>
					 <br /><span class="description"><?php _e('Whether or not customers must be registered and logged in to checkout. (Not recommended: Enabling this can lower conversions)', 'mp') ?></span>
			 			</td>
					 </tr>
					 <tr>
					 <th scope="row"><?php _e('Product Listings Only', 'mp') ?></th>
							<td>
							<label><input value="1" name="mp[disable_cart]" type="radio"<?php checked($this->get_setting('disable_cart'), 1) ?> /> <?php _e('Yes', 'mp') ?></label>
					 <label><input value="0" name="mp[disable_cart]" type="radio"<?php checked($this->get_setting('disable_cart'), 0) ?> /> <?php _e('No', 'mp') ?></label>
					 <br /><span class="description"><?php _e('This option turns MarketPress into more of a product listing plugin, disabling shopping carts, checkout, and order management. This is useful if you simply want to list items you can buy in a store somewhere else, optionally linking the "Buy Now" buttons to an external site. Some examples are a car dealership, or linking to songs/albums in itunes, or linking to products on another site with your own affiliate links.', 'mp') ?></span>
			 			</td>
					 </tr>
					 <tr>
							<th scope="row"><?php _e('Google Analytics Ecommerce Tracking', 'mp') ?></th>
					 <td>
						<?php if ( MP_LITE === true) { ?>
						<a class="mp-pro-update" href="https://premium.wpmudev.org/project/e-commerce/" title="<?php _e('Upgrade Now', 'mp'); ?> &raquo;"><?php _e('Upgrade to enable Google Analytics Ecommerce Tracking &raquo;', 'mp'); ?></a><br />
						<?php } ?>
						<select name="mp[ga_ecommerce]"<?php echo MP_LITE === true ? ' disabled="disabled"' : ''; ?>>
								<option value="none"<?php selected($this->get_setting('ga_ecommerce'), 'none') ?>><?php _e('None', 'mp') ?></option>
								<option value="new"<?php selected($this->get_setting('ga_ecommerce'), 'new') ?>><?php _e('Asynchronous Tracking Code', 'mp') ?></option>
								<option value="old"<?php selected($this->get_setting('ga_ecommerce'), 'old') ?>><?php _e('Old Tracking Code', 'mp') ?></option>
								<option value="universal"<?php selected($this->get_setting('ga_ecommerce'), 'universal') ?>><?php _e('Universal Analytics', 'mp') ?></option>
							</select>
							<br /><span class="description"><?php _e('If you already use Google Analytics for your website, you can track detailed ecommerce information by enabling this setting. Choose whether you are using the new asynchronous or old tracking code. Before Google Analytics can report ecommerce activity for your website, you must enable ecommerce tracking on the profile settings page for your website. Also keep in mind that some gateways do not reliably show the receipt page, so tracking may not be accurate in those cases. It is recommended to use the PayPal gateway for the most accurate data. <a href="http://analytics.blogspot.com/2009/05/how-to-use-ecommerce-tracking-in-google.html" target="_blank">More information &raquo;</a>', 'mp') ?></span>
			 			</td>
					 </tr>
					 <tr>
					 <th scope="row"><?php _e('Special Instructions Field', 'mp') ?></th>
							<td>
							<label><input value="1" name="mp[special_instructions]" type="radio"<?php checked($this->get_setting('special_instructions'), 1) ?> /> <?php _e('Yes', 'mp') ?></label>
					 <label><input value="0" name="mp[special_instructions]" type="radio"<?php checked($this->get_setting('special_instructions'), 0) ?> /> <?php _e('No', 'mp') ?></label>
					 <br /><span class="description"><?php printf(__('Enabling this field will display a textbox on the shipping checkout page for users to enter special instructions for their order. Useful for product personalization, etc. Note you may want to <a href="%s">adjust the message on the shipping page.', 'mp'), admin_url('edit.php?post_type=product&page=marketpress&tab=messages#mp_msgs_shipping')); ?></span>
			 			</td>
					 </tr>
					</table>
				</div>
			 </div>

			 <?php
			 //for adding additional settings for a shipping module
			 do_action('mp_general_settings');
			 ?>

			 <p class="submit">
				<input class="button-primary" type="submit" name="submit_settings" value="<?php _e('Save Changes', 'mp') ?>" />
			 </p>
			</form>
			</div>
			<?php
			break;


			//---------------------------------------------------//
			case "coupons":

			$coupons = get_option('mp_coupons');
				if (!is_array($coupons)) $coupons = array();
				
			//delete checked coupons
			if (isset($_POST['allcoupon_delete'])) {
			 //check nonce
			 check_admin_referer('mp_coupons');

			 if (is_array($_POST['coupons_checks'])) {
				//loop through and delete
				foreach ($_POST['coupons_checks'] as $del_code)
					unset($coupons[$del_code]);

				update_option('mp_coupons', $coupons);
				//display message confirmation
				echo '<div class="updated fade"><p>'.__('Coupon(s) succesfully deleted.', 'mp').'</p></div>';
			 }
			}

			//save or add coupon
			if (isset($_POST['submit_settings'])) {
			 //check nonce
			 check_admin_referer('mp_coupons');

			 $error = false;

			 $new_coupon_code = preg_replace('/[^A-Z0-9_-]/', '', strtoupper($_POST['coupon_code']));
			 if (empty($new_coupon_code))
				$error[] = __('Please enter a valid Coupon Code', 'mp');

			 $coupons[$new_coupon_code]['discount'] = round($_POST['discount'], 2);
			 if ($coupons[$new_coupon_code]['discount'] <= 0)
				$error[] = __('Please enter a valid Discount Amount', 'mp');

			 $coupons[$new_coupon_code]['discount_type'] = $_POST['discount_type'];
			 if ($coupons[$new_coupon_code]['discount_type'] != 'amt' && $coupons[$new_coupon_code]['discount_type'] != 'pct')
				$error[] = __('Please choose a valid Discount Type', 'mp');

			 $coupons[$new_coupon_code]['start'] = strtotime($_POST['start']);
			 if ($coupons[$new_coupon_code]['start'] === false)
				$error[] = __('Please enter a valid Start Date', 'mp');

			 $coupons[$new_coupon_code]['end'] = strtotime($_POST['end']);
			 if ($coupons[$new_coupon_code]['end'] && $coupons[$new_coupon_code]['end'] < $coupons[$new_coupon_code]['start'])
				$error[] = __('Please enter a valid End Date not earlier than the Start Date', 'mp');

			 $coupons[$new_coupon_code]['uses'] = (is_numeric($_POST['uses'])) ? (int)$_POST['uses'] : '';
			 
			 // applies to
			 $applies_to = $_POST['applies_to'];
			 switch( $applies_to ) {
				 case 'all':
				 	
				$coupons[$new_coupon_code]['applies_to'] = '';
				 break;
				 
				 case 'category';
				 	$coupons[$new_coupon_code]['applies_to']['type'] = $_POST['applies_to'];
				$coupons[$new_coupon_code]['applies_to']['id'] = $_POST['coupon_category'];
				if( empty( $_POST['coupon_category'] ) ) {
					$error[] = __('Please choose a Product Category to apply the coupon to', 'mp');
				}
				 break;
				 
				 case 'product':
				 	$coupons[$new_coupon_code]['applies_to']['type'] = $_POST['applies_to'];
				$coupons[$new_coupon_code]['applies_to']['id'] = $_POST['coupon_product'];
				if( empty( $_POST['coupon_product'] ) ) {
					$error[] = __('Please choose an Individual Product to apply the coupon to', 'mp');
				}
				 break;
			 
			 }
			 
			 
			 if (!$error) {
				update_option('mp_coupons', $coupons);
				$new_coupon_code = '';
				echo '<div class="updated fade"><p>'.__('Coupon succesfully saved.', 'mp').'</p></div>';
			 }
			}

			//if editing a coupon
				$new_coupon_code = isset($_GET['code']) ? $_GET['code'] : null;
			?>
			<script type="text/javascript">
				jQuery(document).ready(function ($) {
					jQuery.datepicker.setDefaults(jQuery.datepicker.regional['<?php echo $this->language; ?>']);
					 jQuery('.pickdate').datepicker({dateFormat: 'yy-mm-dd', changeMonth: true, changeYear: true, minDate: 0, firstDay: <?php echo (get_option('start_of_week')=='0') ? 7 : get_option('start_of_week'); ?>});
				});
			</script>
			<div class="icon32"><img src="<?php echo $this->plugin_url . 'images/service.png'; ?>" /></div>
			<h2><?php _e('Coupons', 'mp') ?></h2>
			<p><?php _e('You can create, delete, or update coupon codes for your store here.', 'mp') ?></p>

			<?php
			$apage = isset( $_GET['apage'] ) ? intval( $_GET['apage'] ) : 1;
	 		$num = isset( $_GET['num'] ) ? intval( $_GET['num'] ) : 10;

	 		$coupon_list = get_option('mp_coupons');
	 		$total = (is_array($coupon_list)) ? count($coupon_list) : 0;

			if ($total)
			 $coupon_list = array_slice($coupon_list, intval(($apage-1) * $num), intval($num), true);

	 		$coupon_navigation = paginate_links( array(
	 			'base' => add_query_arg( 'apage', '%#%' ),
//					'base' => add_query_arg( 'apage', '%#%' ).$url2, //@todo: What is $url2???
	 			'format' => '',
	 			'total' => ceil($total / $num),
	 			'current' => $apage
	 		));
	 		$page_link = ($apage > 1) ? '&amp;apage='.$apage : '';
	 		?>

	 		<form id="form-coupon-list" action="edit.php?post_type=product&amp;page=marketpress&amp;tab=coupons<?php echo $page_link; ?>" method="post">
			<?php wp_nonce_field('mp_coupons') ?>
	 		<div class="tablenav">
	 			<?php if ( $coupon_navigation ) echo "<div class='tablenav-pages'>$coupon_navigation</div>"; ?>

	 			<div class="alignleft">
	 				<input type="submit" value="<?php _e('Delete', 'mp') ?>" name="allcoupon_delete" class="button-secondary delete" />
	 				<br class="clear" />
	 			</div>
	 		</div>

	 		<br class="clear" />

	 		<?php
	 		// define the columns to display, the syntax is 'internal name' => 'display name'
	 		$posts_columns = array(
	 			'code'			 => __('Coupon Code', 'mp'),
	 			'discount'		 => __('Discount', 'mp'),
	 			'start'			 => __('Start Date', 'mp'),
	 			'end'			 => __('Expire Date', 'mp'),
					'used'				=> __('Used', 'mp'),
			 		'remaining'		=> __('Remaining Uses', 'mp'),
				'applies_to'	=> __('Applies to', 'mp'),
	 			'edit'			 => __('Edit', 'mp')
	 		);
	 		?>

	 		<table width="100%" cellpadding="3" cellspacing="3" class="widefat">
	 			<thead>
	 				<tr>
	 				<th scope="col" class="check-column"><input type="checkbox" /></th>
	 				<?php foreach($posts_columns as $column_id => $column_display_name) {
	 					$col_url = $column_display_name;
	 					?>
	 					<th scope="col"><?php echo $col_url ?></th>
	 				<?php } ?>
	 				</tr>
	 			</thead>
	 			<tbody id="the-list">
	 			<?php
	 			if ( is_array($coupon_list) && count($coupon_list) ) {
	 				$bgcolor = $class = '';
	 				foreach ($coupon_list as $coupon_code => $coupon) {
						
	 					$class = ('alternate' == $class) ? '' : 'alternate';

					//assign classes based on coupon availability
					$class = ($this->check_coupon($coupon_code)) ? $class . ' coupon-active' : $class . ' coupon-inactive';

	 					echo '<tr class="'.$class.' blog-row">
							 <th scope="row" class="check-column">
	 									<input type="checkbox" name="coupons_checks[]"" value="'.$coupon_code.'" />
	 									</th>';

	 					foreach( $posts_columns as $column_name=>$column_display_name ) {
	 						switch($column_name) {
	 							case 'code': ?>
	 								<th scope="row">
	 									<?php echo $coupon_code; ?>
	 								</th>
	 							<?php
	 							break;

	 							case 'discount': ?>
	 								<th scope="row">
	 									<?php
	 									if ($coupon['discount_type'] == 'pct') {
								echo $coupon['discount'].'%';
							 } else if ($coupon['discount_type'] == 'amt') {
								echo $this->format_currency('', $coupon['discount']);
							 }
							 ?>
	 								</th>
	 							<?php
	 							break;

	 							case 'start': ?>
	 								<th scope="row">
							 <?php echo date_i18n( get_option('date_format'), $coupon['start'] ); ?>
	 								</th>
	 							<?php
	 							break;

	 							case 'end': ?>
	 								<th scope="row">
	 									<?php echo ($coupon['end']) ? date_i18n( get_option('date_format'), $coupon['end'] ) : __('No End', 'mp'); ?>
	 								</th>
	 							<?php
	 							break;

	 							case 'used': ?>
	 								<th scope="row">
	 									<?php echo isset($coupon['used']) ? number_format_i18n($coupon['used']) : 0; ?>
	 								</th>
	 							<?php
	 							break;

	 							case 'remaining': ?>
	 								<th scope="row">
	 									<?php
							 if ($coupon['uses'])
								echo number_format_i18n(intval($coupon['uses']) - intval(@$coupon['used']));
							 else
								_e('Unlimited', 'mp');
							 ?>
	 								</th>
	 							<?php
	 							break;
								
								
								// RW - 
								case 'applies_to':
									?>
												<th scope="row">
												 	<?php
										if( !empty( $coupon['applies_to'] ) ) {
											$type = $coupon['applies_to']['type'];
											$id		 = $coupon['applies_to']['id'];
											//check to make sure the category or product still exists
											$details = ( $type == 'category') ? get_term($id , 'product_category'): get_post( $id );
											if(is_null( $details ) ) {
												$item = ( $type == 'category') ? 'Category' : 'Product';
												printf( __( 'Associated %s Deleted', $item , 'mp' ), $item );
											}else{
												$name = ( $type == 'category') ? $details->name : $details->post_title;
												$display = ( $type == 'category') ? 'All %s' : '%s';
												printf( __( $display, $name, 'mp' ), $name );
											}
										}else{
											_e('All Products','mp');
										}
									?>
												</th>
												<?php
								break;
								// /RW

						case 'edit': ?>
	 								<th scope="row">
	 									<a href="edit.php?post_type=product&amp;page=marketpress&amp;tab=coupons<?php echo $page_link; ?>&amp;code=<?php echo $coupon_code; ?>#add_coupon"><?php _e('Edit', 'mp') ?>&raquo;</a>
	 								</th>
	 							<?php
	 							break;

	 						}
	 					}
	 					?>
	 					</tr>
	 					<?php
	 				}
	 			} else {
	 				$bgcolor = ''; ?>
	 				<tr style='background-color: <?php echo $bgcolor; ?>'>
	 					<td colspan="7"><?php _e('No coupons yet.', 'mp') ?></td>
	 				</tr>
	 			<?php
	 			} // end if coupons
	 			?>

	 			</tbody>
	 			<tfoot>
	 				<tr>
	 				<th scope="col" class="check-column"><input type="checkbox" /></th>
	 				<?php foreach($posts_columns as $column_id => $column_display_name) {
	 					$col_url = $column_display_name;
	 					?>
	 					<th scope="col"><?php echo $col_url ?></th>
	 				<?php } ?>
	 				</tr>
	 			</tfoot>
	 		</table>

	 		<div class="tablenav">
	 			<?php if ( $coupon_navigation ) echo "<div class='tablenav-pages'>$coupon_navigation</div>"; ?>
	 		</div>

	 		<div id="poststuff" class="metabox-holder mp-settings">

	 		<div class="postbox">
			 <h3 class='hndle'><span>
			 <?php
			 if ( isset($_GET['code']) || (isset( $error ) && $error) ) {
				_e('Edit Coupon', 'mp');
			 } else {
				_e('Add Coupon', 'mp');
			 }
			 ?></span></h3>
			 <div class="inside">
				<?php
				//display error message if it exists
				if ( isset( $error ) && $error ) {
			 		?><div class="error"><p><?php echo implode("<br>\n", $error); ?></p></div><?php
			 	}

			 	//setup defaults
						if (isset($coupons[$new_coupon_code]))
							$discount = (isset($coupons[$new_coupon_code]['discount']) && isset($coupons[$new_coupon_code]['discount_type']) && $coupons[$new_coupon_code]['discount_type'] == 'amt') ? round($coupons[$new_coupon_code]['discount'], 2) : $coupons[$new_coupon_code]['discount'];
						else
							$discount = '';
						$discount_type = isset($coupons[$new_coupon_code]['discount_type']) ? $coupons[$new_coupon_code]['discount_type'] : '';
						$start = !empty($coupons[$new_coupon_code]['start']) ? date('Y-m-d', $coupons[$new_coupon_code]['start']) : date('Y-m-d');
						$end = !empty($coupons[$new_coupon_code]['end']) ? date('Y-m-d', $coupons[$new_coupon_code]['end']) : '';
						$uses = isset($coupons[$new_coupon_code]['uses']) ? $coupons[$new_coupon_code]['uses'] : '';
						//
						$applies_to = ( isset($coupons[$new_coupon_code]['applies_to']['type'] ) ) ? $coupons[$new_coupon_code]['applies_to']['type'] : 'all';
						$applies_to_id	 = ( isset( $coupons[$new_coupon_code]['applies_to']['id'] ) ) ? $coupons[$new_coupon_code]['applies_to']['id'] : '';
			 	?>
				<table id="add_coupon">
				<thead>
				<tr>
					<th>
					<?php _e('Coupon Code', 'mp') ?><br />
					 <small style="font-weight: normal;"><?php _e('Letters and Numbers only', 'mp') ?></small>
					 </th>
					<th><?php _e('Discount', 'mp') ?></th>
					<th><?php _e('Start Date', 'mp') ?></th>
					<th>
					 <?php _e('Expire Date', 'mp') ?><br />
					 <small style="font-weight: normal;"><?php _e('No end if blank', 'mp') ?></small>
					</th>
					<th>
					 <?php _e('Allowed Uses', 'mp') ?><br />
					 <small style="font-weight: normal;"><?php _e('Unlimited if blank', 'mp') ?></small>
					</th>
				 <!-- category/listing -->
					<th>
						<?php _e('Applies To', 'mp');?>
					</th>
					<!-- /category/listing -->
					</tr>
				</thead>
				<tbody>
				<tr>
					<td>
					 <input value="<?php echo $new_coupon_code ?>" name="coupon_code" type="text" style="text-transform: uppercase;" />
					</td>
					<td>
					 <input value="<?php echo $discount; ?>" size="3" name="discount" type="text" />
					 <select name="discount_type">
						 <option value="amt"<?php selected($discount_type, 'amt') ?>><?php echo $this->format_currency(); ?></option>
						 <option value="pct"<?php selected($discount_type, 'pct') ?>>%</option>
					 </select>
					</td>
					<td>
					 <input value="<?php echo $start; ?>" class="pickdate" size="11" name="start" type="text" />
					</td>
					<td>
					 <input value="<?php echo $end; ?>" class="pickdate" size="11" name="end" type="text" />
					</td>
					<td>
					 <input value="<?php echo $uses; ?>" size="4" name="uses" type="text" />
					</td>
					<td>
					<select id="applies_to" name="applies_to">
						<option value="all" <?php selected($applies_to, 'all');?>><?php _e('All Products','mp');?></option>
					 <option value="category" <?php selected($applies_to, 'category');?>><?php _e('Product Category','mp');?></option>
					 <option value="product" <?php selected($applies_to, 'product');?>><?php _e('Product','mp');?></option>
					</select>
					<script type="text/javascript">
						jQuery(document).ready(function ($) {
				 		$('#applies_to').change(function(){
						var type = 'coupon_' + $(this).val();
						switch(type) {
							case 'coupon_all':
								$('#coupon_category').hide();
								$('#coupon_product').hide();
							break;
							case 'coupon_category':
								$('#coupon_category').show();
								$('#coupon_product').hide();
							break;
							case 'coupon_product':
								$('#coupon_category').hide();
								$('#coupon_product').show();
							break;
							default:
						}
					});
				 })
				 </script>
					<?php
				 	//get all the product categories
				 	$product_cats = get_terms( array( 'product_category'), array( 'hide_empty' => false ) );
				if( !is_wp_error( $product_cats ) && count( $product_cats ) > 0 ) {
					$cat_style = ( $applies_to == 'category' ) ? '' : 'style="display:none;"';
					
					$cat_select = '<select name="coupon_category" id="coupon_category" '. $cat_style .' >%s</select>';
					$cat_options = '';
					foreach($product_cats as $cat) {
							$cat_options .= '<option value="'.$cat->term_id.'" '. selected( $applies_to_id, $cat->term_id, false ). '	>'.$cat->name.'</option>';
					}
					printf( $cat_select, $cat_options );
				}else{
					_e('No Product Categories','mp');
				}
				
				 	//get all the products
				
				$products = new WP_Query( array( 'post_type' => 'product', 'posts_per_page' => 500, 'update_post_term_cache' => false, 'update_post_meta_cache' => false ) );
				if( !is_wp_error( $products ) ) {
					if( $products->found_posts > 500) {
					?>
					<label id="coupon_product" <?php echo ( $applies_to == 'product' ) ? '' : 'style="display:none;"';?>><?php _e('Post ID','mp');?>
					<input type="text" value="<?php echo ( $applies_to == 'product' ) ? $applies_to_id : ''; ?>" name="coupon_product" />
					</label>
					<?php
					}else{
						$style = ( $applies_to == 'product' ) ? '' : 'style="display:none;"';
						$product_select = '<select name="coupon_product" id="coupon_product" '. $style .' >%s</select>';
						$product_options = '';
						foreach($products->posts as $product) {
							$product_options	 .= '<option value="'.$product->ID.'" '. selected( $applies_to_id, $product->ID, false ). '	>'.$product->post_title.'</option>';
						}
						printf( $product_select, $product_options );
					}
				}
				?>
					</td>
				</tr>
				</tbody>
				</table>

				<p class="submit">
					<input class="button-primary" type="submit" name="submit_settings" value="<?php _e('Save Coupon', 'mp') ?>" />
				</p>
			 </div>
			</div>

			</div>
	 		</form>
			<?php
			break;


			//---------------------------------------------------//
			case "presentation":

			//save settings
			if (isset($_POST['marketplace_settings'])) {
				//get old store slug
			 		$old_slug = $this->get_setting('slugs->store');

				//filter slugs
				$_POST['mp']['slugs'] = array_map('sanitize_title', (array)$_POST['mp']['slugs']);

					// Fixing http://premium.wpmudev.org/forums/topic/store-page-content-overwritten
					$new_slug = $_POST['mp']['slugs']['store'];
					$new_post_id = $wpdb->get_var( $wpdb->prepare("SELECT ID FROM " . $wpdb->posts . " WHERE post_name = %s AND post_type = 'page'", $new_slug) );

					if ($new_slug != $old_slug && $new_post_id != 0) {
						 echo '<div class="error fade"><p>'.__('Store base URL conflicts with another page', 'mp').'</p></div>';
					} else {
						 $settings = array_merge($settings, apply_filters('mp_presentation_settings_filter', $_POST['mp']));
						 update_option('mp_settings', $settings);

						 $this->create_store_page($old_slug);

						 //schedule flush rewrite rules due to product slugs on next page load (too late to do it here)
						update_option('mp_flush_rewrite', 1);

						 echo '<div class="updated fade"><p>'.__('Settings saved.', 'mp').'</p></div>';
					}
			}
			?>
			<script type="text/javascript">
			jQuery(document).ready(function($){
				$('input[name="mp[related_products][show]"]').change(function(){
					var $this = $(this),
						 $section = $('.presentation-related-products-settings');
					
					if ( $this.val() == 1 )
						$section.slideDown(300);
					else
						$section.slideUp(300);
				});
			});
			</script>
			<div class="icon32"><img src="<?php echo $this->plugin_url . 'images/my_work.png'; ?>" /></div>
			<h2><?php _e('Presentation Settings', 'mp'); ?></h2>
			<div id="poststuff" class="metabox-holder mp-settings">

			<form method="post" action="edit.php?post_type=product&amp;page=marketpress&amp;tab=presentation">
			 <input type="hidden" name="marketplace_settings" value="1" />

			 <div class="postbox presentation-general-settings">
				<h3 class='hndle'><span><?php _e('General Settings', 'mp') ?></span></h3>
				<div class="inside">
					<table class="form-table">
							<tr>
					 <th scope="row"><?php _e('Store Style', 'mp') ?></th>
						<td>
						<?php if ( MP_LITE === true ) { ?>
						<a class="mp-pro-update" href="https://premium.wpmudev.org/project/e-commerce/" title="<?php _e('Upgrade Now', 'mp'); ?> &raquo;"><?php _e('Upgrade to enable all styles &raquo;', 'mp'); ?></a><br />
						<?php } ?>
						<?php $this->store_themes_select(); ?>
						<br /><span class="description"><?php _e('This option changes the built-in css styles for store pages.', 'mp') ?></span>
						<?php if ((is_multisite() && is_super_admin()) || !is_multisite()) { ?>
						<br /><span class="description"><?php printf(__('For a custom css style, save your css file with the "MarketPress Style: NAME" header in the "%s/marketpress-styles/" folder and it will appear in this list so you may select it. You can also select "None" and create custom theme templates and css to make your own completely unique store design. More information on that <a href="%sthemes/Themeing_MarketPress.txt">here &raquo;</a>', 'mp'), WP_CONTENT_DIR, $this->plugin_url); ?></span>
						<h4><?php _e('Full-featured MarketPress Themes:', 'mp') ?></h4>
						<div class="mp-theme-preview"><a title="<?php _e('Download Now &raquo;', 'mp') ?>" href="https://premium.wpmudev.org/project/frame-market-theme/"><img alt="FrameMarket Theme" src="//premium.wpmudev.org/wp-content/projects/219/listing-image-thumb.png" />
							<strong><?php _e('FrameMarket/GridMarket', 'mp') ?></strong></a><br />
							<?php _e('The ultimate MarkePress theme brings visual perfection to WordPress e-commerce. This professional front-end does all the work for you!', 'mp') ?></div>
						<div class="mp-theme-preview"><a title="<?php _e('Download Now &raquo;', 'mp') ?>" href="https://premium.wpmudev.org/project/simplemarket/"><img alt="SimpleMarket Theme" src="//premium.wpmudev.org/wp-content/projects/237/listing-image-thumb.png" />
							<strong><?php _e('SimpleMarket', 'mp') ?></strong></a><br />
							<?php _e('The SimpleMarket Theme uses an HTML 5 responsive design so your e-commerce site looks great across all screen-sizes and devices such as smartphones or tablets!', 'mp') ?></div>
						<?php } ?>
						</td>
					 </tr>
					 <tr>
						<th scope="row"><?php _e('Show breadcrumbs for purchase process?', 'mp') ?></th>
						<td>
							<label><input value="1" name="mp[show_purchase_breadcrumbs]" type="radio"<?php checked($this->get_setting('show_purchase_breadcrumbs'), 1) ?> /> <?php _e('Yes', 'mp') ?></label>
							<label><input value="0" name="mp[show_purchase_breadcrumbs]" type="radio"<?php checked($this->get_setting('show_purchase_breadcrumbs'), 0) ?> /> <?php _e('No', 'mp') ?></label>
							<br /><span class="description"><?php _e('Show previous, current and next steps when a customer is purchasing their cart, shown below the title.', 'mp') ?></span>
						</td>
					 </tr>
					</table>
				</div>
			 </div>

			 <div class="postbox presentation-single-product-settings">
				<h3 class='hndle'><span><?php _e('Single Product Settings', 'mp') ?></span></h3>
				<div class="inside">
					<table class="form-table">
					 <tr>
							<th scope="row"><?php _e('Checkout Button Type', 'mp') ?></th>
							<td>
						<label><input value="addcart" name="mp[product_button_type]" type="radio"<?php checked($this->get_setting('product_button_type'), 'addcart') ?> /> <?php _e('Add To Cart', 'mp') ?></label><br />
						<label><input value="buynow" name="mp[product_button_type]" type="radio"<?php checked($this->get_setting('product_button_type'), 'buynow') ?> /> <?php _e('Buy Now', 'mp') ?></label>
							</td>
					 </tr>
					 <tr>
							<th scope="row"><?php _e('Show Quantity Option', 'mp') ?></th>
							<td>
						<label><input value="1" name="mp[show_quantity]" type="radio"<?php checked($this->get_setting('show_quantity'), 1) ?> /> <?php _e('Yes', 'mp') ?></label>
						<label><input value="0" name="mp[show_quantity]" type="radio"<?php checked($this->get_setting('show_quantity'), 0) ?> /> <?php _e('No', 'mp') ?></label>
							</td>
					 </tr>
								<tr>
							<th scope="row"><?php _e('Show Product Image', 'mp') ?></th>
							<td>
						<label><input value="1" name="mp[show_img]" type="radio"<?php checked($this->get_setting('show_img'), 1) ?> /> <?php _e('Yes', 'mp') ?></label>
						<label><input value="0" name="mp[show_img]" type="radio"<?php checked($this->get_setting('show_img'), 0) ?> /> <?php _e('No', 'mp') ?></label>
							</td>
					 </tr>
					 <tr>
							<th scope="row"><?php _e('Product Image Size', 'mp') ?></th>
							<td>
						<label><input value="thumbnail" name="mp[product_img_size]" type="radio"<?php checked($this->get_setting('product_img_size'), 'thumbnail') ?> /> <a href="options-media.php"><?php _e('WP Thumbnail size', 'mp') ?></a></label><br />
						<label><input value="medium" name="mp[product_img_size]" type="radio"<?php checked($this->get_setting('product_img_size'), 'medium') ?> /> <a href="options-media.php"><?php _e('WP Medium size', 'mp') ?></a></label><br />
						<label><input value="large" name="mp[product_img_size]" type="radio"<?php checked($this->get_setting('product_img_size'), 'large') ?> /> <a href="options-media.php"><?php _e('WP Large size', 'mp') ?></a></label><br />
						<label><input value="custom" name="mp[product_img_size]" type="radio"<?php checked($this->get_setting('product_img_size'), 'custom') ?> /> <?php _e('Custom', 'mp') ?></label>:&nbsp;&nbsp;
						<label><?php _e('Height', 'mp') ?><input size="3" name="mp[product_img_height]" value="<?php echo esc_attr($this->get_setting('product_img_height')) ?>" type="text" /></label>&nbsp;
						<label><?php _e('Width', 'mp') ?><input size="3" name="mp[product_img_width]" value="<?php echo esc_attr($this->get_setting('product_img_width')) ?>" type="text" /></label>
					 </td>
					 </tr>
					 <tr>
					 		<th scope="row"><?php _e('Product Image Alignment', 'mp'); ?></th>
					 		<td>
					 			<?php
					 			$alignments = array(
					 				'alignnone' => __('None', 'mp'),
					 				'aligncenter' => __('Center', 'mp'),
					 				'alignleft' => __('Left', 'mp'),
					 				'alignright' => __('Right', 'mp'),
					 			);
					 			foreach ( $alignments as $value => $label ) :
					 				$input_id = 'mp-image-align-single' . $value; ?>
					 			<label for="<?php echo $input_id; ?>"><input value="<?php echo $value; ?>" type="radio" name="mp[image_alignment_single]" id="<?php echo $input_id; ?>" <?php checked($this->get_setting('image_alignment_single'), $value); ?> /> <?php echo $label; ?></label><?php
					 			endforeach; ?>
					 		</td>
					 </tr>
					 <tr>
							<th scope="row"><?php _e('Show Image Lightbox', 'mp') ?></th>
							<td>
						<label><input value="1" name="mp[show_lightbox]" type="radio"<?php checked($this->get_setting('show_lightbox'), 1) ?> /> <?php _e('Yes', 'mp') ?></label>
						<label><input value="0" name="mp[show_lightbox]" type="radio"<?php checked($this->get_setting('show_lightbox'), 0) ?> /> <?php _e('No', 'mp') ?></label>
						<br /><span class="description"><?php _e('Makes clicking the single product image open an instant zoomed preview.', 'mp') ?></span>
					 </td>
					 </tr>
					 <tr>
							<th scope="row"><?php _e('Disable Large Image display', 'mp') ?></th>
							<td>
						<label><input value="1" name="mp[disable_large_image]" type="radio"<?php checked($this->get_setting('disable_large_image'), 1) ?> /> <?php _e('Yes', 'mp') ?></label>
						<label><input value="0" name="mp[disable_large_image]" type="radio"<?php checked($this->get_setting('disable_large_image'), 0) ?> /> <?php _e('No', 'mp') ?></label>
						<br /><span class="description"><?php _e('Disables "Display Larger Image" function. Clicking a product image will not display a larger image.', 'mp') ?></span>
					 </td>
					 </tr>
					 <tr>
					 	<th scope="row"><?php _e('Show Related Products', 'mp') ?></th>
						<td>
							<label><input value="1" name="mp[related_products][show]" type="radio"<?php checked($this->get_setting('related_products->show'), 1) ?> /> <?php _e('Yes', 'mp') ?></label>
							<label><input value="0" name="mp[related_products][show]" type="radio"<?php checked($this->get_setting('related_products->show'), 0) ?> /> <?php _e('No', 'mp') ?></label>
					 </td>
					</table>
				</div>
			 </div>

			<div class="postbox presentation-related-products-settings"<?php echo $this->get_setting('related_products->show', true) ? '' : ' style="display:none"'; ?>>
				<h3 class='hndle'><span><?php _e('Related Products Settings', 'mp') ?></span></h3>
				<div class="inside">
					<table class="form-table">
						<tr>
							<th scope="row"><?php _e('Related Product Limit', 'mp') ?></th>
							<td>
								<label><input name="mp[related_products][show_limit]" type="text" size="2" value="<?php echo intval($this->get_setting('related_products->show_limit') ); ?>" /></label>
							</td>
						</tr>
						<tr>
							<th scope="row"><?php _e('Relate Products By', 'mp') ?></th>
							<td>
								<select name="mp[related_products][relate_by]">
									<option value="both" <?php selected($this->get_setting('related_products->relate_by'), 'both'); ?>><?php _e('Category &amp; Tags', 'mp'); ?></option>
									<option value="category" <?php selected($this->get_setting('related_products->relate_by'), 'category'); ?>><?php _e('Category Only', 'mp'); ?></option>
									<option value="tags" <?php selected($this->get_setting('related_products->relate_by'), 'tags'); ?>><?php _e('Tags Only', 'mp'); ?></option>
								</select>
							</td>
						</tr>
						<tr>
							<th scope="row"><?php _e('Show Related Products As Simple List', 'mp') ?></th>
							<td>
								<label><input value="1" name="mp[related_products][simple_list]" type="radio"<?php checked($this->get_setting('related_products->simple_list'), 1) ?> /> <?php _e('Yes', 'mp') ?></label>
								<label><input value="0" name="mp[related_products][simple_list]" type="radio"<?php checked($this->get_setting('related_products->simple_list'), 0) ?> /> <?php _e('No', 'mp') ?></label>
								<br /><span class="description"><?php _e('Setting to "No" will use the List/Grid View setting.', 'mp') ?></span>
							</td>
						</tr>
					</table>
				</div>
			</div>

			 <div class="postbox presentation-product-list-settings">
				<h3 class='hndle'><span><?php _e('Product List Settings', 'mp') ?></span></h3>
				<div class="inside">
					<table class="form-table">
					 <tr>
							<th scope="row"><?php _e('Product List View', 'mp') ?></th>
							<td>
						<label><input value="list" name="mp[list_view]" type="radio"<?php checked($this->get_setting('list_view', 'grid'), 'list') ?> /> <?php _e('List View', 'mp') ?></label><br />
						<label><input value="grid" name="mp[list_view]" type="radio"<?php checked($this->get_setting('list_view', 'grid'), 'grid') ?> /> <?php _e('Grid View', 'mp') ?></label>
							</td>
					 </tr>
					 <tr>
							<th scope="row"><?php _e('Checkout Button Type', 'mp') ?></th>
							<td>
						<label><input value="addcart" name="mp[list_button_type]" type="radio"<?php checked($this->get_setting('list_button_type'), 'addcart') ?> /> <?php _e('Add To Cart', 'mp') ?></label><br />
						<label><input value="buynow" name="mp[list_button_type]" type="radio"<?php checked($this->get_setting('list_button_type'), 'buynow') ?> /> <?php _e('Buy Now', 'mp') ?></label>
							</td>
					 </tr>
					 <tr>
							<th scope="row"><?php _e('Show Product Thumbnail', 'mp') ?></th>
							<td>
						<label><input value="1" name="mp[show_thumbnail]" type="radio"<?php checked($this->get_setting('show_thumbnail'), 1) ?> /> <?php _e('Yes', 'mp') ?></label>
						<label><input value="0" name="mp[show_thumbnail]" type="radio"<?php checked($this->get_setting('show_thumbnail'), 0) ?> /> <?php _e('No', 'mp') ?></label>
							</td>
					 </tr>
					 <tr>
							<th scope="row"><?php _e('Product Thumbnail Size', 'mp') ?></th>
							<td>
						<label><input value="thumbnail" name="mp[list_img_size]" type="radio"<?php checked($this->get_setting('list_img_size'), 'thumbnail') ?> /> <a href="options-media.php"><?php _e('WP Thumbnail size', 'mp') ?></a></label><br />
						<label><input value="medium" name="mp[list_img_size]" type="radio"<?php checked($this->get_setting('list_img_size'), 'medium') ?> /> <a href="options-media.php"><?php _e('WP Medium size', 'mp') ?></a></label><br />
						<label><input value="large" name="mp[list_img_size]" type="radio"<?php checked($this->get_setting('list_img_size'), 'large') ?> /> <a href="options-media.php"><?php _e('WP Large size', 'mp') ?></a></label><br />
						<label><input value="custom" name="mp[list_img_size]" type="radio"<?php checked($this->get_setting('list_img_size'), 'custom') ?> /> <?php _e('Custom', 'mp') ?></label>:&nbsp;&nbsp;
						<label><?php _e('Height', 'mp') ?><input size="3" name="mp[list_img_height]" value="<?php echo esc_attr($this->get_setting('list_img_height')) ?>" type="text" /></label>&nbsp;
						<label><?php _e('Width', 'mp') ?><input size="3" name="mp[list_img_width]" value="<?php echo esc_attr($this->get_setting('list_img_width')) ?>" type="text" /></label>
					 </td>
					 <tr>
					 		<th scope="row"><?php _e('Product Thumbnail Alignment', 'mp'); ?></th>
					 		<td>
					 			<?php
					 			$alignments = array(
					 				'alignnone' => __('None', 'mp'),
					 				'aligncenter' => __('Center', 'mp'),
					 				'alignleft' => __('Left', 'mp'),
					 				'alignright' => __('Right', 'mp'),
					 			);
					 			foreach ( $alignments as $value => $label ) :
					 				$input_id = 'mp-image-align-list' . $value; ?>
					 			<label for="<?php echo $input_id; ?>"><input value="<?php echo $value; ?>" type="radio" name="mp[image_alignment_list]" id="<?php echo $input_id; ?>" <?php checked($this->get_setting('image_alignment_list'), $value); ?> /> <?php echo $label; ?></label><?php
					 			endforeach; ?>
					 		</td>
					 </tr>					 
					 </tr>
								<tr>
									<th scope="row"><?php _e('Show Excerpts', 'mp') ?></th>
									<td>
									<label><input value="1" name="mp[show_excerpt]" type="radio"<?php checked($this->get_setting('show_excerpt'), 1) ?> /> <?php _e('Yes', 'mp') ?></label>
									<label><input value="0" name="mp[show_excerpt]" type="radio"<?php checked($this->get_setting('show_excerpt'), 0) ?> /> <?php _e('No', 'mp') ?></label>
									</td>
								</tr>
					 <tr>
							<th scope="row"><?php _e('Paginate Products', 'mp') ?></th>
							<td>
						<label><input value="1" name="mp[paginate]" type="radio"<?php checked($this->get_setting('paginate'), 1) ?> /> <?php _e('Yes', 'mp') ?></label>
						<label><input value="0" name="mp[paginate]" type="radio"<?php checked($this->get_setting('paginate'), 0) ?> /> <?php _e('No', 'mp') ?></label>&nbsp;&nbsp;
						<label><input value="<?php echo esc_attr($this->get_setting('per_page', 20)) ?>" name="mp[per_page]" type="text" size="2" /> <?php _e('Products per page', 'mp') ?></label>
							</td>
					 </tr>
					 <tr>
							<th scope="row"><?php _e('Order Products By', 'mp') ?></th>
							<td>
						<select name="mp[order_by]">
							<option value="title"<?php selected($this->get_setting('order_by'), 'title') ?>><?php _e('Product Name', 'mp') ?></option>
							<option value="date"<?php selected($this->get_setting('order_by'), 'date') ?>><?php _e('Publish Date', 'mp') ?></option>
							<option value="ID"<?php selected($this->get_setting('order_by'), 'ID') ?>><?php _e('Product ID', 'mp') ?></option>
							<option value="author"<?php selected($this->get_setting('order_by'), 'author') ?>><?php _e('Product Author', 'mp') ?></option>
							<option value="sales"<?php selected($this->get_setting('order_by'), 'sales') ?>><?php _e('Number of Sales', 'mp') ?></option>
							<option value="price"<?php selected($this->get_setting('order_by'), 'price') ?>><?php _e('Product Price', 'mp') ?></option>
							<option value="rand"<?php selected($this->get_setting('order_by'), 'rand') ?>><?php _e('Random', 'mp') ?></option>
						</select>
						<label><input value="DESC" name="mp[order]" type="radio"<?php checked($this->get_setting('order'), 'DESC') ?> /> <?php _e('Descending', 'mp') ?></label>
						<label><input value="ASC" name="mp[order]" type="radio"<?php checked($this->get_setting('order'), 'ASC') ?> /> <?php _e('Ascending', 'mp') ?></label>
					</td>
					 </tr>
					 <tr>
						<th scope="row"><?php _e('Show Product Filters', 'mp') ?></th>
						<td> 
							<label><input value="1" name="mp[show_filters]" type="radio"<?php checked($this->get_setting('show_filters'), 1) ?> /> <?php _e('Yes', 'mp') ?></label>
							<label><input value="0" name="mp[show_filters]" type="radio"<?php checked($this->get_setting('show_filters'), 0) ?> /> <?php _e('No', 'mp') ?></label>
							<br />
							<span class="description"><?php _e('Show "Product Category" and "Order By" filters at the top of listings pages. Uses AJAX for instant updates based on user selection.', 'mp') ?></span>
						</td>
					 </tr>
					</table>
				</div>
			 </div>

			 <div class="postbox store-url-slugs">
				<h3 class='hndle'><span><?php _e('Store URL Slugs', 'mp') ?></span> - <span class="description"><?php _e('Customizes the url structure of your store', 'mp') ?></span></h3>
				<div class="inside">
					<table class="form-table">
					 <tr valign="top">
					 <th scope="row"><?php _e('Store Base', 'mp') ?></th>
					 <td>/<input type="text" name="mp[slugs][store]" value="<?php echo esc_attr($this->get_setting('slugs->store')); ?>" size="20" maxlength="50" />/<br />
					 <span class="description"><?php _e('This page will be created so you can change it\'s content and the order in which it appears in navigation menus if your theme supports it.', 'mp') ?></span></td>
					 </tr>
					 <tr valign="top">
					 <th scope="row"><?php _e('Products List', 'mp') ?></th>
					 <td>/<?php echo esc_attr($this->get_setting('slugs->store')); ?>/<input type="text" name="mp[slugs][products]" value="<?php echo esc_attr($this->get_setting('slugs->products')); ?>" size="20" maxlength="50" />/</td>
					 </tr>
					 <tr valign="top">
					 <th scope="row"><?php _e('Shopping Cart Page', 'mp') ?></th>
					 <td>/<?php echo esc_attr($this->get_setting('slugs->store')); ?>/<input type="text" name="mp[slugs][cart]" value="<?php echo esc_attr($this->get_setting('slugs->cart')); ?>" size="20" maxlength="50" />/</td>
					 </tr>
					 <tr valign="top">
					 <th scope="row"><?php _e('Order Status Page', 'mp') ?></th>
					 <td>/<?php echo esc_attr($this->get_setting('slugs->store')); ?>/<input type="text" name="mp[slugs][orderstatus]" value="<?php echo esc_attr($this->get_setting('slugs->orderstatus')); ?>" size="20" maxlength="50" />/</td>
					 </tr>
					 <tr valign="top">
					 <th scope="row"><?php _e('Product Category', 'mp') ?></th>
					 <td>/<?php echo esc_attr($this->get_setting('slugs->store')); ?>/<?php echo esc_attr($this->get_setting('slugs->products')); ?>/<input type="text" name="mp[slugs][category]" value="<?php echo esc_attr($this->get_setting('slugs->category')); ?>" size="20" maxlength="50" />/</td>
					 </tr>
					 <tr valign="top">
					 <th scope="row"><?php _e('Product Tag', 'mp') ?></th>
					 <td>/<?php echo esc_attr($this->get_setting('slugs->store')); ?>/<?php echo esc_attr($this->get_setting('slugs->products')); ?>/<input type="text" name="mp[slugs][tag]" value="<?php echo esc_attr($this->get_setting('slugs->tag')); ?>" size="20" maxlength="50" />/</td>
					 </tr>
					</table>
				</div>
			 </div>
			 
			 <!-- pinterest Rich Pins/oEmbed -->
			 <div class="postbox presentation-social">
			 	<h3 class="hndle"><span><?php _e('Social','mp');?></span></h3>
				<div class="inside">
				<img src="<?php echo $this->plugin_url; ?>images/134x35_pinterest_logo.png" width="134" height="35" alt="Pinterest">
<table class="form-table">
				 		<tr>
				 	<th scope="row"><?php _e('Show "Pin It" button','mp');?></th>
					 	<td>
								<label><input value="off" name="mp[social][pinterest][show_pinit_button]" type="radio"<?php checked($this->get_setting('social->pinterest->show_pinit_button'), 'off') ?> /> <?php _e('Off', 'mp') ?></label><br/>
								<label><input value="single_view" name="mp[social][pinterest][show_pinit_button]" type="radio"<?php checked($this->get_setting('social->pinterest->show_pinit_button'), 'single_view') ?> /> <?php _e('Single View', 'mp') ?></label><br/>
								<label><input value="all_view" name="mp[social][pinterest][show_pinit_button]" type="radio"<?php checked($this->get_setting('social->pinterest->show_pinit_button'), 'all_view') ?> /> <?php _e('All View', 'mp') ?></label>
							</td>
					 </tr>
					 <tr>
					 	<th scope="row"><?php _e('Pin Count','mp');?></th>
							<td>
								<label><input value="none" name="mp[social][pinterest][show_pin_count]" type="radio"<?php checked($this->get_setting('social->pinterest->show_pin_count'), 'none') ?> /> <?php _e('None', 'mp') ?></label><br/>
								<label><input value="above" name="mp[social][pinterest][show_pin_count]" type="radio"<?php checked($this->get_setting('social->pinterest->show_pin_count'), 'above') ?> /> <?php _e('Above', 'mp') ?></label><br/>
								<label><input value="beside" name="mp[social][pinterest][show_pin_count]" type="radio"<?php checked($this->get_setting('social->pinterest->show_pin_count'), 'beside') ?> /> <?php _e('Beside', 'mp') ?></label>
							</td>
					 </tr>
				</table>
				</div>
			 </div><!-- /pinterest Rich Pins/oEmbed -->
			 <?php do_action('mp_presentation_settings'); ?>

			 <p class="submit">
				<input class="button-primary" type="submit" name="submit_settings" value="<?php _e('Save Changes', 'mp') ?>" />
			 </p>
			</form>
			</div>
			<?php
			break;


			//---------------------------------------------------//
			case "messages":
			//save settings
			if (isset($_POST['messages_settings'])) {

			 //remove html from emails
			 $_POST['mp']['email'] = array_map('wp_filter_nohtml_kses', (array)$_POST['mp']['email']);

			 //filter msg inputs if necessary
			 if (!current_user_can('unfiltered_html')) {
				$_POST['mp']['msg'] = array_map('wp_kses_post', (array)$_POST['mp']['msg']);
			 }
					
					//strip slashes
			 $_POST['mp']['msg'] = array_map('stripslashes', (array)$_POST['mp']['msg']);
			 $_POST['mp']['email'] = array_map('stripslashes', (array)$_POST['mp']['email']);
					
					//wpautop
					$_POST['mp']['msg'] = array_map('wpautop', (array)$_POST['mp']['msg']);
					
			 $settings = array_merge($settings, apply_filters('mp_messages_settings_filter', $_POST['mp']));
			 update_option('mp_settings', $settings);

			 echo '<div class="updated fade"><p>'.__('Settings saved.', 'mp').'</p></div>';
			}
			?>
			<div class="icon32"><img src="<?php echo $this->plugin_url . 'images/messages.png'; ?>" /></div>
			<h2><?php _e('Messages Settings', 'mp'); ?></h2>
			<div id="poststuff" class="metabox-holder mp-settings">

			<form id="mp-messages-form" method="post" action="edit.php?post_type=product&amp;page=marketpress&amp;tab=messages">
			 <input type="hidden" name="messages_settings" value="1" />

			 <div class="postbox email-notifications">
				<h3 class='hndle'><span><?php _e('Email Notifications', 'mp') ?></span></h3>
				<div class="inside">
					<table class="form-table">
							<tr>
							<th scope="row"><?php _e('Store Admin Email', 'mp'); ?></th>
							<td>
								<?php $store_email = $this->get_setting('store_email') ? $this->get_setting('store_email') : get_option("admin_email"); ?>
							<span class="description"><?php _e('The email address that new order notifications are sent to and received from.', 'mp') ?></span><br />
					 <input type="text" name="mp[store_email]" value="<?php echo esc_attr($store_email); ?>" maxlength="150" size="50" />
					 </td>
					 </tr>
					 <tr>
							<th scope="row"><?php _e('New Order', 'mp'); ?></th>
							<td>
							<span class="description"><?php _e('The email text sent to your customer to confirm a new order. These codes will be replaced with order details: CUSTOMERNAME, ORDERID, ORDERINFO, SHIPPINGINFO, PAYMENTINFO, TOTAL, TRACKINGURL, ORDERNOTES. No HTML allowed.', 'mp') ?></span><br />
					 <label><?php _e('Subject:', 'mp'); ?><br />
					 <input type="text" class="mp_emails_sub" name="mp[email][new_order_subject]" value="<?php echo esc_attr($this->get_setting('email->new_order_subject')); ?>" maxlength="150" /></label><br />
					 <label><?php _e('Text:', 'mp'); ?><br />
					 <textarea class="mp_emails_txt" name="mp[email][new_order_txt]"><?php echo esc_textarea($this->get_setting('email->new_order_txt')); ?></textarea>
					 </label>
					 </td>
					 </tr>
					 <tr>
							<th scope="row"><?php _e('Order Shipped', 'mp'); ?></th>
							<td>
							<span class="description"><?php _e('The email text sent to your customer when you mark an order as "Shipped". These codes will be replaced with order details: CUSTOMERNAME, ORDERID, ORDERINFO, SHIPPINGINFO, PAYMENTINFO, TOTAL, TRACKINGURL, ORDERNOTES. No HTML allowed.', 'mp') ?></span><br />
					 <label><?php _e('Subject:', 'mp'); ?><br />
					 <input type="text" class="mp_emails_sub" name="mp[email][shipped_order_subject]" value="<?php echo esc_attr($this->get_setting('email->shipped_order_subject')); ?>" maxlength="150" /></label><br />
					 <label><?php _e('Text:', 'mp'); ?><br />
					 <textarea class="mp_emails_txt" name="mp[email][shipped_order_txt]"><?php echo esc_textarea($this->get_setting('email->shipped_order_txt')); ?></textarea>
					 </label>
					 </td>
					 </tr>
					</table>
				</div>
			 </div>

			 <div class="postbox mp-pages-msgs store-pages">
				<h3 class='hndle'><span><?php _e('Store Pages', 'mp') ?></span></h3>
				<div class="inside">
					<table class="form-table">
					 <tr>
							<th scope="row"><?php _e('Store Page', 'mp'); ?></th>
							<td>
							<span class="description"><?php _e('The main store page is an actual page on your site. You can edit it here:', 'mp') ?></span>
							<?php
					 $post_id = get_option('mp_store_page');
					 edit_post_link(__('Edit Page &raquo;', 'mp'), '', '', $post_id);
					 ?>
							</td>
					 </tr>
					 <tr>
							<th scope="row"><?php _e('Product Listing Pages', 'mp'); ?></th>
							<td>
							<span class="description"><?php _e('Displayed at the top of the product listing pages. Optional, HTML allowed.', 'mp') ?></span><br />
							<?php wp_editor( $this->get_setting('msg->product_list'), 'product_list', array('textarea_name'=>'mp[msg][product_list]') ); ?>
								</td>
					 </tr>
					 <tr>
							<th scope="row"><?php _e('Order Status Page', 'mp'); ?></th>
							<td>
							<span class="description"><?php _e('Displayed at the top of the Order Status page. Optional, HTML allowed.', 'mp') ?></span><br />
								<?php wp_editor( $this->get_setting('msg->order_status'), 'order_status', array('textarea_name'=>'mp[msg][order_status]') ); ?>
							</td>
					 </tr>
					</table>
				</div>
			 </div>

			 <div class="postbox mp-pages-msgs stopping-cart-pages">
				<h3 class='hndle'><span><?php _e('Shopping Cart Pages', 'mp') ?></span></h3>
				<div class="inside">
					<table class="form-table">
					 <tr>
							<th scope="row"><?php _e('Shopping Cart Page', 'mp'); ?></th>
							<td>
							<span class="description"><?php _e('Displayed at the top of the Shopping Cart page. Optional, HTML allowed.', 'mp') ?></span><br />
								<?php wp_editor( $this->get_setting('msg->cart'), 'cart', array('textarea_name'=>'mp[msg][cart]') ); ?>
							</td>
					 </tr>
					 <tr id="mp_msgs_shipping">
							<th scope="row"><?php _e('Shipping Form Page', 'mp'); ?></th>
							<td>
							<span class="description"><?php _e('Displayed at the top of the Shipping Form page. Optional, HTML allowed.', 'mp') ?></span><br />
								<?php wp_editor( $this->get_setting('msg->shipping'), 'shipping', array('textarea_name'=>'mp[msg][shipping]') ); ?>
							</td>
					 </tr>
					 <tr>
							<th scope="row"><?php _e('Payment Form Page', 'mp'); ?></th>
							<td>
							<span class="description"><?php _e('Displayed at the top of the Payment Form page. Optional, HTML allowed.', 'mp') ?></span><br />
								<?php wp_editor( $this->get_setting('msg->checkout'), 'checkout', array('textarea_name'=>'mp[msg][checkout]') ); ?>
							</td>
					 </tr>
					 <tr>
							<th scope="row"><?php _e('Order Confirmation Page', 'mp'); ?></th>
							<td>
							<span class="description"><?php _e('Displayed at the top of the final Order Confirmation page. HTML allowed.', 'mp') ?></span><br />
								<?php wp_editor( $this->get_setting('msg->confirm_checkout'), 'confirm_checkout', array('textarea_name'=>'mp[msg][confirm_checkout]') ); ?>
							</td>
					 </tr>
					 <tr>
							<th scope="row"><?php _e('Order Complete Page', 'mp'); ?></th>
							<td>
							<span class="description"><?php _e('Displayed at the top of the page notifying customers of a successful order. HTML allowed.', 'mp') ?></span><br />
								<?php wp_editor( $this->get_setting('msg->success'), 'success', array('textarea_name'=>'mp[msg][success]') ); ?>
							</td>
					 </tr>
					</table>
				</div>
			 </div>

			 <?php
			 //for adding additional messages
			 do_action('mp_messages_settings', $settings);
			 ?>

			 <p class="submit">
				<input class="button-primary" type="submit" name="submit_settings" value="<?php _e('Save Changes', 'mp') ?>" />
			 </p>
			</form>
			</div>
			<?php
			break;


		//---------------------------------------------------//
			case "shipping":
				global $mp_shipping_plugins;

				//save settings from screen. Put here to be before plugin is loaded
				if (isset($_POST['shipping_settings'])) {
					$settings = get_option('mp_settings');
					//allow plugins to verify settings before saving
					$settings = $this->parse_args_r($settings, apply_filters('mp_shipping_settings_filter', $_POST['mp']));
					//loop through allowed countries checkboxes and remove unchecked options
					if ( !isset($_POST['mp']['shipping']['allowed_countries']) ) {
						$settings['shipping']['allowed_countries'] = array();
					} else {
						foreach ( $settings['shipping']['allowed_countries'] as $key => $code ) {
							if ( !in_array($code, (array) $_POST['mp']['shipping']['allowed_countries']) ) {
								unset($settings['shipping']['allowed_countries'][$key]);
							}
						}
					}
					
					update_option('mp_settings', $settings);
			 echo '<div class="updated fade"><p>'.__('Settings saved.', 'mp').'</p></div>';
				}
			?>
			<script type="text/javascript">
				jQuery(document).ready(function ($) {
				$("#mp-select-all").click(function() {
					$("#mp-target-countries input[type='checkbox']").prop('checked', true);
					return false;
				});
				$("#mp-select-eu").click(function() {
					$("#mp-target-countries input[type='checkbox']").prop('checked', false).filter('.eu').prop('checked', true);
					return false;
				});
				$("#mp-select-none").click(function() {
					$("#mp-target-countries input[type='checkbox']").prop('checked', false);
					return false;
				});
				$(".mp-shipping-method").change(function() {
					$("#mp-shipping-form").submit();
					});
				});
			</script>
			<div class="icon32"><img src="<?php echo $this->plugin_url . 'images/delivery.png'; ?>" /></div>
			<h2><?php _e('Shipping Settings', 'mp'); ?></h2>
			<div id="poststuff" class="metabox-holder mp-settings">

			<form id="mp-shipping-form" method="post" action="edit.php?post_type=product&amp;page=marketpress&amp;tab=shipping">
			 <input type="hidden" name="shipping_settings" value="1" />

			 <div id="mp_flat_rate" class="postbox">
				<h3 class='hndle'><span><?php _e('General Settings', 'mp') ?></span></h3>
				<div class="inside">
					<table class="form-table">

					 <tr>
							<th scope="row"><?php _e('Choose Target Countries', 'mp') ?></th>
							<td>
						<div><?php _e('Select:', 'mp') ?> <a id="mp-select-all" href="#"><?php _e('All', 'mp') ?></a>&nbsp; <a id="mp-select-eu" href="#"><?php _e('EU', 'mp') ?></a>&nbsp; <a id="mp-select-none" href="#"><?php _e('None', 'mp') ?></a></div>
						<div id="mp-target-countries">
						<?php
							foreach ($this->countries as $code => $name) {
							 ?><label for="mp-target-country-<?php echo $code; ?>"><input id="mp-target-country-<?php echo $code; ?>" type="checkbox"<?php echo (in_array($code, $this->eu_countries)) ? ' class="eu"' : ''; ?> name="mp[shipping][allowed_countries][]" value="<?php echo $code; ?>"<?php echo (in_array($code, $this->get_setting('shipping->allowed_countries', array()))) ? ' checked="checked"' : ''; ?> /> <?php echo esc_attr($name); ?></label><br /><?php
							}
						?>
						</div><br />
						<span class="description"><?php _e('These are the countries you will sell and ship to.', 'mp') ?></span>
			 			</td>
					 </tr>

					 <tr>
							<th scope="row"><?php _e('Select Shipping Method', 'mp') ?></th>
							<td>
						<select name="mp[shipping][method]" class="mp-shipping-method">
							<option value="none"<?php selected($this->get_setting('shipping->method'), 'none'); ?>><?php _e('No Shipping', 'mp'); ?></option>
							<?php
										$calculated_methods = 0;
							foreach ((array)$mp_shipping_plugins as $code => $plugin) {
											if ($plugin[2]) {
												$calculated_methods++;
												continue;
											}
							 ?><option value="<?php echo $code; ?>"<?php selected($this->get_setting('shipping->method'), $code); ?>><?php echo esc_attr($plugin[1]); ?></option><?php
							}
										if ($calculated_methods) {
											?><option value="calculated"<?php selected($this->get_setting('shipping->method'), 'calculated'); ?>><?php _e('Calculated Options', 'mp'); ?></option><?php
										}
							?>
						</select>
			 				</td>
					 </tr>
								<?php
								if ($calculated_methods && $this->get_setting('shipping->method') == 'calculated') {
								?>
								<tr>
								<th scope="row"><?php _e('Select Shipping Options', 'mp') ?></th>
								<td>
									<?php if ( MP_LITE === true ) { ?>
									<a class="mp-pro-update" href="https://premium.wpmudev.org/project/e-commerce/" title="<?php _e('Upgrade Now', 'mp'); ?> &raquo;"><?php _e('Upgrade to enable Calculated Shipping options &raquo;', 'mp'); ?></a><br />
									<?php } ?>
									<span class="description"><?php _e('Select which calculated shipping methods the customer will be able to choose from:', 'mp') ?></span><br />
									<?php
										foreach ((array)$mp_shipping_plugins as $code => $plugin) {
											if (!$plugin[2]) continue; //skip non calculated
											?><label><input type="checkbox" class="mp-shipping-method" name="mp[shipping][calc_methods][<?php echo $code; ?>]" value="<?php echo $code; ?>"<?php echo $this->get_setting("shipping->calc_methods->$code") ? ' checked="checked"' : ''; echo MP_LITE === true ? ' disabled="disabled"' : ''; ?> /> <?php echo esc_attr($plugin[1]); ?></label><br /><?php
										}
									?>
								</td>
								</tr>
								<?php
								}
								?>
								<tr>
							<th scope="row"><?php _e('Measurement System', 'mp') ?></th>
							<td>
						<label><input value="english" name="mp[shipping][system]" type="radio"<?php checked($this->get_setting('shipping->system'), 'english') ?> /> <?php _e('English (Pounds)', 'mp') ?></label>
						<label><input value="metric" name="mp[shipping][system]" type="radio"<?php checked($this->get_setting('shipping->system'), 'metric') ?> /> <?php _e('Metric (Kilograms)', 'mp') ?></label>
							</td>
					 </tr>
					</table>
				</div>
			 </div>

			 <?php
			 //for adding additional settings for a shipping module
			 do_action('mp_shipping_settings', $settings);
			 ?>

			 <p class="submit">
				<input class="button-primary" type="submit" name="submit_settings" value="<?php _e('Save Changes', 'mp') ?>" />
			 </p>
			</form>
			</div>
			<?php
			break;


		//---------------------------------------------------//
			case "gateways":
			global $mp_gateway_plugins;

			//save settings
			if (isset($_POST['gateway_settings'])) {
					if ( isset( $_POST['mp'] ) ) {
						$filtered_settings = apply_filters('mp_gateway_settings_filter', $_POST['mp']);
						//allow plugins to verify settings before saving
						$settings = $this->parse_args_r($settings, $filtered_settings);
						
						update_option('mp_settings', $settings);
					}
			 echo '<div class="updated fade"><p>'.__('Settings saved.', 'mp').'</p></div>';
			}
			?>
			<script type="text/javascript">
				jQuery(document).ready(function ($) {
				$("input.mp_allowed_gateways").change(function() {
					$("#mp-gateways-form").submit();
					});
			 }); 
			</script>
			<div class="icon32"><img src="<?php echo $this->plugin_url . 'images/credit-cards.png'; ?>" /></div>
			<h2><?php _e('Payment Settings', 'mp'); ?></h2>
			<div id="poststuff" class="metabox-holder mp-settings">

			<form id="mp-gateways-form" method="post" action="edit.php?post_type=product&amp;page=marketpress&amp;tab=gateways">
			 <input type="hidden" name="gateway_settings" value="1" />

					<?php if (!$this->global_cart) { ?>
			 <div id="mp_gateways" class="postbox">
				<h3 class='hndle'><span><?php _e('General Settings', 'mp') ?></span></h3>
				<div class="inside">
					<table class="form-table">
					 <tr>
							<th scope="row"><?php _e('Select Payment Gateway(s)', 'mp') ?></th>
							<td>
					 <?php
					 //check network permissions
					 if (is_multisite() && !is_main_site() && !is_super_admin()) {
						$network_settings = get_site_option( 'mp_network_settings' );
						foreach ((array)$mp_gateway_plugins as $code => $plugin) {
							if ($network_settings['allowed_gateways'][$code] == 'full') {
							 $allowed_plugins[$code] = $plugin;
							} else if ($network_settings['allowed_gateways'][$code] == 'supporter' && function_exists('is_pro_site') && is_pro_site(false, $network_settings['gateways_pro_level'][$code]) ) {

							 $allowed_plugins[$code] = $plugin;
							}
						}
						$mp_gateway_plugins = $allowed_plugins;
					 }

					 foreach ((array)$mp_gateway_plugins as $code => $plugin) {
						if ($plugin[3]) { //if demo
							?><label><input type="checkbox" class="mp_allowed_gateways" name="mp[gateways][allowed][]" value="<?php echo $code; ?>" disabled="disabled" /> <?php echo esc_attr($plugin[1]); ?></label> <a class="mp-pro-update" href="https://premium.wpmudev.org/project/e-commerce" title="<?php _e('Upgrade', 'mp'); ?> &raquo;"><?php _e('Pro Only &raquo;', 'mp'); ?></a><br /><?php
									} else {
							?><label><input type="checkbox" class="mp_allowed_gateways" name="mp[gateways][allowed][]" value="<?php echo $code; ?>"<?php echo (in_array($code, $this->get_setting('gateways->allowed', array()))) ? ' checked="checked"' : ''; ?> /> <?php echo esc_attr($plugin[1]); ?></label><br /><?php
									}
								}
					 ?>
							</td>
					 </tr>
					</table>
				</div>
			 </div>
			 <?php } ?>

			 <?php
			 //for adding additional settings for a payment gateway plugin
			 do_action('mp_gateway_settings', $settings);
			 ?>

			 <p class="submit">
				<input class="button-primary" type="submit" name="submit_settings" value="<?php _e('Save Changes', 'mp') ?>" />
			 </p>
			</form>
			</div>
			<?php
			break;


			//---------------------------------------------------//
			case "shortcodes":
			?>
			<div class="icon32"><img src="<?php echo $this->plugin_url . 'images/help.png'; ?>" /></div>
			<h2><?php _e('MarketPress Shortcodes', 'mp'); ?></h2>
			<div id="poststuff" class="metabox-holder mp-settings">

			 <!--
			 <div class="postbox">
				<h3 class='hndle'><span><?php _e('General Information', 'mp') ?></span></h3>
				<div class="inside">
					<iframe src="//premium.wpmudev.org/wdp-un.php?action=help&id=144" width="100%" height="400px"></iframe>
				</div>
			 </div>
			 -->

			 <div class="postbox">
				<h3 class='hndle'><span><?php _e('Shortcodes', 'mp') ?></span></h3>
				<div class="inside">
					<p><?php _e('Shortcodes allow you to include dynamic store content in posts and pages on your site. Simply type or paste them into your post or page content where you would like them to appear. Optional attributes can be added in a format like <em>[shortcode attr1="value" attr2="value"]</em>.', 'mp') ?></p>
					<table class="form-table">
					 <tr>
							<th scope="row"><?php _e('Product Tag Cloud', 'mp') ?></th>
							<td>
						<strong>[mp_tag_cloud]</strong> -
						<span class="description"><?php _e('Displays a cloud or list of your product tags.', 'mp') ?></span>
						<a href="http://codex.wordpress.org/Template_Tags/wp_tag_cloud"><?php _e('Optional Attributes &raquo;', 'mp') ?></a>
					 </td>
					 </tr>
					 <tr>
							<th scope="row"><?php _e('Product Categories List', 'mp') ?></th>
							<td>
						<strong>[mp_list_categories]</strong> -
						<span class="description"><?php _e('Displays an HTML list of your product categories.', 'mp') ?></span>
						<a href="http://codex.wordpress.org/Template_Tags/wp_list_categories"><?php _e('Optional Attributes &raquo;', 'mp') ?></a>
					 </td>
					 </tr>
					 <tr>
							<th scope="row"><?php _e('Product Categories Dropdown', 'mp') ?></th>
							<td>
						<strong>[mp_dropdown_categories]</strong> -
						<span class="description"><?php _e('Displays an HTML dropdown of your product categories.', 'mp') ?></span>
						<a href="http://codex.wordpress.org/Template_Tags/wp_dropdown_categories"><?php _e('Optional Attributes &raquo;', 'mp') ?></a>
					 </td>
					 </tr>
					 <tr>
							<th scope="row"><?php _e('Popular Products List', 'mp') ?></th>
							<td>
						<strong>[mp_popular_products]</strong> -
						<span class="description"><?php _e('Displays a list of popular products ordered by sales.', 'mp') ?></span>
						<p>
						<strong><?php _e('Optional Attributes:', 'mp') ?></strong>
						<ul class="mp-shortcode-options">
							<li><?php _e('"number" - max number of products to display. Defaults to 5.', 'mp') ?></li>
							<li><?php _e('Example:', 'mp') ?> <em>[mp_popular_products number="5"]</em></li>
						</ul></p>
					 </td>
					 </tr>
					 <tr>
							<th scope="row"><?php _e('Related Products', 'mp') ?></th>
							<td>
						<strong>[mp_related_products]</strong> -
						<span class="description"><?php _e('Displays a products related to the one being viewed.', 'mp') ?></span>
						<p>
						<strong><?php _e('Optional Attributes:', 'mp') ?></strong>
						<ul class="mp-shortcode-options">
										<li><?php _e('"product_id" - The product to show related items for.', 'mp') ?></li>
										<li><?php _e('"relate_by" - Whether to limit the related items to products in the same category, tags or both. Defaults to the value set in presentation settings..', 'mp') ?></li>
										<li><?php _e('"limit" - How many related items to show. Defaults to the value set in presentation settings.', 'mp') ?></li>
										<li><?php _e('"simple_list" - Whether to display the items as a simple list or based on the list/grid view setting. Defaults to the value set in presentation settings.', 'mp') ?></li>
										<li><?php _e('Example:', 'mp') ?> <em>[mp_related_products product_id="12345" in_same_category="1" in_same_tags="1" limit="3" simple_list="0"]</em></li>
						</ul></p>
					 </td>
					 </tr>
					 <tr>
							<th scope="row"><?php _e('Products List', 'mp') ?></th>
							<td>
						<strong>[mp_list_products]</strong> -
						<span class="description"><?php _e('Displays a list of products according to preference. Optional attributes default to the values in Presentation Settings -> Product List.', 'mp') ?></span>
						<p>
						<strong><?php _e('Optional Attributes:', 'mp') ?></strong>
						<ul class="mp-shortcode-options">
							<li><?php _e('"paginate" - Whether to paginate the product list. This is useful to only show a subset.', 'mp') ?></li>
							<li><?php _e('"page" - The page number to display in the product list if "paginate" is set to true.', 'mp') ?></li>
							<li><?php _e('"per_page" - How many products to display in the product list if "paginate" is set to true.', 'mp') ?></li>
							<li><?php _e('"order_by" - What field to order products by. Can be: title, date, ID, author, price, sales, rand (random).', 'mp') ?></li>
							<li><?php _e('"order" - Direction to order products by. Can be: DESC, ASC', 'mp') ?></li>
							<li><?php _e('"category" - Limits list to a specific product category. Use the category Slug', 'mp') ?></li>
							<li><?php _e('"tag" - Limits list to a specific product tag. Use the tag Slug', 'mp') ?></li>
							<li><?php _e('"list_view" - 1 for list view, 0 (default) for grid view', 'mp') ?></li>
							<li><?php _e('Example:', 'mp') ?> <em>[mp_list_products paginate="true" page="1" per_page="10" order_by="price" order="DESC" category="downloads"]</em></li>
							<li><?php _e('"filters" - 1 to show product filters, 0 to not show filters', 'mp') ?></li>							
						</ul></p>
					 </td>
					 </tr>
								<tr>
							<th scope="row"><?php _e('Single Product', 'mp') ?></th>
							<td>
						<strong>[mp_product]</strong> -
						<span class="description"><?php _e('Displays a single product according to preference.', 'mp') ?></span>
						<p>
						<strong><?php _e('Optional Attributes:', 'mp') ?></strong>
						<ul class="mp-shortcode-options">
										<li><?php _e('"product_id" - The ID of the product to display. This is the Post ID, you can find it in the url of a product edit page.', 'mp') ?></li>
							<li><?php _e('"title" - Whether to display the product title.', 'mp') ?></li>
							<li><?php _e('"content" - Whether and what type of content to display. Options are false/0, "full", or "excerpt". Default "full"', 'mp') ?></li>
							<li><?php _e('"image" - Whether and what context of image size to display. Options are false/0, "single", or "list". Default "single"', 'mp') ?></li>
							<li><?php _e('"meta" - Whether to display the product meta (price, buy button).', 'mp') ?></li>
							<li><?php _e('Example:', 'mp') ?> <em>[mp_product product_id="1" title="1" content="excerpt" image="single" meta="1"]</em></li>
						</ul></p>
					 </td>
					 </tr>
								<tr>
							<th scope="row"><?php _e('Product Image', 'mp') ?></th>
							<td>
						<strong>[mp_product_image]</strong> -
						<span class="description"><?php _e('Displays the featured image of a given product.', 'mp') ?></span>
						<p>
						<strong><?php _e('Optional Attributes:', 'mp') ?></strong>
						<ul class="mp-shortcode-options">
							<li><?php _e('"product_id" - The ID for the product.	This is the Post ID, you can find it in the url of a product edit page. Optional if shortcode is in the loop.', 'mp') ?></li>
										<li><?php _e('"context" - What context for preset size options. Options are list, single, or widget, default single.', 'mp') ?></li>
										<li><?php _e('"size" - Set a custom pixel width/height. If omitted defaults to the size set by "context".', 'mp') ?></li>
										<li><?php _e('"align" - Set the alignment of the image. If omitted defaults to the alignment set in presentation settings.', 'mp') ?></li>
							<li><?php _e('Example:', 'mp') ?> <em>[mp_product_image product_id="1" size="150" align="left"]</em></li>
						</ul></p>
					 </td>
					 </tr>
								<tr>
							<th scope="row"><?php _e('Product Buy Button', 'mp') ?></th>
							<td>
						<strong>[mp_buy_button]</strong> -
						<span class="description"><?php _e('Displays the buy or add to cart button.', 'mp') ?></span>
						<p>
						<strong><?php _e('Optional Attributes:', 'mp') ?></strong>
						<ul class="mp-shortcode-options">
							<li><?php _e('"product_id" - The ID for the product.	This is the Post ID, you can find it in the url of a product edit page. Optional if shortcode is in the loop.', 'mp') ?></li>
										<li><?php _e('"context" - What context for display. Options are list or single, default single which shows all variations.', 'mp') ?></li>
							<li><?php _e('Example:', 'mp') ?> <em>[mp_buy_button product_id="1" context="single"]</em></li>
						</ul></p>
					 </td>
					 </tr>
								<tr>
							<th scope="row"><?php _e('Product Price', 'mp') ?></th>
							<td>
						<strong>[mp_product_price]</strong> -
						<span class="description"><?php _e('Displays the product price (and sale price).', 'mp') ?></span>
						<p>
						<strong><?php _e('Optional Attributes:', 'mp') ?></strong>
						<ul class="mp-shortcode-options">
							<li><?php _e('"product_id" - The ID for the product.	This is the Post ID, you can find it in the url of a product edit page. Optional if shortcode is in the loop.', 'mp') ?></li>
										<li><?php _e('"label" - A label to prepend to the price. Defaults to "Price: ".', 'mp') ?></li>
							<li><?php _e('Example:', 'mp') ?> <em>[mp_product_price product_id="1" label="Buy this thing now!"]</em></li>
						</ul></p>
					 </td>
					 </tr>
								<tr>
							<th scope="row"><?php _e('Product SKU', 'mp') ?></th>
							<td>
						<strong>[mp_product_sku]</strong> -
						<span class="description"><?php _e('Displays the product SKU number(s).', 'mp') ?></span>
						<p>
						<strong><?php _e('Optional Attributes:', 'mp') ?></strong>
						<ul class="mp-shortcode-options">
							<li><?php _e('"product_id" - The ID for the product.	This is the Post ID, you can find it in the url of a product edit page. Optional if shortcode is in the loop.', 'mp') ?></li>
										<li><?php _e('"seperator" - If there are variation, what to seperate the list of SKUs with. Defaults to a comma ", ".', 'mp') ?></li>
							<li><?php _e('Example:', 'mp') ?> <em>[mp_product_sku product_id="1" seperator=", "]</em></li>
						</ul></p>
					 </td>
					 </tr>
								<tr>
							<th scope="row"><?php _e('Product Meta', 'mp') ?></th>
							<td>
						<strong>[mp_product_meta]</strong> -
						<span class="description"><?php _e('Displays the full product meta box with price and buy now/add to cart button.', 'mp') ?></span>
						<p>
						<strong><?php _e('Optional Attributes:', 'mp') ?></strong>
						<ul class="mp-shortcode-options">
							<li><?php _e('"product_id" - The ID for the product.	This is the Post ID, you can find it in the url of a product edit page. Optional if shortcode is in the loop.', 'mp') ?></li>
										<li><?php _e('"label" - A label to prepend to the price. Defaults to "Price: ".', 'mp') ?></li>
							<li><?php _e('"context" - What context for display. Options are list or single, default single which shows all variations.', 'mp') ?></li>
							<li><?php _e('Example:', 'mp') ?> <em>[mp_product_meta product_id="1" label="Buy this thing now!"]</em></li>
						</ul></p>
					 </td>
					 </tr>
					 <tr>
							<th scope="row"><?php _e('Store Links', 'mp') ?></th>
							<td>
						<strong>[mp_cart_link]</strong> -
						<span class="description"><?php _e('Displays a link or url to the current shopping cart page.', 'mp') ?></span><br />
						<strong>[mp_store_link]</strong> -
						<span class="description"><?php _e('Displays a link or url to the current store page.', 'mp') ?></span><br />
						<strong>[mp_products_link]</strong> -
						<span class="description"><?php _e('Displays a link or url to the current products list page.', 'mp') ?></span><br />
						<strong>[mp_orderstatus_link]</strong> -
						<span class="description"><?php _e('Displays a link or url to the order status page.', 'mp') ?></span><br />
						<p>
						<strong><?php _e('Optional Attributes:', 'mp') ?></strong>
						<ul class="mp-shortcode-options">
							<li><?php _e('"url" - Whether to return a clickable link or url. Can be: true, false. Defaults to showing link.', 'mp') ?></li>
							<li><?php _e('"link_text" - The text to show in the link.', 'mp') ?></li>
							<li><?php _e('Example:', 'mp') ?> <em>[mp_cart_link link_text="Go here!"]</em></li>
						</ul></p>
					 </td>
					 </tr>
					 <tr>
							<th scope="row"><?php _e('Store Navigation List', 'mp') ?></th>
							<td>
						<strong>[mp_store_navigation]</strong> -
						<span class="description"><?php _e('Displays a list of links to your store pages.', 'mp') ?></span>
					 </td>
					 </tr>
					</table>
				</div>
			 </div>

			 <?php
			 //for adding additional help content boxes
			 do_action('mp_help_page', $settings);
			 ?>
			</div>
			<?php
			break;

			//---------------------------------------------------//
			case "importers":
			?>
			<div class="icon32"><img src="<?php echo $this->plugin_url . 'images/import.png'; ?>" /></div>
				<form id="mp-import-form" method="post" action="<?php echo admin_url('edit.php?post_type=product&page=marketpress&tab=importers'); ?>" enctype="multipart/form-data">
				<h2><?php _e('Import Products', 'mp'); ?></h2>
				<div id="poststuff" class="metabox-holder mp-importer">
					<?php do_action('marketpress_add_importer'); ?>
				</div>
				</form>
			</div>
			<?php
			break;

		} //end switch

		//hook to create a new admin screen.
		do_action('marketpress_add_screen', $tab);

		echo '</div>';

	}

	/*
	 * By Onur Demir aka @xenous from Ultimatum Theme
	 * Function to fix all issues with Ultimatum Loop 
	 * ultimatum fixes
	 */
	function ultimatum_fixes_forMP(){
		remove_action( 'ultimatum_post_content', 'ultimatum_do_post_content');
		remove_action('ultimatum_after_post_title','ultimatum_content_item_image');
		remove_action('ultimatum_before_post_title','ultimatum_content_item_image');
		remove_action( 'ultimatum_after_post_title', 'ultimatum_post_meta');
		remove_action( 'ultimatum_after_post_content', 'ultimatum_post_tax');
		remove_action( 'ultimatum_after_post_content', 'ultimatum_post_meta');
		add_action('ultimatum_post_content', 'the_content');
	}

	/**
	 * This function will convert old style arguments (broken out into variables) and convert into an array
	 * @param mixed $args
	 * @param array $defaults
	 * @return array
	 */
	function parse_args( $args, $defaults ) {
		if ( !isset($args[0]) )
			return $defaults;
		
		if ( (isset($args[0]) && is_array($args[0])) || (isset($args[0]) && !is_numeric($args[0]) && !is_bool($args[0])) )
			return wp_parse_args($args[0], $defaults);
		
		$tmp_args = array();
		
		foreach ( $defaults as $key => $value ) {
			$val = array_shift($args);
			$tmp_args[$key] = !is_null($val) ? $val : $value;
		}
		
		return $tmp_args;
	}
} //end class

global $mp;
$mp = new MarketPress();


//Shopping cart widget
class MarketPress_Shopping_Cart extends WP_Widget {

	function MarketPress_Shopping_Cart() {
		$widget_ops = array('classname' => 'mp_cart_widget', 'description' => __('Shows dynamic shopping cart contents along with a checkout button for your MarketPress store.', 'mp') );
		$this->WP_Widget('mp_cart_widget', __('Shopping Cart', 'mp'), $widget_ops);
	}

	function widget($args, $instance) {
		global $mp;

	 if ( get_query_var('pagename') == 'cart' )
		return;

		if ($instance['only_store_pages'] && !mp_is_shop_page())
			return;

		extract( $args );

		echo $before_widget;
		$title = $instance['title'];
		if ( !empty( $title ) ) { echo $before_title . apply_filters('widget_title', $title) . $after_title; };

	 if ( !empty($instance['custom_text']) )
		echo '<div class="custom_text">' . $instance['custom_text'] . '</div>';

	 echo '<div class="mp_cart_widget_content">';
	 mp_show_cart('widget');
	 echo '</div>';

	 echo $after_widget;
	}

	function update( $new_instance, $old_instance ) {
		$instance = $old_instance;
		$instance['title'] = stripslashes( wp_filter_nohtml_kses( $new_instance['title']) );
		$instance['custom_text'] = stripslashes( wp_filter_kses( $new_instance['custom_text']) );
		$instance['only_store_pages'] = !empty($new_instance['only_store_pages']) ? 1 : 0;
		/*
		$instance['show_thumbnail'] = !empty($new_instance['show_thumbnail']) ? 1 : 0;
	 $instance['size'] = !empty($new_instance['size']) ? intval($new_instance['size']) : 25;
		*/

		return $instance;
	}

	function form( $instance ) {
	 $instance = wp_parse_args( (array) $instance, array( 'title' => __('Shopping Cart', 'mp'), 'custom_text' => '', 'only_store_pages' => 0 ) );
		$title = $instance['title'];
		$custom_text = $instance['custom_text'];
		$only_store_pages = isset( $instance['only_store_pages'] ) ? (bool) $instance['only_store_pages'] : false;
		/*
		$show_thumbnail = isset( $instance['show_thumbnail'] ) ? (bool) $instance['show_thumbnail'] : false;
		$size = !empty($instance['size']) ? intval($instance['size']) : 25;
		*/
	?>
		<p><label for="<?php echo $this->get_field_id('title'); ?>"><?php _e('Title:', 'mp') ?> <input class="widefat" id="<?php echo $this->get_field_id('title'); ?>" name="<?php echo $this->get_field_name('title'); ?>" type="text" value="<?php echo esc_attr($title); ?>" /></label></p>
		<p><label for="<?php echo $this->get_field_id('custom_text'); ?>"><?php _e('Custom Text:', 'mp') ?><br />
	 <textarea class="widefat" id="<?php echo $this->get_field_id('custom_text'); ?>" name="<?php echo $this->get_field_name('custom_text'); ?>"><?php echo esc_attr($custom_text); ?></textarea></label>
	 </p>
		<p><input type="checkbox" class="checkbox" id="<?php echo $this->get_field_id('only_store_pages'); ?>" name="<?php echo $this->get_field_name('only_store_pages'); ?>"<?php checked( $only_store_pages ); ?> />
		<label for="<?php echo $this->get_field_id('only_store_pages'); ?>"><?php _e( 'Only show on store pages', 'mp' ); ?></label></p>
	<?php
		/* Disable untill we can mod the cart
		<p><input type="checkbox" class="checkbox" id="<?php echo $this->get_field_id('show_thumbnail'); ?>" name="<?php echo $this->get_field_name('show_thumbnail'); ?>"<?php checked( $show_thumbnail ); ?> />
		<label for="<?php echo $this->get_field_id('show_thumbnail'); ?>"><?php _e( 'Show Thumbnail', 'mp' ); ?></label><br />
		<label for="<?php echo $this->get_field_id('size'); ?>"><?php _e('Thumbnail Size:', 'mp') ?> <input id="<?php echo $this->get_field_id('size'); ?>" name="<?php echo $this->get_field_name('size'); ?>" type="text" size="3" value="<?php echo $size; ?>" /></label></p>
		*/
	}
}

//Product listing widget
class MarketPress_Product_List extends WP_Widget {

	function MarketPress_Product_List() {
		$widget_ops = array('classname' => 'mp_product_list_widget', 'description' => __('Shows a customizable list of products from your MarketPress store.', 'mp') );
		$this->WP_Widget('mp_product_list_widget', __('Product List', 'mp'), $widget_ops);
	}

	function widget($args, $instance) {
	 global $mp, $post;

		if ($instance['only_store_pages'] && !mp_is_shop_page())
			return;

		extract( $args );

		echo $before_widget;
		$title = $instance['title'];
		if ( !empty( $title ) ) { echo $before_title . apply_filters('widget_title', $title) . $after_title; };

	 if ( !empty($instance['custom_text']) )
		echo '<div id="custom_text">' . $instance['custom_text'] . '</div>';

	 /* setup our custom query */

	 //setup taxonomy if applicable
	 if ($instance['taxonomy_type'] == 'category') {
		$taxonomy_query = '&product_category=' . $instance['taxonomy'];
	 } else if ($instance['taxonomy_type'] == 'tag') {
		$taxonomy_query = '&product_tag=' . $instance['taxonomy'];
	 } else {
			$taxonomy_query = '';
		}

	 //figure out perpage
	 if (isset($instance['num_products']) && intval($instance['num_products']) > 0) {
		$paginate_query = '&posts_per_page='.intval($instance['num_products']).'&paged=1';
	 } else {
		$paginate_query = '&posts_per_page=10&paged=1';
	 }

	 //get order by
	 if ($instance['order_by']) {
		if ($instance['order_by'] == 'price')
			$order_by_query = '&meta_key=mp_price_sort&orderby=meta_value_num';
		else if ($instance['order_by'] == 'sales')
			$order_by_query = '&meta_key=mp_sales_count&orderby=meta_value_num';
		else
			$order_by_query = '&orderby='.$instance['order_by'];
	 } else {
		$order_by_query = '&orderby=title';
	 }

	 //get order direction
	 if ($instance['order']) {
		$order_query = '&order='.$instance['order'];
	 } else {
		$order_query = '&orderby=DESC';
	 }

	 //The Query
	 $custom_query = new WP_Query('post_type=product' . $taxonomy_query . $paginate_query . $order_by_query . $order_query);

	 //do we have products?
	 if ( $custom_query->have_posts() ) {
		echo '<ul id="mp_product_list" class="hfeed">';
		while ( $custom_query->have_posts() ) : $custom_query->the_post();

			echo '<li itemscope itemtype="http://schema.org/Product" ' . mp_product_class(false, array('mp_product', 'hentry'), $post->ID) . '>';
			echo '<h3 class="mp_product_name entry-title" itemprop="name"><a href="' . get_permalink( $post->ID ) . '">' . esc_attr($post->post_title) . '</a></h3>';
			if ($instance['show_thumbnail'])
			 mp_product_image( true, 'widget', $post->ID, $instance['size'] );
			
			echo '<div class="entry-content" style="margin:0;padding:0;width:auto;">';

			if ($instance['show_excerpt'])
			 echo '<div class="mp_product_content">' . $mp->product_excerpt($post->post_excerpt, $post->post_content, $post->ID) . '</div>';

			if ($instance['show_price'] || $instance['show_button']) {
			 echo '<div class="mp_product_meta">';

			 if ($instance['show_price'])
				echo mp_product_price(false, $post->ID, '');

			 if ($instance['show_button'])
				echo mp_buy_button(false, 'list', $post->ID);

			echo '</div>';
			}
			
			echo '</div>';
			echo '<div style="display:none">
							<time class="updated">' . get_the_time('Y-m-d\TG:i') . '</time> by
							<span class="author vcard"><span class="fn">' . get_the_author_meta('display_name') . '</span></span>
						</div>';
			echo '</li>';
		endwhile;
		wp_reset_postdata();
		echo '</ul>';
	 } else {
		?>
		<div class="widget-error">
				<?php _e('No Products', 'mp') ?>
			</div>
			<?php
	 }

	 echo $after_widget;
	}

	function update( $new_instance, $old_instance ) {
		$instance = $old_instance;
		$instance['title'] = stripslashes( wp_filter_nohtml_kses( $new_instance['title'] ) );
		$instance['custom_text'] = stripslashes( wp_filter_kses( $new_instance['custom_text'] ) );

		$instance['num_products'] = intval($new_instance['num_products']);
		$instance['order_by'] = $new_instance['order_by'];
		$instance['order'] = $new_instance['order'];
		$instance['taxonomy_type'] = $new_instance['taxonomy_type'];
	 $instance['taxonomy'] = ($new_instance['taxonomy_type']) ? sanitize_title($new_instance['taxonomy']) : '';

	 $instance['show_thumbnail'] = !empty($new_instance['show_thumbnail']) ? 1 : 0;
	 $instance['size'] = !empty($new_instance['size']) ? intval($new_instance['size']) : 50;
	 $instance['show_excerpt'] = !empty($new_instance['show_excerpt']) ? 1 : 0;
	 $instance['show_price'] = !empty($new_instance['show_price']) ? 1 : 0;
	 $instance['show_button'] = !empty($new_instance['show_button']) ? 1 : 0;

		$instance['only_store_pages'] = !empty($new_instance['only_store_pages']) ? 1 : 0;

		return $instance;
	}

	function form( $instance ) {
	 $instance = wp_parse_args( (array) $instance, array( 'title' => __('Our Products', 'mp'), 'custom_text' => '', 'num_products' => 10, 'order_by' => 'title', 'order' => 'DESC', 'show_thumbnail' => 1, 'size' => 50, 'only_store_pages' => 0 ) );
		$title = $instance['title'];
		$custom_text = $instance['custom_text'];

		$num_products = intval($instance['num_products']);
		$order_by = $instance['order_by'];
		$order = $instance['order'];
	 $taxonomy_type = isset($instance['taxonomy_type']) ? $instance['taxonomy_type'] : '';
	 $taxonomy = isset($instance['taxonomy']) ? $instance['taxonomy'] : '';

		$show_thumbnail = isset( $instance['show_thumbnail'] ) ? (bool) $instance['show_thumbnail'] : false;
		$size = !empty($instance['size']) ? intval($instance['size']) : 50;
		$show_excerpt = isset( $instance['show_excerpt'] ) ? (bool) $instance['show_excerpt'] : false;
		$show_price = isset( $instance['show_price'] ) ? (bool) $instance['show_price'] : false;
		$show_button = isset( $instance['show_button'] ) ? (bool) $instance['show_button'] : false;

		$only_store_pages = isset( $instance['only_store_pages'] ) ? (bool) $instance['only_store_pages'] : false;
	?>
		<p><label for="<?php echo $this->get_field_id('title'); ?>"><?php _e('Title:', 'mp') ?> <input class="widefat" id="<?php echo $this->get_field_id('title'); ?>" name="<?php echo $this->get_field_name('title'); ?>" type="text" value="<?php echo esc_attr($title); ?>" /></label></p>
		<p><label for="<?php echo $this->get_field_id('custom_text'); ?>"><?php _e('Custom Text:', 'mp') ?><br />
	 <textarea class="widefat" id="<?php echo $this->get_field_id('custom_text'); ?>" name="<?php echo $this->get_field_name('custom_text'); ?>"><?php echo esc_attr($custom_text); ?></textarea></label>
	 </p>

	 <h3><?php _e('List Settings', 'mp'); ?></h3>
	 <p>
	 <label for="<?php echo $this->get_field_id('num_products'); ?>"><?php _e('Number of Products:', 'mp') ?> <input id="<?php echo $this->get_field_id('num_products'); ?>" name="<?php echo $this->get_field_name('num_products'); ?>" type="text" size="3" value="<?php echo $num_products; ?>" /></label><br />
	 </p>
	 <p>
	 <label for="<?php echo $this->get_field_id('order_by'); ?>"><?php _e('Order Products By:', 'mp') ?></label><br />
	 <select id="<?php echo $this->get_field_id('order_by'); ?>" name="<?php echo $this->get_field_name('order_by'); ?>">
		<option value="title"<?php selected($order_by, 'title') ?>><?php _e('Product Name', 'mp') ?></option>
		<option value="date"<?php selected($order_by, 'date') ?>><?php _e('Publish Date', 'mp') ?></option>
		<option value="ID"<?php selected($order_by, 'ID') ?>><?php _e('Product ID', 'mp') ?></option>
		<option value="author"<?php selected($order_by, 'author') ?>><?php _e('Product Author', 'mp') ?></option>
		<option value="sales"<?php selected($order_by, 'sales') ?>><?php _e('Number of Sales', 'mp') ?></option>
		<option value="price"<?php selected($order_by, 'price') ?>><?php _e('Product Price', 'mp') ?></option>
		<option value="rand"<?php selected($order_by, 'rand') ?>><?php _e('Random', 'mp') ?></option>
	 </select><br />
	 <label><input value="DESC" name="<?php echo $this->get_field_name('order'); ?>" type="radio"<?php checked($order, 'DESC') ?> /> <?php _e('Descending', 'mp') ?></label>
	 <label><input value="ASC" name="<?php echo $this->get_field_name('order'); ?>" type="radio"<?php checked($order, 'ASC') ?> /> <?php _e('Ascending', 'mp') ?></label>
	 </p>
	 <p>
	 <label><?php _e('Taxonomy Filter:', 'mp') ?></label><br />
	 <select id="<?php echo $this->get_field_id('taxonomy_type'); ?>" name="<?php echo $this->get_field_name('taxonomy_type'); ?>">
		<option value=""<?php selected($taxonomy_type, '') ?>><?php _e('No Filter', 'mp') ?></option>
		<option value="category"<?php selected($taxonomy_type, 'category') ?>><?php _e('Category', 'mp') ?></option>
		<option value="tag"<?php selected($taxonomy_type, 'tag') ?>><?php _e('Tag', 'mp') ?></option>
	 </select>
	 <input id="<?php echo $this->get_field_id('taxonomy'); ?>" name="<?php echo $this->get_field_name('taxonomy'); ?>" type="text" size="17" value="<?php echo $taxonomy; ?>" title="<?php _e('Enter the Slug', 'mp'); ?>" />
	 </p>

	 <h3><?php _e('Display Settings', 'mp'); ?></h3>
	 <p><input type="checkbox" class="checkbox" id="<?php echo $this->get_field_id('show_thumbnail'); ?>" name="<?php echo $this->get_field_name('show_thumbnail'); ?>"<?php checked( $show_thumbnail ); ?> />
		<label for="<?php echo $this->get_field_id('show_thumbnail'); ?>"><?php _e( 'Show Thumbnail', 'mp' ); ?></label><br />
		<label for="<?php echo $this->get_field_id('size'); ?>"><?php _e('Thumbnail Size:', 'mp') ?> <input id="<?php echo $this->get_field_id('size'); ?>" name="<?php echo $this->get_field_name('size'); ?>" type="text" size="3" value="<?php echo $size; ?>" /></label></p>

	 <p><input type="checkbox" class="checkbox" id="<?php echo $this->get_field_id('show_excerpt'); ?>" name="<?php echo $this->get_field_name('show_excerpt'); ?>"<?php checked( $show_excerpt ); ?> />
	 <label for="<?php echo $this->get_field_id('show_excerpt'); ?>"><?php _e( 'Show Excerpt', 'mp' ); ?></label><br />
	 <input type="checkbox" class="checkbox" id="<?php echo $this->get_field_id('show_price'); ?>" name="<?php echo $this->get_field_name('show_price'); ?>"<?php checked( $show_price ); ?> />
		<label for="<?php echo $this->get_field_id('show_price'); ?>"><?php _e( 'Show Price', 'mp' ); ?></label><br />
	 <input type="checkbox" class="checkbox" id="<?php echo $this->get_field_id('show_button'); ?>" name="<?php echo $this->get_field_name('show_button'); ?>"<?php checked( $show_button ); ?> />
		<label for="<?php echo $this->get_field_id('show_button'); ?>"><?php _e( 'Show Buy Button', 'mp' ); ?></label></p>

		<p><input type="checkbox" class="checkbox" id="<?php echo $this->get_field_id('only_store_pages'); ?>" name="<?php echo $this->get_field_name('only_store_pages'); ?>"<?php checked( $only_store_pages ); ?> />
		<label for="<?php echo $this->get_field_id('only_store_pages'); ?>"><?php _e( 'Only show on store pages', 'mp' ); ?></label></p>
	<?php
	}
}

//Product categories widget
class MarketPress_Categories_Widget extends WP_Widget {

	function MarketPress_Categories_Widget() {
		$widget_ops = array( 'classname' => 'mp_categories_widget', 'description' => __( "A list or dropdown of product categories from your MarketPress store.", 'mp' ) );
		$this->WP_Widget('mp_categories_widget', __('Product Categories', 'mp'), $widget_ops);
	}

	function widget( $args, $instance ) {

		if ($instance['only_store_pages'] && !mp_is_shop_page())
			return;

		extract( $args );

		$title = apply_filters('widget_title', empty( $instance['title'] ) ? __('Product Categories', 'mp') : $instance['title'], $instance, $this->id_base);
		$c = $instance['count'] ? '1' : '0';
		$h = $instance['hierarchical'] ? '1' : '0';
		$d = $instance['dropdown'] ? '1' : '0';

		echo $before_widget;
		if ( $title )
			echo $before_title . $title . $after_title;

		$cat_args = array('orderby' => 'name', 'show_count' => $c, 'hierarchical' => $h);

		if ( $d ) {
			$cat_args['show_option_none'] = __('Select Category');
			$cat_args['taxonomy'] = 'product_category';
		$cat_args['id'] = 'mp_category_dropdown';
			mp_dropdown_categories( true, $cat_args );
		} else {
?>
<ul id="mp_category_list">
<?php
		$cat_args['title_li'] = '';
		$cat_args['taxonomy'] = 'product_category';
		wp_list_categories( $cat_args );
?>
</ul>
<?php
		}

		echo $after_widget;
	}

	function update( $new_instance, $old_instance ) {
		$instance = $old_instance;
		$instance['title'] = strip_tags($new_instance['title']);
		$instance['count'] = !empty($new_instance['count']) ? 1 : 0;
		$instance['hierarchical'] = !empty($new_instance['hierarchical']) ? 1 : 0;
		$instance['dropdown'] = !empty($new_instance['dropdown']) ? 1 : 0;
		$instance['only_store_pages'] = !empty($new_instance['only_store_pages']) ? 1 : 0;

		return $instance;
	}

	function form( $instance ) {
		//Defaults
		$instance = wp_parse_args( (array) $instance, array( 'title' => '', 'only_store_pages' => 0 ) );
		$title = esc_attr( $instance['title'] );
		$count = isset($instance['count']) ? (bool) $instance['count'] :false;
		$hierarchical = isset( $instance['hierarchical'] ) ? (bool) $instance['hierarchical'] : false;
		$dropdown = isset( $instance['dropdown'] ) ? (bool) $instance['dropdown'] : false;
		$only_store_pages = isset( $instance['only_store_pages'] ) ? (bool) $instance['only_store_pages'] : false;
?>
		<p><label for="<?php echo $this->get_field_id('title'); ?>"><?php _e( 'Title:' ); ?></label>
		<input class="widefat" id="<?php echo $this->get_field_id('title'); ?>" name="<?php echo $this->get_field_name('title'); ?>" type="text" value="<?php echo $title; ?>" /></p>

		<p><input type="checkbox" class="checkbox" id="<?php echo $this->get_field_id('dropdown'); ?>" name="<?php echo $this->get_field_name('dropdown'); ?>"<?php checked( $dropdown ); ?> />
		<label for="<?php echo $this->get_field_id('dropdown'); ?>"><?php _e( 'Show as dropdown' ); ?></label><br />

		<input type="checkbox" class="checkbox" id="<?php echo $this->get_field_id('count'); ?>" name="<?php echo $this->get_field_name('count'); ?>"<?php checked( $count ); ?> />
		<label for="<?php echo $this->get_field_id('count'); ?>"><?php _e( 'Show product counts', 'mp' ); ?></label><br />

		<input type="checkbox" class="checkbox" id="<?php echo $this->get_field_id('hierarchical'); ?>" name="<?php echo $this->get_field_name('hierarchical'); ?>"<?php checked( $hierarchical ); ?> />
		<label for="<?php echo $this->get_field_id('hierarchical'); ?>"><?php _e( 'Show hierarchy' ); ?></label></p>

		<p><input type="checkbox" class="checkbox" id="<?php echo $this->get_field_id('only_store_pages'); ?>" name="<?php echo $this->get_field_name('only_store_pages'); ?>"<?php checked( $only_store_pages ); ?> />
		<label for="<?php echo $this->get_field_id('only_store_pages'); ?>"><?php _e( 'Only show on store pages', 'mp' ); ?></label></p>
<?php
	}
}

//Product tags cloud
class MarketPress_Tag_Cloud_Widget extends WP_Widget {

	function MarketPress_Tag_Cloud_Widget() {
		$widget_ops = array( 'classname' => 'mp_tag_cloud_widget', 'description' => __( "Your most used product tags in cloud format from your MarketPress store.") );
		$this->WP_Widget('mp_tag_cloud_widget', __('Product Tag Cloud', 'mp'), $widget_ops);
	}

	function widget( $args, $instance ) {

		if ($instance['only_store_pages'] && !mp_is_shop_page())
			return;

		extract($args);
		$current_taxonomy = 'product_tag';
		if ( !empty($instance['title']) ) {
			$title = $instance['title'];
		}
		$title = apply_filters('widget_title', $title, $instance, $this->id_base);

		echo $before_widget;
		if ( $title )
			echo $before_title . $title . $after_title;
		echo '<div>';
		wp_tag_cloud( apply_filters('widget_tag_cloud_args', array('taxonomy' => $current_taxonomy) ) );
		echo "</div>\n";
		echo $after_widget;
	}

	function update( $new_instance, $old_instance ) {
		$instance['title'] = strip_tags(stripslashes($new_instance['title']));
		$instance['only_store_pages'] = !empty($new_instance['only_store_pages']) ? 1 : 0;
		return $instance;
	}

	function form( $instance ) {
	 $instance = wp_parse_args( (array) $instance, array( 'title' => __('Product Tags', 'mp'), 'only_store_pages' => 0 ) );
		$only_store_pages = isset( $instance['only_store_pages'] ) ? (bool) $instance['only_store_pages'] : false;
?>
	<p><label for="<?php echo $this->get_field_id('title'); ?>"><?php _e('Title:') ?></label>
	<input type="text" class="widefat" id="<?php echo $this->get_field_id('title'); ?>" name="<?php echo $this->get_field_name('title'); ?>" value="<?php if (isset ( $instance['title'])) {echo esc_attr( $instance['title'] );} ?>" /></p>

	<p><input type="checkbox" class="checkbox" id="<?php echo $this->get_field_id('only_store_pages'); ?>" name="<?php echo $this->get_field_name('only_store_pages'); ?>"<?php checked( $only_store_pages ); ?> />
		<label for="<?php echo $this->get_field_id('only_store_pages'); ?>"><?php _e( 'Only show on store pages', 'mp' ); ?></label></p>
	<?php
	}
}