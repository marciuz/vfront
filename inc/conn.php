<?php
/**
* Connessione al file di configurazione. 
* Questo file viene incluso in tutte le pagine di VFront e fa riferimento al file di configurazione.
* Per il corretto funzionamento dell'applicazione � necessario specificare manualmente il collegamento al
* path reale del file di configurazione.
* 
* @package VFront
* @author Mario Marcello Verona <marcelloverona@gmail.com>
* @copyright 2007-2010 M.Marcello Verona
* @version 0.96 $Id: conn.php 1083 2014-06-14 18:58:01Z marciuz $
* @license http://www.gnu.org/licenses/gpl.html GNU Public License
*/





####################################################
#
#	Link to FILE CONF
#

$CONF_FILE='conf.vfront.php';










############################################################################
#
#	Do not change from here ...
#
############################################################################


// Linux or windows ?
$dir_vfront= (substr(realpath(__FILE__),0,1)=='/')
	? str_replace("/inc/conn.php",'',realpath(__FILE__))
	: str_replace("\\inc\\conn.php",'',realpath(__FILE__));

$VFRONT_CONFIGURATION_FILE=$dir_vfront.'/conf/'.$CONF_FILE;




if(!is_file($VFRONT_CONFIGURATION_FILE)){
	
	header("Location: ./_install/");
	exit;
}

if(!@include_once($VFRONT_CONFIGURATION_FILE)){
	echo "Can not include the configuration file. Change the settings of the file inc/conn.php";
	exit;
}



// DB connection

if($db1['dbtype']=='mysql'){
	
	if(function_exists('mysqli_connect')){
		require_once(FRONT_REALPATH."/inc/vmsql.mysqli.php");
		$vmsql = new mysqli_vmsql();
	}
	else{
		require_once(FRONT_REALPATH."/inc/vmsql.mysqlold.php");
		$vmsql = new mysql_vmsql();
	}
}
elseif($db1['dbtype']=='postgres'){
	
	require_once(FRONT_REALPATH."/inc/vmsql.postgres.php");
	$vmsql = new postgres_vmsql();
}
elseif($db1['dbtype']=='oracle'){

	require_once(FRONT_REALPATH."/inc/vmsql.oracle.php");
	$vmsql = new oracle_vmsql();
}
elseif($db1['dbtype']=='sqlite'){

	if(class_exists('SQLite3') && $db1['dbsqlite_version']=='3'){

		require_once(FRONT_REALPATH."/inc/vmsql.sqlite3.php");
		$vmsql=new sqlite3_vmsql();
	}
	else if(class_exists('SQLiteDatabase') && $db1['dbsqlite_version']=='2'){

		require_once(FRONT_REALPATH."/inc/vmsql.sqlite2.php");
		$vmsql=new sqlite2_vmsql();
	}
	else die(_('The SQLite database seems to be incompatible with the installed libraries'));
}
else{
	
	die(_("Could not connect to database: select a database type!"));
}


// Alias Class for DB connection

if (!function_exists('class_alias')) {
    function class_alias($original, $alias) {
        eval('class ' . $alias . ' extends ' . $original . ' {}');
    }
}



// encoding
$enc_connection= (FRONT_ENCODING=='UTF-8') ? 'utf8' : '';


// backcompatibily separator
if(!isset($db1['sep']))  $db1['sep']='.';

// backcompatibily constant
if(!defined('USE_REG_SQLITE')) define('USE_REG_SQLITE', false);


/*
 * DATABASE (data) Connection
 */
$vmsql->connect($db1, $enc_connection);







// Alias Class for VFront Registry DB connection

if(USE_REG_SQLITE){

	$db1['frontend']="";
	$db1['sep']="";

	
	$cname_sqlite= (VERSION_REG_SQLITE==3) ? 'sqlite3_vmsql':'sqlite2_vmsql';
	$required_file=(VERSION_REG_SQLITE==3) ? 'vmsql.sqlite3.php':'vmsql.sqlite2.php';

	require_once(FRONT_REALPATH."/inc/$required_file");
	$make_alias = class_alias($cname_sqlite, 'vmreg');

	/*
	 * DATABASE (rules) Connection
	 */
	$vmreg = new vmreg();
	$tes_conn=$vmreg->connect($db1['filename_reg'], $enc_connection);
}
else{

	$make_alias = class_alias(get_class($vmsql), 'vmreg');

	/*
	 * DATABASE (rules) Connection
	 */
	$vmreg = new vmreg();
	$vmreg->connect($db1, $enc_connection);
}






// Avvia la sessione
session_name("VFRONT_".preg_replace("|[\W]+|","_",_NOME_PROJ."_"._BASE64_PASSFRASE));
session_start();





// include VARS
include_once(FRONT_ROOT."/inc/func.var_frontend.php");
if(!isset($_SESSION['VF_VARS'])){
	$_SESSION['VF_VARS']=var_frontend('session','session');
}














// Impostazione della lingua 
$locale = (defined('FRONT_LANG')) ? FRONT_LANG : 'en_US';

if(isset($_SESSION['VF_VARS']['lang']) && $_SESSION['VF_VARS']['lang']!=''){
	
	$locale = $_SESSION['VF_VARS']['lang'];
}

// Impostazione della codifica
$encoding = (defined('FRONT_ENCODING')) ? FRONT_ENCODING : 'UTF-8';

$locale_dir = FRONT_REALPATH.'/locale'; // your .po and .mo files should be at $locale_dir/$locale/LC_MESSAGES/messages.{po,mo}

$domain = 'messages';


// sezione gettext
if(!function_exists('_')){
	
	require_once(FRONT_REALPATH.'/plugins/phpgettext/gettext.inc.php');
	
	
	// gettext setup
	if(!defined('LC_MESSAGES')){
		
		@putenv("LC_ALL=$locale");
		@putenv("LC_MESSAGES=$locale");
	}
	
	// gettext setup
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




// Autoloader
spl_autoload_register(function ($class) {
    global $db1;
    include_once FRONT_ROOT.'/classes/class.' . strtolower($class). '.php';
});
		


$USE_JSON = true ;












/**
 * Funzione di protezione delle pagine. 
 * Mediante questa funzione, richiamata in testa ad ogni pagina, � possibile proteggere il singolo script 
 * da accessi non autenticati.
 * Il parametro "Livello" indica il livello minimo necessario per eseguire lo script della pagina.
 *
 * @param int $livello Indica il livello di amministrazione per la pagina nel quale la funzione viene richiamata
 */
function proteggi($livello=1){
	
	if($livello>0){
	
		if(!isset($_SESSION['user']['livello'])){
		    
			
			
			header("Location: ".FRONT_DOCROOT."/index.php?nolog=1&sessione_inesistente&urlreq=".$_SERVER['REQUEST_URI']);
			exit;
		}
		elseif( $_SESSION['user']['livello'] < $livello ){
			header("Location: ".FRONT_DOCROOT."/index.php?nolog=2&sessione_insuff");
			exit;
		}
	}
}

