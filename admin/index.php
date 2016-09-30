<?php
/**
 * @desc Menu dell'area di amministrazione
 * @package VFront
 * @subpackage Administration
 * @author M.Marcello Verona
 * @copyright 2007-2013 M.Marcello Verona
 * @version 0.96 $Id: index.php 1095 2014-06-19 09:14:39Z marciuz $
 * @license http://www.gnu.org/licenses/gpl.html GNU Public License 
 */


include("../inc/conn.php");
include("../inc/layouts.php");

 proteggi(2);
 
 $files=array();
 $files[]="sty/admin.css";
 
 if($_SESSION['user']['livello']==3 && $_SESSION['VF_VARS']['show_updates']==1){
	  $files[]="js/find_updates.js";
 }
 
 echo openLayout1(_("VFront administration"), $files);
 
 echo breadcrumbs(array("HOME","ADMIN"));

 echo "<h1>"._("VFront administration")."</h1>\n";

 echo "<div id=\"find_updates\" style=\"display:none\" class=\"alertbox\"></div>\n";
 
 echo " <div>\n";
 
 
 if($_SESSION['user']['livello']==3){
 	
 echo "
	<div id=\"box-registri\" class=\"box-db\">
		<h2>"._("Registry administration")."</h2>
		<ul class=\"ul-db\">
		";
 
	$qg = $vmreg->query("SELECT * FROM {$db1['frontend']}{$db1['sep']}gruppo ORDER BY gid");
			
	// se ci sono gruppi
	if($vmreg->num_rows($qg)==0){
		
		echo "\t\t<li><a href=\"menu_registri.php?initreg\">"._("Initialize registry")."</a></li>\n";
	}
	else{
 
		echo "\t\t<li><a href=\"menu_registri.php\">"._("Registries menu")."</a></li>\n";
			
 		echo "\t\t<li><a href=\"sync_reg_tab.php\">"._("Syncronize database/frontend")."</a></li>\n";
 		
 		echo "\t\t<li><a href=\"update.php?test\">"._("VFront Update")."</a></li>\n";
		
	}	
				
	echo	"
		</ul>
	</div>\n";
 }
	
	echo "
	<div id=\"box-utenti\" class=\"box-db\">
		<h2>"._("User settings")."</h2>
		<ul class=\"ul-db\">
			<li><a href=\"utenze.db.php\">"._("DB user settings")."</a></li>
			<li>&nbsp;</li>
			<li>&nbsp;</li>
		</ul>
	</div>
	";

	
	echo "

 </div>
 
 
 <div style=\"clear:left;\">
 
 
 
	<div id=\"box-stat\" class=\"box-db\">
		<h2>"._("DB Statistics")."</h2>
		<ul class=\"ul-db\">
			<li><a href=\"../stats/\">"._("Database statistics")."</a></li>
			<li>&nbsp;</li>
			<li>&nbsp;</li>
		</ul>
	</div>	

			
	<div id=\"box-exportdb\" class=\"box-db\">
		<h2>"._("Data export")."</h2>
		<ul class=\"ul-db\">\n";
	
	 if($_SESSION['user']['livello']==3){
			echo "<li><a href=\"export_sql.php\">"._("Database export (DUMP)")."</a></li>\n";
	 }

	 	echo"
	 		<li><a href=\"export_data.php\">"._("Data Export")."</a></li>
			<li>&nbsp;</li>
			<li>&nbsp;</li>
			
		</ul>
	</div>
</div>	

 <div style=\"clear:left;\">
 
 
  	<div id=\"box-log\" class=\"box-db\">
		<h2>"._("Log and data rescue")."</h2>
		<ul class=\"ul-db\">
			<li><a href=\"log.php\">"._("Operations log for database")."</a></li>
			<li>&nbsp;</li>
			<li>&nbsp;</li>
			
		</ul>
	</div>
 
 	<div id=\"box-xml\" class=\"box-db\">
		<h2>"._("XML and reports")."</h2>
		<ul class=\"ul-db\">
			<li><a href=\"xmlreport.php\">"._("Administer XML and reporting")."</a></li>
			<li>&nbsp;</li>
			<li>&nbsp;</li>
			
		</ul>
	</div>
        
 	<div id=\"box-api\" class=\"box-db\">
		<h2>"._("VFront API")."</h2>
		<ul class=\"ul-db\">
			<li><a href=\"api_admin.php\">"._("Administer VFront API")."</a></li>
			<li>&nbsp;</li>
			<li>&nbsp;</li>
			
		</ul>
	</div>
";

 echo "<div style=\"clear:left;\">\n";
	 	
 
 if($_SESSION['user']['livello']==3){

	 echo "
 <div id=\"box-layout\" class=\"box-db\">
		<h2>"._("Layout settings")."</h2>
		<ul class=\"ul-db\">
			<li><a href=\"themes.php\">"._("Select themes")."</a></li>
			<!-- <li><a href=\"set_layout.php\">"._("Home page settings")."</a></li> -->
			<li>&nbsp;</li>
			<li>&nbsp;</li>
		</ul>
	</div>
 ";

echo "
 <div id=\"box-admindb\" class=\"box-db\">
		<h2>"._("DB Administration")."</h2>
		<ul class=\"ul-db\">
			<li><a href=\"../plugins/adminer/?vfseldb\" target=\"_blank\">"._("Adminer")."</a></li>
			<li><a href=\"check_db.php\">"._("Compatibility test")."</a></li>
			<li><a href=\"error_log.php\">"._("DB error log")."</a></li>
		</ul>
	</div>
 ";
 
 //echo "<div style=\"clear:left;\">\n";

 
	echo "
	<div id=\"box-impostazioni\" class=\"box-db\">
		<h2>"._("Miscellaneous")."</h2>
		<ul class=\"ul-db\">
			<li><a href=\"variabili.php\">"._("System variables")."</a></li>
			<li><a href=\"vfront.info.php\">"._("VFront settings")."</a></li>
			<li><a href=\"doc_tecnica.php\">"._("Technical documentation")."</a></li>
			<li><a href=\"../credits.php\">"._("Credits and applications used")."</a></li>
		</ul>
	</div>	
	
	";
	
	
 }
 elseif($_SESSION['user']['livello']==2){	
 
	echo "
	<div id=\"box-impostazioni\" class=\"box-db\">
		<h2>"._("Miscellaneous")."</h2>
		<ul class=\"ul-db\">
			<li><a href=\"doc_tecnica.php\">"._("Technical documentation")."</a></li>
			<li>&nbsp;</li>
			<li>&nbsp;</li>
		</ul>
	</div>	
	
	";
	
	
 }

 
 echo "</div>\n";
 echo "</div>\n";
 
 
 echo closeLayout1();

?>