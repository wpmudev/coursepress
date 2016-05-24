<?php
global $action, $page;
wp_reset_vars( array( 'action', 'page' ) );

$page = sanitize_text_field( $_GET['page'] );

$tab = ( isset( $_GET['tab'] ) ) ? sanitize_text_field( $_GET['tab'] ) : '';
if ( empty( $tab ) ) {
	if ( current_user_can( 'manage_options' ) ) {
		$tab = 'general';
	} else {
		?>
		<div id="error-page">
			<p><?php _e( 'You do not have required permissions to access Settings.', 'cp' ); ?></p>
		</div>
		<?php
		exit;
		//die( __( 'You do not have required permissions to access Settings.', 'cp' ) );
	}
}

if ( isset( $_POST['_wpnonce'] ) && current_user_can( 'manage_options' ) ) {
	if ( wp_verify_nonce( $_REQUEST['_wpnonce'], 'update-coursepress-options' ) ) {
		foreach ( $_POST as $key => $value ) {
			if ( preg_match( "/option_/i", $key ) ) {//every field name with prefix "option_" will be saved as an option
				if ( $_POST[ $key ] != '' ) {
					update_option( str_replace( 'option_', '', $key ), $value );
				}
			}
		}

		do_action( 'coursepress_update_settings', $tab, $_POST );
	}

	if ( $tab == 'general' ) {
		if ( isset( $_POST['display_menu_items'] ) ) {
			update_option( 'display_menu_items', 1 );
		} else {
			update_option( 'display_menu_items', 0 );
		}

		if ( isset( $_POST['use_custom_login_form'] ) ) {
			update_option( 'use_custom_login_form', 1 );
		} else {
			update_option( 'use_custom_login_form', 0 );
		}

		if ( isset( $_POST['redirect_students_to_dashboard'] ) ) {
			update_option( 'redirect_students_to_dashboard', 1 );
		} else {
			update_option( 'redirect_students_to_dashboard', 0 );
		}

		if ( isset( $_POST['option_show_instructor_username'] ) ) {
			update_option( 'show_instructor_username', 1 );
		} else {
			update_option( 'show_instructor_username', 0 );
		}

		if ( isset( $_POST['option_show_tos'] ) ) {
			update_option( 'show_tos', 1 );
		} else {
			update_option( 'show_tos', 0 );
		}

		if ( isset( $_POST['show_messaging'] ) ) {
			update_option( 'show_messaging', 1 );
		} else {
			update_option( 'show_messaging', 0 );
		}

		// Conditional flush_rewrite_rules
		cp_flush_rewrite_rules();
	}
}
?>

<div class='wrap mp-wrap nocoursesub cp-wrap' id="settings-wrap">
	<div class="icon32 icon32-posts-page" id="icon-options-general"><br></div>
	<h2><?php _e( 'Settings', 'cp' ); ?></h2>

	<?php
	if ( isset( $_POST['submit'] ) ) {
		?>
		<div id="message" class="updated fade"><p><?php _e( 'Settings saved successfully.', 'cp' ); ?></p></div>
	<?php
	}
	?>


	<?php
	$menus = array();
	if ( current_user_can( 'manage_options' ) ) {
		$menus['general'] = __( 'General', 'cp' );
	}

	/* if ( current_user_can( 'manage_options' ) || current_user_can( 'coursepress_settings_groups_page_cap' ) ) {
	  //$menus['groups'] = __( 'Class Groups', 'cp' ); //to do in the next release
	  } */

	if ( current_user_can( 'manage_options' ) ) {
		/* $menus['payment'] = __( 'Payment Settings', 'cp' ); */
		$menus['email'] = __( 'E-mail Settings', 'cp' );
	}

	if ( current_user_can( 'manage_options' ) ) {
		$menus['instructor_capabilities'] = __( 'Instructor Capabilities', 'cp' );
	}

	if ( current_user_can( 'coursepress_settings_shortcode_page_cap' ) || current_user_can( 'manage_options' ) ) {
		$menus['shortcodes'] = __( 'Shortcodes', 'cp' );
	}

	if ( current_user_can( 'install_plugins' ) && current_user_can( 'activate_plugins' ) ) {
		$menus['cp-marketpress'] = __( 'MarketPress', 'cp' );
	}

	$menus = apply_filters( 'coursepress_settings_new_menus', $menus );
	?>

	<div id="undefined-sticky-wrapper" class="sticky-wrapper">
		<div class="sticky-slider visible-small visible-extra-small"><i class="fa fa-chevron-circle-right"></i></div>
		<ul class="mp-tabs" style="">
			<?php
			foreach ( $menus as $key => $menu ) {
				?>
				<li class="mp-tab <?php echo( $tab == $key ? 'mp-tab active' : '' ); ?>">
					<a class="mp-tab-link" href="<?php echo esc_attr( admin_url( 'admin.php?page=' . $page . '&amp;tab=' . $key ) ); ?>"><?php echo $menu; ?></a>
				</li>
			<?php
			}
			?>
			<li class="mp-tab">
				<a class="mp-tab-link" href="<?php echo admin_url( 'admin.php?page=courses&quick_setup' ); ?>"><?php _e( 'View Setup Guide', 'cp' ); ?></a>
			</li>
		</ul>
	</div>

	<div class='mp-settings'>
		<?php
		switch ( $tab ) {


			case 'general':
				if ( current_user_can( 'manage_options' ) ) {
					$this->show_settings_general();
				}
				break;


			/* case 'groups':
			  if ( current_user_can( 'manage_options' ) || current_user_can( 'coursepress_settings_groups_page_cap' ) ) {
			  $this->show_settings_groups();
			  }
			  break; */

			/* case 'payment':
			  if ( current_user_can( 'manage_options' ) ) {
			  $this->show_settings_payment();
			  }
			  break; */

			case 'shortcodes':
				if ( current_user_can( 'coursepress_settings_shortcode_page_cap' ) || current_user_can( 'manage_options' ) ) {
					$this->show_settings_shortcodes();
				}
				break;

			case 'instructor_capabilities':
				if ( current_user_can( 'manage_options' ) ) {
					//$this->add_user_roles_and_caps();
					$this->show_settings_instructor_capabilities();
				}
				break;

			case 'email':
				if ( current_user_can( 'manage_options' ) ) {
					$this->show_settings_email();
				}
				break;

			case 'cp-marketpress':
				//if ( current_user_can( 'manage_options' ) ) {
				$this->show_settings_marketpress();
				//}
				break;

			default:
				do_action( 'coursepress_settings_menu_' . $tab );
				break;
		}
		?>
	</div>

</div>
