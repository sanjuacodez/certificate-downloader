<?php

namespace SJS_Cert\Core;

/**
 * Fired during plugin activation.
 */
class Activator {

	/**
	 * Short Description. (use period)
	 *
	 * Long Description.
	 *
	 * @since    1.0.0
	 */
	public static function activate() {
		self::create_tables();
	}

	/**
	 * Create custom database tables.
	 */
	private static function create_tables() {
		global $wpdb;

		$charset_collate = $wpdb->get_charset_collate();

		$table_students = $wpdb->prefix . 'cert_students';
		$table_courses  = $wpdb->prefix . 'cert_courses';

		$sql_students = "CREATE TABLE $table_students (
			id bigint(20) NOT NULL AUTO_INCREMENT,
			admission_number varchar(50) NOT NULL,
			full_name varchar(255) NOT NULL,
			email varchar(100) NOT NULL,
			phone varchar(20) NOT NULL,
			course_id bigint(20) NOT NULL,
			issue_status enum('pending', 'issued') DEFAULT 'pending' NOT NULL,
			photo_url varchar(255) DEFAULT '' NOT NULL,
			download_count int(11) DEFAULT 0 NOT NULL,
			created_at datetime DEFAULT '0000-00-00 00:00:00' NOT NULL,
			updated_at datetime DEFAULT '0000-00-00 00:00:00' NOT NULL,
			PRIMARY KEY  (id),
			UNIQUE KEY admission_number (admission_number)
		) $charset_collate;";

		$sql_courses = "CREATE TABLE $table_courses (
			id bigint(20) NOT NULL AUTO_INCREMENT,
			course_name varchar(255) NOT NULL,
			template_config longtext NOT NULL,
			PRIMARY KEY  (id)
		) $charset_collate;";

		require_once ABSPATH . 'wp-admin/includes/upgrade.php';
		dbDelta( $sql_students );
		dbDelta( $sql_courses );
	}
}
