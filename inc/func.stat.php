<?php
/**
* Libreria di funzioni a supporto delle statistiche. 
* Vengono utilizzate dai file della cartella /stats
* 
* @package VFront
* @subpackage Function-Libraries
* @author Mario Marcello Verona <marcelloverona@gmail.com>
* @copyright 2007-2010 M.Marcello Verona
* @version 0.96 $Id: func.stat.php 828 2010-11-25 15:21:44Z marciuz $
* @license http://www.gnu.org/licenses/gpl.html GNU Public License
*/




/**
 * Generatore delle tabelle riassuntive per le statistiche
 *
 * @param array $eti Array etichette
 * @param array $dati Array dati
 * @param array $th Intetazioni di colonna
 * @return string HTML
 */
function stat_tabella($eti,$dati,$th=array('tabella','record'),$set_perc=false){
	
	$TAB="<table summary=\""._("Statistics table")."\" class=\"tab-stat\">\n";
	
	
	$TOT=array_sum($dati);
	
	$add_label='';
	
	if($set_perc){
		
		$add_label='<th>Perc</th>';
	}

	$TAB.="
		<tr>
			  <th>".$th[0]."</th>
			  <th>".$th[1]."</th>
			  $add_label
		</tr>";
	
	for($i=0;$i<count($eti);$i++){

		$c=($i%2==0) ? "c1":"c2";

		$TAB.= "
		<tr class=\"$c\">\n<td>".$eti[$i]."</td>
			  <td>".$dati[$i]."</td>";
		
		if($set_perc) $TAB.="<td>".round($dati[$i]/$TOT*100,1)."%</td>";
			 
		$TAB.= "</tr>\n";
	
	}
	
	$TAB.="</table>";
	
	
	return $TAB;
}



/**
 * Formula per il calcolo delle percentuali
 *
 * @param int $n Numero dato
 * @param int $tot Totale
 * @param int $round Decimali per l'arrotondamento
 * @return float
 */
function percento($n,$tot,$round=2){
	
	return round($n/$tot*100,$round);
	
}



/**
 * Funzione per il calcolo della deviazione standard
 * Dato un array $x ed un arrotondamento restituisce un numero con virgola
 *
 * @param array $x Array con i valori per cui calcolare la deviazione standard
 * @param int $arrotondamento Decimali per l'arrotondamento
 * @return float
 */
function deviazione_standard($x,$arrotondamento){

	 $n=count($x);
	 $somma_x=array_sum($x);
	 foreach($x as $valore){
	  $somma_x_pow +=pow($valore,2);
	  }
	 $pow_somma_x=pow($somma_x,2);
	
	 $dev=sqrt((($n * $somma_x_pow) - $pow_somma_x ) /   ($n*($n-1)));
	 $dev=round($dev,$arrotondamento);
	 return $dev;

} 

?>