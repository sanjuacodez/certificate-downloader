<div class="wrap sjs-cert-dashboard">
	<h1>Certificate Dashboard</h1>
	
	<div class="sjs-cert-stats-grid">
		<div class="sjs-cert-stat-card">
			<div class="stat-icon dashicons dashicons-groups"></div>
			<div class="stat-content">
				<h3>Total Students</h3>
				<span class="stat-value"><?php echo intval( $stats->total_students ); ?></span>
			</div>
		</div>
		<div class="sjs-cert-stat-card">
			<div class="stat-icon dashicons dashicons-awards"></div>
			<div class="stat-content">
				<h3>Certificates Issued</h3>
				<span class="stat-value"><?php echo intval( $stats->issued ); ?></span>
			</div>
		</div>
		<div class="sjs-cert-stat-card">
			<div class="stat-icon dashicons dashicons-clock"></div>
			<div class="stat-content">
				<h3>Pending Issue</h3>
				<span class="stat-value"><?php echo intval( $stats->pending ); ?></span>
			</div>
		</div>
		<div class="sjs-cert-stat-card">
			<div class="stat-icon dashicons dashicons-download"></div>
			<div class="stat-content">
				<h3>Total Downloads</h3>
				<span class="stat-value"><?php echo intval( $stats->downloads ); ?></span>
			</div>
		</div>
	</div>

	<div class="sjs-cert-dashboard-columns">
		<div class="sjs-cert-column">
			<div class="card">
				<h2>Recent Activity</h2>
				<?php if ( ! empty( $recent_students ) ) : ?>
					<table class="widefat striped">
						<thead>
							<tr>
								<th>Name</th>
								<th>Status</th>
								<th>Date</th>
							</tr>
						</thead>
						<tbody>
							<?php foreach ( $recent_students as $student ) : ?>
								<tr>
									<td><?php echo esc_html( $student->full_name ); ?></td>
									<td>
										<?php if ( $student->issue_status === 'issued' ) : ?>
											<span class="sjs-badge success">Issued</span>
										<?php else : ?>
											<span class="sjs-badge warning">Pending</span>
										<?php endif; ?>
									</td>
									<td><?php echo date( 'M j', strtotime( $student->created_at ) ); ?></td>
								</tr>
							<?php endforeach; ?>
						</tbody>
					</table>
				<?php else : ?>
					<p>No recent activity.</p>
				<?php endif; ?>
				<p style="text-align: right;">
					<a href="<?php echo admin_url( 'admin.php?page=certificate-downloader-students' ); ?>">View All Students &rarr;</a>
				</p>
			</div>
		</div>
		
		<div class="sjs-cert-column">
			<div class="card">
				<h2>Quick Actions</h2>
				<ul class="sjs-cert-quick-links">
					<li>
						<a href="<?php echo admin_url( 'admin.php?page=certificate-downloader-students' ); ?>">
							<span class="dashicons dashicons-plus"></span> Add New Student
						</a>
					</li>
					<li>
						<a href="<?php echo admin_url( 'admin.php?page=certificate-downloader-students' ); ?>">
							<span class="dashicons dashicons-upload"></span> Import CSV
						</a>
					</li>
					<li>
						<a href="<?php echo admin_url( 'admin.php?page=certificate-downloader-courses' ); ?>">
							<span class="dashicons dashicons-book"></span> Manage Courses
						</a>
					</li>
					<li>
						<a href="<?php echo admin_url( 'admin.php?page=certificate-downloader-settings' ); ?>">
							<span class="dashicons dashicons-admin-settings"></span> Settings
						</a>
					</li>
				</ul>
			</div>
		</div>
	</div>
</div>
