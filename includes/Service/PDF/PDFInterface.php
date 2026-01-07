<?php

namespace SJS_Cert\Service\PDF;

interface PDFInterface {
	/**
	 * Generate PDF from HTML.
	 *
	 * @param string $html     HTML content.
	 * @param string $filename Filename for download/save.
	 * @param array  $options  Additional options (e.g., orientation, paper size).
	 * @return mixed           PDF output or file path.
	 */
	public function generate( $html, $filename, $options = array() );
}
