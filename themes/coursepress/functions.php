<?php
/**
 * CoursePress functions and definitions
 *
 * @package CoursePress
 */
add_theme_support( 'post-thumbnails' );

function author_description_excerpt( $user_id = false, $length = 100 ) {

	$excerpt = get_the_author_meta( 'description', $user_id );

	$excerpt		 = strip_shortcodes( $excerpt );
	$excerpt		 = apply_filters( 'the_content', $excerpt );
	$excerpt		 = str_replace( ']]>', ']]&gt;', $excerpt );
	$excerpt		 = strip_tags( $excerpt );
	$excerpt_length	 = apply_filters( 'excerpt_length', $length );
	$excerpt_more	 = apply_filters( 'excerpt_more', ' ' . '[...]' );

	$words = preg_split( "/[\n\r\t ]+/", $excerpt, $excerpt_length + 1, PREG_SPLIT_NO_EMPTY );
	if ( count( $words ) > $excerpt_length ) {
		array_pop( $words );
		$excerpt = implode( ' ', $words );
		$excerpt = $excerpt . $excerpt_more;
	} else {
		$excerpt = implode( ' ', $words );
	}

	return $excerpt;
}

/**
 * Coloroze first word of the widget title
 */
add_filter( 'widget_title', 'customize_widget_title' );

function customize_widget_title( $old_title ) {
	$title		 = explode( " ", $old_title, 2 );
	$titleNew	 = "<span class='yellow'>" . ( isset( $title[ 0 ] ) ? $title[ 0 ] : '' ) . "</span>" . ( isset( $title[ 1 ] ) ? $title[ 1 ] : '' );
	return $titleNew;
}

/**
 * Set the content width based on the theme's design and stylesheet.
 */
if ( !isset( $content_width ) ) {
	$content_width = 960; /* pixels */
}

if ( !function_exists( 'coursepress_setup' ) ) :

	/**
	 * Sets up theme defaults and registers support for various WordPress features.
	 *
	 * Note that this function is hooked into the after_setup_theme hook, which
	 * runs before the init hook. The init hook is too late for some features, such
	 * as indicating support for post thumbnails.
	 */
	function coursepress_setup() {

		/*
		 * Make theme available for translation.
		 * Translations can be filed in the /languages/ directory.
		 * If you're building a theme based on CoursePress, use a find and replace
		 * to change 'coursepress' to the name of your theme in all the template files
		 */
		load_theme_textdomain( 'cp', get_template_directory() . '/languages' );

		// Add default posts and comments RSS feed links to head.
		add_theme_support( 'automatic-feed-links' );

		/*
		 * Enable support for Post Thumbnails on posts and pages.
		 *
		 * @link http://codex.wordpress.org/Function_Reference/add_theme_support#Post_Thumbnails
		 */
		//add_theme_support( 'post-thumbnails' );
		// This theme uses wp_nav_menu() in one location.
		register_nav_menus( array(
			'primary' => __( 'Primary Menu', 'cp' ),
		) );

		register_nav_menus( array(
			'secondary' => __( 'Footer Menu', 'cp' ),
		) );

		// Enable support for Post Formats.
		//add_theme_support( 'post-formats', array( 'aside', 'image', 'video', 'quote', 'link' ) );
		// Setup the WordPress core custom background feature.
		add_theme_support( 'custom-background', apply_filters( 'coursepress_custom_background_args', array(
			'default-color'	 => 'f9f9f9',
			'default-image'	 => '',
		) ) );

		/* add_theme_support( 'custom-header', apply_filters( 'coursepress_custom_header_args', array(
		  'default-image' => get_template_directory_uri() . '/images/logo-default.png',
		  'uploads' => true,
		  'header-text' => true,
		  ) ) ); */
	}

endif; // coursepress_setup
add_action( 'after_setup_theme', 'coursepress_setup' );

/**
 * Register widgetized area and update sidebar with default widgets.
 */
function coursepress_widgets_init() {
	register_sidebar( array(
		'name'			 => __( 'Sidebar', 'cp' ),
		'id'			 => 'sidebar-1',
		'before_widget'	 => '<aside id="%1$s" class="widget %2$s">',
		'after_widget'	 => '</aside>',
		'before_title'	 => '<h1 class="widget-title">',
		'after_title'	 => '</h1>',
	) );

	register_sidebar( array(
		'name'			 => __( 'Footer', 'cp' ),
		'id'			 => 'sidebar-2',
		'before_widget'	 => '<aside id="%1$s" class="widget %2$s">',
		'after_widget'	 => '</aside>',
		'before_title'	 => '<h1 class="widget-title">',
		'after_title'	 => '</h1>',
	) );
}

add_action( 'widgets_init', 'coursepress_widgets_init' );

/**
 * Enqueue scripts and styles.
 */
function coursepress_scripts() {
	global $post;

	wp_enqueue_style( 'coursepress-style', get_stylesheet_uri() );

	wp_enqueue_style( 'coursepress-responsive-navigation', get_template_directory_uri() . '/css/responsive-nav.css' );

	wp_enqueue_script( 'coursepress-navigation', get_template_directory_uri() . '/js/navigation.js', array(), '20120206', true );

	wp_enqueue_script( 'coursepress-responsive-navigation', get_template_directory_uri() . '/js/responsive-nav.min.js', array(), '20120206', true );

	wp_enqueue_script( 'coursepress-general', get_template_directory_uri() . '/js/script.js', array(), '20120207', true );

	wp_enqueue_script( 'coursepress-skip-link-focus-fix', get_template_directory_uri() . '/js/skip-link-focus-fix.js', array(), '20130115', true );

	if ( is_singular() && comments_open() && get_option( 'thread_comments' ) ) {
		wp_enqueue_script( 'comment-reply' );
	}

	// Add FitVids to allow for responsive sizing of videos
	/* wp_register_script( 'fitvids', get_template_directory_uri() . '/js/jquery.fitvids.js', array( 'jquery' ), '20120207', true );
	  wp_enqueue_script( 'fitvids' ); */

	//if ( get_post_type( $post ) == 'unit' ) {
	//}

	wp_register_style( 'google_fonts_lato', '//fonts.googleapis.com/css?family=Lato:300,400' );
	wp_enqueue_style( 'google_fonts_lato' );

	wp_register_style( 'google_fonts_dosis', '//fonts.googleapis.com/css?family=Dosis:300,400' );
	wp_enqueue_style( 'google_fonts_dosis' );
}

add_action( 'wp_enqueue_scripts', 'coursepress_scripts' );

function load_all_jquery() {
	wp_enqueue_script( "jquery" );
	$jquery_ui = array(
		"jquery-ui-core",
		"jquery-ui-widget",
		"jquery-ui-mouse",
		"jquery-ui-accordion",
		//"jquery-ui-autocomplete",
		"jquery-ui-slider",
		"jquery-ui-tabs",
		"jquery-ui-sortable",
		"jquery-ui-draggable",
		"jquery-ui-droppable",
		"jquery-ui-selectable",
		"jquery-ui-position",
		"jquery-ui-datepicker",
		"jquery-ui-resizable",
		"jquery-ui-dialog",
		"jquery-ui-button"
	);
	foreach ( $jquery_ui as $script ) {
		wp_enqueue_script( $script );
	}
}

add_action( 'wp_enqueue_scripts', 'load_all_jquery' );

/**
 * Implement the Custom Header feature.
 */
//require get_template_directory() . '/inc/custom-header.php';

/**
 * Custom template tags for this theme.
 */
require get_template_directory() . '/inc/template-tags.php';

/**
 * Custom functions that act independently of the theme templates.
 */
require get_template_directory() . '/inc/extras.php';

/**
 * Customizer additions.
 */
require get_template_directory() . '/inc/customizer.php';

function author_description_excerptOLD( $user_id ) {
	$word_limit				 = 100; // Limit the number of words
	$txt_end				 = '...'; // Display text end 
	$authorDescription		 = get_the_author_meta( 'description', $user_id );
	$displayAuthorPageLink	 = count( $authorDescription ) > $word_limit ? $txt_end : '';
	$authorDescriptionShort	 = array_slice( $authorDescription, 0, ( $word_limit ) );
	return ( implode( $authorDescriptionShort, ' ' ) ) . $displayAuthorPageLink;
}

function cp_filter_search( $query ) {
	if ( $query->is_search ) {
		if ( !is_admin() ) {
			$query->set( 'post_type', array( 'post', 'course' ) );
		}
	}
	return $query;
}

add_filter( 'pre_get_posts', 'cp_filter_search' );


add_shortcode( 'contact_form', 'coursepress_contact_form' );

function coursepress_contact_form() {
	ob_start();
	get_template_part( 'part-contact' );
	$var = ob_get_contents();
	ob_end_clean();
	return $var;
}

//Walker for mobile menu
class Walker_Nav_Menu_Dropdown extends Walker_Nav_Menu {

	function start_lvl( &$output, $depth = 0, $args = Array() ) {
		
	}

	function end_lvl( &$output, $depth = 0, $args = Array() ) {
		
	}

	function start_el( &$output, $item, $depth = 0, $args = Array(), $current_object_id = 0 ) {
		// Here is where we create each option.
		$item_output = '';

		//$item = new StdClass;
		//$item->title = '';
		// add spacing to the title based on the depth
		$item->title = str_repeat( "&#160;", $depth * 4 ) . $item->title;

		// Get the attributes.. Though we likely don't need them for this...
		$attributes = !empty( $item->attr_title ) ? ' title="' . esc_attr( $item->attr_title ) . '"' : '';
		$attributes .=!empty( $item->target ) ? ' target="' . esc_attr( $item->target ) . '"' : '';
		$attributes .=!empty( $item->xfn ) ? ' rel="' . esc_attr( $item->xfn ) . '"' : '';
		$attributes .=!empty( $item->url ) ? ' href="' . esc_attr( $item->url ) . '"' : '';

		// Add the html
		$item_output .= '<li>';
		$item_output .= '<a href="' . $item->url . '">' . apply_filters( 'the_title_attribute', $item->title ) . '</a>';

		// Add this new item to the output string.
		$output .= $item_output;
	}

	function end_el( &$output, $object, $depth = 0, $args = Array() ) {
		// Close the item.
		$output .= "</li>\n";
	}

	/*
	  function start_el( &$output, $item, $depth = 0, $args = Array(), $current_object_id = 0 ) {
	  // Here is where we create each option.
	  $item_output = '';

	  //$item = new StdClass;
	  //$item->title = '';

	  // add spacing to the title based on the depth
	  $item->title = str_repeat( "&#160;", $depth * 4 ) . $item->title;

	  // Get the attributes.. Though we likely don't need them for this...
	  $attributes = !empty( $item->attr_title ) ? ' title="' . esc_attr( $item->attr_title ) . '"' : '';
	  $attributes .=!empty( $item->target ) ? ' target="' . esc_attr( $item->target ) . '"' : '';
	  $attributes .=!empty( $item->xfn ) ? ' rel="' . esc_attr( $item->xfn ) . '"' : '';
	  $attributes .=!empty( $item->url ) ? ' value="' . esc_attr( $item->url ) . '"' : '';

	  // Add the html
	  $item_output .= '<option' . $attributes . '>';
	  $item_output .= apply_filters( 'the_title_attribute', $item->title );

	  // Add this new item to the output string.
	  $output .= $item_output;
	  }

	  function end_el( &$output, $object, $depth = 0, $args = Array() ) {
	  // Close the item.
	  $output .= "</option>\n";
	  } */
}

add_action( 'wp_footer', 'dropdown_menu_scripts' );

function dropdown_menu_scripts() {
	?>
	<!--<script>
		 jQuery( document ).ready( function( $ ) {
			 $( "#drop-mobile-nav" ).change( function() {
				 document.location.href = $( this ).val();
			 } );
		 } );
	 </script>-->
	<?php
}

/* Add thickbox to all images on Unit Elements */

add_filter( 'element_content_filter', 'cp_theme_element_content_filter_add_thickbox', 12, 1 );

function cp_theme_element_content_filter_add_thickbox( $content ) {
	$rule = '#(<a\s[^>]*href)="([^"]+)".*<img#';
	$rule = str_replace(' ', '', $rule);
	return preg_replace_callback( $rule, "cp_theme_cp_callback_link", $content );
}

function cp_theme_cp_callback_link( $match ) {
	$new_url = str_replace( '../wp-content', WP_CONTENT_URL, $match[ 0 ] );
	$rule = '#(//([^\s]*)\.(jpg|gif|png))#';
	$rule = str_replace(' ', '', $rule);
	$output	 = preg_replace( $rule, '$1" class="thickbox', $new_url );
	return $output;
}

/* Add thickbox to all images on Unit Single pages */

add_filter( 'the_content', 'unit_content' );

function unit_content( $content ) {
	if ( get_post_type( $GLOBALS[ 'post' ]->ID ) == 'unit' ) {
		return cp_theme_element_content_filter_add_thickbox( $content );
	} else {
		return $content;
	}
}

/**
 * Numeric pagination
 */
if ( !function_exists( 'cp_numeric_posts_nav' ) ) {

	function cp_numeric_posts_nav( $navigation_id = '' ) {

		if ( is_singular() )
			return;

		global $wp_query, $paged;
		/** Stop execution if there's only 1 page */
		if ( $wp_query->max_num_pages <= 1 )
			return;

		$paged = get_query_var( 'paged' ) ? absint( get_query_var( 'paged' ) ) : 1;

		$max = intval( $wp_query->max_num_pages );

		/** 	Add current page to the array */
		if ( $paged >= 1 )
			$links[] = $paged;

		/** 	Add the pages around the current page to the array */
		if ( $paged >= 3 ) {
			$links[] = $paged - 1;
			$links[] = $paged - 2;
		}

		if ( ( $paged + 2 ) <= $max ) {
			$links[] = $paged + 2;
			$links[] = $paged + 1;
		}

		if ( $navigation_id != '' ) {
			$id = 'id="' . $navigation_id . '"';
		} else {
			$id = '';
		}

		echo '<div class="navigation" ' . $id . '><ul>' . "\n";

		/** 	Previous Post Link */
		if ( get_previous_posts_link() )
			printf( '<li>%s</li>' . "\n", get_previous_posts_link( '<span class="meta-nav">&larr;</span>' ) );

		/** 	Link to first page, plus ellipses if necessary */
		if ( !in_array( 1, $links ) ) {
			$class = 1 == $paged ? ' class="active"' : '';

			printf( '<li%s><a href="%s">%s</a></li>' . "\n", $class, esc_url( get_pagenum_link( 1 ) ), '1' );

			if ( !in_array( 2, $links ) )
				echo '<li>…</li>';
		}

		/** 	Link to current page, plus 2 pages in either direction if necessary */
		sort( $links );
		foreach ( (array) $links as $link ) {
			$class = $paged == $link ? ' class="active"' : '';
			printf( '<li%s><a href="%s">%s</a></li>' . "\n", $class, esc_url( get_pagenum_link( $link ) ), $link );
		}

		/** 	Link to last page, plus ellipses if necessary */
		if ( !in_array( $max, $links ) ) {
			if ( !in_array( $max - 1, $links ) )
				echo '<li>…</li>' . "\n";

			$class = $paged == $max ? ' class="active"' : '';
			printf( '<li%s><a href="%s">%s</a></li>' . "\n", $class, esc_url( get_pagenum_link( $max ) ), $max );
		}

		/** 	Next Post Link */
		if ( get_next_posts_link() )
			printf( '<li>%s</li>' . "\n", get_next_posts_link( '<span class="meta-nav">&rarr;</span>' ) );

		echo '</ul></div>' . "\n";
	}

}
