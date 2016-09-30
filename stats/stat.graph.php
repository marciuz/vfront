<?php
/**
* @desc Funzioni per la generazione dei grafici
* @package VFront
* @subpackage Stats
* @author Mario Marcello Verona <marcelloverona@gmail.com>
* @copyright 2007-2010 M.Marcello Verona
* @version 0.96 $Id: stat.graph.php 819 2010-11-21 17:07:24Z marciuz $
* @license http://www.gnu.org/licenses/gpl.html GNU Public License
*/


putenv('GDFONTPATH=' . realpath('..') . '/plugins/ttf');







/**
 * Funzione per la generazione dei grafici a barre
 * Utilizza PEAR ed il pacchetto Image/Graph
 * I grafici vengono generati nella cartella tmp in formato PNG
 *
 * @param array $data
 * @param array $labels
 * @param int $scala
 * @param string $testo
 * @param string $nome_file
 * @param int $x
 * @param int $y
 * @param mixed $colore
 * @param int $left
 * @param string $format
 * @param string $colore_sfondo
 * @return bool
 */
function barre($data,$labels,$scala=5,$testo="",$nome_file="image",
				$x=490,$y=280,$colore='orange',$left=160,$format='%.2f', $colore_sfondo='ivory'){
		
	include_once 'Image/Graph.php';     
	
	$Graph =& Image_Graph::factory('graph', array($x, $y)); 

	// create the plotarea
	$Graph->add(
	    Image_Graph::vertical(
	        Image_Graph::factory('title', array($testo, 12)),        
	        Image_Graph::vertical(
	            $Plotarea = Image_Graph::factory('plotarea', array('category', 'axis', 'horizontal')),
	            $Legend = Image_Graph::factory('legend'),
	            90
	        ),
	        5
	    )
	); 
	$Grid =& $Plotarea->addNew('line_grid', array(), IMAGE_GRAPH_AXIS_Y);
	$Grid->setLineColor('black@0.1');   
	
	$Dataset =& Image_Graph::factory('dataset'); 
	
	for($i=(count($data)-1);$i>=0;$i--){

		$Dataset->addPoint($labels[$i], $data[$i]); 
	}

	$Font =& $Graph->addNew('font', 'verdana');
	$Font->setSize(8);
	
	$Graph->setFont($Font); 
	
	
	$Graph->setBackgroundColor($colore_sfondo.'@0.2'); 
	$Graph->setPadding(10); 
		
	$Fill =& Image_Graph::factory('Image_Graph_Fill_Array'); 
	$Fill->addColor('white'); 
	$Plotarea->setFillStyle($Fill);
	
	$Plot =& $Plotarea->addNew('bar', &$Dataset); 
	
	
	if(is_array($colore)){
		$Fill =& Image_Graph::factory('gradient', array(IMAGE_GRAPH_GRAD_VERTICAL, $colore[0], $colore[1]));
		$Plot->setFillStyle($Fill); 
	}
	else{
		$Fill =& Image_Graph::factory('Image_Graph_Fill_Array'); 
		$Fill->addColor($colore); 
		$Plot->setFillStyle($Fill);
	}

	
	
	
	$Graph->done( array('filename' => _PATH_TMP."/$nome_file.png") ); 
    
    
		
		if(is_file(_PATH_TMP."/$nome_file.png"))
			return true;
		else 
			return false;
	}
	
	
	
/**
 * Funzione per la generazione dei grafici a torta
 * Utilizza PEAR ed il pacchetto Image/Graph
 * Nella versione di Image/Graph 0.72 c'� un bug nella generazione delle etichette.
 * Utilizzare la versione CVS o precedente|successiva del file Image/Graph/Plot/Pie.php
 * I grafici vengono generati nella cartella tmp in formato PNG
 *
 * @param array $data
 * @param array $labels
 * @param string $testo
 * @param string $nome_file
 * @param int $x
 * @param int $y
 * @return bool
 */
function torta($data,$labels,$testo="",$nome_file="image",$x=490,$y=300){

	
	include_once 'Image/Graph.php';
	// create the graph
	$Graph =& Image_Graph::factory('graph', array($x, $y));
	

	
	
	    
	// create the plotarea
	$Graph->add(
	    Image_Graph::vertical(
	        Image_Graph::factory('title', array($testo, 12)),
	        Image_Graph::horizontal(
	            $Plotarea = Image_Graph::factory('plotarea'),
	            $Legend = Image_Graph::factory('legend'),
	            70
	        ),
	        5          
	    )
	);
   	$Legend->setPlotarea($Plotarea);
		
	$Dataset =& Image_Graph::factory('dataset'); 
	
	for($i=(count($data)-1);$i>=0;$i--){

		$Dataset->addPoint($labels[$i], $data[$i],$labels[$i]); 
	}
	
	$Plot =& $Plotarea->addNew('pie', array(&$Dataset));
	$Plotarea->hideAxis(); 
	
	
	$Font =& $Graph->addNew('font', 'verdana');
	$Font->setSize(6.5);
	
	$Graph->setFont($Font); 
	
	
	$Graph->setBackgroundColor('white'); 
	$Graph->setPadding(10); 
	
  
	
	
	
	
	
		
	// create a Y data value marker
	$Marker =& $Plot->addNew('Image_Graph_Marker_Value', IMAGE_GRAPH_PCT_Y_TOTAL);
	// create a pin-point marker type
	$PointingMarker =& $Plot->addNew('Image_Graph_Marker_Pointing_Angular', array(15, &$Marker));
	// and use the marker on the 1st plot
	$Plot->setMarker($PointingMarker);    
	// format value marker labels as percentage values
	$Marker->setDataPreprocessor(Image_Graph::factory('Image_Graph_DataPreprocessor_Formatted', '%0.1f%%'));
	
	$Plot->Radius = 2;
	
	$FillArray =& Image_Graph::factory('Image_Graph_Fill_Array');
	$Plot->setFillStyle($FillArray);
	
	for($i=0;$i<count($labels);$i++){
	
		$FillArray->addColor(generate_rand_hex_color(),$labels[$i]);
		
	}
	
	$Plot->explode(5);

	
	// Mando l'output
	$Graph->done( array('filename' => _PATH_TMP."/$nome_file.png") ); 

	if(is_file(_PATH_TMP."/$nome_file.png"))
		return true;
	else 
		return false;
		
}



/**
 * @desc Genera un seed per i numeri pseudocasuali
 * @return float
 */
function make_seed()
{
    list($usec, $sec) = explode(' ', microtime());
    return (float) $sec + ((float) $usec * 100000);
}




/**
 * @desc Genera un colore pseudocasuale in formato esadecimale
 * @return srting
 */
function generate_rand_hex_color() {
	mt_srand(make_seed());
	return "#".sprintf("%02X%02X%02X", mt_rand(0, 255), mt_rand(0, 255), mt_rand(0, 255));
}

?>