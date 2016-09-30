<?php 
/**
 * @desc Script richiamato in modalit� AJAX per verificare la correttezza di una query SELECT
 * @package VFront
 * @subpackage Administration
 * @author M.Marcello Verona
 * @copyright 2007-2010 M.Marcello Verona
 * @version 0.96 $Id: test_query.php 1076 2014-06-13 13:03:44Z marciuz $
 * @license http://www.gnu.org/licenses/gpl.html GNU Public License 
 */


require_once("../inc/conn.php");

 proteggi(2);

if(isset($_GET['sql'])){
	
	$sql = stripslashes($_GET['sql']);
	
	echo $res=$vmsql->query_try($sql,true);
}
else{
	# nessuna chiamata
	echo "-2";
	
}

?>
