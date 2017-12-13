<?php
/**
 * @group coursepress-core
 */
class Coursepress_Data_Module_Test extends CoursePress_UnitTestCase {

	public function test_exists() {
		$this->assertTrue( is_callable( array( 'CoursePress_Data_Module', 'module_init_hooks' ) ) );
		$this->assertTrue( is_callable( array( 'CoursePress_Data_Module', 'get_format' ) ) );
		$this->assertTrue( is_callable( array( 'CoursePress_Data_Module', 'get_post_type_name' ) ) );
		$this->assertTrue( is_callable( array( 'CoursePress_Data_Module', 'get_time_estimation' ) ) );
		$this->assertTrue( is_callable( array( 'CoursePress_Data_Module', 'legacy_map' ) ) );
		$this->assertTrue( is_callable( array( 'CoursePress_Data_Module', 'fix_legacy_meta' ) ) );
		$this->assertTrue( is_callable( array( 'CoursePress_Data_Module', 'attributes' ) ) );
		$this->assertTrue( is_callable( array( 'CoursePress_Data_Module', 'discussion_module_link' ) ) );
		$this->assertTrue( is_callable( array( 'CoursePress_Data_Module', 'discussions_comments_open' ) ) );
		$this->assertTrue( is_callable( array( 'CoursePress_Data_Module', 'discussion_post_link' ) ) );
		$this->assertTrue( is_callable( array( 'CoursePress_Data_Module', 'discussion_edit_redirect' ) ) );
		$this->assertTrue( is_callable( array( 'CoursePress_Data_Module', 'discussion_reply_link' ) ) );
		$this->assertTrue( is_callable( array( 'CoursePress_Data_Module', 'discussion_cancel_reply_link' ) ) );
		$this->assertTrue( is_callable( array( 'CoursePress_Data_Module', 'get_quiz_results' ) ) );
		$this->assertTrue( is_callable( array( 'CoursePress_Data_Module', 'get_form_results' ) ) );
		$this->assertTrue( is_callable( array( 'CoursePress_Data_Module', 'quiz_result_content' ) ) );
		$this->assertTrue( is_callable( array( 'CoursePress_Data_Module', 'get_args_mandatory_modules' ) ) );
		$this->assertTrue( is_callable( array( 'CoursePress_Data_Module', 'get_mandatory_modules' ) ) );
		$this->assertTrue( is_callable( array( 'CoursePress_Data_Module', 'is_module_done_by_student' ) ) );
		$this->assertTrue( is_callable( array( 'CoursePress_Data_Module', 'add_last_login_time' ) ) );
		$this->assertTrue( is_callable( array( 'CoursePress_Data_Module', 'get_modules_ids_by_unit' ) ) );
		$this->assertTrue( is_callable( array( 'CoursePress_Data_Module', 'get_unit_id_by_module' ) ) );
		$this->assertTrue( is_callable( array( 'CoursePress_Data_Module', 'get_course_id_by_module' ) ) );
		$this->assertTrue( is_callable( array( 'CoursePress_Data_Module', 'get_instructors' ) ) );
		$this->assertTrue( is_callable( array( 'CoursePress_Data_Module', 'add_instructors_to_comments_args' ) ) );
		$this->assertTrue( is_callable( array( 'CoursePress_Data_Module', 'get_module_ids_by_unit_ids' ) ) );
		$this->assertTrue( is_callable( array( 'CoursePress_Data_Module', 'show_on_list' ) ) );
	}

	public function test_get_format() {
		$assert = CoursePress_Data_Module::get_format();
		$keys = array(
			'post_type',
			'post_args',
		);
		foreach ( $keys as $key ) {
			$this->assertArrayHasKey( $key, $assert );
		}
		$keys = array(
			'labels',
			'public',
			'show_ui',
			'publicly_queryable',
			'capability_type',
			'map_meta_cap',
			'query_var',
		);
		foreach ( $keys as $key ) {
			$this->assertArrayHasKey( $key, $assert['post_args'] );
		}
		$keys = array(
			'name',
			'singular_name',
			'add_new',
			'add_new_item',
			'edit_item',
			'edit',
			'new_item',
			'view_item',
			'search_items',
			'not_found',
			'not_found_in_trash',
			'view',
		);
		foreach ( $keys as $key ) {
			$this->assertArrayHasKey( $key, $assert['post_args']['labels'] );
		}
	}

	public function test_get_post_type_name() {
		$this->assertEquals( 'module', CoursePress_Data_Module::get_post_type_name() );
	}

	public function test_get_time_estimation() {
		/**
		 * Wrong data
		 */
		$values = $this->get_wrong_values();
		foreach ( $values as $value ) {
			$assert = CoursePress_Data_Module::get_time_estimation( $value );
			$this->assertEmpty($assert );
		}
		/**
		 * Good data
		 */
		$modules = $this->get_modules();
		foreach ( $modules as $module ) {
			$assert = CoursePress_Data_Module::get_time_estimation( $module->ID );
			$this->assertEmpty( $assert );
		}
	}

	public function test_legacy_map() {
		$assert = CoursePress_Data_Module::legacy_map();
		$keys = array(
			'audio_module',
			'chat_module',
			'checkbox_input_module',
			'file_module',
			'file_input_module',
			'image_module',
			'page_break_module',
			'radio_input_module',
			'page_break_module',
			'section_break_module',
			'text_module',
			'text_input_module',
			'textarea_input_module',
			'video_module',
		);
		foreach ( $keys as $key ) {
			$this->assertArrayHasKey( $key, $assert );
		}
	}

	public function test_attributes() {
		/**
		 * Wrong data
		 */
		$assert = CoursePress_Data_Module::attributes( 'foo' );
		$this->assertFalse( $assert );
		$assert = CoursePress_Data_Module::attributes( 0 );
		$this->assertFalse( $assert );
		/**
		 * Good data
		 */
		$keys = array(
			'module_type',
			'mode',
			'duration',
			'show_title',
			'allow_retries',
			'retry_attempts',
			'minimum_grade',
			'assessable',
			'mandatory',
			'module_order',
			'module_page',
			'order',
		);
		$modules = $this->get_modules();
		foreach ( $modules as $module ) {
			$assert = CoursePress_Data_Module::attributes( $module );
			$this->assertInternalType( 'array', $assert );
			foreach ( $keys as $key ) {
				$this->assertArrayHasKey( $key, $assert );
			}
			if ( preg_match( '/^input/', $assert['module_type'] ) ) {
				$this->assertArrayHasKey( 'use_timer', $assert );
			}
			switch ( $assert['module_type'] ) {
				case 'input-radio':
					$this->assertArrayHasKey( 'answers', $assert );
					$this->assertArrayHasKey( 'answers_selected', $assert );
				break;
				case 'input-upload':
					$this->assertArrayHasKey( 'instructor_assessable', $assert );
				break;
			}
			/**
			 * TODO: add (if needed) another module types
			 */
		}
	}

	/**
	 * discussion_module_link
	 */
	public function test_discussion_module_link() {
		/**
		 * Wrong data
		 */
		$assert = CoursePress_Data_Module::discussion_module_link( 'foo', 'baz' );
		$this->assertEquals( 'foo', $assert );
		$assert = CoursePress_Data_Module::discussion_module_link( 'foo', 0 );
		$this->assertEquals( 'foo', $assert );
		/**
		 * Good data
		 */
		$modules = $this->get_modules();
		foreach ( $modules as $module ) {
			$module_type = get_post_meta( $module->ID, 'module_type', true );
			$is_discussion = 'discussion' == $module_type;
			$args = array(
				'post_id' => $module->ID,
				'status' => 'all',
			);
			$comments = get_comments( $args );
			foreach ( $comments as $comment ) {
				/**
				 * discussion_module_link
				 */
				$assert = CoursePress_Data_Module::discussion_module_link( 'foo', $comment );
				$re = sprintf( '/#module-%d/', $module->ID );
				if ( $is_discussion ) {
					$this->assertNotEquals( 'foo', $assert );
					$this->assertRegExp( $re, $assert );
				} else {
					$this->assertEquals( 'foo', $assert );
				}
			}
		}
	}

	/**
	 * discussions_comments_open
	 */
	public function test_discussions_comments_open() {
		/**
		 * Wrong data
		 */
		$assert = CoursePress_Data_Module::discussions_comments_open( 'foo', 'baz' );
		$this->assertEquals( 'foo', $assert );
		$assert = CoursePress_Data_Module::discussions_comments_open( 'foo', 0 );
		$this->assertEquals( 'foo', $assert );
		/**
		 * Good data
		 */
		$modules = $this->get_modules();
		foreach ( $modules as $module ) {
			$module_type = get_post_meta( $module->ID, 'module_type', true );
			$is_discussion = 'discussion' == $module_type;
			$args = array(
				'post_id' => $module->ID,
				'status' => 'all',
			);
			$comments = get_comments( $args );
			foreach ( $comments as $comment ) {
				$assert = CoursePress_Data_Module::discussions_comments_open( 'bar', $module->ID );
				if ( $is_discussion ) {
					$this->assertTrue( $assert );
				} else {
					$this->assertEquals( 'bar', $assert );
				}
			}
		}
	}

	/**
	 * discussion_post_link
	 */
	public function test_discussion_post_link() {
		/**
		 * Wrong data
		 */
		$assert = CoursePress_Data_Module::discussion_post_link( 'foo', 'baz' );
		$this->assertEquals( 'foo', $assert );
		$assert = CoursePress_Data_Module::discussion_post_link( 'foo', 0 );
		$this->assertEquals( 'foo', $assert );
		/**
		 * Good data
		 */
		$modules = $this->get_modules();
		foreach ( $modules as $module ) {
			$module_type = get_post_meta( $module->ID, 'module_type', true );
			$is_discussion = 'discussion' == $module_type;
			$args = array(
				'post_id' => $module->ID,
				'status' => 'all',
			);
			$comments = get_comments( $args );
			foreach ( $comments as $comment ) {
				$re = sprintf( '/#module-%d/', $module->ID );
				$assert = CoursePress_Data_Module::discussion_post_link( 'foo', $module );
				if ( $is_discussion ) {
					$this->assertNotEquals( 'foo', $assert );
					$this->assertRegExp( $re, $assert );
				} else {
					$this->assertEquals( 'foo', $assert );
				}
			}
		}
	}

	/**
	 * discussion_edit_redirect
	 */
	public function test_discussion_edit_redirect() {
		/**
		 * Wrong data
		 */
		$assert = CoursePress_Data_Module::discussion_edit_redirect( 'foo', 0 );
		$this->assertEquals( 'foo', $assert );
		$assert = CoursePress_Data_Module::discussion_edit_redirect( 'foo', 'baz' );
		$this->assertEquals( 'foo', $assert );
		/**
		 * Good data
		 */
		$modules = $this->get_modules();
		foreach ( $modules as $module ) {
			$module_type = get_post_meta( $module->ID, 'module_type', true );
			$is_discussion = 'discussion' == $module_type;
			$args = array(
				'post_id' => $module->ID,
				'status' => 'all',
			);
			$comments = get_comments( $args );
			foreach ( $comments as $comment ) {
				$re = sprintf( '/#comment-%d/', $comment->comment_ID );
				$assert = CoursePress_Data_Module::discussion_edit_redirect( 'foo', $comment->comment_ID );
				if ( $is_discussion ) {
					$this->assertNotEquals( 'foo', $assert );
					$this->assertRegExp( $re, $assert );
				} else {
					$this->assertEquals( 'foo', $assert );
				}
			}
		}
	}

	/**
	 * discussion_reply_link
	 */
	public function test_discussion_reply_link() {
		$assert_args = array(
			'add_below'     => 'comment',
			'respond_id'    => 'respond',
			'reply_text'    => 'Reply',
			'reply_to_text' => 'Reply to %s',
			'login_text'    => 'Log in to Reply',
			'depth'         => 0,
			'before'        => '',
			'after'         => '',
		);
		/**
		 * Wrong data
		 */
		$assert = CoursePress_Data_Module::discussion_reply_link( 'foo', array(), 0, 0 );
		$this->assertEquals( 'foo', $assert );
		$assert = CoursePress_Data_Module::discussion_reply_link( 'foo', $assert_args, 0, 0 );
		$this->assertEquals( 'foo', $assert );
		$assert = CoursePress_Data_Module::discussion_reply_link( 'foo', $assert_args, 0, 'baz' );
		$this->assertEquals( 'foo', $assert );
		$assert = CoursePress_Data_Module::discussion_reply_link( 'foo', $assert_args, 'bar', 0 );
		$this->assertEquals( 'foo', $assert );
		$assert = CoursePress_Data_Module::discussion_reply_link( 'foo', $assert_args, 'bar', 'baz' );
		$this->assertEquals( 'foo', $assert );
		/**
		 * Good data
		 */
		$modules = $this->get_modules();
		foreach ( $modules as $module ) {
			$module_type = get_post_meta( $module->ID, 'module_type', true );
			$is_discussion = 'discussion' == $module_type;
			$args = array(
				'post_id' => $module->ID,
				'status' => 'all',
			);
			$comments = get_comments( $args );
			foreach ( $comments as $comment ) {
				/**
				 * discussion_reply_link
				 */
				$assert = CoursePress_Data_Module::discussion_reply_link( 'foo', $assert_args, $comment, $module );
				$this->assertNotEmpty( $assert );
				$this->assertNotEquals( 'foo', $assert );
				$this->assertRegExp( '/^<a.+a>$/', $assert );
			}
		}
	}

	/**
	 * discussion_cancel_reply_link
	 */
	public function test_discussion_cancel_reply_link() {
		$text = 'Click here to cancel reply.';
		$link = esc_html( remove_query_arg( 'replytocom' ) ) . '#respond';
		$formatted_link = sprintf( '<a href="%s">%s</a>', esc_url( $link ), $text );
		/**
		 * Wrong data
		 */
		$assert = CoursePress_Data_Module::discussion_cancel_reply_link( $formatted_link, $link, $text );
		$this->assertEmpty( $assert );
		/**
		 * Good data
		 */
		$modules = $this->get_modules();
		foreach ( $modules as $module ) {
			$module_type = get_post_meta( $module->ID, 'module_type', true );
			$is_discussion = 'discussion' == $module_type;
			$args = array(
				'post_id' => $module->ID,
				'status' => 'all',
			);
			$comments = get_comments( $args );
			foreach ( $comments as $comment ) {
				$_GET['replytocom'] = $comment->comment_ID;
				$assert = CoursePress_Data_Module::discussion_cancel_reply_link( $formatted_link, $link, $text );
				$this->assertNotEmpty( $assert );
				$this->assertRegExp( '/^<a.+a>$/', $assert );
				$this->assertNotEquals( $formatted_link, $assert );
			}
		}
	}

	/**
	 * get_quiz_results( $student_id, $course_id, $unit_id, $module_id, $response = false, $data = false )
	 */
	public function test_get_quiz_results() {
		$keys = array(
			'attributes',
			'correct',
			'grade',
			'message' => array( 'hide', 'text' ),
			'passed',
			'total_questions',
			'wrong',
		);
		/**
		 * Wrong data
		 */
		$assert = CoursePress_Data_Module::get_quiz_results( 'foo', 'bar', 'baz', 'gas' );
		$this->assertFalse( $assert );
		$assert = CoursePress_Data_Module::get_quiz_results( 0, 0, 0, 0 );
		$this->assertFalse( $assert );
		/**
		 * Good data
		 */
		$modules = $this->get_modules();
		foreach ( $modules as $module ) {
			$assert = CoursePress_Data_Module::get_quiz_results(
				$this->student->ID,
				$this->course->ID,
				$module->post_parent,
				$module->ID
			);
			$this->assertFalse( $assert );
			/**
			 * TODO: add students answer and check it
			 *
			 $this->assertInternalType( 'array', $assert );
			$this->has_keys( $keys, $assert );
			 */
		}
	}

	/**
	 * quiz_result_content( $student_id, $course_id, $unit_id, $module_id, $quiz_result = false )
	 */
	public function test_quiz_result_content() {
		/**
		 * Wrong data
		 */
		$assert = CoursePress_Data_Module::quiz_result_content( 'foo', 'foo', 'foo', 'foo' );
		$this->assertNotEmpty( $assert );
		$assert = CoursePress_Data_Module::quiz_result_content( 0, 0, 0, 0 );
		$this->assertNotEmpty( $assert );
		/**
		 * Good data
		 */
		$modules = $this->get_modules();
		foreach ( $modules as $module ) {
			$assert = CoursePress_Data_Module::quiz_result_content(
				$this->student->ID,
				$this->course->ID,
				$module->post_parent,
				$module->ID
			);
			$this->assertNotEmpty( $assert );
		}
	}

	/**
	 * get_args_mandatory_modules( $unit_id )
	 */
	public function test_get_args_mandatory_modules() {
		$expected = array(
			'fields' => 'ids',
			'meta_key' => 'mandatory',
			'meta_value' => '1',
			'nopaging' => '1',
			'post_parent' => 0,
			'post_type' => 'module',
		);
		/**
		 * Wrong data
		 */
		$assert = CoursePress_Data_Module::get_args_mandatory_modules( 0 );
		$this->assertInternalType( 'array', $assert );
		$this->assertEqualSetsWithIndex( $expected, $assert );
		$assert = CoursePress_Data_Module::get_args_mandatory_modules( 'foo' );
		$this->assertInternalType( 'array', $assert );
		$expected['post_parent'] = 'foo';
		$this->assertEqualSetsWithIndex( $expected, $assert );
		/**
		 * Good data
		 */
		foreach ( $this->course->units as $unit ) {
			$assert = CoursePress_Data_Module::get_args_mandatory_modules( $unit->ID );
			$this->assertInternalType( 'array', $assert );
			$expected['post_parent'] = $unit->ID;
			$this->assertEqualSetsWithIndex( $expected, $assert );
		}
	}

	/**
	 * function get_mandatory_modules( $unit_id )
	 */
	public function test_get_mandatory_modules() {
		/**
		 * Wrong data
		 */
		$assert = CoursePress_Data_Module::get_mandatory_modules( 0 );
		$this->assertInternalType( 'array', $assert );
		$this->assertEmpty( $assert );
		$assert = CoursePress_Data_Module::get_mandatory_modules( 'foo' );
		$this->assertInternalType( 'array', $assert );
		$this->assertEmpty( $assert );
		/**
		 * Good data
		 */
		foreach ( $this->course->units as $unit ) {
			$assert = CoursePress_Data_Module::get_mandatory_modules( $unit->ID );
			$this->assertInternalType( 'array', $assert );
			$this->assertEmpty( $assert );
		}
	}

	/**
	 * is_module_done_by_student( $module_id, $student_id )
	 */
	public function test_is_module_done_by_student() {
		/**
		 * Wrong data
		 */
		$assert = CoursePress_Data_Module::is_module_done_by_student( 'foo', 'bar' );
		$this->assertInternalType( 'boolean', $assert );
		$this->assertTrue( $assert );
		$assert = CoursePress_Data_Module::is_module_done_by_student( 0, 'bar' );
		$this->assertInternalType( 'boolean', $assert );
		$this->assertTrue( $assert );
		$assert = CoursePress_Data_Module::is_module_done_by_student( 0, 0 );
		$this->assertInternalType( 'boolean', $assert );
		$this->assertTrue( $assert );
		$assert = CoursePress_Data_Module::is_module_done_by_student( 'foo', 0 );
		$this->assertInternalType( 'boolean', $assert );
		$this->assertTrue( $assert );
		/**
		 * Good data
		 */
		$modules = $this->get_modules();
		foreach ( $modules as $module ) {
			$assert = CoursePress_Data_Module::is_module_done_by_student( $module->ID, $this->student->ID );
			$this->assertInternalType( 'boolean', $assert );
			$this->assertTrue( $assert );
		}
	}

	/**
	 * add_last_login_time( $comment_id, $comment )
	 */
	public function test_add_last_login_time() {
		/**
		 * Wrong data
		 */
		$assert = CoursePress_Data_Module::add_last_login_time( 'foo', 'bar' );
		$this->assertEmpty( $assert );
		$assert = CoursePress_Data_Module::add_last_login_time( 0, 'bar' );
		$this->assertEmpty( $assert );
		$assert = CoursePress_Data_Module::add_last_login_time( 0, 0 );
		$this->assertEmpty( $assert );
		$assert = CoursePress_Data_Module::add_last_login_time( 'foo', 0 );
		$this->assertEmpty( $assert );
		/**
		 * Good data
		 */
		$modules = $this->get_modules();
		foreach ( $modules as $module ) {
			$args = array(
				'post_id' => $module->ID,
				'status' => 'all',
			);
			$comments = get_comments( $args );
			foreach ( $comments as $comment ) {
				$assert = get_comment_meta( $comment->comment_ID, 'last_login', true );
				$this->assertNotEmpty( $assert );
				$this->assertEquals( '1478686730', $assert );
			}
		}
	}

	/**
	 * get_modules_ids_by_unit( $unit_id )
	 */
	public function test_get_modules_ids_by_unit() {
		/**
		 * Wrong data
		 */
		$assert = CoursePress_Data_Module::get_modules_ids_by_unit( 'foo' );
		$this->assertInternalType( 'array', $assert );
		$this->assertEquals( array(), $assert );
		$assert = CoursePress_Data_Module::get_modules_ids_by_unit( 1 );
		$this->assertInternalType( 'array', $assert );
		$this->assertEquals( array(), $assert );
		/**
		 * Good data
		 */
		foreach ( $this->course->units as $unit ) {
			$assert = CoursePress_Data_Module::get_modules_ids_by_unit( $unit->ID );
			foreach ( $assert as $id ) {
				$this->assertInternalType( 'integer', $id );
			}
		}
	}

	/**
	 * get_unit_id_by_module( $module )
	 */
	public function test_get_unit_id_by_module() {
		/**
		 * Wrong data
		 */
		$assert = CoursePress_Data_Module::get_unit_id_by_module( 'foo' );
		$this->assertEquals( 0, $assert );
		$assert = CoursePress_Data_Module::get_unit_id_by_module( 0 );
		$this->assertEquals( 0, $assert );
		/**
		 * Good data
		 */
		$modules = $this->get_modules();
		foreach ( $modules as $module ) {
			$assert = CoursePress_Data_Module::get_unit_id_by_module( $module->ID );
			$this->assertNotEmpty( $assert );
			$this->assertInternalType( 'integer', $assert );
			$this->assertEquals( $module->post_parent, $assert );
		}
	}

	/**
	 * get_course_id_by_module( $module )
	 */
	public function test_get_course_id_by_module() {
		/**
		 * Wrong data
		 */
		$assert = CoursePress_Data_Module::get_course_id_by_module( 'foo' );
		$this->assertEquals( 0, $assert );
		$assert = CoursePress_Data_Module::get_course_id_by_module( 0 );
		$this->assertEquals( 0, $assert );
		/**
		 * Good data
		 */
		$modules = $this->get_modules();
		foreach ( $modules as $module ) {
			$assert = CoursePress_Data_Module::get_course_id_by_module( $module->ID );
			$this->assertNotEmpty( $assert );
			$this->assertInternalType( 'integer', $assert );
			$post_parent = wp_get_post_parent_id( $module->post_parent );
			$this->assertEquals( $post_parent, $assert );
		}
	}

	/**
	 * get_instructors( $module_id, $objects = false )
	 */
	public function test_get_instructors() {
		/**
		 * Wrong data
		 */
		$assert = CoursePress_Data_Module::get_instructors( 'foo', true );
		$this->assertInternalType( 'array', $assert );
		$this->assertEquals( array(), $assert );
		$assert = CoursePress_Data_Module::get_instructors( 'foo', false );
		$this->assertInternalType( 'array', $assert );
		$this->assertEquals( array(), $assert );
		$assert = CoursePress_Data_Module::get_instructors( 0, true );
		$this->assertInternalType( 'array', $assert );
		$this->assertEquals( array(), $assert );
		$assert = CoursePress_Data_Module::get_instructors( 0, false );
		$this->assertInternalType( 'array', $assert );
		$this->assertEquals( array(), $assert );
		/**
		 * Good data
		 */
		$modules = $this->get_modules();
		foreach ( $modules as $module ) {
			$assert = CoursePress_Data_Module::get_instructors( $module->ID, true );
			$this->assertInternalType( 'array', $assert );
			$assert = $assert[0];
			$this->assertInstanceOf( 'WP_User', $assert );
			$this->assertEquals( $this->instructor->data, $assert->data );
			$this->assertEquals( $this->instructor->ID, $assert->ID );
			$assert = CoursePress_Data_Module::get_instructors( $module->ID, false );
			$this->assertInternalType( 'array', $assert );
			$this->assertEqualSets( array( $this->instructor->ID ), $assert );
		}
	}

	/**
	 * add_instructors_to_comments_args( $args )
	 */
	public function test_add_instructors_to_comments_args() {
		/**
		 * Wrong data
		 */
		$assert = Coursepress_Data_Module::add_instructors_to_comments_args( 'foo' );
		$this->assertEquals( 'foo', $assert );
		$assert = Coursepress_Data_Module::add_instructors_to_comments_args( 0 );
		$this->assertEquals( 0, $assert );
		$assert = Coursepress_Data_Module::add_instructors_to_comments_args( array() );
		$this->assertEquals( array(), $assert );
		$post = $this->course;
		$assert = Coursepress_Data_Module::add_instructors_to_comments_args( 'foo' );
		$this->assertEquals( 'foo', $assert );
		$assert = Coursepress_Data_Module::add_instructors_to_comments_args( 0 );
		$this->assertEquals( 0, $assert );
		$assert = Coursepress_Data_Module::add_instructors_to_comments_args( array() );
		$this->assertEquals( array(), $assert );
		/**
		 * Good data
		 */
		global $post;
		$modules = $this->get_modules();
		foreach ( $modules as $module ) {
			$post = $module;
			$assert = Coursepress_Data_Module::add_instructors_to_comments_args( array() );
			$this->assertInternalType( 'array', $assert );
			$this->assertEqualSets( array( $this->instructor->ID ), $assert['coursepress_instructors'] );
		}
	}

	/**
	 * get_module_ids_by_unit_ids( $ids )
	 */
	public function test_get_module_ids_by_unit_ids() {
		/**
		 * Wrong data
		 */
		$assert = CoursePress_Data_Module::get_module_ids_by_unit_ids( 'foo' );
		$this->assertInternalType( 'array', $assert );
		$this->assertEquals( array(), $assert );
		$assert = CoursePress_Data_Module::get_module_ids_by_unit_ids( 0 );
		$this->assertInternalType( 'array', $assert );
		$this->assertEquals( array(), $assert );
		$assert = CoursePress_Data_Module::get_module_ids_by_unit_ids( array() );
		$this->assertInternalType( 'array', $assert );
		$this->assertEquals( array(), $assert );
		$assert = CoursePress_Data_Module::get_module_ids_by_unit_ids( array( 'foo' ) );
		$this->assertInternalType( 'array', $assert );
		$this->assertEquals( array(), $assert );
		$assert = CoursePress_Data_Module::get_module_ids_by_unit_ids( array( 0 ) );
		$this->assertInternalType( 'array', $assert );
		$this->assertEquals( array(), $assert );
		/**
		 * Good data
		 */
		$units_ids = array();
		foreach ( $this->course->units as $unit ) {
			$units_ids[] = $unit->ID;
		}
		$modules_ids = array();
		$modules = $this->get_modules();
		foreach ( $modules as $module ) {
			$modules_ids[] = $module->ID;
		}
		$assert = CoursePress_Data_Module::get_module_ids_by_unit_ids( $units_ids );
		$this->assertNotEmpty( $assert );
		$this->assertInternalType( 'array', $assert );
		$this->assertEqualSets( $modules_ids, $assert );
	}

	/**
	 * show_on_list( $module_id, $unit_id, $meta )
	 */
	public function test_show_on_list() {
		/**
		 * Wrong data
		 */
		$assert = CoursePress_Data_Module::show_on_list( 'foo', 'bar', 'baz' );
		$this->assertEmpty( $assert );
		$assert = CoursePress_Data_Module::show_on_list( 0, 0, 0 );
		$this->assertEmpty( $assert );
		/**
		 * Good data
		 */
		$modules = $this->get_modules();
		foreach ( $modules as $module ) {
			$meta = get_post_meta( $module->ID );
			$assert = CoursePress_Data_Module::show_on_list( $module->ID, $module->post_parent, $meta );
			$this->assertEmpty( $assert );
		}
	}
}
