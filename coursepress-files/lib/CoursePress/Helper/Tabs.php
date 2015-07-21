<?php

class CoursePress_Helper_Tabs {

	public static function render_tabs( $tabs, $tab_content, $hidden_args, $page, $active, $echo = true, $mode = 'vertical', $extra = '', $tab_arg = 'tab' ) {

		$mode_class = 'horizontal' === $mode ? '-horizontal' : '';
		$sticky_tabs = 'horizontal' === $mode ? false : true;
		$sticky_class = $sticky_tabs ? 'sticky-tabs' : '';

		$content = '';

		// Render the Tabs
		$content .= '<div class="tab-tabs' . $mode_class . '">';

		if( $sticky_tabs ) {
			$content .= '<div class="sticky-slider visible-small visible-extra-small"><i class="fa fa-chevron-circle-right"></i></div>';
		}

		$content .= '<ul class="mp-tabs ' . $sticky_class . '" style="">';
		$page = isset( $_GET['page'] ) ? sanitize_text_field( $_GET['page'] ) : '';

		foreach( $tabs as $key => $tab ) {
			$class = $key === $active ? 'active' : '';
			$class .= ' ' . $tab['class'];

			$query_string = 'page=' . $page;
			$first = true;
			foreach( $hidden_args as $arg_key => $arg_value ) {
				$query_string .= $first ? '' : '&amp;';
				$query_string .= $arg_key . '=' . $arg_value;
				$first = false;
			}
			$query_string .= '&amp;' . $tab_arg . '=' . $key;

			$tab_url = admin_url( 'admin.php?' . $query_string );

			$content .= '<li class="mp-tab ' . $class . '">
				<a class="mp-tab-link" href="' . esc_url( $tab_url ) . '">' . esc_html( $tab['title'] ) . '</a>
			</li>';
		}

		// Render extra tab box only for horizontal tabs
		if( 'horizontal' === $mode ) {
			$content .= '<li class="tab-extra">' . $extra . '</li>';
		}

		$content .= '</ul></div>';


		// Render the Content
		$content .= '<div class="tab-content tab-content-' . $mode . '">';
		if( $tabs[ $active ]['is_form'] === true ) {
			$content .= '<form method="post">';

			// Add hidden arguments
			if( ! empty( $hidden_args ) ) {
				foreach( $hidden_args as $arg_key => $arg_value ) {
					$content .= '<input type="hidden" name="' . $arg_key . '" value="' . $arg_value . '" />';
				}
			}
		}

		// Add top buttons
		if( 'both' === $tabs[ $active ]['buttons'] || 'top' === $tabs[ $active ]['buttons'] ) {
			$content .= '<p class="header-save-button">
				<input class="button-primary" type="submit" value="' . esc_attr__( 'Save Settings', CoursePress::TD ) . '" name="submit_settings_header">
			</p>';
		}

		// Add content header
		if( 'horizontal' !== $mode ) {
		$content .=	'<div class="header">
						<h3>' . esc_html( $tabs[ $active ]['title'] ) . '</h3>
						<p class="description">' . esc_html( $tabs[ $active ]['description'] ) . '</p>
					</div>';
		}

		// Wrap it all in a form if its a form
		//if( $tabs[ $active ]['is_form'] === true ) {
			//$content .= '<form method="post">' . $tab_content . '</form>';
		//} else {
			$content .= '<div class="body">' . $tab_content . '</div>';
		//}

		// Add bottom buttons
		if( 'both' === $tabs[ $active ]['buttons'] || 'bottom' === $tabs[ $active ]['buttons'] ) {
			$content .= '<hr /><p class="section-save-button">
				<input class="button-primary" type="submit" value="' . esc_attr__( 'Save Settings', CoursePress::TD ) . '" name="submit_settings_section">
			</p>';
		}

		// .tab-content
		$content .= '</div>';

		// </form>
		if( $tabs[ $active ]['is_form'] === true ) {
			$content .= '</form>';
		}

		// Wrap the content in a container
		$content = '<div class="tab-container">' . $content . '</div>';

		// Render Heading first for Horizontal display
		//if( 'horizontal' === $mode ) {
		//	$content =	'<div class="header">
		//				<h3>' . esc_html( $tabs[ $active ]['title'] ) . '</h3>
		//			</div>' . $content;
		//}

		if( $echo ) {
			echo apply_filters( 'coursepress_' . $page . '_tabs_content', $content, $tab_content );
		} else {
			return apply_filters( 'coursepress_' .$page . '_tabs_content', $content, $tab_content );
		}

	}



}