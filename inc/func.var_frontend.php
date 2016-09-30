<?php
/**
* Libreria di funzioni per il recupero delle variabili di ambiente di VFront 
* 
* @package VFront
* @subpackage Function-Libraries
* @author Mario Marcello Verona <marcelloverona@gmail.com>
* @copyright 2007-2010 M.Marcello Verona
* @version 0.96 $Id: func.var_frontend.php 992 2012-07-10 13:40:42Z marciuz $
* @license http://www.gnu.org/licenses/gpl.html GNU Public License
*/


/**
* Libreria di funzioni per il recupero delle variabili di ambiente di VFront 
* 
* @param mixed $gid Gruppo per il quale recuperare le variabili
* @return array Viene generato un array globale _VARIABILI
*/
function var_frontend($gid="session",$store_type=null){
	
	global $vmreg, $db1;
		
	$gid= ($gid=="session" && isset($_SESSION['gid'])) ? (int) $_SESSION['gid'] : (int) $gid;
	
	$vfront_vars=array();
		
	// Prendi le variabili globali

	$q1=$vmreg->query("SELECT variabile, valore FROM ".$db1['frontend'].$db1['sep']."variabili WHERE gid=0");
	
	while($RS=$vmreg->fetch_assoc($q1)){
		
		if($store_type==null)
			$GLOBALS['_VARIABILI'][$RS['variabile']]=$RS['valore'];
		else 
			$vfront_vars[$RS['variabile']]=$RS['valore'];
	}
	
	// Prendi le variabili Locali se gid!=0

	if($gid!=0){
		$q2=$vmreg->query("SELECT variabile, valore FROM ".$db1['frontend'].$db1['sep']."variabili WHERE gid=$gid");
		
		while($RS2=$vmreg->fetch_assoc($q2)){
			if($store_type==null)
				$GLOBALS['_VARIABILI'][$RS2['variabile']]=$RS2['valore'];
			else
				$vfront_vars[$RS2['variabile']]=$RS2['valore'];
		}
	}
	
	
	if($store_type!=null){
		return $vfront_vars;
	}
	
}

?>