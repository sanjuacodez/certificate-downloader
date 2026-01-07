<?php
$config = isset( $args['config'] ) ? $args['config'] : array();
$student = $args['student'];
$course = $args['course'];

function sjs_get_preview_config($config, $path, $default = '') {
	$keys = explode('.', $path);
	$current = $config;
	foreach ($keys as $key) {
		if (isset($current[$key])) {
			$current = $current[$key];
		} else {
			return $default;
		}
	}
	return $current;
}

// Layout Settings
$orientation = sjs_get_preview_config($config, 'layout.orientation', 'landscape');
$page_size = sjs_get_preview_config($config, 'layout.page_size', 'A4');
$layout_mode = sjs_get_preview_config($config, 'layout.mode', 'distribute');

// Content Padding Handling
$content_padding = sjs_get_preview_config($config, 'layout.content_padding', '40px');
if (is_numeric($content_padding)) $content_padding .= 'px';

$sidebar_bg = sjs_get_preview_config($config, 'layout.sidebar_bg', '#f0f0f0');
$sidebar_width = sjs_get_preview_config($config, 'layout.sidebar_width', 30);

// Borders
$outer_border_style = sjs_get_preview_config($config, 'border.outer_border.style', 'solid');
$outer_border_color = sjs_get_preview_config($config, 'border.outer_border.color', '#cca43b');
$outer_border_width = sjs_get_preview_config($config, 'border.outer_border.width', 8);
$outer_border_radius = sjs_get_preview_config($config, 'border.outer_border.radius', 0);

$inner_border_enabled = sjs_get_preview_config($config, 'border.inner_border.enabled', false);
$inner_border_style = sjs_get_preview_config($config, 'border.inner_border.style', 'solid');
$inner_border_color = sjs_get_preview_config($config, 'border.inner_border.color', '#e6d8a8');
$inner_border_width = sjs_get_preview_config($config, 'border.inner_border.width', 3);

// Background
$background_color = sjs_get_preview_config($config, 'background.background_color', '#ffffff');
$background_image_url = sjs_get_preview_config($config, 'background.background_image_url', '');
$background_opacity = sjs_get_preview_config($config, 'background.background_opacity', 1.0);

// Header
$logo_url = sjs_get_preview_config($config, 'header.logo_url', '');
$logo_height = sjs_get_preview_config($config, 'header.logo_height', 80);
$logo_alignment = sjs_get_preview_config($config, 'header.logo_alignment', 'center');
$main_heading = sjs_get_preview_config($config, 'meta.certificate_title', 'Certificate of Completion');

// Typography
$heading_font_family = sjs_get_preview_config($config, 'typography.heading_font.family', 'Times-Roman');
$heading_font_size = sjs_get_preview_config($config, 'typography.heading_font.size', 40);
$heading_font_color = sjs_get_preview_config($config, 'typography.heading_font.color', '#000000');

$body_font_family = sjs_get_preview_config($config, 'typography.body_font.family', 'Helvetica');
$body_font_size = sjs_get_preview_config($config, 'typography.body_font.size', 18);
$body_font_color = sjs_get_preview_config($config, 'typography.body_font.color', '#333333');

$name_font_family = sjs_get_preview_config($config, 'typography.name_font.family', 'Times New Roman');
$name_font_size = sjs_get_preview_config($config, 'typography.name_font.size', 32);
$name_font_color = sjs_get_preview_config($config, 'typography.name_font.color', '#000000');
$name_font_style = sjs_get_preview_config($config, 'typography.name_font.font_style', 'italic');
$name_font_decoration = sjs_get_preview_config($config, 'typography.name_font.text_decoration', 'none');

// Seal / Badge
$seal_enabled = sjs_get_preview_config($config, 'seal_badge.enabled', false);
$seal_image_url = sjs_get_preview_config($config, 'seal_badge.image_url', '');
$seal_text = sjs_get_preview_config($config, 'seal_badge.text', 'Awarded ' . date('Y'));
$seal_size = sjs_get_preview_config($config, 'seal_badge.size', 100);
$seal_position = sjs_get_preview_config($config, 'seal_badge.position', 'bottom_center');

// QR Code
$qr_enabled = sjs_get_preview_config($config, 'qr_code.enabled', false);
$qr_size = sjs_get_preview_config($config, 'qr_code.size', 80);
$qr_position = sjs_get_preview_config($config, 'qr_code.position', 'bottom_right');
$qr_data = home_url("/?verify_cert=" . $student->admission_number);
$qr_url = "https://api.qrserver.com/v1/create-qr-code/?size=150x150&data=" . urlencode($qr_data);

// Footer
$organization_name = sjs_get_preview_config($config, 'footer.organization_name', '');
$date_label = sjs_get_preview_config($config, 'footer.issue_date.label', 'Date');
$footer_logo_url = sjs_get_preview_config($config, 'footer.footer_logo_url', '');
$footer_logo_height = sjs_get_preview_config($config, 'footer.footer_logo_height', 60);
$footer_logo_alignment = sjs_get_preview_config($config, 'footer.footer_logo_alignment', 'center');

// Signatures
$signatures = sjs_get_preview_config($config, 'signatures', array());

$formatted_date = date_i18n( get_option( 'date_format' ), strtotime( $student->created_at ) );

// Ratio for preview - reduced for frontend
$base_w = 1123; $base_h = 794;
if ($page_size === 'Letter') { $base_w = 1056; $base_h = 816; }
elseif ($page_size === 'Legal') { $base_w = 1344; $base_h = 816; }

if ($orientation === 'portrait') { $temp = $base_w; $base_w = $base_h; $base_h = $temp; }

$preview_w = 300; // Reduced to 300 for smaller preview size
$scale = $preview_w / $base_w;
?>
<!-- Only using DomPDF core fonts: Helvetica, Times-Roman, Courier -->

<div class="sjs-cert-preview-wrapper" style="margin: 20px auto; max-width: 100%; text-align: center;">
	<div class="sjs-cert-preview-frame" style="
		width: <?php echo $base_w; ?>px; 
		height: <?php echo $base_h; ?>px; 
		transform: scale(<?php echo $scale; ?>); 
		transform-origin: top center;
		background-color: <?php echo $background_color; ?>;
		position: relative;
		box-shadow: 0 0 20px rgba(0,0,0,0.1);
		overflow: hidden;
		box-sizing: border-box;
		display: inline-block;
	">
		<!-- Background Layer -->
		<?php if ($background_image_url): ?>
			<img src="<?php echo esc_url($background_image_url); ?>" style="position: absolute; top:0; left:0; width:100%; height:100%; object-fit: cover; opacity: <?php echo $background_opacity; ?>; z-index: 1;">
		<?php endif; ?>

		<!-- Border Layer -->
		<div style="position: absolute; top:0; left:0; width:100%; height:100%; padding: 40px; box-sizing: border-box; z-index: 2; pointer-events: none;">
			<div style="width: 100%; height: 100%; box-sizing: border-box; border-radius: <?php echo $outer_border_radius; ?>px; <?php echo ($outer_border_style !== 'none') ? "border: {$outer_border_width}px {$outer_border_style} {$outer_border_color};" : ""; ?> padding: 10px;">
				<?php if ($inner_border_enabled): ?>
					<div style="width: 100%; height: 100%; box-sizing: border-box; border-radius: <?php echo max(0, $outer_border_radius - 10); ?>px; border: <?php echo $inner_border_width; ?>px <?php echo $inner_border_style; ?> <?php echo $inner_border_color; ?>;"></div>
				<?php endif; ?>
			</div>
		</div>

		<!-- Seal Layer -->
		<?php if ($seal_enabled): 
			$seal_css = "position: absolute; z-index: 10; width: {$seal_size}px;";
			if ($seal_position === 'top_right') $seal_css .= " top: 60px; right: 60px;";
			elseif ($seal_position === 'top_left') $seal_css .= " top: 60px; left: 60px;";
			else $seal_css .= " bottom: 60px; left: 50%; transform: translateX(-50%);";
		?>
			<div class="seal-badge" style="<?php echo $seal_css; ?>">
				<?php if (!empty($seal_image_url)): ?>
					<img src="<?php echo esc_url($seal_image_url); ?>" style="width: 100%; height: auto;">
				<?php else: ?>
					<div style="border: 2px solid <?php echo $body_font_color; ?>; border-radius: 50%; height: <?php echo $seal_size; ?>px; display: flex; align-items: center; justify-content: center; font-size: 10px; font-weight: bold; background: rgba(255,255,255,0.7); padding: 5px; box-sizing: border-box; text-align: center; color: <?php echo $body_font_color; ?>;">
						<?php echo esc_html($seal_text); ?>
					</div>
				<?php endif; ?>
			</div>
		<?php endif; ?>

		<!-- Content Layer -->
		<div style="position: relative; z-index: 3; width: 100%; height: 100%; padding: <?php echo $content_padding; ?>; box-sizing: border-box;">
			<table style="width: 100%; height: 100%; border-collapse: collapse; table-layout: fixed;">
				<?php if ($layout_mode === 'distribute'): ?>
					<tr>
						<td style="height: 1%; vertical-align: top; text-align: center;">
							<div class="header">
								<?php if ($logo_url): ?>
									<div style="text-align: <?php echo $logo_alignment; ?>;">
										<img src="<?php echo esc_url($logo_url); ?>" style="max-height: <?php echo $logo_height; ?>px; margin-bottom: 20px;">
									</div>
								<?php endif; ?>
								
								<?php 
								$student_photo_url = !empty($student->photo_url) ? $student->photo_url : '';
								if (sjs_get_preview_config($config, 'student_photo.enabled', false) && !empty($student_photo_url)): 
									$p_size = sjs_get_preview_config($config, 'student_photo.size', 120);
									$p_shape = sjs_get_preview_config($config, 'student_photo.shape', 'circle');
									$p_style = "width: {$p_size}px; height: {$p_size}px; object-fit: cover; margin-bottom: 20px; border-radius: " . ($p_shape === 'circle' ? '50%' : '0') . ";";
								?>
									<img src="<?php echo esc_url($student_photo_url); ?>" style="<?php echo $p_style; ?>">
								<?php endif; ?>

								<h1 style="font-family: '<?php echo $heading_font_family; ?>', serif; font-size: <?php echo $heading_font_size; ?>px; color: <?php echo $heading_font_color; ?>; margin: 0 0 20px;"><?php echo esc_html($main_heading); ?></h1>
							</div>
						</td>
					</tr>
					<tr>
						<td style="vertical-align: middle; text-align: center;">
							<div class="content" style="font-family: '<?php echo $body_font_family; ?>', sans-serif; font-size: <?php echo $body_font_size; ?>px; color: <?php echo $body_font_color; ?>;">
								<?php 
								$blocks = sjs_get_preview_config($config, 'content_blocks', array());
								foreach ($blocks as $key => $block): 
									$text = $block['text'];
									$text = str_replace(['[student_name]', '[course_name]', '[completion_date]'], [$student->full_name, $course->course_name, $formatted_date], $text);
									$style = "margin: 5px 0;";
									if ($key === 'student_name') {
										$style = "font-family: '{$name_font_family}', cursive; font-size: {$name_font_size}px; color: {$name_font_color}; text-decoration: {$name_font_decoration}; margin: 15px 0;";
										if ($name_font_style === 'italic') $style .= " font-style: italic;";
										if ($name_font_style === 'bold') $style .= " font-weight: bold;";
										if ($name_font_style === 'bold-italic') $style .= " font-weight: bold; font-style: italic;";
										if ($name_font_style === 'normal') $style .= " font-weight: normal; font-style: normal;";
									} elseif ($key === 'course_name') {
										$style = "font-weight: bold; font-size: 1.2em; margin: 10px 0;";
									}
									echo "<div style=\"$style\">" . esc_html($text) . "</div>";
								endforeach;
								?>
							</div>
						</td>
					</tr>
					<tr>
						<td style="height: 1%; vertical-align: bottom; text-align: center;">
							<?php render_preview_footer($formatted_date, $date_label, $organization_name, $footer_logo_url, $footer_logo_height, $footer_logo_alignment, $signatures, $qr_enabled, $qr_url, $qr_size, $qr_position); ?>
						</td>
					</tr>
				<?php elseif ($layout_mode === 'center'): ?>
					<tr>
						<td style="vertical-align: middle; text-align: center;">
							<div class="header">
								<?php if ($logo_url): ?>
									<div style="text-align: <?php echo $logo_alignment; ?>;">
										<img src="<?php echo esc_url($logo_url); ?>" style="max-height: <?php echo $logo_height; ?>px; margin-bottom: 20px;">
									</div>
								<?php endif; ?>
								<h1 style="font-family: '<?php echo $heading_font_family; ?>', serif; font-size: <?php echo $heading_font_size; ?>px; color: <?php echo $heading_font_color; ?>; margin: 0 0 20px;"><?php echo esc_html($main_heading); ?></h1>
							</div>
							<div class="content" style="font-family: '<?php echo $body_font_family; ?>', sans-serif; font-size: <?php echo $body_font_size; ?>px; color: <?php echo $body_font_color; ?>;">
								<?php 
								$blocks = sjs_get_preview_config($config, 'content_blocks', array());
								foreach ($blocks as $key => $block): 
									$text = $block['text'];
									$text = str_replace(['[student_name]', '[course_name]', '[completion_date]'], [$student->full_name, $course->course_name, $formatted_date], $text);
									$style = "margin: 5px 0;";
									if ($key === 'student_name') {
										$style = "font-family: '{$name_font_family}', cursive; font-size: {$name_font_size}px; color: {$name_font_color}; text-decoration: {$name_font_decoration}; margin: 15px 0;";
										if ($name_font_style === 'italic') $style .= " font-style: italic;";
										if ($name_font_style === 'bold') $style .= " font-weight: bold;";
										if ($name_font_style === 'bold-italic') $style .= " font-weight: bold; font-style: italic;";
										if ($name_font_style === 'normal') $style .= " font-weight: normal; font-style: normal;";
									}
									echo "<div style=\"$style\">" . esc_html($text) . "</div>";
								endforeach;
								?>
							</div>
							<div style="margin-top: 40px;">
								<?php render_preview_footer($formatted_date, $date_label, $organization_name, $footer_logo_url, $footer_logo_height, $footer_logo_alignment, $signatures, $qr_enabled, $qr_url, $qr_size, $qr_position); ?>
							</div>
						</td>
					</tr>
				<?php elseif ($layout_mode === 'two_column'): ?>
					<tr style="height: 100%;">
						<td width="<?php echo $sidebar_width; ?>%" style="background: <?php echo $sidebar_bg; ?>; vertical-align: top; padding: 40px 20px; border-right: 1px solid rgba(0,0,0,0.1);">
							<?php if ($logo_url): ?>
								<div style="text-align: center; margin-bottom: 30px;">
									<img src="<?php echo esc_url($logo_url); ?>" style="max-height: <?php echo $logo_height; ?>px;">
								</div>
							<?php endif; ?>
							
							<?php 
							$student_photo_url = !empty($student->photo_url) ? $student->photo_url : '';
							if (sjs_get_preview_config($config, 'student_photo.enabled', false) && !empty($student_photo_url)): 
								$p_size = sjs_get_preview_config($config, 'student_photo.size', 120);
								$p_shape = sjs_get_preview_config($config, 'student_photo.shape', 'circle');
								$p_style = "width: {$p_size}px; height: {$p_size}px; object-fit: cover; border-radius: " . ($p_shape === 'circle' ? '50%' : '0') . ";";
							?>
								<div style="text-align: center; margin-bottom: 30px;">
									<img src="<?php echo esc_url($student_photo_url); ?>" style="<?php echo $p_style; ?>">
								</div>
							<?php endif; ?>
						</td>
						<td style="vertical-align: middle; text-align: center; padding: 40px;">
							<h1 style="font-family: '<?php echo $heading_font_family; ?>', serif; font-size: <?php echo $heading_font_size * 0.9; ?>px; color: <?php echo $heading_font_color; ?>; margin-bottom: 30px;"><?php echo esc_html($main_heading); ?></h1>
							<div class="content" style="font-family: '<?php echo $body_font_family; ?>', sans-serif; font-size: <?php echo $body_font_size; ?>px; color: <?php echo $body_font_color; ?>;">
								<?php 
								$blocks = sjs_get_preview_config($config, 'content_blocks', array());
								foreach ($blocks as $key => $block): 
									$text = $block['text'];
									$text = str_replace(['[student_name]', '[course_name]', '[completion_date]'], [$student->full_name, $course->course_name, $formatted_date], $text);
									$style = "margin: 5px 0;";
									if ($key === 'student_name') {
										$style = "font-family: '{$name_font_family}', cursive; font-size: {$name_font_size}px; color: {$name_font_color}; text-decoration: {$name_font_decoration}; margin: 15px 0;";
										if ($name_font_style === 'italic') $style .= " font-style: italic;";
										if ($name_font_style === 'bold') $style .= " font-weight: bold;";
										if ($name_font_style === 'bold-italic') $style .= " font-weight: bold; font-style: italic;";
										if ($name_font_style === 'normal') $style .= " font-weight: normal; font-style: normal;";
									}
									echo "<div style=\"$style\">" . esc_html($text) . "</div>";
								endforeach;
								?>
							</div>
							<div style="margin-top: 50px;">
								<table style="width: 100%; border-collapse: collapse;">
									<tr>
										<td width="50%" style="text-align: left; vertical-align: bottom;">
											<div style="font-weight: bold; margin-bottom: 5px;"><?php echo $formatted_date; ?></div>
											<div style="border-bottom: 1px solid #000; width: 120px; margin-bottom: 5px;"></div>
											<span style="font-size: 11px;"><?php echo esc_html($date_label); ?></span>
										</td>
										<td width="50%" style="vertical-align: bottom;">
											<table style="width: auto; margin: 0 auto;">
												<tr>
													<?php foreach ($signatures as $sig): ?>
														<td style="padding: 0 10px;">
															<div style="text-align: center;">
																<?php if (!empty($sig['image_url'])): ?>
																	<img src="<?php echo esc_url($sig['image_url']); ?>" style="max-height: 45px; display: block; margin: 0 auto;">
																<?php endif; ?>
																<div style="border-bottom: 1px solid #000; width: 100px; margin: 5px auto;"></div>
																<div style="font-weight: bold; font-size: 11px;"><?php echo esc_html($sig['name'] ?? ''); ?></div>
																<div style="font-size: 9px;"><?php echo esc_html($sig['role'] ?? ''); ?></div>
															</div>
														</td>
													<?php endforeach; ?>
												</tr>
											</table>
										</td>
									</tr>
								</table>
							</div>
						</td>
					</tr>
				<?php endif; ?>
			</table>
		</div>
	</div>
</div>

<div class="sjs-cert-actions">
	<a href="<?php echo esc_url( admin_url( 'admin-ajax.php?action=sjs_cert_generate_pdf&admission_number=' . $student->admission_number ) ); ?>" class="button button-primary" target="_blank">Download as PDF</a>
</div>

<?php
function render_preview_footer($date, $label, $org, $logo_url, $logo_h, $alignment, $sigs, $qr_enabled, $qr_url, $qr_size, $qr_pos) {
	$qr_left = ($qr_enabled && $qr_pos === 'bottom_left') ? "<img src='$qr_url' style='width:{$qr_size}px; height:{$qr_size}px;'>" : "";
	$qr_right = ($qr_enabled && $qr_pos === 'bottom_right') ? "<img src='$qr_url' style='width:{$qr_size}px; height:{$qr_size}px;'>" : "";
	?>
	<div class="footer" style="width: 100%;">
		<table style="width: 100%; border-collapse: collapse;">
			<tr>
				<td width="33%" style="text-align: center; vertical-align: bottom;">
					<?php echo $qr_left; ?>
					<div style="font-weight: bold; margin-bottom: 5px;"><?php echo $date; ?></div>
					<div style="border-bottom: 1px solid #000; width: 120px; margin: 5px auto;"></div>
					<span style="font-size: 11px;"><?php echo esc_html($label); ?></span>
				</td>
				<td width="33%" style="text-align: <?php echo $alignment; ?>; vertical-align: bottom;">
					<?php if ($logo_url): ?>
						<img src="<?php echo esc_url($logo_url); ?>" style="max-height: <?php echo $logo_h; ?>px; margin-bottom: 10px;">
					<?php endif; ?>
					<?php if ($org): ?>
						<div style="font-weight: bold; font-size: 13px;"><?php echo esc_html(str_replace('[organization_name]', 'Our Academy', $org)); ?></div>
					<?php endif; ?>
				</td>
				<td width="33%" style="text-align: center; vertical-align: bottom;">
					<?php echo $qr_right; ?>
					<table style="width: auto; margin: 0 auto;">
						<tr>
							<?php foreach ($sigs as $sig): ?>
								<td style="padding: 0 10px;">
									<div style="text-align: center;">
										<?php if (!empty($sig['image_url'])): ?>
											<img src="<?php echo esc_url($sig['image_url']); ?>" style="max-height: 45px; display: block; margin: 0 auto;">
										<?php endif; ?>
										<div style="border-bottom: 1px solid #000; width: 100px; margin: 5px auto;"></div>
										<div style="font-weight: bold; font-size: 11px;"><?php echo esc_html($sig['name'] ?? ''); ?></div>
										<div style="font-size: 9px;"><?php echo esc_html($sig['role'] ?? ''); ?></div>
									</div>
								</td>
							<?php endforeach; ?>
						</tr>
					</table>
				</td>
			</tr>
		</table>
	</div>
	<?php
}
?>
