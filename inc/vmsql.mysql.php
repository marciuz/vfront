<?php
/**
* Alias per vmsql.mysqli.php
* 
* @package VFront
* @subpackage DB-Libraries
* @author Mario Marcello Verona <marcelloverona@gmail.com>
* @copyright 2007-2010 M.Marcello Verona
* @version 0.96 $Id: vmsql.mysql.php 819 2010-11-21 17:07:24Z marciuz $
* @see vmsql.mysqli.php
* @license http://www.gnu.org/licenses/gpl.html GNU Public License
*/


/**
* Inclusione del file di libreria per php_mysqli
*/
if(function_exists('mysqli_query')){
	require_once(dirname(__FILE__)."/vmsql.mysqli.php");
}
else{
	require_once(dirname(__FILE__)."/vmsql.mysqlold.php");
}


?>