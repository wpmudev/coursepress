<?php
class CoursePress_Admin_Settings extends CoursePress_Admin_Controller_Menu {
	var $parent_slug = 'coursepress_course';
	var $slug = 'coursepress_settings';
	protected $cap = 'coursepress_settings_cap';
	private static $tabs = array();
	private static $settings_classes = array(
		'General',
		'Email',
		'Capabilities',
		'BasicCertificate',
		'Shortcodes',
		'Extensions',
		'MarketPress',
		'WooCommerce',
		'Setup',
	);

	public function get_labels() {
		return array(
			'title' => __( 'CoursePress Settings', 'coursepress' ),
			'menu_title' => __( 'Settings', 'coursepress' )
		);
	}

	public function before_page_load() {
		// Init all the settings classes
		foreach ( self::$settings_classes as $page ) {
			$class = 'CoursePress_View_Admin_Setting_' . $page;

			if ( method_exists( $class, 'init' ) ) {
				call_user_func( $class . '::init' );
			}
		}

		parent::before_page_load();
	}

	public function get_tabs() {
		$slug = $this->slug;
		// Make it a filter so we can add more tabs easily
		$tabs = apply_filters( $slug . '_tabs', self::$tabs );

		// Make sure that we have all the fields we need
		foreach ( $tabs as $key => $tab ) {
			$tabs[ $key ]['url'] = add_query_arg( 'tab', $key );
			$tabs[ $key ]['buttons'] = isset( $tab['buttons'] ) ? $tab['buttons'] : 'both';
			$tabs[ $key ]['class'] = isset( $tab['class'] ) ? $tab['class'] : '';
			$tabs[ $key ]['is_form'] = isset( $tab['is_form'] ) ? $tab['is_form'] : true;
			$tabs[ $key ]['order'] = isset( $tab['order'] ) ? $tab['order'] : 999; // Set default order to 999... bottom of the list
		}

		// Order the tabs
		$tabs = CoursePress_Helper_Utility::sort_on_key( $tabs, 'order' );

		return $tabs;
	}

	public function process_form() {
		$tabs = $this->get_tabs();
		$tab_keys = array_keys( $tabs );
		$first_tab = ! empty( $tab_keys ) ? $tab_keys[0] : '';

		$tab = empty( $_GET['tab'] ) ? $first_tab : ( in_array( $_GET['tab'], $tab_keys ) ? sanitize_text_field( $_GET['tab'] ) : '' );

		$method = preg_replace( '/\_$/', '', 'process_' . $tab );

		do_action( $this->slug . '_' . $method, $this->slug, $tab );
	}

	public function render_page() {
		$labels = $this->get_labels();
		$tabs = self::get_tabs();
		$tab_keys = array_keys( $tabs );
		$first_tab = ! empty( $tab_keys ) ? $tab_keys[0] : '';

		$tab = empty( $_GET['tab'] ) ? $first_tab : ( in_array( $_GET['tab'], $tab_keys ) ? sanitize_text_field( $_GET['tab'] ) : '' );
		$content = '';

		$method = preg_replace( '/\_$/', '', 'render_tab_' . $tab );
		if ( method_exists( __CLASS__, $method ) ) {
			ob_start();
			call_user_func( __CLASS__ . '::' . $method );
			$content = ob_get_clean();
		}

		// Get the content using a filter if its not in this class
		$content = apply_filters( $this->slug . '_' . $method, $content, $this->slug, $tab );

		$hidden_args = $_GET;
		unset( $hidden_args['_wpnonce'] );

		$output = '<div class="coursepress_settings_wrapper wrap">';
		$output .= CoursePress_Helper_UI::get_admin_page_title( $labels['menu_title'] );
		$output .= CoursePress_Helper_Tabs::render_tabs( $tabs, $content, $hidden_args, $this->slug, $tab, false );
		$output .= '</div>';

		echo apply_filters( 'coursepress_settings_page_main', $output );

	}

	/**
	 * return slug.
	 *
	 * @since 2.0.0
	 *
	 * @return string slug
	 */
	public static function get_slug() {
		return self::$slug;
	}
}