<?php
require_once("../inc/conn.php");
include_once("../plugins/pchart/class/pData.class");
include_once("../plugins/pchart/class/pDraw.class");
include_once("../plugins/pchart/class/pPie.class");
include_once("../plugins/pchart/class/pImage.class");

define('FRONT_FONT_LIB',FRONT_ROOT."/plugins/pchart/fonts");



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
function barre_pchart($data,$labels,$scala=5,$testo="",$nome_file="image",
				$x=490,$y=280,$colore='orange',$left=160,$format='%.2f', $colore_sfondo='ivory'){


	$MyData = new pData();
	$MyData->addPoints($data,"Records");
	//$MyData->setAxisName(0,"Records");

	if(is_array($colore)) $colore = $colore[0];

	$MyData->setPalette("Records",get_graph_color_array($colore));

	$MyData->addPoints($labels,"Tables");
	$MyData->setSerieDescription("Tables","");
	$MyData->setAbscissa("Tables");

	$myPicture = new pImage($x,$y,$MyData);

	$myPicture->drawGradientArea(0,0,$x,$y,DIRECTION_VERTICAL,array("StartR"=>255,"StartG"=>255,"StartB"=>247,"EndR"=>247,"EndG"=>247,"EndB"=>242,"Alpha"=>100));
	$myPicture->setFontProperties(array("FontName"=>FRONT_FONT_LIB."/verdana.ttf","FontSize"=>7.6));

	$len_testo=strlen($testo);

	if($len_testo>50) $fontSizeTitle=13;
	else if($len_testo>40) $fontSizeTitle=14;
	else  $fontSizeTitle=16;

	$myPicture->drawText(round($x/2),10,$testo,array("FontSize"=>$fontSizeTitle,"Align"=>TEXT_ALIGN_TOPMIDDLE));

	$myPicture->setGraphArea(140,60,($x-20),($y-20));
	$myPicture->drawGradientArea(140,60,($x-20),($y-20),DIRECTION_VERTICAL,array("StartR"=>255,"StartG"=>255,"StartB"=>255,"EndR"=>255,"EndG"=>255,"EndB"=>254,"Alpha"=>100));

	$myPicture->drawScale(array("Pos"=>SCALE_POS_TOPBOTTOM,"CycleBackground"=>TRUE,"DrawSubTicks"=>TRUE,
							"GridR"=>0,"GridG"=>0,"GridB"=>0, "GridAlpha"=>5));

	//$myPicture->setShadow(TRUE,array("X"=>1,"Y"=>1,"R"=>0,"G"=>0,"B"=>0,"Alpha"=>10));

	$myPicture->drawBarChart(array("DisplayPos"=>LABEL_POS_INSIDE,"DisplayValues"=>TRUE,"Rounded"=>FALSE,"Surrounding"=>false));

	//$myPicture->drawLegend(370,215,array("Style"=>LEGEND_NOBORDER,"Mode"=>LEGEND_HORIZONTAL));

	$myPicture->Render(_PATH_TMP."/$nome_file.png");



		if(is_file(_PATH_TMP."/$nome_file.png"))
			return true;
		else
			return false;
	}



function torta_pchart($data,$labels,$testo="",$nome_file="image",$x=490,$y=300){


	/* Create and populate the pData object */
	$MyData = new pData();
	$MyData->addPoints($data,"Records");
	$MyData->setSerieDescription("Records","Application A");

	/* Define the absissa serie */
	$MyData->addPoints($labels,"Labels");
	$MyData->setAbscissa("Labels");

	/* Create the pChart object */
	$myPicture = new pImage($x,$y,$MyData);
	$myPicture->drawGradientArea(0,0,$x,$y,DIRECTION_VERTICAL,array("StartR"=>255,"StartG"=>255,"StartB"=>247,"EndR"=>255,"EndG"=>255,"EndB"=>246,"Alpha"=>100));
	$RectangleSettings = array("R"=>180,"G"=>180,"B"=>180,"Alpha"=>100);

	/* Set the default font properties */
	$myPicture->setFontProperties(array("FontName"=>FRONT_FONT_LIB."/verdana.ttf","FontSize"=>10,"R"=>80,"G"=>80,"B"=>80));

	$MyData->loadPalette("../plugins/pchart/vfront01.palette.txt",TRUE);
	
	/* Create the pPie object */
	$PieChart = new pPie($myPicture,$MyData);

	/* Define the slice color */


	$pie_array=array("SecondPass"=>TRUE,"Radius"=>110,"DataGapAngle"=>4,"DataGapRadius"=>3);
	$pie_array['DrawLabels']=FALSE;
	$pie_array['LabelR']=50;
	$pie_array['LabelG']=50;
	$pie_array['LabelB']=50;
	$pie_array['LabelAlpha']=10;

	$myPicture->setFontProperties(array("FontName"=>FRONT_FONT_LIB."/verdana.ttf","FontSize"=>7));

	$len_testo=strlen($testo);
	
	if($len_testo>50) $fontSizeTitle=13;
	else if($len_testo>40) $fontSizeTitle=14;
	else  $fontSizeTitle=16;

	$myPicture->drawText(round($x/2),10,$testo,array("FontSize"=>$fontSizeTitle,"Align"=>TEXT_ALIGN_TOPMIDDLE));

	/* Draw a simple pie chart */
	$PieChart->draw2DPie(170,159,$pie_array);

	/* Enable shadow computing */
	$myPicture->setShadow(TRUE,array("X"=>3,"Y"=>3,"R"=>0,"G"=>0,"B"=>0,"Alpha"=>10));



	$legend=array("R"=>255,"G"=>255,"B"=>254);
	$legend['BorderR']=220;
	$legend['BorderG']=220;
	$legend['BorderB']=220;
	$legend['FontName']=FRONT_FONT_LIB."/verdana.ttf";

	 /* Write down the legend next to the 2nd chart*/
	$PieChart->drawPieLegend(350,53,$legend);

	/* Write the legend */

	$myPicture->Render(_PATH_TMP."/$nome_file.png");

	if(is_file(_PATH_TMP."/$nome_file.png"))
		return true;
	else
		return false;
		

}












function get_graph_color_array($name='orange'){

	switch($name){
		case 'orange': $a=array("R"=>255,"G"=>134,"B"=>3); break;
		case 'red': $a=array("R"=>255,"G"=>0,"B"=>0); break;
		case 'green': $a=array("R"=>104,"G"=>191,"B"=>0); break;
		default : $a=array("R"=>255,"G"=>134,"B"=>3); break;
	}


	return $a;
}







function get_time($label=''){

	$GLOBALS['T'][]=microtime(true);

}
