<?php
/*
MarketPress Multisite Features
*/

class MarketPress_MS {

  var $global_list_template;
  var $tag_template;
  var $category_template;
  
	function MarketPress_MS() {
		$this->__construct();
	}
	
  function __construct() {
    global $mp;
    
    // Plug admin pages
		add_action( 'admin_menu', array(&$this, 'add_menu_items') );
		add_action( 'network_admin_menu', array(&$this, 'add_menu_items') );
    
	}

  //wrapper function for checking if global marketpress blog. Define MP_ROOT_BLOG with a blog_id to override
  function is_main_site() {
    global $wpdb;
    if ( defined( 'MP_ROOT_BLOG' ) ) {
      return $wpdb->blogid == MP_ROOT_BLOG;
    } else {
      return is_main_site();
    }
  }

  function add_menu_items() {
    global $mp, $wp_version;
    
    if ( version_compare($wp_version, '3.0.9', '>') ) {
      $page = add_submenu_page('settings.php', __('MarketPress Network Options', 'mp'), __('MarketPress', 'mp'), 10, 'marketpress-ms', array(&$this, 'super_admin_page'));
    } else {
      $page = add_submenu_page('ms-admin.php', __('MarketPress Network Options', 'mp'), __('MarketPress', 'mp'), 10, 'marketpress-ms', array(&$this, 'super_admin_page'));
    }
    //add_action( 'admin_print_scripts-' . $page, array(&$this, 'admin_script_settings') );
    //add_action( 'admin_print_styles-' . $page, array(&$this, 'admin_css_settings') );
  }


  function super_admin_page() {
    global $mp, $mp_gateway_plugins;
    
    //double-check rights
    if(!is_super_admin()) {
  		echo "<p>" . __('Nice Try...', 'mp') . "</p>";  //If accessed properly, this message doesn't appear.
  		return;
  	}

    ?>
    <div class="wrap">
    <div class="icon32"><img src="<?php echo $mp->plugin_url . 'images/settings.png'; ?>" /></div>
    <h2><?php _e('MarketPress Network Options', 'mp') ?></h2>
    <div class="error"><p><a class="mp-pro-update" href="http://premium.wpmudev.org/project/e-commerce/" title="<?php _e('Upgrade Now', 'mp'); ?> &raquo;"><?php _e('Upgrade to MarketPress Pro to enable Multisite features &raquo;', 'mp'); ?></a></p></div>

		<div id="poststuff" class="metabox-holder mp-settings">
      <form id="mp-main-form" method="post" action="">
        <div class="postbox">
          <h3 class='hndle'><span><?php _e('General Settings', 'mp') ?></span></h3>
          <div class="inside">
            <table class="form-table">
              <tr>
			      		<th scope="row"><?php _e('Limit Global Widgets/Shortcodes To Main Blog', 'mp'); ?></th>
			      		<td>
									<label><input value="1" name="mp[main_blog]" type="radio" checked="checked" disabled="disabled" /> <?php _e('Yes', 'mp') ?></label>
					    		<label><input value="0" name="mp[main_blog]" type="radio" disabled="disabled" /> <?php _e('No', 'mp') ?></label>
			        	</td>
			        </tr>
				      <tr>
			      		<th scope="row"><?php _e('Enable Global shopping cart', 'mp'); ?></th>
			      		<td>
								  <label><input class="mp_change_submit" value="1" name="mp[global_cart]" type="radio" disabled="disabled" /> <?php _e('Yes', 'mp') ?></label>
								  <label><input class="mp_change_submit" value="0" name="mp[global_cart]" type="radio" checked="checked" disabled="disabled" /> <?php _e('No', 'mp') ?></label>
			        	</td>
              </tr>
            </table>
          </div>
        </div>

        <div class="postbox">
          <h3 class='hndle'><span><?php _e('Gateway Permissions', 'mp') ?></span> - <span class="description"><?php _e('Set payment gateway access permissions for network stores. The main site will maintain access to all gateways.', 'mp') ?></span></h3>
          <div class="inside">
            <table class="form-table">
              <?php
              foreach ((array)$mp_gateway_plugins as $code => $plugin) {
                $allowed = ($settings['allowed_gateways'][$code]) ? $settings['allowed_gateways'][$code] : 'none';
              ?>
              <tr>
      				<th scope="row"><?php echo $plugin[1]; ?></th>
      				<td>
              <label><input checked="checked" disabled="disabled" value="full" id="gw_full_<?php echo $code; ?>" name="mp[allowed_gateways][<?php echo $code; ?>]" type="radio" /> <?php _e('All Can Use', 'mp') ?></label><br />
              <?php if (function_exists('is_supporter')) { ?>
              <label><input disabled="disabled" value="supporter" id="gw_supporter_<?php echo $code; ?>" name="mp[allowed_gateways][<?php echo $code; ?>]" type="radio" /> <?php _e('Supporter Sites Only', 'mp') ?></label><br />
              <?php } ?>
              <label><input disabled="disabled" value="none" id="gw_none_<?php echo $code; ?>" name="mp[allowed_gateways][<?php echo $code; ?>]" type="radio" /> <?php _e('No Access', 'mp') ?></label>
              </td>
              </tr>
              <?php
							}
							?>
            </table>
          </div>
        </div>
        
        <?php
        //for adding additional settings via plugins
        do_action('mp_network_gateway_settings', $settings);
        ?>
        
        <div class="postbox">
          <h3 class='hndle'><span><?php _e('Theme Permissions', 'mp') ?></span> - <span class="description"><?php _e('Set theme access permissions for network stores.', 'mp') ?></span></h3>
          <div class="inside">
            <span class="description"><?php _e('For a custom css theme, save your css file with the "MarketPress Theme: NAME" header in the "/marketpress/css/themes/" folder and it will appear in this list so you may select it.', 'mp') ?></span>
            <table class="form-table">
              <?php
              //get theme dir
              $theme_dir = $mp->plugin_dir . 'themes/';

              //scan directory for theme css files
              $theme_list = array();
              if ($handle = @opendir($theme_dir)) {
                while (false !== ($file = readdir($handle))) {
                  if (($pos = strrpos($file, '.css')) !== false) {
                    $value = substr($file, 0, $pos);
                    if (is_readable("$theme_dir/$file")) {
                      $theme_data = get_file_data( "$theme_dir/$file", array('name' => 'MarketPress Theme') );
                      if (is_array($theme_data))
                        $theme_list[$value] = $theme_data['name'];
                    }
                  }
                }

                @closedir($handle);
              }

              //sort the themes
              asort($theme_list);

              foreach ($theme_list as $value => $name) {
                $allowed = ($settings['allowed_themes'][$value]) ? $settings['allowed_themes'][$value] : 'full';
                ?>
                <tr>
        				<th scope="row"><?php echo $name; ?></th>
        				<td>
                <label><input checked="checked" disabled="disabled" value="full" name="mp[allowed_themes][<?php echo $value; ?>]" type="radio" /> <?php _e('All Can Use', 'mp') ?></label><br />
                <?php if (function_exists('is_supporter')) { ?>
                <label><input disabled="disabled" value="supporter" name="mp[allowed_themes][<?php echo $value; ?>]" type="radio" /> <?php _e('Supporter Sites Only', 'mp') ?></label><br />
                <?php } ?>
                <label><input disabled="disabled" value="none" name="mp[allowed_themes][<?php echo $value; ?>]" type="radio" /> <?php _e('No Access', 'mp') ?></label>
                </td>
                </tr>
                <?php
              }
              ?>
            </table>
          </div>
        </div>
        
        <div class="postbox">
            <h3 class='hndle'><span><?php _e('Global Marketplace URL Slugs', 'mp') ?></span></h3>
            <div class="inside">
              <span class="description"><?php _e('Customizes the url structure of global category and tag listings.', 'mp') ?></span>
              <table class="form-table">
                <tr valign="top">
                <th scope="row"><?php _e('Marketplace Base', 'mp') ?></th>
                <td>/<input disabled="disabled" type="text" name="mp[slugs][marketplace]" value="marketplace" size="20" maxlength="50" />/<br />
                </tr>
                <tr valign="top">
                <th scope="row"><?php _e('Product Categories', 'mp') ?></th>
                <td>/marketplace/<input disabled="disabled" type="text" name="mp[slugs][categories]" value="categories" size="20" maxlength="50" />/</td>
                </tr>
                <tr valign="top">
                <th scope="row"><?php _e('Product Tags', 'mp') ?></th>
                <td>/marketplace/<input disabled="disabled" type="text" name="mp[slugs][tags]" value="tags" size="20" maxlength="50" />/</td>
                </tr>
              </table>
            </div>
          </div>
        
        <?php
        //for adding additional settings via plugins
        do_action('mp_network_settings', $settings);
        ?>

        <div class="postbox">
          <h3 class='hndle'><span><?php _e('Shortcodes', 'mp') ?></span></h3>
          <div class="inside">
            <p><?php _e('Shortcodes allow you to include dynamic store content in posts and pages on your site. Simply type or paste them into your post or page content where you would like them to appear. Optional attributes can be added in a format like <em>[shortcode attr1="value" attr2="value"]</em>. Note that depending on your preference above, you may only be able to use these on the main blog.', 'mp') ?></p>
            <table class="form-table">
              <tr>
      				<th scope="row"><?php _e('Global Products List', 'mp') ?></th>
      				<td>
                <strong>[mp_list_global_products]</strong> -
                <span class="description"><?php _e('Displays a network-wide list of products according to preference.', 'mp') ?></span>
                <p>
                <strong><?php _e('Optional Attributes:', 'mp') ?></strong>
                <ul class="mp-shortcode-options">
                  <li><?php _e('"paginate" - Whether to paginate the product list. This is useful to only show a subset. Default: 1', 'mp') ?></li>
                  <li><?php _e('"page" - How many products to display in the product list if "paginate" is set to true. Default: 20', 'mp') ?></li>
                  <li><?php _e('"per_page" - How many products to display in the product list if "paginate" is set to 1.', 'mp') ?></li>
                  <li><?php _e('"order_by" - What field to order products by. Can be: date, title, price, sales, rand. Default: date', 'mp') ?></li>
                  <li><?php _e('"order" - Direction to order products by. Can be: DESC, ASC. Default: DESC', 'mp') ?></li>
                  <li><?php _e('"category" - Limits list to a specific product category. Use the category Slug', 'mp') ?></li>
                  <li><?php _e('"tag" - Limits list to a specific product tag. Use the tag Slug', 'mp') ?></li>
                  <li><?php _e('"show_thumbnail" - Whether to show the product thumbnail. Default: 1', 'mp') ?></li>
                  <li><?php _e('"thumbnail_size" - Max thumbnail width/height. Default: 150', 'mp') ?></li>
                  <li><?php _e('"show_price" - Whether to show the product price. Default: 1', 'mp') ?></li>
                  <li><?php _e('"text" - Choose "excerpt", "content", or "none". Default: excerpt', 'mp') ?></li>
                  <li><?php _e('"as_list" - Whether to show as an unordered list. Default: 0', 'mp') ?></li>
                  <li><?php _e('Example:', 'mp') ?> <em>[mp_list_global_products paginate="1" page="0" per_page="10" order_by="price" order="DESC" category="downloads"]</em></li>
                </ul></p>
                <span class="description"><?php _e('You may also use the mp_list_global_products() template function in your theme with the same arguments.', 'mp') ?></span>
              </td>
              </tr>
              <tr>
      				<th scope="row"><?php _e('Global Tag Cloud', 'mp') ?></th>
      				<td>
                <strong>[mp_global_tag_cloud]</strong> -
                <span class="description"><?php _e('Displays global most used product tags in cloud format from network MarketPress stores.', 'mp') ?></span>
                <p>
                <strong><?php _e('Optional Attributes:', 'mp') ?></strong>
                <ul class="mp-shortcode-options">
                  <li><?php _e('"limit" - Maximum amount of tags to display. Default: 45', 'mp') ?></li>
                  <li><?php _e('"seperator" - String to seperate tags by, like a comma, etc.', 'mp') ?></li>
                  <li><?php _e('Example:', 'mp') ?> <em>[mp_global_tag_cloud limit="55" seperator=", "]</em></li>
                </ul></p>
                <span class="description"><?php _e('You may also use the mp_global_tag_cloud() template function in your theme with the same arguments.', 'mp') ?></span>
              </td>
              </tr>
              <tr>
      				<th scope="row"><?php _e('Global Categories List', 'mp') ?></th>
      				<td>
                <strong>[mp_global_categories_list]</strong> -
                <span class="description"><?php _e('Displays a network-wide HTML list of product categories according to preference.', 'mp') ?></span>
                <p>
                <strong><?php _e('Optional Attributes:', 'mp') ?></strong>
                <ul class="mp-shortcode-options">
                  <li><?php _e('"limit" - Text to display for showing all categories. Default: 50', 'mp') ?></li>
                  <li><?php _e('"order_by" - What column to use for ordering the categories. "count" or "name". Default: count', 'mp') ?></li>
                  <li><?php _e('"order" - Direction to order products by. Can be: DESC, ASC. Default: DESC', 'mp') ?></li>
                  <li><?php _e('"show_count" - Whether to show how many posts are in the category. Default: 0', 'mp') ?></li>
                  <li><?php _e('"include" - What to show, "tags", "categories", or "both".', 'mp') ?></li>
                  <li><?php _e('Example:', 'mp') ?> <em>[mp_global_categories_list limit="30" order_by="name" order="ASC" show_count="1" include="both"]</em></li>
                </ul></p>
                <span class="description"><?php _e('You may also use the mp_global_categories_list() template function in your theme with the same arguments.', 'mp') ?></span>
              </td>
              </tr>
            </table>
          </div>
        </div>

        <p class="submit">
          <input type="submit" name="submit_settings" value="<?php _e('Save Changes', 'mp') ?>" disabled="disabled" />
        </p>
      </form>
    </div>
    </div>
    <?php
  }
}
$mp_wpmu = new MarketPress_MS();

function mp_main_site_id() {
  global $current_site;
  if ( defined( 'MP_ROOT_BLOG' ) ) {
    return MP_ROOT_BLOG;
  } else {
    return $current_site->blog_id;
  }
}

?>