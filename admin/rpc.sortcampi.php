<?php
/**
 * Il file permette di ridefinire il campo "in_ordine" che viene letto per l'ordinamento dei campi.
 * In caso di azzeramento di ordinamento manuale il campo in_ordine viene impostato a 0
 * e viene utilizzato il campo di default "odinal_position". 
 * Lo script è richiamato dal file {@link gestione_tabelle_gruppi.php}
 * 
 * @desc Script per l'ordinamento con modalità AJAX dei campi
 * @package VFront
 * @subpackage Administration
 * @author M.Marcello Verona
 * @copyright 2007-2010 M.Marcello Verona
 * @version 0.96 $Id: rpc.sortcampi.php 880 2010-12-14 12:43:47Z marciuz $
 * @license http://www.gnu.org/licenses/gpl.html GNU Public License 
 * @see gestione_tabelle_gruppi.php
 */

include("../inc/conn.php");


proteggi(2);


if(isset($_POST['firstlist'])){

	
	
	
	/*
	
	OLD SETTINGS (only order)
	
	for($i=0;$i<count($_POST['firstlist']);$i++){
		
		static $e=0;
		static $c=0;
		
		$sql="UPDATE {$db1['frontend']}{$db1['sep']}registro_col SET in_ordine=$i WHERE id_reg=".intval($_POST['firstlist'][$i]);
		$test=$vmsql->query_try($sql,false);
		
		$e+= ($test) ? 1:0;
		$c++;
		
	}
	
	if($e==$c){
	
		echo "<span class=\"verde\"><img src=\"../img/op_ok.gif\" alt=\"opok\" /> "._("Operation done")."</span>";
	}
	else{
		echo "<span class=\"rosso\"><img src=\"../img/op_ko.gif\" alt=\"opok\" /> "._("Operation not performed!")."</span>";
	}*/
	
	
	
	
	
	
	$ordine=array(0);
		
	$max=(count($_POST['firstlist'])>=count($_POST['secondlist'])) ? count($_POST['firstlist']):count($_POST['secondlist']);
	
	$new_in_line=array();
	
	for($i=0;$i<$max;$i++){

		$_POST['firstlist'][$i] = (isset($_POST['firstlist'][$i])) ? intval($_POST['firstlist'][$i]) : 0;
		$_POST['secondlist'][$i] =(isset($_POST['secondlist'][$i])) ? intval($_POST['secondlist'][$i]) : 0;
		
		if($_POST['firstlist'][$i]>0){
			
			$ordine[]=$_POST['firstlist'][$i];
		}
		
		if($_POST['secondlist'][$i]>0){
			
			$ordine[]=$_POST['secondlist'][$i];
			
			$new_in_line[]=$_POST['firstlist'][$i];
			$new_in_line[]=$_POST['secondlist'][$i];
		}
	}
	
	unset($ordine[0]);
	
	foreach($ordine as $k=>$val){
		
		static $e1=0;
		static $c1=0;
		
		$test1=$vmreg->query_try("UPDATE {$db1['frontend']}{$db1['sep']}registro_col SET in_ordine=$k WHERE id_reg=".intval($val),false);
		
		$e1+= ($test1) ? 1:0;
		$c1++;
	}
	
	// metto a 0 gli inline (il dafault=NULL) 
	// ossia tutti potenzialmente a capo:
	$test2=$vmreg->query_try("UPDATE {$db1['frontend']}{$db1['sep']}registro_col SET in_line=0 WHERE id_table=".intval($_POST['oid']),false);
	
	foreach ($new_in_line as $id_reg){
		
		static $e2=0;
		static $c2=0;
		
		$test3=$vmreg->query_try("UPDATE {$db1['frontend']}{$db1['sep']}registro_col SET in_line=1 WHERE id_reg=".intval($id_reg),false);

		$e2+= ($test1) ? 1:0;
		$c2++;
	}
	
	if($e1==$c1){
	
		echo "<span class=\"verde\"><img src=\"../img/op_ok.gif\" alt=\"opok\" /> "._("Operation done")."</span>";
	}
	else{
		echo "<span class=\"rosso\"><img src=\"../img/op_ko.gif\" alt=\"opok\" /> "._("Operation not performed!")."</span>";
	}
	
}
elseif(isset($_GET['ripristina']) && isset($_POST['oid'])){
	
//	$q=$vmreg->query("UPDATE {$db1['frontend']}{$db1['sep']}registro_col SET in_ordine=0 WHERE id_table=".intval($_POST['oid']));
	$q=$vmreg->query("UPDATE {$db1['frontend']}{$db1['sep']}registro_col SET in_ordine=0, in_line=NULL WHERE id_table=".intval($_POST['oid']));
	header("Location: ".dirname($_SERVER['PHP_SELF'])."/gestione_tabelle_gruppi.php?".$_POST['url']."&a=4");
	exit;	
}



?>