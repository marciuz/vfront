<?php
/**
* Wrapper per vmsql.sqlite[x].php
* 
* @package VFront
* @subpackage DB-Libraries
* @author Mario Marcello Verona <marcelloverona@gmail.com>
* @copyright 2007-2010 M.Marcello Verona
* @version 0.96 $Id: vmsql.sqlite.php 1084 2014-06-16 08:36:13Z marciuz $
* @see vmsql.sqlite3.php
* @see vmsql.sqlite2.php
* @license http://www.gnu.org/licenses/gpl.html GNU Public License
*/


/**
* Inclusione del file di libreria per php_mysqli
*/
if(class_exists('SQLite3')){
	require_once(dirname(__FILE__)."/vmsql.sqlite3.php");
}
else if(function_exists('sqlite_open')){
	require_once(dirname(__FILE__)."/vmsql.sqlite2.php");
}
else{

    die("SQLite not found");
}


?>