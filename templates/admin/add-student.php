<div class="wrap">
	<h1>Add New Student</h1>
	<?php settings_errors( 'sjs_cert_messages' ); ?>
	
	<div class="card" style="max-width: 600px;">
		<form method="post" action="" enctype="multipart/form-data">
			<?php wp_nonce_field( 'sjs_cert_add_student_nonce' ); ?>
			<table class="form-table">
				<tr>
					<th scope="row"><label for="admission_number">Admission Number</label></th>
					<td><input name="admission_number" type="text" id="admission_number" class="regular-text" required></td>
				</tr>
				<tr>
					<th scope="row"><label for="full_name">Full Name</label></th>
					<td><input name="full_name" type="text" id="full_name" class="regular-text" required></td>
				</tr>
				<tr>
					<th scope="row"><label for="student_photo">Student Photo</label></th>
					<td>
						<input name="student_photo" type="file" id="student_photo" accept="image/*">
						<p class="description">Upload a photo for the certificate.</p>
					</td>
				</tr>
				<tr>
					<th scope="row"><label for="email">Email</label></th>
					<td><input name="email" type="email" id="email" class="regular-text" required></td>
				</tr>
				<tr>
					<th scope="row"><label for="phone">Phone</label></th>
					<td><input name="phone" type="text" id="phone" class="regular-text"></td>
				</tr>
				<tr>
					<th scope="row"><label for="course_id">Course</label></th>
					<td>
						<select name="course_id" id="course_id" required>
							<option value="">Select Course</option>
							<?php foreach ( $courses as $course ) : ?>
								<option value="<?php echo esc_attr( $course->id ); ?>"><?php echo esc_html( $course->course_name ); ?></option>
							<?php endforeach; ?>
						</select>
						<p class="description"><a href="<?php echo admin_url( 'admin.php?page=certificate-downloader-courses' ); ?>">Manage Courses</a></p>
					</td>
				</tr>
			</table>
			<p class="submit">
				<input type="submit" name="sjs_cert_add_student" id="submit" class="button button-primary" value="Add Student" aria-label="Add new student to the system">
			</p>
		</form>
	</div>
</div>
