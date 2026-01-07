<?php
/**
 * Course Model
 *
 * Manages course records and certificate template configurations.
 * Handles CRUD operations for courses and provides methods for
 * template configuration normalization and migration.
 *
 * @package SJS_Cert
 * @subpackage Models
 * @since 1.0.0
 */

namespace SJS_Cert\Model;

/**
 * Class Course
 *
 * Database model for course records with JSON-based template configuration.
 * Supports schema versioning and automatic migration of template configs.
 */
class Course {

	/**
	 * Database table name for courses
	 *
	 * @var string
	 */
	private $table_name;

	/**
	 * Constructor
	 *
	 * Initializes the course model with the WordPress database table name.
	 *
	 * @since 1.0.0
	 * @global wpdb $wpdb WordPress database abstraction object.
	 */
	public function __construct() {
		global $wpdb;
		$this->table_name = $wpdb->prefix . 'cert_courses';
	}

	/**
	 * Insert a new course record
	 *
	 * Creates a new course with the provided data. The template_config
	 * should be a JSON string representing the certificate template design.
	 *
	 * @since 1.0.0
	 * @global wpdb $wpdb WordPress database abstraction object.
	 *
	 * @param array $data {
	 *     Course data to insert.
	 *
	 *     @type string $course_name     Course title/name (required).
	 *     @type string $template_config JSON string of template configuration (default: '{}').
	 * }
	 *
	 * @return int|false Number of rows inserted (1), or false on error.
	 */
	public function create( $data ) {
		global $wpdb;

		$defaults = array(
			'course_name'     => '',
			'template_config' => '{}',
		);

		$data = wp_parse_args( $data, $defaults );

		return $wpdb->insert(
			$this->table_name,
			$data,
			array( '%s', '%s' )
		);
	}

	/**
	 * Get course by ID
	 *
	 * Retrieves a single course record including its template configuration.
	 * The template_config field is returned as a JSON string.
	 *
	 * Uses WordPress transient caching to reduce database queries for
	 * frequently accessed courses. Cache duration: 12 hours.
	 *
	 * @since 1.0.0
	 * @global wpdb $wpdb WordPress database abstraction object.
	 *
	 * @param int $id Course ID to retrieve.
	 * @return object|null Course object on success, null if not found.
	 */
	public function get( $id ) {
		// Check cache first
		$cache_key = 'sjs_cert_course_' . $id;
		$course = get_transient( $cache_key );
		
		if ( false === $course ) {
			// Cache miss, fetch from database
			global $wpdb;
			$course = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM $this->table_name WHERE id = %d", $id ) );
			
			// Store in cache for 12 hours (43200 seconds)
			if ( $course ) {
				set_transient( $cache_key, $course, 12 * HOUR_IN_SECONDS );
			}
		}
		
		return $course;
	}

	/**
	 * Update course data
	 *
	 * Updates specific fields for an existing course record.
	 * Commonly used to save template configuration changes.
	 * Automatically clears the cache for this course.
	 *
	 * @since 1.0.0
	 * @global wpdb $wpdb WordPress database abstraction object.
	 *
	 * @param int   $id   Course ID to update.
	 * @param array $data Associative array of field => value pairs to update.
	 * @return int|false Number of rows updated (0 or 1), or false on error.
	 */
	public function update( $id, $data ) {
		global $wpdb;
		$result = $wpdb->update(
			$this->table_name,
			$data,
			array( 'id' => $id ),
			null,
			array( '%d' )
		);
		
		// Clear cache after update
		if ( $result !== false ) {
			$this->clear_cache( $id );
		}
		
		return $result;
	}

	/**
	 * Delete a course record
	 *
	 * Permanently removes a course from the database.
	 * Note: This does not check for students assigned to this course.
	 * Consider adding foreign key constraints or validation before deletion.
	 * Automatically clears the cache for this course.
	 *
	 * @since 1.0.0
	 * @global wpdb $wpdb WordPress database abstraction object.
	 *
	 * @param int $id Course ID to delete.
	 * @return int|false Number of rows deleted (0 or 1), or false on error.
	 */
	public function delete( $id ) {
		global $wpdb;
		$result = $wpdb->delete(
			$this->table_name,
			array( 'id' => $id ),
			array( '%d' )
		);
		
		// Clear cache after deletion
		if ( $result !== false ) {
			$this->clear_cache( $id );
		}
		
		return $result;
	}

	/**
	 * Clear cache for a specific course
	 *
	 * Removes the transient cache for a given course ID.
	 * Should be called after any update or delete operation.
	 *
	 * @since 1.0.0
	 *
	 * @param int $id Course ID whose cache should be cleared.
	 * @return bool True if cache was cleared, false otherwise.
	 */
	private function clear_cache( $id ) {
		$cache_key = 'sjs_cert_course_' . $id;
		return delete_transient( $cache_key );
	}

	/**
	 * Get course with normalized template configuration
	 *
	 * Retrieves a course and automatically migrates its template_config
	 * to the latest schema version. The template_config is returned as
	 * an associative array instead of a JSON string.
	 *
	 * @since 1.0.0
	 *
	 * @param int $id Course ID to retrieve.
	 * @return object|null Course object with normalized template_config array, or null if not found.
	 */
	public function get_with_normalized_config( $id ) {
		$course = $this->get( $id );
		if ( $course ) {
			$course->template_config = $this->normalize_config( json_decode( $course->template_config, true ) );
		}
		return $course;
	}

	/**
	 * Normalize configuration to the latest schema version
	 *
	 * Migrates older template configurations to the current schema (v1.0.0).
	 * Handles backward compatibility by mapping old field names to new structure.
	 * This method ensures all certificate templates work with the latest editor.
	 *
	 * @since 1.0.0
	 *
	 * @param array|mixed $config Template configuration (can be array or any type).
	 * @return array Normalized configuration array with schema_version 1.0.0.
	 */
	public function normalize_config( $config ) {
		if ( ! is_array( $config ) ) {
			$config = array();
		}

		if ( isset( $config['schema_version'] ) && $config['schema_version'] === '1.0.0' ) {
			return $config;
		}

		// Helper to safely get value from nested config or fallback location
		$get_val = function($arr, $keys, $fallback_key, $default) {
			// Try nested keys first (e.g. ['layout', 'orientation'])
			$current = $arr;
			$found_deep = true;
			foreach ($keys as $k) {
				if ( isset( $current[$k] ) ) {
					$current = $current[$k];
				} else {
					$found_deep = false;
					break;
				}
			}
			if ( $found_deep ) return $current;

			// Try flat fallback key
			if ( $fallback_key && isset( $arr[$fallback_key] ) ) return $arr[$fallback_key];

			return $default;
		};

		// Migration Logic / Normalization
		$normalized = array(
			'schema_version' => '1.0.0',
			'meta' => array(
				'certificate_title' => $get_val($config, ['meta', 'certificate_title'], 'main_heading', 'Certificate of Completion'),
				'certificate_type' => 'completion',
			),
			'layout' => array(
				'orientation' => $get_val($config, ['layout', 'orientation'], 'orientation', 'landscape'),
				'page_size' => $get_val($config, ['layout', 'page_size'], 'page_size', 'A4'),
				'mode' => $get_val($config, ['layout', 'layout_mode'], 'layout_mode', 'distribute'),
				'content_padding' => intval($get_val($config, ['layout', 'content_padding'], 'content_padding', 40)),
				'sidebar_bg' => $get_val($config, ['layout', 'sidebar_bg'], 'sidebar_bg', '#f0f0f0'),
				'sidebar_width' => intval($get_val($config, ['layout', 'sidebar_width'], 'sidebar_width', 30)),
				'section_spacing' => 20
			),
			'background' => array(
				'background_color' => $get_val($config, ['background', 'background_color'], 'background_color', '#ffffff'),
				'background_image_url' => $get_val($config, ['background', 'background_image_url'], 'background_image_url', ''),
				'background_opacity' => floatval($get_val($config, ['background', 'background_opacity'], 'background_opacity', 1.0))
			),
			'border' => array(
				'outer_border' => array(
					'style' => $get_val($config, ['border', 'outer_border', 'style'], 'border_style', 'none'),
					'color' => $get_val($config, ['border', 'outer_border', 'color'], 'border_color', '#cca43b'),
					'width' => intval($get_val($config, ['border', 'outer_border', 'width'], 'border_width', 8)),
					'radius' => intval($get_val($config, ['border', 'outer_border', 'radius'], 'border_radius', 0))
				),
				'inner_border' => array(
					'enabled' => $get_val($config, ['border', 'inner_border', 'enabled'], 'inner_border_enabled', false),
					'style' => $get_val($config, ['border', 'inner_border', 'style'], 'inner_border_style', 'solid'),
					'color' => $get_val($config, ['border', 'inner_border', 'color'], 'inner_border_color', '#e6d8a8'),
					'width' => intval($get_val($config, ['border', 'inner_border', 'width'], 'inner_border_width', 3))
				)
			),
			'header' => array(
				'logo_url' => $get_val($config, ['header', 'logo_url'], 'logo_url', ''),
				'logo_height' => intval($get_val($config, ['header', 'logo_height'], 'logo_height', 80)),
				'logo_alignment' => $get_val($config, ['header', 'logo_alignment'], 'logo_alignment', 'center'),
				'show_title' => true
			),
			'typography' => array(
				'heading_font' => array(
					'family' => $get_val($config, ['typography', 'heading_font', 'family'], 'heading_font_family', 'Times-Roman'),
					'size' => intval($get_val($config, ['typography', 'heading_font', 'size'], 'heading_font_size', 40)),
					'color' => $get_val($config, ['typography', 'heading_font', 'color'], 'secondary_color', '#000000')
				),
				'name_font' => array(
					'family' => $get_val($config, ['typography', 'name_font', 'family'], 'name_font_family', 'Times New Roman'),
					'size' => intval($get_val($config, ['typography', 'name_font', 'size'], 'name_font_size', 32)),
					'color' => $get_val($config, ['typography', 'name_font', 'color'], 'primary_color', '#000000'),
					'font_style' => $get_val($config, ['typography', 'name_font', 'font_style'], 'name_font_style', 'italic'),
					'text_decoration' => $get_val($config, ['typography', 'name_font', 'text_decoration'], 'name_font_decoration', 'none')
				),
				'body_font' => array(
					'family' => $get_val($config, ['typography', 'body_font', 'family'], 'font_family', 'Helvetica'),
					'size' => intval($get_val($config, ['typography', 'body_font', 'size'], 'body_font_size', 18)),
					'color' => $get_val($config, ['typography', 'body_font', 'color'], 'primary_color', '#333333')
				)
			),
			'content_blocks' => $get_val($config, ['content_blocks'], 'content_blocks', array(
				'pre_title' => array('text' => 'This is to certify that', 'alignment' => 'center'),
				'student_name' => array('text' => '[student_name]', 'alignment' => 'center'),
				'main_text' => array('text' => 'has successfully completed the course', 'alignment' => 'center'),
				'course_name' => array('text' => '[course_name]', 'alignment' => 'center'),
				'description' => array('text' => 'with dedication and excellence.', 'alignment' => 'center')
			)),
			'student_photo' => array(
				'enabled' => $get_val($config, ['student_photo', 'enable_student_photo'], 'enable_student_photo', false) == '1' || $get_val($config, ['student_photo', 'enable_student_photo'], 'enable_student_photo', false) === true,
				'shape' => (intval($get_val($config, ['student_photo', 'photo_border_radius'], 'photo_border_radius', 0)) > 0) ? 'circle' : 'square',
				'size' => intval($get_val($config, ['student_photo', 'photo_size'], 'photo_size', 120)),
				'position' => 'left'
			),
			'seal_badge' => array(
				'enabled' => $get_val($config, ['seal_badge', 'enabled'], 'seal_enabled', false),
				'image_url' => $get_val($config, ['seal_badge', 'image_url'], 'seal_image_url', ''),
				'text' => $get_val($config, ['seal_badge', 'text'], 'seal_text', 'Awarded ' . date('Y')),
				'size' => intval($get_val($config, ['seal_badge', 'size'], 'seal_size', 100)),
				'position' => $get_val($config, ['seal_badge', 'position'], 'seal_position', 'bottom_center')
			),
			'qr_code' => array(
				'enabled' => $get_val($config, ['qr_code', 'enabled'], 'qr_enabled', false),
				'size' => intval($get_val($config, ['qr_code', 'size'], 'qr_size', 80)),
				'position' => $get_val($config, ['qr_code', 'position'], 'qr_position', 'bottom_right')
			),
			'signatures' => $get_val($config, ['signatures'], 'signatures', array()),
			'footer' => array(
				'issue_date' => array(
					'label' => $get_val($config, ['footer', 'issue_date', 'label'], 'date_label', 'Date'),
					'value' => '[completion_date]'
				),
				'organization_name' => $get_val($config, ['footer', 'organization_name'], 'organization_name', '[organization_name]'),
				'footer_logo_url' => $get_val($config, ['footer', 'footer_logo_url'], 'footer_logo_url', ''),
				'footer_logo_height' => intval($get_val($config, ['footer', 'footer_logo_height'], 'footer_logo_height', 60)),
				'footer_logo_alignment' => $get_val($config, ['footer', 'footer_logo_alignment'], 'footer_logo_alignment', 'center')
			),
			'output' => array(
				'file_format' => 'pdf',
				'dpi' => 300,
				'file_name_pattern' => 'certificate-[student_name]-[course_name]'
			)
		);

		return $normalized;
	}

	public function get_all() {
		global $wpdb;
		return $wpdb->get_results( "SELECT * FROM $this->table_name ORDER BY course_name ASC" );
	}
}
