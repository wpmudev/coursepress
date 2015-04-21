<?php
/**
 * Plugin installation and activation
 */
if ( ! class_exists( 'CP_Plugin_Activation' ) ) {

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
		public $plugin = array();

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

			// Bail out if we're on Campus
			if ( CoursePress_Capabilities::is_campus() ) {
				return false;
			}

			global $coursepress, $plugin_dir, $screen_base;

			//Menu where plugin could be installed
			$this->tab  = 'cp-marketpress';
			$this->menu = $screen_base . '_settings&tab=' . $this->tab;

			if ( CoursePress_Capabilities::is_pro() ) {
				$this->plugin = array(
					array(
						'name'           => 'MarketPress',
						// The plugin name.
						'slug'           => 'marketpress',
						// The plugin slug (typically the folder name).
						'base_path'      => 'marketpress/marketpress.php',
						'source'         => is_object( $coursepress ) ? $coursepress->plugin_dir . 'includes/plugins/' . $coursepress->mp_file : '',
						// The plugin source.
						'source_message' => __( 'Included in the CoursePress Plugin', 'cp' ),
						'external_url'   => '',
						// http://premium.wpmudev.org/project/e-commerce/
					),
				);
			}
			if ( ! CoursePress_Capabilities::is_pro() ) {
				$this->plugin = array(
					array(
						'name'           => 'MarketPress - WordPress eCommerce',
						// The plugin name.
						'slug'           => 'wordpress-ecommerce',
						// The plugin slug (typically the folder name).
						'base_path'      => 'wordpress-ecommerce/marketpress.php',
						'source'         => 'downloads.wordpress.org/plugin/wordpress-ecommerce.zip',
						//without protocol (i.e. https://) because it may be killed by mod_security
						'source_message' => __( 'WordPress.org Repository', 'cp' ),
						'external_url'   => '',
						// https://wordpress.org/plugins/wordpress-ecommerce/
					),
				);
			}

			$this->plugin = $this->plugin[0];

			$this->config = array(
				'default_path' => '', // Default absolute path to pre-packaged plugins.
				'menu'         => $screen_base . '_settings&tab=' . $this->tab, // Menu slug.
				'has_notices'  => true, // Show admin notices or not.
				'dismissable'  => true, // If false, a user cannot dismiss the nag message.
				'dismiss_msg'  => '', // If 'dismissable' is false, this message will be output at top of nag.
				'message'      => '', // Message to output right before the plugins table.
			);

			$this->strings = array(
				'page_title'                      => __( 'Install Plugin', 'cp' ),
				'installing'                      => sprintf( __( 'Installing Plugin: %s', 'cp' ), $this->plugin['name'] ),
				'oops'                            => __( 'Something went wrong with the plugin API.', 'cp' ),
				'notice_can_install_recommended'  => sprintf( __( 'Install %1$s plugin in order to sell courses.', 'cp' ), $this->plugin['name'] ),
				'notice_can_activate_recommended' => '',
				'install_link'                    => sprintf( __( 'Install %1$s', 'cp' ), $this->plugin['name'] ),
				'activate_link'                   => sprintf( __( 'Activate %1$s plugin in order to sell courses', 'cp' ), $this->plugin['name'] ),
				'return'                          => __( 'Return to MarketPress Installer', 'cp' ),
				'dashboard'                       => __( 'Return to the dashboard', 'cp' ),
				'plugin_activated'                => __( 'Plugin activated successfully.', 'cp' ),
				'activated_successfully'          => __( 'The following plugin was activated successfully:', 'cp' ),
				'complete'                        => __( 'Installed and activated successfully.', 'cp' ),
				'dismiss'                         => __( 'Dismiss this notice', 'cp' ),
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
			if ( $this->plugin ) {
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
				} else {

				}
			} else {

			}
		}

		/**
		 * Handles calls to show plugin information via links in the notices.
		 *
		 * @global string $tab Used as iframe div class names, helps with styling
		 * @global string $body_id Used as the iframe body ID, helps with styling
		 */
		public function admin_init() {

			if ( ! $this->is_cp_plugin_installation_page() ) {
				return;
			}

			if ( isset( $_REQUEST['tab'] ) && $this->tab == $_REQUEST['tab'] ) {
				require_once ABSPATH . 'wp-admin/includes/plugin-install.php'; // Need for install_plugin_information().

				wp_enqueue_style( 'plugin-install' );

				global $tab, $body_id;
				$body_id = $tab = $this->tab;

				exit;
			}
		}

		function is_plugin_active( $plugin ) {
			if ( is_plugin_active_for_network( $plugin ) || is_plugin_active( $plugin ) ) {
				return true;
			} else {
				return false;
			}
		}

		function is_plugin_installed( $plugin_dir ) {
			$plugins = get_plugins( $plugin_dir );
			if ( $plugins ) {
				return true;
			}

			return false;
		}

		public function get_plugin_status( $plugin_dir ) {
			$status = '';
			if ( $this->is_plugin_installed( '/' . $plugin_dir ) ) {
				$status .= '<font color="green">' . __( 'Installed', 'cp' ) . '</font>';
			} else {
				$status .= '<font color="red">' . __( 'Not Installed', 'cp' ) . '</font>';
			}

			$status .= ' / ';

			if ( is_plugin_active( $this->plugin['base_path'] ) ) {
				$status .= '<font color="green">' . __( 'Active', 'cp' ) . '</font>';
			} else {
				$status .= '<font color="red">' . __( 'Inactive', 'cp' ) . '</font>';
			}

			return $status;
		}

		public function get_plugin_activation_title( $plugin_dir ) {
			$status = '';
			if ( ! $this->is_plugin_installed( '/' . $plugin_dir ) ) {
				$installed = false;
			} else {
				$installed = true;
			}

			$active = false;
			if ( ! is_plugin_active( $this->plugin['base_path'] ) ) {
				if ( $installed ) {
					$active = false;
					$status .= __( 'Activate ', 'cp' );
				} else {
					$status .= __( 'Install & Activate ', 'cp' );
				}
			} else {
				$active = true;
			}

			if ( $active && $installed ) {
				$status = '';
			}

			return $status;
		}

		public function get_plugin_action_link() {
			// No need to display any hover links.
			if ( $this->is_plugin_active( $this->plugin['base_path'] ) ) {
				$actions = array();
			}

			// We need to display the 'Install' hover link.
			if ( ! $this->is_plugin_installed( '/' . $this->plugin['slug'] ) ) {
				$actions = array(
					'install' => sprintf(
						'<a href="%1$s" title="' . __( 'Install', 'cp' ) . ' %2$s">' . __( 'Install', 'cp' ) . '</a>', wp_nonce_url(
						esc_url( add_query_arg(
							array(
								'page'              => CP_Plugin_Activation::$instance->menu,
								'plugin'            => $this->plugin['slug'],
								'plugin_name'       => $this->plugin['name'],
								'plugin_source'     => $this->plugin['source'],
								'cp-plugin-install' => 'install-plugin',
							), admin_url( 'admin.php' )
						) ), 'cp-plugin-install'
					), $this->plugin['name']
					),
				);
			} // We need to display the 'Activate' hover link.
			elseif ( ! $this->is_plugin_active( $this->plugin['base_path'] ) ) {
				$actions = array(
					'activate' => sprintf(
						'<a href="%1$s" title="' . __( 'Activate', 'cp' ) . ' %2$s">' . __( 'Activate', 'cp' ) . '</a>', esc_url( add_query_arg(
						array(
							'page'                     => CP_Plugin_Activation::$instance->menu,
							'plugin'                   => $this->plugin['slug'],
							'plugin_name'              => $this->plugin['name'],
							'plugin_source'            => $this->plugin['source'],
							'cp-activate-plugin'       => 'activate-plugin',
							'cp-activate-plugin-nonce' => wp_create_nonce( 'cp-activate-plugin' ),
						), admin_url( 'admin.php' )
					) ), $this->plugin['name']
					),
				);
			}

			return $actions;
		}

		/**
		 * Echoes plugin installation form.
		 *
		 * @return null Aborts early if we're processing a plugin installation action
		 */
		public function install_plugins_page() {
			// Return early if processing a plugin installation action.
			if ( $this->do_plugin_install() ) {
				return;
			}
			?>
			<div class="cp wrap">
				<h2><?php echo $this->get_plugin_activation_title( $this->plugin['slug'] ) . $this->plugin['name']; ?></h2>
				<br/>

				<?php
				if ( isset( $this->message ) ) {
					echo wp_kses_post( $this->message );
				}
				?>

				<form id="cp-plugins" action="" method="post">
					<input type="hidden" name="cp-installation-page" value="<?php echo $this->menu; ?>"/>
					<table class="wp-list-table widefat fixed plugins">
						<thead>
						<tr>
							<th scope="col" id="plugin" class="manage-column column-plugin" style=""><?php _e( 'Plugin', 'cp' ); ?></th>
							<th scope="col" id="source" class="manage-column column-source" style=""><?php _e( 'Source', 'cp' ); ?></th>
							<th scope="col" id="status" class="manage-column column-status" style=""><?php _e( 'Status', 'cp' ); ?></th>
						</tr>
						</thead>
						<?php
						$is_link = isset( $this->plugin['external_url'] ) && $this->plugin['external_url'] !== '' ? true : false;
						?>
						<tbody id="the-list" data-wp-lists="list:plugin">
						<tr class="alternate">
							<td class="plugin column-plugin"><strong><?php if ($is_link) { ?>
									<a href="<?php echo $this->plugin['external_url']; ?>" title="<?php echo $this->plugin['name']; ?>" target="_blank"><?php }
										echo $this->plugin['name'];
										if ($is_link) { ?></a><?php } ?></strong>

								<div class="row-actions">
										<span class="install">
											<?php
											$action_link = $this->get_plugin_action_link();
											if ( current_user_can( 'install_plugins' ) ) {
												echo isset( $action_link['install'] ) ? $action_link['install'] : '';
											}
											if ( current_user_can( 'activate_plugins' ) ) {
												echo isset( $action_link['activate'] ) ? $action_link['activate'] : '';
											}
											?>
										</span>
								</div>
							</td>
							<td class="source column-source"><?php echo $this->plugin['source_message']; ?></td>
							<td class="status column-status">
								<?php echo $this->get_plugin_status( $this->plugin['slug'] ); ?>
							</td>
						</tr>
						</tbody>
					</table>
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
			if ( isset( $_GET['plugin'] ) && ( isset( $_GET['cp-plugin-install'] ) && 'install-plugin' == $_GET['cp-plugin-install'] ) ) {
				check_admin_referer( 'cp-plugin-install' );

				$plugin['name']   = $_GET['plugin_name']; // Plugin name.
				$plugin['slug']   = $_GET['plugin']; // Plugin slug.
				$plugin['source'] = $_GET['plugin_source']; // Plugin source.
				// Pass all necessary information via URL if WP_Filesystem is needed.
				$url = wp_nonce_url(
					esc_url( add_query_arg(
						array(
							'page'              => $this->menu,
							'plugin'            => $plugin['slug'],
							'plugin_name'       => $plugin['name'],
							'plugin_source'     => $plugin['source'],
							'cp-plugin-install' => 'install-plugin',
						), admin_url( 'admin.php' )
					) ), 'cp-plugin-install'
				);

				$method = ''; // Leave blank so WP_Filesystem can populate it as necessary.
				$fields = array( 'cp-plugin-install' ); // Extra fields to pass to WP_Filesystem.

				if ( false === ( $creds = request_filesystem_credentials( $url, $method, false, false, $fields ) ) ) {
					return true;
				}

				if ( ! WP_Filesystem( $creds ) ) {
					request_filesystem_credentials( $url, $method, true, false, $fields ); // Setup WP_Filesystem.
					return true;
				}

				require_once ABSPATH . 'wp-admin/includes/plugin-install.php'; // Need for plugins_api.
				require_once ABSPATH . 'wp-admin/includes/class-wp-upgrader.php'; // Need for upgrade classes.
				// Prep variables for Plugin_Installer_Skin class.
				$title = sprintf( $this->strings['installing'], $plugin['name'] );
				$url   = add_query_arg( array(
					'action' => 'install-plugin',
					'plugin' => $plugin['slug']
				), 'update.php' );

				if ( isset( $_GET['from'] ) ) {
					$url .= add_query_arg( 'from', urlencode( stripslashes( $_GET['from'] ) ), $url );
				}
				$url = esc_url( $url ); // Avoid XSS

				$nonce = 'install-plugin_' . $plugin['slug'];

				// Prefix a default path to pre-packaged plugins.
				if ( ! CoursePress_Capabilities::is_pro() ) {
					$source = 'https://' . $plugin['source']; //added protocol to avoid mod_security
				} else {
					$source = $plugin['source'];
				}

				// Create a new instance of Plugin_Upgrader.
				$upgrader = new Plugin_Upgrader( $skin = new Plugin_Installer_Skin( compact( 'type', 'title', 'url', 'nonce', 'plugin', 'api' ) ) );

				// Perform the action and install the plugin from the $source urldecode().
				$upgrader->install( $source );

				// Flush plugins cache so we can make sure that the installed plugins list is always up to date.
				wp_cache_flush();

				// Display message based on if all plugins are now active or not.
				$complete = array();

				if ( ! $this->is_plugin_active( $this->plugin['base_path'] ) ) {
					echo '<p><a href="' . esc_url( add_query_arg( 'page', $this->menu, admin_url( 'admin.php' ) ) ) . '" title="' . esc_attr( $this->strings['return'] ) . '" target="_parent">' . $this->strings['return'] . '</a></p>';
					$complete[] = $plugin;
				} // Nothing to store.
				else {
					$complete[] = '';
				}

				// Filter out any empty entries.
				$complete = array_filter( $complete );

				// All plugins are active, so we display the complete string and hide the plugin menu.
				if ( empty( $complete ) ) {
					echo '<p>' . sprintf( $this->strings['complete'], '<a href="' . admin_url() . '" title="' . __( 'Return to the Dashboard', 'cp' ) . '">' . __( 'Return to the Dashboard', 'cp' ) . '</a>' ) . '</p>';
					echo '<style type="text/css">#adminmenu .wp-submenu li.current { display: none !important; }</style>';
				}

				return true;
			} // Checks for actions from hover links to process the activation.
			elseif ( isset( $_GET['plugin'] ) && ( isset( $_GET['cp-activate-plugin'] ) && 'activate-plugin' == $_GET['cp-activate-plugin'] ) ) {
				check_admin_referer( 'cp-activate-plugin', 'cp-activate-plugin-nonce' );

				// Populate $plugin array with necessary information.
				$plugin['name']   = $_GET['plugin_name'];
				$plugin['slug']   = $_GET['plugin'];
				$plugin['source'] = $_GET['plugin_source'];

				$plugin_data        = get_plugins( '/' . $plugin['slug'] ); // Retrieve all plugins.
				$plugin_file        = array_keys( $plugin_data ); // Retrieve all plugin files from installed plugins.
				$plugin_to_activate = $plugin['slug'] . '/' . $plugin_file[0]; // Match plugin slug with appropriate plugin file.
				$activate           = activate_plugin( $plugin_to_activate ); // Activate the plugin.

				if ( is_wp_error( $activate ) ) {
					echo '<div id="message" class="error"><p>' . $activate->get_error_message() . '</p></div>';
					echo '<p><a href="' . esc_url( add_query_arg( 'page', $this->menu, admin_url( 'admin.php' ) ) ) . '" title="' . esc_attr( $this->strings['return'] ) . '" target="_parent">' . $this->strings['return'] . '</a></p>';

					return true; // End it here if there is an error with activation.
				} else {
					// Make sure message doesn't display again if bulk activation is performed immediately after a single activation.
					if ( ! isset( $_POST['action'] ) ) {
						$msg = $this->strings['activated_successfully'] . ' <strong>' . $plugin['name'] . '</strong>';
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

			if ( ! isset( $_GET['tab'] ) || isset( $_GET['tab'] ) && $_GET['tab'] !== $this->tab ) {

				// Return early if the nag message has been dismissed.
				if ( get_user_meta( get_current_user_id(), 'cp_plugin_installation_dismissed_notice', true ) ) {
					return;
				}

				$installed_plugins = get_plugins(); // Retrieve a list of all the plugins

				$message             = array(); // Store the messages in an array to be outputted after plugins have looped through.
				$install_link        = false;   // Set to false, change to true in loop if conditions exist, used for action link 'install'.
				$install_link_count  = 0; // Used to determine plurality of install action link text.
				$activate_link       = false;   // Set to false, change to true in loop if conditions exist, used for action link 'activate'.
				$activate_link_count = 0; // Used to determine plurality of activate action link text.

				$plugin = $this->plugin;

				// Not installed.
				if ( ! $this->is_plugin_installed( '/' . $this->plugin['slug'] ) ) {
					$install_link = true; // We need to display the 'install' action link.
					$install_link_count ++; // Increment the install link count.

					if ( current_user_can( 'install_plugins' ) ) {
						// This plugin is only recommended.
						$message['notice_can_install_recommended'][] = $plugin['name'];
					}
				} // Installed but not active.
				elseif ( ! is_plugin_active( $this->plugin['base_path'] ) ) {
					$activate_link = true; // We need to display the 'activate' action link.
					$activate_link_count ++; // Increment the activate link count.
					if ( current_user_can( 'activate_plugins' ) ) {
						$message['notice_can_activate_recommended'][] = $plugin['name'];
					}
				}

				// If we have notices to display, we move forward.
				if ( ! empty( $message ) ) {
					krsort( $message ); // Sort messages.
					$rendered = ''; // Display all nag messages as strings.
					// If dismissable is false and a message is set, output it now.
					if ( ! $this->dismissable && ! empty( $this->dismiss_msg ) ) {
						$rendered .= '<p><strong>' . wp_kses_post( $this->dismiss_msg ) . '</strong></p>';
					}

					// Grab all plugin names.
					foreach ( $message as $type => $plugin_groups ) {
						$linked_plugin_groups = array();

						// Count number of plugins in each message group to calculate singular/plural message.
						$count = count( $plugin_groups );

						$external_url = $this->plugin['external_url'];
						$source       = $this->plugin['source'];

						$last_plugin = array_pop( $plugin_groups );
						$imploded    = empty( $plugin_groups ) ? '<em>' . $last_plugin . '</em>' : '<em>' . ( implode( ', ', $plugin_groups ) . '</em> and <em>' . $last_plugin . '</em>' );

						$rendered .= '<p>' . isset( $this->strings[ $type ] ) ? $this->strings[ $type ] : '' . '</p>';
					}

					// Setup variables to determine if action links are needed.
					$show_install_link  = $install_link ? '<a href="' . add_query_arg( 'page', $this->menu, admin_url( 'admin.php' ) ) . '">' . $this->strings['install_link'] . '</a>' : '';
					$show_activate_link = $activate_link ? '<a href="' . add_query_arg( 'page', $this->menu, admin_url( 'admin.php' ) ) . '">' . $this->strings['activate_link'] . '</a>' : '';
					$show_install_link = esc_url( $show_install_link );
					$show_activate_link = esc_url( $show_activate_link );

					// Define all of the action links.
					$action_links = apply_filters(
						'cp_notice_action_links', array(
							'install'  => ( current_user_can( 'install_plugins' ) ) ? $show_install_link : '',
							'activate' => ( current_user_can( 'activate_plugins' ) ) ? $show_activate_link : '',
							'dismiss'  => $this->dismissable ? '<a class="dismiss-notice" href="' . esc_url( add_query_arg( 'cp-dismiss', 'dismiss_admin_notices' ) ) . '" target="_parent">' . $this->strings['dismiss'] . '</a>' : '',
						)
					);

					$action_links = array_filter( $action_links ); // Remove any empty array items.

					if ( $action_links ) {
						$rendered .= '<p>' . implode( ' | ', $action_links ) . '</p>';
					}

					// Register the nag messages and prepare them to be processed.
					$nag_class = version_compare( $this->wp_version, '3.8', '<' ) ? 'updated' : 'update-nag';
					if ( ! empty( $this->strings['nag_type'] ) ) {
						add_settings_error( 'cp', 'cp', $rendered, sanitize_html_class( strtolower( $this->strings['nag_type'] ) ) );
					} else {
						add_settings_error( 'cp', 'cp', $rendered, $nag_class );
					}
				}

				// Admin options pages already output settings_errors, so this is to avoid duplication.
				if ( 'options-general' !== $current_screen->parent_base ) {
					settings_errors( 'cp' );
				}
			}
		}

		/**
		 * Add dismissable admin notices.
		 *
		 * Appends a link to the admin nag messages. If clicked, the admin notice disappears and no longer is visible to users.
		 */
		public function dismiss() {
			if ( isset( $_GET['cp-dismiss'] ) ) {
				update_user_meta( get_current_user_id(), 'cp_plugin_installation_dismissed_notice', 1 );
			}
		}

		/**
		 * Amend default configuration settings.
		 *
		 * @param array $config Array of config options to pass as class properties.
		 */
		public function config( $config ) {

			$keys = array( 'default_path', 'has_notices', 'dismissable', 'dismiss_msg', 'menu', 'message', 'strings' );

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
		 *
		 * @return array                 Amended array of actions.
		 */
		public function actions( $install_actions ) {
			if ( $this->is_cp_plugin_installation_page() ) {
				return false;
			}

			return $install_actions;
		}

		protected function is_cp_plugin_installation_page() {
			if ( isset( $_GET['tab'] ) && isset( $_GET['page'] ) && $this->menu === $_GET['page'] ) {
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
			if ( ! isset( self::$instance ) && ! ( self::$instance instanceof CP_Plugin_Activation ) ) {
				self::$instance = new CP_Plugin_Activation();
			}

			return self::$instance;
		}

	}

}

global $cp_plugin_activation;
$cp_plugin_activation = new CP_Plugin_Activation();