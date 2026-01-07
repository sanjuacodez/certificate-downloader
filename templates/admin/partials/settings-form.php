<?php if ( ! defined( 'ABSPATH' ) ) exit; ?>

<form method="post" enctype="multipart/form-data">
	<?php wp_nonce_field( 'sjs_cert_save_course_nonce' ); ?>
	
	<div style="padding: 20px; border-bottom: 1px solid #f0f0f1;">
		<div class="form-field">
			<label>Course Name</label>
			<input type="text" name="course_name" value="<?php echo esc_attr( $course->course_name ); ?>" class="widefat">
		</div>
	</div>

	<div class="sjs-cert-settings-accordion">
		<!-- General & Layout -->
		<details open>
			<summary>General & Layout</summary>
			<div class="accordion-content">
				<div style="display: grid; grid-template-columns: 1fr 1fr; gap: 10px;">
					<div class="form-field">
						<label>Orientation</label>
						<select name="template_config[layout][orientation]" id="orientation" class="widefat">
							<option value="landscape" <?php selected( sjs_get_config($config, 'layout.orientation'), 'landscape' ); ?>>Landscape</option>
							<option value="portrait" <?php selected( sjs_get_config($config, 'layout.orientation'), 'portrait' ); ?>>Portrait</option>
						</select>
					</div>
					<div class="form-field">
						<label>Page Size</label>
						<select name="template_config[layout][page_size]" id="page_size" class="widefat">
							<option value="A4" <?php selected( sjs_get_config($config, 'layout.page_size'), 'A4' ); ?>>A4</option>
							<option value="Letter" <?php selected( sjs_get_config($config, 'layout.page_size'), 'Letter' ); ?>>Letter</option>
							<option value="Legal" <?php selected( sjs_get_config($config, 'layout.page_size'), 'Legal' ); ?>>Legal</option>
						</select>
					</div>
				</div>
				<div class="form-field">
					<label>Layout Mode</label>
					<select name="template_config[layout][mode]" id="layout_mode" class="widefat">
						<option value="distribute" <?php selected( sjs_get_config($config, 'layout.mode'), 'distribute' ); ?>>Distribute Vertically</option>
						<option value="center" <?php selected( sjs_get_config($config, 'layout.mode'), 'center' ); ?>>Center All</option>
						<option value="top" <?php selected( sjs_get_config($config, 'layout.mode'), 'top' ); ?>>Top Aligned</option>
						<option value="two_column" <?php selected( sjs_get_config($config, 'layout.mode'), 'two_column' ); ?>>Two Column (Sidebar)</option>
					</select>
				</div>
				<div class="form-field">
					<label>Content Padding (e.g. 40px 40px 40px 40px)</label>
					<input type="text" name="template_config[layout][content_padding]" id="content_padding" value="<?php echo esc_attr( sjs_get_config($config, 'layout.content_padding', '40px') ); ?>" class="widefat" placeholder="Top Right Bottom Left">
				</div>
				<div id="two-column-settings" style="display: <?php echo (sjs_get_config($config, 'layout.mode') === 'two_column') ? 'block' : 'none'; ?>; border-top: 1px solid #ddd; padding-top: 10px; margin-top: 10px;">
					<div style="display: grid; grid-template-columns: 1fr 1fr; gap: 10px;">
						<div class="form-field">
							<label>Sidebar BG</label>
							<input type="color" name="template_config[layout][sidebar_bg]" id="sidebar_bg" value="<?php echo esc_attr( sjs_get_config($config, 'layout.sidebar_bg', '#f0f0f0') ); ?>">
						</div>
						<div class="form-field">
							<label>Sidebar Width (%)</label>
							<input type="number" name="template_config[layout][sidebar_width]" id="sidebar_width" min="10" max="50" value="<?php echo esc_attr( sjs_get_config($config, 'layout.sidebar_width', 30) ); ?>" class="widefat">
						</div>
					</div>
				</div>
			</div>
		</details>

		<!-- Background -->
		<details>
			<summary>Background</summary>
			<div class="accordion-content">
				<div class="form-field">
					<label>Background Color</label>
					<input type="color" name="template_config[background][background_color]" id="background_color" value="<?php echo esc_attr( sjs_get_config($config, 'background.background_color') ); ?>">
				</div>
				<div class="form-field">
					<label>Background Image</label>
					<input type="file" name="background_image_file" id="background_image_file" accept="image/*">
					<input type="hidden" name="template_config[background][background_image_url]" id="background_image_url" value="<?php echo esc_attr( sjs_get_config($config, 'background.background_image_url') ); ?>">
					<?php if ( sjs_get_config($config, 'background.background_image_url') ) : ?>
						<div style="margin-top: 5px;" id="existing-bg-preview">
							<img src="<?php echo esc_url( sjs_get_config($config, 'background.background_image_url') ); ?>" style="max-height: 50px; vertical-align: middle;">
							<label><input type="checkbox" name="remove_background_image" value="1"> Remove</label>
						</div>
					<?php endif; ?>
				</div>
				<div class="form-field">
					<label>Opacity</label>
					<input type="range" name="template_config[background][background_opacity]" id="background_opacity" min="0.1" max="1.0" step="0.1" value="<?php echo esc_attr( sjs_get_config($config, 'background.background_opacity') ); ?>" oninput="this.nextElementSibling.innerText = this.value">
					<span><?php echo esc_attr( sjs_get_config($config, 'background.background_opacity') ); ?></span>
				</div>
			</div>
		</details>

		<!-- Borders -->
		<details>
			<summary>Borders</summary>
			<div class="accordion-content">
				<h4>Outer Border</h4>
				<div class="form-field">
					<label>Style</label>
					<select name="template_config[border][outer_border][style]" id="border_style" class="widefat">
						<?php foreach(['none', 'solid', 'double', 'dashed', 'dotted', 'groove', 'ridge', 'inset', 'outset'] as $s): ?>
							<option value="<?php echo $s; ?>" <?php selected( sjs_get_config($config, 'border.outer_border.style'), $s ); ?>><?php echo ucfirst($s); ?></option>
						<?php endforeach; ?>
					</select>
				</div>
				<div style="display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 10px;">
					<div class="form-field">
						<label>Color</label>
						<input type="color" name="template_config[border][outer_border][color]" id="border_color" value="<?php echo esc_attr( sjs_get_config($config, 'border.outer_border.color') ); ?>">
					</div>
					<div class="form-field">
						<label>Width (px)</label>
						<input type="number" name="template_config[border][outer_border][width]" id="border_width" value="<?php echo esc_attr( sjs_get_config($config, 'border.outer_border.width') ); ?>" class="widefat">
					</div>
					<div class="form-field">
						<label>Radius (px)</label>
						<input type="number" name="template_config[border][outer_border][radius]" id="border_radius" value="<?php echo esc_attr( sjs_get_config($config, 'border.outer_border.radius', 0) ); ?>" class="widefat">
					</div>
				</div>

				<h4>Inner Border</h4>
				<label><input type="checkbox" name="template_config[border][inner_border][enabled]" id="inner_border_enabled" value="1" <?php checked( sjs_get_config($config, 'border.inner_border.enabled'), true ); ?>> Enable Inner Border</label>
				<div style="display: grid; grid-template-columns: 1fr 1fr; gap: 10px; margin-top: 10px;">
					<div class="form-field">
						<label>Style</label>
						<select name="template_config[border][inner_border][style]" id="inner_border_style" class="widefat">
							<?php foreach(['solid', 'dashed'] as $s): ?>
								<option value="<?php echo $s; ?>" <?php selected( sjs_get_config($config, 'border.inner_border.style'), $s ); ?>><?php echo ucfirst($s); ?></option>
							<?php endforeach; ?>
						</select>
					</div>
					<div class="form-field">
						<label>Color</label>
						<input type="color" name="template_config[border][inner_border][color]" id="inner_border_color" value="<?php echo esc_attr( sjs_get_config($config, 'border.inner_border.color', '#e6d8a8') ); ?>">
					</div>
					<div class="form-field">
						<label>Width (px)</label>
						<input type="number" name="template_config[border][inner_border][width]" id="inner_border_width" value="<?php echo esc_attr( sjs_get_config($config, 'border.inner_border.width', 3) ); ?>" class="widefat">
					</div>
				</div>
			</div>
		</details>

		<!-- Header -->
		<details>
			<summary>Header</summary>
			<div class="accordion-content">
				<div class="form-field">
					<label>Certificate Title</label>
					<input type="text" name="template_config[meta][certificate_title]" id="main_heading" value="<?php echo esc_attr( sjs_get_config($config, 'meta.certificate_title') ); ?>" class="widefat">
				</div>
				<div class="form-field">
					<label>Logo</label>
					<input type="file" name="logo_file" id="logo_file" accept="image/*">
					<input type="hidden" name="template_config[header][logo_url]" id="logo_url" value="<?php echo esc_attr( sjs_get_config($config, 'header.logo_url') ); ?>">
					<?php if ( sjs_get_config($config, 'header.logo_url') ) : ?>
						<img src="<?php echo esc_url( sjs_get_config($config, 'header.logo_url') ); ?>" style="max-height: 50px;">
					<?php endif; ?>
				</div>
				<div style="display: grid; grid-template-columns: 1fr 1fr; gap: 10px;">
					<div class="form-field">
						<label>Height (px)</label>
						<input type="number" name="template_config[header][logo_height]" id="logo_height" value="<?php echo esc_attr( sjs_get_config($config, 'header.logo_height') ); ?>" class="widefat">
					</div>
					<div class="form-field">
						<label>Alignment</label>
						<select name="template_config[header][logo_alignment]" id="logo_alignment" class="widefat">
							<option value="left" <?php selected( sjs_get_config($config, 'header.logo_alignment'), 'left' ); ?>>Left</option>
							<option value="center" <?php selected( sjs_get_config($config, 'header.logo_alignment'), 'center' ); ?>>Center</option>
							<option value="right" <?php selected( sjs_get_config($config, 'header.logo_alignment'), 'right' ); ?>>Right</option>
						</select>
					</div>
				</div>
			</div>
		</details>

		<!-- Typography -->
		<details>
			<summary>Typography</summary>
			<div class="accordion-content">
				<h4>Heading Font</h4>
				<div class="form-field">
					<select name="template_config[typography][heading_font][family]" id="heading_font_family" class="widefat">
						<?php foreach ( $fonts as $font ) : ?>
							<option value="<?php echo esc_attr( $font ); ?>" <?php selected( sjs_get_config($config, 'typography.heading_font.family'), $font ); ?>><?php echo esc_html( $font ); ?></option>
						<?php endforeach; ?>
					</select>
				</div>
				<div style="display: grid; grid-template-columns: 1fr 1fr; gap: 10px;">
					<input type="number" name="template_config[typography][heading_font][size]" id="heading_font_size" value="<?php echo esc_attr( sjs_get_config($config, 'typography.heading_font.size') ); ?>" class="widefat" placeholder="Size">
					<input type="color" name="template_config[typography][heading_font][color]" id="heading_font_color" value="<?php echo esc_attr( sjs_get_config($config, 'typography.heading_font.color') ); ?>">
				</div>

				<h4>Body Font</h4>
				<div class="form-field">
					<select name="template_config[typography][body_font][family]" id="body_font_family" class="widefat">
						<?php foreach ( $fonts as $font ) : ?>
							<option value="<?php echo esc_attr( $font ); ?>" <?php selected( sjs_get_config($config, 'typography.body_font.family'), $font ); ?>><?php echo esc_html( $font ); ?></option>
						<?php endforeach; ?>
					</select>
				</div>
				<div style="display: grid; grid-template-columns: 1fr 1fr; gap: 10px;">
					<input type="number" name="template_config[typography][body_font][size]" id="body_font_size" value="<?php echo esc_attr( sjs_get_config($config, 'typography.body_font.size') ); ?>" class="widefat" placeholder="Size">
					<input type="color" name="template_config[typography][body_font][color]" id="body_font_color" value="<?php echo esc_attr( sjs_get_config($config, 'typography.body_font.color') ); ?>">
				</div>

				<h4>Name Font (Student Name)</h4>
				<div class="form-field">
					<select name="template_config[typography][name_font][family]" id="name_font_family" class="widefat">
						<?php foreach ( $fonts as $font ) : ?>
							<option value="<?php echo esc_attr( $font ); ?>" <?php selected( sjs_get_config($config, 'typography.name_font.family'), $font ); ?>><?php echo esc_html( $font ); ?></option>
						<?php endforeach; ?>
					</select>
				</div>
				<div style="display: grid; grid-template-columns: 1fr 1fr 1fr 1fr; gap: 10px;">
					<input type="number" name="template_config[typography][name_font][size]" id="name_font_size" value="<?php echo esc_attr( sjs_get_config($config, 'typography.name_font.size', 32) ); ?>" class="widefat" placeholder="Size">
					<input type="color" name="template_config[typography][name_font][color]" id="name_font_color" value="<?php echo esc_attr( sjs_get_config($config, 'typography.name_font.color', '#000000') ); ?>">
					<select name="template_config[typography][name_font][font_style]" id="name_font_style" class="widefat">
						<option value="normal" <?php selected( sjs_get_config($config, 'typography.name_font.font_style'), 'normal' ); ?>>Normal</option>
						<option value="italic" <?php selected( sjs_get_config($config, 'typography.name_font.font_style'), 'italic' ); ?>>Italic</option>
						<option value="bold" <?php selected( sjs_get_config($config, 'typography.name_font.font_style'), 'bold' ); ?>>Bold</option>
						<option value="bold-italic" <?php selected( sjs_get_config($config, 'typography.name_font.font_style'), 'bold-italic' ); ?>>Bold Italic</option>
					</select>
					<select name="template_config[typography][name_font][text_decoration]" id="name_font_decoration" class="widefat">
						<option value="none" <?php selected( sjs_get_config($config, 'typography.name_font.text_decoration'), 'none' ); ?>>No Decoration</option>
						<option value="underline" <?php selected( sjs_get_config($config, 'typography.name_font.text_decoration'), 'underline' ); ?>>Underline</option>
					</select>
				</div>
			</div>
		</details>

		<!-- Content Blocks -->
		<details>
			<summary>Content Blocks</summary>
			<div class="accordion-content">
				<?php 
				$blocks = ['pre_title' => 'Pre Title', 'student_name' => 'Student Name Placeholder', 'main_text' => 'Main Text', 'course_name' => 'Course Name Placeholder', 'description' => 'Description'];
				foreach($blocks as $key => $label): 
				?>
				<div class="form-field">
					<label><?php echo $label; ?></label>
					<input type="text" name="template_config[content_blocks][<?php echo $key; ?>][text]" value="<?php echo esc_attr( sjs_get_config($config, "content_blocks.$key.text") ); ?>" class="widefat">
				</div>
				<?php endforeach; ?>
			</div>
		</details>

		<!-- Visual Elements -->
		<details>
			<summary>Visual Elements</summary>
			<div class="accordion-content">
				<h4>Student Photo</h4>
				<label><input type="checkbox" name="template_config[student_photo][enabled]" id="enable_student_photo" value="1" <?php checked( sjs_get_config($config, 'student_photo.enabled'), true ); ?>> Enable</label>
				<div style="display: grid; grid-template-columns: 1fr 1fr; gap: 10px; margin-top: 10px;">
					<input type="number" name="template_config[student_photo][size]" id="photo_size" value="<?php echo esc_attr( sjs_get_config($config, 'student_photo.size') ); ?>" class="widefat" placeholder="Size">
					<select name="template_config[student_photo][shape]" id="photo_shape" class="widefat">
						<option value="square" <?php selected( sjs_get_config($config, 'student_photo.shape'), 'square' ); ?>>Square</option>
						<option value="circle" <?php selected( sjs_get_config($config, 'student_photo.shape'), 'circle' ); ?>>Circle</option>
					</select>
				</div>

				<h4>QR Code</h4>
				<label><input type="checkbox" name="template_config[qr_code][enabled]" id="enable_qr_code" value="1" <?php checked( sjs_get_config($config, 'qr_code.enabled'), true ); ?>> Enable</label>
				<div style="display: grid; grid-template-columns: 1fr 1fr; gap: 10px; margin-top: 10px;">
					<input type="number" name="template_config[qr_code][size]" id="qr_size" value="<?php echo esc_attr( sjs_get_config($config, 'qr_code.size') ); ?>" class="widefat" placeholder="Size">
					<select name="template_config[qr_code][position]" id="qr_position" class="widefat">
						<option value="bottom_left" <?php selected( sjs_get_config($config, 'qr_code.position'), 'bottom_left' ); ?>>Bottom Left</option>
						<option value="bottom_right" <?php selected( sjs_get_config($config, 'qr_code.position'), 'bottom_right' ); ?>>Bottom Right</option>
					</select>
				</div>

				<h4>Seal / Badge</h4>
				<label><input type="checkbox" name="template_config[seal_badge][enabled]" id="seal_enabled" value="1" <?php checked( sjs_get_config($config, 'seal_badge.enabled'), true ); ?>> Enable Seal</label>
				<div style="display: block; margin-top: 10px;">
					<div class="form-field">
						<label>Seal Image</label>
						<input type="file" name="seal_image_file" id="seal_image_file" accept="image/*">
						<input type="hidden" name="template_config[seal_badge][image_url]" id="seal_image_url" value="<?php echo esc_attr( sjs_get_config($config, 'seal_badge.image_url') ); ?>">
						<?php if ( sjs_get_config($config, 'seal_badge.image_url') ) : ?>
							<img src="<?php echo esc_url( sjs_get_config($config, 'seal_badge.image_url') ); ?>" id="existing-seal-preview" style="max-height: 50px;">
						<?php endif; ?>
					</div>
					<div style="display: grid; grid-template-columns: 1fr 1fr; gap: 10px;">
						<div class="form-field">
							<label>Seal Size (px)</label>
							<input type="number" name="template_config[seal_badge][size]" id="seal_size" value="<?php echo esc_attr( sjs_get_config($config, 'seal_badge.size', 100) ); ?>" class="widefat">
						</div>
						<div class="form-field">
							<label>Position</label>
							<select name="template_config[seal_badge][position]" id="seal_position" class="widefat">
								<option value="top_right" <?php selected( sjs_get_config($config, 'seal_badge.position'), 'top_right' ); ?>>Top Right</option>
								<option value="top_left" <?php selected( sjs_get_config($config, 'seal_badge.position'), 'top_left' ); ?>>Top Left</option>
								<option value="bottom_center" <?php selected( sjs_get_config($config, 'seal_badge.position'), 'bottom_center' ); ?>>Bottom Center</option>
							</select>
						</div>
					</div>
					<div class="form-field">
						<label>Seal Text (if no image)</label>
						<input type="text" name="template_config[seal_badge][text]" id="seal_text" value="<?php echo esc_attr( sjs_get_config($config, 'seal_badge.text', 'Awarded ' . date('Y')) ); ?>" class="widefat">
					</div>
				</div>
			</div>
		</details>

		<!-- Signatures -->
		<details>
			<summary>Signatures</summary>
			<div class="accordion-content">
				<div id="signatures-container">
					<?php 
					$sigs = sjs_get_config($config, 'signatures');
					if(!is_array($sigs)) $sigs = [];
					foreach ( $sigs as $index => $sig ) : 
					?>
						<div class="signature-item">
							<div class="form-field">
								<label>Name</label>
								<input type="text" name="template_config[signatures][<?php echo $index; ?>][name]" value="<?php echo esc_attr( isset($sig['name']) ? $sig['name'] : '' ); ?>" class="widefat sig-name">
							</div>
							<div class="form-field">
								<label>Role</label>
								<input type="text" name="template_config[signatures][<?php echo $index; ?>][role]" value="<?php echo esc_attr( isset($sig['role']) ? $sig['role'] : '' ); ?>" class="widefat sig-role">
							</div>
							<div class="form-field">
								<label>Image</label>
								<input type="file" name="signature_files[<?php echo $index; ?>]" class="sig-file-input" accept="image/*">
								<input type="hidden" name="template_config[signatures][<?php echo $index; ?>][image_url]" value="<?php echo esc_attr( isset($sig['image_url']) ? $sig['image_url'] : '' ); ?>">
								<?php if ( ! empty( $sig['image_url'] ) ) : ?>
									<img src="<?php echo esc_url( $sig['image_url'] ); ?>" class="existing-sig-img" style="max-height: 30px;">
								<?php endif; ?>
							</div>
							<button type="button" class="button remove-signature">Remove</button>
						</div>
					<?php endforeach; ?>
				</div>
				<button type="button" id="add-signature" class="button">Add Signature</button>
			</div>
		</details>

		<!-- Footer -->
		<details>
			<summary>Footer</summary>
			<div class="accordion-content">
				<div class="form-field">
					<label>Date Label</label>
					<input type="text" name="template_config[footer][issue_date][label]" id="date_label" value="<?php echo esc_attr( sjs_get_config($config, 'footer.issue_date.label') ); ?>" class="widefat">
				</div>
				<div class="form-field">
					<label>Footer Logo</label>
					<input type="file" name="footer_logo_file" id="footer_logo_file" accept="image/*">
					<input type="hidden" name="template_config[footer][footer_logo_url]" id="footer_logo_url" value="<?php echo esc_attr( sjs_get_config($config, 'footer.footer_logo_url') ); ?>">
					<?php if ( sjs_get_config($config, 'footer.footer_logo_url') ) : ?>
						<img src="<?php echo esc_url( sjs_get_config($config, 'footer.footer_logo_url') ); ?>" style="max-height: 50px;">
					<?php endif; ?>
				</div>
				<div style="display: grid; grid-template-columns: 1fr 1fr; gap: 10px;">
					<div class="form-field">
						<label>Height (px)</label>
						<input type="number" name="template_config[footer][footer_logo_height]" id="footer_logo_height" value="<?php echo esc_attr( sjs_get_config($config, 'footer.footer_logo_height') ); ?>" class="widefat">
					</div>
					<div class="form-field">
						<label>Alignment</label>
						<select name="template_config[footer][footer_logo_alignment]" id="footer_logo_alignment" class="widefat">
							<option value="left" <?php selected( sjs_get_config($config, 'footer.footer_logo_alignment'), 'left' ); ?>>Left</option>
							<option value="center" <?php selected( sjs_get_config($config, 'footer.footer_logo_alignment'), 'center' ); ?>>Center</option>
							<option value="right" <?php selected( sjs_get_config($config, 'footer.footer_logo_alignment'), 'right' ); ?>>Right</option>
						</select>
					</div>
				</div>
				<div class="form-field">
					<label>Organization Name</label>
					<input type="text" name="template_config[footer][organization_name]" id="organization_name" value="<?php echo esc_attr( sjs_get_config($config, 'footer.organization_name', '[organization_name]') ); ?>" class="widefat">
				</div>
			</div>
		</details>
	</div>

	<div style="padding: 20px; border-top: 1px solid #f0f0f1;">
		<input type="submit" name="sjs_cert_save_course" class="button button-primary" value="Save Course">
	</div>
</form>
