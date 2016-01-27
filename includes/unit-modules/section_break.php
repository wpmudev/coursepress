<?php

class section_break_module extends Unit_Module {

	var $order = 6;
	var $name = 'section_break_module';
	var $label = 'Section Break';
	var $description = '';
	const FRONT_SAVE = false;
	var $response_type = '';
	var $visible = false;

	function __construct() {
		$this->on_create();
	}

	function page_break_module() {
		$this->__construct();
	}

	public static function front_main( $data ) {
		$data->name = __CLASS__;
		?>
		<hr class="<?php echo $data->name; ?> front-single-module<?php echo( section_break_module::FRONT_SAVE == true ? '-save' : '' ); ?>"/>
	<?php
	}

	function admin_main( $data ) {
		?>

		<div class="<?php if ( empty( $data ) ) { ?>draggable-<?php } ?>module-holder-<?php echo $this->name; ?> module-holder-title" <?php if (empty( $data )) { ?>style="display:none;"<?php } ?>>

			<h3 class="module-title sidebar-name">
				<span class="h3-label">

					<span class="h3-label-left"><?php echo( isset( $data->post_title ) && $data->post_title !== '' ? $data->post_title : $this->label ); ?></span>
					<span class="page-break-dashed"></span>
					<span class="page-break-right-fix">...</span>
					<span class="h3-label-right"><?php echo $this->label; ?></span>
					<?php
					parent::get_module_move_link();
					?>
				</span>
			</h3>

			<div class="module-content">
				<input type="hidden" name="<?php echo $this->name; ?>_module_page[]" class="module_page" value="<?php echo( isset( $data->module_page ) ? $data->module_page : '' ); ?>"/>
				<input type="hidden" name="<?php echo $this->name; ?>_module_order[]" class="module_order" value="<?php echo( isset( $data->module_order ) ? $data->module_order : 999 ); ?>"/>
				<input type="hidden" name="module_type[]" value="<?php echo $this->name; ?>"/>
				<input type="hidden" name="<?php echo $this->name; ?>_id[]" class="unit_element_id" value="<?php echo esc_attr( isset( $data->ID ) ? $data->ID : '' ); ?>"/>
				<input type="hidden" name="<?php echo $this->name; ?>_title[]" value="<?php echo esc_attr( isset( $data->post_title ) ? $data->post_title : '' ); ?>"/>

				<input type="hidden" class="element_id" value="<?php echo esc_attr( isset( $data->ID ) ? $data->ID : '' ); ?>"/>

				<div class="editor_in_place" style="display:none;">

					<?php
					$editor_name    = $this->name . "_content[]";
					$editor_id      = ( esc_attr( isset( $data->ID ) ? 'editor_' . $data->ID : rand( 1, 9999 ) ) );
					$editor_content = htmlspecialchars_decode( ( isset( $data->post_content ) ? $data->post_content : '' ) );

					$args = array(
						"textarea_name" => $editor_name,
						"textarea_rows" => 5,
						"quicktags"     => true,
						"teeny"         => false
					);

					$args = apply_filters( 'coursepress_element_editor_args', $args, $editor_name, $editor_id );

					wp_editor( $editor_content, $editor_id, $args );
					?>
				</div>

				<?php
				parent::get_module_delete_link();
				?>
			</div>
		</div>

	<?php
	}

	function on_create() {
		$this->order       = apply_filters( 'coursepress_' . $this->name . '_order', $this->order );
		$this->description = __( 'Inserts section break ( <hr> element )', 'cp' );
		$this->label       = __( 'Section Break', 'cp' );
		$this->save_module_data();
		parent::additional_module_actions();
	}

	function save_module_data() {
		global $wpdb, $last_inserted_unit_id, $save_elements;

		if ( isset( $_POST['module_type'] ) && ( $save_elements == true ) ) {

			foreach ( array_keys( $_POST['module_type'] ) as $module_type => $module_value ) {

				if ( $module_value == $this->name ) {
					$data                       = new stdClass();
					$data->ID                   = '';
					$data->unit_id              = '';
					$data->title                = '';
					$data->excerpt              = '';
					$data->content              = '';
					$data->metas                = array();
					$data->metas['module_type'] = $this->name;
					$data->post_type            = 'module';

					if ( isset( $_POST[ $this->name . '_id' ] ) ) {
						foreach ( $_POST[ $this->name . '_id' ] as $key => $value ) {
							$data->ID                    = $_POST[ $this->name . '_id' ][ $key ];
							$data->unit_id               = ( ( isset( $_POST['unit_id'] ) and ( isset( $_POST['unit'] ) && $_POST['unit'] != '' ) ) ? $_POST['unit_id'] : $last_inserted_unit_id );
							$data->title                 = $_POST[ $this->name . '_title' ][ $key ];
							$data->content               = '';
							$data->metas['module_order'] = $_POST[ $this->name . '_module_order' ][ $key ];
							$data->metas['module_page']  = $_POST[ $this->name . '_module_page' ][ $key ];
							parent::update_module( $data );
						}
					}
				}
			}
		}
	}

}

cp_register_module( 'section_break_module', 'section_break_module', 'output' );
?>