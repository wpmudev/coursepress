<?php
/**
 * CoursePress helper class.
 *
 * @package CoursePress
 */

/**
 * Handles navigation elements for paginated lists.
 */
class CoursePress_Helper_Pagination {

	/**
	 * Total number of items in list.
	 * @var int
	 */
	protected $total_items = -1;

	/**
	 * Items per page.
	 * @var int
	 */
	protected $limit = 10;

	/**
	 * Pagination title (i.e. what is displayed in list)
	 * @var string
	 */
	protected $title_single = 'item';

	/**
	 * Pagination title (i.e. what is displayed in list)
	 * @var string
	 */
	protected $title_plural = 'items';

	/**
	 * Target-URL for the navigation links.
	 *
	 * @var string
	 */
	protected $target_url = '';

	/**
	 * URL parameter to append to the target-URL. This parameter will pass the
	 * new page-number to the target-URL page.
	 * @var string
	 */
	protected $parameter_name = 'page';

	/**
	 * Current page that is open.
	 * @var int
	 */
	protected $page = 1;

	/**
	 * Display page-count value at the end of pagination links.
	 * @var bool
	 */
	protected $show_page_counter = false;

	/**
	 * CSS class of the outermost pagination element.
	 * @var string
	 */
	protected $class_name = 'pagination-links';

	/**
	 * HTML code for navigation elements.
	 * This value is prepared by calculate() function.
	 * @var string
	 */
	protected $pagination = '';


	public function items( $value ) {
		$this->total_items = (int) $value;
	}

	public function limit( $value ) {
		$this->limit = (int) $value;
	}

	public function target_url( $value ) {
		$this->target_url = $value;
	}

	public function current_page( $value ) {
		$this->page = (int) $value;
	}

	public function show_counter( $value = true ) {
		$this->show_counter = cp_is_true( $value );
	}

	public function change_class( $value ) {
		$this->class_name = $value;
	}

	public function parameter_name( $value ) {
		$this->parameter_name = $value;
	}

	public function title( $single, $plural ) {
		$this->title_single = $single;
		$this->title_plural = $plural;
	}


	public function show() {
		if ( $this->calculate() ) {
			if ( 1 == $this->total_items ) {
				$title = $this->title_single;
			} else {
				$title = $this->title_plural;
			}

			printf(
				'<span class="displaying-num">%d %s</span>',
				(int) $this->total_items,
				esc_html( $title )
			);
			echo $this->get_output();
		}
	}

	public function get_output() {
		if ( $this->calculate() ) {
			return '<span class="' . esc_attr( $this->class_name ) . '">' . $this->pagination . '</span>';
		}
	}

	public function get_pagenum_link( $id ) {
		return sprintf(
			'%s?%s=%s',
			esc_url( $this->target_url ),
			esc_attr( $this->parameter_name ),
			esc_attr( $id )
		);
	}

	protected function calculate() {
		$this->total_items = (int) $this->total_items;

		if ( $this->pagination ) {
			return true;
		}

		$this->pagination = '';
		$error = false;

		if ( $this->total_items < 0 ) {
			$error = true;
		}

		if ( empty( $this->limit ) ) {
			$error = true;
		}

		if ( $error ) { return false; }

		// Setup page vars for display.
		$lastpage = ceil( $this->total_items / $this->limit );
		$prev = $this->page - 1;
		$next = $this->page + 1;

		if ( $lastpage > 1 ) {
			if ( $this->page ) {
				// Anterior button.
				if ( $this->page > 1 ) {
					$this->pagination .= '<a href="' . $this->get_pagenum_link( 1 ) . '" class="first-page">&laquo;</a>&nbsp;<a href="' . $this->get_pagenum_link( $prev ) . '" class="prev-page">&lsaquo;</a>&nbsp;';
				} else {
					$this->pagination .= '<a class="first-page disabled">&laquo;</a>&nbsp;<a class="prev-page disabled">&lsaquo;</a>&nbsp;';
				}
			}

			// Pages.
			/*
			for ( $counter = 1; $counter <= $lastpage; $counter ++ ) {
				if ( $counter == $this->page ) {
					$this->pagination .= '<span class="current-page">' . $counter . '</span>';
				} else {
					$this->pagination .= '<a href="' . $this->get_pagenum_link( $counter ) . '">' . $counter . '</a>';
				}
			}
			*/

			$this->pagination .= '&nbsp;<span class="paging-input">' .
				sprintf(
					__( '%s of %s', 'coursepress' ),
					$this->page,
					'<span class="total-pages">' . $lastpage . '</span>'
				) . '</span>&nbsp;';

			if ( $this->page ) {
				if ( $this->page < $counter - 1 ) {
					$this->pagination .= '&nbsp;<a href="' . $this->get_pagenum_link( $next ) . '" class="next-page">&rsaquo;</a>&nbsp;<a href="' . $this->get_pagenum_link( $lastpage ) . '" class="last-page">&raquo;</a>';
				} else {
					$this->pagination .= '&nbsp;<a class="next-page disabled">&rsaquo;</a>&nbsp;<a class="last-page disabled">&raquo;</a>';
				}

				if ( $this->show_page_counter ) {
					$this->pagination .= '<div class="pagination_data">' .
						sprintf(
							__( '(%s Pages)', 'coursepress' ),
							$this->total_items
						) .
						'</div>';
				}
			}
		}

		return true;
	}
}
