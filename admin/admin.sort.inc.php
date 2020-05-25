<?php

	######################################################################################
	#
	#	IMPOSTAZIONI CAMPI SORT
	#
	#


	$st_sub = (isset($_GET['a']) && $_GET['a']==4) ? "" : "display:none;"; 

	echo "<div class=\"campi-sort\" id=\"campi-sort\" style=\"$st_sub\">\n";


	echo "<h3>"._("Field order")."</h3>\n";

	echo "<p>"._("Through this dialogue you can change the order of table fields in the view tab. (Fields in gray are currently set as invisible.) <br /> To change the field order, select the field label and drag it to the desired position")."</p>
	 <form name=\"ripristina\" method=\"post\" action=\"rpc.sortcampi.php?ripristina\" >

	 <input type=\"hidden\" name=\"url\" value=\"".htmlentities($_SERVER['QUERY_STRING'])."\" />
	 <input type=\"hidden\" name=\"oid\" value=\"$oid\" />
	 <input type=\"button\" onclick=\"submit()\" value=\""._("Reset to default order")."\" name=\"ripristina1\" />

	 </form>\n";

	list($id_reg_sort,
		 $campi_tabella_sort,
		 $in_visibile_sort,
		 $data_type,
		 $in_tipo,
		 $maxsize,
		 $in_line) = RegTools::prendi_colonne_frontend($oid,"id_reg,
		 										   column_name,
		 										   in_visibile,
		 										   data_type,
		 										   in_tipo,
		 										   character_maximum_length,
		 										   in_line",true);


	$next=1;
	$i=0;
	$li=array(1=>array(),2=>array());

	foreach((array) $campi_tabella_sort as $k=>$campo){

		$tipo = ($in_tipo[$i]=='' || $in_tipo[$i]==null) ? $data_type[$i] : $in_tipo[$i];

		$classe_sort = ($in_visibile_sort[$k]=='1') ? "campi-float-blu":"campi-float-grigio";

		if($in_line[$i]!=''){

			if(intval($in_line[$i])===0){
				$li[1][]=array($id_reg_sort[$i],$campi_tabella_sort[$i],$tipo);
				$li[2][]=null;
				$next=1;
			}
			else{

				if($next==1){
					$li[1][]=array($id_reg_sort[$i],$campi_tabella_sort[$i],$tipo);
					$next=2;
				}
				else{
					$li[2][]=array($id_reg_sort[$i],$campi_tabella_sort[$i],$tipo);
					$next=1;
				}
			}
		}
		else{

			if( (($tipo=='varchar' || $tipo=='char') && $maxsize>100)
				|| ($tipo=='text' || $tipo=='mediumtext' || $tipo=='longtext')
				|| ($tipo=='richtext')
				|| ($tipo=='select_from')
                || (strtolower($tipo)=='varchar2')
				|| ($in_visibile_sort[$i]==0)
				){

				$li[1][]=array($id_reg_sort[$i],$campi_tabella_sort[$i],$tipo);
				$li[2][]=null;
				$next=1;			
			}
			else{
				if($next==1){
					$li[1][]=array($id_reg_sort[$i],$campi_tabella_sort[$i],$tipo);
					$next=2;
				}
				else{
					$li[2][]=array($id_reg_sort[$i],$campi_tabella_sort[$i],$tipo);
					$next=1;
				}
			}
		}

		$i++;
	}


	echo "<p id=\"list-info\" >&nbsp;</p>";

	?>

	<div class="colonna-cont">
			<div class="colonna">
			<ul class="sortabledemo" id="firstlist">

			<?php 

			foreach ($li[1] as $l){

				echo "\t\t<li class=\"item\" id=\"li1_".$l[0]."\">
					<div class=\"handle\">".$l[1]."</div> ".$l[2]."
				</li>
				";

			}
			?>
			</ul>
		</div>
		<div class="colonna">
			<ul class="sortabledemo" id="secondlist">
				<?php 

				foreach ($li[2] as $l){

				if($l==null){ echo "\t\t<li class=\"item-null\" >&nbsp;</li>\n"; }
				else{

					echo "\t\t<li class=\"item\" id=\"li2_".$l[0]."\">
						<div class=\"handle\">".$l[1]."</div> ".$l[2]."
					</li>
					";	
				}
			}
			?>
			</ul>
		</div>
	</div>

	 <script type="text/javascript">
	 // <![CDATA[

	   Sortable.create("firstlist",
	   	    {dropOnEmpty:true,
		      containment:["firstlist","secondlist"],
		      constraint:false,
		      onUpdate:function(){
		     		new Ajax.Updater('list-info',"rpc.sortcampi.php?getsort",
		     			{
		     				method:'post',
		     				postBody: Sortable.serialize('firstlist')+"&"+Sortable.serialize('secondlist')+"&oid=<?php echo $oid;?>",
		     				evalScripts:true, 
		     				asynchronous:true
		     			});
		     			setTimeout("$('list-info').innerHTML = '&nbsp;'; ", 3000 );
		     		}
		      });


	   Sortable.create("secondlist",
	   	    {dropOnEmpty:true,
		      containment:["firstlist","secondlist"],
		      constraint:false,
		      onUpdate:function(){
		     		new Ajax.Updater('list-info',"rpc.sortcampi.php?getsort",
		     			{
		     				method:'post',
		     				postBody: Sortable.serialize('firstlist')+"&"+Sortable.serialize('secondlist')+"&oid=<?php echo $oid;?>",
		     				evalScripts:true, 
		     				asynchronous:true
		     			});
		     			setTimeout("$('list-info').innerHTML = '&nbsp;'; ", 3000 );
		     		}
		      });

	 // ]]>
	 </script>

 	<?php

	echo "</div>\n";



	//-- fine campi-sort
