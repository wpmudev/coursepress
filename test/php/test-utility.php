<?php
/**
 * Unit tests.
 *
 * @package CoursePress
 */

/**
 * Test cases for Utility functions (Helper).
 */
class CoursePress_Utilty_Tests extends CoursePress_UnitTestCase {

	/**
	 * Assert that the utility `cp_is_true` works correctly.
	 */
	function test_cp_is_true() {
		$test_values = array(
			0 => false,
			'0' => false,
			1 => true,
			'1' => true,
			'true' => true,
			'false' => false,
			'' => false,
			'foo' => false,
			-1 => true,
			15 => true,
			'on' => true,
			'off' => false,
			'yes' => true,
			'no' => false,
			'YES' => true,
			'Yes' => true,
			'NO' => false,
		);
		foreach ( $test_values as $value => $expected ) {
			$res = cp_is_true( $value );
			$this->assertEquals( $res, $expected );
		}

		$this->assertTrue( cp_is_true( true ) );
		$this->assertfalse( cp_is_true( false ) );
		$this->assertfalse( cp_is_true( null ) );
		$this->assertfalse( cp_is_true( array() ) );
		$this->assertfalse( cp_is_true( array( 1 ) ) );
		$this->assertfalse( cp_is_true( array( 'no' ) ) );
		$this->assertfalse( cp_is_true( array( 'yes' ) ) );
	}

	function test__enable_extended_upload__new_mime_types_added_for_plugin_pages()
	{
		$this->change_referrer('http://test-url.com/?page=coursepress_settings');
		$this->assertEquals(
			array(
				'gz'  => 'application/x-gzip',
				'zip' => 'application/zip'
			),
			\CoursePress_Helper_Utility::enable_extended_upload()
		);
	}

	function test__enable_extended_upload__new_mime_types_not_added_for_irrelevant_pages()
	{
		$this->change_referrer('http://test-url.com/?page=irrelevant_page');
		$this->assertEmpty(
			\CoursePress_Helper_Utility::enable_extended_upload()
		);
	}

	function test__sort_on_key__sorts_array_in_ascending_order()
	{
		$this->assertEquals(
			array(
				0 => array('key' => 'A'),
				2 => array('key' => 'brown'),
				8 => array('key' => 'dog'),
				3 => array('key' => 'fox'),
				4 => array('key' => 'jumped'),
				7 => array('key' => 'lazy'),
				5 => array('key' => 'over'),
				1 => array('key' => 'quick'),
				6 => array('key' => 'the'),
			),
			\CoursePress_Helper_Utility::sort_on_key(
				array(
					array('key' => 'A'),
					array('key' => 'quick'),
					array('key' => 'brown'),
					array('key' => 'fox'),
					array('key' => 'jumped'),
					array('key' => 'over'),
					array('key' => 'the'),
					array('key' => 'lazy'),
					array('key' => 'dog'),
				),
				'key'
			)
		);
	}

	function test__sort_on_key__sorts_array_in_descending_order()
	{
		$this->assertEquals(
			array(
				6 => array('key' => 'the'),
				1 => array('key' => 'quick'),
				5 => array('key' => 'over'),
				7 => array('key' => 'lazy'),
				4 => array('key' => 'jumped'),
				3 => array('key' => 'fox'),
				8 => array('key' => 'dog'),
				2 => array('key' => 'brown'),
				0 => array('key' => 'A'),
			),
			\CoursePress_Helper_Utility::sort_on_key(
				array(
					array('key' => 'A'),
					array('key' => 'quick'),
					array('key' => 'brown'),
					array('key' => 'fox'),
					array('key' => 'jumped'),
					array('key' => 'over'),
					array('key' => 'the'),
					array('key' => 'lazy'),
					array('key' => 'dog'),
				),
				'key',
				false
			)
		);
	}

	function test__sort_on_object_key__sorts_objects_in_ascending_order()
	{
		$this->assertEquals(
			array(
				0 => (object) array('key' => 'A'),
				2 => (object) array('key' => 'brown'),
				8 => (object) array('key' => 'dog'),
				3 => (object) array('key' => 'fox'),
				4 => (object) array('key' => 'jumped'),
				7 => (object) array('key' => 'lazy'),
				5 => (object) array('key' => 'over'),
				1 => (object) array('key' => 'quick'),
				6 => (object) array('key' => 'the'),
			),
			\CoursePress_Helper_Utility::sort_on_object_key(
				array(
					(object) array('key' => 'A'),
					(object) array('key' => 'quick'),
					(object) array('key' => 'brown'),
					(object) array('key' => 'fox'),
					(object) array('key' => 'jumped'),
					(object) array('key' => 'over'),
					(object) array('key' => 'the'),
					(object) array('key' => 'lazy'),
					(object) array('key' => 'dog'),
				),
				'key'
			)
		);
	}

	function test__sort_on_object_key__sorts_objects_in_descending_order()
	{
		$this->assertEquals(
			array(
				6 => (object) array('key' => 'the'),
				1 => (object) array('key' => 'quick'),
				5 => (object) array('key' => 'over'),
				7 => (object) array('key' => 'lazy'),
				4 => (object) array('key' => 'jumped'),
				3 => (object) array('key' => 'fox'),
				8 => (object) array('key' => 'dog'),
				2 => (object) array('key' => 'brown'),
				0 => (object) array('key' => 'A'),
			),
			\CoursePress_Helper_Utility::sort_on_object_key(
				array(
					(object) array('key' => 'A'),
					(object) array('key' => 'quick'),
					(object) array('key' => 'brown'),
					(object) array('key' => 'fox'),
					(object) array('key' => 'jumped'),
					(object) array('key' => 'over'),
					(object) array('key' => 'the'),
					(object) array('key' => 'lazy'),
					(object) array('key' => 'dog'),
				),
				'key',
				false
			)
		);
	}

	function test__set_array_value__value_set_when_path_empty()
	{
		$this->assertEquals(
			array(
				'a' => array('creatively'),
				'b' => 'created',
				'c' => 'array',
				3   => 'new value'
			),
			\CoursePress_Helper_Utility::set_array_value(
				array(
					'a' => array('creatively'),
					'b' => 'created',
					'c' => 'array'
				),
				'',
				'new value'
			)
		);
	}

	function test__set_array_value__value_set_when_path_contains_single_key()
	{
		$this->assertEquals(
			array(
				'a' => array('creatively'),
				'b' => 'created',
				'c' => 'array',
				'd' => 'new value'
			),
			\CoursePress_Helper_Utility::set_array_value(
				array(
					'a' => array('creatively'),
					'b' => 'created',
					'c' => 'array'
				),
				'd',
				'new value'
			)
		);
	}

	function test__set_array_value__value_set_when_path_contains_multiple_keys()
	{
		$this->assertEquals(
			array(
				'a' => array(
					'creatively',
					'd' => array(
						'e' => 'new value'
					)
				),
				'b' => 'created',
				'c' => 'array'
			),
			\CoursePress_Helper_Utility::set_array_value(
				array(
					'a' => array('creatively'),
					'b' => 'created',
					'c' => 'array'
				),
				'a/d/e',
				'new value'
			)
		);
	}

	function test__set_array_value__value_set_when_path_is_array()
	{
		$this->assertEquals(
			array(
				'a' => array(
					'creatively',
					'd' => array(
						'e' => 'new value'
					)
				),
				'b' => 'created',
				'c' => 'array'
			),
			\CoursePress_Helper_Utility::set_array_value(
				array(
					'a' => array('creatively'),
					'b' => 'created',
					'c' => 'array'
				),
				array('a', 'd', 'e'),
				'new value'
			)
		);
	}

	function test__unset_array_value__value_unset_when_path_contains_single_key()
	{
		$this->assertEquals(
			array(
				'a' => array('creatively'),
				'b' => 'created'
			),
			\CoursePress_Helper_Utility::unset_array_value(
				array(
					'a' => array('creatively'),
					'b' => 'created',
					'c' => 'array'
				),
				'c'
			)
		);
	}

	function test__unset_array_value__value_unset_when_path_contains_multiple_keys()
	{
		$this->assertEquals(
			array(
				'a' => array(
					'creatively',
					'd' => array()
				),
				'b' => 'created',
				'c' => 'array'
			),
			\CoursePress_Helper_Utility::unset_array_value(
				array(
					'a' => array(
						'creatively',
						'd' => array(
							'e' => 'deep nested value'
						)
					),
					'b' => 'created',
					'c' => 'array'
				),
				'a/d/e'
			)
		);
	}

	function test__unset_array_value__value_unset_when_path_contains_array()
	{
		$this->assertEquals(
			array(
				'a' => array(
					'creatively',
					'd' => array()
				),
				'b' => 'created',
				'c' => 'array'
			),
			\CoursePress_Helper_Utility::unset_array_value(
				array(
					'a' => array(
						'creatively',
						'd' => array(
							'e' => 'deep nested value'
						)
					),
					'b' => 'created',
					'c' => 'array'
				),
				array('a', 'd', 'e')
			)
		);
	}

	function test__object_to_array__stdClass_object_converted_to_array()
	{
		$this->assertEquals(
			array(
				'a' => array(
					'creatively',
					'd' => array(
						'e' => 'deep nested value'
					)
				),
				'b' => 'created',
				'c' => 'array'
			),
			\CoursePress_Helper_Utility::object_to_array(
				(object) array(
					'a' => array(
						'creatively',
						'd' => (object) array(
							'e' => 'deep nested value'
						)
					),
					'b' => 'created',
					'c' => 'array'
				)
			)
		);
	}

	function test__object_to_array__custom_object_converted_to_array()
	{
		$person = get_dummy_person_object();

		$this->assertEquals(
			array(
				'name'           => 'John Doe',
				'designation'    => 'Creative Director',
				'interests'      => array('swimming', 'writing'),
				'related_people' => array(
					array(
						'name'           => 'Joshua Doe',
						'designation'    => 'Kid',
						'interests'      => array('crawling', 'crying'),
						'related_people' => array()
					)
				)
			),
			\CoursePress_Helper_Utility::object_to_array($person)
		);
	}

	function test__array_to_object__array_converted_to_stdClass_object()
	{
		$this->assertEquals(
			(object) array(
				'a' => (object) array(
					'creatively',
					'd' => (object) array(
						'e' => 'deep nested value'
					)
				),
				'b' => 'created',
				'c' => 'array'
			),
			\CoursePress_Helper_Utility::array_to_object(
				array(
					'a' => array(
						'creatively',
						'd' => array(
							'e' => 'deep nested value'
						)
					),
					'b' => 'created',
					'c' => 'array'
				)
			)
		);
	}

	function test__merge_distinct__merges_arrays()
	{
		/* TODO: Understand what the function does and create test */
	}

	function test__delete_user_meta_by_key__deletes_meta_with_prefix()
	{
		$legacy_meta_key = $this->legacy_meta_key('meta_to_delete');

		$this->insert_user_meta($this->student->ID, $legacy_meta_key, 'I am a student!');
		$this->insert_user_meta($this->instructor->ID, $legacy_meta_key, 'And I am an instructor!');

		\CoursePress_Helper_Utility::delete_user_meta_by_key('meta_to_delete');

		$this->assertEquals(
			array(),
			get_user_meta($this->student->ID, $legacy_meta_key)
		);

		$this->assertEquals(
			array(),
			get_user_meta($this->instructor->ID, $legacy_meta_key)
		);
	}

	function legacy_meta_key($normal_key)
	{
		global $wpdb;
		return $wpdb->prefix . $normal_key;
	}

	function test__delete_user_meta_by_key__deletes_normal_meta()
	{
		$this->insert_user_meta($this->student->ID, 'meta_to_delete', 'I am a student!');
		$this->insert_user_meta($this->instructor->ID, 'meta_to_delete', 'And I am an instructor!');

		\CoursePress_Helper_Utility::delete_user_meta_by_key('meta_to_delete');

		$this->assertEquals(
			array(),
			get_user_meta($this->student->ID, 'meta_to_delete')
		);

		$this->assertEquals(
			array(),
			get_user_meta($this->instructor->ID, 'meta_to_delete')
		);
	}

	function test__get_id__returns_id_when_user_object_passed()
	{
		$this->assertEquals(
			$this->student->ID,
			\CoursePress_Helper_Utility::get_id($this->student)
		);
	}

	function test__get_id__returns_id_when_id_passed()
	{
		$this->assertEquals(
			$this->student->ID,
			\CoursePress_Helper_Utility::get_id($this->student->ID)
		);
	}

	function test__sanitize_recursive__sanitizes_string_value()
	{
		$this->assertEquals(
			'alert(1); The post <strong>content</strong>. <p>Here is an embedded video: Inside iFrame.</p>',
			\CoursePress_Helper_Utility::sanitize_recursive(
				'<script type="application/javascript">alert(1);</script> The post <strong>content</strong>. <p>Here is an embedded video: <iframe>Inside iFrame</iframe>.</p>'
			)
		);
	}

	function test__sanitize_recursive__sanitizes_nested_array()
	{
		$this->assertEquals(
			array(
				'a' => array(
					'very' => array(
						'nested' => array(
							'value' => 'alert(1); The post <strong>content</strong>. <p>Here is an embedded video: Inside iFrame.</p>'
						)
					)
				)
			),
			\CoursePress_Helper_Utility::sanitize_recursive(
				array(
					'a' => array(
						'very' => array(
							'nested' => array(
								'value' => '<script type="application/javascript">alert(1);</script> The post <strong>content</strong>. <p>Here is an embedded video: <iframe>Inside iFrame</iframe>.</p>'
							)
						)
					)
				)
			)
		);
	}

	function test__checked__attribute_added_when_compare_argument_not_passed()
	{
		$this->assertEquals(
			'checked="checked"',
			\CoursePress_Helper_Utility::checked('on')
		);

		$this->assertEquals(
			'',
			\CoursePress_Helper_Utility::checked('off')
		);

		$this->assertEquals(
			'checked="checked"',
			\CoursePress_Helper_Utility::checked(true)
		);

		$this->assertEquals(
			'',
			\CoursePress_Helper_Utility::checked(false)
		);
	}

	function test__checked__attribute_added_when_compare_argument_passed()
	{
		$this->assertEquals(
			'checked="checked"',
			\CoursePress_Helper_Utility::checked('on', 'on')
		);

		$this->assertEquals(
			'',
			\CoursePress_Helper_Utility::checked('off', 'on')
		);

		$this->assertEquals(
			'checked="checked"',
			\CoursePress_Helper_Utility::checked(true, true)
		);

		$this->assertEquals(
			'',
			\CoursePress_Helper_Utility::checked(false, true)
		);
	}

	function test__get_ajax_url__returns_correct_ajax_url()
	{
		$this->assertContains(
			'wp-admin/admin-ajax.php',
			\CoursePress_Helper_Utility::get_ajax_url()
		);
	}

	function test__get_current_url()
	{
		/* TODO: Write tests */
	}

	function test__get_image_extensions__returns_image_extensions()
	{
		$this->assertEquals(
			array(
				'jpg',
				'jpeg',
				'jpe',
				'gif',
				'png',
				'bmp',
				'tif',
				'tiff',
				'ico',
			),
			\CoursePress_Helper_Utility::get_image_extensions()
		);
	}

	function test__get_image_extensions__image_extensions_can_be_filtered()
	{
		add_filter('coursepress_allowed_image_extensions', function(){
			return array('jpeg');
		});

		$this->assertEquals(
			array('jpeg'),
			\CoursePress_Helper_Utility::get_image_extensions()
		);
	}

	function test__filter_content__can_remove_all_html_tags()
	{
		$this->assertEquals(
			'The post content. Here is an embedded video: Inside iFrame.',
			\CoursePress_Helper_Utility::filter_content(
				'<script type="application/javascript"></script>The post <strong>content</strong>. <p>Here is an embedded video: <iframe>Inside iFrame</iframe>.</p>',
				true
			)
		);
	}

	function test__filter_content__can_remove_unsafe_html_tags()
	{
		$this->assertEquals(
			'The post <strong>content</strong>. <p>Here is an embedded video: Inside iFrame.</p>',
			\CoursePress_Helper_Utility::filter_content(
				'<script type="application/javascript"></script>The post <strong>content</strong>. <p>Here is an embedded video: <iframe>Inside iFrame</iframe>.</p>',
				false
			)
		);
	}

	function test__filter_content__allowed_html_tags_are_filtered()
	{
		add_filter('coursepress_allowed_post_tags', function($tags){
			unset($tags['strong']);
			return $tags;
		});

		$this->assertEquals(
			'The post content. <p>Here is an embedded video: Inside iFrame.</p>',
			\CoursePress_Helper_Utility::filter_content(
				'<script type="application/javascript"></script>The post <strong>content</strong>. <p>Here is an embedded video: <iframe>Inside iFrame</iframe>.</p>'
			)
		);
	}

	function test__filter_content__unfiltered_html_returned_for_admin()
	{
		add_filter('user_has_cap', function($all_caps, $caps){
			$new_caps = array();
			foreach ($caps as $cap) {
				$new_caps[$cap] = true;
			}
			return $new_caps;
		}, 10, 2);

		$this->assertEquals(
			'<script type="application/javascript"></script>The post <strong>content</strong>. <p>Here is an embedded video: <iframe>Inside iFrame</iframe>.</p>',
			\CoursePress_Helper_Utility::filter_content(
				'<script type="application/javascript"></script>The post <strong>content</strong>. <p>Here is an embedded video: <iframe>Inside iFrame</iframe>.</p>'
			)
		);
	}

	function test__filter_content__html_tags_removed_from_array_values()
	{
		$this->assertEquals(
			array('The post <strong>content</strong>. <p>Here is an embedded video: Inside iFrame.</p>'),
			\CoursePress_Helper_Utility::filter_content(
				array('<script type="application/javascript"></script>The post <strong>content</strong>. <p>Here is an embedded video: <iframe>Inside iFrame</iframe>.</p>')
			)
		);
	}

	function test__users_can_register__returns_correct_value()
	{
		/* TODO: Find a way to test this. The static variable won't change. */

//		update_option('users_can_register', '1');
//		$this->assertTrue(
//			\CoursePress_Helper_Utility::users_can_register()
//		);
//
//		update_option('users_can_register', '0');
//		$this->assertFalse(
//			\CoursePress_Helper_Utility::users_can_register()
//		);
//
//		add_filter('coursepress_users_can_register', '__return_false');
//		update_option('users_can_register', '1');
//		$this->assertFalse(
//			\CoursePress_Helper_Utility::users_can_register()
//		);
	}

	function test__is_payment_supported__returns_correct_value()
	{
		$this->assertFalse(
			\CoursePress_Helper_Utility::is_payment_supported()
		);

		add_filter('coursepress_payment_supported', '__return_true');
		$this->assertTrue(
			\CoursePress_Helper_Utility::is_payment_supported()
		);
	}

	function test__send_bb_json__returns_correct_json()
	{
		/* TODO: Find a way to test this method. */
//		\Patchwork\redefine('headers_sent', '__return_true');
//		\Patchwork\redefine('header', function(){
//			$this->fail();
//		});
//
//		ob_start();
//		\CoursePress_Helper_Utility::send_bb_json(array());
//		$json = ob_get_clean();
//
//		$this->assertEquals(
//			'',
//			$headers
//		);
//
//		$this->assertEquals(
//			'',
//			$json
//		);
	}

	function test__attachment_from_url__returns_attachments()
	{
		wp_insert_post(
			array(
				'post_title'  => 'Test Attachment',
				'post_status' => 'publish',
				'guid'        => 'http://dummy.url/file.png',
				'post_type'   => 'attachment'
			)
		);

		/**
		 * @var \WP_Post $attachment
		 */
		$attachment = \CoursePress_Helper_Utility::attachment_from_url('http://dummy.url/file.png');
		$this->assertEquals(
			'Test Attachment',
			$attachment->post_title
		);
	}

	function test__get_file_size__file_size_returned_for_url()
	{
		$this->assertContains(
			' KB',
			\CoursePress_Helper_Utility::get_file_size('https://google.com', true)
		);
	}

	function test__get_file_size__file_size_returned_for_file()
	{
		$this->assertContains(
			' KB',
			\CoursePress_Helper_Utility::get_file_size(__FILE__, true)
		);
	}

	function test__format_file_size__size_formatted()
	{
		$this->assertEquals(
			'2.00 GB',
			\CoursePress_Helper_Utility::format_file_size(2150629376)
		);

		$this->assertEquals(
			'3.49 MB',
			\CoursePress_Helper_Utility::format_file_size(3657728)
		);

		$this->assertEquals(
			'5.10 KB',
			\CoursePress_Helper_Utility::format_file_size(5220)
		);

		$this->assertEquals(
			'3 bytes',
			\CoursePress_Helper_Utility::format_file_size(3)
		);

		$this->assertEquals(
			'1 byte',
			\CoursePress_Helper_Utility::format_file_size(1)
		);

		$this->assertEquals(
			'0 bytes',
			\CoursePress_Helper_Utility::format_file_size(0)
		);
	}

	function test__truncate_html()
	{
		/* TODO: Write tests */
	}

	function test__author_description_excerpt()
	{
		update_user_option(
			$this->student->ID,
			'description',
			'This is the student <strong>description</strong>. [gallery]Inside shortcode[/gallery] the things.'
		);

		$this->assertEquals(
			'This is the student description. ...',
			\CoursePress_Helper_Utility::author_description_excerpt($this->student, 5)
		);

		$this->assertEquals(
			'This is the student description. the things.',
			\CoursePress_Helper_Utility::author_description_excerpt($this->student, 7)
		);

		add_filter('excerpt_length', function(){
			return 3;
		});

		$this->assertEquals(
			'This is the ...',
			\CoursePress_Helper_Utility::author_description_excerpt($this->student, 100)
		);
	}

	function test__allowed_student_mimes()
	{
		$this->assertEquals(
			array(
				'txt' => 'text/plain',
				'pdf' => 'application/pdf',
				'zip' => 'application/zip',
			),
			\CoursePress_Helper_Utility::allowed_student_mimes()
		);

		add_filter('coursepress_allowed_student_mimes', function($mime_types){
			$mime_types['jpg'] = 'image/jpg';
			return $mime_types;
		});

		$this->assertEquals(
			array(
				'txt' => 'text/plain',
				'pdf' => 'application/pdf',
				'zip' => 'application/zip',
				'jpg' => 'image/jpg'
			),
			\CoursePress_Helper_Utility::allowed_student_mimes()
		);
	}

	function test__get_user_name__name_returned_in_last_first_format()
	{
		$this->set_student_user_name('John', 'Doe', 'johnDoe');
		$this->assertEquals(
			'Doe, John (johnDoe)',
			\CoursePress_Helper_Utility::get_user_name($this->student->ID, true, true)
		);

		$this->assertEquals(
			'Doe, John',
			\CoursePress_Helper_Utility::get_user_name($this->student->ID, true, false)
		);

		$this->set_student_user_name('', 'Doe', 'johnDoe');
		$this->assertEquals(
			'Doe',
			\CoursePress_Helper_Utility::get_user_name($this->student->ID, true, false)
		);

		$this->set_student_user_name('John', '', 'johnDoe');
		$this->assertEquals(
			'John',
			\CoursePress_Helper_Utility::get_user_name($this->student->ID, true, false)
		);

		$this->set_student_user_name('', '', 'johnDoe');
		$this->assertEquals(
			'johnDoe',
			\CoursePress_Helper_Utility::get_user_name($this->student->ID, true, false)
		);
	}

	function test__get_user_name__name_returned_in_first_last_format()
	{
		$this->set_student_user_name('John', 'Doe', 'johnDoe');
		$this->assertEquals(
			'John Doe (johnDoe)',
			\CoursePress_Helper_Utility::get_user_name($this->student->ID, false, true)
		);

		$this->assertEquals(
			'John Doe',
			\CoursePress_Helper_Utility::get_user_name($this->student->ID, false, false)
		);

		$this->set_student_user_name('', 'Doe', 'johnDoe');
		$this->assertEquals(
			'Doe',
			\CoursePress_Helper_Utility::get_user_name($this->student->ID, false, false)
		);

		$this->set_student_user_name('John', '', 'johnDoe');
		$this->assertEquals(
			'John',
			\CoursePress_Helper_Utility::get_user_name($this->student->ID, false, false)
		);

		$this->set_student_user_name('', '', 'johnDoe');
		$this->assertEquals(
			'johnDoe',
			\CoursePress_Helper_Utility::get_user_name($this->student->ID, true, false)
		);
	}

	function test__duration_to_seconds()
	{
		$this->assertEquals(
			5430,
			\CoursePress_Helper_Utility::duration_to_seconds('01:30:30')
		);

		$this->assertEquals(
			5430,
			\CoursePress_Helper_Utility::duration_to_seconds('1:30:30')
		);

		$this->assertEquals(
			1830,
			\CoursePress_Helper_Utility::duration_to_seconds('30:30')
		);

		$this->assertEquals(
			30,
			\CoursePress_Helper_Utility::duration_to_seconds('30')
		);

		$this->assertEquals(
			0,
			\CoursePress_Helper_Utility::duration_to_seconds('')
		);
	}

	function test__replace_vars()
	{
		$vars = array(
			'FIRST_NAME' => 'John',
			'LAST_NAME' => 'Doe'
		);

		$this->assertEquals(
			'Hi! My name is John Doe and I am a dummy user.',
			\CoursePress_Helper_Utility::replace_vars('Hi! My name is FIRST_NAME LAST_NAME and I am a dummy user.', $vars)
		);

		$this->assertEquals(
			'Hi! My name is John and I am a dummy user.',
			\CoursePress_Helper_Utility::replace_vars('Hi! My name is FIRST_NAME and I am a dummy user.', $vars)
		);
	}

	function test__seconds_to_duration()
	{
		$this->assertEquals(
			'01:30:30',
			\CoursePress_Helper_Utility::seconds_to_duration(5430)
		);

		$this->assertEquals(
			'00:30:30',
			\CoursePress_Helper_Utility::seconds_to_duration(1830)
		);

		$this->assertEquals(
			'00:00:30',
			\CoursePress_Helper_Utility::seconds_to_duration(30)
		);

		$this->assertEquals(
			'00:00:00',
			\CoursePress_Helper_Utility::seconds_to_duration(0)
		);
	}

	function test__add_meta_unique()
	{
		/* TODO: Don't understand the point of method add_meta_unique since update_post_meta would do the same thing. */
	}

	function test__get_post_by_post_type__returns_post_type_object()
	{
		$post_type = \CoursePress_Helper_Utility::get_post_by_post_type('page');

		$this->assertEquals(
			'page',
			$post_type->post_type
		);

		$this->assertEquals(
			'draft',
			$post_type->post_status
		);
	}

	function test__get_post_by_post_type__throws_error()
	{
		\Patchwork\redefine('wp_die', function($died_with_message, $error_code) use (&$error){
			$error = $error_code;
		});

		$post_id = wp_insert_post(array('post_title' => 'Some Post'));
		\CoursePress_Helper_Utility::get_post_by_post_type('page', $post_id);

		$this->assertEquals(
			403,
			$error
		);
	}

	function test__get_post_by_post_type__returns_post_object()
	{
		$post_id = wp_insert_post(array('post_title' => 'Some Post'));
		$post = \CoursePress_Helper_Utility::get_post_by_post_type('post', $post_id);

		$this->assertEquals(
			'Some Post',
			$post->post_title
		);
	}

	function test__course_signup()
	{
		/* TODO: Write some tests */
	}

	function test__get_time()
	{
		$this->assertEquals(
			array(
				'total_seconds' => 23552,
				'time'          => '06:32:32',
				'hours'         => '06',
				'minutes'       => '32',
				'seconds'       => '32',
			),
			\CoursePress_Helper_Utility::get_time(32, 32, 6)
		);
	}

	function test__convert_array_to_params__numeric_keys_not_included()
	{
		$this->assertEquals(
			'',
			\CoursePress_Helper_Utility::convert_array_to_params(
				array(
					0   => 'foo',
					1   => 'bar',
					'3' => 'param',
					'4' => 'val'
				)
			)
		);
	}

	function test__convert_array_to_params__array_values_converted_to_params()
	{
		$this->assertEquals(
			' foo="bar" param="val&amp;"',
			\CoursePress_Helper_Utility::convert_array_to_params(
				array(
					'foo'   => 'bar',
					'param' => 'val&'
				)
			)
		);
	}

	function test__add_site_vars()
	{
		$site_vars = \CoursePress_Helper_Utility::add_site_vars();
		$this->assertCount(
			5,
			$site_vars
		);

		$this->assertArrayHasKey('BLOG_ADDRESS', $site_vars);
		$this->assertArrayHasKey('BLOG_NAME', $site_vars);
		$this->assertArrayHasKey('WEBSITE_NAME', $site_vars);
		$this->assertArrayHasKey('LOGIN_ADDRESS', $site_vars);
		$this->assertArrayHasKey('WEBSITE_ADDRESS', $site_vars);

		add_filter('coursepress_site_vars', function(){
			return array();
		});

		$this->assertCount(
			0,
			\CoursePress_Helper_Utility::add_site_vars()
		);
	}

	function test__convert_hex_color_to_rgb__colors_converted_successfully()
	{
		/* TODO: TCPDF_COLORS is not getting included while running the test */
//		$this->assertEmpty(
//			\CoursePress_Helper_Utility::convert_hex_color_to_rgb('fff')
//		);
//
//		$this->assertEmpty(
//			\CoursePress_Helper_Utility::convert_hex_color_to_rgb('ffffff')
//		);
//
//		$this->assertEmpty(
//			\CoursePress_Helper_Utility::convert_hex_color_to_rgb('#FFF')
//		);
//
//		$this->assertEquals(
//			array(),
//			\CoursePress_Helper_Utility::convert_hex_color_to_rgb('#FFFFFF')
//		);
//
//		$this->assertEquals(
//			array(),
//			\CoursePress_Helper_Utility::convert_hex_color_to_rgb('#BADA55')
//		);
	}

	function test__is_password_strong()
	{
		$this->set_post(array('password_strength_level' => 2));
		$this->assertFalse(
			\CoursePress_Helper_Utility::is_password_strong()
		);

		$this->set_post(array('password_strength_level' => 3));
		$this->assertTrue(
			\CoursePress_Helper_Utility::is_password_strong()
		);

		$this->set_post(
			array(
				'confirm_weak_password'   => true,
				'password_strength_level' => 2
			)
		);
		$this->assertTrue(
			\CoursePress_Helper_Utility::is_password_strong()
		);

		add_filter('coursepress_display_password_strength_meter', '__return_false');
		$this->set_post(array('password' => 'abc12'));
		$this->assertFalse(
			\CoursePress_Helper_Utility::is_password_strong()
		);

		$this->set_post(array('password' => 'abcde1'));
		$this->assertTrue(
			\CoursePress_Helper_Utility::is_password_strong()
		);

		$this->set_post(
			array(
				'confirm_weak_password' => true,
				'password'              => 'a'
			)
		);
		$this->assertTrue(
			\CoursePress_Helper_Utility::is_password_strong()
		);
	}

	function test__is_password_strength_meter_enabled()
	{
		$this->assertTrue(\CoursePress_Helper_Utility::is_password_strength_meter_enabled());

		add_filter('coursepress_display_password_strength_meter', '__return_false');
		$this->assertFalse(\CoursePress_Helper_Utility::is_password_strength_meter_enabled());
	}

	function test__get_minimum_password_length()
	{
		$this->assertEquals(
			6,
			\CoursePress_Helper_Utility::get_minimum_password_length()
		);

		add_filter('coursepress_min_password_length', function(){
			return 3;
		});
		$this->assertEquals(
			3,
			\CoursePress_Helper_Utility::get_minimum_password_length()
		);
	}

	function test__is_youtube_url()
	{
		$this->assertTrue(
			\CoursePress_Helper_Utility::is_youtube_url('https://www.youtube.com/watch?v=owTPZQQAVyQ')
		);

		$this->assertTrue(
			\CoursePress_Helper_Utility::is_youtube_url('https://www.youtu.be/watch?v=owTPZQQAVyQ')
		);
	}

	function test__is_vimeo_url()
	{
		$this->assertTrue(
			\CoursePress_Helper_Utility::is_vimeo_url('https://vimeo.com/64122803')
		);
	}

	function test__create_video_js_setup_data__creates_youtube_data()
	{
		$video_js_setup_data = \CoursePress_Helper_Utility::create_video_js_setup_data('https://www.youtu.be/watch?v=owTPZQQAVyQ');
		$video_js_setup_data = \CoursePress_Helper_Utility::object_to_array(json_decode($video_js_setup_data));
		$this->assertEquals(
			array('youtube'),
			$video_js_setup_data['techOrder']
		);

		$this->assertEquals(
			array(
				'type' => 'video/youtube',
				'src'  => 'https://www.youtu.be/watch?v=owTPZQQAVyQ'
			),
			$video_js_setup_data['sources'][0]
		);
	}

	function test__create_video_js_setup_data__creates_vimeo_data()
	{
		$video_js_setup_data = \CoursePress_Helper_Utility::create_video_js_setup_data('https://vimeo.com/64122803');
		$video_js_setup_data = \CoursePress_Helper_Utility::object_to_array(json_decode($video_js_setup_data));

		$this->assertEquals(
			array('vimeo'),
			$video_js_setup_data['techOrder']
		);

		$this->assertEquals(
			array(
				'type' => 'video/vimeo',
				'src'  => 'https://vimeo.com/64122803'
			),
			$video_js_setup_data['sources'][0]
		);
	}

	function set_post($values)
	{
		$_POST = $values;
	}

	function set_student_user_name($first_name, $last_name, $display_name)
	{
		update_user_option( $this->student->ID, 'display_name', $display_name );
		update_user_option( $this->student->ID, 'last_name', $last_name );
		update_user_option( $this->student->ID, 'first_name', $first_name );
	}

	function change_referrer($url)
	{
		\Patchwork\redefine('wp_get_referer', function() use ($url){
			return $url;
		});
	}

	/**
	 * @param $user_id
	 * @param $meta_key
	 * @param $meta_value
	 */
	private function insert_user_meta($user_id, $meta_key, $meta_value)
	{
		update_user_meta($user_id, $meta_key, $meta_value);
		$this->assertEquals(
			$meta_value,
			get_user_meta($user_id, $meta_key, true)
		);
	}
}

function get_dummy_person_object()
{
	class Person
	{
		var $name;
		var $designation;
		var $interests;
		var $related_people = array();

		function __construct($name, $designation, $interests, $related_people = array())
		{
			$this->name = $name;
			$this->designation = $designation;
			$this->interests = $interests;
			$this->related_people = $related_people;
		}
	}

	$child = new Person('Joshua Doe', 'Kid', array('crawling', 'crying'));

	return new Person('John Doe', 'Creative Director', array('swimming', 'writing'), array($child));
}