<div class="wrap">
	<h1>Courses</h1>
	<?php settings_errors( 'sjs_cert_messages' ); ?>

	<div style="display: flex; gap: 20px; align-items: flex-start;">
		
		<!-- List Courses -->
		<div style="flex: 2;">
			<table class="wp-list-table widefat fixed striped">
				<thead>
					<tr>
						<th>ID</th>
						<th>Course Name</th>
						<th>Actions</th>
					</tr>
				</thead>
				<tbody>
					<?php if ( ! empty( $courses ) ) : ?>
						<?php foreach ( $courses as $course ) : ?>
							<tr>
								<td><?php echo esc_html( $course->id ); ?></td>
								<td><?php echo esc_html( $course->course_name ); ?></td>
								<td>
									<?php 
									$edit_url = admin_url( 'admin.php?page=certificate-downloader-edit-course&id=' . $course->id );
									$delete_url = wp_nonce_url( 
										admin_url( 'admin.php?page=certificate-downloader-courses&action=delete&id=' . $course->id ), 
										'sjs_cert_delete_course_nonce' 
									); 
									?>
								<a href="<?php echo $edit_url; ?>" class="button button-small" aria-label="Edit <?php echo esc_attr( $course->course_name ); ?>">Edit</a>
								<a href="<?php echo $delete_url; ?>" class="button button-small button-link-delete" onclick="return confirm('Are you sure you want to delete this course?')" aria-label="Delete <?php echo esc_attr( $course->course_name ); ?>">Delete</a>
								</td>
							</tr>
						<?php endforeach; ?>
					<?php else : ?>
						<tr>
							<td colspan="3" style="text-align: center; padding: 40px 20px;">
								<div class="empty-state">
									<svg style="width: 48px; height: 48px; margin-bottom: 16px; opacity: 0.3;" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
										<path d="M2 3h6a4 4 0 0 1 4 4v14a3 3 0 0 0-3-3H2z"></path>
										<path d="M22 3h-6a4 4 0 0 0-4 4v14a3 3 0 0 1 3-3h7z"></path>
									</svg>
									<h3 style="margin: 0 0 8px 0; font-size: 16px; color: #2c3338;">No Courses Yet</h3>
									<p style="margin: 0 0 16px 0; color: #646970;">Create your first course to start issuing certificates to students.</p>
									<p style="margin: 0;">Use the form on the right to add a new course →</p>
								</div>
							</td>
						</tr>
					<?php endif; ?>
				</tbody>
			</table>
		</div>

		<!-- Add Course Form -->
		<div class="card" style="flex: 1; margin-top: 0;">
			<h2>Add New Course</h2>
			<form method="post" action="">
				<?php wp_nonce_field( 'sjs_cert_add_course_nonce' ); ?>
				<p>
					<label for="course_name">Course Name</label>
					<input name="course_name" type="text" id="course_name" class="large-text" required>
				</p>
				<p class="submit">
					<input type="submit" name="sjs_cert_add_course" id="submit" class="button button-primary" value="Add Course">
				</p>
			</form>
		</div>

	</div>
</div>
