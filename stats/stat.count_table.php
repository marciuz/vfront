<?php
/**
* @desc Statistica di default sul popolamento delle tabelle del database
* @package VFront
* @subpackage Stats
* @author Mario Marcello Verona <marcelloverona@gmail.com>
* @copyright 2007-2010 M.Marcello Verona
* @version 0.96 $Id: stat.count_table.php 1078 2014-06-13 15:35:53Z marciuz $
* @license http://www.gnu.org/licenses/gpl.html GNU Public License
*/


require_once("../inc/conn.php");
require_once("../inc/layouts.php");
require_once("../inc/func.stat.php");
//require_once("./stat.graph.php");
require_once("./stat.graph2.php");

proteggi(2);


// PRENDI TUTTE LE TABELLE e le viste
$tabs = RegTools::prendi_tabelle(0, false, false, "table_name,table_type");


# PARAMETRI 
$i=0;
$v=0;
$scala=0;
$scala_vista=0;
$nome_file_img='tab_count';
$nome_file_viste_img='view_count';
$tabella=array();
$vista=array();

foreach($tabs as $k=>$RS){
	
	if($RS['table_type']=='BASE TABLE'){

		$tabella[$i]=$RS['table_name'];
		
		$q_count=$vmsql->query("SELECT count(*) FROM ".$RS['table_name']);
		
		list($count_tabella[$i])=$vmsql->fetch_row($q_count);
		
		$scala = ($count_tabella[$i]>$scala) ? $count_tabella[$i]:$scala;
		
		$i++;
	}
	else{
		
		$vista[$v]=$RS['table_name'];
		
		$q_count_v=$vmsql->query("SELECT count(*) FROM ".$RS['table_name']);
		
		list($count_vista[$v])=$vmsql->fetch_row($q_count_v);
		
		$scala_vista = ($count_vista[$v]>$scala) ? $count_vista[$v]:$scala_vista;
		
		$v++;
	}
	
}

// OUT

$OUT="";



// GRAFICO E TABELLA PER LE TABELLE

if(count($tabella)>0){
	
	
		$scala = $scala*1.1;
		$stima_altezza_t = round(count($tabella)*22.5,0);
		$altezza_grafico_t = (($stima_altezza_t)<280) ? 280:$stima_altezza_t;


		$T01=microtime(true);
		//$grafico = barre($count_tabella,$tabella,$scala,_('Table population'),$nome_file_img,540,$altezza_grafico_t,array('red','orange'),165,'%d','ivory');

		$T02=microtime(true)-$T01;


		$T11=microtime(true);
		$grafico = barre_pchart($count_tabella,$tabella,$scala,_('Table population'),$nome_file_img,540,$altezza_grafico_t,array('orange'),165,'%d','ivory');
		
		$T12=microtime(true)-$T11;


		// SCRIVE UNA TABELLA:
		
		$OUT.="<table summary=\"cont\" class=\"tab-cont\">\n<tr>";
		
		if($grafico){
			
			$OUT.= "<td><img src=\""._PATH_TMP_HTTP."/$nome_file_img.png\" alt=\"test\"   class=\"img-stat\" /></td>\n";
		}
		
		
		$OUT.= "<td>".stat_tabella($tabella,$count_tabella) . "</td>";
		
		$OUT.="</tr></table>\n";


}





// GRAFICO E TABELLA PER LE VISTE

if(count($vista)>0){

		$scala_v = $scala*1.1;
		$stima_altezza_v = round(count($vista)*22.5,0);
		$altezza_grafico_v = (($stima_altezza_v)<220) ? 220:$stima_altezza_v;
		
		//$grafico2 = barre($count_vista,$vista,$scala_v,_('Views population'),$nome_file_viste_img,540,$altezza_grafico_v,array('green','yellowgreen'),165,'%d','ivory');
		$grafico2 = barre_pchart($count_vista,$vista,$scala_v,_('Views population'),$nome_file_viste_img,540,$altezza_grafico_v,array('green','yellowgreen'),165,'%d','ivory');
		
		
		
		
		$OUT.="<table summary=\"cont\"  class=\"tab-cont\">\n<tr>";
		
		
		
		if($grafico2){
			
			$OUT.=  "<td><img src=\""._PATH_TMP_HTTP."/$nome_file_viste_img.png\" alt=\""._("population data")."\" class=\"img-stat\" /></td>\n";
		}
		
		
		$OUT.= "<td>".stat_tabella($vista,$count_vista) . "</td>";
		
		$OUT.="</tr></table>\n";

}


$files=array("sty/stat.css");

echo openLayout1(_("Data for populating tables"),$files);

echo breadcrumbs(array("HOME","ADMIN","index.php"=>_("statistics"),_("table population")));

echo "<h1>"._("Data for populating tables")."</h1>\n";

echo $OUT;


echo closeLayout1();



?>