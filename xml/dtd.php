<?php
/**
 * Questo file permette di generare le dtd
 * 
 * @desc Generazione dinamica di DTD
 * @package VFront
 * @subpackage VFront_XML
 * @author M.Marcello Verona
 * @copyright 2007-2010 M.Marcello Verona
 * @version 0.96 $Id: dtd.php 1078 2014-06-13 15:35:53Z marciuz $
 * @license http://www.gnu.org/licenses/gpl.html GNU Public License 
 */


require("../inc/conn.php");

function genera_dtd($tabella,$solo_visibili=false){

	list($col,$is_null)= RegTools::prendi_colonne_frontend($tabella,'column_name,is_nullable',$solo_visibili,intval($_SESSION['gid']));
	
	
	
$DTD='<?xml version="1.0" encoding="UTF-8"?>
<!ELEMENT recordset (row+)>
<!ELEMENT row ('.implode(",",$col).') >'."\n";


for($i=0;$i<count($col);$i++){
	
	$DTD.="<!ELEMENT ".$col[$i]." (#PCDATA) >\n";
	
}

$DTD.="
<!ATTLIST recordset tot CDATA #REQUIRED >
<!ATTLIST row offset CDATA #REQUIRED >	
";
	
	
return $DTD;
	
}

$solo_visibili= (int) $_GET['vis'];

$dtd= genera_dtd($_GET['action'],$solo_visibili);

header("Content-type: application/octet-stream");
echo $dtd;

?>