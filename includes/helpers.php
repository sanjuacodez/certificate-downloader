<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Helper functions for the plugin.
 */

if ( ! function_exists( 'sjs_cert_get_template' ) ) {
	/**
	 * Locate and load a template file.
	 *
	 * @param string $template_name Template name.
	 * @param array  $args          Arguments to pass to the template.
	 */
	function sjs_cert_get_template( $template_name, $args = array() ) {
		if ( ! empty( $args ) && is_array( $args ) ) {
			extract( $args );
		}

		$template_path = plugin_dir_path( __DIR__ ) . 'templates/' . $template_name . '.php';

		if ( file_exists( $template_path ) ) {
			include $template_path;
		} else {
			echo 'Template not found: ' . esc_html( $template_name );
		}
	}
}
