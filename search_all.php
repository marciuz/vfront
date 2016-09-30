<?php
require("./inc/conn.php");
require("./inc/layouts.php");

proteggi(1);

function search_all($s, $only_visible=true, $exact=false, $views=true,  $max_res_x_tab=10){
	
	global  $vmsql, $db1;
	
	if($s=='') return null;
	
	
	$tab=RegTools::prendi_tabelle($_SESSION['gid'],$only_visible,!$views);
	
	$stat['n_tabelle']=count($tab);
	$stat['n_campi']=0;
	
	$cols=array();
	
	foreach($tab as $t){
		list($cols[$t['table_name']])=RegTools::prendi_colonne_frontend($t['table_name'],"column_name,table_type",$only_visible,$_SESSION['gid']);
	}
	
	
	$sql_all=array();
	
	// Ricerca
	foreach($cols as $tabella=>$campo){
		
		$sql=array();
		
		$stat['n_campi']+=count($campo);
		
		for($i=0;$i<count($campo);$i++){
			
			if($exact){

				$sql0=" SELECT '$tabella' tabella, '{$campo[$i]}' campo, count(*) n FROM $tabella WHERE _utf8 {$campo[$i]} COLLATE utf8_unicode_ci ='$s' \n";
				
				$sql[]= ($db1['dbtype']=='mysql')
					  ? $sql0 : str_replace(array("_utf8","COLLATE utf8_unicode_ci"),"",$sql0);
			}
			else{
				$sql0=" SELECT '$tabella' tabella, '{$campo[$i]}' campo, count(*) n FROM $tabella WHERE {$campo[$i]} LIKE _utf8 '%$s%' COLLATE utf8_unicode_ci \n";
				$sql[]= ($db1['dbtype']=='mysql')
					  ? $sql0 : str_replace(array("_utf8","COLLATE utf8_unicode_ci"),"",$sql0);
				
			}
		}
		
		$sql_all[]=implode(" UNION \n",$sql);
	}
	
	
	$sql_final= "SELECT * FROM (".implode(" UNION ",$sql_all).") tt WHERE n>0";
	
	$t0=microtime(true);
	
	$q=$vmsql->query($sql_final);
	
	$t1=microtime(true);

	$n=$vmsql->num_rows($q);
	
	if($n>0){
		
		$mat=$vmsql->fetch_assoc_all($q);
	}
	else{
		$mat=null;
	}
	
	$stat['t']=($t1-$t0);
	
	return array($mat,$stat);
	
	
}



$ss=(isset($_GET['s'])) ? trim($_GET['s']) : "";

$exact=(isset($_GET['e']) && $_GET['e']==1) ? 1:0;

$view_search=(isset($_GET['view_search']) && $_GET['view_search']==0) ? '':"checked=\"checked\"";

$exact_search=(!isset($_GET['e']) || $_GET['e']==0) ? '':"checked=\"checked\"";



$files=array("sty/tabelle.css","sty/admin.css","js/confirm_delete.js");	


$title_pag = _("Global search");

echo openLayout1($title_pag,$files);

echo breadcrumbs(array("HOME",strtolower($title_pag)));

echo "<h1><img src=\"./img/search.png\" alt=\"search\" style=\"vertical-align:middle\" /> ".$title_pag."</h1>\n";

echo "<form action=\"".$_SERVER['PHP_SELF']."\" method=\"get\">
	<p><label for=\"s\">"._('Search the entire database').":</label> 
	<input type=\"text\" size=\"80\" id=\"s\" name=\"s\" value=\"$ss\" tabindex=\"1\" />
	
	<input type=\"submit\" value=\""._('Search')."\" tabindex=\"3\"  />
	
	<input type=\"hidden\" value=\"0\" name=\"view_search\"  />
	<br />
	<input type=\"checkbox\" id=\"view_search\" value=\"1\" name=\"view_search\" $view_search  tabindex=\"2\" />
	<label for=\"view_search\">"._('Search also in Views')."</label>
	<br />
	<input type=\"checkbox\" id=\"e\" value=\"1\" name=\"e\" $exact_search  tabindex=\"3\" />
	<label for=\"e\">"._('Exact search')."</label>
	</p>
	</form>\n";

 
if(isset($_GET['s'])){
	
 $s=trim($vmsql->escape($_GET['s']));

 $only_visibile = ($_SESSION['user']['livello']<3) ? true:false;
// $only_visibile = true;
 
 $also_view= (bool) $_GET['view_search'];

 list($res,$stat) = search_all($s,$only_visibile,$exact,$also_view);
 
 
 if($res!=null){
 	
 	echo "<div id=\"risultati\">
 		<p>"
 		.sprintf(_('The word %s has been found in %s fields/tables:'),
 		"<strong>$ss</strong>","<strong>".count($res)."</strong>").
 		"</p>\n";
 		
 		echo "<table class=\"tab-color\" summary=\"res\" border=\"1\">";
 		echo "<tr>
 			<th>"._('table')."</th>
 			<th>"._('field')."</th>
 			<th>"._('occurrences')."</th>
 			<th>"._('see')."</th>
 			</tr>\n";
 	
 	foreach($res as $val){
 		
 		echo "<tr>\n";
 		
 		foreach($val as $v){
 			echo "<td>".$v."</td>\n";
 		}
 		
 		$oid= RegTools::name2oid($val['tabella'],$_SESSION['gid']);
 		
 		if(RegTools::is_tabella_by_oid($oid,true)){
 			
 			if(!$exact){
 				$sqs='*'.$s.'*';
 			}
 			else{
 				$sqs=$s;
 			}
 			
 			echo "<td><a href=\"./scheda.php?oid=".$oid."&amp;qs=".urlencode("dati[".$val['campo']."]")."=".$sqs."\">"._('show results')."</a></td>\n";
 		}
 		else{
 			echo "<td> - </td>\n";
 		}
 		
 		
 		
 		echo "</tr>\n";
 	}
 	
 	echo "</table></div>\n";
 	
 }
 elseif ($s==''){
 	
 	print  "<p>"._('You have not searched anything!')."</p>\n";
 }
 else{
 	
 	print  "<p>".sprintf(_('No results for %s'),"<strong>".$ss."</strong>")."</p>\n";
 }
 
}
else{

	$s='';
}

if($s!=''){
echo "<p class=\"grigio piccolo\">"
	.sprintf(_('Search on %d tables, %d fields in %01.4f seconds'),$stat['n_tabelle'],$stat['n_campi'],$stat['t'])
	."</p>\n";
}


echo closeLayout1();


?>