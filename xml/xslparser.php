<?php
/**
 * Questo parser viene utilizzato qualora si imposti la variabile 
 * di trasformazione XSLT lato server come true nelle variabili globali.
 * 
 * @desc Parser XSLT lato server
 * @package VFront
 * @subpackage VFront_XML
 * @author M.Marcello Verona
 * @copyright 2007-2010 M.Marcello Verona
 * @version 0.96 $Id: xslparser.php 819 2010-11-21 17:07:24Z marciuz $
 * @license http://www.gnu.org/licenses/gpl.html GNU Public License 
 */


if (PHP_VERSION < 5) {
	
		
	/**
	 * Funzione di parser XSLT. 
	 * Richiede l'apertura di file remoti (allor_fopen_url = TRUE )
	 * 
	 *
	 * @param string $file_xml URL del file XML
	 * @param string $file_xsl URL del file XSL
	 * @return string HTML output
	 */
	function xslparser($file_xml,$file_xsl){
	
		$arguments = array(
		    '/_xml' => file_get_contents($file_xml),
		    '/_xsl' => file_get_contents($file_xsl)
		);
		
		$xsltproc = xslt_create();
		$html = xslt_process(
		    $xsltproc,
		    'arg:/_xml',
		    'arg:/_xsl',
		    null,
		    $arguments
		);
	
		xslt_free($xsltproc);
		
		return $html;
	
	}

}

// PHP 5
else{
	
	function xslparser($file_xml,$file_xsl){
		
	   $xslDoc = new DOMDocument();
	   $xslDoc->load($file_xsl);
	
	   $xmlDoc = new DOMDocument();
	   $xmlDoc->load($file_xml);
	
	   $proc = new XSLTProcessor();
	   $proc->importStylesheet($xslDoc);
	   
	   return $proc->transformToXML($xmlDoc);
	
	}
}

?> 