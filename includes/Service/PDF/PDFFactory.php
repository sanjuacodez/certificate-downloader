<?php

namespace SJS_Cert\Service\PDF;

use SJS_Cert\Service\PDF\Drivers\DomPDFDriver;

class PDFFactory {

	/**
	 * Get PDF Driver instance.
	 *
	 * @param string $driver Driver name (default: dompdf).
	 * @return PDFInterface
	 * @throws \Exception If driver is not supported.
	 */
	public static function get_driver( $driver = 'dompdf' ) {
		switch ( $driver ) {
			case 'dompdf':
				return new DomPDFDriver();
			// Future drivers (fpdf, tcpdf) can be added here
			default:
				throw new \Exception( "Unsupported PDF driver: $driver" );
		}
	}
}
