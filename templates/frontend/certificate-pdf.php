<?php
/**
 * Certificate PDF Template - DomPDF Optimized Version
 * Uses only DomPDF-compatible CSS with inline styles
 */

$config = isset( $args['config'] ) ? $args['config'] : array();
$student = $args['student'];
$course = $args['course'];

/**
 * Config Helper
 */
if (!function_exists('sjs_get_pdf_config')) {
	function sjs_get_pdf_config($config, $path, $default = '') {
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
}

/**
 * Image to Base64 Converter
 */
if (!function_exists('sjs_cert_get_path')) {
	function sjs_cert_get_path($url) {
		if (empty($url)) return '';
		
		// Keep external URLs as-is (QR codes)
		if (strpos($url, 'api.qrserver.com') !== false) {
			return $url;
		}
		
		$upload_dir = wp_upload_dir();
		$site_url = preg_replace('#^https?://#', '', site_url());
		$upload_url = preg_replace('#^https?://#', '', $upload_dir['baseurl']);
		$plugin_url = preg_replace('#^https?://#', '', plugins_url());
		
		$clean_url = preg_replace('#^https?://#', '', $url);
		
		// Check if it's an upload file
		if (strpos($clean_url, $upload_url) !== false) {
			$relative = str_replace($upload_url, '', $clean_url);
			$relative = strtok($relative, '?');
			$local_path = $upload_dir['basedir'] . $relative;
			if (file_exists($local_path)) {
				$ext = strtolower(pathinfo($local_path, PATHINFO_EXTENSION));
				$mime = 'image/' . ($ext === 'jpg' ? 'jpeg' : $ext);
				if ($ext === 'svg') $mime = 'image/svg+xml';
				return 'data:' . $mime . ';base64,' . base64_encode(file_get_contents($local_path));
			}
		}
		
		// Check if it's a plugin file
		if (strpos($clean_url, $plugin_url) !== false) {
			$relative = str_replace($plugin_url, '', $clean_url);
			$relative = strtok($relative, '?');
			$local_path = WP_PLUGIN_DIR . $relative;
			if (file_exists($local_path)) {
				$ext = strtolower(pathinfo($local_path, PATHINFO_EXTENSION));
				$mime = 'image/' . ($ext === 'jpg' ? 'jpeg' : $ext);
				if ($ext === 'svg') $mime = 'image/svg+xml';
				return 'data:' . $mime . ';base64,' . base64_encode(file_get_contents($local_path));
			}
		}
		
		return $url;
	}
}

// Extract all config values
// Only using DomPDF core fonts: Helvetica, Times-Roman, Courier
$orientation = sjs_get_pdf_config($config, 'layout.orientation', 'landscape');
$page_size = sjs_get_pdf_config($config, 'layout.page_size', 'A4');
$layout_mode = sjs_get_pdf_config($config, 'layout.mode', 'distribute');

$content_padding = sjs_get_pdf_config($config, 'layout.content_padding', '40px');
if (is_numeric($content_padding)) $content_padding .= 'px';

$sidebar_bg = sjs_get_pdf_config($config, 'layout.sidebar_bg', '#f0f0f0');
$sidebar_width = sjs_get_pdf_config($config, 'layout.sidebar_width', 30);

// Borders
$outer_border_style = sjs_get_pdf_config($config, 'border.outer_border.style', 'none');
$outer_border_color = sjs_get_pdf_config($config, 'border.outer_border.color', '#cca43b');
$outer_border_width = sjs_get_pdf_config($config, 'border.outer_border.width', 8);
$outer_border_radius = sjs_get_pdf_config($config, 'border.outer_border.radius', 0);

$inner_border_enabled = sjs_get_pdf_config($config, 'border.inner_border.enabled', false);
$inner_border_style = sjs_get_pdf_config($config, 'border.inner_border.style', 'solid');
$inner_border_color = sjs_get_pdf_config($config, 'border.inner_border.color', '#e6d8a8');
$inner_border_width = sjs_get_pdf_config($config, 'border.inner_border.width', 3);

// Background
$background_color = sjs_get_pdf_config($config, 'background.background_color', '#ffffff');
$background_image_url = sjs_get_pdf_config($config, 'background.background_image_url', '');
$background_opacity = sjs_get_pdf_config($config, 'background.background_opacity', 1.0);

// Header
$logo_url = sjs_get_pdf_config($config, 'header.logo_url', '');
$logo_height = sjs_get_pdf_config($config, 'header.logo_height', 80);
$logo_alignment = sjs_get_pdf_config($config, 'header.logo_alignment', 'center');
$main_heading = sjs_get_pdf_config($config, 'meta.certificate_title', 'Certificate of Completion');

// Typography - Only DomPDF core fonts (Helvetica, Times-Roman, Courier)
$heading_font_family = sjs_get_pdf_config($config, 'typography.heading_font.family', 'Times-Roman');
$heading_font_size = sjs_get_pdf_config($config, 'typography.heading_font.size', 40);
$heading_font_color = sjs_get_pdf_config($config, 'typography.heading_font.color', '#000000');

$body_font_family = sjs_get_pdf_config($config, 'typography.body_font.family', 'Helvetica');
$body_font_size = sjs_get_pdf_config($config, 'typography.body_font.size', 18);
$body_font_color = sjs_get_pdf_config($config, 'typography.body_font.color', '#333333');

$name_font_family = sjs_get_pdf_config($config, 'typography.name_font.family', 'Times-Roman');
$name_font_size = sjs_get_pdf_config($config, 'typography.name_font.size', 32);
$name_font_color = sjs_get_pdf_config($config, 'typography.name_font.color', '#000000');
$name_font_style = sjs_get_pdf_config($config, 'typography.name_font.font_style', 'italic');
$name_font_decoration = sjs_get_pdf_config($config, 'typography.name_font.text_decoration', 'none');

// Student Photo
$enable_student_photo = sjs_get_pdf_config($config, 'student_photo.enabled', false);
$photo_size = sjs_get_pdf_config($config, 'student_photo.size', 120);
$photo_shape = sjs_get_pdf_config($config, 'student_photo.shape', 'circle');

// Seal / Badge
$seal_enabled = sjs_get_pdf_config($config, 'seal_badge.enabled', false);
$seal_image_url = sjs_get_pdf_config($config, 'seal_badge.image_url', '');
$seal_text = sjs_get_pdf_config($config, 'seal_badge.text', 'Awarded ' . date('Y'));
$seal_size = sjs_get_pdf_config($config, 'seal_badge.size', 100);
$seal_position = sjs_get_pdf_config($config, 'seal_badge.position', 'bottom_center');

// QR Code
$qr_enabled = sjs_get_pdf_config($config, 'qr_code.enabled', false);
$qr_size = sjs_get_pdf_config($config, 'qr_code.size', 80);
$qr_position = sjs_get_pdf_config($config, 'qr_code.position', 'bottom_right');
$qr_data = home_url("/?verify_cert=" . $student->admission_number);
$qr_url = "https://api.qrserver.com/v1/create-qr-code/?size=150x150&data=" . urlencode($qr_data);

// Footer
$organization_name = sjs_get_pdf_config($config, 'footer.organization_name', '');
$date_label = sjs_get_pdf_config($config, 'footer.issue_date.label', 'Date');
$footer_logo_url = sjs_get_pdf_config($config, 'footer.footer_logo_url', '');
$footer_logo_height = sjs_get_pdf_config($config, 'footer.footer_logo_height', 60);
$footer_logo_alignment = sjs_get_pdf_config($config, 'footer.footer_logo_alignment', 'center');

// Signatures
$signatures = sjs_get_pdf_config($config, 'signatures', array());

$formatted_date = date_i18n( get_option( 'date_format' ), strtotime( $student->created_at ) );

// Convert all images to Base64
$background_image_url = sjs_cert_get_path($background_image_url);
$logo_url = sjs_cert_get_path($logo_url);
$seal_image_url = sjs_cert_get_path($seal_image_url);
$footer_logo_url = sjs_cert_get_path($footer_logo_url);
$qr_url = sjs_cert_get_path($qr_url);
$student_photo_url = !empty($student->photo_url) ? sjs_cert_get_path($student->photo_url) : '';

foreach ($signatures as &$sig) {
	if (!empty($sig['image_url'])) {
		$sig['image_url'] = sjs_cert_get_path($sig['image_url']);
	}
}

// Calculate actual padding in pixels (assume A4 landscape = 297mm = 1123px at 96dpi)
$padding_px = intval($content_padding);

// Calculate page dimensions in pixels (96 DPI standard)
$page_width_px = 1123; // A4 landscape width (297mm)
$page_height_px = 794; // A4 landscape height (210mm)

if (strtolower($page_size) === 'a4') {
	$page_width_px = 1123; // 297mm
	$page_height_px = 794; // 210mm
} elseif (strtolower($page_size) === 'letter') {
	$page_width_px = 1056; // 11 inches = 279.4mm
	$page_height_px = 816; // 8.5 inches = 215.9mm
} elseif (strtolower($page_size) === 'legal') {
	$page_width_px = 1344; // 14 inches = 355.6mm
	$page_height_px = 816; // 8.5 inches = 215.9mm
}

// Swap for portrait
if (strtolower($orientation) === 'portrait') {
	$tmp = $page_width_px;
	$page_width_px = $page_height_px;
	$page_height_px = $tmp;
}

// Calculate border dimensions (avoiding calc() for DomPDF compatibility)
$border_width_px = $page_width_px - ($padding_px * 2);
$border_height_px = $page_height_px - ($padding_px * 2);
$inner_border_width_px = $border_width_px - 20;
$inner_border_height_px = $border_height_px - 20;

// Calculate content area dimensions (for elements that need explicit height)
$content_area_height_px = $page_height_px - ($padding_px * 2);
$table_full_height_px = $page_height_px; // For two_column layout table

// Build border styles
$outer_border_css = '';
if ($outer_border_style !== 'none') {
	$outer_border_css = "border: {$outer_border_width}px {$outer_border_style} {$outer_border_color}; border-radius: {$outer_border_radius}px;";
}

$inner_border_css = '';
if ($inner_border_enabled) {
	$inner_border_css = "border: {$inner_border_width}px {$inner_border_style} {$inner_border_color};";
}
?>
<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8">
<style>
@page {
	size: <?php echo strtolower($page_size . ' ' . $orientation); ?>;
	margin: 0;
}
* {
	margin: 0;
	padding: 0;
	box-sizing: border-box;
}
body {
	margin: 0;
	padding: 0;
	font-family: <?php echo $body_font_family; ?>, sans-serif;
	font-size: <?php echo $body_font_size; ?>px;
	color: <?php echo $body_font_color; ?>;
	background: <?php echo $background_color; ?>;
	overflow: hidden;
	line-height: 1.2;
}

/* Debug Mode - Browser Preview Only */
@media screen {
	/* Using DomPDF core fonts - no external fonts needed */
	
	html {
		background-color: #525659;
		padding: 40px;
		min-height: 100vh;
	}
	body {
		position: relative;
		box-shadow: 0 0 20px rgba(0,0,0,0.5);
		background-color: <?php echo $background_color; ?>;
		margin: 0 auto;
		overflow: visible;
		font-family: <?php echo $body_font_family; ?>, sans-serif;
		<?php 
		// Calculate dimensions based on page size
		$w = '297mm'; $h = '210mm'; // A4 Landscape default
		if (strtolower($page_size) === 'a4') {
			$w = '297mm'; $h = '210mm';
		} elseif (strtolower($page_size) === 'letter') {
			$w = '279.4mm'; $h = '215.9mm';
		} elseif (strtolower($page_size) === 'legal') {
			$w = '355.6mm'; $h = '215.9mm';
		}
		
		// Swap dimensions for portrait
		if (strtolower($orientation) === 'portrait') {
			$tmp = $w; $w = $h; $h = $tmp;
		}
		?>
		width: <?php echo $w; ?>;
		height: <?php echo $h; ?>;
	}
}
</style>
</head>
<body>

<!-- Background Image (if any) -->
<?php if ($background_image_url): ?>
<div style="position: absolute; top: 0; left: 0; width: 100%; height: 100%; z-index: 1;">
	<img src="<?php echo esc_attr($background_image_url); ?>" style="width: 100%; height: 100%; opacity: <?php echo $background_opacity; ?>;">
</div>
<?php endif; ?>

<!-- Outer Border -->
<div style="position: absolute; top: <?php echo $padding_px; ?>px; left: <?php echo $padding_px; ?>px; width: <?php echo $border_width_px; ?>px; height: <?php echo $border_height_px; ?>px; z-index: 2; <?php echo $outer_border_css; ?>">
	<!-- Inner Border -->
	<?php if ($inner_border_enabled): ?>
	<div style="position: absolute; top: 10px; left: 10px; width: <?php echo $inner_border_width_px; ?>px; height: <?php echo $inner_border_height_px; ?>px; <?php echo $inner_border_css; ?>"></div>
	<?php endif; ?>
</div>

<!-- Seal/Badge -->
<?php if ($seal_enabled): ?>
<div style="position: absolute; z-index: 10; width: <?php echo $seal_size; ?>px; <?php 
if ($seal_position === 'top_right') {
	echo "top: 60px; right: 60px;";
} elseif ($seal_position === 'top_left') {
	echo "top: 60px; left: 60px;";
} else {
	echo "bottom: 60px; left: 50%; margin-left: -" . ($seal_size/2) . "px;";
}
?>">
	<?php if (!empty($seal_image_url)): ?>
	<img src="<?php echo esc_attr($seal_image_url); ?>" style="width: 100%;">
	<?php else: ?>
	<div style="border: 2px solid <?php echo $body_font_color; ?>; border-radius: 50%; height: <?php echo $seal_size; ?>px; display: flex; align-items: center; justify-content: center; font-size: 10px; font-weight: bold; background: rgba(255,255,255,0.7); padding: 5px; text-align: center;"><?php echo esc_html($seal_text); ?></div>
	<?php endif; ?>
</div>
<?php endif; ?>

<!-- Content Area -->
<div style="position: relative; z-index: 5; padding: <?php echo $content_padding; ?>; height: <?php echo $content_area_height_px; ?>px; box-sizing: border-box;">

<?php if ($layout_mode === 'distribute'): ?>
	<!-- Distribute Layout -->
	<table style="width: 100%; height: 100%; border-collapse: collapse;">
		<!-- Header Section -->
		<tr>
			<td style="vertical-align: top; text-align: center; height: 1%;">
				<?php if ($logo_url): ?>
				<div style="text-align: <?php echo $logo_alignment; ?>; margin-bottom: 20px;">
					<img src="<?php echo esc_attr($logo_url); ?>" style="max-height: <?php echo $logo_height; ?>px; width: auto;">
				</div>
				<?php endif; ?>
				
				<?php if ($enable_student_photo && !empty($student_photo_url)): ?>
				<div style="text-align: center; margin-bottom: 20px;">
					<img src="<?php echo esc_attr($student_photo_url); ?>" style="width: <?php echo $photo_size; ?>px; height: <?php echo $photo_size; ?>px; <?php echo $photo_shape === 'circle' ? 'border-radius: 50%;' : ''; ?>">
				</div>
				<?php endif; ?>
				
				<h1 style="font-family: <?php echo $heading_font_family; ?>, serif; font-size: <?php echo $heading_font_size; ?>px; color: <?php echo $heading_font_color; ?>; margin: 0 0 20px 0;"><?php echo esc_html($main_heading); ?></h1>
			</td>
		</tr>
		
		<!-- Content Section -->
		<tr>
			<td style="vertical-align: middle; text-align: center;">
				<?php 
				$blocks = sjs_get_pdf_config($config, 'content_blocks', array());
				foreach ($blocks as $key => $block): 
					$text = $block['text'];
					$text = str_replace(['[student_name]', '[course_name]', '[completion_date]'], [$student->full_name, $course->course_name, $formatted_date], $text);
					
					$inline_style = "margin: 5px 0;";
				if ($key === 'student_name') {
					$inline_style = "font-family: {$name_font_family}, cursive; font-size: {$name_font_size}px; color: {$name_font_color}; text-decoration: {$name_font_decoration}; margin: 15px 0;";
					if ($name_font_style === 'italic') $inline_style .= " font-style: italic;";
					if ($name_font_style === 'bold') $inline_style .= " font-weight: bold;";
					if ($name_font_style === 'bold-italic') $inline_style .= " font-weight: bold; font-style: italic;";
				} elseif ($key === 'course_name') {
					$inline_style = "font-weight: bold; font-size: " . ($body_font_size * 1.2) . "px; margin: 10px 0;";
				}
				echo "<div style=\"{$inline_style}\">" . esc_html($text) . "</div>";
				endforeach;
				?>
			</td>
		</tr>
		
		<!-- Footer Section -->
		<tr>
			<td style="vertical-align: bottom; height: 1%;">
				<table style="width: 100%; border-collapse: collapse;">
					<tr>
						<!-- Date Column -->
						<td style="width: 33%; text-align: center; vertical-align: bottom;">
							<?php if ($qr_enabled && $qr_position === 'bottom_left'): ?>
							<img src="<?php echo esc_attr($qr_url); ?>" style="width: <?php echo $qr_size; ?>px; height: <?php echo $qr_size; ?>px; margin-bottom: 10px;">
							<?php endif; ?>
							<div style="font-weight: bold; margin-bottom: 5px;"><?php echo $formatted_date; ?></div>
							<div style="border-bottom: 1px solid #000; width: 120px; margin: 5px auto;"></div>
							<div style="font-size: 11px;"><?php echo esc_html($date_label); ?></div>
						</td>
						
						<!-- Organization Logo Column -->
						<td style="width: 33%; text-align: <?php echo $footer_logo_alignment; ?>; vertical-align: bottom;">
							<?php if ($footer_logo_url): ?>
							<img src="<?php echo esc_attr($footer_logo_url); ?>" style="max-height: <?php echo $footer_logo_height; ?>px; width: auto; margin-bottom: 10px;">
							<?php endif; ?>
							<?php if ($organization_name): ?>
							<div style="font-weight: bold; font-size: 13px;"><?php echo esc_html(str_replace('[organization_name]', 'Our Academy', $organization_name)); ?></div>
							<?php endif; ?>
						</td>
						
						<!-- Signatures Column -->
						<td style="width: 33%; text-align: center; vertical-align: bottom;">
							<?php if ($qr_enabled && $qr_position === 'bottom_right'): ?>
							<img src="<?php echo esc_attr($qr_url); ?>" style="width: <?php echo $qr_size; ?>px; height: <?php echo $qr_size; ?>px; margin-bottom: 10px;">
							<?php endif; ?>
							<table style="width: auto; margin: 0 auto; border-collapse: collapse;">
								<tr>
									<?php foreach ($signatures as $sig): ?>
									<td style="padding: 0 10px; text-align: center;">
										<?php if (!empty($sig['image_url'])): ?>
										<img src="<?php echo esc_attr($sig['image_url']); ?>" style="max-height: 45px; margin-bottom: 5px;">
										<?php endif; ?>
										<div style="border-bottom: 1px solid #000; width: 100px; margin: 5px auto;"></div>
										<div style="font-weight: bold; font-size: 11px;"><?php echo esc_html($sig['name'] ?? ''); ?></div>
										<div style="font-size: 9px;"><?php echo esc_html($sig['role'] ?? ''); ?></div>
									</td>
									<?php endforeach; ?>
								</tr>
							</table>
						</td>
					</tr>
				</table>
			</td>
		</tr>
	</table>

<?php elseif ($layout_mode === 'center'): ?>
	<!-- Center Layout -->
	<table style="width: 100%; height: 100%; border-collapse: collapse;">
		<tr>
			<td style="vertical-align: middle; text-align: center;">
				<?php if ($logo_url): ?>
				<div style="text-align: <?php echo $logo_alignment; ?>; margin-bottom: 20px;">
					<img src="<?php echo esc_attr($logo_url); ?>" style="max-height: <?php echo $logo_height; ?>px; width: auto;">
				</div>
				<?php endif; ?>
				
				<h1 style="font-family: <?php echo $heading_font_family; ?>, serif; font-size: <?php echo $heading_font_size; ?>px; color: <?php echo $heading_font_color; ?>; margin: 0 0 20px 0;"><?php echo esc_html($main_heading); ?></h1>
				
				<?php 
				$blocks = sjs_get_pdf_config($config, 'content_blocks', array());
				foreach ($blocks as $key => $block): 
					$text = $block['text'];
					$text = str_replace(['[student_name]', '[course_name]', '[completion_date]'], [$student->full_name, $course->course_name, $formatted_date], $text);
					
					$inline_style = "margin: 5px 0;";
				if ($key === 'student_name') {
					$inline_style = "font-family: {$name_font_family}, cursive; font-size: {$name_font_size}px; color: {$name_font_color}; text-decoration: {$name_font_decoration}; margin: 15px 0;";
					if ($name_font_style === 'italic') $inline_style .= " font-style: italic;";
					if ($name_font_style === 'bold') $inline_style .= " font-weight: bold;";
				}
				echo "<div style=\"{$inline_style}\">" . esc_html($text) . "</div>";
				endforeach;
				?>
				
				<div style="margin-top: 40px;">
					<!-- Footer for center layout -->
					<table style="width: 100%; border-collapse: collapse;">
						<tr>
							<td style="width: 33%; text-align: center;">
								<div style="font-weight: bold; margin-bottom: 5px;"><?php echo $formatted_date; ?></div>
								<div style="border-bottom: 1px solid #000; width: 120px; margin: 5px auto;"></div>
								<div style="font-size: 11px;"><?php echo esc_html($date_label); ?></div>
							</td>
							<td style="width: 33%; text-align: center;">
								<?php if ($footer_logo_url): ?>
								<img src="<?php echo esc_attr($footer_logo_url); ?>" style="max-height: <?php echo $footer_logo_height; ?>px;">
								<?php endif; ?>
							</td>
							<td style="width: 33%; text-align: center;">
								<?php foreach ($signatures as $sig): ?>
								<div style="display: inline-block; padding: 0 10px;">
									<?php if (!empty($sig['image_url'])): ?>
									<img src="<?php echo esc_attr($sig['image_url']); ?>" style="max-height: 45px;">
									<?php endif; ?>
									<div style="border-bottom: 1px solid #000; width: 100px; margin: 5px auto;"></div>
									<div style="font-weight: bold; font-size: 11px;"><?php echo esc_html($sig['name'] ?? ''); ?></div>
								</div>
								<?php endforeach; ?>
							</td>
						</tr>
					</table>
				</div>
			</td>
		</tr>
	</table>

<?php elseif ($layout_mode === 'top'): ?>
	<!-- Top Aligned Layout -->
	<div style="text-align: center;">
		<?php if ($logo_url): ?>
		<div style="text-align: <?php echo $logo_alignment; ?>; margin-bottom: 20px;">
			<img src="<?php echo esc_attr($logo_url); ?>" style="max-height: <?php echo $logo_height; ?>px; width: auto;">
		</div>
		<?php endif; ?>
		
		<?php if ($enable_student_photo && !empty($student_photo_url)): ?>
		<div style="text-align: center; margin-bottom: 20px;">
			<img src="<?php echo esc_attr($student_photo_url); ?>" style="width: <?php echo $photo_size; ?>px; height: <?php echo $photo_size; ?>px; <?php echo $photo_shape === 'circle' ? 'border-radius: 50%;' : ''; ?>">
		</div>
		<?php endif; ?>
		
		<h1 style="font-family: <?php echo $heading_font_family; ?>, serif; font-size: <?php echo $heading_font_size; ?>px; color: <?php echo $heading_font_color; ?>; margin: 0 0 20px 0;"><?php echo esc_html($main_heading); ?></h1>
		
		<?php 
		$blocks = sjs_get_pdf_config($config, 'content_blocks', array());
		foreach ($blocks as $key => $block): 
			$text = $block['text'];
			$text = str_replace(['[student_name]', '[course_name]', '[completion_date]'], [$student->full_name, $course->course_name, $formatted_date], $text);
			
			$inline_style = "margin: 5px 0;";
			if ($key === 'student_name') {
				$inline_style = "font-family: {$name_font_family}, cursive; font-size: {$name_font_size}px; color: {$name_font_color}; text-decoration: {$name_font_decoration}; margin: 15px 0;";
				if ($name_font_style === 'italic') $inline_style .= " font-style: italic;";
				if ($name_font_style === 'bold') $inline_style .= " font-weight: bold;";
				if ($name_font_style === 'bold-italic') $inline_style .= " font-weight: bold; font-style: italic;";
			} elseif ($key === 'course_name') {
				$inline_style = "font-weight: bold; font-size: " . ($body_font_size * 1.2) . "px; margin: 10px 0;";
			}
			echo "<div style=\"{$inline_style}\">" . esc_html($text) . "</div>";
		endforeach;
		?>
		
		<div style="margin-top: 60px;">
			<table style="width: 100%; border-collapse: collapse;">
				<tr>
					<td style="width: 33%; text-align: center; vertical-align: bottom;">
						<?php if ($qr_enabled && $qr_position === 'bottom_left'): ?>
						<img src="<?php echo esc_attr($qr_url); ?>" style="width: <?php echo $qr_size; ?>px; height: <?php echo $qr_size; ?>px; margin-bottom: 10px;">
						<?php endif; ?>
						<div style="font-weight: bold; margin-bottom: 5px;"><?php echo $formatted_date; ?></div>
						<div style="border-bottom: 1px solid #000; width: 120px; margin: 5px auto;"></div>
						<div style="font-size: 11px;"><?php echo esc_html($date_label); ?></div>
					</td>
					<td style="width: 33%; text-align: <?php echo $footer_logo_alignment; ?>; vertical-align: bottom;">
						<?php if ($footer_logo_url): ?>
						<img src="<?php echo esc_attr($footer_logo_url); ?>" style="max-height: <?php echo $footer_logo_height; ?>px; width: auto; margin-bottom: 10px;">
						<?php endif; ?>
						<?php if ($organization_name): ?>
						<div style="font-weight: bold; font-size: 13px;"><?php echo esc_html(str_replace('[organization_name]', 'Our Academy', $organization_name)); ?></div>
						<?php endif; ?>
					</td>
					<td style="width: 33%; text-align: center; vertical-align: bottom;">
						<?php if ($qr_enabled && $qr_position === 'bottom_right'): ?>
						<img src="<?php echo esc_attr($qr_url); ?>" style="width: <?php echo $qr_size; ?>px; height: <?php echo $qr_size; ?>px; margin-bottom: 10px;">
						<?php endif; ?>
						<table style="width: auto; margin: 0 auto; border-collapse: collapse;">
							<tr>
								<?php foreach ($signatures as $sig): ?>
								<td style="padding: 0 10px; text-align: center;">
									<?php if (!empty($sig['image_url'])): ?>
									<img src="<?php echo esc_attr($sig['image_url']); ?>" style="max-height: 45px; margin-bottom: 5px;">
									<?php endif; ?>
									<div style="border-bottom: 1px solid #000; width: 100px; margin: 5px auto;"></div>
									<div style="font-weight: bold; font-size: 11px;"><?php echo esc_html($sig['name'] ?? ''); ?></div>
									<div style="font-size: 9px;"><?php echo esc_html($sig['role'] ?? ''); ?></div>
								</td>
								<?php endforeach; ?>
							</tr>
						</table>
					</td>
				</tr>
			</table>
		</div>
	</div>

<?php elseif ($layout_mode === 'two_column'): ?>
	<!-- Two Column Layout -->
	<table style="width: 100%; border-collapse: collapse; height: <?php echo $content_area_height_px; ?>px;">
		<tr>
			<!-- Sidebar -->
			<td style="width: <?php echo $sidebar_width; ?>%; background: <?php echo $sidebar_bg; ?>; vertical-align: top; padding: 20px; border-right: 1px solid rgba(0,0,0,0.1);">
				<?php if ($logo_url): ?>
				<div style="text-align: center; margin-bottom: 30px;">
					<img src="<?php echo esc_attr($logo_url); ?>" style="max-height: <?php echo $logo_height; ?>px; width: auto; margin: 0 auto;">
				</div>
				<?php endif; ?>
				
				<?php if ($enable_student_photo && !empty($student_photo_url)): ?>
				<div style="text-align: center; margin-bottom: 30px;">
					<img src="<?php echo esc_attr($student_photo_url); ?>" style="width: <?php echo $photo_size; ?>px; height: <?php echo $photo_size; ?>px; <?php echo $photo_shape === 'circle' ? 'border-radius: 50%;' : ''; ?>">
				</div>
				<?php endif; ?>
			</td>
			
			<!-- Main Content -->
			<td style="vertical-align: middle; text-align: center; padding: 40px;">
				<h1 style="font-family: <?php echo $heading_font_family; ?>, serif; font-size: <?php echo $heading_font_size * 0.9; ?>px; color: <?php echo $heading_font_color; ?>; margin-bottom: 30px;"><?php echo esc_html($main_heading); ?></h1>
				
				<?php 
				$blocks = sjs_get_pdf_config($config, 'content_blocks', array());
				foreach ($blocks as $key => $block): 
					$text = $block['text'];
					$text = str_replace(['[student_name]', '[course_name]', '[completion_date]'], [$student->full_name, $course->course_name, $formatted_date], $text);
					
					$inline_style = "margin: 5px 0;";
					if ($key === 'student_name') {
						$inline_style = "font-family: {$name_font_family}, cursive; font-size: {$name_font_size}px; color: {$name_font_color}; text-decoration: {$name_font_decoration}; margin: 15px 0;";
						if ($name_font_style === 'italic') $inline_style .= " font-style: italic;";
						if ($name_font_style === 'bold') $inline_style .= " font-weight: bold;";
						if ($name_font_style === 'bold-italic') $inline_style .= " font-weight: bold; font-style: italic;";
					} elseif ($key === 'course_name') {
						$inline_style = "font-weight: bold; font-size: " . ($body_font_size * 1.2) . "px; margin: 10px 0;";
					}
					echo "<div style=\"{$inline_style}\">" . esc_html($text) . "</div>";
				endforeach;
				?>
				
				<div style="margin-top: 50px;">
					<table style="width: 100%; border-collapse: collapse;">
						<tr>
							<td style="width: 50%; text-align: left; vertical-align: bottom;">
								<?php if ($qr_enabled && $qr_position === 'bottom_left'): ?>
								<img src="<?php echo esc_attr($qr_url); ?>" style="width: <?php echo $qr_size; ?>px; height: <?php echo $qr_size; ?>px; margin-bottom: 10px;">
								<?php endif; ?>
								<div style="font-weight: bold; margin-bottom: 5px;"><?php echo $formatted_date; ?></div>
								<div style="border-bottom: 1px solid #000; width: 120px; margin-bottom: 5px;"></div>
								<div style="font-size: 11px;"><?php echo esc_html($date_label); ?></div>
							</td>
							<td style="width: 50%; vertical-align: bottom; text-align: right;">
								<?php if ($qr_enabled && $qr_position === 'bottom_right'): ?>
								<img src="<?php echo esc_attr($qr_url); ?>" style="width: <?php echo $qr_size; ?>px; height: <?php echo $qr_size; ?>px; margin-bottom: 10px;">
								<?php endif; ?>
								<table style="width: auto; margin: 0 auto; border-collapse: collapse;">
									<tr>
										<?php foreach ($signatures as $sig): ?>
										<td style="padding: 0 10px; text-align: center;">
											<?php if (!empty($sig['image_url'])): ?>
											<img src="<?php echo esc_attr($sig['image_url']); ?>" style="max-height: 45px; margin-bottom: 5px;">
											<?php endif; ?>
											<div style="border-bottom: 1px solid #000; width: 100px; margin: 5px auto;"></div>
											<div style="font-weight: bold; font-size: 11px;"><?php echo esc_html($sig['name'] ?? ''); ?></div>
											<div style="font-size: 9px;"><?php echo esc_html($sig['role'] ?? ''); ?></div>
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
	</table>

<?php endif; ?>

</div>

</body>
</html>
