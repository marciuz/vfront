<?php
/**
 * Questo file gestisce i diritti e la sicurezza per l'esecuzione di Apache FOP.
 * 
 * @desc Esegue Apache FOP
 * @package VFront
 * @subpackage VFront_XML
 * @author M.Marcello Verona
 * @copyright 2007-2010 M.Marcello Verona
 * @version 0.96 $Id: fop_exec.php 1078 2014-06-13 15:35:53Z marciuz $
 * @license http://www.gnu.org/licenses/gpl.html GNU Public License 
 * @todo Mettere le estensioni possibili di FOP nelle variabili di ambiente, Verificare l'apertura dei report query_based
 */

require_once("../inc/conn.php");
require_once("../inc/func.xmlize.php");
require_once("../inc/func.getxml.php");





	
$tabella = preg_replace("'[^a-z0-9_]'i","",$_GET['action']);

// SICUREZZA ---------------------------------------------




	//Cerca le regole in DB// cerca i diritti
	$q=$vmreg->query("SELECT * FROM {$db1['frontend']}{$db1['sep']}xml_rules WHERE tabella='$tabella' ORDER BY lastData DESC LIMIT 1");
	
	if($vmreg->num_rows($q)==0){
		echo "<h1>"._("Access forbidden")."</h1>\n"; exit;
	}
	else $RS_rules=$vmreg->fetch_assoc($q);
	
	
	if($RS_rules['xslfo']=='-1'){
		
		echo "<h1>"._("Unexpected file")."</h1>\n"; exit;
	}
	
	
	if($RS_rules['accesso']=='PUBLIC'){
		
		// non fa niente e continua con lo script
	}
	elseif($RS_rules['accesso']=='FRONTEND'){
		proteggi(1);
	}
	elseif($RS_rules['accesso']=='GROUP'){
		
		$gruppi=explode(",",$RS_rules['accesso_gruppo']);
		
		if(is_array($gruppi) && in_array($_SESSION['gid'],$gruppi)){
			
			// va avanti
		}
		else{
			echo "<h1>"._("Access forbidden")."</h1>\n"; exit;
		}
	}
	else{ // RESTRICT o altro...
		
		echo "<h1>"._("Access forbidden")."</h1>\n"; exit;
	}
	
	
	
	
	
	
// ---------------------------------------------------------
	
#################
#
#	TODO: gestire l'accesso con i diritti...
#	
#
	
if(isset($_GET['c'])){
	
	$xml_content= get_vfront_xml(array('action'=>$tabella,'c'=>$_GET['c'], 'type'=>'XML')); //FRONT_DOCROOT."/xml/$tabella/".$_GET['c']."/XML";

}
else if(isset($_GET['id'])){
	
//	$file_xml_web= FRONT_DOCROOT."/xml/$tabella/id/".intval($_GET['id'])."/XML";
	$xml_content= get_vfront_xml(array('action'=>$tabella,'id'=>intval($_GET['id']), 'type'=>'XML'));

}
else{
	
//	$file_xml_web= FRONT_DOCROOT."/xml/$tabella/all/XML";
	$xml_content= get_vfront_xml(array('action'=>$tabella, 'c'=>'all', 'type'=>'XML'));
}

$file_xml=_PATH_TMP."/".$tabella.".xml";

/* TODO
	non gestisci i casi non accessibili via web (FRONTEND / GROUP)
*/

$fp=fopen($file_xml,'w');
fwrite($fp,$xml_content['XML']);
fclose($fp);

//copy($file_xml_web,$file_xml);


// FILE XSL 

if($RS_rules['xslfo']==''){

	include("./fo.php");
	
	$file_xsl= _PATH_TMP."/$tabella.xslt";
	
}
else{
	
	$file_xsl=_PATH_XSL."/".$RS_rules['xslfo'];
}

// TIPI SUPPORTATI
$types=array('pdf','rtf','ps','txt','tiff','png','pcl','apf','svg');

// tipo di documento
$tipo_richiesto=trim(strtolower($_GET['type']));

if(in_array($tipo_richiesto,$types)){
	
	$TYPE_DOC=$tipo_richiesto;
}
else{
	
	$TYPE_DOC='pdf';
}


$file_output= _PATH_TMP."/".date("Ymd")."_".$tabella.".".strtolower($TYPE_DOC);

fop_exec('',$file_xml,$file_xsl,$file_output,$TYPE_DOC ,false);


?>