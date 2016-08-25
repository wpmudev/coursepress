<?php

if ( ! class_exists( 'WP_List_Table' ) ) {
	require_once( ABSPATH . 'wp-admin/includes/class-wp-list-table.php' );
}

/**
 * Extends WP_List_Table, but overrides all output methods to avoid echoing
 */

class CoursePress_Helper_UI_ListTable extends WP_List_Table {

	/**
	 * Message to be displayed when there are no items
	 */
	public function no_items() {
		return __( 'No items found.', 'cp' );
	}

	/**
	 * Display the search box.
	 */
	public function search_box( $text, $input_id ) {
		if ( empty( $_REQUEST['s'] ) && ! $this->has_items() ) {
			return; }

		$input_id = $input_id . '-search-input';

		$content = '';

		if ( ! empty( $_REQUEST['orderby'] ) ) {
			$content .= '<input type="hidden" name="orderby" value="' . esc_attr( $_REQUEST['orderby'] ) . '" />'; }
		if ( ! empty( $_REQUEST['order'] ) ) {
			$content .= '<input type="hidden" name="order" value="' . esc_attr( $_REQUEST['order'] ) . '" />'; }
		if ( ! empty( $_REQUEST['post_mime_type'] ) ) {
			$content .= '<input type="hidden" name="post_mime_type" value="' . esc_attr( $_REQUEST['post_mime_type'] ) . '" />'; }
		if ( ! empty( $_REQUEST['detached'] ) ) {
			$content .= '<input type="hidden" name="detached" value="' . esc_attr( $_REQUEST['detached'] ) . '" />'; }

		$admin_search_query = isset( $_REQUEST['s'] ) ? esc_attr( wp_unslash( $_REQUEST['s'] ) ) : '';

		$content .= '
			<p class="search-box">
				<label class="screen-reader-text" for="' . esc_attr( $input_id ) . '">' . esc_html( $text ) . ':</label>
				<input type="search" id="' . esc_attr( $input_id ) . '" name="s" value="' . esc_attr( $admin_search_query ) . '" />
				' . get_submit_button( $text, 'button', '', false, array( 'id' => 'search-submit' ) ) . '
			</p>
		';

		return $content;
	}

	/**
	 * Display the list of views available on this table.
	 *
	 * @since 3.1.0
	 * @access public
	 */
	public function views() {
		$views = $this->get_views();

		$views = apply_filters( "views_{$this->screen->id}", $views );

		if ( empty( $views ) ) {
			return; }

		$content = '<ul class="subsubsub">';

		foreach ( $views as $class => $view ) {
			$views[ $class ] = "<li class='$class'>$view";
		}
		$content .= implode( ' |</li>', $views ) . '</li>';

		$content .= '</ul>';

		return $content;
	}

	/**
	 * Display the bulk actions dropdown.
	 */
	protected function bulk_actions( $which = '' ) {
		if ( is_null( $this->_actions ) ) {
			$no_new_actions = $this->_actions = $this->get_bulk_actions();
			$this->_actions = apply_filters( "bulk_actions-{$this->screen->id}", $this->_actions );
			$this->_actions = array_intersect_assoc( $this->_actions, $no_new_actions );
			$two = '';
		} else {
			$two = '2';
		}

		if ( empty( $this->_actions ) ) {
			return; }

		$content = '';

		$content .= "<label for='bulk-action-selector-" . esc_attr( $which ) . "' class='screen-reader-text'>" . __( 'Select bulk action', 'cp' ) . '</label>';
		$content .= "<select name='action$two' id='bulk-action-selector-" . esc_attr( $which ) . "'>";
		$content .= "<option value='-1' selected='selected'>" . __( 'Bulk Actions', 'cp' ) . '</option>';

		foreach ( $this->_actions as $name => $title ) {
			$class = 'edit' == $name ? ' class="hide-if-no-js"' : '';

			$content .= "<option value='$name'$class>$title</option>";
		}

		$content .= '</select>';

		$content .= get_submit_button( __( 'Apply', 'cp' ), 'action', '', false, array( 'id' => "doaction$two" ) );

		return $content;
	}

	/**
	 * Display a monthly dropdown for filtering items
	 */
	protected function months_dropdown( $post_type ) {
		global $wpdb, $wp_locale;

		if ( apply_filters( 'disable_months_dropdown', false, $post_type ) ) {
			return;
		}

		$months = $wpdb->get_results( $wpdb->prepare( "
			SELECT DISTINCT YEAR( post_date ) AS year, MONTH( post_date ) AS month
			FROM $wpdb->posts
			WHERE post_type = %s
			ORDER BY post_date DESC
		", $post_type ) );

		$months = apply_filters( 'months_dropdown_results', $months, $post_type );

		$month_count = count( $months );

		if ( ! $month_count || ( 1 == $month_count && 0 == $months[0]->month ) ) {
			return; }

		$m = isset( $_GET['m'] ) ? (int) $_GET['m'] : 0;

		$content = '
			<label for="filter-by-date" class="screen-reader-text">' . esc_html__( 'Filter by date', 'cp' ) . '</label>
			<select name="m" id="filter-by-date">
			<option ' . selected( $m, 0, false ) . ' value="0">'. esc_html__( 'All dates', 'cp' ) . '</option>
		';

		foreach ( $months as $arc_row ) {
			if ( 0 == $arc_row->year ) {
				continue; }

			$month = zeroise( $arc_row->month, 2 );
			$year = $arc_row->year;

			$content .= sprintf( "<option %s value='%s'>%s</option>",
				selected( $m, $year . $month, false ),
				esc_attr( $arc_row->year . $month ),
				/* translators: 1: month name, 2: 4-digit year */
				sprintf( __( '%1$s %2$d', 'cp' ), $wp_locale->get_month( $month ), $year )
			);
		}

		$content .= '
			</select>
		';

		return $content;
	}

	/**
	 * Display a view switcher
	 */
	protected function view_switcher( $current_mode ) {

		$content = '
			<input type="hidden" name="mode" value="' . esc_attr( $current_mode ) . '" />
			<div class="view-switch">
		';

		foreach ( $this->modes as $mode => $title ) {
			$classes = array( 'view-' . $mode );

			if ( $current_mode == $mode ) {
				$classes[] = 'current'; }

			$content .= sprintf(
				"<a href='%s' class='%s' id='view-switch-$mode'><span class='screen-reader-text'>%s</span></a>",
				esc_url( add_query_arg( 'mode', $mode ) ),
				implode( ' ', $classes ),
				$title
			);
		}

		$content .= '
			</div>
		';

		return $content;
	}

	/**
	 * Display a comment count bubble
	 */
	protected function comments_bubble( $post_id, $pending_comments ) {
		$pending_phrase = sprintf( __( '%s pending' ), number_format( $pending_comments ) );

		$content = '';

		if ( $pending_comments ) {
			$content .= '<strong>'; }

		$content .= "<a href='" . esc_url( add_query_arg( 'p', $post_id, admin_url( 'edit-comments.php' ) ) ) . "' title='" . esc_attr( $pending_phrase ) . "' class='post-com-count'><span class='comment-count'>" . number_format_i18n( get_comments_number() ) . '</span></a>';

		if ( $pending_comments ) {
			$content .= '</strong>'; }

		return $content;
	}


	/**
	 * Display the pagination.
	 */
	protected function pagination( $which ) {
		if ( empty( $this->_pagination_args ) ) {
			return;
		}

		$total_items = $this->_pagination_args['total_items'];
		$total_pages = $this->_pagination_args['total_pages'];
		$infinite_scroll = false;
		if ( isset( $this->_pagination_args['infinite_scroll'] ) ) {
			$infinite_scroll = $this->_pagination_args['infinite_scroll'];
		}

		$output = '<span class="displaying-num">' . sprintf( _n( '1 item', '%s items', $total_items ), number_format_i18n( $total_items ) ) . '</span>';

		$current = $this->get_pagenum();
		$current_url = set_url_scheme( 'http://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'] );
		$current_url = remove_query_arg( array( 'hotkeys_highlight_last', 'hotkeys_highlight_first' ), $current_url );
		$page_links = array();

		$disable_first = $disable_last = '';

		if ( 1 == $current ) {
			$disable_first = ' disabled';
		}
		if ( $current == $total_pages ) {
			$disable_last = ' disabled';
		}
		$page_links[] = sprintf( "<a class='%s' title='%s' href='%s'>%s</a>",
			'first-page' . $disable_first,
			esc_attr__( 'Go to the first page' ),
			esc_url( remove_query_arg( 'paged', $current_url ) ),
			'&laquo;'
		);

		$page_links[] = sprintf( "<a class='%s' title='%s' href='%s'>%s</a>",
			'prev-page' . $disable_first,
			esc_attr__( 'Go to the previous page' ),
			esc_url( add_query_arg( 'paged', max( 1, $current -1 ), $current_url ) ),
			'&lsaquo;'
		);

		if ( 'bottom' == $which ) {
			$html_current_page = $current;
		} else {
			$html_current_page = sprintf( "%s<input class='current-page' id='current-page-selector' title='%s' type='text' name='paged' value='%s' size='%d' />",
				'<label for="current-page-selector" class="screen-reader-text">' . __( 'Select Page' ) . '</label>',
				esc_attr__( 'Current page' ),
				$current,
				strlen( $total_pages )
			);
		}
		$html_total_pages = sprintf( "<span class='total-pages'>%s</span>", number_format_i18n( $total_pages ) );
		$page_links[] = '<span class="paging-input">' . sprintf( _x( '%1$s of %2$s', 'paging' ), $html_current_page, $html_total_pages ) . '</span>';

		$page_links[] = sprintf( "<a class='%s' title='%s' href='%s'>%s</a>",
			'next-page' . $disable_last,
			esc_attr__( 'Go to the next page' ),
			esc_url( add_query_arg( 'paged', min( $total_pages, $current + 1 ), $current_url ) ),
			'&rsaquo;'
		);

		$page_links[] = sprintf( "<a class='%s' title='%s' href='%s'>%s</a>",
			'last-page' . $disable_last,
			esc_attr__( 'Go to the last page' ),
			esc_url( add_query_arg( 'paged', $total_pages, $current_url ) ),
			'&raquo;'
		);

		$pagination_links_class = 'pagination-links';
		if ( ! empty( $infinite_scroll ) ) {
			$pagination_links_class = ' hide-if-js';
		}
		$output .= "<span class='$pagination_links_class'>" . join( '', $page_links ) . '</span>';

		if ( $total_pages ) {
			$page_class = $total_pages < 2 ? ' one-page' : '';
		} else {
			$page_class = ' no-pages';
		}
		$this->_pagination = "<div class='tablenav-pages{$page_class}'>$output</div>";

		return $this->_pagination;
	}

	/**
	 * Print column headers, accounting for hidden and sortable columns.
	 */
	public function print_column_headers( $with_id = true ) {
		list( $columns, $hidden, $sortable ) = $this->get_column_info();

		$current_url = set_url_scheme( 'http://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'] );
		$current_url = remove_query_arg( 'paged', $current_url );

		if ( isset( $_GET['orderby'] ) ) {
			$current_orderby = $_GET['orderby'];
		} else {
			$current_orderby = '';
		}

		if ( isset( $_GET['order'] ) && 'desc' == $_GET['order'] ) {
			$current_order = 'desc';
		} else {
			$current_order = 'asc';
		}

		if ( ! empty( $columns['cb'] ) ) {
			static $cb_counter = 1;
			$columns['cb'] = '<label class="screen-reader-text" for="cb-select-all-' . $cb_counter . '">' . __( 'Select All' ) . '</label>'
				. '<input id="cb-select-all-' . $cb_counter . '" type="checkbox" />';
			$cb_counter++;
		}

		$content = '';
		foreach ( $columns as $column_key => $column_display_name ) {
			$class = array( 'manage-column', "column-$column_key" );

			$style = '';
			if ( in_array( $column_key, $hidden ) ) {
				$style = 'display:none;';
			}

			$style = ' style="' . $style . '"';

			if ( 'cb' == $column_key ) {
				$class[] = 'check-column';
			} elseif ( in_array( $column_key, array( 'posts', 'comments', 'links' ) ) ) {
				$class[] = 'num';
			}

			if ( isset( $sortable[ $column_key ] ) ) {
				list( $orderby, $desc_first ) = $sortable[ $column_key ];

				if ( $current_orderby == $orderby ) {
					$order = 'asc' == $current_order ? 'desc' : 'asc';
					$class[] = 'sorted';
					$class[] = $current_order;
				} else {
					$order = $desc_first ? 'desc' : 'asc';
					$class[] = 'sortable';
					$class[] = $desc_first ? 'asc' : 'desc';
				}

				$column_display_name = '<a href="' . esc_url( add_query_arg( compact( 'orderby', 'order' ), $current_url ) ) . '"><span>' . $column_display_name . '</span><span class="sorting-indicator"></span></a>';
			}

			$id = $with_id ? "id='$column_key'" : '';

			if ( ! empty( $class ) ) {
				$class = "class='" . join( ' ', $class ) . "'"; }

			$content .= "<th scope='col' $id $class $style>$column_display_name</th>";
		}
		return $content;
	}

	/**
	 * Display the table
	 *
	 * @since 3.1.0
	 * @access public
	 */
	public function display() {
		$singular = $this->_args['singular'];

		$content = '';

		$singular_text = $singular ? ' data-wp-lists="list:' . $singular . '" ' : '';

		$content .= $this->display_tablenav( 'top' );
		$content .= '
			<table class="wp-list-table ' . esc_attr( implode( ' ', $this->get_table_classes() ) ) . '">
				<thead>
				<tr>
					' . $this->print_column_headers() . '
				</tr>
				</thead>

				<tbody id="the-list" ' . $singular_text . '>
				' . $this->display_rows_or_placeholder() .'
				</tbody>

				<tfoot>
				<tr>
					' . $this->print_column_headers( false ) . '
				</tr>
				</tfoot>

			</table>
		';

		$content .= $this->display_tablenav( 'bottom' );

		return $content;
	}

	/**
	 * Generate the table navigation above or below the table
	 */
	protected function display_tablenav( $which ) {
		$content = '';

		if ( 'top' == $which ) {
			$content .= wp_nonce_field( 'bulk-' . $this->_args['plural'], '_wpnonce', true , false ); }

		$content .= '
			<div class="tablenav ' . esc_attr( $which ) . '">
				<div class="alignleft actions bulkactions">
					' . $this->bulk_actions( $which ) . '
				</div>
				' . $this->extra_tablenav( $which ) .
				$this->pagination( $which ) . '
				<br class="clear" />
			</div>
		';

		return $content;
	}

	/**
	 * Generate the tbody element for the list table.
	 */
	public function display_rows_or_placeholder() {
		$x = '';
		if ( $this->has_items() ) {
			return $this->display_rows();
		} else {

			$content = '<tr class="no-items"><td class="colspanchange" colspan="' . $this->get_column_count() . '">';
			$content .= $this->no_items();
			$content .= '</td></tr>';

			return $content;
		}
	}

	/**
	 * Generate the table rows
	 */
	public function display_rows() {

		$content = '';
		foreach ( $this->items as $item ) {
			$content .= $this->single_row( $item );
		}

		return $content;
	}

	/**
	 * Generates content for a single row of the table
	 */
	public function single_row( $item ) {

		$content = '<tr>';
		$content .= $this->single_row_columns( $item );
		$content .= '</tr>';

		return $content;
	}

	/**
	 * Generates the columns for a single row of the table
	 */
	protected function single_row_columns( $item ) {
		list( $columns, $hidden ) = $this->get_column_info();

		$content = '';

		foreach ( $columns as $column_name => $column_display_name ) {
			$class = "class='$column_name column-$column_name'";

			$style = '';
			if ( in_array( $column_name, $hidden ) ) {
				$style = ' style="display:none;"'; }

			$attributes = "$class$style";

			if ( 'cb' == $column_name ) {
				$content .= '<th scope="row" class="check-column">';
				$content .= $this->column_cb( $item );
				$content .= '</th>';
			} elseif ( method_exists( $this, 'column_' . $column_name ) ) {
				$content .= "<td $attributes>";
				$content .= call_user_func( array( $this, 'column_' . $column_name ), $item );
				$content .= '</td>';
			} else {
				$content .= "<td $attributes>";
				$content .= $this->column_default( $item, $column_name );
				$content .= '</td>';
			}
		}

		return $content;
	}

	/**
	 * Handle an incoming ajax request (called from admin-ajax.php)
	 */
	public function ajax_response() {
		$this->prepare_items();

		$rows = '';
		if ( ! empty( $_REQUEST['no_placeholder'] ) ) {
			$rows = $this->display_rows();
		} else {
			$rows = $this->display_rows_or_placeholder();
		}

		$response = array( 'rows' => $rows );

		if ( isset( $this->_pagination_args['total_items'] ) ) {
			$response['total_items_i18n'] = sprintf(
				_n( '1 item', '%s items', $this->_pagination_args['total_items'] ),
				number_format_i18n( $this->_pagination_args['total_items'] )
			);
		}
		if ( isset( $this->_pagination_args['total_pages'] ) ) {
			$response['total_pages'] = $this->_pagination_args['total_pages'];
			$response['total_pages_i18n'] = number_format_i18n( $this->_pagination_args['total_pages'] );
		}

		die( wp_json_encode( $response ) );
	}
}
