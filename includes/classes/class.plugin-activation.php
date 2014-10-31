<?php
/**
 * Plugin installation and activation
 */
if ( !class_exists( 'CP_Plugin_Activation' ) ) {

	/**
	 * Automatic plugin installation and activation library.
	 * The plugins can be either pre-packaged, downloaded from the WordPress
	 */
	class CP_Plugin_Activation {

		/**
		 *
		 * @var CP_Plugin_Activation
		 */
		public static $instance;

		/**
		 * Holds arrays of plugin details.
		 *
		 * @var array
		 */
		public $plugins = array();

		/**
		 * Name of the querystring argument for the admin page.
		 *
		 * @var string
		 */
		public $menu = '';

		/**
		 * Default absolute path to folder containing pre-packaged plugin zip files.
		 *
		 * @var string Absolute path prefix to packaged zip file location. Default is empty string.
		 */
		public $default_path = '';

		/**
		 * Flag to show admin notices or not.
		 *
		 * @var boolean
		 */
		public $has_notices = true;

		/**
		 * Flag to determine if the user can dismiss the notice nag.
		 *
		 * @var boolean
		 */
		public $dismissable = true;

		/**
		 * Message to be output above nag notice if dismissable is false.
		 *
		 * @var string
		 */
		public $dismiss_msg = '';

		/**
		 * Flag to set automatic activation of plugins. Off by default.
		 *
		 * @var boolean
		 */
		public $is_automatic = false;

		/**
		 * Optional message to display before the plugins table.
		 *
		 * @var string Message filtered by wp_kses_post(). Default is empty string.
		 */
		public $message = '';

		/**
		 * Holds configurable array of strings.
		 *
		 * Default values are added in the constructor.
		 *
		 * @var array
		 */
		public $strings = array();

		/**
		 * Holds the version of WordPress.
		 *
		 * @var int
		 */
		public $wp_version;

		/**
		 * Adds a reference of this object to $instance, populates default strings,
		 *
		 * @see CP_Plugin_Activation::init()
		 */
		public function __construct() {
			global $coursepress;

			//Menu where plugin could be installed
			$this->plugin_name	 = 'MarketPress';
			$this->menu			 = 'coursepress-pro_settings&tab=cp-marketpress';

			if ( CoursePress_Capabilities::is_pro() && !CoursePress_Capabilities::is_campus() ) {
				$this->plugins = array(
					array(
						'name'			 => 'MarketPress', // The plugin name.
						'slug'			 => 'marketpress', // The plugin slug (typically the folder name).
						'base_path'		 => 'marketpress/marketpress.php',
						'source'		 => $coursepress->plugin_dir . 'includes/external/plugins/' . $coursepress->mp_file, // The plugin source.
						'required'		 => false, // If false, the plugin is only 'recommended' instead of required.
						'version'		 => '', // E.g. 1.0.0. If set, the active plugin must be this version or higher.
						'external_url'	 => 'http://premium.wpmudev.org/project/e-commerce/', // If set, overrides default API URL and points to an external URL.
					),
				);
			}
			if ( !CoursePress_Capabilities::is_pro() ) {
				$this->plugins = array(
					array(
						'name'		 => 'MarketPress - WordPress eCommerce', // The plugin name.
						'slug'		 => 'wordpress-ecommerce', // The plugin slug (typically the folder name).
						'required'	 => false, // If false, the plugin is only 'recommended' instead of required.
					),
				);
			}

			$this->config = array(
				'default_path'	 => '', // Default absolute path to pre-packaged plugins.
				'menu'			 => 'coursepress-pro_settings&tab=cp-marketpress', // Menu slug.
				'has_notices'	 => true, // Show admin notices or not.
				'dismissable'	 => true, // If false, a user cannot dismiss the nag message.
				'dismiss_msg'	 => '', // If 'dismissable' is false, this message will be output at top of nag.
				//'is_automatic'	 => true, // Automatically activate plugins after installation or not.
				'message'		 => '', // Message to output right before the plugins table.
				'strings'		 => array(
					'page_title'						 => __( 'Install Plugin', 'cp' ),
					'installing'						 => __( 'Installing Plugin: %s', 'cp' ), // %s = plugin name.
					'oops'								 => __( 'Something went wrong with the plugin API.', 'cp' ),
					'notice_can_install_recommended'	 => sprintf( __( 'CoursePress recommends the following plugin: %2$s.', 'cp' ), $coursepress->name, $this->plugin_name ), // %1$s = plugin name(s).
					'notice_cannot_install'				 => __( 'Sorry, but you do not have the correct permissions to install the plugin. Contact the administrator of this site for help on getting the plugin installed.', 'cp' ), // %1$s = plugin name(s).
					'notice_can_activate_recommended'	 => sprintf( __( 'The following recommended plugin is currently inactive: %1$s.', 'cp' ), $this->plugin_name ), // %1$s = plugin name(s).
					'notice_cannot_activate'			 => __( 'Sorry, but you do not have the correct permissions to activate the plugin. Contact the administrator of this site for help on getting the plugin activated.', 'cp' ), // %1$s = plugin name(s).
					'notice_ask_to_update'				 => sprintf( __( 'The following plugin needs to be updated to its latest version to ensure maximum compatibility with this theme: %1$s.', 'cp' ), $this->plugin_name ), // %1$s = plugin name(s).
					'notice_cannot_update'				 => sprintf( __( 'Sorry, but you do not have the correct permissions to update the %s plugin. Contact the administrator of this site for help on getting the plugin updated.', 'cp' ), $this->plugin_name ), // %1$s = plugin name(s).
					'install_link'						 => __( 'Begin installing plugin', 'cp' ),
					'activate_link'						 => __( 'Begin activating plugin', 'cp' ),
					'return'							 => __( 'Return to Plugins Installer', 'cp' ),
					'plugin_activated'					 => __( 'Plugin activated successfully.', 'cp' ),
					'complete'							 => __( 'Installed and activated successfully.', 'cp' ), // %s = dashboard link.
					'nag_type'							 => 'updated' // Determines admin notice type - can only be 'updated', 'update-nag' or 'error'.
				)
			);

			$this->strings = array(
				'page_title'					 => __( 'Install Plugin', 'cp' ),
				'installing'					 => __( 'Installing Plugin', 'cp' ), // %s = plugin name.
				'oops'							 => __( 'Something went wrong with the plugin API.', 'cp' ),
				'notice_can_install_recommended' => sprintf( __( 'CoursePress recommends the following plugin: %2$s.', 'cp' ), $coursepress->name, $this->plugin_name ), // %1$s = plugin name(s).
				'notice_ask_to_update'			 => sprintf( __( 'The following plugin needs to be updated to its latest version to ensure maximum compatibility with this theme: %1$s.', 'cp' ), $this->plugin_name ), // %1$s = plugin name(s).
				'notice_cannot_update'			 => sprintf( __( 'Sorry, but you do not have the correct permissions to update the %s plugin. Contact the administrator of this site for help on getting the plugin updated.', 'cp' ), $this->plugin_name ), // %1$s = plugin name(s).
				'install_link'					 => __( 'Begin installing plugin', 'cp' ),
				'activate_link'					 => __( 'Begin activating plugin', 'cp' ),
				'return'						 => __( 'Return to Plugins Installer', 'cp' ),
				'dashboard'						 => __( 'Return to the dashboard', 'cp' ),
				'plugin_activated'				 => __( 'Plugin activated successfully.', 'cp' ),
				'activated_successfully'		 => __( 'The following plugin was activated successfully:', 'cp' ),
				'complete'						 => __( 'Installed and activated successfully.', 'cp' ),
				'dismiss'						 => __( 'Dismiss this notice', 'cp' ),
			);
			
			self::$instance = $this;

			// Set the current WordPress version.
			global $wp_version;
			$this->wp_version = $wp_version;

			// When the rest of WP has loaded, kick-start the rest of the class.
			add_action( 'init', array( $this, 'init' ) );
		}

		/**
		 * Initialise the interactions between this class and WordPress.
		 *
		 * Hooks in three new methods for the class: admin_menu, notices and styles.
		 *
		 * @see CP_Plugin_Activation::admin_menu()
		 * @see CP_Plugin_Activation::notices()
		 * @see CP_Plugin_Activation::styles()
		 */
		public function init() {

			// After this point, the plugins should be registered and the configuration set.
			// Proceed only if we have plugins to handle.
			if ( $this->plugins ) {
				$sorted = array();

				//print_r($this->plugins);

				foreach ( $this->plugins as $plugin ) {
					$sorted[] = $plugin[ 'name' ];
				}

				array_multisort( $sorted, SORT_ASC, $this->plugins );

				//add_action( 'admin_menu', array( $this, 'admin_menu' ) );
				add_action( 'admin_head', array( $this, 'dismiss' ) );
				add_filter( 'install_plugin_complete_actions', array( $this, 'actions' ) );

				// Load admin bar in the header to remove flash when installing plugins.
				if ( $this->is_cp_plugin_installation_page() ) {
					remove_action( 'wp_footer', 'wp_admin_bar_render', 1000 );
					remove_action( 'admin_footer', 'wp_admin_bar_render', 1000 );
					add_action( 'wp_head', 'wp_admin_bar_render', 1000 );
					add_action( 'admin_head', 'wp_admin_bar_render', 1000 );
				}

				if ( $this->has_notices ) {
					add_action( 'admin_notices', array( $this, 'notices' ) );
					add_action( 'admin_init', array( $this, 'admin_init' ), 1 );
					add_action( 'admin_enqueue_scripts', array( $this, 'thickbox' ) );
					add_action( 'switch_theme', array( $this, 'update_dismiss' ) );
				}
			}
		}

		/**
		 * Handles calls to show plugin information via links in the notices.
		 *
		 * @global string $tab Used as iframe div class names, helps with styling
		 * @global string $body_id Used as the iframe body ID, helps with styling
		 */
		public function admin_init() {

			if ( !$this->is_cp_plugin_installation_page() ) {
				return;
			}

			if ( isset( $_REQUEST[ 'tab' ] ) && 'cp-marketpress' == $_REQUEST[ 'tab' ] ) {
				require_once ABSPATH . 'wp-admin/includes/plugin-install.php'; // Need for install_plugin_information().

				wp_enqueue_style( 'plugin-install' );

				global $tab, $body_id;
				$body_id = $tab	 = 'cp-marketpress';

				install_plugin_information();

				exit;
			}
		}

		/**
		 * Enqueues thickbox scripts/styles for plugin info.
		 *
		 * Thickbox is not automatically included on all admin pages, so we must
		 * manually enqueue it for those pages.
		 *
		 * Thickbox is only loaded if the user has not dismissed the admin
		 * notice or if there are any plugins left to install and activate.
		 */
		public function thickbox() {

			if ( !get_user_meta( get_current_user_id(), 'cp_plugin_installation_dismissed_notice', true ) ) {
				add_thickbox();
			}
		}

		/**
		 * Echoes plugin installation form.
		 *
		 * @return null Aborts early if we're processing a plugin installation action
		 */
		public function install_plugins_page() {

			// Store new instance of plugin table in object.
			$plugin_table = new CP_List_Table;

			// Return early if processing a plugin installation action.
			if ( $this->do_plugin_install() ) {
				return;
			}
			?>
			<div class="cp wrap">
				<?php $plugin_table->prepare_items(); ?>

				<?php
				if ( isset( $this->message ) ) {
					echo wp_kses_post( $this->message );
				}
				?>

				<form id="cp-plugins" action="" method="post">
					<input type="hidden" name="cp-installation-page" value="<?php echo $this->menu; ?>" />
					<?php $plugin_table->display(); ?>
				</form>

			</div>
			<?php
		}

		/**
		 * Installs a plugin or activates a plugin depending on the hover
		 * link clicked by the user.
		 *
		 * Checks the $_GET variable to see which actions have been
		 * passed and responds with the appropriate method.
		 *
		 * Uses WP_Filesystem to process and handle the plugin installation
		 * method.
		 *
		 * @uses WP_Filesystem
		 * @uses WP_Error
		 * @uses WP_Upgrader
		 * @uses Plugin_Upgrader
		 * @uses Plugin_Installer_Skin
		 *
		 * @return boolean True on success, false on failure
		 */
		protected function do_plugin_install() {

			// All plugin information will be stored in an array for processing.
			$plugin = array();

			// Checks for actions from hover links to process the installation.
			if ( isset( $_GET[ 'plugin' ] ) && ( isset( $_GET[ 'cp-plugin-install' ] ) && 'install-plugin' == $_GET[ 'cp-plugin-install' ] ) ) {
				check_admin_referer( 'cp-plugin-install' );

				$plugin[ 'name' ]	 = $_GET[ 'plugin_name' ]; // Plugin name.
				$plugin[ 'slug' ]	 = $_GET[ 'plugin' ]; // Plugin slug.
				$plugin[ 'source' ]	 = $_GET[ 'plugin_source' ]; // Plugin source.
				// Pass all necessary information via URL if WP_Filesystem is needed.
				$url				 = wp_nonce_url(
				add_query_arg(
				array(
					'page'				 => $this->menu,
					'plugin'			 => $plugin[ 'slug' ],
					'plugin_name'		 => $plugin[ 'name' ],
					'plugin_source'		 => $plugin[ 'source' ],
					'cp-plugin-install'	 => 'install-plugin',
				), network_admin_url( 'admin.php' )
				), 'cp-plugin-install'
				);
				$method				 = ''; // Leave blank so WP_Filesystem can populate it as necessary.
				$fields				 = array( 'cp-plugin-install' ); // Extra fields to pass to WP_Filesystem.

				if ( false === ( $creds = request_filesystem_credentials( $url, $method, false, false, $fields ) ) ) {
					return true;
				}

				if ( !WP_Filesystem( $creds ) ) {
					request_filesystem_credentials( $url, $method, true, false, $fields ); // Setup WP_Filesystem.
					return true;
				}

				require_once ABSPATH . 'wp-admin/includes/plugin-install.php'; // Need for plugins_api.
				require_once ABSPATH . 'wp-admin/includes/class-wp-upgrader.php'; // Need for upgrade classes.
				// Set plugin source to WordPress API link if available.
				if ( isset( $plugin[ 'source' ] ) && 'repo' == $plugin[ 'source' ] ) {
					$api = plugins_api( 'plugin_information', array( 'slug' => $plugin[ 'slug' ], 'fields' => array( 'sections' => false ) ) );

					if ( is_wp_error( $api ) ) {
						wp_die( $this->strings[ 'oops' ] . var_dump( $api ) );
					}

					if ( isset( $api->download_link ) ) {
						$plugin[ 'source' ] = $api->download_link;
					}
				}

				// Set type, based on whether the source starts with http:// or https://.
				$type = preg_match( '|^http(s)?://|', $plugin[ 'source' ] ) ? 'web' : 'upload';

				// Prep variables for Plugin_Installer_Skin class.
				$title	 = sprintf( $this->strings[ 'installing' ], $plugin[ 'name' ] );
				$url	 = add_query_arg( array( 'action' => 'install-plugin', 'plugin' => $plugin[ 'slug' ] ), 'update.php' );
				
				if ( isset( $_GET[ 'from' ] ) ) {
					$url .= add_query_arg( 'from', urlencode( stripslashes( $_GET[ 'from' ] ) ), $url );
				}

				$nonce = 'install-plugin_' . $plugin[ 'slug' ];

				// Prefix a default path to pre-packaged plugins.
				$source = ( 'upload' == $type ) ? $this->default_path . $plugin[ 'source' ] : $plugin[ 'source' ];

				// Create a new instance of Plugin_Upgrader.
				$upgrader	 = new Plugin_Upgrader( $skin		 = new Plugin_Installer_Skin( compact( 'type', 'title', 'url', 'nonce', 'plugin', 'api' ) ) );

				// Perform the action and install the plugin from the $source urldecode().
				$upgrader->install( $source );

				// Flush plugins cache so we can make sure that the installed plugins list is always up to date.
				wp_cache_flush();

				// Only activate plugins if the config option is set to true.

				if ( $this->is_automatic ) {
					$plugin_activate = $upgrader->plugin_info(); // Grab the plugin info from the Plugin_Upgrader method.
					$activate		 = activate_plugin( $plugin_activate ); // Activate the plugin.
					$this->populate_file_path(); // Re-populate the file path now that the plugin has been installed and activated.

					if ( is_wp_error( $activate ) ) {
						echo '<div id="message" class="error"><p>' . $activate->get_error_message() . '</p></div>';
						echo '<p><a href="' . add_query_arg( 'page', $this->menu, network_admin_url( 'admin.php' ) ) . '" title="' . esc_attr( $this->strings[ 'return' ] ) . '" target="_parent">' . $this->strings[ 'return' ] . '</a></p>';
						return true; // End it here if there is an error with automatic activation
					} else {
						echo '<p>' . $this->strings[ 'plugin_activated' ] . '</p>';
					}
				}

				// Display message based on if all plugins are now active or not.
				$complete = array();
				foreach ( $this->plugins as $plugin ) {
					if ( !is_plugin_active( $plugin[ 'file_path' ] ) ) {
						echo '<p><a href="' . add_query_arg( 'page', $this->menu, network_admin_url( 'admin.php' ) ) . '" title="' . esc_attr( $this->strings[ 'return' ] ) . '" target="_parent">' . $this->strings[ 'return' ] . '</a></p>';
						$complete[] = $plugin;
						break;
					}
					// Nothing to store.
					else {
						$complete[] = '';
					}
				}

				// Filter out any empty entries.
				$complete = array_filter( $complete );

				// All plugins are active, so we display the complete string and hide the plugin menu.
				if ( empty( $complete ) ) {
					echo '<p>' . sprintf( $this->strings[ 'complete' ], '<a href="' . network_admin_url() . '" title="' . __( 'Return to the Dashboard', 'cp' ) . '">' . __( 'Return to the Dashboard', 'cp' ) . '</a>' ) . '</p>';
					echo '<style type="text/css">#adminmenu .wp-submenu li.current { display: none !important; }</style>';
				}

				return true;
			}
			// Checks for actions from hover links to process the activation.
			elseif ( isset( $_GET[ 'plugin' ] ) && ( isset( $_GET[ 'cp-activate-plugin' ] ) && 'activate-plugin' == $_GET[ 'cp-activate-plugin' ] ) ) {
				check_admin_referer( 'cp-activate-plugin', 'cp-activate-plugin-nonce' );

				// Populate $plugin array with necessary information.
				$plugin[ 'name' ]	 = $_GET[ 'plugin_name' ];
				$plugin[ 'slug' ]	 = $_GET[ 'plugin' ];
				$plugin[ 'source' ]	 = $_GET[ 'plugin_source' ];

				$plugin_data		 = get_plugins( '/' . $plugin[ 'slug' ] ); // Retrieve all plugins.
				$plugin_file		 = array_keys( $plugin_data ); // Retrieve all plugin files from installed plugins.
				$plugin_to_activate	 = $plugin[ 'slug' ] . '/' . $plugin_file[ 0 ]; // Match plugin slug with appropriate plugin file.
				$activate			 = activate_plugin( $plugin_to_activate ); // Activate the plugin.

				if ( is_wp_error( $activate ) ) {
					echo '<div id="message" class="error"><p>' . $activate->get_error_message() . '</p></div>';
					echo '<p><a href="' . add_query_arg( 'page', $this->menu, network_admin_url( 'admin.php' ) ) . '" title="' . esc_attr( $this->strings[ 'return' ] ) . '" target="_parent">' . $this->strings[ 'return' ] . '</a></p>';
					return true; // End it here if there is an error with activation.
				} else {
					// Make sure message doesn't display again if bulk activation is performed immediately after a single activation.
					if ( !isset( $_POST[ 'action' ] ) ) {
						$msg = $this->strings[ 'activated_successfully' ] . ' <strong>' . $plugin[ 'name' ] . '</strong>';
						echo '<div id="message" class="updated"><p>' . $msg . '</p></div>';
					}
				}
			}

			return false;
		}

		/**
		 * Echoes required plugin notice.
		 *
		 * Outputs a message telling users that a specific plugin is required for
		 * their theme. If appropriate, it includes a link to the form page where
		 * users can install and activate the plugin.
		 *
		 * @global object $current_screen
		 * @return null Returns early if we're on the Install page.
		 */
		public function notices() {

			global $current_screen;

			// Remove nag on the install page.
			if ( $this->is_cp_plugin_installation_page() ) {
				return;
			}

			// Return early if the nag message has been dismissed.
			if ( get_user_meta( get_current_user_id(), 'cp_plugin_installation_dismissed_notice', true ) ) {
				return;
			}

			$installed_plugins = get_plugins(); // Retrieve a list of all the plugins
			$this->populate_file_path();

			$message			 = array(); // Store the messages in an array to be outputted after plugins have looped through.
			$install_link		 = false;   // Set to false, change to true in loop if conditions exist, used for action link 'install'.
			$install_link_count	 = 0; // Used to determine plurality of install action link text.
			$activate_link		 = false;   // Set to false, change to true in loop if conditions exist, used for action link 'activate'.
			$activate_link_count = 0; // Used to determine plurality of activate action link text.

			foreach ( $this->plugins as $plugin ) {
				// If the plugin is installed and active, check for minimum version argument before moving forward.
				if ( is_plugin_active( $plugin[ 'file_path' ] ) ) {
					// A minimum version has been specified.
					if ( isset( $plugin[ 'version' ] ) ) {
						if ( isset( $installed_plugins[ $plugin[ 'file_path' ] ][ 'Version' ] ) ) {
							// If the current version is less than the minimum required version, we display a message.
							if ( version_compare( $installed_plugins[ $plugin[ 'file_path' ] ][ 'Version' ], $plugin[ 'version' ], '<' ) ) {
								if ( current_user_can( 'install_plugins' ) ) {
									$message[ 'notice_ask_to_update' ][] = $plugin[ 'name' ];
								} else {
									$message[ 'notice_cannot_update' ][] = $plugin[ 'name' ];
								}
							}
						}
						// Can't find the plugin, so iterate to the next condition.
						else {
							continue;
						}
					}
					// No minimum version specified, so iterate over the plugin.
					else {
						continue;
					}
				}

				// Not installed.
				if ( !isset( $installed_plugins[ $plugin[ 'file_path' ] ] ) ) {
					$install_link = true; // We need to display the 'install' action link.
					$install_link_count++; // Increment the install link count.
					if ( current_user_can( 'install_plugins' ) ) {
						if ( $plugin[ 'required' ] ) {
							$message[ 'notice_can_install_required' ][] = $plugin[ 'name' ];
						}
						// This plugin is only recommended.
						else {
							$message[ 'notice_can_install_recommended' ][] = $plugin[ 'name' ];
						}
					}
					// Need higher privileges to install the plugin.
					else {
						$message[ 'notice_cannot_install' ][] = $plugin[ 'name' ];
					}
				}
				// Installed but not active.
				elseif ( is_plugin_inactive( $plugin[ 'file_path' ] ) ) {
					$activate_link = true; // We need to display the 'activate' action link.
					$activate_link_count++; // Increment the activate link count.
					if ( current_user_can( 'activate_plugins' ) ) {
						$message[ 'notice_can_activate_recommended' ][] = $plugin[ 'name' ];
					}
					// Need higher privileges to activate the plugin.
					else {
						$message[ 'notice_cannot_activate' ][] = $plugin[ 'name' ];
					}
				}
			}

			// If we have notices to display, we move forward.
			if ( !empty( $message ) ) {
				krsort( $message ); // Sort messages.
				$rendered = ''; // Display all nag messages as strings.
				// If dismissable is false and a message is set, output it now.
				if ( !$this->dismissable && !empty( $this->dismiss_msg ) ) {
					$rendered .= '<p><strong>' . wp_kses_post( $this->dismiss_msg ) . '</strong></p>';
				}

				// Grab all plugin names.
				foreach ( $message as $type => $plugin_groups ) {
					$linked_plugin_groups = array();

					// Count number of plugins in each message group to calculate singular/plural message.
					$count = count( $plugin_groups );

					// Loop through the plugin names to make the ones pulled from the .org repo linked.
					foreach ( $plugin_groups as $plugin_group_single_name ) {
						$external_url	 = $this->_get_plugin_data_from_name( $plugin_group_single_name, 'external_url' );
						$source			 = $this->_get_plugin_data_from_name( $plugin_group_single_name, 'source' );

						if ( $external_url && preg_match( '|^http(s)?://|', $external_url ) ) {
							$linked_plugin_groups[] = '<a href="' . esc_url( $external_url ) . '" title="' . $plugin_group_single_name . '" target="_blank">' . $plugin_group_single_name . '</a>';
						} elseif ( !$source || preg_match( '|^http://wordpress.org/extend/plugins/|', $source ) ) {
							$url = add_query_arg(
							array(
								'tab'		 => 'plugin-information',
								'plugin'	 => $this->_get_plugin_data_from_name( $plugin_group_single_name ),
								'TB_iframe'	 => 'true',
								'width'		 => '640',
								'height'	 => '500',
							), network_admin_url( 'plugin-install.php' )
							);

							$linked_plugin_groups[] = '<a href="' . esc_url( $url ) . '" class="thickbox" title="' . $plugin_group_single_name . '">' . $plugin_group_single_name . '</a>';
						} else {
							$linked_plugin_groups[] = $plugin_group_single_name; // No hyperlink.
						}

						if ( isset( $linked_plugin_groups ) && (array) $linked_plugin_groups ) {
							$plugin_groups = $linked_plugin_groups;
						}
					}

					$last_plugin = array_pop( $plugin_groups ); // Pop off last name to prep for readability.
					$imploded	 = empty( $plugin_groups ) ? '<em>' . $last_plugin . '</em>' : '<em>' . ( implode( ', ', $plugin_groups ) . '</em> and <em>' . $last_plugin . '</em>' );

					$rendered .= '<p>' . $this->strings[ $type ]/* sprintf( translate_nooped_plural( $this->strings[ $type ], $count, 'cp' ), $imploded, $count ) */ . '</p>';
				}

				// Setup variables to determine if action links are needed.
				$show_install_link	 = $install_link ? '<a href="' . add_query_arg( 'page', $this->menu, network_admin_url( 'admin.php' ) ) . '">' . $this->strings[ 'install_link' ] . '</a>' : '';
				$show_activate_link	 = $activate_link ? '<a href="' . add_query_arg( 'page', $this->menu, network_admin_url( 'admin.php' ) ) . '">' . $this->strings[ 'activate_link' ] . '</a>' : '';

				// Define all of the action links.
				$action_links = apply_filters(
				'cp_notice_action_links', array(
					'install'	 => ( current_user_can( 'install_plugins' ) ) ? $show_install_link : '',
					'activate'	 => ( current_user_can( 'activate_plugins' ) ) ? $show_activate_link : '',
					'dismiss'	 => $this->dismissable ? '<a class="dismiss-notice" href="' . add_query_arg( 'cp-dismiss', 'dismiss_admin_notices' ) . '" target="_parent">' . $this->strings[ 'dismiss' ] . '</a>' : '',
				)
				);

				$action_links = array_filter( $action_links ); // Remove any empty array items.
				if ( $action_links ) {
					$rendered .= '<p>' . implode( ' | ', $action_links ) . '</p>';
				}

				// Register the nag messages and prepare them to be processed.
				$nag_class = version_compare( $this->wp_version, '3.8', '<' ) ? 'updated' : 'update-nag';
				if ( !empty( $this->strings[ 'nag_type' ] ) ) {
					add_settings_error( 'cp', 'cp', $rendered, sanitize_html_class( strtolower( $this->strings[ 'nag_type' ] ) ) );
				} else {
					add_settings_error( 'cp', 'cp', $rendered, $nag_class );
				}
			}

			// Admin options pages already output settings_errors, so this is to avoid duplication.
			if ( 'options-general' !== $current_screen->parent_base ) {
				settings_errors( 'cp' );
			}
		}

		/**
		 * Add dismissable admin notices.
		 *
		 * Appends a link to the admin nag messages. If clicked, the admin notice disappears and no longer is visible to users.
		 */
		public function dismiss() {

			if ( isset( $_GET[ 'cp-dismiss' ] ) ) {
				update_user_meta( get_current_user_id(), 'cp_plugin_installation_dismissed_notice', 1 );
			}
		}

		/**
		 * Add individual plugin to our collection of plugins.
		 *
		 * If the required keys are not set or the plugin has already
		 * been registered, the plugin is not added.
		 *
		 * @param array $plugin Array of plugin arguments.
		 */
		public function register( $plugin ) {

			if ( !isset( $plugin[ 'slug' ] ) || !isset( $plugin[ 'name' ] ) ) {
				return;
			}

			foreach ( $this->plugins as $registered_plugin ) {
				if ( $plugin[ 'slug' ] == $registered_plugin[ 'slug' ] ) {
					return;
				}
			}

			$this->plugins[] = $plugin;
		}

		/**
		 * Amend default configuration settings.
		 *
		 * @param array $config Array of config options to pass as class properties.
		 */
		public function config( $config ) {

			$keys = array( 'default_path', 'has_notices', 'dismissable', 'dismiss_msg', 'menu', 'is_automatic', 'message', 'strings' );

			foreach ( $keys as $key ) {
				if ( isset( $config[ $key ] ) ) {
					if ( is_array( $config[ $key ] ) ) {
						foreach ( $config[ $key ] as $subkey => $value ) {
							$this->{$key}[ $subkey ] = $value;
						}
					} else {
						$this->$key = $config[ $key ];
					}
				}
			}
		}

		/**
		 * Amend action link after plugin installation.
		 *
		 * @param array $install_actions Existing array of actions.
		 * @return array                 Amended array of actions.
		 */
		public function actions( $install_actions ) {
			if ( $this->is_cp_plugin_installation_page() ) {
				return false;
			}

			return $install_actions;
		}

		/**
		 * Set file_path key for each installed plugin.
		 */
		public function populate_file_path() {
			// Add file_path key for all plugins.
			foreach ( $this->plugins as $plugin => $values ) {
				$this->plugins[ $plugin ][ 'file_path' ] = $this->_get_plugin_basename_from_slug( $values[ 'slug' ] );
			}
		}

		/**
		 * Helper function to extract the file path of the plugin file from the
		 * plugin slug, if the plugin is installed.
		 *
		 * @param string $slug Plugin slug (typically folder name) as provided by the developer.
		 * @return string      Either file path for plugin if installed, or just the plugin slug.
		 */
		protected function _get_plugin_basename_from_slug( $slug ) {
			$keys = array_keys( get_plugins() );

			foreach ( $keys as $key ) {
				if ( preg_match( '|^' . $slug . '/|', $key ) ) {
					return $key;
				}
			}

			return $slug;
		}

		/**
		 * Retrieve plugin data, given the plugin name.
		 *
		 * Loops through the registered plugins looking for $name. If it finds it,
		 * it returns the $data from that plugin. Otherwise, returns false.
		 *
		 * @param string $name    Name of the plugin, as it was registered.
		 * @param string $data    Optional. Array key of plugin data to return. Default is slug.
		 * @return string|boolean Plugin slug if found, false otherwise.
		 */
		protected function _get_plugin_data_from_name( $name, $data = 'slug' ) {

			foreach ( $this->plugins as $plugin => $values ) {
				if ( $name == $values[ 'name' ] && isset( $values[ $data ] ) ) {
					return $values[ $data ];
				}
			}

			return false;
		}

		protected function is_cp_plugin_installation_page() {

			if ( isset( $_GET[ 'tab' ] ) && $this->menu === $_GET[ 'page' ] ) {
				return true;
			}

			return false;
		}

		/**
		 * Delete dismissable nag option when theme is switched.
		 *
		 * This ensures that the user is again reminded via nag of required
		 * and/or recommended plugins if they re-activate the theme.
		 */
		public function update_dismiss() {
			delete_user_meta( get_current_user_id(), 'cp_plugin_installation_dismissed_notice' );
		}

		/**
		 * Returns the singleton instance of the class.
		 *
		 * @return object The CP_Plugin_Activation object.
		 */
		public static function get_instance() {

			if ( !isset( self::$instance ) && !( self::$instance instanceof CP_Plugin_Activation ) ) {
				self::$instance = new CP_Plugin_Activation();
			}

			return self::$instance;
		}

	}

	// Ensure only one instance of the class is ever invoked.
	$cp_ap = CP_Plugin_Activation::get_instance();
}

if ( !function_exists( 'cp_ap' ) ) {

	/**
	 * Helper function to register a collection of required plugins.
	 * @api
	 *
	 * @param array $plugins An array of plugin arrays.
	 * @param array $config  Optional. An array of configuration values.
	 */
	function cp_ap( $plugins, $config = array() ) {

		foreach ( $plugins as $plugin ) {
			CP_Plugin_Activation::$instance->register( $plugin );
		}

		if ( $config ) {
			CP_Plugin_Activation::$instance->config( $config );
		}
	}

}

/**
 * WP_List_Table isn't always available. If it isn't available,
 * we load it here.
 */
if ( !class_exists( 'WP_List_Table' ) ) {
	require_once( ABSPATH . 'wp-admin/includes/class-wp-list-table.php' );
}

if ( !class_exists( 'CP_List_Table' ) ) {

	/**
	 * List table class for handling plugins.
	 *
	 * Extends the WP_List_Table class to provide a future-compatible
	 * way of listing out all required/recommended plugins.
	 *
	 * Gives users an interface similar to the Plugin Administration
	 * area with similar (albeit stripped down) capabilities.
	 *
	 */
	class CP_List_Table extends WP_List_Table {

		/**
		 * References parent constructor and sets defaults for class.
		 *
		 * and stores it in the global object CP_Plugin_Activation::$instance.
		 *
		 * @global unknown $status
		 * @global string $page
		 */
		public function __construct() {

			global $status, $page;

			parent::__construct(
			array(
				'singular'	 => 'plugin',
				'plural'	 => 'plugins',
				'ajax'		 => false,
			)
			);
		}

		/**
		 * Gathers and renames all of our plugin information to be used by
		 * WP_List_Table to create our table.
		 *
		 * @return array $table_data Information for use in table.
		 */
		protected function _gather_plugin_data() {

			// Load thickbox for plugin links.
			CP_Plugin_Activation::$instance->admin_init();
			CP_Plugin_Activation::$instance->thickbox();

			// Prep variables for use and grab list of all installed plugins.
			$table_data			 = array();
			$i					 = 0;
			$installed_plugins	 = get_plugins();

			foreach ( CP_Plugin_Activation::$instance->plugins as $plugin ) {
				if ( is_plugin_active( $plugin[ 'file_path' ] ) ) {
					continue; // No need to display plugins if they are installed and activated.
				}

				$table_data[ $i ][ 'sanitized_plugin' ]	 = $plugin[ 'name' ];
				$table_data[ $i ][ 'slug' ]				 = $this->_get_plugin_data_from_name( $plugin[ 'name' ] );

				$external_url	 = $this->_get_plugin_data_from_name( $plugin[ 'name' ], 'external_url' );
				$source			 = $this->_get_plugin_data_from_name( $plugin[ 'name' ], 'source' );

				if ( $external_url && preg_match( '|^http(s)?://|', $external_url ) ) {
					$table_data[ $i ][ 'plugin' ] = '<strong><a href="' . esc_url( $external_url ) . '" title="' . $plugin[ 'name' ] . '" target="_blank">' . $plugin[ 'name' ] . '</a></strong>';
				} elseif ( !$source || preg_match( '|^http://wordpress.org/extend/plugins/|', $source ) ) {
					$url							 = add_query_arg(
					array(
						'tab'		 => 'plugin-information',
						'plugin'	 => $this->_get_plugin_data_from_name( $plugin[ 'name' ] ),
						'TB_iframe'	 => 'true',
						'width'		 => '640',
						'height'	 => '500',
					), network_admin_url( 'plugin-install.php' )
					);
					$table_data[ $i ][ 'plugin' ]	 = '<strong><a href="' . esc_url( $url ) . '" class="thickbox" title="' . $plugin[ 'name' ] . '">' . $plugin[ 'name' ] . '</a></strong>';
				} else {
					$table_data[ $i ][ 'plugin' ] = '<strong>' . $plugin[ 'name' ] . '</strong>'; // No hyperlink.
				}

				if ( isset( $table_data[ $i ][ 'plugin' ] ) && (array) $table_data[ $i ][ 'plugin' ] ) {
					$plugin[ 'name' ] = $table_data[ $i ][ 'plugin' ];
				}

				if ( !empty( $plugin[ 'source' ] ) ) {
					// The plugin must be from a private repository.
					if ( preg_match( '|^http(s)?://|', $plugin[ 'source' ] ) ) {
						$table_data[ $i ][ 'source' ] = __( 'Private Repository', 'cp' );
						// The plugin is pre-packaged with the theme.
					} else {
						$table_data[ $i ][ 'source' ] = __( 'Pre-packed / Included in the plugin', 'cp' );
					}
				}
				// The plugin is from the WordPress repository.
				else {
					$table_data[ $i ][ 'source' ] = __( 'WordPress Repository', 'cp' );
				}

				$table_data[ $i ][ 'type' ] = isset( $plugin[ 'required' ] ) && $plugin[ 'required' ] ? __( 'Required', 'cp' ) : __( 'Recommended', 'cp' );

				if ( !isset( $installed_plugins[ $plugin[ 'base_path' ] ] ) ) {
					$table_data[ $i ][ 'status' ] = sprintf( '%1$s', __( 'Not Installed', 'cp' ) );
				} elseif ( is_plugin_inactive( $plugin[ 'file_path' ] ) ) {
					$table_data[ $i ][ 'status' ] = sprintf( '%1$s', __( 'Installed But Not Activated', 'cp' ) );
				}

				$table_data[ $i ][ 'file_path' ] = $plugin[ 'file_path' ];
				$table_data[ $i ][ 'url' ]		 = isset( $plugin[ 'source' ] ) ? $plugin[ 'source' ] : 'repo';

				$i++;
			}

			// Sort plugins by Required/Recommended type and by alphabetical listing within each type.
			$resort	 = array();
			$req	 = array();
			$rec	 = array();

			// Grab all the plugin types.
			foreach ( $table_data as $plugin ) {
				$resort[] = $plugin[ 'type' ];
			}

			// Sort each plugin by type.
			foreach ( $resort as $type ) {
				if ( 'Required' == $type ) {
					$req[] = $type;
				} else {
					$rec[] = $type;
				}
			}

			// Sort alphabetically each plugin type array, merge them and then sort in reverse (lists Required plugins first).
			sort( $req );
			sort( $rec );
			array_merge( $resort, $req, $rec );
			array_multisort( $resort, SORT_DESC, $table_data );

			return $table_data;
		}

		/**
		 * Retrieve plugin data, given the plugin name. Taken from the
		 * CP_Plugin_Activation class.
		 *
		 * Loops through the registered plugins looking for $name. If it finds it,
		 * it returns the $data from that plugin. Otherwise, returns false.
		 *
		 * @param string $name Name of the plugin, as it was registered.
		 * @param string $data Optional. Array key of plugin data to return. Default is slug.
		 * @return string|boolean Plugin slug if found, false otherwise.
		 */
		protected function _get_plugin_data_from_name( $name, $data = 'slug' ) {

			foreach ( CP_Plugin_Activation::$instance->plugins as $plugin => $values ) {
				if ( $name == $values[ 'name' ] && isset( $values[ $data ] ) ) {
					return $values[ $data ];
				}
			}

			return false;
		}

		/**
		 * Create default columns to display important plugin information
		 * like type, action and status.
		 *
		 * @param array $item         Array of item data.
		 * @param string $column_name The name of the column.
		 */
		public function column_default( $item, $column_name ) {

			switch ( $column_name ) {
				case 'source':
				case 'type':
				case 'status':
					return $item[ $column_name ];
			}
		}

		/**
		 * Create default title column along with action links of 'Install'
		 * and 'Activate'.
		 *
		 * @param array $item Array of item data.
		 * @return string     The action hover links.
		 */
		public function column_plugin( $item ) {

			$installed_plugins = get_plugins();
			//print_r( $item );
			// No need to display any hover links.
			if ( is_plugin_active( $item[ 'file_path' ] ) ) {
				$actions = array();
			}

			// We need to display the 'Install' hover link.
			if ( !isset( $installed_plugins[ $item[ 'file_path' ] ] ) ) {
				$actions = array(
					'install' => sprintf(
					'<a href="%1$s" title="' . __( 'Install', 'cp' ) . ' %2$s">' . __( 'Install', 'cp' ) . '</a>', wp_nonce_url(
					add_query_arg(
					array(
						'page'				 => CP_Plugin_Activation::$instance->menu,
						'plugin'			 => $item[ 'slug' ],
						'plugin_name'		 => $item[ 'sanitized_plugin' ],
						'plugin_source'		 => $item[ 'url' ],
						'cp-plugin-install'	 => 'install-plugin',
					), network_admin_url( 'admin.php' )
					), 'cp-plugin-install'
					), $item[ 'sanitized_plugin' ]
					),
				);
			}
			// We need to display the 'Activate' hover link.
			elseif ( is_plugin_inactive( $item[ 'file_path' ] ) ) {
				$actions = array(
					'activate' => sprintf(
					'<a href="%1$s" title="' . __( 'Activate', 'cp' ) . ' %2$s">' . __( 'Activate', 'cp' ) . '</a>', add_query_arg(
					array(
						'page'						 => CP_Plugin_Activation::$instance->menu,
						'plugin'					 => $item[ 'slug' ],
						'plugin_name'				 => $item[ 'sanitized_plugin' ],
						'plugin_source'				 => $item[ 'url' ],
						'cp-activate-plugin'		 => 'activate-plugin',
						'cp-activate-plugin-nonce'	 => wp_create_nonce( 'cp-activate-plugin' ),
					), network_admin_url( 'admin.php' )
					), $item[ 'sanitized_plugin' ]
					),
				);
			}

			return sprintf( '%1$s %2$s', $item[ 'plugin' ], $this->row_actions( $actions ) );
		}

		/**
		 * Sets default message within the plugins table if no plugins
		 * are left for interaction.
		 *
		 * Hides the menu item to prevent the user from clicking and
		 * getting a permissions error.
		 */
		public function no_items() {

			printf( __( 'No plugins to install or activate. <a href="%1$s" title="Return to the Dashboard">Return to the Dashboard</a>', 'cp' ), network_admin_url() );
			echo '<style type="text/css">#adminmenu .wp-submenu li.current { display: none !important; }</style>';
		}

		/**
		 * Output all the column information within the table.
		 *
		 * @return array $columns The column names.
		 */
		public function get_columns() {

			$columns = array(
				'plugin' => __( 'Plugin', 'cp' ),
				'source' => __( 'Source', 'cp' ),
				/* 'type'	 => __( 'Type', 'cp' ), */
				'status' => __( 'Status', 'cp' )
			);

			return $columns;
		}

		/**
		 * Prepares all of our information to be outputted into a usable table.
		 */
		public function prepare_items() {

			$per_page				 = 1; // Set it high so we shouldn't have to worry about pagination.
			$columns				 = $this->get_columns(); // Get all necessary column information.
			$hidden					 = array(); // No columns to hide, but we must set as an array.
			$sortable				 = array(); // No reason to make sortable columns.
			$this->_column_headers	 = array( $columns, $hidden, $sortable ); // Get all necessary column headers.
			// Process our bulk actions here.
			//$this->process_bulk_actions();
			// Store all of our plugin data into $items array so WP_List_Table can use it.
			$this->items			 = $this->_gather_plugin_data();
		}

	}

}