<?php
/**
 * @group coursepress-data-discussion
 */
class CoursePress_Data_Discussion_Test extends CoursePress_UnitTestCase {

	public function __construct() {
		parent::__construct();
	}

	public function test_exists() {
		$this->assertTrue( class_exists( 'CoursePress_Data_Discussion' ) );
		$this->assertTrue( is_callable( array( 'CoursePress_Data_Discussion', 'get_format' ) ) );
		$this->assertTrue( is_callable( array( 'CoursePress_Data_Discussion', 'get_post_type_name' ) ) );
		$this->assertTrue( is_callable( array( 'CoursePress_Data_Discussion', 'attributes' ) ) );
		$this->assertTrue( is_callable( array( 'CoursePress_Data_Discussion', 'get_discussions' ) ) );
		$this->assertTrue( is_callable( array( 'CoursePress_Data_Discussion', 'permalink' ) ) );
		$this->assertTrue( is_callable( array( 'CoursePress_Data_Discussion', 'update_discussion' ) ) );
		$this->assertTrue( is_callable( array( 'CoursePress_Data_Discussion', 'get_one' ) ) );
		$this->assertTrue( is_callable( array( 'CoursePress_Data_Discussion', 'before_add_comment' ) ) );
		$this->assertTrue( is_callable( array( 'CoursePress_Data_Discussion', 'after_add_comment' ) ) );
		$this->assertTrue( is_callable( array( 'CoursePress_Data_Discussion', 'init' ) ) );
		$this->assertTrue( is_callable( array( 'CoursePress_Data_Discussion', 'approved_discussion_comment' ) ) );
		$this->assertTrue( is_callable( array( 'CoursePress_Data_Discussion', 'comment_post_types' ) ) );
		$this->assertTrue( is_callable( array( 'CoursePress_Data_Discussion', 'is_comment_in_discussion' ) ) );
		$this->assertTrue( is_callable( array( 'CoursePress_Data_Discussion', 'preprocess_discussion_comment' ) ) );
		$this->assertTrue( is_callable( array( 'CoursePress_Data_Discussion', 'redirect_back' ) ) );
		$this->assertTrue( is_callable( array( 'CoursePress_Data_Discussion', 'is_discussion_subscriber' ) ) );
		$this->assertTrue( is_callable( array( 'CoursePress_Data_Discussion', 'is_discussion_reactions_subscriber' ) ) );
		$this->assertTrue( is_callable( array( 'CoursePress_Data_Discussion', 'is_subscriber' ) ) );
		$this->assertTrue( is_callable( array( 'CoursePress_Data_Discussion', 'is_unsubscribe_link' ) ) );
		$this->assertTrue( is_callable( array( 'CoursePress_Data_Discussion', 'unsubscribe_from_discussion' ) ) );
		$this->assertTrue( is_callable( array( 'CoursePress_Data_Discussion', 'update_user_subscription' ) ) );
		$this->assertTrue( is_callable( array( 'CoursePress_Data_Discussion', 'comment_add_new' ) ) );
		$this->assertTrue( is_callable( array( 'CoursePress_Data_Discussion', 'ajax_update' ) ) );
		$this->assertTrue( is_callable( array( 'CoursePress_Data_Discussion', 'is_correct_post_type' ) ) );
		$this->assertTrue( is_callable( array( 'CoursePress_Data_Discussion', 'wp_list_comments_args' ) ) );
		$this->assertTrue( is_callable( array( 'CoursePress_Data_Discussion', 'comments_template_query_args' ) ) );
		$this->assertTrue( is_callable( array( 'CoursePress_Data_Discussion', 'have_comments' ) ) );
	}

	/**
	 * get_format()
	 */
	public function test_get_format() {
		$keys = array(
			'post_type',
			'post_args' => array(
				'labels' => array(
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
				),
				'public',
				'show_ui',
				'publicly_queryable',
				'capability_type',
				'map_meta_cap',
				'query_var',
			),
		);
		$assert = CoursePress_Data_Discussion::get_format();
		$this->has_keys( $keys, $assert );
	}

	/**
	 * get_post_type_name()
	 */
	public function test_get_post_type_name() {
		$assert = CoursePress_Data_Discussion::get_post_type_name();
		$this->assertInternalType( 'string', $assert );
		$this->assertEquals( 'discussions', $assert );
	}

	/**
	 * attributes( $n_id )
	 */
	public function test_attributes() {
		$keys = array( 'course_id', 'course_title', 'unit_id', 'unit_title' );
		/**
		 * Wrong data
		 */
		$values = $this->get_wrong_values();
		foreach ( $values as $value ) {
			$assert = CoursePress_Data_Discussion::attributes( $value );
			$this->assertInternalType( 'array', $assert );
			$this->assertEquals( array(), $assert );
		}
		/**
		 * Good data
		 */
		foreach ( $this->course->discussions as $discussion ) {
			$assert = CoursePress_Data_Discussion::attributes( $discussion );
			$this->assertInternalType( 'array', $assert );
			$this->has_keys( $keys, $assert );

			$value = $discussion->ID;
			$assert = CoursePress_Data_Discussion::attributes( $value );
			$this->assertInternalType( 'array', $assert );
			$this->has_keys( $keys, $assert );
		}
	}

	/**
	 * get_discussions( $course )
	 */
	public function test_get_discussions() {
		/**
		 * Wrong data
		 */
		$values = $this->get_wrong_values();
		foreach ( $values as $value ) {
			$assert = CoursePress_Data_Discussion::get_discussions( $value );
			$this->assertInternalType( 'array', $assert );
			$this->assertEquals( array(), $assert );
		}
		/**
		 * Good data
		 */
		$value = $this->course->ID;
		$assert = CoursePress_Data_Discussion::get_discussions( $value );
		$this->assertInternalType( 'array', $assert );
		$assert = $assert[0];
		$this->assertInstanceOf( 'WP_Post', $assert );
	}

	/**
	 * permalink( $permalink, $post, $leavename )
	 */
	public function test_permalink() {
		/**
		 * this function do ... nothing ...
		 */
	}

	/**
	 * TODO
	 * update_discussion( $discussion_title = '', $discussion_description = '', $course_id = '', $unit_id = '' )
	 */
	public function test_update_discussion() {
		/**
		 * Wrong data
		 */
		$values = $this->get_wrong_values();
		foreach ( $values as $course_id ) {
			foreach ( $values as $unit_id ) {
				//$assert = CoursePress_Data_Discussion::update_discussion( 'foo', 'bar', $course_id, $unit_id );
			}
		}
		/**
		 * Good data
		 */
		//$assert = CoursePress_Data_Discussion::update_discussion( 'foo', 'bar', $course_id, $unit_id );

	}

	/**
	 * get_one( $post_id = 0 )
	 */
	public function test_get_one() {
		$post = array(
			'ID'		   => 0,
			'post_title'   => '',
			'post_content' => '',
		);
		/**
		 * Wrong data
		 */
		$values = $this->get_wrong_values();
		foreach ( $values as $value ) {
			$assert = CoursePress_Data_Discussion::get_one( $value );
			$this->assertInternalType( 'array', $assert );
			$this->assertEquals( $post, $assert );
		}
		/**
		 * Good data
		 */
		wp_set_current_user( $this->admin->ID );
		foreach ( $this->course->discussions as $value ) {
			$assert = CoursePress_Data_Discussion::get_one( $value );
			$this->assertInternalType( 'array', $assert );
			$this->assertEquals( $value->ID, $assert['ID'] );
			$this->assertEquals( $value->post_title, $assert['post_title'] );
			$this->assertEquals( $value->post_content, $assert['post_content'] );
		}
	}

	/**
	 * before_add_comment( $comment_post_ID, $course_id )
	 */
	public function test_before_add_comment() {
		/**
		 * Wrong data
		 */
		$values = $this->get_wrong_values();
		foreach ( $values as $comment_post_ID ) {
			foreach ( $values as $course_id ) {
				$assert = CoursePress_Data_Discussion::before_add_comment( $comment_post_ID, $course_id );
				$this->assertEmpty( $assert );
			}
		}
		/**
		 * Good data
		 */
		$course_id = $this->course->ID;
		foreach ( $this->course->discussions as $value ) {
			$comment_post_ID = $value->ID;
			$assert = CoursePress_Data_Discussion::before_add_comment( $comment_post_ID, $course_id );
			$this->assertEmpty( $assert );
			/**
			 * TODO add test after adding a comment
			 */
		}
	}

	/**
	 * after_add_comment( $comment_id, $student_id, $comment_post_ID, $course_id )
	 */
	public function test_after_add_comment() {
		/**
		 * Wrong data
		 */
		$values = $this->get_wrong_values();
		foreach ( $values as $comment_id ) {
			foreach ( $values as $student_id ) {
				foreach ( $values as $comment_post_ID ) {
					foreach ( $values as $course_id ) {
						$assert = CoursePress_Data_Discussion::after_add_comment( $comment_id, $student_id, $comment_post_ID, $course_id );
						$this->assertEmpty( $assert );
					}
				}
			}
		}
		/**
		 * Good data
		 * TODO add test after adding a comment
		 */
		$course_id = $this->course->ID;
		foreach ( $this->course->discussions as $value ) {
		}
		//		$assert = CoursePress_Data_Discussion::;
	}

	/**
	 * init()
	 */
	public function test_init() {
		$assert = has_action( 'coursepress_before_add_comment', array( 'CoursePress_Data_Discussion', 'before_add_comment' ) );
		$this->assertEquals( 10, $assert );

		$assert = has_action( 'coursepress_after_add_comment', array( 'CoursePress_Data_Discussion', 'after_add_comment' ) );
		$this->assertEquals( 10, $assert );

		$assert = has_action( 'the_content', array( 'CoursePress_Data_Discussion', 'unsubscribe_from_discussion' ) );
		$this->assertEquals( 10, $assert );

		$assert = has_filter( 'comment_post_redirect', array( 'CoursePress_Data_Discussion', 'redirect_back' ) );
		$this->assertEquals( 10, $assert );

		$assert = has_filter( 'coursepress_no_access_redirect_url', array( 'CoursePress_Data_Discussion', 'is_unsubscribe_link' ) );
		$this->assertEquals( 10, $assert );

		$assert = has_filter( 'wp_list_comments_args', array( 'CoursePress_Data_Discussion', 'wp_list_comments_args' ) );
		$this->assertEquals( 10, $assert );

		$assert = has_filter( 'comments_template_query_args', array( 'CoursePress_Data_Discussion', 'comments_template_query_args' ) );
		$this->assertEquals( 10, $assert );
	}

	/**
	 * TODO
	 * approved_discussion_comment( $is_approved, $commentdata )
	 */
	public function test_approved_discussion_comment() {
		/**
		 * Wrong data
		 */
		$values = $this->get_wrong_values();
		foreach ( $values as $value ) {
		}
		/**
		 * Good data
		 */
		$value = '';
		//		$assert = CoursePress_Data_Discussion::;

	}

	/**
	 * comment_post_types()
	 */
	public function test_comment_post_types() {
		$values = array( 'discussions', 'module', 'unit' );
		$assert = CoursePress_Data_Discussion::comment_post_types();
		$this->assertInternalType( 'array', $assert );
		$this->assertEqualSets( $values, $assert );
	}

	/**
	 * is_comment_in_discussion( $comment_id )
	 */
	public function test_is_comment_in_discussion() {
		/**
		 * Wrong data
		 */
		$values = $this->get_wrong_values();
		foreach ( $values as $value ) {
			$assert = CoursePress_Data_Discussion::is_comment_in_discussion( $value );
			$this->assertInternalType( 'boolean', $assert );
			$this->assertFalse( $assert );
		}
		/**
		 * TODO
		 * Good data
		 */
		$value = '';
		//		$assert = CoursePress_Data_Discussion::;

	}

	/**
	 * TODO
	 * preprocess_discussion_comment( $comment_data )
	 */
	public function test_preprocess_discussion_comment() {
		/**
		 * Wrong data
		 */
		$values = $this->get_wrong_values();
		foreach ( $values as $value ) {
		}
		/**
		 * Good data
		 */
		$value = '';
		//		$assert = CoursePress_Data_Discussion::;

	}

	/**
	 * redirect_back( $location, $comment )
	 */
	public function test_redirect_back() {
		/**
		 * Wrong data
		 */
		$values = $this->get_wrong_values();
		foreach ( $values as $location ) {
			foreach ( $values as $comment ) {
				$assert = CoursePress_Data_Discussion::redirect_back( $location, $comment );
				$this->assertEquals( $location, $assert );
			}
		}
		/**
		 * TODO
		 * Good data
		 */
		$value = '';
		//		$assert = CoursePress_Data_Discussion::;
	}

	/**
	 * is_discussion_subscriber( $user_id, $discussion_id )
	 */
	public function test_is_discussion_subscriber() {
		/**
		 * Wrong data
		 */
		$values = $this->get_wrong_values();
		foreach ( $values as $user_id ) {
			foreach ( $values as $discussion_id ) {
				$assert = CoursePress_Data_Discussion::is_discussion_subscriber( $user_id, $discussion_id );
				$this->assertInternalType( 'boolean', $assert );
				$this->assertFalse( $assert );
			}
		}
		/**
		 * TODO
		 * Good data
		 */
		$value = '';
		//		$assert = CoursePress_Data_Discussion::;

	}

	/**
	 * is_discussion_reactions_subscriber( $user_id, $discussion_id )
	 */
	public function test_is_discussion_reactions_subscriber() {
		/**
		 * Wrong data
		 */
		$values = $this->get_wrong_values();
		foreach ( $values as $user_id ) {
			foreach ( $values as $discussion_id ) {
				$assert = CoursePress_Data_Discussion::is_discussion_reactions_subscriber( $user_id, $discussion_id );
				$this->assertInternalType( 'boolean', $assert );
				$this->assertTrue( $assert );
			}
		}
		/**
		 * Good data
		 * TODO
		 */
		$value = '';
		//		$assert = CoursePress_Data_Discussion::;
	}

	/**
	 * is_subscriber( $user_id, $discussion_id )
	 */
	public function test_is_subscriber() {
		/**
		 * Wrong data
		 */
		$values = $this->get_wrong_values();
		foreach ( $values as $user_id ) {
			foreach ( $values as $discussion_id ) {
				$assert = CoursePress_Data_Discussion::is_subscriber( $user_id, $discussion_id );
				$this->assertInternalType( 'boolean', $assert );
				$this->assertTrue( $assert );
			}
		}
		/**
		 * Good data
		 * TODO
		 */
		$value = '';
		//		$assert = CoursePress_Data_Discussion::;
	}

	/**
	 * is_unsubscribe_link( $course_url, $course_id )
	 */
	public function test_is_unsubscribe_link() {
		/**
		 * Wrong data
		 */
		$values = $this->get_wrong_values();
		foreach ( $values as $course_url ) {
			foreach ( $values as $course_id ) {
				$assert = CoursePress_Data_Discussion::is_unsubscribe_link( $course_url, $course_id );
				$this->assertEquals( $course_url, $assert );
			}
		}
		/**
		 * Good data
		 * TODO
		 */
		$value = '';
		//		$assert = CoursePress_Data_Discussion::;
	}

	/**
	 * unsubscribe_from_discussion( $content )
	 */
	public function test_unsubscribe_from_discussion() {
		/**
		 * Wrong data
		 */
		$values = $this->get_wrong_values();
		foreach ( $values as $value ) {
			foreach ( $values as $user_id ) {
				foreach ( $values as $post_id ) {
					$_GET['uid'] = $user_id;
					$_GET['unsubscribe'] = $post_id;
					$assert = CoursePress_Data_Discussion::unsubscribe_from_discussion( $value );
					$this->assertEquals( $value, $assert );
				}
			}
		}
		/**
		 * TODO
		 * Good data
		 */
		$value = '';
		//		$assert = CoursePress_Data_Discussion::;
	}

	/**
	 * TODO
	 * update_user_subscription( $user_id, $discussion_id, $new_value = false )
	 */
	public function test_update_user_subscription() {
		/**
		 * Wrong data
		 */
		$values = $this->get_wrong_values();
		foreach ( $values as $value ) {
		}
		/**
		 * Good data
		 */
		$value = '';
		//		$assert = CoursePress_Data_Discussion::;

	}

	/**
	 * TODO
	 * comment_add_new( $data, $json_data )
	 */
	public function test_comment_add_new() {
		/**
		 * Wrong data
		 */
		$values = $this->get_wrong_values();
		foreach ( $values as $value ) {
		}
		/**
		 * Good data
		 */
		$value = '';
		//		$assert = CoursePress_Data_Discussion::;

	}

	/**
	 * TODO
	 * ajax_update()
	 */
	public function test_ajax_update() {
		/**
		 * Wrong data
		 */
		$values = $this->get_wrong_values();
		foreach ( $values as $value ) {
		}
		/**
		 * Good data
		 */
		$value = '';
		//		$assert = CoursePress_Data_Discussion::;
	}

	/**
	 * is_correct_post_type( $post )
	 */
	public function test_is_correct_post_type() {
		/**
		 * Wrong data
		 */
		$values = $this->get_wrong_values();
		foreach ( $values as $value ) {
			$assert = CoursePress_Data_Discussion::is_correct_post_type( $value );
			$this->assertInternalType( 'boolean', $assert );
			$this->assertFalse( $assert );
		}
		/**
		 * Good data
		 */
		foreach ( $this->course->discussions as $discussion ) {
			$assert = CoursePress_Data_Discussion::is_correct_post_type( $discussion );
			$this->assertInternalType( 'boolean', $assert );
			$this->assertTrue( $assert );
		}
	}

	/**
	 * wp_list_comments_args( $args )
	 */
	public function test_wp_list_comments_args() {
		/**
		 * Wrong data
		 */
		$values = $this->get_wrong_values();
		foreach ( $values as $value ) {
			$assert = CoursePress_Data_Discussion::wp_list_comments_args( $value );
			$this->assertEquals( $value, $assert );
		}
		/**
		 * Good data
		 */
		foreach ( $this->course->discussions as $value ) {
			$assert = CoursePress_Data_Discussion::wp_list_comments_args( $value );
			$this->assertInstanceOf( 'WP_Post', $assert );
			$this->assertEquals( $value, $assert );
		}
	}

	/**
	 * comments_template_query_args( $args )
	 */
	public function test_comments_template_query_args() {
		/**
		 * Wrong data
		 */
		$values = $this->get_wrong_values();
		foreach ( $values as $value ) {
			$assert = CoursePress_Data_Discussion::comments_template_query_args( $value );
			$this->assertInternalType( 'array', $assert );
			$this->assertArrayHasKey( 'number', $assert );
			$this->assertEquals( 20, $assert['number'] );
		}
		/**
		 * Good data
		 */
		$value = array();
		$assert = CoursePress_Data_Discussion::comments_template_query_args( $value );
		$this->assertInternalType( 'array', $assert );
		$this->assertArrayHasKey( 'number', $assert );
		$this->assertEquals( 20, $assert['number'] );
	}

	/**
	 * have_comments( $student_id, $post_id )
	 */
	public function test_have_comments() {
		/**
		 * Wrong data
		 */
		$values = $this->get_wrong_values();
		foreach ( $values as $student_id ) {
			foreach ( $values as $post_id ) {
				$assert = CoursePress_Data_Discussion::have_comments( $student_id, $post_id );
				$this->assertInternalType( 'boolean', $assert );
				$this->assertFalse( $assert );
			}
		}
		/**
		 * Good data
		 */
		$value = '';
		foreach ( $this->course->discussions as $post_id ) {
			$assert = CoursePress_Data_Discussion::have_comments( $this->student->ID, $post_id );
			$this->assertInternalType( 'boolean', $assert );
			$this->assertFalse( $assert );
		}
	}
}
/**
print_r( array( gettype( $assert ), $assert ) );
 */
