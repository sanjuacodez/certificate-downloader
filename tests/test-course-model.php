<?php
/**
 * Course Model Tests
 *
 * Tests for the Course model CRUD operations and caching.
 *
 * @package SJS_Cert
 * @subpackage Tests
 */

namespace SJS_Cert\Tests;

use SJS_Cert\Model\Course;
use WP_UnitTestCase;

/**
 * Class Test_Course_Model
 *
 * @group models
 */
class Test_Course_Model extends WP_UnitTestCase {

	/**
	 * Course model instance
	 *
	 * @var Course
	 */
	private $course_model;

	/**
	 * Setup test environment
	 */
	public function setUp() {
		parent::setUp();
		$this->course_model = new Course();
	}

	/**
	 * Test course creation
	 */
	public function test_create_course() {
		$course_data = array(
			'course_name'     => 'Test Course',
			'template_config' => json_encode( array( 'test' => 'value' ) ),
		);

		$result = $this->course_model->create( $course_data );

		$this->assertNotFalse( $result );
	}

	/**
	 * Test get course by ID
	 */
	public function test_get_course() {
		// Create a test course
		$this->course_model->create( array(
			'course_name'     => 'Test Course 2',
			'template_config' => json_encode( array( 'key' => 'value' ) ),
		) );

		// Get the course (should hit cache on second call)
		$course = $this->course_model->get( 1 );

		$this->assertIsObject( $course );
		$this->assertEquals( 'Test Course 2', $course->course_name );
	}

	/**
	 * Test course caching
	 */
	public function test_course_caching() {
		// Create a course
		$this->course_model->create( array(
			'course_name'     => 'Cached Course',
			'template_config' => '{}',
		) );

		// First call - cache miss
		$course1 = $this->course_model->get( 1 );

		// Second call - should hit cache
		$course2 = $this->course_model->get( 1 );

		$this->assertEquals( $course1->course_name, $course2->course_name );

		// Verify transient exists
		$cache_key = 'sjs_cert_course_1';
		$cached = get_transient( $cache_key );
		$this->assertNotFalse( $cached );
	}

	/**
	 * Test update course
	 */
	public function test_update_course() {
		// Create a course
		$this->course_model->create( array(
			'course_name'     => 'Original Name',
			'template_config' => '{}',
		) );

		// Update the course
		$result = $this->course_model->update( 1, array(
			'course_name' => 'Updated Name',
		) );

		$this->assertNotFalse( $result );

		// Verify cache was cleared
		$cache_key = 'sjs_cert_course_1';
		$cached = get_transient( $cache_key );
		// Cache should be cleared after update
		$this->assertFalse( $cached );

		// Verify the update
		$course = $this->course_model->get( 1 );
		$this->assertEquals( 'Updated Name', $course->course_name );
	}

	/**
	 * Test delete course
	 */
	public function test_delete_course() {
		// Create a course
		$this->course_model->create( array(
			'course_name'     => 'To Be Deleted',
			'template_config' => '{}',
		) );

		$course_id = 1;

		// Delete the course
		$result = $this->course_model->delete( $course_id );

		$this->assertNotFalse( $result );

		// Verify cache was cleared
		$cache_key = 'sjs_cert_course_' . $course_id;
		$cached = get_transient( $cache_key );
		$this->assertFalse( $cached );

		// Verify deletion
		$course = $this->course_model->get( $course_id );
		$this->assertNull( $course );
	}

	/**
	 * Test get course with normalized config
	 */
	public function test_get_with_normalized_config() {
		// Create a course with old config format
		$this->course_model->create( array(
			'course_name'     => 'Config Test',
			'template_config' => json_encode( array(
				'main_heading' => 'Certificate',
				'orientation'  => 'landscape',
			) ),
		) );

		$course = $this->course_model->get_with_normalized_config( 1 );

		$this->assertIsObject( $course );
		$this->assertIsArray( $course->template_config );
		$this->assertArrayHasKey( 'schema_version', $course->template_config );
		$this->assertEquals( '1.0.0', $course->template_config['schema_version'] );
	}

	/**
	 * Test normalize config with empty array
	 */
	public function test_normalize_empty_config() {
		$normalized = $this->course_model->normalize_config( array() );

		$this->assertIsArray( $normalized );
		$this->assertArrayHasKey( 'schema_version', $normalized );
		$this->assertEquals( '1.0.0', $normalized['schema_version'] );
		$this->assertArrayHasKey( 'meta', $normalized );
		$this->assertArrayHasKey( 'layout', $normalized );
	}

	/**
	 * Test normalize config with current schema version
	 */
	public function test_normalize_current_schema() {
		$config = array( 'schema_version' => '1.0.0', 'data' => 'test' );
		$normalized = $this->course_model->normalize_config( $config );

		// Should return unchanged if already at current version
		$this->assertEquals( $config, $normalized );
	}

	/**
	 * Cleanup after tests
	 */
	public function tearDown() {
		parent::tearDown();
	}
}
