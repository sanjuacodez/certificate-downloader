<?php if ( ! defined( 'ABSPATH' ) ) exit; ?>

<div id="preview-wrapper" style="position: relative;">
	<div id="sjs-cert-preview-frame" style="background: #fff; box-shadow: 0 0 20px rgba(0,0,0,0.5); position: relative; box-sizing: border-box; transform-origin: top left; overflow: hidden;">
		<!-- Background Layer -->
		<div class="layer bg-layer">
			<img id="preview-bg-img" src="" class="bg-img" style="display: none;">
		</div>

		<!-- Border Layer -->
		<div class="layer border-layer">
			<div id="preview-border-box" class="border-box" style="padding: 10px; width: 100%; height: 100%; box-sizing: border-box;">
				<div id="preview-inner-border-box" class="inner-border-box" style="width: 100%; height: 100%; box-sizing: border-box;"></div>
			</div>
		</div>

		<!-- Content Layer -->
		<div id="preview-content-layer" class="layer content-layer">
			<!-- Content injected via JS -->
		</div>
	</div>
</div>

<!-- Hidden images to store state for JS injection -->
<img id="preview-logo-hidden" src="<?php echo esc_url( sjs_get_config($config, 'header.logo_url') ); ?>" style="display:none;">
<img id="preview-footer-logo-hidden" src="<?php echo esc_url( sjs_get_config($config, 'footer.footer_logo_url') ); ?>" style="display:none;">
