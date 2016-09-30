<?php 
/**
* @desc Funzione per il test delle query RPC dalla gestione delle nuove statistiche
* @package VFront
* @subpackage Stats
* @author Mario Marcello Verona <marcelloverona@gmail.com>
* @copyright 2007-2010 M.Marcello Verona
* @version 0.96 $Id: test_query.php 880 2010-12-14 12:43:47Z marciuz $
* @license http://www.gnu.org/licenses/gpl.html GNU Public License
*/

require_once("../inc/conn.php");

 proteggi(2);

if(isset($_GET['sql'])){
	
	echo $res=$vmsql->query_try($_GET['sql'],true);
}
else{
	# nessuna chiamata
	echo "-1";
	
}

?>
