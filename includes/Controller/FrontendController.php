<?php

namespace SJS_Cert\Controller;

/**
 * The public-facing functionality of the plugin.
 */
class FrontendController {

	private $plugin_name;
	private $version;

	public function __construct( $plugin_name, $version ) {
		$this->plugin_name = $plugin_name;
		$this->version     = $version;
	}

	/**
	 * Register the stylesheets for the public-facing side of the site.
	 */
	public function enqueue_styles() {
		wp_enqueue_style( $this->plugin_name, plugin_dir_url( dirname( dirname( __FILE__ ) ) ) . 'assets/css/frontend.css', array(), $this->version, 'all' );
	}

	/**
	 * Register the JavaScript for the public-facing side of the site.
	 */
	public function enqueue_scripts() {
		wp_enqueue_script( $this->plugin_name, plugin_dir_url( dirname( dirname( __FILE__ ) ) ) . 'assets/js/frontend.js', array( 'jquery' ), $this->version, false );

		wp_localize_script( $this->plugin_name, 'sjs_cert_frontend', array(
			'ajax_url' => admin_url( 'admin-ajax.php' ),
			'nonce'    => wp_create_nonce( 'sjs_cert_frontend_nonce' ),
		) );
	}

	/**
	 * Shortcode callback for [certificate_lookup].
	 */
	public function shortcode_certificate_lookup( $atts ) {
		ob_start();
		sjs_cert_get_template( 'frontend/certificate-lookup' );
		return ob_get_clean();
	}

	/**
	 * AJAX handler for certificate lookup.
	 */
	public function ajax_lookup_certificate() {
		check_ajax_referer( 'sjs_cert_frontend_nonce', 'nonce' );

		$admission_number = isset( $_POST['admission_number'] ) ? sanitize_text_field( $_POST['admission_number'] ) : '';
		$email = isset( $_POST['email'] ) ? sanitize_email( $_POST['email'] ) : '';

		if ( empty( $admission_number ) ) {
			wp_send_json_error( 'Admission number is required.' );
		}

		if ( empty( $email ) ) {
			wp_send_json_error( 'Email address is required.' );
		}

		if ( ! is_email( $email ) ) {
			wp_send_json_error( 'Please enter a valid email address.' );
		}

		$student_model = new \SJS_Cert\Model\Student();
		$student       = $student_model->get_by_admission_number( $admission_number );

		if ( ! $student ) {
			wp_send_json_error( 'No certificate found with this admission number and email combination.' );
		}

		// Validate email matches
		if ( strtolower( $student->email ) !== strtolower( $email ) ) {
			wp_send_json_error( 'Email address does not match our records. Please check and try again.' );
		}

		if ( $student->issue_status !== 'issued' ) {
			wp_send_json_error( 'Certificate is not yet issued.' );
		}

		$course_model = new \SJS_Cert\Model\Course();
		$course       = $course_model->get_with_normalized_config( $student->course_id );

		ob_start();
		sjs_cert_get_template( 'frontend/certificate-preview', array(
			'student' => $student,
			'course'  => $course,
			'config'  => $course->template_config
		) );
		$html = ob_get_clean();

		wp_send_json_success( array( 'html' => $html ) );
	}

	/**
	 * AJAX handler for generating PDF.
	 */
	public function ajax_generate_pdf() {
		// Verify nonce (optional, depending on flow, but recommended)
		// check_ajax_referer( 'sjs_cert_frontend_nonce', 'nonce' );

		$admission_number = isset( $_REQUEST['admission_number'] ) ? sanitize_text_field( $_REQUEST['admission_number'] ) : '';

		if ( empty( $admission_number ) ) {
			wp_die( 'Admission number is required.' );
		}

		$student_model = new \SJS_Cert\Model\Student();
		$student       = $student_model->get_by_admission_number( $admission_number );

		if ( ! $student || $student->issue_status !== 'issued' ) {
			wp_die( 'Invalid request.' );
		}

		// Update download count
		$student_model->update( $student->id, array( 'download_count' => $student->download_count + 1 ) );

		$course_model = new \SJS_Cert\Model\Course();
		$course       = $course_model->get_with_normalized_config( $student->course_id );

		// Generate HTML for PDF
		ob_start();
		sjs_cert_get_template( 'frontend/certificate-pdf', array(
			'student' => $student,
			'course'  => $course,
			'config'  => $course->template_config
		) );
		$html = ob_get_clean();

		// Debug Mode: Output HTML directly
		if ( isset( $_REQUEST['debug'] ) && $_REQUEST['debug'] === 'true' ) {
			if ( ! defined( 'SJS_CERT_PDF_DEBUG' ) ) {
				define( 'SJS_CERT_PDF_DEBUG', true );
			}
			// Regenerate template with the constant defined
			ob_start();
			sjs_cert_get_template( 'frontend/certificate-pdf', array(
				'student' => $student,
				'course'  => $course,
				'config'  => $course->template_config
			) );
			echo ob_get_clean();
			exit;
		}

		try {
			$pdf_factory = \SJS_Cert\Service\PDF\PDFFactory::get_driver( 'dompdf' );
			
			// Get course config for layout
			$config = $course->template_config;
			$orientation = isset( $config['layout']['orientation'] ) ? $config['layout']['orientation'] : 'landscape';
			$page_size = isset( $config['layout']['page_size'] ) ? $config['layout']['page_size'] : 'A4';

			$pdf_factory->generate( $html, 'certificate-' . $student->admission_number . '.pdf', array(
				'stream'      => true,
				'orientation' => $orientation,
				'paper_size'  => $page_size,
			) );
		} catch ( \Exception $e ) {
			wp_die( $e->getMessage() );
		}
		exit;
	}
}
