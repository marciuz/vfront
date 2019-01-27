<?php
#################################################
#
#	 This file is part of VFront.
#
#    VFront is free software; you can redistribute it and/or modify
#    it under the terms of the GNU General Public License as published by
#    the Free Software Foundation; either version 2 of the License, or
#    any later version.
#
#    VFront is distributed in the hope that it will be useful,
#    but WITHOUT ANY WARRANTY; without even the implied warranty of
#    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
#    GNU General Public License for more details.
#
#    You should have received a copy of the GNU General Public License
#    along with this program.  If not, see <http://www.gnu.org/licenses/>.
#

/**
 * VFront Web Installer - Utility di installazione dell'applicazione VFront
 * Caratteristiche richieste: PHP5.x , MySQL 5.x, php_mysqli
 * Oppure: PHP5.x , Postgres 8.x, php_pgsql
 * @package VFront
 * @subpackage VFront_Web_Installer
 * @author M.Marcello Verona
 * @copyright 2007-2010 M.Marcello Verona
 * @version 0.96 $Id: index.php 1173 2017-05-12 20:46:23Z marciuz $
 * @license http://www.gnu.org/licenses/gpl.html GNU Public License
 * 
 */

session_start();


// Impostazione della lingua
if(isset($_GET['install_lang'])){

	$_SESSION['install_lang']=preg_replace("|[^a-z_]+|i",'',$_GET['install_lang']);
}

$locale = (isset($_SESSION['install_lang'])) ? $_SESSION['install_lang'] : 'en_US';

$locale_dir = '../locale';

$domain = 'messages';

$encoding='UTF-8';

// TEST GETTEXT
if(!function_exists('_')){

	require_once('../plugins/phpgettext/gettext.inc.php');

	// gettext setup
	if(!defined('LC_MESSAGES')){

		@putenv("LC_ALL=$locale");
		@putenv("LC_MESSAGES=$locale");
	}

	T_setlocale(LC_MESSAGES, $locale);

	// Set the text domain as 'messages'
	bindtextdomain($domain, $locale_dir);

	// bind_textdomain_codeset is supported only in PHP 4.2.0+
	if (function_exists('bind_textdomain_codeset'))
	  bind_textdomain_codeset($domain, $encoding);

	textdomain($domain);
}
else{

	putenv("LANGUAGE=$locale");
	putenv("LC_ALL=$locale");
	setlocale(LC_ALL, $locale, "$locale.utf8");
	bindtextdomain($domain,$locale_dir);
	textdomain($domain);
}






if(!isset($_GET['p'])){

	$_GET['p']='0';
}






######################################################################
#
#	Procedura installazione
#

switch($_GET['p']){

	// Installation language select and license
	case '0': step0(); break;

	// system test
	case '1': step1(); break;

	// Form for conf file
	case '2': step2(); break;

	// SERVER ONLY: create conf file
	case '2s': step2s(); break;

	// DB connection
	case '3': step3(); break;

	// SERVER ONLY:	DB creation
	case '3s': step3s(); break;

	// Form crea admin
	case '4': step4(); break;

	// SERVER ONLY: crea admin
	case '4s': step4s(); break;

	// SERVER ONLY:	DB creation
	case '5': step5(); break;

	case 'panic': panic_page(); break;


}

























/**
 * @desc Genera il codice HTML da mostrare a inizio installazione
 * @return string HTML
 */
function step0(){
    
    if(isset($_GET['esci'])){
	
	$old_conf_file="../conf/conf.vfront.php";
	
	if(file_exists($old_conf_file)){
	    
	    unlink($old_conf_file);
	}
	
    }

	echo "<!DOCTYPE html PUBLIC \"-//W3C//DTD XHTML 1.0 Transitional//EN\" \"http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd\">
<html>
	<head><title>VFront Installer</title>
	<style type=\"text/css\">
		@import \"install.css\";
	</style>
	<script type=\"text/javascript\" src=\"../js/scriptaculous/lib/prototype.js\" ></script>
	<script type=\"text/javascript\" src=\"install.js\" ></script>
	</head>
	<body>\n";


	echo "<h1>VFront Installer - Select Language</h1>\n";

	echo "<div class=\"margin_left\">Welcome to the installer VFront. This procedure will install and configure VFront on your server.</div>";


	$license=htmlspecialchars(join("",file("../LICENSE")));

	echo "
	<form action=\"" . htmlentities($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8') . "\" method=\"get\" id=\"form1\">


		<div class=\"installbox\" id=\"ilanguage\">
			<h2>Select install language</h2>

			<p>
				<label for=\"lang\">Language</label>
				<select name=\"install_lang\" id=\"lang\">
					<option value=\"en_US\" selected=\"selected\">English</option>
					<option value=\"it_IT\" >Italiano</option>
				</select>
			</p>

			<p>&nbsp;</p>

		</div> <!-- END ilanguage -->

		<div class=\"installbox\" id=\"ilicense\">
			<h2>License</h2>

			<p>
				<textarea cols=\"82\" rows=\"14\" id=\"license\">$license</textarea>
			</p>

			<p>
				<input type=\"checkbox\" id=\"accept\" value=\"1\" />
				<label for=\"accept\" style=\"float:none\">Accept license</label>
			</p>

		</div> <!-- END ilicense -->


		<p><input type=\"hidden\" name=\"install0\" value=\"1\" />
		<input type=\"hidden\" name=\"p\" value=\"1\" />
		<input type=\"button\" id=\"invia\" value=\"  Next  &gt;&gt;\" onclick=\"lic();\" /></p>

	</form>

	</body>
	</html>";


}





/**
 * @desc Test del sistema
 * @return string HTML
 */
function step1(){

	echo "<!DOCTYPE html PUBLIC \"-//W3C//DTD XHTML 1.0 Transitional//EN\" \"http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd\">
<html>
	<head><title>"._('VFront installation')."</title>
	<style type=\"text/css\">
		@import \"install.css\";
	</style>

	<script type=\"text/javascript\" src=\"../js/scriptaculous/lib/prototype.js\" ></script>
	</head>
	<body>\n";


	echo "<h1>"._("VFront installation - System requirements")."</h1>\n";


	$cartella_writable=is_writable("../conf");

	$fatal=false;
	$notice=false;

	// prova a cambiare i diritti della directory conf:
	if(!$cartella_writable && !@chmod('../conf',0777)){

		echo "<p class=\"install-err\">".sprintf(_("Please set the <em>conf</em> folder as writeable and %s"),"<a href=\"".htmlentities($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8')."?p=1\">"._('try again')."</a>")."</p>";

		$fatal=true;
	}
	else{
		echo "<p class=\"install-ok\">"._("The folder <em>conf</em> is writeable")."</p>";
	}



	// controllo PHP
	$php_version = (substr(phpversion(),0,1)>='5') ? 1:0;

	if($php_version){

		$classe_php='install-ok';
	}
	else{
		$fatal=true;
		$classe_php='install-err';
	}

	echo "<p class=\"$classe_php\">"._("PHP Version").": <strong>".phpversion()."</strong><br />\n";
	echo "<small>"._("VFront requires PHP version &gt;= 5.x")."</small></p>\n";



	// ESTENSIONI PHP
	$ext=get_loaded_extensions();

	// Check Apache Modules
	if(function_exists('apache_get_modules')){

		$php_modules = (array) @apache_get_modules();
		$l_modrewrite=(in_array('mod_rewrite',$php_modules)) ? 1:0;
		$class_alert['modrewrite']= ($l_modrewrite) ? 'install-ok' : 'install-alert';
		$alert_rewrite_na='';
	}
	// caso CGI
	else{

		$php_modules= array();
		$l_modrewrite='na';
		$class_alert['modrewrite']= 'install-na';
		$alert_rewrite_na=_('This information is not available, since it seems to be using PHP as CGI');
	}

	$l_mysqli=(in_array('mysqli',$ext)) ? 1:0;
	$l_mysql_old=(in_array('mysql',$ext)) ? 1:0;
	$l_pgsql=(in_array('pgsql',$ext)) ? 1:0;

	if(file_exists("../inc/vmsql.oracle.php")){
		$l_oci8=(in_array('oci8',$ext)) ? 1:0;
	}
	else{
		$l_oci8=false;
	}

	$l_sqlite3 =(in_array('sqlite3', $ext)) ? 1:0;
	$l_sqlite2 =(in_array('SQLite', $ext)) ? 1:0;


	// Check MySQL
	if($l_mysqli || $l_mysql_old){

		$classe_mysqli='install-ok';
		$txt_mysqli='';

		// Alt Check for MySQL
		if(!$l_mysqli){
			$classe_mysqli='install-alert';
			$txt_mysqli=_('VFront uses the library MySQLi, but may optionally use the old library Mysql. Try to load the new library MySQLi');
		}
	}
	else{

		$classe_mysqli='install-alert';

	}



	// Check SQLite
	if($l_sqlite2 || $l_sqlite3){

		$l_sqlite=1;

		$classe_sqlite='install-ok';
		$txt_sqlite='';
		$version_sqlite='3.x';

		// Alt Check for SQLite
		if(!$l_sqlite3){
			$classe_sqlite='install-alert';
			$txt_sqlite=_('VFront uses the library SQLite3, but may optionally use the old library SQLite. If is possible, install the new library SQLite3');
			$version_sqlite='2.x';
		}
	}
	else{

		$l_sqlite=0;
		$classe_sqlite='install-alert';
		$version_sqlite='';
		$txt_sqlite=_("If you use this DB <b>the extension should be loaded</b>");

	}


	$classe_pgsql = ($l_pgsql) ? 'install-ok':'install-alert';

	$classe_oci8 = ($l_oci8) ? "install-ok" : 'install-alert';

	if(!$l_mysqli && !$l_mysql_old && !$l_pgsql && !$l_oci8 && !$l_sqlite){

		$fatal=true;
	}




	echo "<p class=\"$classe_mysqli\">"._("MySQL extension (MySQL Improved):")."<br />\n";
	echo "<small>"._("VFront uses the Mysqli library to connect to MySQL. If you use this DB the <b>extension should be loaded</b>")." ".$txt_mysqli."</small></p>\n";

	echo "<p class=\"$classe_pgsql\">"._("Postgres extension:")."<br />\n";
	echo "<small>"._("If you use this DB <b>the extension should be loaded</b>")."</small></p>\n";


	if(file_exists("../inc/vmsql.oracle.php")){
		echo "<p class=\"$classe_oci8\">"._("Oracle extension:")."<br />\n";
		echo "<small>"._("If you use this DB <b>the extension should be loaded</b>")."</small></p>\n";
	}

	echo "<p class=\"$classe_sqlite\">"._("SQlite extension:")." $version_sqlite<br />\n";
	echo "<small>".$txt_sqlite."</small></p>\n";


	$class_alert['gd']= (in_array('gd',$ext)) ? 'install-ok':'install-alert';

	echo "<p class=\"".$class_alert['gd']."\">"._("GD Library:")."<br />\n";
	echo "<small>"._("GD library is used by Vfront for the creation of statistics graphs")."</small></p>\n";


	echo "<p class=\"".$class_alert['modrewrite']."\">"._("Apache Module mod_rewrite:")."<br />\n";
	echo "<small>"._("VFront uses mod_rewite module")." $alert_rewrite_na</small></p>\n";


	/*$l_xsl=(in_array('xsl',$ext)) ? 1:0;

	echo "<span class=\"grigio\">"._("XSL transformation:")."</span> <strong>$l_xsl</strong>\n";
	echo "<div class=\"piccolo\">".sprintf(_("If the module is not loaded, go to the %s menu and cancel the server-side XSLT CONVERSION"),
									"<a href=\"variabili.php\">"._("variables")."</a>")
									."</div><br />\n";
	*/



// CARTELLE SCRIVIBILI O NO -------------------------------------



	###########################################
	#
	# OTHER HIDDEN TEST FOR THE CONFIGURATION
	#

	// test dir
	if(	is_writable('../files/tmp') &&
		is_writable('../files/html') &&
		is_writable('../files/db') &&
		is_writable('../files') &&
		is_writable('../files/docs') &&
		is_writable('../files/docsadmin')
	){
		// ok
		$class_alert['writable_files_dir']="install-ok";
		$txt_files_dir=_("Files folder writeable");
	}
	else{
		$class_alert['writable_files_dir']="install-err";
		$fatal=true;
		$txt_files_dir=sprintf(_('Please set the files folder as writeable and %s')
						,"<a href=\"".htmlentities($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8')."?p=1\">"._('try again')."</a>");
	}

	echo "<p class=\"".$class_alert['writable_files_dir']."\">".$txt_files_dir."<br />\n";
	echo "<small>"._("The files folder and its subfolders must be writable by the Apache user.")."</small></p>\n";



	if(in_array('install-alert',$class_alert)) $notice=true;








	// PRINT FEEDBACK

	if(!$fatal){

		// crea un file conf/.testvfront
		if($fp=@fopen("../conf/.testvfront",'w')){
			fwrite($fp, intval($notice),1);
			fclose($fp);
		}

		if($notice){

			echo "<p>"._("Some VFront configuration settings are not optimal. However these do not prevent installation, you can always adjust them later")."</a> </p>\n";
		}
		else{

			echo "<p>"._("All seems well configured")."</a> </p>\n";
		}

		echo "<p style=\"font-size:1.4em;\" > <a href=\"?p=2\">"._("Next step")."</a> </p>\n";

	}
	else{

		echo "<p>"._("There are some errors in configuration. These must be resolved to allow the installation to proceed")."</a> </p>\n";
	}



	echo "</body>\n</html>\n";
}






/**
 * @desc Imposta tutti i parametri del file di connessione che viene generato
 * @return string HTML
 */
function step2(){



	echo "<!DOCTYPE html PUBLIC \"-//W3C//DTD XHTML 1.0 Transitional//EN\" \"http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd\">
<html>
	<head><title>"._('VFront installation')."</title>
	<style type=\"text/css\">
		@import \"install.css\";
	</style>
	<script type=\"text/javascript\" src=\"../js/scriptaculous/lib/prototype.js\" ></script>
	<script type=\"text/javascript\" src=\"./install.js\" ></script>
	<script type=\"text/javascript\" >



	function ut(n){


		$('host').disabled= (n) ? true:false;
		$('user').disabled= (n) ? true:false;
		$('passw').disabled= (n) ? true:false;
		$('port').disabled= (n) ? true:false;

	}


	</script>
	</head>
	<body>\n";


	echo "<h1>"._("VFront installation")." - ".sprintf(_("Step %d of %d"),1,3)."</h1>\n";


	// Oracle Test
	if(file_exists("../inc/vmsql.oracle.php") && function_exists('oci_connect')){

		$add_oci_val="<option value=\"oracle\">Oracle</option>";
	}
	else{
		$add_oci_val='';
	}


	// Mysql test
	$option_mysql_dis=(function_exists('mysqli_query') || function_exists('mysql_query'))
					  ? '' : 'disabled="disabled"';

	// Postgres test
	$option_pgsql_dis=(function_exists('pg_query'))
						? '':'disabled="disabled"';

	// SQLite test
	$option_sqlite_dis=(class_exists('SQLite3') || class_exists('SQLiteDatabase'))
						? '':'disabled="disabled"';



	?>


<form method="post" action="<?php echo htmlentities($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8');?>?p=2s" id="installform">


	<a name="DBregistrymethod"></a>
	<div class="installbox" id="idbrules">

		<h2><?php echo _('VFront registry method');?></h2>

		<div class="method-radio">
			<input type="radio" name="var[regmethod]" value="internal" id="regmethod_internal" checked="checked" />
			<label for="regmethod_internal"><?php echo _('Create a rules DB for VFront registry');?></label>
			<span class="desc"><?php echo _('Classic method. This is the default method prior to version 0.96 and use a &quot;frontend&quot; schema (in Postgres) or another DB (in MySQL) for internal rules');?></span>
		</div>

		<div class="method-radio">
			<input type="radio" name="var[regmethod]" value="sqlite" id="regmethod_sqlite" <?php echo $option_sqlite_dis; ?>/>
			<label for="regmethod_sqlite"><?php echo _('Use SQLite for VFront registry');?></label>
			<span class="desc"><?php echo _('This is the alternative method since version 0.96. With this method VFront uses a database of internal rules, in a file .sqlite');?></span>
		</div>

	</div>


	<a name="DBconnection"></a>
	<div class="installbox" id="idb">

		<h2><?php echo _('DB connection');?></h2>

		<p>
			<label for="dbtype">DBType</label>
			<select id="dbtype" name="var[dbtype]" >
				<option value="mysql" selected="selected" <?php echo $option_mysql_dis;?>>MySQL</option>
				<option value="postgres" <?php echo $option_pgsql_dis;?>>PostgreSQL</option>
				<option value="sqlite" <?php echo $option_sqlite_dis;?>>SQLite</option>
				<?php echo $add_oci_val;?>
			</select>
			<img class="help-image" src="../img/info_small.gif" alt="help" onclick="$(this).next(0).toggle()" />
			<span class="desc" style="display:none"><?php echo _('Database type you want to use with VFront');?></span>
		</p>

		<div id="conn_mysql">


			<p>
				<label for="dbhost1">DB host</label>
				<input type="text" name="var[dbhost1]" id="dbhost1" value="localhost" />
				<img class="help-image" src="../img/info_small.gif" alt="help" onclick="$(this).next(0).toggle()" />
				<span class="desc" style="display:none" ><?php echo _('Database host (default: localhost)');?></span>
			</p>

			<p>
				<label for="dbport1">DB port</label>
				<input type="text" name="var[dbport1]" id="dbport1" value="3306" />
				<img class="help-image" src="../img/info_small.gif" alt="help" onclick="$(this).next(0).toggle()" />
				<span class="desc" style="display:none"><?php echo _('Database port (default: 3306)');?></span>
			</p>

			<p>
				<label for="dbname1">DB-data name</label>
				<input type="text" name="var[dbname1]" id="dbname1" value="" onblur="set_frontend_name(this.value)" />
				<img class="help-image" src="../img/info_small.gif" alt="help" onclick="$(this).next(0).toggle()" />
				<span class="desc" style="display:none"><?php echo _('Name of the existing database on which you want to use VFront');?></span>
			</p>

			<p class="internal-frontend">
				<label for="dbfrontend1">DB-frontend name</label>
				<input type="text" name="var[dbfrontend1]" id="dbfrontend1" value="" />
				<img class="help-image" src="../img/info_small.gif" alt="help" onclick="$(this).next(0).toggle()" />
				<span class="desc" style="display:none"><?php echo _('Name of the database to be created for the rules of VFront');?></span>
			</p>

			<p>
				<label for="dbuser1">DB user</label>
				<input type="text" name="var[dbuser1]" id="dbuser1" value="" />
				<img class="help-image" src="../img/info_small.gif" alt="help" onclick="$(this).next(0).toggle()" />
				<span class="desc" style="display:none"><?php echo _('Username to connect to database');?></span>
			</p>

			<p>
				<label for="dbpassw1">DB password</label>
				<input type="password" name="var[dbpassw1]" id="dbpassw1" value="" />
				<img class="help-image" src="../img/info_small.gif" alt="help" onclick="$(this).next(0).toggle()" />
				<span class="desc" style="display:none"><?php echo _('Password to connect to database');?></span>
			</p>



		</div>

		<div id="conn_postgres" style="display:none">
			<p>
				<label for="dbhost2">DB host</label>
				<input type="text" name="var[dbhost2]" id="dbhost2" value="localhost" />
				<img class="help-image" src="../img/info_small.gif" alt="help" onclick="$(this).next(0).toggle()" />
				<span class="desc" style="display:none"><?php echo _('Database host (default: localhost)');?></span>
			</p>

			<p>
				<label for="dbport2">DB port</label>
				<input type="text" name="var[dbport2]" id="dbport2" value="5432" />
				<img class="help-image" src="../img/info_small.gif" alt="help" onclick="$(this).next(0).toggle()" />
				<span class="desc" style="display:none"><?php echo _('Database port (default: 5432)');?></span>
			</p>

			<p>
				<label for="dbname2">DB name</label>
				<input type="text" name="var[dbname2]" id="dbname2" value="" />
				<img class="help-image" src="../img/info_small.gif" alt="help" onclick="$(this).next(0).toggle()" />
				<span class="desc" style="display:none"><?php echo _('Name of the existing database on which you want to use VFront');?></span>
			</p>

			<p>
				<label for="dbschema2">DB schema</label>
				<input type="text" name="var[dbschema2]" id="dbschema2" value="public" />
				<img class="help-image" src="../img/info_small.gif" alt="help" onclick="$(this).next(0).toggle()" />
				<span class="desc" style="display:none"><?php echo _('Name of the existing schema on which you want to use VFront');?></span>
			</p>

			<p class="internal-frontend">
				<label for="dbfrontend2">VFront Schema</label>
				<input type="text" name="var[dbfrontend2]" id="dbfrontend2" value="frontend" />
				<img class="help-image" src="../img/info_small.gif" alt="help" onclick="$(this).next(0).toggle()" />
				<span class="desc" style="display:none"><?php echo _('Name of the new schema for VFront rules');?></span>
			</p>

			<p>
				<label for="dbuser2">DB user</label>
				<input type="text" name="var[dbuser2]" id="dbuser2" value="" />
				<img class="help-image" src="../img/info_small.gif" alt="help" onclick="$(this).next(0).toggle()" />
				<span class="desc" style="display:none"><?php echo _('Username to connect to database');?></span>
			</p>

			<p>
				<label for="dbpassw2">DB password</label>
				<input type="password" name="var[dbpassw2]" id="dbpassw2" value="" />
				<img class="help-image" src="../img/info_small.gif" alt="help" onclick="$(this).next(0).toggle()" />
				<span class="desc" style="display:none"><?php echo _('Password to connect to database');?></span>
			</p>
		</div>


		<div id="conn_oracle" style="display:none">
			<p>
				<label for="dbhost3">DB host</label>
				<input type="text" name="var[dbhost3]" id="dbhost3" value="localhost" />
				<img class="help-image" src="../img/info_small.gif" alt="help" onclick="$(this).next(0).toggle()" />
				<span class="desc" style="display:none"><?php echo _('Database host (default: localhost)');?></span>
			</p>

			<p>
				<label for="dbport3">DB port</label>
				<input type="text" name="var[dbport3]" id="dbport3" value="" />
				<img class="help-image" src="../img/info_small.gif" alt="help" onclick="$(this).next(0).toggle()" />
				<span class="desc" style="display:none"><?php echo _('Database port');?></span>
			</p>

			<p>
				<label for="dbservice3">DB service</label>
				<input type="text" name="var[dbservice3]" id="dbservice3" value="" />
				<img class="help-image" src="../img/info_small.gif" alt="help" onclick="$(this).next(0).toggle()" />
				<span class="desc" style="display:none"><?php echo _('Name of the Oracle service on which you want to use VFront');?></span>
			</p>

			<p>
				<label for="dbuser3">DB user</label>
				<input type="text" name="var[dbuser3]" id="dbuser3" value="" />
				<img class="help-image" src="../img/info_small.gif" alt="help" onclick="$(this).next(0).toggle()" />
				<span class="desc" style="display:none"><?php echo _('Username to connect to database');?></span>
			</p>

			<p>
				<label for="dbpassw3">DB password</label>
				<input type="password" name="var[dbpassw3]" id="dbpassw3" value="" />
				<img class="help-image" src="../img/info_small.gif" alt="help" onclick="$(this).next(0).toggle()" />
				<span class="desc" style="display:none"><?php echo _('Password to connect to database');?></span>
			</p>

			<p class="internal-frontend">
				<label for="dbfrontend3">VFront table prefix</label>
				<input type="text" name="var[dbfrontend3]" id="dbfrontend3" value="VF_" />
				<img class="help-image" src="../img/info_small.gif" alt="help" onclick="$(this).next(0).toggle()" />
				<span class="desc" style="display:none"><?php echo _('Prefix for the VFront registry tables');?></span>
			</p>
		</div>



		<div id="conn_sqlite" style="display:none">
			<p>
				<label for="dbfilename4">SQLite file (*.db or *.sqlite) </label>
				<input type="text" name="var[dbfilename4]" id="dbfilename4" value="" size="70"/>
				<img class="help-image" src="../img/info_small.gif" alt="help" onclick="$(this).next(0).toggle()" />
				<span class="desc" style="display:none"><?php echo _('SQlite database');?></span>
			</p>

			
		</div>

		<p>
			<input	type="button" onclick="test_db_conn()" id="testdb" value="Test connection" /> <span id="testdb_feedback" >&nbsp;</span>
		</p>

	</div>  <!-- END idb -->
	<hr />




	<a name="Authentication"></a>
	<div class="installbox" id="iauth">

		<h2><?php echo _('Authentication');?></h2>

		<div id="authtype_box">

			<p>
				<label for="dbhost1"><?php echo _('Authentication method');?></label>
				<select id="authtype" name="var[authtype]" onchange="show_auth_div(this.value); show_ajax_help(this.value);">
					<option value="null" selected="selected">VFront Internal</option>
					<option value="db">Data DB (on same server)</option>
					<option value="db_ext">External DB</option>
					<option value="ldap">LDAP/Active Directory</option>
					<!--<option value="soap">Soap</option>
					<option value="OpenID">OpenID</option>-->
				</select>
				<img class="help-image" src="../img/info_small.gif" alt="help" onclick="$(this).next(0).toggle()" />
				<span class="desc" style="display:none"><?php echo _('For user management and authentication, VFront can use your database, or use external data sources such as another database or an LDAP server');?></span>
				<span id="auth_null">&nbsp;</span>
			</p>

		</div>



		<div id="auth_ext_common" class="auth-box" style="display:none;">


			<div id="auth_db" class="auth-box" style="display:none;">
				<p>
					<label for="authdb_dbname">Db name (on same server!)</label>
					<span id="authdb_dbname_cont"><input type="text" name="var[authdb_dbname]" id="authdb_dbname" value="" /></span>
					<span id="authdb_dbname_wait" style="display:none"><img src="../img/refresh1.gif" alt="wait" /></span>
				</p>

				<p>
					<label for="authdb_usertable">User table name</label>
					<span id="authdb_usertable_cont"><input type="text" name="var[authdb_usertable]" id="authdb_usertable" value="" /></span>
					<span id="authdb_usertable_wait" style="display:none"><img src="../img/refresh1.gif" alt="wait" /></span>
				</p>


			</div>

			<div id="auth_db_ext" class="auth-box" style="display:none;">

				<p>
					<label for="authdb_ext_type">External DB type</label>
					<select name="var[authdb_ext_type]" id="authdb_ext_type">
						<option value="mysql">MySQL</option>
						<option value="postgres">PostgreSQL</option>
						<option value="odbc">ODBC</option>
					</select>
				</p>

				<p>
					<label for="authdb_ext_host">External DB Host</label>
					<input type="text" name="var[authdb_ext_host]" id="authdb_ext_host" value="" />
				</p>

				<p>
					<label for="authdb_ext_port">External DB Port</label>
					<input type="text" name="var[authdb_ext_port]" id="authdb_ext_port" value="" />
				</p>

				<p>
					<label for="authdb_ext_user">External DB User</label>
					<input type="text" name="var[authdb_ext_user]" id="authdb_ext_user" value="" />
				</p>

				<p>
					<label for="authdb_ext_passwd">External DB Password</label>
					<input type="password" name="var[authdb_ext_passwd]" id="authdb_ext_passwd" value="" />
				</p>

				<p>
					<label for="authdb_ext_dbname">External DB Name</label>
					<input type="text" name="var[authdb_ext_dbname]" id="authdb_ext_dbname" value="" />
				</p>

				<p>
					<label for="authdb_ext_table">External DB Table</label>
					<input type="text" name="var[authdb_ext_table]" id="authdb_ext_table" value="" />
				</p>

				<p>
					<label for="authdb_ext_odbcdsn">ODBC DSN (only ODBC)</label>
					<input type="text" name="var[authdb_ext_odbcdsn]" id="authdb_ext_odbcdsn" value="" />
				</p>

			</div>


			<?php

			#############################
			#
			#	AUTH LDAP

			?>

			<div id="auth_ldap" class="auth-box" style="display:none;">
				<p>
					<label for="ldap_host">LDAP server</label>
					<input type="text" name="var[ldap_host]" id="ldap_host" value="" />
					<img class="help-image" src="../img/info_small.gif" alt="help" onclick="$(this).next(0).toggle()" />
					<span class="desc" style="display:none"><?php echo _('The domain name or IP address of your LDAP Server. For example: localhost');?></span>
				</p>

				<p>
					<label for="ldap_port">LDAP port</label>
					<input type="text" name="var[ldap_port]" id="ldap_port" value="389" />
					<img class="help-image" src="../img/info_small.gif" alt="help" onclick="$(this).next(0).toggle()" />
					<span class="desc" style="display:none"><?php echo _('Default').": 389";?></span>
				</p>

				<p>
					<label for="ldap_basedn">LDAP base dn</label>
					<input type="text" name="var[ldap_basedn]" id="ldap_basedn" value="" />


				</p>

				<p>
					<label for="ldap_anonymus_bind">LDAP Anonymus bind</label>
					<input type="checkbox" id="ldap_anonymus_bind" value="1" name="var[ldap_anonymus_bind]" onclick="anon_bind()" />
				</p>


				<p>
					<label for="ldap_bind_user">LDAP User for bind</label>
					<input type="text" id="ldap_bind_user" value="" name="var[ldap_bind_user]" />
				</p>

				<p>
					<label for="ldap_bind_passw">LDAP Password for bind</label>
					<input type="password" id="ldap_bind_passw" value="" name="var[ldap_bind_passw]" />
				</p>



			</div>

			<!--<div id="auth_soap" class="auth-box" style="display:none;">
				auth_soap
			</div>

			<div id="auth_OpenID" class="auth-box" style="display:none;">
				auth_OpenID
			</div>-->

			<p id="authext_nick_p">
				<label for="authext_nick"><?php echo _('User nickname field (or email)');?></label>
				<span id="authext_nick_cont"><input type="text" name="var[authext_nick]" id="authext_nick" value="" /></span>
				<span id="authext_nick_wait" style="display:none"><img src="../img/refresh1.gif" alt="wait" /></span>
			</p>

			<p id="authext_passwd_p">
				<label for="authext_passwd"><?php echo _('User password field');?></label>
				<span id="authext_passwd_cont"><input type="text" name="var[authext_passwd]" id="authext_passwd" value="" /></span>
				<span id="authext_passwd_wait" style="display:none"><img src="../img/refresh1.gif" alt="wait" /></span>
			</p>

			<p id="authext_passwd_encode_p">
				<label for="authext_passwd_encode"><?php echo _('Password Type');?></label>
				<select name="var[authext_passwd_encode]" id="authext_passwd_encode">
					<option value="" selected="selected">none</option>
					<option value="md5">md5</option>
					<option value="sha1">sha1</option>
				</select>
			</p>

			<p id="authext_mail_p">
				<label for="authext_mail"><?php echo _('User email Field (optional)');?></label>
				<span id="authext_mail_cont"><input type="text" name="var[authext_mail]" id="authext_mail" value="" /></span>
				<span id="authext_mail_wait" style="display:none"><img src="../img/refresh1.gif" alt="wait" /></span>
			</p>


			<p id="authext_name_p">
				<label for="authext_name"><?php echo _('User name Field (optional)');?></label>
				<span id="authext_name_cont"><input type="text" name="var[authext_name]" id="authext_name" value="" /></span>
				<span id="authext_name_wait" style="display:none"><img src="../img/refresh1.gif" alt="wait" /></span>
			</p>


			<p id="authext_surname_p">
				<label for="authext_surname"><?php echo _('User surname Field (optional)');?></label>
				<span id="authext_surname_cont"><input type="text" name="var[authext_surname]" id="authext_surname" value="" /></span>
				<span id="authext_surname_wait" style="display:none"><img src="../img/refresh1.gif" alt="wait" /></span>
			</p>

			<p>
				<input type="button" onclick="test_ext()" id="testext" value="<?php echo _('Test connection');?>" /> <span id="testext_feedback" >&nbsp;</span>
			</p>

		</div>





	</div> <!-- END iauth -->

	<hr />


	<div class="installbox" id="imail">

		<h2>Mail &amp; SMTP</h2>

		<p>
			<label for="mail_sysamin">Administrator email</label>
			<input type="text" name="var[mail_sysamin]" id="mail_sysamin" value="<?php echo $_SERVER['SERVER_ADMIN'];?>" />
		</p>


		<p>
			<label for="mail_dev">Developer email (optional)</label>
			<input type="text" name="var[mail_dev]" id="mail_dev" value="" />
		</p>

		<p>
			<label for="smtp_sender">Default sender</label>
			<input type="text" name="var[smtp_sender]" id="smtp_sender" value="<?php echo $_SERVER['SERVER_ADMIN'];?>" />
		</p>

			<p>
			<label for="smtp_sendername">Default sender displayed name</label>
			<input type="text" name="var[smtp_sendername]" id="smtp_sendername" value="VFront admin" />
		</p>


		<p>
			<label for="smtp_use">Use SMTP</label>
			<select name="var[smtp_use]" id="smtp_use" onchange="display_smtp()" >
				<option value="false">No</option>
				<option value="true">Yes</option>
			</select>
		</p>

		<div id="use_smtp" style="display:none" >

			<p>
				<label for="smtp_address">SMTP address</label>
				<input type="text" name="var[smtp_address]" id="smtp_address" value="" />
			</p>

			<p>
				<label for="smtp_user">SMTP user</label>
				<input type="text" name="var[smtp_user]" id="smtp_user" value="" />
			</p>

			<p>
				<label for="smtp_passwd">SMTP password</label>
				<input type="password" name="var[smtp_passwd]" id="smtp_passwd" value="" />
			</p>

		</div>

	</div>
	<!-- END idebug -->

	<hr />

	<div class="installbox" id="idebug">
		<h2>Debug</h2>

			<p>
				<label for="debug_sql">Debug SQL</label>
				<select name="var[debug_sql]" id="debug_sql">
					<option value="false" selected="selected">false</option>
					<option value="true">true</option>
				</select>
			</p>
			<!--
			<p>
				<label for="rpc_debug">RPC Debug</label>
				<select name="var[rpc_debug]" id="rpc_debug">
					<option value="false" selected="selected">false</option>
					<option value="true">true</option>
				</select>
			</p>
			-->


	</div> <!-- END idebug -->



	<hr />

	<div class="installbox" id="ilang">

		<h2><?php echo _('Language and encoding');?></h2>

		<p>
			<label for="lang"><?php echo _('Language');?></label>
			<select name="var[lang]" id="lang">
			<?php

			if ($handle = opendir('../locale')) {
			   while (false !== ($dirr = readdir($handle))) {
			   		if(preg_match("|[a-z]{2}_[a-z]{2}|i",$dirr)){

			   			$sel = ($dirr=='en_US') ? " selected=\"selected\"" : "";

			   		    echo "<option value=\"$dirr\"$sel>$dirr</option>\n";
			   		}
			   }

			   closedir($handle);
			}

			?>
			</select>
		</p>

		<p>
			<label for="encoding"><?php echo _('Encoding');?></label>
			<select name="var[encoding]" id="encoding">
				<option value="UTF-8" selected="selected">UTF-8</option>
				<option value="iso-8859-1">iso-8859-1</option>
			</select>
		</p>

	</div> <!-- END ilang -->


	<hr />

	<div class="installbox" id="idatetime">
		<h2><?php echo _('Date and time');?></h2>

			<p>
				<label for="dateformat"><?php echo _('Date and time format');?></label>
				<select name="var[dateformat]" id="dateformat">
					<option value="iso" selected="selected">ISO (ISO 8601), ex: 2009-01-15</option>
					<option value="ita">Latin/Europe (Italy, German, France, etc.), ex: 15/01/2009</option>
					<option value="eng">USA, ex: 01/15/2009</option>
				</select>
			</p>

	</div> <!-- END idatetime -->

	<hr />

	<div class="installbox" id="ipath">

		<h2><?php echo _('Paths');?></h2>

		<?php

			// Linux or windows ?
			$front_root0=  (substr(realpath(__FILE__),0,1)=='/')  ? __FILE__ : str_replace("\\","/",__FILE__) ;
			$front_root= str_replace("/_install/index.php","",$front_root0);

			$doc_root="http://".$_SERVER['HTTP_HOST'].str_replace("/_install/index.php",'',$_SERVER['PHP_SELF']);

		?>

			<p>
				<label for="front_root">VFront root (realpath)</label>
				<input type="text" name="var[front_root]" id="front_root" value="<?php echo $front_root;?>" size="50" />
			</p>

			<p>
				<label for="document_root">VFront document root (http...)</label>
				<input type="text" name="var[document_root]" id="document_root" value="<?php echo $doc_root;?>" size="50" />
			</p>

			<p id="sqlite-path" style="display:none;">
				<label for="sqlite_path">VFront SQLite path</label>
				<input type="text" name="var[sqlite_path]" id="sqlite_path" value="<?php echo $front_root."/files/db/vfront.sqlite";?>" size="50" />
			</p>

	</div>	<!-- END ipath -->

	<hr />

	<?php


		// FOP settings:

		if(strtoupper(substr(PHP_OS, 0, 3)) === 'WIN'){
			$FOP_EXEC='fop.bat';
		}
		else{
			$FOP_EXEC='fop';
		}

		// passphrase
		$passph=implode("",array_rand(array_flip(range("a","z")),8));


	?>

	<div class="installbox" id="ifop">
		<h2>Apache FOP</h2>

			<p>
				<label for="fop_enabled">FOP Enabled</label>
				<select name="var[fop_enabled]" id="fop_enabled">
					<option value="false" selected="selected">false</option>
					<option value="true" >true</option>
				</select>
			</p>

			<p>
				<label for="path_fop">path fop</label>
				<input type="text" name="var[path_fop]" id="path_fop" value="<?php // echo $front_root."/plugins/fop/".$FOP_EXEC;?>" size="50" />
			</p>
	</div> <!-- END ifop -->

	<hr />

	<div class="installbox" id="imisc">
	<h2><?php echo _('Miscellaneous');?></h2>

		<p>
			<label for="max_tempo_edit">Max tempo edit (default: 240 seconds)</label>
			<input type="text" name="var[max_tempo_edit]" id="max_tempo_edit" value="240" size="50" />
		</p>

		<p>
			<label for="passfrase">Passphrase for base64</label>
			<input type="text" name="var[passfrase]" id="passfrase" value="<?php echo $passph;?>" size="50" />
		</p>

		<p>
			<label for="name_proj">Name of project</label>
			<input type="text" name="var[name_proj]" id="name_proj" value="VFront" />
		</p>

	</div> <!-- END imisc -->

	<p>
		<input type="hidden" name="file_connessione" value="" id="file_connessione" />
		<input type="button" value="&lt;&lt; <?php echo _('Previous');?> " onclick="window.location='<?php echo htmlentities($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8');?>'" id="back_button" />
		<input type="button" value="  <?php echo _('Next');?>  &gt;&gt;" onclick="check_installer()" id="submit_button" /> <span id="check_feed" >&nbsp;</span>
	</p>





</form>

	<?php

	echo "</body>\n</html>";


	exit;

}



function step2s(){

		// Create Conf FILE
	if(isset($_POST['file_connessione'])){

		$test_include=true;
		require_once("./create_conf.php");

		// If the registry is SQLITE based, go to 3s
		if($var['regmethod']=='sqlite' || $var['dbtype']=='sqlite'){
			header("Location: ".$_SERVER['PHP_SELF']."?p=3s");
		}
		// else go to DB creation
		else{
			header("Location: ".$_SERVER['PHP_SELF']."?p=3");
		}
		exit;

	}
	else{

		header("Location: ".$_SERVER['PHP_SELF']."?p=2&error_in=2");
		exit;
	}
}







/**
 * @desc Definisci la connessione per generare il DB ed eventualmente crea il DB nel prossimo passo
 * @return string HTML
 */
function step3(){

	include_once("../conf/conf.vfront.php");
	include_once("./vfront.{$db1['dbtype']}.sql.php");
	include_once("../plugins/highlight/highlight_sql.php");



	echo "<!DOCTYPE html PUBLIC \"-//W3C//DTD XHTML 1.0 Transitional//EN\" \"http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd\">
<html>
	<head><title>"._('VFront installation')."</title>
	<style type=\"text/css\">
		@import \"install.css\";
	</style>

	<script type=\"text/javascript\" src=\"../js/scriptaculous/lib/prototype.js\"></script>
	<script type=\"text/javascript\">

	function ut(n){
		$('host').disabled= (n) ? true:false;
		$('user').disabled= (n) ? true:false;
		$('passw').disabled= (n) ? true:false;
		$('port').disabled= (n) ? true:false;
	}

	</script>

	</head>
	<body>\n";

	echo "<h1>"._("VFront installation")." - "._("Step 2 of 3")."</h1>\n";

	// errori di connessione:

	if(isset($_GET['err'])){

		switch ($_GET['err']){

			case 1: echo "<span style=\"color:red\"><b>"._("Warning! Procedure interrupted ")."</b>. "._("Open a connection for installation")."</span>\n";
			break;

			case 2: echo "<span style=\"color:red\">"._("Could not create tables with privileges for the following user")."</span>\n";
			break;
		}
	}





	echo "<div id=\"createdb\" >\n";
	echo "<h2>"._("VFront DB install")."</h2>\n";

	if($db1['dbtype']=='mysql' || $db1['dbtype']=='postgres'){


		echo "<p>
		"._("Tables will be inserted in the database specified in the CONF file")." <strong>".$db1['frontend']."</strong><br />
		"._("Will try to create it if it does not exist (privileges required).")."
		</p>

		<form action=\"".htmlentities($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8')."?p=3s\" method=\"post\" >\n";

		$PORTA = ($db1['dbtype']=='mysql') ? "3306" : "5432";

		$UT_DEFAULT =($db1['dbtype']=='mysql') ? "root" : "postgres";


		// Mostra il box per la connessione del superutente se non e' root|postgres
		if($UT_DEFAULT!=$db1['user']){

			echo "
				<input type=\"hidden\" name=\"tipo_link\" id=\"tl1\" value=\"1\" />

			<p>
				"._("host").": <input type=\"text\" name=\"host\" id=\"host\" value=\"localhost\"  /><br />
				"._("user").": <input type=\"text\" name=\"user\" id=\"user\" value=\"$UT_DEFAULT\" /><br />
				"._("password").": <input type=\"password\" name=\"passw\" id=\"passw\" value=\"\"  /><br />
				"._("port").": <input type=\"text\" name=\"port\" id=\"port\" value=\"$PORTA\" /><br />
			</p>

			";


			echo "
			<p>
				<input type=\"radio\" name=\"grant_user\" id=\"g0\" value=\"1\"  checked=\"checked\" /> "._("Assign permissions to user")." <b>".$db1['user']."</b> "._("on this DB (GRANT option required)")." <br />
				<input type=\"radio\" name=\"grant_user\" id=\"g1\" value=\"0\"  /> "._("Set privileges manually")."<br />
			</p>
			";
		}
		else{

			// manda un segnale per usare i dati della connessione
			echo "<input type=\"hidden\" name=\"user\" id=\"user\" value=\"USER_DEFAULT_CONF\" />\n";
		}






		if($UT_DEFAULT!=$db1['user']){

			echo "
			<p>"._("If you do not have sufficient rights to perform an unattended installation,<br />if you want to install manually <br />or you simply want to see what the installation on your server is going to do,")."<br />
			 "._("open")." <span class=\"fakelink\" onclick=\"$('boxsql').toggle();\">"._("this box")."</span></p>\n";

		}
		else{

			echo "
		<p>"._("If you wish to install manually <br /> or simply want to see what the installation on your server is going to do,")."<br />
		 "._("open")." <span class=\"fakelink\" onclick=\"$('boxsql').toggle();\">"._("this box")."</span></p>\n";


		}

	}

	else if ($db1['dbtype']=='oracle'){


		echo "<p>
		"._("Tables will be inserted in the database specified in the CONF file")." <strong>".$db1['frontend']."</strong><br />
		"._("Will try to create it if it does not exist (privileges required).")."
		</p>";


		echo "<p>"._("If you wish to install manually <br /> or simply want to see what the installation on your server is going to do,")."<br />
		 "._("open")." <span class=\"fakelink\" onclick=\"$('boxsql').toggle();\">"._("this box")."</span></p>\n";


		echo "<form action=\"".htmlentities($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8')."?install_db\" method=\"post\" >\n";
	}

	echo "

	<p>
		<input type=\"submit\" name=\"Invia\" value=\" "._("Proceed")." \" />
		<input type=\"button\" name=\"esci\" value=\" "._("Exit installation")." \" onclick=\"location.href='?esci'\"/>
	</p>

	</form>\n";


	echo "<div id=\"boxsql\" style=\"display:none\">\n";
	echo "<code class=\"sql\">\n";

	$create_db = ($db1['dbtype']=='mysql') ? "CREATE DATABASE ".$db1['frontend'].";\n\n" : '';

	echo syntax_highlight(nl2br($create_db.implode(";\n",$SQL_DEFINITION)),"SQL");
	echo "</code>\n";
	echo "</div>\n";





	echo "
	</div>

	</body>
	</html>";


}




/**
 * @desc Step 3 on server
 * Copy the SQLite file or create the vfront schema using SQL templates
 * @return void
 */
function step3s(){

	require_once("../conf/conf.vfront.php");

	$GLOBALS['db1']=$db1;

	if(defined('USE_REG_SQLITE') && USE_REG_SQLITE){

		$feed_op=copy_sqlite();
		$ty='sqlite';

	}
	else{

		$feed_op=crea_db_rules();
		$ty='dbrules';
	}



	if($feed_op)
		header("Location: ".$_SERVER['PHP_SELF']."?p=4");
	else
		header("Location: ".$_SERVER['PHP_SELF']."?p=panic&errty=copy_$ty");

	exit;
	
}


/**
 * @desc Copy the SQLite file in files/db
 * @global array $db1
 * @return bool
 */
function copy_sqlite(){

	global $db1;

	$v = (class_exists('SQLite3')) ? 3:2;

	$test_copy=copy(FRONT_ROOT."/_install/sqlite/vfront{$v}.dist.sqlite", $db1['filename_reg']);

	return $test_copy;
}



/**
 * @desc Crea il DB e le tabelle
 *
 */
function crea_db_rules(){

	global $db1;

	$GLOBALS['DEBUG_SQL']=true;


	require_vmsql($db1['dbtype']);

	if($db1['dbtype']=='oracle' || (isset($_POST['user']) && $_POST['user']==='USER_DEFAULT_CONF')){


		$vmsql = new vmsql();
		$test_conn = $vmsql->connect($db1);

		if(!$test_conn){
			header("Location: ".$_SERVER['PHP_SELF']."?p=panic&err=2&errty=no_db_conn");
			exit;
		}

	}
	else{

		if(!isset($db1['postgres_dbname'])){
			$db1['postgres_dbname']='';
		}

		$db2=array("host"=>$_POST['host'],
					"user"=>$_POST['user'],
					"passw"=>$_POST['passw'],
					"dbname"=>$db1['dbname'],
					"port"=>$_POST['port'],
					"postgres_dbname"=>$db1['postgres_dbname'],
					"dbtype"=>$db1['dbtype'],
					"frontend"=>$db1['frontend'],
					"sep"=>$db1['sep']
					);

		$vmsql = new vmsql();
		$test_conn2=$vmsql->connect($db2);

		if(!$test_conn2){

			header("Location: ".$_SERVER['PHP_SELF']."?p=panic&err=2&errty=no_db_conn");
			exit;
		}

	}


	if($db1['dbtype']=='mysql'){

		if(!is_file("./vfront.mysql.sql.php")){

			die(_("Could not read SQL data from original file. Procedure interrupted ."));
		}

		require_once("./vfront.mysql.sql.php");
	}
	else if($db1['dbtype']=='postgres'){

		if(!is_file("./vfront.postgres.sql.php")){

			die(_("Could not read SQL data from original file. Procedure interrupted ."));
		}

		require_once("./vfront.postgres.sql.php");
	}
	else if($db1['dbtype']=='oracle'){

		if(!is_file("./vfront.oracle.sql.php")){

			die(_("Could not read SQL data from original file. Procedure interrupted ."));
		}

		require_once("./vfront.oracle.sql.php");
	}

	// crea il database
	if($db1['dbtype']=='mysql'){
		$sql_creadb=  "CREATE DATABASE IF NOT EXISTS ".$db1['frontend'];

		$q0=@$vmsql->query($sql_creadb) or die("Unable to create the database {$db1['frontend']}");

	}
	elseif($db1['dbtype']=='postgres'){

		// Null
	}


	$errore=0;

	for($i=0;$i<count($SQL_DEFINITION);$i++){
		$q_creatabelle= $vmsql->query($SQL_DEFINITION[$i]);

		if(!$q_creatabelle){
			$vmsql->query("ROLLBACK");
			die(sprintf("$xerr<br /> "._('Unable to create tables in database %s'),$db1['frontend']));
		}

	}

	if($db1['dbtype']=='mysql'){
		//$q_use0=$vmsql->query("USE ".$db1['dbname']) or die(_sprintf(_("Cannot use database %s with command USE",$db1['dbname'])));
	}

	if(isset($_POST['grant_user']) && $_POST['grant_user']==1){

		$esito_grant=grant(false,$vmsql,$db1);
	}

	return true;
}






/**
 * @desc Funzione per la generazione della pagina di creazione primo utente
 *
 */
function step4(){

	global  $vmsql, $vmreg, $db1, $conf_auth;


	echo "<!DOCTYPE html PUBLIC \"-//W3C//DTD XHTML 1.0 Transitional//EN\" \"http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd\">
<html>
	<head><title>"._("VFront installation")."</title>
	<style type=\"text/css\">
		@import \"install.css\";
	</style>
	<script type=\"text/javascript\" src=\"../js/yav/yav.js\" ></script>
	<script type=\"text/javascript\" src=\"../js/yav/yav-config-it.js\" ></script>
	<script type=\"text/javascript\" >

	var rules= new Array();

	rules[0]='email|required|"._("Email required")."';
	rules[1]='passw1|minlength|6|"._("Password must be at least 6 characters")."';
	rules[2]='passw1|equal|\$passw2|".addslashes(_("The two password don't match"))."';
	rules[3]='nome|required|"._("Name required")."';
	rules[4]='cognome|required|"._("Family name required")."';

	</script>
	</head>

	<body>\n";

	echo "<h1>"._("VFront installation")." - "._("Step 3 of 3")."</h1>\n";



	// analizza se il login richiesto anche esternamente

	if($conf_auth['tipo_external_auth']!=''){

		echo "<p>"._("Warning! Authentication is required through an external database0")." ("
			 .$conf_auth['tipo_external_auth']."). "."<br />
			 ".sprintf(_("The first user access will be as administrator, then use your normal account for your first login."),$conf_auth['tipo_external_auth'])."</p>";

		echo "<p><a href=\"?p=5\">"._("Next step")."</a></p>\n";
	}

	else{

		echo "<form action=\"?p=4s\" method=\"post\" name=\"f1\" onsubmit=\"return performCheck('f1', rules,'classic');\">

		<div class=\"installbox\" id=\"iadmin\">

		<h2>"._("Creation of admin user")."</h2>

		<p>"._("Now insert admin user data")."</p>

			<p>
				<label for=\"email\">"._("Email").":</label><br />
				<input type=\"text\" size=\"42\" value=\"\" name=\"email\" id=\"email\" />
			</p>

			<p>
				<label for=\"passw1\">"._("Password").":</label><br />
				<input type=\"password\" size=\"42\" value=\"\" name=\"passw1\" id=\"passw1\" />
			</p>

			<p>
				<label for=\"passw2\">"._("Re-enter the password").":</label><br />
				<input type=\"password\" size=\"42\" value=\"\" name=\"passw2\" id=\"passw2\" />
			</p>

			<p>
				<label for=\"nome\">"._("Name").":</label><br />
				<input type=\"text\" size=\"42\" value=\"\" name=\"nome\" id=\"nome\" />
			</p>

			<p>
				<label for=\"cognome\">"._("Surname").":</label><br />
				<input type=\"text\" size=\"42\" value=\"\" name=\"cognome\" id=\"cognome\" />
			</p>

		</div>


			<p>
				<input type=\"submit\" value=\" "._("Save data")."\" name=\"invia\"  />
			</p>

		</form>\n";

	}

	echo "</body>\n</html>\n";

	exit;

}







/**
 * @desc Funzione di creazione primo utente
 *
 */
function step4s(){

	require_once("../conf/conf.vfront.php");

	$GLOBALS['db1']=$db1;
	$GLOBALS['DEBUG_SQL']=true;


	if(USE_REG_SQLITE){

		if(VERSION_REG_SQLITE==3){
			require_once("../inc/vmsql.sqlite3.php");
			$vmreg = new sqlite3_vmsql();
		}
		else{
			require_once("../inc/vmsql.sqlite2.php");
			$vmreg = new sqlite2_vmsql();
		}

		
		$test_conn =$vmreg->connect($db1['filename_reg']);

		if(!$test_conn){
			header("Location: ".$_SERVER['PHP_SELF']."?p=panic&errty=no_insert_admin_sqlite");
			exit;
		}

	}
	else{

		if(isset($db1['dbsqlite_version'])){
			$db_file_version=$db1['dbsqlite_version'];
		}
		else{
			$db_file_version='';
		}

		require_vmsql($db1['dbtype'],$db_file_version);

		$vmreg = new vmsql();
		$test_conn =$vmreg->connect($db1);

	}

	$_dati=$vmreg->recursive_escape($_POST);

	$sql=sprintf("INSERT INTO {$db1['frontend']}{$db1['sep']}utente
				(nick, passwd, email, livello, gid, data_ins, nome, cognome) VALUES
  				('%s', '%s','%s', %d, %d, '%s', '%s', '%s')",
				$_dati['email'],
				md5($_dati['passw1']),
				$_dati['email'],
				3,
				0,
				date("Y-m-d"),
				$_dati['nome'],
				$_dati['cognome']
				);

	$q=$vmreg->query($sql);

	if($vmreg->affected_rows($q)==1){

		// set Oracle theme?
		if(file_exists("../inc/vmsql.oracle.php") && $db1['dbtype']=='oracle'){

			$sql2="UPDATE variabili SET valore='oracle_edition' WHERE variabile='layout'";
			$q=$vmreg->query($sql2);
		}


		header("Location: ".$_SERVER['PHP_SELF']."?p=5");
		exit;
	}
	else{
		header("Location: ".$_SERVER['PHP_SELF']."?p=panic&errty=no_user_admin");
		exit;
	}

}





















/**
 * @desc Genera il codice HTML da mostrare a fine installazione
 * @param bool $esito
 * @return string HTML
 */
function step5($esito=true){

	require_once("../conf/conf.vfront.php");
	require_once("../inc/func.ppal.php");

	echo "<!DOCTYPE html PUBLIC \"-//W3C//DTD XHTML 1.0 Transitional//EN\" \"http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd\">
<html>
	<head><title>"._('VFront installation')."</title>
	<style type=\"text/css\">
		@import \"install.css\";
	</style>
	<script type=\"text/javascript\" src=\"../js/scriptaculous/lib/prototype.js\" ></script>
	</head>
	<body>\n";

	if($esito){

		// prendi info:


		$INFO_TXT="";
		$INFO_POST='';

		$auth_type= ($conf_auth['tipo_external_auth']=='') ? 'vfront': $conf_auth['tipo_external_auth'].".".$conf_auth['db_ext']['dbtype'];

		/*$file_version
		$version=*/

		$reg_method=(USE_REG_SQLITE) ? "sqlite":"internal";

		$INFO_TXT.="Date: ".date("Y-m-d H:i")."<br />\n";
		$INFO_TXT.="OS: ".PHP_OS."<br />\n";
		$INFO_TXT.="DB Type: ".$db1['dbtype']."<br />\n";
		$INFO_TXT.="Auth Type: ".$auth_type."<br />\n";
		$INFO_TXT.="Language: ".FRONT_LANG."<br />\n";
		$INFO_TXT.="Encoding: ".FRONT_ENCODING."<br />\n";
		$INFO_TXT.="VFront Registry Method: ".$reg_method."<br />\n";
		$INFO_TXT.="PHP Version: ".phpversion()."<br />\n";

		$INFO_POST.="os=".PHP_OS."&amp;";
		$INFO_POST.="d=".date("YmdHis")."&amp;";
		$INFO_POST.="db=".$db1['dbtype']."&amp;";
		$INFO_POST.="auth=".$auth_type."&amp;";
		$INFO_POST.="l=".FRONT_LANG."&amp;";
		$INFO_POST.="enc=".FRONT_ENCODING."&amp;";
		$INFO_POST.="meth=".$reg_method."&amp;";
		$INFO_POST.="v=".phpversion();


		$START_dev=1187301600;
		$now_dev=time()-$START_dev;
		$gg_dev=round($now_dev/(3600*24));


		echo "<h1>"._("Installation complete!")."</h1>

		<div id=\"ifeedback\">
			<h2>"._("Please send us feedback!")."</h2>
			<div id=\"pleasesend\">
				<p>"._('Please help us to know the usage of VFront in the world :-)')."<br />
				"._('You can send information by clicking on the link below.')."</p>

				<div id=\"sendinfo\" ><code><strong>Info to send:</strong><br />------------------<br />$INFO_TXT</code></div>

				<p><span class=\"fakelink\" onclick=\"window.open('http://www.vfront.org/getinfo.php?".addslashes($INFO_POST)."','infovfront','width=400,height=350,toolbar=no, location=no,status=no,menubar=no,scrollbars=yes,resizable=yes'); $('pleasesend').hide();$('thankyou').show();\">"._("Send info!")."</span></p>
			</div>

			<div id=\"thankyou\" style=\"display:none\">

				<p>"._('Thank you for the feedback!')."</p>
			</div>

		</div>


		<div id=\"isupport\">
			<h2>"._("Support us!")."</h2>
			<div id=\"isupport2\">
				<p><strong>"._('The numbers behind VFront').":</strong></p>
				<ul id=\"isupport-ul\">
					<li>".sprintf(_('%s code lines'),'~30.000')."</li>
					<li>".sprintf(_('%d days of development and maintenance'),$gg_dev)."</li>
					<li>".sprintf(_('%s pages of manuals and documentation'),'~200')."</li>
					<li>".sprintf(_('%d cups of coffee'),$gg_dev*3)."</li>
				</ul>

				<p>"._('Do you want to offer me some coffee?')."</p>

			</div>

			";



		echo "<div id=\"support_form\" >".ppal()."</div>";

		echo "
		</div>


		<div id=\"ilogin\">
			<h2>"._("Go to login")."</h2>
			<p>"._("Go to login page and enter your email and password you previously specfied.")."</p>
			<p><a href=\"../index.php\">"._('Go to login')."</a></p>
		</div>\n";

	}
	else{

		echo "<h1>"._("Error creating user!")."</h1>
		<p>"._("Try to create the user in the table <em>utente</em> manually, specifying level = 3 and group = 0")."</p>
		<p><a href=\"../index.php\">"._('Go to login')."</a></p>\n";

	}

	echo "</body>\n</html>\n";
}










/**
 * @desc Genera il codice HTML da mostrare a inizio installazione
 * @return string HTML
 */
function panic_page(){

	if(file_exists("../conf/conf.vfront.php")){
		require_once("../conf/conf.vfront.php");
	}

	$OUT= "<!DOCTYPE html PUBLIC \"-//W3C//DTD XHTML 1.0 Transitional//EN\" \"http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd\">
<html>
	<head><title>VFront Installer</title>
	<style type=\"text/css\">
		@import \"install.css\";
	</style>
	<script type=\"text/javascript\" src=\"../js/scriptaculous/lib/prototype.js\" ></script>
	<script type=\"text/javascript\" src=\"install.js\" ></script>
	</head>
	<body>\n";


	$OUT.= "<h1><span class=\"ko\">"._('Oh no!')."</span> "._('Something goes wrong...')."</h1>\n";


	if(defined('FRONT_ERROR_LOG') && file_exists(FRONT_ERROR_LOG) && is_readable(FRONT_ERROR_LOG)){

		$ERROR_LOG = file_get_contents(FRONT_ERROR_LOG);

		$ERROR_LOG_HTML="<div id=\"error_log\"><code>"
						.str_replace("\n", "<br /><br />\n",htmlentities($ERROR_LOG,ENT_QUOTES))
						."</code></div>\n";
	}
	else{
		$ERROR_LOG_HTML='';
	}

	

	if(!isset($_GET['errty'])) $_GET['errty']='';

	switch($_GET['errty']){

		case 'copy_sqlite': $msg="<p>";
							$msg.=sprintf(_('There is a problem copying the SQLite file in %s.'),
										$db1['filename_reg']);
							$msg.="</p>\n<p>\n";
							$msg.="<b>"._('Is the directory writable by apache user?')."</b></p>";
							$msg.="<p>".sprintf(_("Try to change the directory permissions and %s go back to step 2 %s"),
										"<a href=\"?p=2\">","</a>");
							$msg.="</p>";
		break;


		case 'copy_dbrules': $msg="<p>";
							$msg.=_('There is a problem creating the VFront schema/database.');
							$msg.="</p>\n<p>\n";
							$msg.="<b>"._('Is you DB connected? Have the user the correct permissions? Please see you error log:')."</b> ";
							$msg.="</p>";
							$msg.=$ERROR_LOG_HTML;
		break;

		case 'no_insert_admin_sqlite':
							$msg="<p>";
							$msg.=_('There is a problem creating the admin user.');
							$msg.="</p>\n<p>\n";
							$msg.="<b>".sprintf(_('The SQLite file %s exists and is writable by apache user?'),
										$db1['filename_reg'])."</b></p>";
							$msg.="<p>".sprintf(_("Try to change the directory permissions and %s go back to step 4 %s"),
										"<a href=\"?p=4\">","</a>");
							$msg.="</p>";
		break;


		case 'no_db_conn':  $msg="<p>";
							$msg.=sprintf(_('There is a problem connecting to %s.'), $db1['frontend']);
							$msg.="</p>\n<p>\n";
							$msg.=sprintf(_("Please check your DB connection in %s step 1 %s"),
										"<a href=\"?p=1\">","</a>");
							$msg.="</p>";
		break;
	
		case 'no_create_table':$msg="<p>";
							$msg.=_('There is a problem creating the VFront tables.');
							$msg.="</p>\n<p>\n";
							$msg.="<b>"._('Is you DB connected? Have the user the CREATE permission?')."<br />";
							$msg.=_('Please see you error log:')."</b> ";
							$msg.="</p>";
							$msg.=$ERROR_LOG_HTML;
		break;


		case 'no_user_admin':$msg="<p>";
							$msg.=_('There is a problem creating the admin user.');
							$msg.="</p>\n<p>\n";
							$msg.="<b>"._('Please see you error log:')."</b> ";
							$msg.="</p>";
							$msg.=$ERROR_LOG_HTML;
		break;

		default:	$msg="<p>"._('Unknow error')."</p>\n";
					$msg.=$ERROR_LOG_HTML;

	}

	$OUT.=$msg;



	$OUT.=  "
	</body>
	</html>";

	print $OUT;

	exit;
}
























function require_vmsql($dbtype,$dbsqlite_version=''){

	if(file_exists("../inc/vmsql.".$dbtype.$dbsqlite_version.".php")){
		require_once("../inc/vmsql.".$dbtype.$dbsqlite_version.".php");
	}
	else{
		die("Errore su ".__LINE__);
	}

	// Alias Class for DB connection

	if (!function_exists('class_alias')) {
		function class_alias($original, $alias) {
			eval('class ' . $alias . ' extends ' . $original . ' {}');
		}
	}

	if($dbtype=='mysql' && class_exists('mysqli_vmsql')){
		$cname='mysqli_vmsql';
	}
	else if($dbtype=='mysql' && class_exists('mysql_vmsql')){
		$cname='mysql_vmsql';
	}
	else if($dbtype=='sqlite'){

		$cname=($dbsqlite_version=='3') ? 'sqlite3_vmsql' : 'sqlite2_vmsql';
	}
    else if($dbtype=='postgres'){
        $cname="postgres_vmsql";
    }
	else{
		$cname=$dbtype."_vmsql";
	}

	if(!class_exists('vmsql')) $make_alias1 = class_alias($cname, 'vmsql');
	if(!class_exists('vmreg')) $make_alias2 = class_alias($cname, 'vmreg');
}



/**
 * Funzione di test per la generazione di una tabella.
 * Serve a testare il diritto CREATE, mediante la creazione di una tabella di nome pseudocasuale
 *
 * @return bool
 */
function test_crea_tabella(){

	global  $vmsql, $db1;

	// nome casuale
	$nome_tabella="a".substr(md5(time()),0,16);

	$sql_test="CREATE TABLE $nome_tabella (test integer)";

	$q_test=$vmsql->query_try($sql_test,false);

	if(!$q_test){

		return false;
	}
	else{
		$q_test2=$vmsql->query("DROP TABLE $nome_tabella");
		return true;
	}

}





/**
 * @desc Esegue i GRANT necessari per il nuovo utente
 * @param bool $return_only_sql
 * @return int
 */
function grant($return_only_sql, $vmsql, $db1){

	$sql=array();


	if($db1['dbtype']=='mysql'){

		//$sql_user= "CREATE USER {$db1['user']}@{$db1['host']} IDENTIFIED BY '{$db1['passw']}'; ";

		$sql[]="GRANT SELECT, INSERT, UPDATE, DELETE ON {$db1['frontend']}{$db1['sep']}* TO "
			  ."'{$db1['user']}'@{$db1['host']} IDENTIFIED BY '{$db1['passw']}'";

		//$sql[]= "GRANT SELECT, INSERT, UPDATE, DELETE, SHOW VIEW ON {$db1['dbname']}.* TO '{$db1['user']}'@{$db1['host']} IDENTIFIED BY '{$db1['passw']}';";
	}
	else if ($db1['dbtype']=='postgres'){


		// crea l'utente
		//$sql_user= "CREATE USER {$db1['user']} WITH PASSWORD '{$db1['passw']}'; ";

		$sql=array();

		// diritto d'uso dello schema frontend
		$sql[]="GRANT USAGE ON SCHEMA {$db1['frontend']} TO {$db1['user']};";

		// diritto d'uso dello schema information_schema
		$sql[]="GRANT USAGE ON SCHEMA information_schema TO {$db1['user']};";

		// diritto d'uso dello schema pg_catalog
		$sql[]="GRANT USAGE ON SCHEMA pg_catalog TO {$db1['user']};";

		// prendi le tabelle dei due schemi
		$q_tab=$vmsql->query("SELECT table_schema || '.' || '\"' || table_name || '\"' FROM information_schema.tables WHERE table_schema IN ('{$db1['dbname']}','{$db1['frontend']}')");

		while($RS_tab=pg_fetch_row($q_tab)){

			$sql[]="GRANT SELECT, INSERT, UPDATE, DELETE, REFERENCES ON ".$RS_tab[0]." TO {$db1['user']};";
		}

		// prendi le tabelle dell'information_schema
		$q_tab=$vmsql->query("SELECT table_schema || '.' || '\"' || table_name || '\"' FROM information_schema.tables WHERE table_schema='information_schema'; ");

		while($RS_tab=pg_fetch_row($q_tab)){

			$sql[]="GRANT SELECT ON ".$RS_tab[0]." TO {$db1['user']};";
		}

		// prendi le funzioni dei due schemi
		$q_func=$vmsql->query("select 'GRANT EXECUTE ON FUNCTION '||n.nspname||'.'||p.proname||'('||oidvectortypes(p.proargtypes)||') TO {$db1['user']};' from pg_proc p, pg_namespace n where n.oid = p.pronamespace and n.nspname IN ('{$db1['dbname']}','{$db1['frontend']}');");

		while($RS_func=$vmsql->fetch_row($q_func)){

			$sql[]=$RS_func[0];
		}

		// prendi le sequenze dei due schemi
		$q_seq=$vmsql->query("select 'GRANT ALL ON '||n.nspname||'.'||c.relname||' TO {$db1['user']};' from pg_class c, pg_namespace n where n.oid = c.relnamespace and c.relkind IN ('S') and n.nspname in ('{$db1['dbname']}','{$db1['frontend']}');");

		while($RS_seq=$vmsql->fetch_row($q_seq)){

			$sql[]=$RS_seq[0];
		}
	}

	if($return_only_sql){

		return $sql_user."\n".implode("\n",$sql);
	}

	//$q_user=$vmsql->query_try($sql_user,false);

	for($i=0;$i<count($sql);$i++){

		$q=$vmsql->query($sql[$i]) or die($vmsql->error());
	}

	$aff_rows=$vmsql->affected_rows($q);

	if($db1['dbtype']=='mysql')  $q_flush=$vmsql->query("FLUSH PRIVILEGES");

	return $aff_rows;
}





function get_sqlite_file_version($path_sqlite){

	if(!file_exists($path_sqlite)) return false;

	$test=0;

	if(class_exists('SQLite3')){

		$mylink= new SQLite3($path_sqlite,SQLITE3_OPEN_READONLY);
		$test=$mylink->exec("SELECT 1 from sqlite_master");

		if($test) return 3;
	}

	if($test==0 || class_exists('SQLiteDatabase')){

		$mylink= new SQLiteDatabase($path_sqlite);
		$test=$mylink->queryExec("SELECT 1 from sqlite_master");

		if($test) return 2;
	}

	return 0;
}
