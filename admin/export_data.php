<?php
/**
 * @desc Utility per l'esportazione dei dati delle tabelle in vari formati
 * @package VFront
 * @subpackage Administration
 * @author M.Marcello Verona
 * @copyright 2007-2010 M.Marcello Verona
 * @version 0.96 $Id: export_data.php 1078 2014-06-13 15:35:53Z marciuz $
 * @license http://www.gnu.org/licenses/gpl.html GNU Public License 
 */

include("../inc/conn.php");
include("../inc/layouts.php");

 proteggi(1);


if((isset($_GET['id_table']) && is_numeric($_GET['id_table'])) || isset($_GET['qr_send'])){
	
	$id_tabella = (isset($_GET['qr_send'])) ? RegTools::name2oid($_GET['qr_send'],$_SESSION['gid']) : (int) $_GET['id_table'];
	
	if($id_tabella==0 || !RegTools::is_tabella_by_oid($id_tabella)){
		
		
		header("Location: ".$_SERVER['PHP_SELF']);
		exit;
	}
	
	// classe esportazione
	$EXP=new Export($id_tabella);
	
	$EXP->raw = (bool) $_GET['raw'];
	
	if(isset($_GET['qr_send'])){
		
		$EXP->ids_search=str_replace("|",",",$_SESSION['qresults']['ids']);
	}
	
	
	
	
	switch($_GET['formato']){
		
		case 'csv':  $EXP->tabella_csv($sep=',');
		break;
		
		case 'html': $EXP->tabella_html();
		break;
		
		case 'xls':  $EXP->tabella_xls();
		break;
		
		case 'ods':  $EXP->tabella_ods();
		break;
		
		default: header("Location: ".$_SERVER['PHP_SELF']);	exit;
		
	}
	
	exit;
}


 
 echo openLayout1(_("Data Export"), array("sty/admin.css"),'popup');
 
 if(!isset($_GET['idt'])){
 	
	echo breadcrumbs(array("HOME","ADMIN",_("data export")));

 }
 
 
 if(isset($_GET['qr'])){
 	
 	echo "<h1>".sprintf(_("Export search for table %s"),"<span class=\"var\">".ucfirst($_GET['t'])."</span>")."</h1>\n";
 }
 else{
 	echo "<h1>"._("Data Export")."</h1>\n";
 }
 
 
 echo "<img src=\"../img/db_export.gif\" class=\"img-float\" alt=\""._("registry settings")."\" />\n";

 
 
 echo "<form action=\"".$_SERVER['PHP_SELF']."\" method=\"get\" style=\"margin:30px 0px 0px 90px\">\n";
 
 
 if(isset($_GET['idt'])){
 	$idt = intval(str_replace(_BASE64_PASSFRASE,"",base64_decode($_GET['idt'])));
 	
 	echo  "<input type=\"hidden\" name=\"id_table\" id=\"id_table\" value=\"$idt\" />\n";
 }
 else if(isset($_GET['qr'])){
 	
 	echo  "<input type=\"hidden\" name=\"qr_send\" id=\"qr_send\" value=\"{$_GET['t']}\" />\n";
 }
 else{
 
 
	 echo "\t\t<label for=\"id_table\"><strong>"._("Table to export:")." </strong></label>\n";
	 
	 $TABELLE=RegTools::prendi_tabelle();
	 
	 echo "\t\t<select id=\"id_table\" name=\"id_table\">\n";
	 
	 for($i=0;$i<count($TABELLE);$i++){
	 	
	 	echo "\t\t\t<option value=\"{$TABELLE[$i]['id_table']}\">".$TABELLE[$i]['table_name']."</option>\n";
	 }
	 
	 echo "\t\t</select><br /><br />\n";
 
 }
 echo "\t\t<p><strong>"._("Type:")."</strong></p>\n";
 
 echo "\t\t<blockquote>\n";
 echo "\t\t\t<input type=\"radio\" name=\"formato\" id=\"formato_csv\" value=\"csv\" checked=\"checked\"/><label for=\"formato_csv\">CSV (Comma Separate Value)</label>\n<br />";
 echo "\t\t\t<input type=\"radio\" name=\"formato\" id=\"formato_html\" value=\"html\" /><label for=\"formato_html\">HTML</label>\n<br />";
 echo "\t\t\t<input type=\"radio\" name=\"formato\" id=\"formato_ods\" value=\"ods\" /><label for=\"formato_ods\">ODS (Open Document Spreadsheet)</label>\n<br />";
 echo "\t\t\t<input type=\"radio\" name=\"formato\" id=\"formato_xls\" value=\"xls\" /><label for=\"formato_xls\">XLS (Excel)</label>\n<br />";
 
 echo "\t\t</blockquote><br />\n";
 
 
 
  echo "\t\t<p><strong>"._("Data export mode:")."</strong></p>\n";
 
 echo "\t\t<blockquote>\n";
 echo "\t\t\t<input type=\"radio\" name=\"raw\" id=\"raw_1\" value=\"1\" checked=\"checked\"/><label for=\"raw_1\">"._("Data as raw table")."</label>\n<br />";
 echo "\t\t\t<input type=\"radio\" name=\"raw\" id=\"raw_0\" value=\"0\" /><label for=\"raw_0\">"._("Data with group registry settings")."</label>\n<br />";
 
 echo "\t\t</blockquote>\n";
 
 echo "<br /><br />\n";
 
 echo "<input type=\"button\" onclick=\"submit();\" name=\"esporta\" value=\" "._("Export data")." \" />\n";
 
 
 echo "</form>\n";
 
 
 
 
 echo closeLayout1();
 
?>