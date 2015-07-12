<?php

class CoursePress_Helper_Tabs {

	public static function render_tabs( $tabs, $tab_content, $page, $active, $echo = true ) {

		// Render the Tabs
		$content = '<div class="tab-tabs">
					<div class="sticky-slider visible-small visible-extra-small"><i class="fa fa-chevron-circle-right"></i></div>
					<ul class="mp-tabs sticky-tabs" style="">';
		$page = isset( $_GET['page'] ) ? sanitize_text_field( $_GET['page'] ) : '';

		foreach( $tabs as $key => $tab ) {
			$class = $key === $active ? 'active' : '';
			$class .= ' ' . $tab['class'];
			$content .= '<li class="mp-tab ' . $class . '">
				<a class="mp-tab-link" href="' . esc_url( admin_url( 'admin.php?page=' . $page . '&amp;tab=' . $key ) ) . '">' . esc_html( $tab['title'] ) . '</a>
			</li>';
		}
		$content .= '</ul></div>';


		// Render the Content
		$content .= '<div class="tab-content">';
		if( $tabs[ $active ]['is_form'] === true ) {
			$content .= '<form method="post">';
		}

		// Add top buttons
		if( 'both' === $tabs[ $active ]['buttons'] || 'top' === $tabs[ $active ]['buttons'] ) {
			$content .= '<p class="header-save-button">
				<input class="button-primary" type="submit" value="' . esc_attr__( 'Save Settings', CoursePress::TD ) . '" name="submit_settings_header">
			</p>';
		}

		// Add content header
		$content .=	'<div class="header">
						<h3>' . esc_html( $tabs[ $active ]['title'] ) . '</h3>
						<p class="description">' . esc_html( $tabs[ $active ]['description'] ) . '</p>
					</div>';

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

		if( $echo ) {
			echo apply_filters( 'coursepress_settings_tabs_content', $content, $tab_content );
		} else {
			return apply_filters( 'coursepress_settings_tabs_content', $content, $tab_content );
		}

	}




}