<?php
/**
 * Attraverso lo script è eseguita una diagnostica dell'attuale stato della configurazione
 * di VFront. Sono presenti varie sezioni. L'esecuzione di questo script è appannaggio degli amministratori 
 * (livello 3).
 * 
 * @desc Informazioni sulla installazione corrente di VFront
 * @package VFront
 * @subpackage Administration
 * @author M.Marcello Verona
 * @copyright 2007 M.Marcello Verona
 * @version 0.96 $Id: vfront.info.php 1147 2015-04-24 15:40:56Z marciuz $
 * @license http://www.gnu.org/licenses/gpl.html GNU Public License 
 */



require_once("../inc/conn.php");
require_once("../inc/layouts.php");

proteggi(3);

$OUT=openLayout1(_("VFront settings"),array("sty/admin.css"));

$OUT.=breadcrumbs(array("HOME","ADMIN",strtolower(_("Vfront settings")) ));

$OUT.="<h1>"._("VFront settings")."</h1>";




$flog="# VFront Log: ".$_SERVER['SERVER_NAME']." - ".date("Y-m-d H:i:s")."\n";
$flog.="--------------------------------------------------------------------\n";














$OUT.="<h2 class=\"title-vfrontinfo\">"._("VFront Version")."</h2>";

// Versione VFRONT -----------------------------------------------------------------------------------------------

    $classe='';
	$xmlversion = Common::vfront_version(true);
	
	$OUT.="<span class=\"grigio\">"._("VFront Version:")."</span> ".
		 "<strong><span class=\"$classe\">".$xmlversion->version."</span></strong>\n";
	$OUT.="<div class=\"piccolo\">"._('Update information not yet available')."</div><br />\n";

	exec('svnversion -c '. FRONT_ROOT,$output,$ret);
	$output_svn=implode("\n",$output);
    
	if(preg_match('|([0-9]+)M|',$output_svn,$revision)){
		
//		preg_match('|Last Changed Date:(.*)|',$output_svn,$last_changed_date);
		
		$OUT.="<span class=\"grigio\">"._("VFront Subversion:")."</span> ".
			 "<strong><span class=\"$classe\">".$revision[1]."</span></strong>\n"; // - ".$last_changed_date[1]."\n";
		$OUT.="<div class=\"piccolo\">"._("VFront is using SVN")."</div><br />\n";
	
	}
	else{
		
		preg_match('|\$Date:(.*)\$|', $output_svn, $date);
		preg_match('|\$Revision:(.*)\$|', $output_svn, $revision);
		
        if(count($date)>0 && count($revision)>0){
            $OUT.="<span class=\"grigio\">"._("VFront Subversion:")."</span> ".
                 "<strong><span class=\"$classe\">".$revision[1]."</span></strong> - ".$date[1]."\n";
            $OUT.="<div class=\"piccolo\">"._("VFront is not using SVN")."</div><br />\n";
        }
	}


	// info dal conf
	
	switch($db1['dbtype']){
		case 'mysql': $nome_db=$db1['dbname'];
		break;
	
		case 'postgres': $nome_db=$db1['postgres_dbname'];
		break;

		case 'oracle': $nome_db="<em>"._('Not available')."</em>";
		break;

		case 'sqlite': $nome_db=$db1['filename'];
		break;

	}
	
	$OUT.="<span class=\"grigio\">"._("DB:")."</span> ".
		 "<strong><span class=\"$classe\">".$nome_db."</span></strong>\n";
	$OUT.="<div class=\"piccolo\">"._('Database data currently in use')."</div><br />\n";



	// info dal conf
	$DB_RULES_TYPE=(USE_REG_SQLITE) ? "SQLite": $db1['dbtype']." internal";
	$OUT.="<span class=\"grigio\">"._("DB Rules method:")."</span> ".
		 "<strong><span class=\"$classe\">".$DB_RULES_TYPE."</span></strong>\n";
	$OUT.="<div class=\"piccolo\">"._('Method used for VFront database rules')."</div><br />\n";



	// info dal conf
	$DB_RULES=(USE_REG_SQLITE) ? $db1['filename_reg']. " (SQLite)":$db1['frontend'];

	$OUT.="<span class=\"grigio\">"._("DB Rules:")."</span> ".
		 "<strong><span class=\"$classe\">".$DB_RULES."</span></strong>\n";
	$OUT.="<div class=\"piccolo\">"._('Database rules currently in use')."</div><br />\n";
	

	// info dal conf
	$tipo_auth = ($conf_auth['tipo_external_auth']=='') ? "Interna" : $conf_auth['tipo_external_auth'];
	
	$OUT.="<span class=\"grigio\">"._("Authentication type:")."</span> ".
		 "<strong><span class=\"$classe\">".$tipo_auth."</span></strong>\n";
	$OUT.="<div class=\"piccolo\">"._('Authentication type:')."</div><br />\n";


	

$OUT.="<h2 class=\"title-vfrontinfo\">"._("Software version")."</h2>";


// VERSIONE Apache ---------------------------------------------------------------------------------------------------

$classe = (preg_match("!^2!",$_SERVER["SERVER_SOFTWARE"])) ? "verde" : "arancio";

$flog.="ApacheVersion: ".$_SERVER["SERVER_SOFTWARE"]."\n";

$OUT.="<span class=\"grigio\">"._("Apache Version").":</span> <strong><span class=\"$classe\">".$_SERVER["SERVER_SOFTWARE"]."</span></strong>\n";
$OUT.="<div class=\"piccolo\">"._("VFront requires Apache version &gt;= 2.x")."</div><br />\n";


// VERSIONE PHP ---------------------------------------------------------------------------------------------------

$classe = (preg_match("!^5!",phpversion())) ? "verde" : "arancio";

$flog.="PHPVersion: ".phpversion()."\n";

$OUT.="<span class=\"grigio\">"._("PHP version").":</span> <strong><span class=\"$classe\">".phpversion()."</span></strong>\n";
$OUT.="<div class=\"piccolo\">"._("VFront requires PHP version &gt;= 5.x")."</div><br />\n";




// VERSIONE DATABASE ---------------------------------------------------------------------------------------------------

$db_version=$vmsql->db_version();

if($db1['dbtype']=='oracle'){

    $db_version=$vmsql->db_version();

    $OUT.="<span class=\"grigio\">"._("Database version:")."</span> <strong><span class=\"$classe\">".$db_version."</span></strong>\n";
}
else if($db1['dbtype']=='mysql'){


        $classe = (preg_match("!^5\.*!",$db_version)) ? "verde" : "arancio";
        $OUT.="<span class=\"grigio\">"._("Database version:")."</span> <strong><span class=\"$classe\">".$db_version."</span></strong>\n";
        $OUT.="<div class=\"piccolo\">"._("VFront requires MySQL version &gt;= 5.x")."</div><br />\n";
}
else if($db1['dbtype']=='postgres'){


        $classe = (preg_match("!8\.*!",$db_version)) ? "verde" : "arancio";
        $OUT.="<span class=\"grigio\">"._("Database version:")."</span> <strong><span class=\"$classe\">".$db_version."</span></strong>\n";
        $OUT.="<div class=\"piccolo\">"._("VFront requires Postgres version &gt;= 8.x")."</div><br />\n";
}
else if($db1['dbtype']=='sqlite'){
	

        $classe = (preg_match("!3\.*!",$db_version)) ? "verde" : "arancio";
        $OUT.="<span class=\"grigio\">"._("Database version:")."</span> <strong><span class=\"$classe\">".$db_version."</span></strong>\n";
        $OUT.="<div class=\"piccolo\">"._("VFront run better with SQLite version &gt;= 3.x")."</div><br />\n";
	
}

	
$flog.="DB Version: ".$db_version."\n";

// VFRONT CONFIG



// COMPATIBILITY CHECK ---------------------------------------------------------------------------------------------------


require_once("../inc/func.checkdb.php");

$checkdb=check_db();
$OUT.="<h2 class=\"title-vfrontinfo\">"._("Compatibility check")."</h2>";
$problems_found=($checkdb['n']==0) ? "<strong><span class=\"verde\">0</span></strong>"
	: "<strong><span class=\"rosso\">".$checkdb['n']."</span></strong> - "
	.sprintf(_('Please see %s for details')
			,"<a href=\"check_db.php\">"._('Compatibility check')."</a>");

$OUT.="<span class=\"grigio\">"._("Problems found").":</span> $problems_found";
$OUT.="<div class=\"piccolo\">"._("Check compatibility between VFront and your DB")."</div><br />\n";











// SECURITY SETTINGS ---------------------------------------------------------------------------------------------------
$permessi_dirconf= substr(sprintf('%o', fileperms('../conf')), -4);
$permessi_fileconf= substr(sprintf('%o', fileperms("../conf/$CONF_FILE")), -4);
$file_modello=is_file("../conf/conf.vfront.php.dist");
$dir_install=is_dir("../_install");

$OUT.="<h2 class=\"title-vfrontinfo\">"._("Security settings")."</h2>";

$write_dir_conf=(!is_writable('../conf')) ? "<span class=\"verde\">"._("NO")."</span>" : "<span class=\"rosso\">"._("YES")."</span>";
$OUT.="<span class=\"grigio\">"._("Configuration folder writable:")."</span> <strong>$write_dir_conf ($permessi_dirconf)</strong>\n";
$OUT.="<div class=\"piccolo\">"._("The conf directory should not be writable by the user web")."</div><br />\n";

$write_file_conf=(!is_writable("../conf/$CONF_FILE")) ? "<span class=\"verde\">"._("NO")."</span>" : "<span class=\"rosso\">"._("YES")."</span>";
$OUT.="<span class=\"grigio\">"._("Configuration file writable")."</span> <strong>$write_file_conf ($permessi_fileconf)</strong>\n";
$OUT.="<div class=\"piccolo\">"._("The configuration file not should be writable by the user web")."</div><br />\n";

$presenza_file_modello=(!$file_modello) ? "<span class=\"verde\">"._("NO")."</span>" : "<span class=\"rosso\">"._("YES")."</span>";
$OUT.="<span class=\"grigio\">"._("Installation file present:")."</span> <strong>$presenza_file_modello</strong>\n";
$OUT.="<div class=\"piccolo\">"._("The file conf.vfront.php.dist should be removed")."</div><br />\n";

$presenza_dir_install=(!$dir_install) ? "<span class=\"verde\">"._("NO")."</span>" : "<span class=\"rosso\">"._("YES")."</span>";
$OUT.="<span class=\"grigio\">"._("Installation directory present:")."</span> <strong>$presenza_dir_install</strong>\n";
$OUT.="<div class=\"piccolo\">"._("The install directory <em>_install</em> should be removed")."</div><br />\n";





// ESTENSIONI IMPORTANTI ---------------------------------------------------------------------------------------------------
$OUT.="<h2 class=\"title-vfrontinfo\">"._("PHP modules")."</h2>";

$ext=get_loaded_extensions();

$flog.="PHPLoadedExtensions: ".implode(",",$ext)."\n";

$l_mysqli=(in_array('mysqli',$ext)) ? "<span class=\"verde\">"._("YES")."</span>" : "<span class=\"rosso\">"._("NO")."</span>";
$OUT.="<span class=\"grigio\">"._("MySQLi library (MySQL Improved):")."</span> <strong>$l_mysqli</strong>\n";
$OUT.="<div class=\"piccolo\">"._("VFront uses the Mysqli library to connect to MySQL. If you use this DB the <b>extension should be loaded</b>")."</div><br />\n";


$l_mysqli=(in_array('mysql',$ext)) ? "<span class=\"verde\">"._("YES")."</span>" : "<span class=\"rosso\">"._("NO")."</span>";
$OUT.="<span class=\"grigio\">"._("MySQL library")."</span> <strong>$l_mysqli</strong>\n";
$OUT.="<div class=\"piccolo\">"._("VFront uses mysqli library if available. As an alternative it uses the old library php_mysql to connect to MySQ")."</div><br />\n";


$l_pgsql=(in_array('pgsql',$ext)) ? "<span class=\"verde\">"._("YES")."</span>" : "<span class=\"rosso\">"._("NO")."</span>";
$OUT.="<span class=\"grigio\">"._("Postgres library:")."</span> <strong>$l_pgsql</strong>\n";
$OUT.="<div class=\"piccolo\">"._("If you use this DB <b>the extension should be loaded</b>")."</div><br />\n";

$l_gd=(in_array('gd',$ext))   ? "<span class=\"verde\">"._("YES")."</span>" : "<span class=\"rosso\">"._("NO")."</span>";
$OUT.="<span class=\"grigio\">"._("GD Library:")."</span> <strong>$l_gd</strong>\n";
$OUT.="<div class=\"piccolo\">"._("GD library is used by Vfront for the creation of statistics graphs")."</div><br />\n";

$l_xsl=(in_array('xsl',$ext)) ? "<span class=\"verde\">"._("YES")."</span>" : "<span class=\"rosso\">"._("NO")."</span>";
$OUT.="<span class=\"grigio\">"._("XSL transformation:")."</span> <strong>$l_xsl</strong>\n";
$OUT.="<div class=\"piccolo\">".sprintf(_("If the module is not loaded, go to the %s menu and cancel the server-side XSLT CONVERSION"),
								"<a href=\"variabili.php\">"._("variables")."</a>")
								."</div><br />\n";



// IMPOSTAZIONI IMPORTANTI ---------------------------------------------------------------------------------------------------
$OUT.="<h2 class=\"title-vfrontinfo\">"._("PHP variables")."</h2>";

$php_ini_rg=(ini_get('register_globals')==0) ? "<span class=\"verde\">"._("OFF")."</span>" : "<span class=\"rosso\">"._("ON")."</span>";
$OUT.="<span class=\"grigio\">"._("register_globals:")."</span> <strong>$php_ini_rg</strong>\n";
$OUT.="<div class=\"piccolo\">"._("VFront requires register_globals to be disabled. You can turn this variable off in php.ini or httpd.conf")."</div><br />\n";





// IMPOSTAZIONI IMPORTANTI ---------------------------------------------------------------------------------------------------
$OUT.="<h2 class=\"title-vfrontinfo\">"._("Apache Modules")."</h2>";

$modules_apache=(array) @apache_get_modules();

$flog.="ApacheLoadedModules: ".implode(",",$modules_apache)."\n";

$apache_mod_rewrite=(in_array("mod_rewrite", $modules_apache)) ? "<span class=\"verde\">"._("YES")."</span>" : "<span class=\"rosso\">"._("NO")."</span>";
$OUT.="<span class=\"grigio\">"._("Mod_rewrite:")."</span> <strong>$apache_mod_rewrite</strong>\n";
$OUT.="<div class=\"piccolo\">"._("VFront requires the use of mod_rewrite for different functions, such as generating reports")."</div><br />\n";




// USO DI PEAR ---------------------------------------------------------------------------------------------------
/* Removed from 0.96beta3

$OUT.="<h2 class=\"title-vfrontinfo\">"._("PEAR packages")."</h2>";

$pear=(@include_once("PEAR.php")) ? "<span class=\"verde\">"._("YES")."</span>" : "<span class=\"rosso\">"._("NO")."</span>";
$OUT.="<span class=\"grigio\">"._("PEAR includes:")."</span> <strong>$pear</strong>\n";
$OUT.="<div class=\"piccolo\">"._("PEAR is used in creating statistical graphs")."</div><br />\n";

$pear_img_graph=(@include_once("Image/Graph.php")) ? "<span class=\"verde\">"._("YES")."</span>" : "<span class=\"rosso\">"._("NO")."</span>";
$OUT.="<span class=\"grigio\">"._("PEAR Image/Graph module include:")."</span> <strong>$pear_img_graph</strong>\n";
$OUT.="<div class=\"piccolo\">"._("PEAR module used for creating graphs")."</div><br />\n";


$pear_img_canvas=(@include_once("Image/Canvas.php")) ? "<span class=\"verde\">"._("YES")."</span>" : "<span class=\"rosso\">"._("NO")."</span>";
$OUT.="<span class=\"grigio\">"._("PEAR Image/Canvas module include:")."</span> <strong>$pear_img_canvas</strong>\n";
$OUT.="<div class=\"piccolo\">"._("PEAR module used to create statisctical graphs (dependency Image / Graph.php)")."</div><br />\n";


$pear_img_color=(@include_once("Image/Color.php")) ? "<span class=\"verde\">"._("YES")."</span>" : "<span class=\"rosso\">"._("NO")."</span>";
$OUT.="<span class=\"grigio\">"._("PEAR Image/Color module include:")."</span> <strong>$pear_img_color</strong>\n";
$OUT.="<div class=\"piccolo\">"._("PEAR module used to create statisctical graphs (dependency Image / Graph.php)")."</div><br />\n";


*/


// CARTELLE SCRIVIBILI O NO ---------------------------------------------------------------------------------------------------
$OUT.="<h2 class=\"title-vfrontinfo\">"._("Writable folders settings")."</h2>";


	// TMP

	$is_tmp_write = (is_writable(_PATH_TMP)) ? "<span class=\"verde\">"._("YES")."</span>" : "<span class=\"rosso\">"._("NO")."</span>";
	$is_tmp_write_txt = (is_writable(_PATH_TMP)) ? _("Temporary folder")." ( "._PATH_TMP." ) "._("is writable from VFront.") 
						: _("Temporary folder")." ( "._PATH_TMP." ) "._("is not writable by VFront. <br /> Modify the rights of the folder") ;
	
	
	$OUT.="<span class=\"grigio\">"._("Temp folder writable:")."</span> <strong>$is_tmp_write</strong>\n";
	$OUT.="<div class=\"piccolo\">$is_tmp_write_txt</div><br />\n";

	
	// HTML


	$is_html_write = (is_writable(FRONT_ROOT."/files/html")) ? "<span class=\"verde\">"._("YES")."</span>" : "<span class=\"rosso\">"._("NO")."</span>";
	$is_html_write_txt = (is_writable(FRONT_ROOT."/files/html")) ? _("Work folder set")." ( ".FRONT_ROOT."/files/html"." ) "._("is writable from VFront")
						: _("Work folder set")." ( ".FRONT_ROOT."/files/html"." ) "._("is not writable by VFront. <br /> Modify the rights of the folder") ;
	
	
	$OUT.="<span class=\"grigio\">"._("Folder HTML writable:")."</span> <strong>$is_html_write</strong>\n";
	$OUT.="<div class=\"piccolo\">$is_html_write_txt</div><br />\n";



	


	// ATTACH
	
	$is_attach_write = (is_writable(_PATH_ATTACHMENT)) ? "<span class=\"verde\">"._("YES")."</span>" : "<span class=\"rosso\">"._("NO")."</span>";
	$is_attach_write_txt = (is_writable(_PATH_ATTACHMENT)) ? _("The folder set for attachements:")." ( "._PATH_ATTACHMENT." ) "._("is writable from VFront.") 
						: _("The folder set for attachements:")." ( "._PATH_ATTACHMENT." ) "._("is not writable by VFront. <br /> Modify the rights of the folder");
	
	
	$OUT.="<span class=\"grigio\">"._("Attachments folder writable:")."</span> <strong>$is_attach_write</strong>\n";
	$OUT.="<div class=\"piccolo\">$is_attach_write_txt</div><br />\n";

	
	// TMP ATTACH
	
	$is_attach_tmp_write = (is_writable(_PATH_ATTACHMENT_TMP)) ? "<span class=\"verde\">"._("YES")."</span>" : "<span class=\"rosso\">"._("NO")."</span>";
	$is_attach_tmp_write_txt = (is_writable(_PATH_ATTACHMENT_TMP)) ? _("The temp folderset  for attachments")." ( "._PATH_ATTACHMENT_TMP." ) "._("is writable from VFront.") 
						: _("The temp folderset  for attachments")." ( "._PATH_ATTACHMENT_TMP." ) "._("is not writable by VFront. <br /> Modify the rights of the folder") ;
	
	
	$OUT.="<span class=\"grigio\">"._("Temp folder for attachments writable:")."</span> <strong>$is_attach_tmp_write</strong>\n";
	$OUT.="<div class=\"piccolo\">$is_attach_tmp_write_txt</div><br />\n";
	
	
	// DOCS
	
	$is_helpdocs_write = (is_writable(_PATH_HELPDOCS)) ? "<span class=\"verde\">"._("YES")."</span>" : "<span class=\"rosso\">"._("NO")."</span>";
	$is_helpdocs_write_txt = (is_writable(_PATH_HELPDOCS)) ? _("The folder set for documents")." ( "._PATH_HELPDOCS." ) "._("is writable from VFront.") 
						: _("The folder set for documents")." ( "._PATH_HELPDOCS." ) "._("is not writable by VFront. <br /> Modify the rights of the folder") ;
	
	
	$OUT.="<span class=\"grigio\">"._("Folder for documents writable:")."</span> <strong>$is_helpdocs_write</strong>\n";
	$OUT.="<div class=\"piccolo\">$is_helpdocs_write_txt</div><br />\n";
	
	// XSL
	
	$is_xsl_write = (is_writable(_PATH_XSL)) ? "<span class=\"verde\">"._("YES")."</span>" : "<span class=\"rosso\">"._("NO")."</span>";
	$is_xsl_write_txt = (is_writable(_PATH_HELPDOCS)) ? _("The folder set for XSL stylesheets")." ( "._PATH_XSL." ) "._("is writable from VFront.")
						: _("The folder set for XSL stylesheets")." ( "._PATH_XSL." ) "._("is not writable by VFront. <br /> Modify the rights of the folder");
	
	
	$OUT.="<span class=\"grigio\">"._("Folder for XSL stylesheets writable:")."</span> <strong>$is_xsl_write</strong>\n";
	$OUT.="<div class=\"piccolo\">$is_xsl_write_txt</div><br />\n";




// FOP ---------------------------------------------------------------------------------------------------
$OUT.="<h2 class=\"title-vfrontinfo\">Apache FOP</h2>";

$FOP_en=(_FOP_ENABLED) ? "<span class=\"verde\">"._("YES")."</span>" : "<span class=\"rosso\">"._("NO")."</span>";
$OUT.="<span class=\"grigio\">"._("FOP is running:")."</span> <strong>$FOP_en</strong>\n";
$OUT.="<div class=\"piccolo\">"._("Apache FOP is used by VFront transformation (XSLT to generate PDF from XML and other formats)")."</div><br />\n";

if(_FOP_ENABLED){

	// EXEC FOP?
	
	$is_fop_exec = (is_executable(_PATH_FOP)) ? "<span class=\"verde\">"._("YES")."</span>" : "<span class=\"rosso\">"._("NO")."</span>";
	$is_fop_exec_txt = (is_executable(_PATH_FOP)) ? _("FOP executable set in the configuration file")." ( "._PATH_FOP." ) "._("is executable by VFront.")
						: _("FOP executable set in the configuration file")." ( "._PATH_FOP." ) "._("is not executable from VFront.<br />Modify the file settings");
	
	
	$OUT.="<span class=\"grigio\">FOP "._("is executable?")." </span> <strong>$is_fop_exec</strong>\n";
	$OUT.="<div class=\"piccolo\">$is_fop_exec_txt</div><br />\n";
	
	
	
	
	// VERSIONE
	
	exec(_PATH_FOP." -v" ,$output,$ret);
	$output=preg_replace("'\n'"," ",implode("\n",$output));
	
	preg_match("'FOP.*?([0-9\.]+)'",$output,$found);
	
	if(isset($found[1])){
		$OUT.="<span class=\"grigio\">"._("Version")." FOP:</span> <strong>".$found[1]."</strong>\n";
		$OUT.="<div class=\"piccolo\">"._("Apache FOP is used by VFront for the XSLT transformation ")."</div><br />\n";
	}
}

$OUT.=closeLayout1();


if(isset($_GET['log'])){

	print $flog;
}
else{

	print $OUT;
}

?>