<?php
/**
 * File che getisce la creazione dinamica e lo stream dell'XML di VFront
 * Viene utilizzato anche per generare report HTML e PDF, se associato a fogli di stile
 * Il file non � richiamato direttamente ma mediante riscrittura degli URL
 * da parte del file .htaccess presente nella stessa cartella
 * 
 * @desc Output XML
 * @package VFront
 * @subpackage VFront_XML
 * @author M.Marcello Verona
 * @copyright 2007-2010 M.Marcello Verona
 * @version 0.96 $Id: xml.php 1078 2014-06-13 15:35:53Z marciuz $
 * @license http://www.gnu.org/licenses/gpl.html GNU Public License 
 */


require_once("../inc/conn.php");
require_once("../inc/layouts.php");
require_once("../inc/func.xmlize.php");
require_once("../inc/func.getxml.php");

	
	
	
	
	
	########################################################################################
	#
	#
	#	GENERAZIONE DELLA PAGINA DI RIEPILOGO
	#
	#
	
	
	if(isset($_GET['riepilogo'])){
		
		$QB = (isset($_GET['querybased']) && $_GET['querybased']=='@') ? 1:0;
		
		if($QB){
		
			$nome_report = preg_replace("'[^a-z0-9_-]'i","",$_GET['action']);
		}
		else{
			$tabella = preg_replace("'[^a-z0-9_]'i","",$_GET['action']);
		}
		
		if($QB){
			
			$test_query_custom = $vmsql->query_try(stripslashes($RS_rules['def_query']));
			
			if($test_query_custom){
			
				$q_conta=$vmsql->query(stripslashes($RS_rules['def_query']));
				$titolo_pag = _("Report");
				$n_record=$vmsql->num_rows($q_conta);
			}
		}
		else{
			
			$q_conta=$vmsql->query("SELECT count(*) FROM $tabella ");
			$titolo_pag= _("Table");
			list($n_record)=$vmsql->fetch_row($q_conta);
		}
		
		
		
		
		
		
		$tab= preg_replace("'[^a-z0-9_]'i","",$_GET['action']);
		
		
		
		echo openLayout1($titolo_pag." ".$tab);
		
		echo breadcrumbs(array("HOME","ADMIN",
						FRONT_DOCROOT."/admin/xmlreport.php"=>_("XML administration and reports"),
						strtolower(_("New XML report"))	));

		echo "<h1>$titolo_pag ".$tab."</h1>\n";
		
		echo "<p><b>"._("All records:")."</b></p>\n";
		
		// se � query based metti una @ davanti al nome
		if($QB) $tab="@".$tab;
		
		echo "<a href=\"".FRONT_DOCROOT."/xml/$tab/all/XML\">XML ($n_record records)</a>\n";
		echo " | <a href=\"".FRONT_DOCROOT."/xml/$tab/all/\">HTML ($n_record records)</a>\n";
		
		
		$q_fo=$vmreg->query("SELECT xslfo FROM {$db1['frontend']}{$db1['sep']}xml_rules WHERE tabella='$tab' ORDER BY lastData DESC");
		
		list($xslfo)=$vmreg->fetch_row($q_fo);
		
		if($xslfo!='-1' && _FOP_ENABLED){
			
			echo " | <a href=\"".FRONT_DOCROOT."/xml/$tab/all/PDF\">PDF ($n_record "._("records").")</a>\n";
		}
		else{
			
			// non fa nulla
		}
		
		
		
		echo "<p><b>"._("views with paging:")."</b></p>\n";
		
		$n_pag=50;
		
		
		echo "<p>"._('XML Version ').": ";
		$XML_PAG='';
		for($i=0;$i<ceil($n_record/$n_pag);$i++){
			
			$of=$i*$n_pag;
			$XML_PAG.= " <a href=\"".FRONT_DOCROOT."/xml/$tab/$of,$n_pag/XML\">". ($i+1) ."</a> |";
			
		}
		echo substr($XML_PAG,0,-2)."</p>\n";
		
		echo "<p>"._('HTML Version ').": ";
		$XML_PAG='';
		for($i=0;$i<ceil($n_record/$n_pag);$i++){
			$of=$i*$n_pag;
			$XML_PAG.= " <a href=\"".FRONT_DOCROOT."/xml/$tab/$of,$n_pag/\">". ($i+1) ."</a> |";
		}
		echo substr($XML_PAG,0,-2)."</p>\n";
		
		
		if($xslfo!='-1' && _FOP_ENABLED){
			echo "<p>"._('PDF Version ').": ";
			$XML_PAG='';
			for($i=0;$i<ceil($n_record/$n_pag);$i++){
				$of=$i*$n_pag;
				$XML_PAG.= " <a href=\"".FRONT_DOCROOT."/xml/$tab/$of,$n_pag/PDF\">". ($i+1) ."</a> |";
			}
			echo substr($XML_PAG,0,-2)."</p>\n";
		}
		
		echo closeLayout1();
		
		exit;
		
	}
	else if($_GET['type']=='XML'){
		header("Content-Type: text/xml; charset=".FRONT_ENCODING);
		
		$XML_array=get_vfront_xml($_GET);
		
		echo $XML_array['XML'];
	}
	else{
		
			
		if($_SESSION['VF_VARS']['server_xslt']=='1'){
			
			require_once("./xslparser.php");
			
			$file_xml=_PATH_TMP."/".md5(serialize($_SESSION)).".xml";
			$file_xsl=_PATH_TMP."/".md5(serialize($_SESSION)).".xsl";
			
			$XML_array=get_vfront_xml($_GET);
			
			$fp=fopen($file_xml,"w");
			fwrite($fp,$XML_array['XML']);
			fclose($fp);
			
						
			copy($XML_array['XSL'],$file_xsl);
			
			print xslparser($file_xml,$file_xsl);
			
			unlink($file_xml);
			unlink($file_xsl);
		}
		else {
			header("Content-Type: text/xml; charset=".FRONT_ENCODING);
			$XML_array=get_vfront_xml($_GET);
			echo $XML_array['XML'];
		}
	}
	
		


?>