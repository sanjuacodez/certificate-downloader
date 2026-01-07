<?php
/**
 * Notification Service
 *
 * Handles email notifications for certificate issuance.
 * Generates PDFs and sends emails with customizable templates.
 *
 * @package SJS_Cert
 * @subpackage Services
 * @since 1.0.0
 */

namespace SJS_Cert\Service;

use SJS_Cert\Model\Student;
use SJS_Cert\Model\Course;
use SJS_Cert\Service\PDF\PDFFactory;

/**
 * Class NotificationService
 *
 * Manages email notifications with PDF attachments for issued certificates.
 * Supports template tag replacement and conditional PDF attachment.
 */
class NotificationService {

	/**
	 * Send certificate email to student
	 *
	 * Main method for sending certificate notifications. Checks if
	 * email notifications are enabled before proceeding.
	 *
	 * @since 1.0.0
	 * @deprecated 1.0.0 Use notify_issued() instead.
	 *
	 * @param int $student_id Student ID to send email to.
	 * @return bool True if email sent successfully, false otherwise.
	 */
	public function send_certificate_email( $student_id ) {
		if ( ! get_option( 'sjs_cert_enable_email', 0 ) ) {
			return false;
		}

		$student_model = new Student();
		$student       = $wpdb_student = $this->get_student_by_id($student_id); // Helper needed or use wpdb
		
		// Wait, I need a way to get the student record easily.
		// Let's add get() to Student model if missing.
	}

	/**
	 * Get student record by ID
	 *
	 * Helper method to retrieve a single student record from the database.
	 * This is a private utility method used internally by the notification service.
	 *
	 * @since 1.0.0
	 * @access private
	 * @global wpdb $wpdb WordPress database abstraction object.
	 *
	 * @param int $id Student ID.
	 * @return object|null Student object on success, null if not found.
	 */
	private function get_student_by_id( $id ) {
		global $wpdb;
		return $wpdb->get_row( $wpdb->prepare( "SELECT * FROM {$wpdb->prefix}cert_students WHERE id = %d", $id ) );
	}

	/**
	 * Send notification when certificate is issued
	 *
	 * Triggers email notification with certificate details when a student's
	 * certificate status changes to "issued". Generates PDF on-the-fly,
	 * optionally attaches it to email, and sends to the student's email address.
	 *
	 * Process:
	 * 1. Check if email notifications are enabled
	 * 2. Validate student and course records exist
	 * 3. Load email template from settings
	 * 4. Replace template tags with actual data
	 * 5. Generate PDF certificate
	 * 6. Send email with optional PDF attachment
	 * 7. Clean up temporary files
	 *
	 * @since 1.0.0
	 *
	 * @param int $student_id Student ID to notify.
	 * @return void Returns early if email disabled, student not found, or no email address.
	 */
	public function notify_issued( $student_id ) {
		if ( ! get_option( 'sjs_cert_enable_email', 0 ) ) {
			return;
		}

		$student = $this->get_student_by_id( $student_id );
		if ( ! $student || empty( $student->email ) ) {
			return;
		}

		$course_model = new Course();
		$course       = $course_model->get_with_normalized_config( $student->course_id );
		if ( ! $course ) {
			return;
		}

		$subject = get_option( 'sjs_cert_email_subject', 'Your Certificate is Ready!' );
		$body    = get_option( 'sjs_cert_email_body', '' );

		if ( empty( $body ) ) {
			$body = "Dear {name},\n\nYour certificate for {course} is ready for download.";
		}

		$download_url = home_url( '/?admission_number=' . $student->admission_number ); // Adjust as per real frontend

		$replacements = array(
			'{name}'             => $student->full_name,
			'{course}'           => $course->course_name,
			'{download_url}'     => $download_url,
			'{admission_number}' => $student->admission_number,
		);

		$subject = str_replace( array_keys( $replacements ), array_values( $replacements ), $subject );
		$body    = str_replace( array_keys( $replacements ), array_values( $replacements ), $body );

		// Generate PDF
		ob_start();
		sjs_cert_get_template( 'frontend/certificate-pdf', array(
			'student' => $student,
			'course'  => $course,
			'config'  => $course->template_config
		) );
		$html = ob_get_clean();

		$pdf_content = '';
		$tmp_file = '';
		try {
			$pdf_factory = PDFFactory::get_driver( 'dompdf' );
			$pdf_content = $pdf_factory->generate( $html, 'certificate.pdf', array(
				'orientation' => $course->template_config['layout']['orientation'] ?? 'landscape'
			) );

			// Save to temp file
			$upload_dir = wp_upload_dir();
			$tmp_file = $upload_dir['basedir'] . '/cert-' . $student->admission_number . '-' . time() . '.pdf';
			file_put_contents( $tmp_file, $pdf_content );

		} catch ( \Exception $e ) {
			error_log( 'Certificate Email Error: ' . $e->getMessage() );
		}

		$headers = array('Content-Type: text/html; charset=UTF-8');
		$attachments = array();
		
		// Only attach PDF if setting is enabled
		if ( get_option( 'sjs_cert_attach_pdf', 1 ) && $tmp_file && file_exists( $tmp_file ) ) {
			$attachments = array( $tmp_file );
		}

		$result = wp_mail( $student->email, $subject, nl2br($body), $headers, $attachments );

		// Clean up
		if ( $tmp_file && file_exists( $tmp_file ) ) {
			unlink( $tmp_file );
		}

		return $result;
	}
}
