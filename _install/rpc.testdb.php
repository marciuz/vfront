<?php
/**
 * VFront Web Installer - Utility di installazione dell'applicazione VFront 
 * Caratteristiche richieste: PHP5.x , MySQL 5.x, php_mysqli 
 * Oppure: PHP5.x , Postgres 8.x, php_pgsql
 * @package VFront
 * @subpackage VFront_Web_Installer
 * @author M.Marcello Verona
 * @copyright 2007-2010 M.Marcello Verona
 * @version 0.96 $Id: rpc.testdb.php 1076 2014-06-13 13:03:44Z marciuz $
 * @license http://www.gnu.org/licenses/gpl.html GNU Public License 
 */

$_data=$_POST['var'];

foreach($_data as $k=>$v) $_data[$k]=trim(strip_tags($v));


if($_data['dbtype']=='mysql'){
	
	if(function_exists('mysqli_connect')){
		$mylink = @mysqli_connect($_data['dbhost1'],$_data['dbuser1'],$_data['dbpassw1'],$_data['dbname1'],$_data['dbport1']) or die(0);
	}
	else{
		$mylink = @mysql_connect($_data['dbhost1'].":".$_data['dbport1'],$_data['dbuser1'],$_data['dbpassw1']) or die(0);
		$connection_DB_test= @mysql_select_db($_data['dbname1']) or die(0);
	}
	echo 1;
}
elseif($_data['dbtype']=='postgres'){
	
	$mylink = @pg_connect("host={$_data['dbhost2']} port={$_data['dbport2']} dbname={$_data['dbname2']} user={$_data['dbuser2']} password={$_data['dbpassw2']}")  or die(0);
	echo 1;
}
elseif($_data['dbtype']=='oracle'){

	$conn_string=$_data['dbhost3'];

        if($_data['dbport3']!=''){
            $conn_string.=":".$_data['dbport3'];
        }

        $conn_string.="/".$_data['dbservice3'];

	$mylink = @oci_connect($_data['dbuser3'],$_data['dbpassw3'],$conn_string) or die(0);
	echo 1;
}
elseif($_data['dbtype']=='sqlite'){

	$test=0;

	if(class_exists('SQLite3')){

		if(file_exists($_data['dbfilename4'])){
			$mylink= new SQLite3($_data['dbfilename4'],SQLITE3_OPEN_READONLY);
			$test=$mylink->exec("SELECT 1 from sqlite_master");

			if( intval($test) == 1) {

				die('1');
			}
		}
	}

	if($test==0 || class_exists('SQLiteDatabase')){

		if(file_exists($_data['dbfilename4'])){
			$mylink= new SQLiteDatabase($_data['dbfilename4']);
			$test=$mylink->queryExec("SELECT 1 from sqlite_master");

			$test=(int) $test;
		}
		else $test=0;
		
	}

	echo $test;


}
else echo -1;



?>