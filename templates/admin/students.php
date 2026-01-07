<div class="wrap">
	<h1 class="wp-heading-inline">Students</h1>
	<hr class="wp-header-end">

	<?php settings_errors( 'sjs_cert_messages' ); ?>

	<div class="sjs-cert-accordions-wrapper">
		<!-- Add Student Accordion -->
		<details class="sjs-cert-accordion">
			<summary>
				<span class="dashicons dashicons-plus"></span> Add New Student
			</summary>
			<div class="sjs-cert-accordion-content">
				<form method="post" action="" enctype="multipart/form-data">
					<?php wp_nonce_field( 'sjs_cert_add_student_nonce' ); ?>
					<div class="sjs-cert-form-grid">
						<div class="form-field">
							<label for="admission_number">Admission Number <span class="required">*</span></label>
							<input name="admission_number" type="text" id="admission_number" required>
						</div>
						<div class="form-field">
							<label for="full_name">Full Name <span class="required">*</span></label>
							<input name="full_name" type="text" id="full_name" required>
						</div>
						<div class="form-field">
							<label for="email">Email <span class="required">*</span></label>
							<input name="email" type="email" id="email" required>
						</div>
						<div class="form-field">
							<label for="phone">Phone</label>
							<input name="phone" type="text" id="phone">
						</div>
						<div class="form-field">
							<label for="course_id">Course <span class="required">*</span></label>
							<select name="course_id" id="course_id" required>
								<option value="">Select Course</option>
								<?php foreach ( $courses as $course ) : ?>
									<option value="<?php echo esc_attr( $course->id ); ?>"><?php echo esc_html( $course->course_name ); ?></option>
								<?php endforeach; ?>
							</select>
						</div>
						<div class="form-field">
							<label for="issue_status">Status</label>
							<select name="issue_status" id="issue_status">
								<option value="issued">Issued</option>
								<option value="pending">Pending</option>
							</select>
						</div>					<div class="form-field">
						<label for="student_photo">Student Photo</label>
						<input name="student_photo" type="file" id="student_photo" accept="image/*">
						<p class="description">Upload a student photo (JPEG, PNG, GIF). Recommended size: 300x300px.</p>
					</div>					</div>
					<div class="form-actions">
						<input type="submit" name="sjs_cert_add_student" id="submit" class="button button-primary" value="Add Student" aria-label="Add new student to the system">
					</div>
				</form>
			</div>
		</details>

		<!-- Import Students Accordion -->
		<details class="sjs-cert-accordion">
			<summary>
				<span class="dashicons dashicons-upload"></span> Import Students (CSV)
			</summary>
			<div class="sjs-cert-accordion-content">
				<div class="sjs-cert-import-header">
					<p>Upload a CSV file to bulk import students.</p>
					<a href="<?php echo admin_url( 'admin-post.php?action=sjs_cert_download_sample_csv' ); ?>" class="button button-secondary">
						<span class="dashicons dashicons-download" style="line-height: 1.3;"></span> Download Sample CSV
					</a>
				</div>
				
				<form id="sjs-cert-import-form" enctype="multipart/form-data" class="sjs-cert-import-form">
					<div class="file-upload-wrapper">
						<input type="file" name="csv_file" accept=".csv" required id="csv_file_input" aria-label="Choose CSV file to import">
						<label for="csv_file_input" class="button">Choose File</label>
						<span class="file-name">No file chosen</span>
					</div>
					<button type="submit" class="button button-primary" aria-label="Import students from CSV file">Import Students</button>
				</form>
				<div id="sjs-cert-import-progress" style="display:none; margin-top:20px; background: #f0f0f1; border: 1px solid #ccd0d4; border-radius: 4px; overflow: hidden;">
					<div class="progress-bar-inner" style="width:0%; height:20px; background:#2271b1; transition: width 0.3s;"></div>
					<div class="progress-text" style="text-align:center; font-size:12px; margin-top:5px; padding-bottom: 5px;">0%</div>
				</div>
				<div id="sjs-cert-import-result"></div>
			</div>
		</details>
	</div>

	<!-- Students Table -->
	<div class="sjs-cert-table-card">
		<form method="get">
			<input type="hidden" name="page" value="certificate-downloader-students" />
			<?php
			$table->search_box( 'Search Students', 'search_id' );
			$table->display();
			?>
		</form>
	</div>
</div>

<script>
jQuery(document).ready(function($) {
	// File input styling
	$('#csv_file_input').on('change', function() {
		var fileName = $(this).val().split('\\').pop();
		if (fileName) {
			$(this).siblings('.file-name').text(fileName);
		} else {
			$(this).siblings('.file-name').text('No file chosen');
		}
	});
});
</script>
