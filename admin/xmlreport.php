<?php
/**
 * Sono qui definite le procedure per gestire i report di VFront.
 * 
 * @desc Modulo di gestione dei report
 * @package VFront
 * @subpackage Administration
 * @author M.Marcello Verona
 * @copyright 2007-2010 M.Marcello Verona
 * @version 0.96 $Id: xmlreport.php 1078 2014-06-13 15:35:53Z marciuz $
 * @license http://www.gnu.org/licenses/gpl.html GNU Public License 
 */

include("../inc/conn.php");
include("../inc/layouts.php");

 proteggi(2);
 
 
 ######################################
 #
 #	INSERIMENTO IN DB
 #
 ######################################
 
 if(isset($_GET['creanew'])){
 	
 	
 	$_dati=$vmreg->recursive_escape($_POST);
 	
 	
 	
 	
 	// gestione file -------------------------------------------------------------------------------
 	if($_FILES['new_xsl']['size']>0){
 		
 		// è stato richiesto un nuovo file XSL da uplodare
 		$NEW_XSL_NAME=preg_replace("'[^a-z0-9_.]'i","_",$_FILES['new_xsl']['name']);
 		
 		while(is_file(_PATH_XSL."/".$NEW_XSL_NAME)){
 			
 			$NEW_XSL_NAME="_".$NEW_XSL_NAME;
 		}
 		
 		$test_xsl=move_uploaded_file($_FILES['new_xsl']['tmp_name'],_PATH_XSL."/".$NEW_XSL_NAME);
 	}
 	else{
 		
 		if($_dati['xsl']!='default') $NEW_XSL_NAME=$_dati['xsl'];
 		else $NEW_XSL_NAME='';
 		
 		
 	}// gestione file -------------------------------------------------------------------------------
 	
 	
 	// gestione file -------------------------------------------------------------------------------
 	if($_FILES['new_fo']['size']>0){
 		
 		// è stato richiesto un nuovo file XSL da uplodare
 		$NEW_XSLFO_NAME=preg_replace("'[^a-z0-9_.]'i","_",$_FILES['new_fo']['name']);
 		
 		while(is_file(_PATH_XSL."/".$NEW_XSLFO_NAME)){
 			
 			$NEW_XSLFO_NAME="_".$NEW_XSLFO_NAME;
 		}
 		
 		
 		$test_xslfo=move_uploaded_file($_FILES['new_fo']['tmp_name'],_PATH_XSL."/".$NEW_XSLFO_NAME);
 	}
 	else{
 		
 		if($_dati['fo']=='') $NEW_XSLFO_NAME='-1';
 		else if($_dati['fo']!='default') $NEW_XSLFO_NAME=$_dati['xsl'];
 		else $NEW_XSLFO_NAME='';
 		
 		
 	}// gestione file -------------------------------------------------------------------------------
 	
 	
 	
 	$nome_report = preg_replace("|[^\w-_]+|","_",$_dati['nome_report']);
 	
 	$nome_report=strtolower($nome_report);
 	
 	if(!is_array($_dati['gid'])) $_dati['gid']=array();
 	
 	$sql=sprintf("INSERT INTO {$db1['frontend']}{$db1['sep']}xml_rules (nome_report,tabella,accesso, accesso_gruppo, autore, lastData, xsl, xslfo, def_query, tipo_report)
 				  VALUES ('%s','%s','%s','%s',%d,'%s','%s','%s','%s','%s')",
 				$nome_report,
 				$_dati['tabella'],
 				$_dati['accesso'],
 				implode(",",$_dati['gid']),
 				$_SESSION['user']['uid'],
 				date("Y-m-d H:i:s"),
 				$NEW_XSL_NAME,
 				$NEW_XSLFO_NAME,
 				$_dati['def_query'],
 				$_dati['tipo_report']
 				);

 	$q=$vmreg->query($sql);
 	
 	if($vmreg->affected_rows($q)==1){
 		header("Location: ".$_SERVER['PHP_SELF']."?feed=ok");
 	}
 	else{
 		header("Location: ".$_SERVER['PHP_SELF']."?feed=ko");
 	}
 	
 	exit;
 	
 }
 
 
  ######################################
 #
 #	MODIFICA IN DB
 #
 ######################################
 
 if(isset($_GET['appmod'])){
 	
 	$_dati=$vmreg->recursive_escape($_POST);
 	
 	
 	// gestione file -------------------------------------------------------------------------------
 	if($_FILES['new_xsl']['size']>0){
 		
 		// è stato richiesto un nuovo file XSL da uplodare
 		$NEW_XSL_NAME=preg_replace("'[^a-z0-9_.]'i","_",$_FILES['new_xsl']['name']);
 		
 		while(is_file(_PATH_XSL."/".$NEW_XSL_NAME)){
 			
 			$NEW_XSL_NAME="_".$NEW_XSL_NAME;
 		}
 		
 		
 		$test_xsl=move_uploaded_file($_FILES['new_xsl']['tmp_name'],_PATH_XSL."/".$NEW_XSL_NAME);
 	}
 	else{
 		
 		if($_dati['xsl']!='default') $NEW_XSL_NAME=$_dati['xsl'];
 		else $NEW_XSL_NAME='';
 		
 		
 	}// gestione file -------------------------------------------------------------------------------
 	
 	
 	// gestione file -------------------------------------------------------------------------------
 	if($_FILES['new_fo']['size']>0){
 		
 		// E' stato richiesto un nuovo file XSL da uplodare
 		$NEW_XSLFO_NAME=preg_replace("'[^a-z0-9_.]'i","_",$_FILES['new_fo']['name']);
 		
 		while(is_file(_PATH_XSL."/".$NEW_XSLFO_NAME)){
 			
 			$NEW_XSLFO_NAME="_".$NEW_XSLFO_NAME;
 		}
 		
 		
 		$test_xslfo=move_uploaded_file($_FILES['new_fo']['tmp_name'],_PATH_XSL."/".$NEW_XSLFO_NAME);
 	}
 	else{
 		
 		if($_dati['fo']=='') $NEW_XSLFO_NAME='-1';
 		else if($_dati['fo']!='default') $NEW_XSLFO_NAME=$_dati['fo'];
 		else $NEW_XSLFO_NAME='';
 		
 		
 	}// gestione file -------------------------------------------------------------------------------
 	
 	
 	
 	if(!is_array($_dati['gid'])) $_dati['gid']=array();
 	
 	$nome_report = preg_replace("|[^\w-_]+|","_",strtolower($_dati['nome_report']));
 	
 	$sql=sprintf("UPDATE {$db1['frontend']}{$db1['sep']}xml_rules SET 
 				  nome_report='%s', accesso='%s', accesso_gruppo='%s', autore=%d, 
 				  lastData='%s', xsl='%s', xslfo='%s', def_query='%s'
 				  WHERE id_xml_rules=%d",
 				$nome_report,	
 				$_dati['accesso'],
 				implode(",",$_dati['gid']),
 				$_SESSION['user']['uid'],
 				date("Y-m-d H:i:s"),
 				$NEW_XSL_NAME,
 				$NEW_XSLFO_NAME,
 				$_dati['def_query'],
 				$_dati['id_xml_rules']
 				);

 	$q=$vmreg->query($sql);
 	
 	if($vmreg->affected_rows($q)==1){
 		header("Location: ".$_SERVER['PHP_SELF']."?feed=ok");
 	}
 	else{
 		header("Location: ".$_SERVER['PHP_SELF']."?feed=ko");
 	}
 	
 	exit;
 	
 }
 
 
 ######################################
 #
 #	CANCELLAZIONE DAL DB
 #
 ######################################
 else if(isset($_GET['del'])){
 	
 	// verifico che l'allegato sia presente in un solo record
 	$sql_xsl="SELECT count(id_xml_rules) FROM {$db1['frontend']}{$db1['sep']}xml_rules
 						WHERE xsl=(SELECT xsl FROM {$db1['frontend']}{$db1['sep']}xml_rules WHERE id_xml_rules=".intval($_GET['del']).")";
 	$q_xsl=$vmreg->query($sql_xsl);
 	
 	list($n_xsl)=$vmreg->fetch_row($q_xsl);
 	
 	
 	
 	$q=$vmreg->query("DELETE FROM {$db1['frontend']}{$db1['sep']}xml_rules WHERE id_xml_rules=".intval($_GET['del']));
 	
 	 if($vmreg->affected_rows($q)==1){
 		header("Location: ".$_SERVER['PHP_SELF']."?feed=delok");
 	}
 	else{
 		header("Location: ".$_SERVER['PHP_SELF']."?feed=delko");
 	}
 	
 	exit;
 }
 
 
 
 ######################################
 #
 #	CREA NUOVO
 #
 ######################################
else if(isset($_GET['new']) || isset($_GET['newquery'])){
 	
 	$files=array("js/test_query.js");
 	
 	$TIPO_NEW = (isset($_GET['new'])) ? "tabella" : "query";
 	 
 	echo openLayout1(_("XML Administration and Reports"),$files);
 	
 	$title_ = _("New XML report");

	echo breadcrumbs(array("HOME","ADMIN","xmlreport.php"=>_("XML administration and reports"), $title_));

	$testo_tit = ($TIPO_NEW=="tabella") ? _("table based") : _("query based");
	
	echo "<h1>". $title_ ." $testo_tit</h1>\n";
 	
 	echo "<form action=\"".$_SERVER['PHP_SELF']."?creanew\" method=\"post\" enctype=\"multipart/form-data\" >\n";
 	
 	
 	echo "<label for=\"nome_report\" >"._("Report name (use only letters, numbers, underscore and dashes):")."</label>\n ";
 	echo "<input type=\"text\" name=\"nome_report\" id=\"nome_report\" maxlength=\"250\" size=\"40\" /><br /><br />\n";
 	
 	if($TIPO_NEW=="tabella"){

 		// prendi tabelle
	 	$tab=RegTools::prendi_tabelle();
	 	
	 	
 		echo "<input type=\"hidden\" name=\"tipo_report\" value=\"t\" />\n";
 		echo "<input type=\"hidden\" name=\"def_query\" value=\"\" />\n";
	 	
	 	echo "<label for=\"tabella\" >"._("Table:")."</label>\n ";
	 	echo "<select name=\"tabella\" id=\"tabella\">\n";
	 	for($i=0;$i<count($tab);$i++){
	 		
	 		echo "<option value=\"".$tab[$i]['table_name']."\">".$tab[$i]['table_name']."</option>\n";
	 		
	 	}
	 	echo "</select>\n";
 	
 	}
 	else{
 		
 		echo "<input type=\"hidden\" name=\"tabella\" value=\"\" />\n";
 		echo "<input type=\"hidden\" name=\"tipo_report\" value=\"q\" />\n";
 		
 		echo "<label for=\"def_query\" >"._("SQL query for the report:")."</label>\n ";
 		echo "<input value=\""._("Test")."\" onclick=\"try_query(document.getElementById('def_query').value,1)\" type=\"button\" />\n";
 		echo "<span id=\"feed_altro_1\" class=\"feed_altro\">&nbsp;</span>\n";
 		echo "<br />\n";
 		
 		echo "<textarea name=\"def_query\" id=\"def_query\" cols=\"80\" rows=\"8\"></textarea>\n";
	 	
 		
 	}
 	
 	
 	echo "<br /><br /><label for=\"accesso\" >"._("Access type:")."</label>\n ";
 	echo "<select name=\"accesso\" id=\"accesso\" onchange=\"if(this.value=='GROUP'){ document.getElementById('gruppi').style.display='';} else {document.getElementById('gruppi').style.display='none';}\">
 		<option value=\"RESTRICT\" >"._("Not allowed")."</option>
 		<option value=\"PUBLIC\" >"._("Public (web)")."</option>
 		<option value=\"FRONTEND\" >"._("Frontend (only authenticated users)")."</option>
 		<option value=\"GROUP\" >"._("Only for groups (select)")."</option>
 		</select>\n";
 	
 	
 	// prendi gruppi
 	$gruppi= RegTools::prendi_gruppi();
 	
 	
 	echo "<div id=\"gruppi\" style=\"display:none;\">
 	<br /><br /><label for=\"gid\" >"._("Allowed groups:")."</label><select name=\"gid[]\" id=\"gid\" multiple=\"multiple\" size=\"5\">
 	";
 	
 	for($g=0;$g<count($gruppi);$g++){
 		
 		echo "<option value=\"".$gruppi[$g]['gid']."\">".$gruppi[$g]['gid']." - ".$gruppi[$g]['nome_gruppo']."</option>\n";
 	}
 	
 	echo "</select>
 	
 	</div>";
 	
 	// cerca fogli di stile XSL custom
 	
 	$dir_xsl = _PATH_XSL;
 	
 	if (is_dir($dir_xsl)) {
	   if ($dh = opendir($dir_xsl)) {
	      while (($file_xsl = readdir($dh)) !== false) {
	             if($file_xsl!='.' && $file_xsl!='..' && is_file($dir_xsl."/".$file_xsl)) $xsl_custom[]=$file_xsl;
	        }
	        closedir($dh);
	    }
	}

 	
 	echo "<br /><br /><label for=\"xsl\" >"._("XSL sheet to be linked:")."</label>\n<select name=\"xsl\" id=\"xsl\">
 			<option value=\"default\">"._("default")."</option>";
 	
 	for($i=0;$i<count($xsl_custom);$i++){
 		
 		echo "\t\t<option value=\"".$xsl_custom[$i]."\">".$xsl_custom[$i]."</option>\n";
 		
 	}
 			
 	echo "</select>\n";
 	
 	echo _("otherwise link a new file:")." <input type=\"file\" name=\"new_xsl\" id=\"new_xsl\" size=\"48\" />\n";
 	
 	
 	
 	###################################################
 	#
 	#	FO
 	#
 	
 	// cerca fogli di stile FO custom
 	
 	$dir_fo = _PATH_XSL;
 	$fo_custom=array();
 	
 	if (is_dir($dir_fo)) {
	   if ($dh = opendir($dir_fo)) {
	      while (($file_fo = readdir($dh)) !== false) {
	             if($file_fo!='.' && $file_fo!='..' && is_file($dir_fo."/".$file_fo)) $fo_custom[]=$file_fo;
	        }
	        closedir($dh);
	    }
	}
 	
 	
 	echo "<br /><br /><label for=\"fo\" >"._("XSL-FO stylesheet to be associed:")."</label>\n<select name=\"fo\" id=\"fo\">
 			<option value=\"\"> - "._("-")." - </option>
 			<option value=\"default\">"._("default")."</option>
 			";
 	
 	for($i=0;$i<count($fo_custom);$i++){
 		
 		echo "\t\t<option value=\"".$fo_custom[$i]."\">".$fo_custom[$i]."</option>\n";
 		
 	}
 			
 	echo "</select>\n";
 	
 	echo _("otherwise link a new file:")." <input type=\"file\" name=\"new_fo\" id=\"new_fo\" size=\"48\" />\n";
 	
 	echo "<br /><br /><input type=\"submit\" name=\"invia\" value=\"   "._("Save")."   \" />\n";
 	
 	echo "</form>\n";
 	
 	echo closeLayout1();
 	exit;
 }
 
 
 
 
 ######################################
 #
 #	MODIFICA
 #
 ######################################
 
 
else if(isset($_GET['mod']) && $_GET['mod']>0){
 	
 	$files=array("js/test_query.js");
 	 
 	echo openLayout1(_("XML Administration and Reports"),$files);

	echo breadcrumbs(array("HOME","ADMIN","xmlreport.php"=>_("XML administration and reports"), _("XML report")));

	echo "<h1>"._("Modify XML report")."</h1>\n";
 	
 	echo "<form action=\"".$_SERVER['PHP_SELF']."?appmod\" method=\"post\" enctype=\"multipart/form-data\" >\n";
 	
 	$q=$vmreg->query("SELECT * FROM {$db1['frontend']}{$db1['sep']}xml_rules WHERE id_xml_rules=".intval($_GET['mod']));
 	
 	$RS=$vmreg->fetch_assoc($q);
 	
 	$TIPO_REPORT = ((string) $RS['tipo_report']=='t') ? "t" : "q";
 	
 	$sel_RESTRICT = ($RS['accesso']=='RESTRICT') ? "selected=\"selected\"" : "";
 	$sel_PUBLIC = ($RS['accesso']=='PUBLIC') ? "selected=\"selected\"" : "";
 	$sel_FRONTEND = ($RS['accesso']=='FRONTEND') ? "selected=\"selected\"" : "";
 	$sel_GROUP = ($RS['accesso']=='GROUP') ? "selected=\"selected\"" : "";
 	
 	
 	echo "<label for=\"nome_report\" >"._("Report name (use only letters, numbers, underscore and dashes):")."</label>\n ";
 	echo "<input type=\"text\" name=\"nome_report\" id=\"nome_report\" maxlength=\"250\" size=\"40\" value=\"".$RS['nome_report']."\"/><br /><br />\n";
 	
 	
 	if($TIPO_REPORT=="t"){
 		
 		echo "<input type=\"hidden\" name=\"tipo_report\" value=\"t\" />\n";
 	}
 	else if ($TIPO_REPORT=="q"){
 		
 		echo "<input type=\"hidden\" name=\"tabella\" value=\"\" />\n";
 		echo "<input type=\"hidden\" name=\"tipo_report\" value=\"q\" />\n";
 		
 		echo "<label for=\"def_query\" >"._("SQL query for the report:")."</label>\n ";
 		echo "<input value=\""._("Test")."\" onclick=\"try_query(document.getElementById('def_query').value,1)\" type=\"button\" />\n";
 		echo "<span id=\"feed_altro_1\" class=\"feed_altro\">&nbsp;</span>\n";
 		echo "<br />\n";
 		
 		echo "<textarea name=\"def_query\" id=\"def_query\" cols=\"80\" rows=\"8\">".stripslashes($RS['def_query'])."</textarea>\n";
 		
 	}
 	
 	
 	echo "<br /><br /><label for=\"accesso\" >"._("Access type:")."</label>\n ";
 	
 
 	
 	
 	
 	// TENDINA ACCESS TYPE
 	
 	echo "<select name=\"accesso\" id=\"accesso\" onchange=\"if(this.value=='GROUP'){ document.getElementById('gruppi').style.display='';} else {document.getElementById('gruppi').style.display='none';}\">
 		<option value=\"RESTRICT\" $sel_RESTRICT>"._("Not allowed")."</option>
 		<option value=\"PUBLIC\" $sel_PUBLIC>"._("Public (web)")."</option>
 		<option value=\"FRONTEND\" $sel_FRONTEND>"._("Frontend (only authenticated users)")."</option>
 		<option value=\"GROUP\" $sel_GROUP>"._("Only for groups (select)")."</option>
 		</select>\n";
 	
 	
 	
 	
 	
 	// prendi gruppi
 	$gruppi= RegTools::prendi_gruppi();
 	
 	$sty_gruppi = ($sel_GROUP=='') ? "display:none;" :"";
 	
 	echo "<div id=\"gruppi\" style=\"$sty_gruppi\">
 	<br /><br /><label for=\"gid\" >"._("Allowed groups:")."</label><select name=\"gid[]\" id=\"gid\" multiple=\"multiple\" size=\"5\">
 	";
 	
 	$gids=explode(",",$RS['accesso_gruppo']);
 	
 	for($g=0;$g<count($gruppi);$g++){
 	
 		if(is_array($gids) && in_array($gruppi[$g]['gid'],$gids)){ 
 			$gsel="selected=\"selected\"";	
 		}
 		else $gsel="";	
 			
 		echo "<option value=\"".$gruppi[$g]['gid']."\" $gsel>".$gruppi[$g]['gid']." - ".$gruppi[$g]['nome_gruppo']."</option>\n";
 	}
 	
 	echo "</select>
 	
 	</div>";
 	
 	
 	
 	
 	
 	
 	
 	// cerca fogli di stile XSL custom
 	
 	$dir_xsl = _PATH_XSL;

 	if (is_dir($dir_xsl)) {
	   if ($dh = opendir($dir_xsl)) {
	      while (($file_xsl = readdir($dh)) !== false) {
	             if($file_xsl!='.' && $file_xsl!='..' && is_file($dir_xsl."/".$file_xsl)) $xsl_custom[]=$file_xsl;
	        }
	        closedir($dh);
	    }
	}

	
 	
 	
 	echo "<br /><br /><label for=\"xsl\" >"._("XSL sheet to be linked:")."</label>\n<select name=\"xsl\" id=\"xsl\">
 			<option value=\"default\">"._("default")."</option>";
 	
 	for($i=0;$i<count($xsl_custom);$i++){
 		
 		$sel_xsl=($RS['xsl']==$xsl_custom[$i]) ? "selected=\"selected\"" : "";
 		
 		echo "\t\t<option value=\"".$xsl_custom[$i]."\" $sel_xsl>".$xsl_custom[$i]."</option>\n";
 		
 	}
 			
 	echo "</select>\n";
 	
 	echo _("otherwise link a new file:")." <input type=\"file\" name=\"new_xsl\" id=\"new_xsl\" size=\"48\" />\n";
 	
 	
 	
 	###################################################
 	#
 	#	FO
 	#
 	
 	// cerca fogli di stile FO custom
 	
 	$dir_fo = _PATH_XSL;
 	$fo_custom=array();
 	
 	if (is_dir($dir_fo)) {
	   if ($dh = opendir($dir_fo)) {
	      while (($file_fo = readdir($dh)) !== false) {
	             if($file_fo!='.' && $file_fo!='..' && is_file($file_fo)) $fo_custom[]=$file_fo;
	        }
	        closedir($dh);
	    }
	}
 	
	$sel_fo_none=($RS['xslfo']=='-1') ?  "selected=\"selected\"" : "";
 		
 	echo "<br /><br /><label for=\"fo\" >"._("XSL-FO stylesheet to be associed:")."</label>\n<select name=\"fo\" id=\"fo\">
 			<option value=\"default\">"._("default")."</option>
 			<option value=\"-1\" $sel_fo_none> - "._("nobody")." - </option>
 			";
 	
 	for($i=0;$i<count($fo_custom);$i++){
 		
 		$sel_fo=($RS['xsl']==$fo_custom[$i]) ? "selected=\"selected\"" : "";
 		
 		echo "\t\t<option value=\"".$fo_custom[$i]."\" $sel_fo>".$fo_custom[$i]."</option>\n";
 		
 	}
 			
 	echo "</select>\n";
 	
 	echo _("otherwise link a new file:")." <input type=\"file\" name=\"new_fo\" id=\"new_fo\" size=\"48\" />\n";
 	
 	echo "<input type=\"hidden\" name=\"id_xml_rules\" value=\"".intval($_GET['mod'])."\" />\n";
 	echo "<br /><br /><input type=\"submit\" name=\"invia\" value=\"   "._("Save")."   \" />\n";
 	
 	echo "</form>\n";
 	
 	echo closeLayout1();
 	exit;
 }
 
 
 
 
 
 
 
 
 
 
 $files=array('sty/tabelle.css');
 
 
 
 
 
 echo openLayout1(_("XML Administration and Reports"),$files);

	echo breadcrumbs(array("HOME","ADMIN","xmlreport.php"=>_("XML administration and reports")));

	echo "<h1>"._("XML Administration and Reports")."</h1>\n";


	echo "<p><a href=\"?new\">"._("Create new XML report from table or view")."</a> <span class=\"nocss\">|</span> <br /><br /><a href=\"?newquery\">"._("Create new XML report from query")."</a></p>";
	
	// PRENDI RECORD IN DB

	$q=$vmreg->query("SELECT x.*, ".$vmreg->concat("u.nome,' ',u.cognome", 'nomecognome')." FROM {$db1['frontend']}{$db1['sep']}xml_rules x, {$db1['frontend']}{$db1['sep']}utente u
					WHERE x.autore=u.id_utente
					ORDER BY tabella ASC");

	$mat_xml=$vmreg->fetch_assoc_all($q);
	
	echo "<table class=\"tab-color\" summary=\"Tabella Log\">
	
	 	<tr>
			<th>"._("report name")."</th>
			<th>"._("report type")."</th>
			<th>"._("table")."</th>
			<th>XSL</th>
			<th>XSL-FO</th>
			<th>"._("access")."</th>
			<th>"._("Authorized groups")."</th>
			<th>"._("author")."</th>
			<th>"._("date")."</th>
			<th>"._("preview")."</th>
			<th>"._("modify")."</th>
			<th class=\"arancio\">"._("delete")."</th>
		</tr>
	
		";
	 
	 for($i=0;$i<count($mat_xml);$i++){
	 
	 	$data = VFDate::date_encode($mat_xml[$i]['lastData'],true,'string');
	 	
	 	$xsl_riga=($mat_xml[$i]['xsl']=='') ? '<em>'._("default").'</em>' : "<em>".$mat_xml[$i]['xsl']."</em>";
	 	
	 	if($mat_xml[$i]['xslfo']=='-1')  $xslfo_riga="-";
	 	else if($mat_xml[$i]['xslfo']=='') $xslfo_riga="<em>"._("default")."</em>"; 
	 	else  $xslfo_riga=$mat_xml[$i]['xslfo'];
	 	
	 	
	 	
	 	$tipo_report= ($mat_xml[$i]['tipo_report'].""=='t') ? "da tabella" : "da query";
	 	
	 	
	 	// link anteprima
	 	$link_anteprima = ($mat_xml[$i]['tipo_report'].""=='t') ?
	 					  FRONT_DOCROOT."/xml/".$mat_xml[$i]['tabella'] 
	 					  :
	 					  FRONT_DOCROOT."/xml/@".$mat_xml[$i]['nome_report'] ;
	 	
	 	echo "
	 	<tr>
			<td>".$mat_xml[$i]['nome_report']."</td>
			<td>".$tipo_report."</td>
			<td>".$mat_xml[$i]['tabella']."</td>
			<td>".$xsl_riga."</td>
			<td>".$xslfo_riga."</td>
			<td>".$mat_xml[$i]['accesso']."</td>
			<td>".$mat_xml[$i]['accesso_gruppo']."</td>
			<td>".$mat_xml[$i]['nomecognome']."</td>
			<td>".$data."</td>
			<td><a href=\"".$link_anteprima."/\">"._("preview")."</a></td>
			<td><a href=\"xmlreport.php?mod=".$mat_xml[$i]['id_xml_rules']."\">"._("modify")."</a></td>
			<td><a href=\"xmlreport.php?del=".$mat_xml[$i]['id_xml_rules']."\">"._("delete")."</a></td>
		</tr>
		 ";
	 }
	 
	 echo "</table>\n";














echo closeLayout1();



?>