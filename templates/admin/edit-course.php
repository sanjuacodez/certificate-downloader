<?php if ( ! defined( 'ABSPATH' ) ) exit; ?>

<div class="wrap">
	<h1>Edit Course: <?php echo esc_html( $course->course_name ); ?></h1>
	
	<?php settings_errors( 'sjs_cert_messages' ); ?>

	<?php
	$config = $course->template_config;
	if ( ! is_array( $config ) ) {
		$config = array();
	}

	// Helper to safely get nested values (Internal use only)
	if ( ! function_exists( 'sjs_get_config' ) ) {
		function sjs_get_config($config, $path, $default = '') {
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

	// Only DomPDF core fonts - guaranteed to work in PDF generation
	$fonts = array( 
		'Helvetica',
		'Times-Roman',
		'Courier'
	);

	// Pass config to JavaScript
	$plugin_base = dirname( dirname( dirname( __FILE__ ) ) ) . '/certificate-downloader.php';
	$js_config = array(
		'config'               => $config,
		'background_image_url' => sjs_get_config($config, 'background.background_image_url'),
		'seal_image_url'       => sjs_get_config($config, 'seal_badge.image_url'),
		'placeholder_url'      => plugins_url( 'assets/images/placeholder.svg', $plugin_base ),
		'bg_ornamental'        => plugins_url( 'assets/images/bg-ornamental.svg', $plugin_base ),
		'bg_modern_geo'        => plugins_url( 'assets/images/bg-modern-geo.svg', $plugin_base ),
		'bg_waves'             => plugins_url( 'assets/images/bg-waves.svg', $plugin_base ),
		'seal_sample'          => plugins_url( 'assets/images/seal-sample.jpg', $plugin_base ),
		'course_name'          => esc_js( $course->course_name ),
		'organization_name'    => esc_js( get_option( 'sjs_cert_organization_name', 'Your Academy' ) )
	);
	?>

	<script type="text/javascript">
		var sjsCertEditorConfig = <?php echo json_encode( $js_config ); ?>;
	</script>

	<!-- Templates Reel -->
	<?php include plugin_dir_path( __FILE__ ) . 'partials/template-reel.php'; ?>

	<div style="display: flex; gap: 20px; align-items: flex-start; position: relative;">
		<!-- Settings Column -->
		<div class="card" style="flex: 0 0 350px; max-width: 710px; padding: 0; overflow: hidden;">
			<?php include plugin_dir_path( __FILE__ ) . 'partials/settings-form.php'; ?>
		</div>

		<!-- Live Preview Column -->
		<div class="card" style="flex: 1; background: #f0f0f1; display: flex; align-items: flex-start; justify-content: center; overflow: auto; padding: 20px; position: sticky; top: 40px; height: calc(100vh - 80px); border: none; max-width: 670px;">
			<?php include plugin_dir_path( __FILE__ ) . 'partials/preview-frame.php'; ?>
		</div>
	</div>
</div>
