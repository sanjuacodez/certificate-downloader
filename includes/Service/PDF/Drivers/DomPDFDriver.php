<?php

namespace SJS_Cert\Service\PDF\Drivers;

use SJS_Cert\Service\PDF\PDFInterface;
use Dompdf\Dompdf;
use Dompdf\Options;

class DomPDFDriver implements PDFInterface {

	public function generate( $html, $filename, $options = array() ) {
		$dompdf_options = new Options();
		$dompdf_options->set( 'isRemoteEnabled', true );
		$dompdf_options->set( 'isHtml5ParserEnabled', true );

		$dompdf = new Dompdf( $dompdf_options );
		
		$paper_size        = isset( $options['paper_size'] ) ? $options['paper_size'] : 'A4';
		$paper_orientation = isset( $options['orientation'] ) ? $options['orientation'] : 'portrait';

		$dompdf->setPaper( $paper_size, $paper_orientation );
		$dompdf->loadHtml( $html );
		$dompdf->render();

		if ( isset( $options['stream'] ) && $options['stream'] ) {
			$dompdf->stream( $filename, array( 'Attachment' => true ) );
		} else {
			return $dompdf->output();
		}
	}
}
