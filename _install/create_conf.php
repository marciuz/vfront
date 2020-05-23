<?php
/**
 * VFront Web Installer - Utility di installazione dell'applicazione VFront 
 * Caratteristiche richieste: PHP5.x , MySQL 5.x, php_mysqli 
 * Oppure: PHP5.x , Postgres 8.x, php_pgsql
 * @package VFront
 * @subpackage VFront_Web_Installer
 * @author M.Marcello Verona
 * @copyright 2007-2010 M.Marcello Verona
 * @version 0.96 $Id: create_conf.php 1076 2014-06-13 13:03:44Z marciuz $
 * @license http://www.gnu.org/licenses/gpl.html GNU Public License 
 */

if($test_include!==true) exit;


foreach ($_POST['var'] as $k=>$v){
	
	$var[$k]=trim(addslashes(stripslashes($v)));
}

$WR="<?php
/**
######################################################################
#
#	 VFRONT Configuration file
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
#######################################################################
*/
";


$WR.="
/**
 * @desc VFront Configuration file
 * @package VFront
 * @subpackage Config
 * @author M.Marcello Verona
 * @copyright 2007-2020 M.Marcello Verona
 * @version 0.96 \$Id: create_conf.php 1076 2014-06-13 13:03:44Z marciuz $
 * @license http://www.gnu.org/licenses/gpl.html GNU Public License 
 */
 
 
";


if($var['dbtype']=='mysql'){
	
	if($var['regmethod']=='sqlite'){
		
		$var['dbfrontend1']='';
		$var['sep']='';
		
	}
	else{
		$var['sep']='.';
	}

$WR.=<<<PHPW
/*  DB  CONNECTION */

// Connect to MYSQL 5.x: comment the block in case you use other DB (Postgres)
\$db1['dbtype']="mysql";
\$db1['host']="{$var['dbhost1']}";
\$db1['port']="{$var['dbport1']}";
\$db1['user']="{$var['dbuser1']}";
\$db1['passw']="{$var['dbpassw1']}";
\$db1['dbname']="{$var['dbname1']}";
\$db1['frontend']="{$var['dbfrontend1']}";
\$db1['sep']="{$var['sep']}";

PHPW;


}
else if($var['dbtype']=='postgres'){


	if($var['regmethod']=='sqlite'){

		$var['dbfrontend2']='';
		$var['sep']='';

	}
	else{
		$var['dbfrontend2']= ($var['dbfrontend2']=='') ? 'frontend' : $var['dbfrontend2'];
		$var['sep']='.';
	}


$db_schema=(isset($var['dbschema2']) && trim($var['dbschema2'])!='') ? $var['dbschema2']:'public';

$WR.=<<<PHPW
// POSTGRES - comment the block in case you use other DB (MYSQL)
\$db1['dbtype']="postgres";
\$db1['host']="{$var['dbhost2']}";
\$db1['port']="{$var['dbport2']}";
\$db1['user']="{$var['dbuser2']}";
\$db1['passw']="{$var['dbpassw2']}";
\$db1['postgres_dbname']="{$var['dbname2']}";
\$db1['dbname']="{$var['dbschema2']}";
\$db1['frontend']="{$var['dbfrontend2']}"; 
\$db1['sep']="{$var['sep']}";

PHPW;

}
else if($var['dbtype']=='oracle'){

	$oci_prefix = ($var['regmethod']=='sqlite') ? '':$var['dbfrontend3'];


$WR.=<<<PHPW
// ORACLE
\$db1['dbtype']="oracle";
\$db1['host']="{$var['dbhost3']}";
\$db1['port']="{$var['dbport3']}";
\$db1['user']="{$var['dbuser3']}";
\$db1['passw']="{$var['dbpassw3']}";
\$db1['dbname']=""; 
\$db1['frontend']="$oci_prefix";
\$db1['sep']='';
\$db1['service']="{$var['dbservice3']}";

PHPW;
}

else if($var['dbtype']=='sqlite'){
	
	$sql_file_version=get_sqlite_file_version($var['dbfilename4']);

$WR.=<<<PHPW
// SQLITE
\$db1['dbtype']="sqlite";
\$db1['host']="";
\$db1['port']="";
\$db1['user']="";
\$db1['passw']="";
\$db1['dbname']="";
\$db1['frontend']="";
\$db1['sep']='';
\$db1['filename']='{$var['dbfilename4']}';
\$db1['dbsqlite_version']='$sql_file_version';

PHPW;
}


$WR.=<<<PHPW

define('VFRONT_DBTYPE', \$db1['dbtype']);
    
PHPW;


$authtype=($var['authtype']=='null') ? '' : $var['authtype'];


$WR.=<<<PHPW



// PARAMETRI PER LA MODALITA' DI AUTENTICAZIONE ESTERNA  --------------------------------------------------------------------


// Questo parametro permette di effettuare l'autenticazion mediante uno strumento esterno (database, ldap, eccetera)
// Qualora si volesse effettuare l'autenticazione direttamente dal database di VFront si imposti la variabile = '' oppure null

\$conf_auth['tipo_external_auth']= '{$authtype}'; // 'db' | 'db_ext' | 'ldap' | 'soap' | null

PHPW;




if($authtype!=''){

$WR.=<<<PHPW
	
// Names of the fields where to find email, password (if any), first and last name of the user from DB, external DB, LDAP or SOAP
// these variables must be set in case of external authentication
\$conf_auth['campo_nick']='{$var['authext_nick']}';
\$conf_auth['campo_email']='{$var['authext_mail']}';
\$conf_auth['campo_password']='{$var['authext_passwd']}';
\$conf_auth['campo_nome']='{$var['authext_name']}'; // opzionale
\$conf_auth['campo_cognome']='{$var['authext_surname']}'; // opzionale
\$conf_auth['password_crypt']='{$var['authext_passwd_encode']}'; // md5 | sha1 | null  // impostare se le password dovessero essere criptate o si dovesse usare un hash 

PHPW;

}


if($authtype=='db'){

$WR.=<<<PHPW

/* DB */
// if you have chosen external authentication via DB other than VFront, set the following parameters 
// to read user name and password from the database and table chosen for external authentication
\$conf_auth['db']['database']='{$var['authdb_dbname']}'; // deve risiedere sullo stesso server (Solo Mysql) -- per altri server utilizzare DB_EXT, SOAP o altri metodi
\$conf_auth['db']['tabella']='{$var['authdb_usertable']}';

PHPW;

}


else if($authtype=='db_ext'){

$WR.=<<<PHPW

/*  SEZIONE DB_EXT (DB ESTERNO) */
// if you have chosen external authentication via external DB or resident on another server, set the following parameters 
// Generic parameters will also be used \$conf_auth['campo_email'], \$conf_auth['campo_password'], \$conf_auth['campo_nome'],\$conf_auth['campo_cognome']
// above defined
\$conf_auth['db_ext']['dbtype']="{$var['authdb_ext_type']}"; // mysql | postgres | odbc
\$conf_auth['db_ext']['host']="{$var['authdb_ext_host']}"; // host del server DB esterno utilizzato per l'autenticazione
\$conf_auth['db_ext']['port']="{$var['authdb_ext_port']}"; // porta del server DB esterno utilizzato per l'autenticazione
\$conf_auth['db_ext']['user']="{$var['authdb_ext_user']}"; // utente
\$conf_auth['db_ext']['passw']="{$var['authdb_ext_passwd']}"; // password
\$conf_auth['db_ext']['dbname']="{$var['authdb_ext_dbname']}"; // nome del database
\$conf_auth['db_ext']['tabella']="{$var['authdb_ext_table']}"; // nome della tabella
\$conf_auth['db_ext']['odbc_dsn']="{$var['authdb_ext_odbcdsn']}"; // solo per connessioni ODBC

PHPW;

}

else if($authtype=='ldap'){
	
	$var['ldap_anonymus_bind']= (int) $var['ldap_anonymus_bind'];

$WR.=<<<PHPW

	
/*  LDAP Section (or Active Directory)  */
// if you have chosen external authentication via LDAP (or Active Directory) set the following parameters 
// to read user name and password from the server 
\$conf_auth['ldap']['base_dn']='{$var['ldap_basedn']}';
\$conf_auth['ldap']['host']='{$var['ldap_host']}';
\$conf_auth['ldap']['anonymus_bind']={$var['ldap_anonymus_bind']};
\$conf_auth['ldap']['bind_user']='{$var['ldap_bind_user']}';
\$conf_auth['ldap']['bind_passw']='{$var['ldap_bind_passw']}';

PHPW;

}


$WR.=<<<PHPW

//--------------   End of external authentication  --------------  //


// SMTP AND MAIL SECTION
// if you want to use a custom SMTP for email management 

\$conf_mail['SMTP_AUTH']={$var['smtp_use']};
\$conf_mail['SMTP']="{$var['smtp_address']}";
\$conf_mail['SMTP_AUTH_USER']="{$var['smtp_user']}";
\$conf_mail['SMTP_AUTH_PASSW']="{$var['smtp_passwd']}";
\$conf_mail['MAIL_SENDER']="{$var['smtp_sender']}";
\$conf_mail['MAIL_SENDER_NAME']="{$var['smtp_sendername']}";

/**
 * system administrator email
 */
define('_SYS_ADMIN_MAIL','{$var['mail_sysamin']}');

/**
 * developer emails (for debug emails)
 */
define('_DEV_MAIL','{$var['mail_dev']}');




/* DEBUG Section */

/**
 * errors on screen | errors in email
 * In production environment we recommend 
 * set the variable to FALSE: in case of error an email will be sent to the administrator
 * and the developer. The user sees a screen where they are notified that an error has been generated.
 * In case the variable is TRUE the errors will be shown on screen instead
 */
\$DEBUG_SQL={$var['debug_sql']};

/**
 * open a javascript popup showing SQL queries - default: FALSE
 */
\$DEBUG_SQL_SHOW_QUERY=false;

/**
 * write the SQL calls in a file (default ./rpc.debug.txt) - default: FALSE
 */
\$RPC_DEBUG=false;




/* LOG Secrion */

/**
 * writes a log of the SQL calls for insertion, modification and deletion - default: TRUE
 */
\$RPC_LOG=true;

PHPW;


$WR.="

/*  SEZIONE LANGUAGE AND ENCODING  */

/**
 * Language : Valori possibili:  en_US, fr_FR, it_IT, de_DE...
 */
define('FRONT_LANG','{$var['lang']}');



/**
 * Encoding
 */
define('FRONT_ENCODING','{$var['encoding']}');




/*  SEZIONE DATE */

/**
 * Date format: (iso,eng,ita)
 */
define('FRONT_DATE_FORMAT','{$var['dateformat']}');




/*  SEZIONE PATH  */

/**
 * Real path
 */
define('FRONT_ROOT','{$var['front_root']}');

/**
 * Real path
 */
define('FRONT_REALPATH','{$var['front_root']}');



/**
 * Path della document root
 */
define('FRONT_DOCROOT','{$var['document_root']}');

/**
 * Path mysqldump (per l'esportazione di MySQL) - Default: mysqldump
 */
define('_PATH_MYSQLDUMP','mysqldump');

/**
 * path pg_dump (per l'esportazione di Postgres) - Default: pg_dump
 */
define('_PATH_PG_DUMP','pg_dump');

/**
 * path per il filesystem allegati
 */
define('_PATH_ATTACHMENT',FRONT_REALPATH.'/files/data');

/**
 * path di tmp per il filesystem allegati
 */
define('_PATH_ATTACHMENT_TMP',FRONT_REALPATH.'/files/tmp');

/**
 * path per il filesystem documenti utili
 */
define('_PATH_HELPDOCS',FRONT_REALPATH.'/files/docs');

/**
 * path per il filesystem documenti utili admin
 */
define('_PATH_HELPDOCS2',FRONT_REALPATH.'/files/docsadmin');

/**
 * path di tmp accessibile via web
 */
define('_PATH_TMP',FRONT_REALPATH.'/files/tmp');

/**
 * path di tmp accessibile via web
 */
define('_PATH_TMP_HTTP',FRONT_DOCROOT.'/files/tmp');

/**
 * path per i fogli di stile XSL allegati
 */
define('_PATH_XSL',FRONT_REALPATH.'/files/xsl_custom');

/**
 * path web per i fogli di stile XSL allegati
 */
define('_PATH_WEB_XSL',FRONT_DOCROOT.'/files/xsl_custom');

/**
 * path per error log
 */
define('FRONT_ERROR_LOG',FRONT_REALPATH.'/files/db/error_log.txt');





/*  SEZIONE FOP  */
/* Use the Apache FOP application http://xmlgraphics.apache.org/fop/ 
to generate the PDF version of XML files */

/**
 * Imposta se Vfront puo' utilizzare l'applicazione FOP 
 */
define('_FOP_ENABLED',{$var['fop_enabled']});

/**
 * Imposta se Vfront puo' utilizzare l'applicazione FOP 
 */
define('_PATH_FOP','{$var['path_fop']}');






/*  SEZIONE ALLEGATI E LINK  */

/**
 * definizione della tabella allegato
 */
define('_TABELLA_ALLEGATO',\"{\$db1['frontend']}{\$db1['sep']}allegato\");

/**
 * definizione della tabella link
 */
define('_TABELLA_LINK',\"{\$db1['frontend']}{\$db1['sep']}link\");





/*  SEZIONE MISC  */


/**
 * massimo tempo di editing di un record per considerarlo bloccato (in secondi)
 */
define('_MAX_TEMPO_EDIT',{$var['max_tempo_edit']});

/**
 * passphrase per le codifiche base64
 */
define('_BASE64_PASSFRASE',\"{$var['passfrase']}\");

/**
 * Nome progetto
 */
define('_NOME_PROJ','{$var['name_proj']}');


";

$registry_method= ($var['regmethod']=='sqlite' || $var['dbtype']=='sqlite') ? 'true':'false';

$version_reg_sqlite=(class_exists('SQLite3')) ? 3:2;


$WR.="


/* SEZIONE SQLITE */

define('USE_REG_SQLITE',$registry_method);
define('VERSION_REG_SQLITE',$version_reg_sqlite);

// SQLite Reg
\$db1['filename_reg']=\"{$var['sqlite_path']}\"; // path of sqlite database


";



##################################################
#
#	WRITE CONF (if possible)
#

$file_conf_target="../conf/conf.target_install";
$file_conf_dest="../conf/conf.vfront.php";

	
		
	if($fp=@fopen($file_conf_target,"w")){
		$fpw=@fwrite($fp,$WR);
		@fclose($fp);
		
		rename($file_conf_target,$file_conf_dest);
		
		$_SESSION['file_connessione']=realpath($file_conf_dest);
	}
	else{
		
		$_SESSION['cont_file_connessione']=$WR;
	}

