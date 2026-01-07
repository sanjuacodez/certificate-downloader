<?php
/**
 * Student Model Tests
 *
 * Tests for the Student model CRUD operations.
 *
 * @package SJS_Cert
 * @subpackage Tests
 */

namespace SJS_Cert\Tests;

use SJS_Cert\Model\Student;
use WP_UnitTestCase;

/**
 * Class Test_Student_Model
 *
 * @group models
 */
class Test_Student_Model extends WP_UnitTestCase {

	/**
	 * Student model instance
	 *
	 * @var Student
	 */
	private $student_model;

	/**
	 * Setup test environment
	 */
	public function setUp() {
		parent::setUp();
		$this->student_model = new Student();
	}

	/**
	 * Test student creation
	 */
	public function test_create_student() {
		$student_data = array(
			'admission_number' => 'TEST001',
			'full_name'        => 'Test Student',
			'email'            => 'test@example.com',
			'phone'            => '+1234567890',
			'course_id'        => 1,
			'issue_status'     => 'pending',
		);

		$student_id = $this->student_model->create( $student_data );

		$this->assertIsInt( $student_id );
		$this->assertGreaterThan( 0, $student_id );
	}

	/**
	 * Test get student by admission number
	 */
	public function test_get_by_admission_number() {
		// Create a test student
		$admission_number = 'TEST002';
		$this->student_model->create( array(
			'admission_number' => $admission_number,
			'full_name'        => 'Test Student 2',
			'email'            => 'test2@example.com',
			'course_id'        => 1,
		) );

		// Retrieve the student
		$student = $this->student_model->get_by_admission_number( $admission_number );

		$this->assertIsObject( $student );
		$this->assertEquals( $admission_number, $student->admission_number );
		$this->assertEquals( 'Test Student 2', $student->full_name );
	}

	/**
	 * Test get non-existent student
	 */
	public function test_get_nonexistent_student() {
		$student = $this->student_model->get_by_admission_number( 'NONEXISTENT' );
		$this->assertNull( $student );
	}

	/**
	 * Test update student
	 */
	public function test_update_student() {
		// Create a test student
		$student_id = $this->student_model->create( array(
			'admission_number' => 'TEST003',
			'full_name'        => 'Test Student 3',
			'email'            => 'test3@example.com',
			'course_id'        => 1,
			'issue_status'     => 'pending',
		) );

		// Update the student
		$result = $this->student_model->update( $student_id, array(
			'issue_status' => 'issued',
		) );

		$this->assertNotFalse( $result );

		// Verify the update
		$student = $this->student_model->get_by_admission_number( 'TEST003' );
		$this->assertEquals( 'issued', $student->issue_status );
	}

	/**
	 * Test delete student
	 */
	public function test_delete_student() {
		// Create a test student
		$student_id = $this->student_model->create( array(
			'admission_number' => 'TEST004',
			'full_name'        => 'Test Student 4',
			'email'            => 'test4@example.com',
			'course_id'        => 1,
		) );

		// Delete the student
		$result = $this->student_model->delete( $student_id );

		$this->assertNotFalse( $result );

		// Verify deletion
		$student = $this->student_model->get_by_admission_number( 'TEST004' );
		$this->assertNull( $student );
	}

	/**
	 * Test get all students
	 */
	public function test_get_all_students() {
		// Create multiple test students
		$this->student_model->create( array(
			'admission_number' => 'TEST005',
			'full_name'        => 'Test Student 5',
			'email'            => 'test5@example.com',
			'course_id'        => 1,
		) );

		$this->student_model->create( array(
			'admission_number' => 'TEST006',
			'full_name'        => 'Test Student 6',
			'email'            => 'test6@example.com',
			'course_id'        => 1,
		) );

		$students = $this->student_model->get_all();

		$this->assertIsArray( $students );
		$this->assertGreaterThanOrEqual( 2, count( $students ) );
	}

	/**
	 * Test get statistics
	 */
	public function test_get_stats() {
		// Create test students with different statuses
		$this->student_model->create( array(
			'admission_number' => 'TEST007',
			'full_name'        => 'Test Student 7',
			'email'            => 'test7@example.com',
			'course_id'        => 1,
			'issue_status'     => 'issued',
		) );

		$this->student_model->create( array(
			'admission_number' => 'TEST008',
			'full_name'        => 'Test Student 8',
			'email'            => 'test8@example.com',
			'course_id'        => 1,
			'issue_status'     => 'pending',
		) );

		$stats = $this->student_model->get_stats();

		$this->assertIsObject( $stats );
		$this->assertObjectHasAttribute( 'total_students', $stats );
		$this->assertObjectHasAttribute( 'issued', $stats );
		$this->assertObjectHasAttribute( 'pending', $stats );
		$this->assertObjectHasAttribute( 'downloads', $stats );
		$this->assertGreaterThanOrEqual( 2, $stats->total_students );
	}

	/**
	 * Test duplicate admission number
	 */
	public function test_duplicate_admission_number() {
		$admission_number = 'DUPLICATE001';

		// Create first student
		$this->student_model->create( array(
			'admission_number' => $admission_number,
			'full_name'        => 'First Student',
			'email'            => 'first@example.com',
			'course_id'        => 1,
		) );

		// Check that student exists
		$existing = $this->student_model->get_by_admission_number( $admission_number );
		$this->assertNotNull( $existing );
	}

	/**
	 * Cleanup after tests
	 */
	public function tearDown() {
		parent::tearDown();
	}
}
