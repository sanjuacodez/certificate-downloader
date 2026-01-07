<?php
/**
 * Student Model
 *
 * Handles CRUD operations for student records in the cert_students table.
 * Provides methods for creating, reading, updating, and deleting student data,
 * as well as querying student statistics.
 *
 * @package SJS_Cert
 * @subpackage Models
 * @since 1.0.0
 */

namespace SJS_Cert\Model;

/**
 * Class Student
 *
 * Database model for student records with certificate issuance tracking.
 */
class Student {

	/**
	 * Database table name for students
	 *
	 * @var string
	 */
	private $table_name;

	/**
	 * Constructor
	 *
	 * Initializes the student model with the WordPress database table name.
	 * Uses the WordPress global $wpdb object for database operations.
	 *
	 * @since 1.0.0
	 * @global wpdb $wpdb WordPress database abstraction object.
	 */
	public function __construct() {
		global $wpdb;
		$this->table_name = $wpdb->prefix . 'cert_students';
	}

	/**
	 * Insert a new student record
	 *
	 * Creates a new student in the database with the provided data.
	 * Automatically sets created_at timestamp if not provided.
	 *
	 * @since 1.0.0
	 * @global wpdb $wpdb WordPress database abstraction object.
	 *
	 * @param array $data {
	 *     Student data to insert.
	 *
	 *     @type string $admission_number Unique student identifier (required).
	 *     @type string $full_name        Student's full name (required).
	 *     @type string $email            Student's email address (required).
	 *     @type string $phone            Phone number (optional).
	 *     @type int    $course_id        Foreign key to cert_courses table (required).
	 *     @type string $issue_status     Certificate status: 'pending' or 'issued' (default: 'pending').
	 *     @type string $photo_url        URL to student photo (optional).
	 *     @type string $created_at       Creation timestamp (auto-generated if not provided).
	 * }
	 *
	 * @return int|false The inserted student ID on success, false on error.
	 */
	public function create( $data ) {
		global $wpdb;

		$defaults = array(
			'admission_number' => '',
			'full_name'        => '',
			'email'            => '',
			'phone'            => '',
			'course_id'        => 0,
			'issue_status'     => 'pending',
			'created_at'       => current_time( 'mysql' ),
		);

		$data = wp_parse_args( $data, $defaults );

		$result = $wpdb->insert(
			$this->table_name,
			$data,
			array( '%s', '%s', '%s', '%s', '%d', '%s', '%s' )
		);

		return $result ? $wpdb->insert_id : false;
	}

	/**
	 * Get student by admission number
	 *
	 * Retrieves a single student record matching the provided admission number.
	 * Used for validation and certificate lookup operations.
	 *
	 * @since 1.0.0
	 * @global wpdb $wpdb WordPress database abstraction object.
	 *
	 * @param string $admission_number Student's unique admission number.
	 * @return object|null Student object on success, null if not found.
	 */
	public function get_by_admission_number( $admission_number ) {
		global $wpdb;
		return $wpdb->get_row( $wpdb->prepare( "SELECT * FROM $this->table_name WHERE admission_number = %s", $admission_number ) );
	}

	/**
	 * Update student data
	 *
	 * Updates specific fields for an existing student record.
	 * Commonly used for changing certificate status or incrementing download count.
	 *
	 * @since 1.0.0
	 * @global wpdb $wpdb WordPress database abstraction object.
	 *
	 * @param int   $id   Student ID to update.
	 * @param array $data Associative array of field => value pairs to update.
	 * @return int|false Number of rows updated (0 or 1), or false on error.
	 */
	public function update( $id, $data ) {
		global $wpdb;
		return $wpdb->update(
			$this->table_name,
			$data,
			array( 'id' => $id ),
			null,
			array( '%d' )
		);
	}

	/**
	 * Delete a student
	 *
	 * Permanently removes a student from the database.
	 * This action cannot be undone.
	 *
	 * @since 1.0.0
	 * @global wpdb $wpdb WordPress database abstraction object.
	 *
	 * @param int $id Student ID to delete.
	 * @return int|false Number of rows deleted (0 or 1), or false on error.
	 */
	public function delete( $id ) {
		global $wpdb;
		return $wpdb->delete(
			$this->table_name,
			array( 'id' => $id ),
			array( '%d' )
		);
	}

	/**
	 * Retrieves all student records from the database.
	 * Returns students ordered by creation date (newest first).
	 * Note: This method loads all records into memory. For large datasets,
	 * consider using WP_List_Table with pagination instead.
	 *
	 * @since 1.0.0
	 * @global wpdb $wpdb WordPress database abstraction object.
	 *
	 * @param array $args Optional. Query arguments for filtering (currently unused, reserved for future expansion).
	 * @return array Array of student objects.
	 */
	public function get_all( $args = array() ) {
		global $wpdb;
		return $wpdb->get_results( "SELECT * FROM $this->table_name ORDER BY created_at DESC" );
	}

	/**
	 * Get student statistics
	 *
	 * Aggregates data across all students to provide dashboard statistics.
	 * Calculates total students, certificate issuance status counts,
	 * and total download count.
	 *
	 * @since 1.0.0
	 * @global wpdb $wpdb WordPress database abstraction object.
	 *
	 * @return object {
	 *     Statistics object with aggregated student data.
	 *
	 *     @type int $total_students Total number of student records.
	 *     @type int $issued         Number of students with issued certificates.
	 *     @type int $pending        Number of students with pending certificates.
	 *     @type int $downloads      Sum of all download_count fields.
	 * }
	 */
	public function get_stats() {
		global $wpdb;
		
		$total_students = $wpdb->get_var( "SELECT COUNT(id) FROM $this->table_name" );
		$issued         = $wpdb->get_var( "SELECT COUNT(id) FROM $this->table_name WHERE issue_status = 'issued'" );
		$pending        = $wpdb->get_var( "SELECT COUNT(id) FROM $this->table_name WHERE issue_status = 'pending'" );
		$downloads      = $wpdb->get_var( "SELECT SUM(download_count) FROM $this->table_name" );

		return (object) array(
			'total_students' => intval( $total_students ),
			'issued'         => intval( $issued ),
			'pending'        => intval( $pending ),
			'downloads'      => intval( $downloads ),
		);
	}
}
