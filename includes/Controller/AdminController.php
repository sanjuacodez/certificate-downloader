<?php

namespace SJS_Cert\Controller;

/**
 * The admin-specific functionality of the plugin.
 */
class AdminController {

	private $plugin_name;
	private $version;

	public function __construct( $plugin_name, $version ) {
		$this->plugin_name = $plugin_name;
		$this->version     = $version;
		
		// Enable SVG uploads for student photos
		add_filter( 'upload_mimes', array( $this, 'enable_svg_upload' ) );
		add_filter( 'wp_check_filetype_and_ext', array( $this, 'fix_svg_mime_type' ), 10, 4 );
	}
	
	/**
	 * Enable SVG uploads
	 */
	public function enable_svg_upload( $mimes ) {
		$mimes['svg'] = 'image/svg+xml';
		$mimes['svgz'] = 'image/svg+xml';
		return $mimes;
	}
	
	/**
	 * Fix SVG mime type detection
	 */
	public function fix_svg_mime_type( $data, $file, $filename, $mimes ) {
		$ext = isset( $data['ext'] ) ? $data['ext'] : '';
		if ( strlen( $ext ) < 1 ) {
			$exploded = explode( '.', $filename );
			$ext = strtolower( end( $exploded ) );
		}
		if ( $ext === 'svg' ) {
			$data['type'] = 'image/svg+xml';
			$data['ext'] = 'svg';
		}
		return $data;
	}

	/**
	 * Register the administration menu for this plugin into the WordPress Dashboard.
	 */
	public function register_menu() {
		add_menu_page(
			'Certificate Downloader',
			'Certificates',
			'manage_options',
			'certificate-downloader',
			array( $this, 'display_dashboard' ),
			'dashicons-awards',
			26
		);

		add_submenu_page(
			'certificate-downloader',
			'Dashboard',
			'Dashboard',
			'manage_options',
			'certificate-downloader',
			array( $this, 'display_dashboard' )
		);



		add_submenu_page(
			'certificate-downloader',
			'Students',
			'Students',
			'manage_options',
			'certificate-downloader-students',
			array( $this, 'display_students' )
		);



		add_submenu_page(
			'certificate-downloader',
			'Courses',
			'Courses',
			'manage_options',
			'certificate-downloader-courses',
			array( $this, 'display_courses' )
		);
		
		// Hidden page for editing course
		add_submenu_page(
			'certificate-downloader',
			'Edit Course',
			'Edit Course',
			'manage_options',
			'certificate-downloader-edit-course',
			array( $this, 'display_edit_course' )
		);

		add_submenu_page(
			'certificate-downloader',
			'Settings',
			'Settings',
			'manage_options',
			'certificate-downloader-settings',
			array( $this, 'display_settings' )
		);
		
		// Hide the Edit Course menu item but keep the page accessible
		// remove_submenu_page( 'certificate-downloader', 'certificate-downloader-edit-course' );
		
		add_action( 'admin_init', array( $this, 'register_settings' ) );
		add_filter( 'submenu_file', array( $this, 'highlight_courses_menu' ) );
	}

	/**
	 * Highlight Courses menu when editing a course.
	 */
	public function highlight_courses_menu( $submenu_file ) {
		global $plugin_page;
		if ( 'certificate-downloader-edit-course' === $plugin_page ) {
			return 'certificate-downloader-courses';
		}
		return $submenu_file;
	}



	/**
	 * Register settings.
	 */
	public function register_settings() {
		register_setting( 'sjs_cert_options_group', 'sjs_cert_orientation' );
		register_setting( 'sjs_cert_options_group', 'sjs_cert_logo_url' );
		register_setting( 'sjs_cert_options_group', 'sjs_cert_organization_name' );
		register_setting( 'sjs_cert_options_group', 'sjs_cert_enable_email' );
		register_setting( 'sjs_cert_options_group', 'sjs_cert_attach_pdf' );
		register_setting( 'sjs_cert_options_group', 'sjs_cert_email_subject' );
		register_setting( 'sjs_cert_options_group', 'sjs_cert_email_body' );
	}

	/**
	 * Render the dashboard view.
	 */
	public function display_dashboard() {
		$student_model = new \SJS_Cert\Model\Student();
		$stats         = $student_model->get_stats();
		
		// Get recent students
		$recent_students = $student_model->get_all(); // TODO: optimize with LIMIT
		$recent_students = array_slice( $recent_students, 0, 5 );

		sjs_cert_get_template( 'admin/dashboard', array( 
			'stats'           => $stats,
			'recent_students' => $recent_students
		) );
	}

	/**
	 * Render the import view.
	 */
	public function display_import() {
		sjs_cert_get_template( 'admin/import' );
	}

	/**
	 * Render the settings view.
	 */
	public function display_settings() {
		// Enqueue WordPress media uploader
		wp_enqueue_media();
		sjs_cert_get_template( 'admin/settings' );
	}

	/**
	 * Register the stylesheets for the admin area.
	 */
	public function enqueue_styles() {
		$plugin_file = dirname( dirname( dirname( __FILE__ ) ) ) . '/certificate-downloader.php';
		wp_enqueue_style( $this->plugin_name, plugins_url( 'assets/css/admin.css', $plugin_file ), array(), $this->version, 'all' );
		
		if ( isset($_GET['page']) && $_GET['page'] === 'certificate-downloader-edit-course' ) {
			wp_enqueue_style( $this->plugin_name . '-editor', plugins_url( 'assets/css/admin-certificate-editor.css', $plugin_file ), array(), $this->version, 'all' );
		}

		// Hide Edit Course submenu item
		wp_add_inline_style( $this->plugin_name, 'a[href="admin.php?page=certificate-downloader-edit-course"] { display: none !important; }' );

		// Enqueue Google Fonts for Admin Preview
		wp_enqueue_style( 'sjs-cert-google-fonts', 'https://fonts.googleapis.com/css2?family=Great+Vibes&family=Dancing+Script&family=Pacifico&family=Alex+Brush&family=Satisfy&display=swap', array(), null );
	}

	/**
	 * Register the JavaScript for the admin area.
	 */
	public function enqueue_scripts() {
		$plugin_file = dirname( dirname( dirname( __FILE__ ) ) ) . '/certificate-downloader.php';
		wp_enqueue_script( $this->plugin_name, plugins_url( 'assets/js/admin.js', $plugin_file ), array( 'jquery' ), $this->version, false );
		
		if ( isset($_GET['page']) && $_GET['page'] === 'certificate-downloader-edit-course' ) {
			wp_enqueue_script( $this->plugin_name . '-editor', plugins_url( 'assets/js/admin-certificate-editor.js', $plugin_file ), array( 'jquery' ), $this->version, false );
		}

		wp_localize_script( $this->plugin_name, 'sjs_cert_admin', array(
			'ajax_url' => admin_url( 'admin-ajax.php' ),
			'nonce'    => wp_create_nonce( 'sjs_cert_admin_nonce' ),
		) );
	}

	/**
	 * Render the add student view.
	 */
	public function display_add_student() {
		// Handle form submission
		if ( isset( $_POST['sjs_cert_add_student'] ) && check_admin_referer( 'sjs_cert_add_student_nonce' ) ) {
			$this->process_add_student();
		}
		
		$course_model = new \SJS_Cert\Model\Course();
		$courses      = $course_model->get_all();
		
		sjs_cert_get_template( 'admin/add-student', array( 'courses' => $courses ) );
	}

	/**
	 * Process add student form.
	 */
	private function process_add_student() {
		$photo_url = '';
		
		// Handle photo upload with validation
		if ( ! empty( $_FILES['student_photo']['name'] ) ) {
			$file = $_FILES['student_photo'];
			
			// Validate file size (max 2MB)
			$max_file_size = 2 * 1024 * 1024; // 2MB in bytes
			if ( $file['size'] > $max_file_size ) {
				add_settings_error( 'sjs_cert_messages', 'sjs_cert_error', 'Photo file size exceeds 2MB limit. Please upload a smaller image.', 'error' );
				return;
			}
			
			// Validate file type
			$allowed_types = array( 'image/jpeg', 'image/jpg', 'image/png', 'image/gif', 'image/svg+xml', 'image/webp' );
			$file_type = wp_check_filetype( $file['name'] );
			
			if ( ! in_array( $file['type'], $allowed_types ) && ! in_array( $file_type['type'], $allowed_types ) ) {
				add_settings_error( 'sjs_cert_messages', 'sjs_cert_error', 'Invalid file type. Please upload JPG, PNG, GIF, SVG, or WebP images only.', 'error' );
				return;
			}
			
			// Upload the file
			$upload = wp_handle_upload( $file, array( 'test_form' => false ) );
			
			if ( isset( $upload['error'] ) ) {
				add_settings_error( 'sjs_cert_messages', 'sjs_cert_error', 'Photo upload failed: ' . $upload['error'], 'error' );
				return;
			}
			
			if ( isset( $upload['url'] ) ) {
				$photo_url = $upload['url'];
			}
		}

		// Validate required fields
		$admission_number = sanitize_text_field( $_POST['admission_number'] );
		$full_name = sanitize_text_field( $_POST['full_name'] );
		$email = sanitize_email( $_POST['email'] );
		$course_id = intval( $_POST['course_id'] );
		
		if ( empty( $admission_number ) ) {
			add_settings_error( 'sjs_cert_messages', 'sjs_cert_error', 'Admission number is required.', 'error' );
			return;
		}
		
		if ( empty( $full_name ) ) {
			add_settings_error( 'sjs_cert_messages', 'sjs_cert_error', 'Full name is required.', 'error' );
			return;
		}
		
		if ( empty( $email ) || ! is_email( $email ) ) {
			add_settings_error( 'sjs_cert_messages', 'sjs_cert_error', 'Valid email address is required.', 'error' );
			return;
		}
		
		if ( empty( $course_id ) ) {
			add_settings_error( 'sjs_cert_messages', 'sjs_cert_error', 'Course selection is required.', 'error' );
			return;
		}

		$data = array(
			'admission_number' => $admission_number,
			'full_name'        => $full_name,
			'email'            => $email,
			'phone'            => sanitize_text_field( $_POST['phone'] ),
			'course_id'        => $course_id,
			'issue_status'     => isset( $_POST['issue_status'] ) ? sanitize_text_field( $_POST['issue_status'] ) : 'pending',
			'photo_url'        => esc_url_raw( $photo_url ),
		);

		$student_model = new \SJS_Cert\Model\Student();
		
		// Check for duplicate admission number
		if ( $existing_student = $student_model->get_by_admission_number( $data['admission_number'] ) ) {
			add_settings_error( 
				'sjs_cert_messages', 
				'sjs_cert_error', 
				sprintf( 
					'A student with admission number "%s" already exists. <a href="?page=sjs-cert-students&action=edit&student=%d">View existing student</a>', 
					esc_html( $data['admission_number'] ),
					$existing_student->id
				), 
				'error' 
			);
			return;
		}

		if ( $student_id = $student_model->create( $data ) ) {
			if ( $data['issue_status'] === 'issued' ) {
				$notification_service = new \SJS_Cert\Service\NotificationService();
				$notification_service->notify_issued( $student_id );
			}
			add_settings_error( 'sjs_cert_messages', 'sjs_cert_success', 'Student added successfully.', 'success' );
		} else {
			add_settings_error( 'sjs_cert_messages', 'sjs_cert_error', 'Failed to add student. Please try again or contact support.', 'error' );
		}
	}

	/**
	 * Render the courses view.
	 */
	public function display_courses() {
		// Handle course addition/deletion
		if ( isset( $_POST['sjs_cert_add_course'] ) && check_admin_referer( 'sjs_cert_add_course_nonce' ) ) {
			$this->process_add_course();
		}
		
		if ( isset( $_GET['action'] ) && $_GET['action'] === 'delete' && isset( $_GET['id'] ) && check_admin_referer( 'sjs_cert_delete_course_nonce' ) ) {
			$this->process_delete_course();
		}

		$course_model = new \SJS_Cert\Model\Course();
		$courses      = $course_model->get_all();
		
		sjs_cert_get_template( 'admin/courses', array( 'courses' => $courses ) );
	}

	/**
	 * Process add course form.
	 */
	private function process_add_course() {
		$data = array(
			'course_name' => sanitize_text_field( $_POST['course_name'] ),
		);

		$course_model = new \SJS_Cert\Model\Course();
		
		if ( $course_model->create( $data ) ) {
			add_settings_error( 'sjs_cert_messages', 'sjs_cert_success', 'Course added successfully.', 'success' );
		} else {
			add_settings_error( 'sjs_cert_messages', 'sjs_cert_error', 'Failed to add course.', 'error' );
		}
	}

	/**
	 * Process delete course.
	 */
	private function process_delete_course() {
		$id = intval( $_GET['id'] );
		$course_model = new \SJS_Cert\Model\Course();
		
		if ( $course_model->delete( $id ) ) {
			add_settings_error( 'sjs_cert_messages', 'sjs_cert_success', 'Course deleted successfully.', 'success' );
		} else {
			add_settings_error( 'sjs_cert_messages', 'sjs_cert_error', 'Failed to delete course.', 'error' );
		}
	}

	/**
	 * Render the students list view.
	 */
	public function display_students() {
		// Handle Add Student Submission
		if ( isset( $_POST['sjs_cert_add_student'] ) && check_admin_referer( 'sjs_cert_add_student_nonce' ) ) {
			$this->process_add_student();
		}

		require_once plugin_dir_path( dirname( __DIR__ ) ) . 'includes/Admin/StudentListTable.php';
		$table = new \SJS_Cert\Admin\StudentListTable();
		
		// Handle Bulk Actions
		if ( isset( $_REQUEST['action'] ) && $_REQUEST['action'] !== '-1' ) {
			$action = $_REQUEST['action'];
			if ( isset( $_REQUEST['action2'] ) && $_REQUEST['action2'] !== '-1' ) {
				$action = $_REQUEST['action2'];
			}

			$ids = isset( $_REQUEST['student'] ) ? $_REQUEST['student'] : array();
			if ( ! is_array( $ids ) && isset( $_REQUEST['student'] ) ) {
				$ids = array( $_REQUEST['student'] );
			}

			if ( ! empty( $ids ) ) {
				$student_model = new \SJS_Cert\Model\Student();
				foreach ( $ids as $id ) {
					if ( 'delete' === $action || 'bulk-delete' === $action ) {
						$student_model->delete( intval( $id ) );
					} elseif ( 'bulk-issue' === $action ) {
						if ($student_model->update( intval( $id ), array( 'issue_status' => 'issued' ) )) {
							$notification_service = new \SJS_Cert\Service\NotificationService();
							$notification_service->notify_issued( intval( $id ) );
						}
					} elseif ( 'bulk-pending' === $action ) {
						$student_model->update( intval( $id ), array( 'issue_status' => 'pending' ) );
					}
				}
			}
		}

		$table->prepare_items();
		
		$course_model = new \SJS_Cert\Model\Course();
		$courses      = $course_model->get_all();

		sjs_cert_get_template( 'admin/students', array( 
			'table'   => $table,
			'courses' => $courses 
		) );
	}

	/**
	 * Render the edit course view.
	 */
	public function display_edit_course() {
		$id = isset( $_GET['id'] ) ? intval( $_GET['id'] ) : 0;
		$course_model = new \SJS_Cert\Model\Course();
		$course = $course_model->get_with_normalized_config( $id );

		if ( ! $course ) {
			wp_die( 'Course not found.' );
		}

		// Handle Save
		if ( isset( $_POST['sjs_cert_save_course'] ) && check_admin_referer( 'sjs_cert_save_course_nonce' ) ) {
			$existing_config = $course->template_config;
			if ( ! is_array( $existing_config ) ) {
				$existing_config = array();
			}

			// Get base config from POST
			$config = isset($_POST['template_config']) ? $_POST['template_config'] : array();
			
			// Sanitize recursively
			array_walk_recursive($config, function(&$value) {
				$value = sanitize_text_field($value);
			});

			// Handle Header Logo
			if ( ! empty( $_FILES['logo_file']['name'] ) ) {
				$upload = wp_handle_upload( $_FILES['logo_file'], array( 'test_form' => false ) );
				if ( ! isset( $upload['error'] ) && isset( $upload['url'] ) ) {
					$config['header']['logo_url'] = $upload['url'];
				}
			}

			// Handle Footer Logo
			if ( ! empty( $_FILES['footer_logo_file']['name'] ) ) {
				$upload = wp_handle_upload( $_FILES['footer_logo_file'], array( 'test_form' => false ) );
				if ( ! isset( $upload['error'] ) && isset( $upload['url'] ) ) {
					$config['footer']['footer_logo_url'] = $upload['url'];
				}
			}

			// Handle Background Image
			if ( ! empty( $_FILES['background_image_file']['name'] ) ) {
				$upload = wp_handle_upload( $_FILES['background_image_file'], array( 'test_form' => false ) );
				if ( ! isset( $upload['error'] ) && isset( $upload['url'] ) ) {
					$config['background']['background_image_url'] = $upload['url'];
				}
			} elseif ( isset( $_POST['remove_background_image'] ) && $_POST['remove_background_image'] === '1' ) {
				$config['background']['background_image_url'] = '';
			}

			// Handle Seal Image
			if ( ! empty( $_FILES['seal_image_file']['name'] ) ) {
				$upload = wp_handle_upload( $_FILES['seal_image_file'], array( 'test_form' => false ) );
				if ( ! isset( $upload['error'] ) && isset( $upload['url'] ) ) {
					$config['seal_badge']['image_url'] = $upload['url'];
				}
			}

			// Handle Signatures Images
			if ( isset( $config['signatures'] ) && is_array( $config['signatures'] ) ) {
				foreach ( $config['signatures'] as $index => &$sig ) {
					// Check if a file was uploaded for this index
					if ( ! empty( $_FILES['signature_files']['name'][$index] ) ) {
						$file = array(
							'name'     => $_FILES['signature_files']['name'][$index],
							'type'     => $_FILES['signature_files']['type'][$index],
							'tmp_name' => $_FILES['signature_files']['tmp_name'][$index],
							'error'    => $_FILES['signature_files']['error'][$index],
							'size'     => $_FILES['signature_files']['size'][$index],
						);
						$upload = wp_handle_upload( $file, array( 'test_form' => false ) );
						if ( ! isset( $upload['error'] ) && isset( $upload['url'] ) ) {
							$sig['image_url'] = $upload['url'];
						}
					}
				}
			}
			
			// Ensure schema version
			$config['schema_version'] = '1.0.0';
			
			// Handle checkboxes that might be missing from POST if unchecked
			if (!isset($config['student_photo']['enabled'])) $config['student_photo']['enabled'] = false;
			if (!isset($config['qr_code']['enabled'])) $config['qr_code']['enabled'] = false;

			$course_model->update( $id, array(
				'course_name'     => sanitize_text_field( $_POST['course_name'] ),
				'template_config' => json_encode( $config ),
			) );
			
			// Refresh data
			$course = $course_model->get_with_normalized_config( $id );
			add_settings_error( 'sjs_cert_messages', 'sjs_cert_success', 'Course updated successfully.', 'success' );
		}

		sjs_cert_get_template( 'admin/edit-course', array( 'course' => $course ) );
	}
	public function ajax_import_batch_students() {
		check_ajax_referer( 'sjs_cert_admin_nonce', 'nonce' );

		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( 'Permission denied.' );
		}

		$students = isset( $_POST['students'] ) ? $_POST['students'] : array();
		if ( empty( $students ) || ! is_array( $students ) ) {
			wp_send_json_error( 'No student data received.' );
		}

		$student_model = new \SJS_Cert\Model\Student();
		$success_count = 0;
		$errors        = array();

		foreach ( $students as $index => $student_data ) {
			$row_num = $index + 2;
			
			// Download remote image if photo_url provided (column 6)
			$photo_url = '';
			if ( isset( $student_data[5] ) && ! empty( trim( $student_data[5] ) ) ) {
				$remote_url = trim( $student_data[5] );
				$photo_url = $this->download_remote_image( $remote_url );
				if ( empty( $photo_url ) ) {
					$errors[] = "Row $row_num: Failed to download photo from $remote_url";
				}
			}
			
			$data = array(
				'admission_number' => sanitize_text_field( $student_data[0] ),
				'full_name'        => sanitize_text_field( $student_data[1] ),
				'email'            => sanitize_email( $student_data[2] ),
				'phone'            => sanitize_text_field( $student_data[3] ),
				'course_id'        => intval( $student_data[4] ),
				'issue_status'     => 'issued',
				'photo_url'        => $photo_url,
			);

			if ( empty( $data['admission_number'] ) || empty( $data['full_name'] ) ) {
				$errors[] = "Missing required data for student: " . ($data['full_name'] ?: 'Unknown');
				continue;
			}

			if ( $student_model->get_by_admission_number( $data['admission_number'] ) ) {
				$errors[] = "Duplicate admission number: {$data['admission_number']}";
				continue;
			}

			if ( $student_id = $student_model->create( $data ) ) {
				$success_count++;
				// Send notification if enabled
				if ( get_option( 'sjs_cert_enable_email', 0 ) ) {
					$notification_service = new \SJS_Cert\Service\NotificationService();
					$notification_service->notify_issued( $student_id );
				}
			} else {
				$errors[] = "Database error for student: {$data['full_name']}";
			}
		}

		wp_send_json_success( array(
			'imported' => $success_count,
			'errors'   => $errors
		) );
	}

	public function ajax_import_students() {
		check_ajax_referer( 'sjs_cert_admin_nonce', 'nonce' );

		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( 'Permission denied. You do not have sufficient permissions to import students.' );
		}

		if ( empty( $_FILES['csv_file'] ) ) {
			wp_send_json_error( 'No file uploaded. Please select a CSV file to import.' );
		}

		$file = $_FILES['csv_file'];
		
		// Check for upload errors
		if ( $file['error'] !== UPLOAD_ERR_OK ) {
			$error_messages = array(
				UPLOAD_ERR_INI_SIZE   => 'The uploaded file exceeds the maximum file size allowed by the server.',
				UPLOAD_ERR_FORM_SIZE  => 'The uploaded file exceeds the maximum file size allowed by the form.',
				UPLOAD_ERR_PARTIAL    => 'The file was only partially uploaded. Please try again.',
				UPLOAD_ERR_NO_FILE    => 'No file was uploaded.',
				UPLOAD_ERR_NO_TMP_DIR => 'Missing temporary folder on server.',
				UPLOAD_ERR_CANT_WRITE => 'Failed to write file to disk.',
				UPLOAD_ERR_EXTENSION  => 'A PHP extension stopped the file upload.',
			);
			$error_message = isset( $error_messages[ $file['error'] ] ) ? $error_messages[ $file['error'] ] : 'Unknown file upload error.';
			wp_send_json_error( $error_message );
		}
		
		// Check file extension
		$file_ext = strtolower( pathinfo( $file['name'], PATHINFO_EXTENSION ) );
		if ( $file_ext !== 'csv' ) {
			wp_send_json_error( 'Invalid file type. Please upload a CSV file (.csv extension required).' );
		}

		$mimes = array( 'text/csv', 'text/plain', 'application/csv', 'text/comma-separated-values', 'application/excel', 'application/vnd.ms-excel', 'application/vnd.msexcel' );
		if ( ! in_array( $file['type'], $mimes ) ) {
			// Strict mime check can fail on some servers, rely on extension check above
		}

		$handle = fopen( $file['tmp_name'], 'r' );
		if ( ! $handle ) {
			wp_send_json_error( 'Could not open the uploaded file. The file may be corrupted.' );
		}

		$header = fgetcsv( $handle ); // Assume first row is header
		
		// Validate CSV header
		$required_columns = array( 'admission_number', 'full_name', 'email', 'phone', 'course_id' );
		$missing_columns = array();
		foreach ( $required_columns as $col ) {
			if ( ! in_array( $col, $header ) ) {
				$missing_columns[] = $col;
			}
		}
		if ( ! empty( $missing_columns ) ) {
			fclose( $handle );
			wp_send_json_error( 'Invalid CSV format. Missing required columns: ' . implode( ', ', $missing_columns ) . '. Please download the sample CSV for the correct format.' );
		}

		$student_model = new \SJS_Cert\Model\Student();
		$course_model = new \SJS_Cert\Model\Course();
		$success_count = 0;
		$errors        = array();
		$row_index     = 1; // Header is row 1, data starts at 2

		// Process each row of the CSV file
		while ( ( $row = fgetcsv( $handle ) ) !== false ) {
			$row_index++;
			
			// Skip empty rows (all columns are empty)
			if ( empty( array_filter( $row ) ) ) {
				continue;
			}
			
			// Validate minimum column count
			if ( count( $row ) < 5 ) {
				$errors[] = "Row $row_index: Invalid data. Expected at least 5 columns (admission_number, full_name, email, phone, course_id).";
				continue;
			}

			// Sanitize and extract data from CSV columns
			$admission_number = sanitize_text_field( trim( $row[0] ) );
			$full_name = sanitize_text_field( trim( $row[1] ) );
			$email = sanitize_email( trim( $row[2] ) );
			$phone = sanitize_text_field( trim( $row[3] ) );
			$course_id = intval( $row[4] );
			
			// Validate required fields before insertion
			if ( empty( $admission_number ) ) {
				$errors[] = "Row $row_index: Admission number is required.";
				continue;
			}
			
			if ( empty( $full_name ) ) {
				$errors[] = "Row $row_index: Full name is required.";
				continue;
			}
			
			if ( empty( $email ) || ! is_email( $email ) ) {
				$errors[] = "Row $row_index: Invalid or missing email address '$email'.";
				continue;
			}
			
			if ( empty( $course_id ) || ! $course_model->get( $course_id ) ) {
				$errors[] = "Row $row_index: Invalid course ID '$course_id'. Course does not exist.";
				continue;
			}

			// Check for duplicate admission number
			if ( $student_model->get_by_admission_number( $admission_number ) ) {
				$errors[] = "Row $row_index: Duplicate admission number '$admission_number'. This student already exists.";
				continue;
			}

			// Download remote image if photo_url provided
			$photo_url = '';
			if ( isset( $row[5] ) && ! empty( trim( $row[5] ) ) ) {
				$remote_url = trim( $row[5] );
				$photo_url = $this->download_remote_image( $remote_url );
				if ( empty( $photo_url ) ) {
					$errors[] = "Row $row_index: Failed to download photo from '$remote_url'. Student will be imported without photo.";
					// Continue with import even if photo fails
				}
			}

			$data = array(
				'admission_number' => $admission_number,
				'full_name'        => $full_name,
				'email'            => $email,
				'phone'            => $phone,
				'course_id'        => $course_id,
				'issue_status'     => 'issued',
				'photo_url'        => $photo_url,
			);

			if ( $student_model->create( $data ) ) {
				$success_count++;
			} else {
				$errors[] = "Row $row_index: Database error while saving student '$full_name'.";
			}
		}

		fclose( $handle );

		// Build detailed response message
		$total_processed = $row_index - 1; // Subtract header row
		$failed_count = count( $errors );
		
		if ( $success_count === 0 && $failed_count > 0 ) {
			// Complete failure
			$message = "Import failed. No students were imported. ";
			$message .= "Errors found: " . implode( ' | ', array_slice( $errors, 0, 3 ) );
			if ( $failed_count > 3 ) {
				$message .= " ...and " . ( $failed_count - 3 ) . " more errors.";
			}
			wp_send_json_error( $message );
		} elseif ( $success_count > 0 && $failed_count > 0 ) {
			// Partial success
			$message = "Import completed with warnings. Successfully imported $success_count of $total_processed students. ";
			$message .= "Errors: " . implode( ' | ', array_slice( $errors, 0, 3 ) );
			if ( $failed_count > 3 ) {
				$message .= " ...and " . ( $failed_count - 3 ) . " more errors.";
			}
			wp_send_json_success( $message );
		} else {
			// Complete success
			$message = "Import successful! Imported $success_count student" . ( $success_count > 1 ? 's' : '' ) . " successfully.";
			wp_send_json_success( $message );
		}
	}

	/**
	 * AJAX handler for previewing certificates.
	 */
	public function ajax_preview_certificate() {
		check_ajax_referer( 'sjs_cert_admin_nonce', 'nonce' );
		// Logic to be implemented
		wp_send_json_success( 'Preview functionality pending.' );
	}

	/**
	 * Download sample CSV.
	 */
	public function download_sample_csv() {
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( 'Permission denied.' );
		}

		header( 'Content-Type: text/csv' );
		header( 'Content-Disposition: attachment; filename="students_sample.csv"' );
		header( 'Pragma: no-cache' );
		header( 'Expires: 0' );

		$output = fopen( 'php://output', 'w' );
		
		// Header
		fputcsv( $output, array( 'admission_number', 'full_name', 'email', 'phone', 'course_id', 'photo_url' ), ',', '"', '\\' );
		
		// Sample Data
		fputcsv( $output, array( 'ADM001', 'John Doe', 'john@example.com', '1234567890', '1', 'https://example.com/photo1.jpg' ), ',', '"', '\\' );
		fputcsv( $output, array( 'ADM002', 'Jane Smith', 'jane@example.com', '0987654321', '1', '' ), ',', '"', '\\' );

		fclose( $output );
		exit;
	}
	
	/**
	 * Download and save remote image to WordPress uploads directory
	 */
	private function download_remote_image( $url ) {
		if ( empty( $url ) ) {
			return '';
		}
		
		// Validate URL
		if ( ! filter_var( $url, FILTER_VALIDATE_URL ) ) {
			return '';
		}
		
		// Use WordPress HTTP API to download
		$response = wp_remote_get( $url, array(
			'timeout' => 30,
			'sslverify' => false,
			'user-agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/91.0.4472.124 Safari/537.36',
			'redirection' => 5
		) );
		
		if ( is_wp_error( $response ) ) {
			return '';
		}
		
		$response_code = wp_remote_retrieve_response_code( $response );
		if ( $response_code !== 200 ) {
			return '';
		}
		
		$image_data = wp_remote_retrieve_body( $response );
		if ( empty( $image_data ) ) {
			return '';
		}
		
		// Get file extension from URL or content-type
		$content_type = wp_remote_retrieve_header( $response, 'content-type' );
		$extension = 'jpg'; // default
		
		if ( strpos( $content_type, 'svg' ) !== false ) {
			$extension = 'svg';
		} elseif ( strpos( $content_type, 'png' ) !== false ) {
			$extension = 'png';
		} elseif ( strpos( $content_type, 'gif' ) !== false ) {
			$extension = 'gif';
		} elseif ( strpos( $content_type, 'webp' ) !== false ) {
			$extension = 'webp';
		} elseif ( strpos( $content_type, 'jpeg' ) !== false || strpos( $content_type, 'jpg' ) !== false ) {
			$extension = 'jpg';
		} else {
			// Try to detect from URL
			$url_path = parse_url( $url, PHP_URL_PATH );
			$url_ext = pathinfo( $url_path, PATHINFO_EXTENSION );
			if ( in_array( strtolower( $url_ext ), array( 'jpg', 'jpeg', 'png', 'gif', 'svg', 'webp' ) ) ) {
				$extension = strtolower( $url_ext );
			}
		}
		
		// Create unique filename
		$filename = 'student_' . uniqid() . '.' . $extension;
		
		// Upload to WordPress
		$upload = wp_upload_bits( $filename, null, $image_data );
		
		if ( ! $upload['error'] ) {
			return $upload['url'];
		} else {
			// Fallback: Direct file save for SVG (bypasses WordPress restrictions)
			if ( $extension === 'svg' || $extension === 'svgz' ) {
				$upload_dir = wp_upload_dir();
				$file_path = $upload_dir['path'] . '/' . $filename;
				$file_url = $upload_dir['url'] . '/' . $filename;
				
				if ( file_put_contents( $file_path, $image_data ) ) {
					return $file_url;
				}
			}
		}
		
		return '';
	}
}
