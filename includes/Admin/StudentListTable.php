<?php

namespace SJS_Cert\Admin;

if ( ! class_exists( 'WP_List_Table' ) ) {
	require_once ABSPATH . 'wp-admin/includes/class-wp-list-table.php';
}

class StudentListTable extends \WP_List_Table {

	public function __construct() {
		parent::__construct( array(
			'singular' => 'student',
			'plural'   => 'students',
			'ajax'     => false,
		) );
	}

	public function get_columns() {
		return array(
			'cb'               => '<input type="checkbox" />',
			'photo'            => 'Photo',
			'admission_number' => 'Admission Number',
			'full_name'        => 'Full Name',
			'email'            => 'Email',
			'course'           => 'Course',
			'issue_status'     => 'Status',
			'download_count'   => 'Downloads',
			'created_at'       => 'Date Added',
		);
	}

	public function get_sortable_columns() {
		return array(
			'admission_number' => array( 'admission_number', false ),
			'full_name'        => array( 'full_name', false ),
			'created_at'       => array( 'created_at', false ),
			'issue_status'     => array( 'issue_status', false ),
		);
	}

	protected function column_default( $item, $column_name ) {
		switch ( $column_name ) {
			case 'photo':
				$photo_url = ! empty( $item->photo_url ) ? $item->photo_url : plugins_url( 'assets/images/placeholder.svg', dirname( dirname( __DIR__ ) ) . '/certificate-downloader.php' );
				return sprintf( '<img src="%s" style="width: 50px; height: 50px; object-fit: cover; border-radius: 4px;" alt="Student Photo">', esc_url( $photo_url ) );
			case 'admission_number':
			case 'full_name':
			case 'email':
			case 'issue_status':
			case 'download_count':
			case 'created_at':
				return esc_html( $item->$column_name );
			case 'course':
				// We need to fetch course name. Ideally join in query, but for now:
				$course_model = new \SJS_Cert\Model\Course();
				$course = $course_model->get( $item->course_id );
				return $course ? esc_html( $course->course_name ) : 'Unknown';
			default:
				return print_r( $item, true );
		}
	}

	protected function column_cb( $item ) {
		return sprintf(
			'<input type="checkbox" name="student[]" value="%s" />',
			$item->id
		);
	}

	protected function column_admission_number( $item ) {
		
		$actions = array(
			'edit'   => sprintf( '<a href="?page=%s&action=%s&student=%s" aria-label="Edit student %s">Edit</a>', $_REQUEST['page'], 'edit', $item->id, esc_attr( $item->full_name ) ),
			'delete' => sprintf( '<a href="?page=%s&action=%s&student=%s&_wpnonce=%s" onclick="return confirm(\'Are you sure you want to delete %s?\')" aria-label="Delete student %s">Delete</a>', $_REQUEST['page'], 'delete', $item->id, wp_create_nonce( 'sjs_cert_delete_student_' . $item->id ), esc_js( $item->full_name ), esc_attr( $item->full_name ) ),
			'download' => sprintf( '<a href="%s" target="_blank" aria-label="Download certificate for %s">Download PDF</a>', admin_url( 'admin-ajax.php?action=sjs_cert_generate_pdf&admission_number=' . $item->admission_number ), esc_attr( $item->full_name ) ),
			'debug' => sprintf( '<a href="%s" target="_blank" style="color: orange;" aria-label="Debug certificate HTML for %s">Debug HTML</a>', admin_url( 'admin-ajax.php?action=sjs_cert_generate_pdf&admission_number=' . $item->admission_number . '&debug=true' ), esc_attr( $item->full_name ) ),
		);

		return sprintf( '%1$s %2$s', $item->admission_number, $this->row_actions( $actions ) );
	}

	public function get_bulk_actions() {
		return array(
			'bulk-delete' => 'Delete',
			'bulk-issue'  => 'Set Issued',
			'bulk-pending' => 'Set Pending',
		);
	}

	public function no_items() {
		?>
		<div class="sjs-cert-empty-state">
			<svg width="48" height="48" viewBox="0 0 48 48" fill="none" xmlns="http://www.w3.org/2000/svg">
				<path d="M38 6H10C8.89543 6 8 6.89543 8 8V40C8 41.1046 8.89543 42 10 42H38C39.1046 42 40 41.1046 40 40V8C40 6.89543 39.1046 6 38 6Z" stroke="#cbd5e1" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
				<circle cx="24" cy="20" r="6" stroke="#cbd5e1" stroke-width="2"/>
				<path d="M14 35C14 35 16 30 24 30C32 30 34 35 34 35" stroke="#cbd5e1" stroke-width="2" stroke-linecap="round"/>
			</svg>
			<h3>No Students Yet</h3>
			<p>Get started by adding your first student using the form above, or import multiple students from a CSV file.</p>
		</div>
		<?php
	}

	public function extra_tablenav( $which ) {
		if ( 'top' !== $which ) {
			return;
		}

		$course_model = new \SJS_Cert\Model\Course();
		$courses      = $course_model->get_all();
		
		$filter_course = isset( $_REQUEST['filter_course'] ) ? intval( $_REQUEST['filter_course'] ) : '';
		$filter_status = isset( $_REQUEST['filter_status'] ) ? sanitize_text_field( $_REQUEST['filter_status'] ) : '';
		?>
		<div class="alignleft actions">
			<select name="filter_course" aria-label="Filter by course">
				<option value="">All Courses</option>
				<?php foreach ( $courses as $course ) : ?>
					<option value="<?php echo esc_attr( $course->id ); ?>" <?php selected( $filter_course, $course->id ); ?>>
						<?php echo esc_html( $course->course_name ); ?>
					</option>
				<?php endforeach; ?>
			</select>
			
			<select name="filter_status" aria-label="Filter by status">
				<option value="">All Statuses</option>
				<option value="issued" <?php selected( $filter_status, 'issued' ); ?>>Issued</option>
				<option value="pending" <?php selected( $filter_status, 'pending' ); ?>>Pending</option>
			</select>
			
			<?php submit_button( 'Filter', '', 'filter_action', false, array( 'aria-label' => 'Apply filters' ) ); ?>
		</div>
		<?php
	}

	public function prepare_items() {
		$columns  = $this->get_columns();
		$hidden   = array();
		$sortable = $this->get_sortable_columns();

		$this->_column_headers = array( $columns, $hidden, $sortable );

		$per_page = 20;
		$current_page = $this->get_pagenum();
		
		// Handle searching and sorting
		$search = isset( $_REQUEST['s'] ) ? sanitize_text_field( $_REQUEST['s'] ) : '';
		$orderby = isset( $_REQUEST['orderby'] ) ? sanitize_text_field( $_REQUEST['orderby'] ) : 'created_at';
		$order = isset( $_REQUEST['order'] ) ? sanitize_text_field( $_REQUEST['order'] ) : 'DESC';
		
		// Filters
		$filter_course = isset( $_REQUEST['filter_course'] ) ? intval( $_REQUEST['filter_course'] ) : '';
		$filter_status = isset( $_REQUEST['filter_status'] ) ? sanitize_text_field( $_REQUEST['filter_status'] ) : '';

		$student_model = new \SJS_Cert\Model\Student();
		
		// We need to extend Model to support pagination/search/sort
		// For now, fetching all and slicing (Not performant for large datasets, but MVP)
		// TODO: Update Model to support LIMIT/OFFSET and WHERE
		
		$all_items = $student_model->get_all(); // This needs improvement for real pagination
		
		// Filter by search
		if ( $search ) {
			$all_items = array_filter( $all_items, function( $item ) use ( $search ) {
				return stripos( $item->full_name, $search ) !== false || stripos( $item->admission_number, $search ) !== false;
			} );
		}
		
		// Filter by Course
		if ( $filter_course ) {
			$all_items = array_filter( $all_items, function( $item ) use ( $filter_course ) {
				return intval( $item->course_id ) === $filter_course;
			} );
		}
		
		// Filter by Status
		if ( $filter_status ) {
			$all_items = array_filter( $all_items, function( $item ) use ( $filter_status ) {
				return $item->issue_status === $filter_status;
			} );
		}

		// Sort
		usort( $all_items, function( $a, $b ) use ( $orderby, $order ) {
			$result = strcmp( $a->$orderby, $b->$orderby );
			return ( $order === 'asc' ) ? $result : -$result;
		} );

		$total_items = count( $all_items );
		$this->items = array_slice( $all_items, ( $current_page - 1 ) * $per_page, $per_page );

		$this->set_pagination_args( array(
			'total_items' => $total_items,
			'per_page'    => $per_page,
			'total_pages' => ceil( $total_items / $per_page ),
		) );
	}
}
