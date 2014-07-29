<?php

/**
 * Marketpress Oembed Enpoint
 */
if(!class_exists('MP_Oembed') ): 
class MP_Oembed {
	
	const TRANSIENT_TITLE_BASE = 'mp_oembed_cache_';
	const CACHE_TIMEOUT = 3600; //1 hour
	
	
	private $_passed_url;
	private $_cache_identifier;
	

	/**
	 * Construct
	 */
	function __construct() {
		add_action('wp_ajax_mp_oembed', array(&$this, 'process_oembed_request_action'));
		add_action('wp_ajax_no_priv_mp_oembed', array(&$this, 'process_oembed_request_action'));
		
		// we'll need to invalidate the cache on post save for products
		add_action('save_post', array(&$this, 'check_for_invalid_cache_action' ));
		
		//hook into the footer for the pinit script
		add_action('wp_footer', array(&$this, 'inject_pinterest_js_action') );
	}
	
	
	
	/**
	 * parses the request to look for the service type and act accordingly
	 */
	function process_oembed_request_action() {
		$type = isset($_GET['type']) ? $_GET['type'] : '';
		
		switch ( $type ) {
			case 'pinterest' :
				$results = $this->process();
				echo $results->package;
			break;
			
			default :
				_e('Service Unavailable', 'mp');
			break;
		}
		
		exit;
	}
	
	
	/**
	 * Checks on post save for invalid caches
	 */
	function check_for_invalid_cache_action( $post_id ) {
		//we only want this running on product pages
		if ( get_post_type() != 'product' ) {
			return $post_id;
		}
		
		$this->_cache_identifier = $post_id;
		if ( $cache = $this->checkForCache() ) {
			$this->removeCache();
		}
	}
	
	
	/**
	 * Injects the pinterest js into the footer if enabled
	 */
	function inject_pinterest_js_action() {
		global $mp;
		
		$show_pinit_button = ($show = $mp->get_setting('social->pinterest->show_pinit_button') ) ? $show : 'off';
		
		if ( get_post_type() == 'product' && $show_pinit_button != 'off' ){
		?>
        <script type="text/javascript">
		(function(d){
		  var f = d.getElementsByTagName('SCRIPT')[0], p = d.createElement('SCRIPT');
		  p.type = 'text/javascript';
		  p.async = true;
		  p.src = '//assets.pinterest.com/js/pinit.js';
		  f.parentNode.insertBefore(p, f);
		}(document));
		</script>
        <?php
		}
	}
	
	

	/**
	 * Process the product and create the JSON packages
	 *
	 */
	private function process() {
		global $mp;
		//no get - get out, get it?
		if( !isset( $_GET['url'] ) ) {
			$this->_package = __('url parameter required','mp');
		}else{
			//get the url
			$this->_passed_url = $_GET['url'];
			$product_post_object = get_page_by_path( basename( untrailingslashit( $_GET['url'] ) ) , OBJECT, 'product');
			if( !is_null( $product_post_object ) ) {
				//post id
				$ID = $product_post_object->ID;
				
				//look for the transient first
				$this->_cache_identifier = $ID;
				
				if( $cache = $this->checkForCache() ) {
					$this->_package = $cache;
					
				}else{
					
					//no cache build it
					$meta = $mp->get_meta_details( $ID );
					
					//grab some items that we will need
					$common_elements = array(
						'provider_name' => get_bloginfo('title'),
						'url' => get_permalink($ID),
						'title' => get_the_title( $ID ),
						'currency_code' => $mp->get_setting('currency'),
						'description' => $product_post_object->post_content,
					);
					
					//is this a single product page or multiple
					$variations = $meta['mp_var_name'];
					$format =  ( count( $variations ) > 1 ) ? 'multiple' : 'single' ;
					
					if( $format == 'multiple' ) {
						$this->processProductWithVariations( $meta, $common_elements );
					}else{
						$this->processProductNoVariations( $meta, $common_elements );
					}
				}
			}else{
				//if it's not a single page - lets try for one of the taxonomies
				$cats_url = '/'.$mp->get_setting('slugs->products') .'/'.$mp->get_setting('slugs->category');
				$tags_url = '/'.$mp->get_setting('slugs->products') .'/'.$mp->get_setting('slugs->tag');
			
				if( strpos( $this->_passed_url, $cats_url) || strpos( $this->_passed_url, $tags_url) ) {
					
					//get the tax we're displaying
					preg_match('/\/(\w+)\/$/', $this->_passed_url , $matches);
					
					
					//check for cache
					$this->_cache_identifier = ( strpos( $this->_passed_url, $cats_url)  ) ? 'category_'. $matches[1] : 'tag_'. $matches[1];
					if( $cache = $this->checkForCache() ) {
						$this->_package = $cache;
					}else{
						//no cache - build it
						$tax =  ( strpos( $this->_passed_url, $cats_url)  ) ? 'product_category' : 'product_tag'; 
						//setup the query vars
						$args = array(
							'post_type' => 'product',
							$tax  => $matches[1],
							'posts_per_page' => ( $mp->get_setting('paginate') ) ? $mp->get_setting('per_page') : -1 ,
						);
						
						$all_products = new WP_Query($args);
						
						$this->processTaxonomyPage( $all_products->posts );
					}
				}else{
					//we don't know what to do with this url
					$this->_package = __("Please check the url",'mp');
				}
				
			}
		}
		//return the final data
		return $this->_getPackage();
	}
	
	

	/**
	 * Create the package for a single product
	 * 
	 * @param array Post meta
	 * @param array  Elements that are common to all formats
	 */
	private function processProductNoVariations( $post_meta, $common ) {
		$specific_items = array();
		
		//get the product_id
		if( isset( $post_meta['mp_sku'][0]) && !empty($post_meta['mp_sku'][0])  ) {
			$specific_items['product_id'] = $post_meta['mp_sku'][0];
		}
		
		//get the price 
		if( isset( $post_meta['mp_price'][0]) ) {	
			$specific_items['price'] = ( @$post_meta['mp_is_sale']) ? floatval( $post_meta['mp_sale_price'][0] ):  floatval( $post_meta['mp_price'][0] );
		}
		
		$specific_items['availability'] = ( $post_meta['mp_track_inventory'] && @$post_meta['mp_inventory'][0]  < 1 ) ? 'out of stock' : 'in stock';
		
		$final_array = array_merge(	 $common, $specific_items );
		$this->_package = json_encode($final_array);
	}
	
	
	/**
	 * Create the package for multiple products
	 *
	 */
	private function processProductWithVariations( $post_meta, $common ) {
		//we only need a couple items from the common array
		$specific_data = array_intersect_key( $common, array( 'provider_name' => '', 'url' =>'' ) );
		
		//build the offers
		$offers = $this->_processOffersForProductNode( $post_meta );
		
		//create the products array
		$specific_data['products'][] = array(
				'title' => $common['title'],
				'description' => $common['description'],
				'product_id' => ( isset( $post_meta['mp_sku'][0]) && !empty($post_meta['mp_sku'][0]) ) ? $post_meta['mp_sku'][0] : '',
				'offers' => $offers,
		);
		
		$this->_package = json_encode($specific_data);
	}
	
	
	
	/**
	 * Create the package for a tax page
	 *
	 * @param array List of post objects
	 */
	private function processTaxonomyPage( $posts ) {
		global $mp;
		$package = array(
					'provider_name' => get_bloginfo('title'),
					'url' => $this->_passed_url,
					'products' => array()
				);
		//loop the products
		foreach( $posts as $post) {
			$package['products'][] = $this->_processProductNode( $post );
		}
		
		$this->_package = json_encode($package);
	}
	
	
	/**
	 * Generate the package
	 */
	private function _getPackage() {
		
		//create the cache
		$this->createCache();
		
		
		//return the data
		$rtn = new stdClass();
		$rtn->package = $this->_package;
		return $rtn;
	}
	
	
	//============================
	// Helper methods
	//============================

	 
	/**
	 * Process a single product and all of its offers
	 *
	 * @param object Post object
	 * @return array
	 */
	private function _processProductNode( $post ) {
		global $mp;
		$post_meta = $mp->get_meta_details( $post->ID );
		//build the offers
		$offers = $this->_processOffersForProductNode( $post_meta );
		
		$product = array(
			'title' => $post->post_title,
			'description' => strip_tags($post->post_content),
			'product_id' => '',
			'offers' => $offers,
		);
			
		return $product;
	}
	
	
	/**
	 * Generate Offers for product
	 * 
	 * @param array Post meta for a product
	 */
	private function _processOffersForProductNode( $post_meta ) {
		global $mp;
		//build the offers list
		$offers = array();
		
		for($i = 0; $i < count( $post_meta['mp_var_name'] ) ; $i++) {
			$offers[] = array(
				'description' => $post_meta['mp_var_name'][$i],
				'price' => ( @$post_meta['mp_is_sale']) ? floatval( $post_meta['mp_sale_price'][$i] ):  floatval( $post_meta['mp_price'][$i] ),
				'currency_code' => $mp->get_setting('currency'),
				'availability' =>  ( $post_meta['mp_track_inventory'] && @$post_meta['mp_inventory'][$i]  < 1 ) ? 'out of stock' : 'in stock',	
				'offer_id' => ( isset( $post_meta['mp_sku'][$i]) && !empty($post_meta['mp_sku'][$i]) ) ? $post_meta['mp_sku'][$i] : '',
			);
		}
		
		return $offers;
	}
	
	
	
	/**
	 * Check for the cache
	 * @return bool | json object The cache or false if no data is found
	 */
	private function checkForCache() {
		
		$rtn = false;
		$transient_title = self::TRANSIENT_TITLE_BASE . $this->_cache_identifier;
		if( $cache = get_transient($transient_title) ) {
			$rtn =  $cache;
		}
		
		return $rtn;
	}
	
	/**
	 * Create Cache
	 */
	private function createCache() {	
		$transient_title = self::TRANSIENT_TITLE_BASE . $this->_cache_identifier;
		set_transient( $transient_title, $this->_package, self::CACHE_TIMEOUT);
	}
	
	/**
	 * Remove transient
	 */
	private function removeCache() {
		$transient_title = self::TRANSIENT_TITLE_BASE . $this->_cache_identifier;
		delete_transient( $transient_title );
	}
	
}
endif;
$mp_ombed = new MP_Oembed();