<?php

class CoursePress_View_Admin_CoursePress {

	private static $slug = 'coursepress';

	public static function init() {

		add_filter( 'coursepress_admin_valid_pages', array( get_class(), 'add_valid' ) );
		add_action( 'coursepress_admin_' . self::$slug, array( get_class(), 'render_page' ) );

	}

	public static function add_valid( $valid_pages ) {
		$valid_pages[] = self::$slug;
		return $valid_pages;
	}

	public static function render_page() {

		$courseListTable = new CoursePress_Helper_Table_CourseList();
		$courseListTable->prepare_items();
		?>

		<div class="wrap">

                <div id="icon-users" class="icon32"></div>
                <h2>Example List Table Page</h2>
			<?php $courseListTable->display(); ?>
			<!--<form method="post">-->
		    <!--<input type="hidden" name="page" value="example_list_table" />-->
				<?php //$courseListTable->search_box('search', 'search_id'); ?>
		<!--</form>-->
            </div>
		<?php

	}

}