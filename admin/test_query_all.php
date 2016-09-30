<?php 
/**
 * @desc Script richiamato in modalit� AJAX per verificare la correttezza di numerose query SELECT
 * @package VFront
 * @subpackage Administration
 * @author M.Marcello Verona
 * @copyright 2007-2010 M.Marcello Verona
 * @version 0.96 $Id: test_query_all.php 880 2010-12-14 12:43:47Z marciuz $
 * @license http://www.gnu.org/licenses/gpl.html GNU Public License 
 * @see test_query.php
 */


require_once("../inc/conn.php");

 proteggi(2);

if(isset($_POST['sql'])){
	
	$n=count($_POST['sql']);
	$res=0;
	
	for($i=0;$i<$n;$i++){
		$res+=$vmsql->query_try($_POST['sql'][$i],true);
	}
	
 	echo ($res==$n && $n>0) ? 1:0;
		
	
}
else{
	# nessuna chiamata
	echo "-1";
	
}

?>