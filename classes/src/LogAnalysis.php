<?php


class LogAnalysis {

	private $firstLogMonth;

	public $container = '';

	public $possible_k=array("update","delete","ripristino","insert","import");

	public $possible_months=array();

	private $dir_pchart;

	public $datef = "M Y";

	function  __construct($startM=null,$endM=null) {

		global  $vmsql, $vmreg, $db1;

		$this->dir_pchart=FRONT_ROOT."/plugins/pchart";

		if($startM!==null && preg_match("|[0-9]{4}-[0-9]{2}|",$startM)){
			$this->firstLogMonth=$startM;
		}
		else{
			$q=$vmreg->query("SELECT MIN(data) FROM {$db1['frontend']}{$db1['sep']}log");

			if($vmreg->num_rows($q)>0){

				list($first)=$vmreg->fetch_row($q);

				$this->firstLogMonth=$this->ym($first);
			}
			else{
				$this->firstLogMonth=null;
			}
		}

	}


	private function month_diff($max,$min){

		if($min===null){
			return null;
		}

		list($y_max,$m_max)=explode("-",$max);
		list($y_min,$m_min)=explode("-",$min);

		$mm = ($y_max-$y_min)*12;

		$mm+= $m_max-$m_min;

		return $mm;
	}

	function act_create_matrix_month($mesi=null){

		if($mesi===null){

			$mesi = $this->month_diff(date("Y-m"), $this->firstLogMonth);
		}

		$this->container = new stdClass();

		$time_first_month = strtotime("-".($mesi)." month");
		$first_month = date("Y-m",$time_first_month);
		$last_month = date("Y-m");


		$this->container->startData=$first_month;
		$this->container->endData=$last_month;

		$this->possible_months=array();

		for($i=0;$i<count($this->possible_k);$i++){

			$this->container->{$this->possible_k[$i]}=array();

			$next_date=$first_month;

			for($m=1;$m<=$mesi;$m++){

				$mmm = strtotime("+$m month",$time_first_month);

				if($i==0)	$this->possible_months[]=date("Y-m",$mmm);

				$actual_month=date($this->datef,$mmm);

				$this->container->{$this->possible_k[$i]}[$actual_month]=0;
			}

		}

		$this->act_gen_for_month();

		return $this->container;

	}


	function act_create_matrix_days($mese=null){

		if($mese===null){

			$mese = date("Y-m");
		}

		$this->container = new stdClass();

		for($i=0;$i<count($this->possible_k);$i++){

			$this->container->{$this->possible_k[$i]}=array();

			$next_date=$first_month;

			for($m=0;$m<date("t",strtotime($mese));$m++){

				$actual_day=date("d",strtotime("+$m days",$mese));

				$this->container->{$this->possible_k[$i]}[$actual_day]=0;
			}

		}

		$this->act_gen_for_days($mese);

		return $this->container;

	}

	private function ym($data){
		return substr($data,0,7);
	}

	private function ymd($data){
		return substr($data,0,10);
	}

	private function act_gen_for_month($ym=''){

		global $db1, $vmsql, $vmreg;

		$add_sql= ($ym=='') ? '' : "WHERE data>='$ym-01'";

		$sql="
			SELECT op, data ym, COUNT(id_log) n
		FROM {$db1['frontend']}{$db1['sep']}log
		$add_sql
		GROUP BY op, ym
		ORDER BY ym ASC, op";

		$q=$vmreg->query($sql);

		if($vmreg->num_rows($q)>0){
			$mat = $vmreg->fetch_assoc_all($q);
		}
		else{
			$mat=array();
		}

		foreach($mat as $k=>$RS){

			$RS['ym2']=date($this->datef,  strtotime($this->ym($RS['ym'])));

			if(isset($this->container->{$RS['op']}[$RS['ym2']])){

				$this->container->{$RS['op']}[$RS['ym2']]+=$RS['n'];
				continue;
			}
		}
	}

	private function act_gen_for_days($ym=''){

		global $db1, $vmsql, $vmreg;

		$sql="
			SELECT op, data ymd, COUNT(id_log) n
		FROM {$db1['frontend']}{$db1['sep']}log
		WHERE LEFT(data,7)='$ym'
		GROUP BY op, ymd
		ORDER BY ymd ASC, op";

		$q=$vmreg->query($sql);

		$mat = $vmreg->fetch_assoc_all($q);

		foreach($mat as $k=>$RS){

			$RS['ym2']=date("d",  strtotime($this->ymd($RS['ymd'])));

			if(isset($this->container->{$RS['op']}[$RS['ym2']])){

				$this->container->{$RS['op']}[$RS['ym2']]+=$RS['n'];
				continue;
			}
		}
	}


	public function ___graph_log_activities($type='month',$mesi=null){

		  // Standard inclusions
		  include_once($this->dir_pchart."/class/pData.class");
		  include_once($this->dir_pchart."/class/pDraw.class");
		  include_once($this->dir_pchart."/class/pImage.class");

		  $T0=microtime(true);

		  if($type=='month'){
			  $obj=$this->act_create_matrix_month($mesi);
		  }
		  else if($type=='days'){
			  $obj=$this->act_create_matrix_days($mesi);
		  }

		  // Dataset definition
		  $DataSet = new pData;

		  $k=1;



		  foreach($this->possible_k as $operation){

				$DataSet->addPoints(array_values($obj->{$operation}),"Serie$k");

				$DataSet->SetSerieName($operation,"Serie$k");

				$k++;
		  }


		  $DataSet->addPoints(array_keys($obj->update),"SerieX");
		  $DataSet->SetAbsciseLabelSerie("SerieX");

		  $DataSet->SetYAxisName("Operation");
		  //$DataSet->SetYAxisUnit("µs");

		  if($type=='days'){
			  $DataSet->SetXAxisName(date("M Y",strtotime($mesi)));
		  }


		  // Initialise the graph
		  $Test = new pChart(700,240);
		  $Test->setFontProperties($this->dir_pchart."/fonts/tahoma.ttf",8);
		  $Test->setGraphArea(65,30,585,185);
		  $Test->drawFilledRoundedRectangle(7,7,693,233,5,255,248,239);
		  $Test->drawRoundedRectangle(5,5,695,235,5,230,240,240);
		  $Test->drawGraphArea(255,255,255,TRUE);
		  $Test->drawScale($DataSet->GetData(),$DataSet->GetDataDescription(),SCALE_NORMAL,150,150,150,TRUE,0,2);
		  $Test->drawGrid(4,TRUE,230,230,230,50);

		  // Draw the 0 line
		  $Test->setFontProperties("Fonts/tahoma.ttf",6);
		  $Test->drawTreshold(0,143,55,72,TRUE,TRUE);

		  $DataSet->RemoveSerie("SerieX");

		// Cache definition
		//$Cache = new pCache();
		//$Cache->GetFromCache("Graph1",$DataSet->GetData());



		  // Draw the line graph
		  $Test->drawLineGraph($DataSet->GetData(),$DataSet->GetDataDescription());
		  $Test->drawPlotGraph($DataSet->GetData(),$DataSet->GetDataDescription(),3,2,255,255,255);

		  // Finish the graph
		  $Test->setFontProperties($this->dir_pchart."/fonts/tahoma.ttf",8);
		  $Test->drawLegend(605,30,$DataSet->GetDataDescription(),255,255,255);
		  $Test->setFontProperties($this->dir_pchart."/fonts/tahoma.ttf",10);
		  $Test->drawTitle(60,22,"DB activities",50,50,50,585);



		  $name="activities_".md5(microtime(true)).".png";

		  //$Test->Render($name);


		  $Cache->WriteToCache("Graph1",$DataSet->GetData(),$Test);

		  $T=microtime(true)-$T0;

		  //echo $T;

		  return $name;

	}


	public function graph_act($type='month',$mesi=null, $W=847, $H=304){

		 include($this->dir_pchart."/class/pData.class");
		 include($this->dir_pchart."/class/pDraw.class");
		 include($this->dir_pchart."/class/pImage.class");

		  $T0=microtime(true);

		  if($type=='month'){
			  $obj=$this->act_create_matrix_month($mesi);
		  }
		  else if($type=='days'){
			  $obj=$this->act_create_matrix_days($mesi);
		  }



		  // Dataset definition
		  $MyData = new pData();

		  $k=1;



		  foreach($this->possible_k as $operation){

				$MyData->addPoints(array_values($obj->{$operation}),"$operation");

				$k++;
		  }


		  $lab_mesi=array_keys($obj->update);


		 $MyData->setAxisName(0,_("Log entries"));
		 $MyData->addPoints($lab_mesi,"Labels");
		 $MyData->setSerieDescription("Labels","Months");
		 $MyData->setAbscissa("Labels");


		 /* Create the pChart object */
		 $myPicture = new pImage($W,$H,$MyData);
		 $myPicture->drawGradientArea(0,0,$W,$H,DIRECTION_VERTICAL,array("StartR"=>240,"StartG"=>240,"StartB"=>255,"EndR"=>27,"EndG"=>27,"EndB"=>27,"Alpha"=>100));
		 $myPicture->drawGradientArea(0,$H,$W,$H,DIRECTION_VERTICAL,array("StartR"=>47,"StartG"=>47,"StartB"=>47,"EndR"=>27,"EndG"=>27,"EndB"=>27,"Alpha"=>100));
		 //$myPicture->drawLine(0,249,$W,$H,array("R"=>255,"G"=>253,"B"=>245));
		// $myPicture->drawLine(0,250,$W,$H,array("R"=>70,"G"=>70,"B"=>70));

		 /* Add a border to the picture */
		 $myPicture->drawRectangle(0,0,($W-1),($H-1),array("R"=>204,"G"=>204,"B"=>204));

		 /* Write the picture title */
		 $myPicture->setFontProperties(array("FontName"=>$this->dir_pchart."/fonts/verdana.ttf","FontSize"=>12));
		 $myPicture->drawText(423,14,_("VFront activities"),array("R"=>0,"G"=>0,"B"=>0,"Align"=>TEXT_ALIGN_MIDDLEMIDDLE));

		 /* Define the chart area */
		 $myPicture->setGraphArea(58,27,($W-30),($H-30));

		 /* Draw a rectangle */
		 $myPicture->drawFilledRectangle(58,27,($W-30),($H-30),array("R"=>255,"G"=>255,"B"=>255,"Dash"=>TRUE,"DashR"=>239,"DashG"=>239,"DashB"=>239,"BorderR"=>0,"BorderG"=>0,"BorderB"=>0));

		 /* Turn on shadow computing */
		$myPicture->setShadow(TRUE,array("X"=>1,"Y"=>1,"R"=>255,"G"=>255,"B"=>255,"Alpha"=>20));


		 /* Draw the scale */
		 $myPicture->setFontProperties(array("FontName"=>$this->dir_pchart."/fonts/Forgotte.ttf","FontSize"=>9));
		 $myPicture->setGraphArea(50,60,($W-60),($H-80));
		 $myPicture->drawFilledRectangle(50,60,($W-60),($H-80),array("R"=>240,"G"=>240,"B"=>240,"Surrounding"=>-200,"Alpha"=>40));
		 $myPicture->drawScale(array("DrawSubTicks"=>TRUE,"R"=>0,"G"=>0,"B"=>0));

		 /* Draw a treshold area */
		 $myPicture->drawThresholdArea(5,17,array("R"=>180,"G"=>229,"B"=>11,"Alpha"=>12));

		 /* Draw the data series */
		 $myPicture->setFontProperties(array("FontName"=>$this->dir_pchart."/fonts/pf_arma_five.ttf","FontSize"=>6));
		 $myPicture->drawSplineChart();
		 $myPicture->setShadow(FALSE);

		 /* Write the legend */
		 $myPicture->drawLegend(($W-310),50,array("Style"=>LEGEND_NOBORDER,"Mode"=>LEGEND_HORIZONTAL));

		 $position = md5(time()).".png";
		 /* Render the picture (choose the best way) */
		 $myPicture->Render(_PATH_ATTACHMENT_TMP."/$position");

		 return _PATH_TMP_HTTP."/$position";
	}


	public function table_cont($cont){

		$out="<table class=\"tab-stat\" id=\"cont-stat-log\" summary=\""._("VFront activities")."\" >\n";

		$lab=array_keys($cont->{$this->possible_k[0]});

		// Labels
		$out.="<tr>";
		$out.="<th>&nbsp;</th>\n";

		foreach($lab as $l){

			$out.="<th>".$l."</th>\n";
		}

		$out.="</tr>\n";

		// Values
		foreach($this->possible_k as $kk){
			
			static $i=0;

			$cl=($i%2==0) ? "c1":"c2";

			$out.="<tr class=\"$cl\"><th>"._($kk)."</th>";
			foreach($cont->{$kk} as $v) {

				$class=($v==0) ? " style=\"color:#666\"" : '';
				$out.="<td{$class}>".$v."</td>\n";
			}
			$out.="</tr>\n";

			$i++;
		}

		$out.="</table>\n";

		return $out;
	}


}
