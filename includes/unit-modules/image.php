<?php

class image_module extends Unit_Module {

	var $order = 2;
	var $name = 'image_module';
	var $label = 'Image';
	var $description = '';
	const FRONT_SAVE = false;
	var $response_type = '';

	function __construct() {
		$this->on_create();
	}

	function image_module() {
		$this->__construct();
	}

	public static function front_main( $data ) {
		$data->name = __CLASS__;
		?>
		<div class="<?php echo $data->name; ?> front-single-module<?php echo( image_module::FRONT_SAVE == true ? '-save' : '' ); ?>">
			<?php if ( $data->post_title != '' && parent::display_title_on_front( $data ) ) { ?>
				<h2 class="module_title"><?php echo $data->post_title; ?></h2>
			<?php } ?>

			<?php
			echo cp_do_attachment_caption( $data );
			?>
		</div>
	<?php
	}

	function admin_main( $data ) {
		wp_enqueue_style( 'thickbox' );
		wp_enqueue_script( 'thickbox' );
		wp_enqueue_media();
		wp_enqueue_script( 'media-upload' );
		$supported_image_extensions = implode( ", ", cp_wp_get_image_extensions() );
		?>

		<div class="<?php if ( empty( $data ) ) { ?>draggable-<?php } ?>module-holder-<?php echo $this->name; ?> module-holder-title" <?php if (empty( $data )) { ?>style="display:none;"<?php } ?>>

			<h3 class="module-title sidebar-name <?php echo( ! empty( $data->active_module ) ? 'is_active_module' : '' ); ?>" data-panel="<?php echo( ! empty( $data->panel ) ? $data->panel : '' ); ?>" data-id="<?php echo( ! empty( $data->ID ) ? $data->ID : '' ); ?>">
				<span class="h3-label">
					<span class="h3-label-left"><?php echo( isset( $data->post_title ) && $data->post_title !== '' ? $data->post_title : __( 'Untitled', 'cp' ) ); ?></span>
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

				<input type="hidden" class="element_id" value="<?php echo esc_attr( isset( $data->ID ) ? $data->ID : '' ); ?>"/>

				<label class="bold-label"><?php
					_e( 'Element Title', 'cp' );
					$this->time_estimation( $data );
					?></label>
				<?php echo $this->element_title_description(); ?>
				<input type="text" class="element_title" name="<?php echo $this->name; ?>_title[]" value="<?php echo esc_attr( isset( $data->post_title ) ? $data->post_title : '' ); ?>"/>

				<?php echo $this->show_title_on_front_element( $data ); ?>

				<div class="editor_in_place" style="display:none;">

					<?php
					$editor_name    = $this->name . "_content[]";
					$editor_id      = ( esc_attr( isset( $data->ID ) ? 'editor_' . $data->ID : rand( 1, 9999 ) ) );
					$editor_content = htmlspecialchars_decode( ( isset( $data->post_content ) ? $data->post_content : '' ) );

					$args = array(
						"textarea_name" => $editor_name,
						"textarea_rows" => 5,
						"quicktags"     => true,
						"teeny"         => false,
						"editor_class"  => 'cp-editor cp-unit-element',
					);


					$args = apply_filters( 'coursepress_element_editor_args', $args, $editor_name, $editor_id );

					wp_editor( $editor_content, $editor_id, $args );
					?>
				</div>

				<div class="file_url_holder">
					<label><?php _e( 'Enter a URL or Browse for an image.', 'cp' ); ?>
						<input class="image_url" type="text" size="36" name="<?php echo $this->name; ?>_image_url[]" value="<?php echo esc_attr( ( isset( $data->image_url ) ? $data->image_url : '' ) ); ?>"/>
						<input class="attachment_id" type="hidden" size="36" name="<?php echo $this->name; ?>_attachment_id[]" value="<?php echo esc_attr( ( isset( $data->attachment_id ) ? $data->attachment_id : '0' ) ); ?>"/>
						<input class="image_url_button" type="button" value="<?php _e( 'Browse', 'cp' ); ?>"/>

						<div class="invalid_extension_message"><?php echo sprintf( __( 'Extension of the file is not valid. Please use one of the following: %s', 'cp' ), $supported_image_extensions ); ?></div>
					</label>
				</div>

				<?php echo $this->show_media_caption( $data ); ?>

				<?php
				parent::get_module_delete_link();
				?>
			</div>

		</div>

	<?php
	}

	function on_create() {
		$this->order       = apply_filters( 'coursepress_' . $this->name . '_order', $this->order );
		$this->description = __( 'Image, 100% width', 'cp' );
		$this->label       = __( 'Image', 'cp' );
		$this->save_module_data();
		parent::additional_module_actions();
	}

	function show_media_caption( $data ) {

		if ( empty( $data ) ) {
			$data = false;
		}
		?>
		<div class="caption-settings">
			<label class="show_media_caption">
				<input type="checkbox" name="<?php echo $this->name; ?>_show_media_caption[]" value="yes" <?php echo( ! empty( $data ) && isset( $data->show_media_caption ) && $data->show_media_caption == 'yes' ? 'checked' : ( empty( $data ) || ! isset( $data->show_media_caption ) ) ? 'checked' : '' ) ?> />
				<input type="hidden" name="<?php echo $this->name; ?>_show_caption_field[]" value="<?php echo( ! empty( $data ) && isset( $data->show_media_caption ) && $data->show_media_caption == 'yes' ? 'yes' : empty( $data ) ? 'yes' : 'no' ) ?>"/>
				<?php _e( 'Show Caption', 'cp' ); ?><br/>
				<span class="element_title_description"><?php _e( 'Show a caption for this image.', 'cp' ); ?></span>
			</label>

			<div class="caption-source <?php echo ( ! empty( $data ) && isset( $data->show_media_caption ) && $data->show_media_caption == 'yes' ) || empty( $data ) ? '' : 'hidden'; ?>">
				<?php
				$caption_source = ( ! empty( $data ) && isset( $data->caption_field ) ? $data->caption_field : 'media' );

				// Usually the module ID, but if we cant use the ID, we'll take a timestamp
				$unique = ! empty( $data ) ? $data->ID : time();
				?>
				<input type="radio" name="<?php echo $this->name . '_' . $unique . '_caption_source[]'; ?>" value="media" <?php checked( $caption_source, 'media', true ); ?>/> <?php _e( 'Media Caption', 'cp' ); ?>
				<span class="element_title_description">
					<?php
					$no_caption_text = __( 'Media has no caption.', 'cp' );
					$attachment_id   = false;
					if ( ! empty( $data ) ) {
						$attachment_id = ! empty( $data->attachment_id ) ? $data->attachment_id : false;
						// $attachment_id = cp_get_attachment_id_from_src($data->image_url);
					}

					if ( ! empty( $attachment_id ) ) {
						$attachment = get_post( $attachment_id );
						$caption    = $attachment->post_excerpt;
						if ( ! empty( $caption ) ) {
							echo '"' . $caption . '"';
						} else {
							echo $no_caption_text;
						}
					} else {
						echo $no_caption_text;
					}
					?>
				</span>
				<input type="radio" name="<?php echo $this->name . '_' . $unique . '_caption_source[]'; ?>" value="custom" <?php checked( $caption_source, 'custom', true ); ?>/> <?php _e( 'Custom Caption', 'cp' ); ?>
				<input type="hidden" name="<?php echo $this->name . '_caption_field[]'; ?>" value="<?php echo $caption_source; ?>"/>
				<input type="text" name="<?php echo $this->name . '_caption_custom_text[]'; ?>" value="<?php echo( ! empty( $data ) && isset( $data->caption_custom_text ) ? $data->caption_custom_text : '' ); ?>" placeholder="<?php echo( ! empty( $data ) && isset( $data->caption_custom_text ) ? '' : __( 'Please enter a custom caption here.', 'cp' ) ); ?>"/><br/><br/>
			</div>
		</div>
	<?php
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
					// $data->metas['show_media_caption'] = array();
					$data->post_type = 'module';

					if ( isset( $_POST[ $this->name . '_id' ] ) ) {

						foreach ( $_POST[ $this->name . '_id' ] as $key => $value ) {

							$data->ID                       = $_POST[ $this->name . '_id' ][ $key ];
							$data->unit_id                  = ( ( isset( $_POST['unit_id'] ) and ( isset( $_POST['unit'] ) && $_POST['unit'] != '' ) ) ? $_POST['unit_id'] : $last_inserted_unit_id );
							$data->title                    = $_POST[ $this->name . '_title' ][ $key ];
							$data->metas['module_order']    = $_POST[ $this->name . '_module_order' ][ $key ];
							$data->metas['module_page']     = $_POST[ $this->name . '_module_page' ][ $key ];
							$data->metas['image_url']       = $_POST[ $this->name . '_image_url' ][ $key ];
							$data->metas['attachment_id']   = $_POST[ $this->name . '_attachment_id' ][ $key ];
							$data->metas['time_estimation'] = $_POST[ $this->name . '_time_estimation' ][ $key ];

							$data->metas['show_title_on_front'] = $_POST[ $this->name . '_show_title_field' ][ $key ];
							$data->metas['show_media_caption']  = $_POST[ $this->name . '_show_caption_field' ][ $key ];
							$data->metas['caption_custom_text'] = $_POST[ $this->name . '_caption_custom_text' ][ $key ];
							$data->metas['caption_field']       = $_POST[ $this->name . '_caption_field' ][ $key ];

							parent::update_module( $data );
						}
					}
				}
			}
		}
	}

}

cp_register_module( 'image_module', 'image_module', 'output' );
?>