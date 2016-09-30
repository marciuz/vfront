<?php
require_once("../inc/conn.php");

proteggi();


// prendi tabella
//$nome_tabella=preg_replace("|[^a-zA-Z0-9_-]+|","",$_GET['action']);

$id_reg= (int) $_POST['id_reg'];

$q0=$vmreg->query("SELECT column_name, in_default FROM {$db1['frontend']}{$db1['sep']}registro_col WHERE id_reg=$id_reg");

if($vmreg->num_rows($q0)==1){
	
	list($column_name,$query)=$vmreg->fetch_row($q0);
}
else die(-1);

//$q= ($vmsql->query_try($query)) ? $vmsql->query($query . " ORDER BY 2,1") : die(-2);
$q=$vmsql->query($query . " ORDER BY 2,1");

$n_res= $vmsql->num_rows($q);

if($n_res>0){

$JSON="[{'c':'$column_name','val':[";
	
	while($RS=$vmsql->fetch_row($q)){
		
		$JSON.="['{$RS[0]}','".addslashes($RS[1])."'],";
	}
	
	$JSON=substr($JSON,0,-1)."]}]";
	
	echo $JSON;

}
else die(0);
















?>