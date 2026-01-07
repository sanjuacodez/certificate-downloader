<?php

namespace SJS_Cert\Core;

/**
 * Fired during plugin deactivation.
 */
class Deactivator {

	/**
	 * Short Description. (use period)
	 *
	 * Long Description.
	 *
	 * @since    1.0.0
	 */
	public static function deactivate() {
		// Flush rewrite rules if necessary
		flush_rewrite_rules();
	}
}
