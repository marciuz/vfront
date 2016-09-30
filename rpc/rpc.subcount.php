<?php
/**
* File di conteggio dei record nelle sottomaschere. 
* Questo file è richiamato dal file {@link scheda.php}. 
* Il numero di record presenti nelle sottomaschere viene dinamicamente caricato nel nome delle sottomaschere, tra parentesi.
* 
* @package VFront
* @subpackage RPC
* @author Mario Marcello Verona <marcelloverona@gmail.com>
* @copyright 2007-2010 M.Marcello Verona
* @version 0.96 $Id: rpc.subcount.php 1123 2014-12-16 15:09:19Z marciuz $
* @license http://www.gnu.org/licenses/gpl.html GNU Public License
*/

require("../inc/conn.php");

proteggi(1);

$sottomaschere = explode("|",$_GET['subs']);

$id_table=RegTools::name2oid($_GET['action'],$_SESSION['gid']);
$_K = RegTools::prendi_K_relazione_sub($id_table, 'assoc');

$_FK=array();

foreach($_K as $kk){
    $_FK[$kk['nome_tabella']]=$kk;
}

$out_num='';

for($i=0;$i<count($sottomaschere);$i++){
	
	if(RegTools::is_tabella($sottomaschere[$i])){
		
		$PK=RegTools::prendi_PK($_GET['action']);
		
		$sql="SELECT count(*) FROM ".$sottomaschere[$i]." sub, ".$_GET['action']." t
			WHERE t.{$_FK[$sottomaschere[$i]]['campo_pk_parent']} = sub.{$_FK[$sottomaschere[$i]]['campo_fk_sub']}
			AND t.$PK='".$vmsql->escape($_GET['id'])."'";
		
		$q=$vmsql->query($sql);
		list($out)= $vmsql->fetch_row($q);
		$out_num.= (int) $out.",";
	}
}

echo substr($out_num,0,-1);

