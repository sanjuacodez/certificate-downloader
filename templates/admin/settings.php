<div class="wrap">
	<h1>Certificate Settings</h1>
	<form method="post" action="options.php" enctype="multipart/form-data">
		<?php settings_fields( 'sjs_cert_options_group' ); ?>
		
		<h2 class="title">General Branding</h2>
		<table class="form-table">
			<tr valign="top">
				<th scope="row">Default Certificate Logo</th>
				<td>
					<div class="logo-upload-wrapper">
						<?php $logo_url = get_option( 'sjs_cert_logo_url' ); ?>
						<input type="hidden" name="sjs_cert_logo_url" id="sjs_cert_logo_url" value="<?php echo esc_attr( $logo_url ); ?>" />
						<div class="logo-preview" style="margin-bottom: 10px;">
							<?php if ( $logo_url ) : ?>
								<img src="<?php echo esc_url( $logo_url ); ?>" style="max-width: 200px; max-height: 100px; border: 1px solid #ddd; padding: 5px;" />
							<?php else : ?>
								<div style="padding: 20px; border: 2px dashed #ddd; text-align: center; color: #999;">No logo selected</div>
							<?php endif; ?>
						</div>
						<button type="button" class="button" id="upload_logo_button" aria-label="Upload organization logo">Upload Logo</button>
						<?php if ( $logo_url ) : ?>
							<button type="button" class="button" id="remove_logo_button" aria-label="Remove uploaded logo">Remove Logo</button>
						<?php endif; ?>
					</div>
					<p class="description">This logo will be used as the default for all course certificates. You can override it for individual courses in the course editor.</p>
				</td>
			</tr>
			<tr valign="top">
				<th scope="row">Organization Name</th>
				<td>
					<input type="text" name="sjs_cert_organization_name" value="<?php echo esc_attr( get_option( 'sjs_cert_organization_name' ) ); ?>" class="regular-text" />
					<p class="description">This will be available as <code>{organization_name}</code> placeholder in certificate templates.</p>
				</td>
			</tr>
		</table>

		<h2 class="title">Email Notifications</h2>
		<p>Settings for automated emails sent to students when their certificate is issued.</p>
		<table class="form-table">
			<tr valign="top">
				<th scope="row">Enable Notifications</th>
				<td>
					<label>
						<input type="checkbox" name="sjs_cert_enable_email" value="1" <?php checked( get_option( 'sjs_cert_enable_email', 0 ), 1 ); ?> />
						Send email automatically when status is changed to "Issued"
					</label>
				</td>
			</tr>
			<tr valign="top">
				<th scope="row">Attach PDF to Email</th>
				<td>
					<label>
						<input type="checkbox" name="sjs_cert_attach_pdf" value="1" <?php checked( get_option( 'sjs_cert_attach_pdf', 1 ), 1 ); ?> />
						Attach certificate PDF to notification email
					</label>
					<p class="description">If enabled, the certificate PDF will be generated and attached to the email. Note: This may increase email sending time.</p>
				</td>
			</tr>
			<tr valign="top">
				<th scope="row">Email Subject</th>
				<td>
					<input type="text" name="sjs_cert_email_subject" value="<?php echo esc_attr( get_option( 'sjs_cert_email_subject', 'Your Certificate is Ready!' ) ); ?>" class="regular-text" />
				</td>
			</tr>
			<tr valign="top">
				<th scope="row">Email Body</th>
				<td>
					<?php 
					$default_body = "Dear {name},\n\nCongratulations! You have successfully completed the course {course}.\n\nYou can download your certificate from the following link:\n{download_url}\n\nWe have also attached your certificate to this email.\n\nBest regards,\nAcademy Team";
					$body = get_option( 'sjs_cert_email_body', $default_body );
					wp_editor( $body, 'sjs_cert_email_body', array( 'textarea_name' => 'sjs_cert_email_body', 'rows' => 10 ) ); 
					?>
					<p class="description">Available tags: <code>{name}</code>, <code>{course}</code>, <code>{download_url}</code>, <code>{admission_number}</code></p>
				</td>
			</tr>
		</table>

		<?php submit_button( 'Save Settings', 'primary', 'submit', true, array( 'aria-label' => 'Save certificate settings' ) ); ?>
	</form>
</div>

<script>
jQuery(document).ready(function($) {
	var mediaUploader;
	
	$('#upload_logo_button').on('click', function(e) {
		e.preventDefault();
		
		if (mediaUploader) {
			mediaUploader.open();
			return;
		}
		
		mediaUploader = wp.media({
			title: 'Select Logo',
			button: { text: 'Use this logo' },
			multiple: false
		});
		
		mediaUploader.on('select', function() {
			var attachment = mediaUploader.state().get('selection').first().toJSON();
			$('#sjs_cert_logo_url').val(attachment.url);
			$('.logo-preview').html('<img src="' + attachment.url + '" style="max-width: 200px; max-height: 100px; border: 1px solid #ddd; padding: 5px;" />');
			$('#remove_logo_button').show();
		});
		
		mediaUploader.open();
	});
	
	$('#remove_logo_button').on('click', function(e) {
		e.preventDefault();
		$('#sjs_cert_logo_url').val('');
		$('.logo-preview').html('<div style="padding: 20px; border: 2px dashed #ddd; text-align: center; color: #999;">No logo selected</div>');
		$(this).hide();
	});
});
</script>
