<?php

#################################################
#
#	 This file is part of VFront.
#
#    VFront is free software; you can redistribute it and/or modify
#    it under the terms of the GNU General Public License as published by
#    the Free Software Foundation; either version 2 of the License, or
#    any later version.
#
#    VFront is distributed in the hope that it will be useful,
#    but WITHOUT ANY WARRANTY; without even the implied warranty of
#    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
#    GNU General Public License for more details.
#
#    You should have received a copy of the GNU General Public License
#    along with this program.  If not, see <http://www.gnu.org/licenses/>.
#

/**
 * VFront Web Installer - Utility di installazione dell'applicazione VFront 
 * Caratteristiche richieste: PHP5.x , MySQL 5.x, php_mysqli 
 * Oppure: PHP5.x , Postgres 8.x, php_pgsql
 * @package VFront
 * @subpackage VFront_Import
 * @author M.Marcello Verona
 * @copyright 2007-2010 M.Marcello Verona
 * @version 0.96 $Id: import.php 1170 2017-05-12 18:06:01Z marciuz $
 * @license http://www.gnu.org/licenses/gpl.html GNU Public License 
 */

require_once("./inc/conn.php");
require_once("./inc/layouts.php");

proteggi(1);


//workaround for 4th param.
function vf_fgetcsv($fp, $lenght=null, $sep=null, $quote=null){

	if($quote==null){
		return fgetcsv($fp, $lenght, $sep);
	}
	else{
		return fgetcsv($fp, $lenght, $sep, $quote);
	}
}


function controllo_tipo($dato,$tipo,$is_nullable){

	global  $vmsql, $vmreg;

	if(($dato=='' || $dato=='NULL') && $is_nullable=='YES'){

		return "NULL,";
	}

	else if(in_array($tipo,array('int','mediumint','tinyint','bigint','smallint'))){

		return (int) $dato.",";
	}

	else if(in_array($tipo,array('double','float'))){

		return (double) $dato.",";
	}

	else {

		return "'".$vmsql->escape($dato)."',";
	}


}


function modificatori($input,$i,$post_data){


	if(isset($post_data['mod'][$i])){


		foreach($post_data['mod'][$i] as $k=>$mod){

			switch ($mod){

				case 'upper': $input=strtoupper($input); break;
				case 'lower': $input=strtolower($input); break;
				case 'upperfirst': $input=ucfirst($input); break;
				case 'upperword': $input=ucwords($input); break;
				case 'md5': $input=md5($input); break;
				case 'sha1': $input=sha1($input); break;
				case 'prefisso': $input=$post_data['pref'][$i].$input; break;
			}
		}
	}

	return $input;
}


function errors_mysql($n){

	switch($n){

		case 1062: return _('Duplicate key'); 
		case 1364: return _('A record which is specified as "not null" doesn\'t have a default value');
		case 1471: return _('Table not writable: file permissions error'); 
		case 1146: return _('Non-existent table: error'); 
		case 1022: return _('Can\'t add record - Duplicate key');
		case 1452: return _('Unable to add a record - The reference is missing and/or not connected to the reference table.');
		default : return "";
	}
}


// STEP 0
// SELECT AND UPLOAD SOURCE CSV FILE


function import_step0(){

	global $oid;

	$OUT='';

	echo openLayout1(_("Data import"),array(),'popup');

	echo "<h1>".sprintf(_('Data import - Step %d of %d'),1,4)."</h1>\n";

	echo "<p>"._('Choose a CSV (Comma Separated Value) file to load. <br /> To generate a csv file upload a file to excel or open office, choose \'Save as\' and set the CSV')."</p>\n";

	if(intval($oid)>0 && RegTools::is_tabella_by_oid($oid)){

		$OUT.="<div id=\"form-container\">
			<form enctype=\"multipart/form-data\" method=\"post\" action=\"" . Common::phpself() . "?step=1&amp;oid=$oid\" >

			<p id=\"csvfile-box\">
			<label for=\"csvfile\">"._('csv File ').":</label><br />
			<input type=\"file\" name=\"csvfile\" id=\"csvfile\" size=\"60\" />
			<input type=\"hidden\" name=\"oid\" id=\"oid\" value=\"".intval($oid)."\" />
			</p>

			<p><input type=\"submit\" id=\"send\" value=\"  "._('Next')."  &gt;&gt; \" /></p>

			</form>
		</div>
		";

	}
	else{

		echo "<p>"._('Warning! Non-existent table')."</p>\n";
	}


	echo $OUT;

	echo closeLayout1();

}


// STEP 1
// UPLOAD FILE


function import_step1(){

	// salva il file e vai oltre
	$nome_file=md5($_FILES['csvfile']['tmp_name']).".csv";
	$test = move_uploaded_file($_FILES['csvfile']['tmp_name'],_PATH_TMP."/".$nome_file);

	if($test) {
		$_SESSION['import']['filename']=$nome_file;
		$_SESSION['import']['oid']=intval($_POST['oid']);
//		$_SESSION['import']['quote']=$_POST['quote'];
//		$_SESSION['import']['sep']=$_POST['sep'];

		header("Location: ".$_SERVER['PHP_SELF']."?step=2");
	}
	else header("Location: ".$_SERVER['PHP_SELF']."?feed=nofile");
	exit;
}


function import_step1_options(){


		$_SESSION['import']['quote']=$_POST['quote'];
		$_SESSION['import']['sep']=$_POST['sep'];

		header("Location: ".$_SERVER['PHP_SELF']."?step=2");
	exit;
}


// STEP 2
// SHOW OPTIONS AND PREVIEW


function import_step2(){

	global  $vmsql, $vmreg, $db1;

	$nome_tab=RegTools::oid2name($_SESSION['import']['oid']);

	// prendi i campi disponibili per la tabella
	$campi_tab=RegTools::prendi_colonne_frontend($nome_tab,"column_name",false);


	$opzioni="<option value=\"\">"._('No such field')." ----</option>\n";

	for($i=0;$i<count($campi_tab[0]);$i++){
		$opzioni.="<option value=\"".$campi_tab[0][$i]."\">-&gt;".$campi_tab[0][$i]."</option>\n";
	}


	// CONTA LE RIGHE
	$handle = fopen(_PATH_TMP."/".$_SESSION['import']['filename'],'r');
	$x=0;
	while (!feof($handle)) {
	    $buffer = fgets($handle,16384);

	    if($buffer!="\n") $x++;
	}
	fclose($handle);

	$_SESSION['import']['length']=($x-1);


	$fp=fopen(_PATH_TMP."/".$_SESSION['import']['filename'],'r');

	$row=1;

	$T= "<table summary=\"csv\" id=\"csv-table\" >\n";


	if(isset($_SESSION['import']['sep'])){

		// SEPARATORE
		switch($_SESSION['import']['sep']){

			case ';': $sep=';'; break;
			case ',': $sep=','; break;
			case 'tab': $sep="\t"; break;
			case 'space': $sep=" "; break;
			default: $sep=';';
		}

	} 
	else{
		$sep=';';
	}

	if(isset($_SESSION['import']['quote'])){

		switch($_SESSION['import']['quote']){

			case 'single':	$quote="'"; break;
			case 'double': 	$quote='"'; break;
			case 'null': 		$quote= null; break;
			default: 		$quote= null;
		}
	}
	else{

		$quote= null;
	}


	while (($CSV = vf_fgetcsv($fp, null, $sep, $quote)) !== FALSE) {

	    $num = count($CSV);


		if($row==1){

			$T.="<tr>\n";

			for ($c=0; $c < $num; $c++) {
				$T.= "<th class=\"int$c\"><select name=\"i[$c]\" id=\"i$c\" onchange=\"colora_riga($c);\">$opzioni</select></th>\n";
			}
	        $T.="</tr>\n";

	        $T.="<tr id=\"tr-sep\"><td colspan=\"".($num)."\">&nbsp;</td></tr>\n";
	    }


	    $T.="<tr class=\"r$row\">\n";

	    for ($c=0; $c < $num; $c++) {
	         $T.= "<td class=\"c$c\">".$CSV[$c]."</td>\n";
	    }

	     $T.= "</tr>\n";

	    $row++;


	    if($row>=20) break;
	}

	fclose($fp);

	 $T.= "</table>\n";


	 $files=array("js/scriptaculous/lib/prototype.js","sty/import.css","js/import.js");

	 echo openLayout1(_("Data import"),$files,'popup');

	 echo "<h1>"._('Import data into the table')." <span class=\"var\">".$nome_tab."</span></h1>\n";


	 echo "<form action=\"" . Common::phpself() . "?step=11\" method=\"post\">\n";

	 $sepr[0]=($sep==';') ? ' selected="selected"' : '';
	 $sepr[1]=($sep==',') ? ' selected="selected"' : '';
	 $sepr[2]=($sep=='tab') ? ' selected="selected"' : '';
	 $sepr[3]=($sep=='space') ? ' selected="selected"' : '';

	 $quot[0]=($quote=='double') ? ' selected="selected"' : '';
	 $quot[1]=($quote=='single') ? ' selected="selected"' : '';
	 $quot[2]=($quote=='null' || $quote==null) ? ' selected="selected"' : '';

	 echo "<p>
			<label for=\"sep\">"._('Separator')."</label>
			<select id=\"sep\" name=\"sep\">
				<option value=\";\"{$sepr[0]}>; </option>
				<option value=\",\"{$sepr[1]}>,</option>
				<option value=\"tab\"{$sepr[2]}>TAB</option>
				<option value=\"space\"{$sepr[3]}>SPACE</option>
			</select>


			<label for=\"quote\">"._('Quote')."</label>
			<select id=\"quote\" name=\"quote\">
				<option value=\"double\"{$quot[0]}>&quot;</option>
				<option value=\"single\"{$quot[1]}>'</option>
				<option value=\"null\"{$quot[2]}> </option>
			</select>

			<input type=\"button\" onclick=\"submit()\" value=\""._('Change csv options')."\" />

		</p>\n";

	 echo "</form>\n";

	 echo "<form action=\"" . Common::phpself() . "?step=21\" method=\"post\">\n";

	 echo "<p><input type=\"checkbox\" id=\"prima_riga\" name=\"prima_riga\" value=\"1\" onclick=\"colora_prima_riga();\" /> <label for=\"prima_riga\">"._('Skip the first line')."</label> </p>\n";

	 echo "<p>".sprintf(_('Data Preview: displays rows %s of % s'),"<b>$row</b>","<b>".$_SESSION['import']['length']."</b>")."</p>\n";

	 echo $T;

	 echo "<p><input type=\"button\" onclick=\"history.back()\" value=\"&lt;&lt; "._('Previous')."\" />   <input type=\"button\" onclick=\"submit()\" value=\""._('Next')." &gt;&gt;\" /></p>\n";

	 echo "</form>\n";


	 echo closeLayout1();

}


function import_step2_ins(){

	$_da_processare='';
	$campi='';

	// prendi i campi buoni
	for($j=0;$j<count($_POST['i']);$j++){
		if($_POST['i'][$j]!=''){

			$_da_processare[]=$j;

			$campi.=$_POST['i'][$j].",";
		}
	}

	$_SESSION['import']['campi_da_processare']=$_da_processare;
	$_SESSION['import']['nome_campi']=substr($campi,0,-1);
	$_SESSION['import']['prima_riga']=$_POST['prima_riga'];

	header("Location: ".$_SERVER['PHP_SELF']."?step=3");
	exit;

}


// STEP 2 
// IMPORT DATA


function import_step3(){

	global  $vmsql, $vmreg, $db1;

	$nome_tab=RegTools::oid2name($_SESSION['import']['oid']);

	// prendi i campi disponibili per la tabella
	$campi_tab=RegTools::prendi_colonne_frontend($nome_tab,"column_name,data_type,is_nullable",false);


	$files=array("js/scriptaculous/lib/prototype.js","sty/import.css","js/import.js");

	 echo openLayout1(_("Data import"),$files,'popup');

	echo "<h1>".sprintf(_('Data import step %d of %d'),3,4)."</h1>";

	echo "<p>"._('If you need to perform operations on the fields identified for the import or insert constants, operate from this window, otherwise go ahead')."</p>\n";


	 echo "<form action=\"" . Common::phpself() . "?step=4\" method=\"post\" >\n";

	  echo "<p><input type=\"button\" onclick=\"history.back()\" value=\"&lt;&lt; "._('Previous')."\" />   <input type=\"button\" onclick=\"submit()\" value=\""._('Next')." &gt;&gt;\" /></p>\n";

	echo "<table summary=\"tabella importazione2\" id=\"tabella-conversioni\">\n";


	for($i=0;$i<count($campi_tab[0]);$i++){

		echo "<tr class=\"import-campo\" id=\"imp_".$campi_tab[0][$i]."\">\n";

		echo "<td><strong>".$campi_tab[0][$i]."</strong></td>\n";

		if(in_array($campi_tab[0][$i],explode(",",$_SESSION['import']['nome_campi']))){

			$importazione_csv="<span class=\"verde\"><strong>"._('Selected value')."</strong></span>";
		}
		else{
			$importazione_csv='<span class="fakelink small" onclick="set_costante('.$i.');" >&lt;'._('set constant value').'&gt;</span>'.
			'<span id="costante_'.$i.'"  style="display:none" > <input type="text" name="costante['.$i.']" /> '.
			' <span class="fakelink small" onclick="unset_costante('.$i.');" id="costante_'.$i.'_trigger">'._('remove').'</span></span>';
		}


		echo "<td id=\"td$i\" width=\"340\"><div class=\"up\"></div><span class=\"fakelink small\" id=\"s$i\" onclick=\"modificatore($i,this);\">&lt;"._('add action')."&gt;</span></td>";

		echo "<td width=\"270\">".$importazione_csv."</td>\n";


		echo "</tr>\n";
	}


	echo "</table>\n";

	echo "<p><input type=\"button\" onclick=\"history.back()\" value=\"&lt;&lt; "._('Previous')."\" />   <input type=\"button\" onclick=\"submit()\" value=\""._('Next')." &gt;&gt;\" /></p>\n";

	echo "</form>\n";

	 echo closeLayout1();
}


// STEP 3
// REFRESH WINDOWS

function import_step4(){

	$_SESSION['import']['post_data']=$_POST;
	header("Location: ".$_SERVER['PHP_SELF']."?step=5");
	exit;
}


function import_query($only_sql=false){

	ini_set('max_execution_time',600);

	global  $vmsql, $vmreg, $db1;

	$post_data = $_SESSION['import']['post_data'];

	// nome della tabella
	$nome_tab=RegTools::oid2name($_SESSION['import']['oid']);
	$PK_tab=RegTools::prendi_PK($nome_tab);

	$is_autoincrement = RegTools::is_autoincrement($nome_tab,$PK_tab);

	// prendi i campi disponibili per la tabella
	$campi_tab=RegTools::prendi_colonne_frontend($nome_tab,"column_name,data_type,is_nullable",false);


	// apro il file
	$fp=fopen(_PATH_TMP."/".$_SESSION['import']['filename'],'r');


	$_da_processare= $_SESSION['import']['campi_da_processare'];

	$campi = $_SESSION['import']['nome_campi'];
	$array_campi = explode(",",$_SESSION['import']['nome_campi']);

	$costanti='';
	$costanti_val='';

	// IMPOSTA LE EVENTUALI COSTANTI
	foreach ($post_data['costante'] as $k=>$val) {

		if($val!='' && $val!='%AUTO'){

			$costanti.=$campi_tab[0][$k].",";
			$costanti_val.=controllo_tipo(modificatori($val,$k,$post_data),$campi_tab[1][$k],$campi_tab[2][$k]);
		}
		else{

		}
	}


	if($costanti!=''){
		// aggiungo le eventuali costanti
		$campi = (strlen($campi)>0) ? $campi.",".substr($costanti,0,-1) : substr($costanti,0,-1);
	}


	// SEPARATORE
	switch($_SESSION['import']['sep']){

		case ';': $sep=';'; break;
		case ',': $sep=','; break;
		case 'tab': $sep="\t"; break;
		case 'space': $sep=" "; break;
		default: $sep=';';
	}

	switch($_SESSION['import']['quote']){

		case 'single': $quote="'"; break;
		case 'double': $quote='"'; break;
		case 'null': $quote= null; break;
		default: $quote= null;
	}

	$SQL_FINALE='';
	$errori='';
    $righe_errore=0;
    $row=1;

    if(!$only_sql){
        $q_begin=$vmsql->query("BEGIN");
    }

	while (($CSV = vf_fgetcsv($fp, null, $sep, $quote)) !== FALSE) {

	    $num = count($CSV);

		if($row==1 && isset($_SESSION['import']['prima_riga'])){

			$row++;
			continue;
	    }

	    $sql='';
	    $kcampo=0;

	    // prendi valori
	    for ($c=0; $c < $num; $c++) {

	    	if(in_array($c,$_da_processare)){

	    		// cerca la chiave di campo nelle info dei campi 
	    		$k=array_search($array_campi[$kcampo],$campi_tab[0]);

	    		// controllo sul tipo di dati
	    		$sql.=controllo_tipo( modificatori($CSV[$c],$k,$post_data) ,$campi_tab[1][$k], $campi_tab[2][$k]);

	    		$kcampo++;
	    	}
	    }

	    // aggiungo il valore delle costanti
	    if(strlen($costanti_val)>0){
	    	$sql.=$costanti_val;
	    }


	    $sql_da_eseguire="INSERT INTO ".$nome_tab." (".$campi.") VALUES (".substr($sql,0,-1).");\n";

	    if(!$only_sql){
	    	$res = $vmsql->query_try($sql_da_eseguire,false,true);
	    }


	    if($res!=1){


	    	$errori.=sprintf(_("Line %d - error %s "),$row,$res) .errors_mysql($res)."<br />";
	    	$righe_errore++;
	    }
	    else if($is_autoincrement && !$only_sql){

	    	$last_id=$vmsql->insert_id($nome_tab,$PK_tab);
	    	$ids[]=$last_id;
	    	$Log = new Log();
	    	$Log->rpc_log('import',$nome_tab,$_SESSION['user']['uid'],$_SESSION['gid'],$last_id,true,'',$sql_da_eseguire,$info_browser);
	    }


	    if(!$only_sql){

		    // Periodica scrittura del file di stato e controllo di eventuali rollback
		    if(($row%10)==0 || $row==$_SESSION['import']['length']){

		    	// sleep(1);

		    	// ROLLABACK SU CHIAMATA
		    	if(file_exists(_PATH_TMP."/".$_SESSION['import']['filename'].".stop")){

		    		$vmsql->query("ROLLBACK");

		    		// esce
				    die(0);
		    	}


		    	// Scrittura del file di stato JSON
			    $fp_json=fopen(_PATH_TMP."/".$_SESSION['import']['filename'].".dat",'w');

			    $JSON="[{'row':$row,'tot':".intval($_SESSION['import']['length']).",'errori':$righe_errore,'txt_errore':'$errori'}]";

			    fwrite($fp_json,$JSON);
			    fclose($fp_json);
		    }

	    }

	    $row++;


	    $SQL_FINALE.=$sql_da_eseguire;
	}


	if(!$only_sql){
		fclose($fp);

		$vmsql->query("COMMIT");
	}

	// crea un file temp sql
	$fp=fopen(_PATH_TMP."/".$_SESSION['import']['filename'].".sql",'w');
	fwrite($fp,$SQL_FINALE);
	fclose($fp);
}


function exec_query_import(){

	global  $vmsql, $vmreg, $db1;

	$files=array("js/scriptaculous/lib/prototype.js","sty/import.css","js/import.js","js/open_window.js");

	// tipo di browser.. serve per il corretto funzionamento della percentuale
	include_once("./inc/func.browser_detection.php");
	$browser=browser_detection('full');
	if($browser[0]=='ie')	$files[]="sty/import_ie.css";

	echo openLayout1(_("Import execution"),$files,'popup');
	echo "<h1>".sprintf(_('Step %s of %s'),4,4)." <span class=\"var\">"._('Import processing')."</span></h1>\n";

	echo "<div>"._('Import status')." <span id=\"feed1\">&nbsp;</span></div>\n";
	echo "<div id=\"num\">"._('Processing row')." <span id=\"row\">0</span> "._('of')." {$_SESSION['import']['length']}</div>\n";
	echo "<div>"._('Inserts:')." <span id=\"ins\">0</span> - "._('Errors:')." <span id=\"errori\">0</span></div>\n";
	echo "<div><span id=\"percento\">0% "._('done')."</span></div>\n";


	echo "<p><input type=\"button\" id=\"importa\" value=\" "._('Import')." \" />" 
	    ." <input type=\"button\" id=\"annulla\" value=\" "._('Cancel')." \" disabled=\"disabled\" />"
	    ." <input type=\"button\" id=\"mostra_log\" value=\" "._('Show SQL')." \"  />".
	    " <input type=\"button\" id=\"chiudi\" value=\" "._('Close')." \" /></p>\n";

	echo "<div id=\"barra\" style=\"visibility:hidden\" ><img id=\"barra-img\" src=\"img/barra1.gif\" alt=\"\" width=\"0\" height=\"100%\" /></div>\n";

	echo "<div id=\"txt\">
			<div id=\"txt_start\"></div>
			<div id=\"txt_errore\"></div>
			<div id=\"txt_end\"></div>
		  </div>\n";

	echo "<div id=\"perc-barra\" ></div>\n";

	?>

	<script type="text/javascript">

	/* <![CDATA[ */

	var stato;
	var updater=null;
	var azione_rollback=false;

	function add0(dd){
		dd=dd+'';
		return (dd.length==1) ? '0'+dd : dd;
	}

	function esecuzione(){

		$('importa').disable();
		$('annulla').enable();
		$('chiudi').disable();

		var d = new Date();

		$('txt_start').update(add0(d.getHours())+":"+add0(d.getMinutes())+":"+add0(d.getSeconds())+' -- <?php echo _('Begin procedure');?></span><br />');


		updater= new PeriodicalExecuter(function(pe) {

			 if(azione_rollback){

			 	pe.stop();

			 	 new Ajax.Request("./rpc/rpc.import_stop.php?h=<?php echo $_SESSION['import']['filename'];?>",{
				 	method: 'post',
				 	onSuccess: function(transport){
				 		if(transport.responseText==1){

				 			$('feed1').update("<strong><?php echo _('Execution cancelled - Rollback done');?></strong>");
				 			$('chiudi').enable();

				 			// procedura di reset
				 			reset_import(1);
				 		}

				 	}
				 });
			 }
			 else{

			      new Ajax.Request("<?php echo _PATH_TMP_HTTP."/".$_SESSION['import']['filename'].".dat";?>",
					{
						method: 'post',
						onComplete: null,
						onSuccess: function(transport){

							$('feed1').update('<?php echo _('Processing...');?>');

							$('barra').style.visibility='';

							var stato0=$A(eval(transport.responseText));
							stato=stato0[0];
							//	console.log(stato);

							$('row').update(stato.row);
							$('errori').update(stato.errori);
							$('txt_errore').update(stato.txt_errore.replace(/@/,'<br />'));

							var conta_inserite = ((stato.row) - stato.errori);
							if(conta_inserite<0) { conta_inserite=0;}
							$('ins').update(conta_inserite);

							var perc=Math.round(stato.row/stato.tot*100);
							$('percento').update(perc+'% completato');
							$('perc-barra').update(perc+'%');

							var rapp_img = Math.round(606/100*perc);
							$('barra-img').width=rapp_img;

							// Chiusura					
							if(stato.row>=stato.tot){
								pe.stop();

								d=new Date();

								$('feed1').update('<strong><?php echo _('Operation completed');?></strong>');
								$('annulla').disable();
								$('mostra_log').enable();
								$('chiudi').enable();
								$('txt_end').update(add0(d.getHours())+":"+add0(d.getMinutes())+":"+add0(d.getSeconds())+' -- <?php echo _('Operation completed');?></span>');

								window.opener.location=window.opener.location;
							}

						},
						onFailure:  function(){ $('feed1').update('<?php echo _('Data pending');?>');}
					});
			 }

		  }, 3);


		new Ajax.Request("./import.php?step=6",
		{
			method: 'post',
			onSuccess: function(){}
		});
	}

	function feedback(){

		$('feed1').update('<strong><?php echo _('Operation completed');?></strong>');
	}

	function rollback(){

		azione_rollback=true;
		$('feed1').update('<strong><?php echo _('Closing... please wait');?></strong>');
	}

	function reset_import(roll){

		new Ajax.Request("./import.php?step=7",
		{
			method: 'post',
			onSuccess: function(transport){

				if(roll==1){
					$('importa').value='<?php echo _('Restart import process');?>';
					$('importa').enable();
					$('annulla').disable();
					$('mostra_log').disable();
					updater=null;
					azione_rollback=false;
				}
				else{
					$('mostra_log').enable();
					$('annulla').disable();
				}
			}
		});
	}

	function mostra_sql(){

		var preview_sql='<?php echo (!is_file(_PATH_TMP_HTTP."/".$_SESSION['import']['filename'].".sql")) ? 'true':'false';?>';

		if(preview_sql){

			new Ajax.Request("./import.php?step=61",
			{
				method: 'post',
				asynchronous: false,
				onSuccess: function(transport){}
			});

		}

		<?php echo "/*"; var_dump($_SESSION['import']); echo "*/"; ?>

		openWindow('<?php echo _PATH_TMP_HTTP."/".$_SESSION['import']['filename'].".sql";?>', 'sql', 80);
	}

	function chiudi_import(){
		new Ajax.Request("./import.php?step=7&csvdel=1",
		{
			method: 'post',
			onSuccess: function(transport){}
		});

		window.close();
	}


	Event.observe('annulla','click',rollback);
	Event.observe('importa','click',esecuzione);
	Event.observe('mostra_log','click',mostra_sql);
	Event.observe('chiudi','click',chiudi_import);

	/* ]]> */

	</script>

	<?php

	echo closeLayout1();
}


function pulisci_import(){

	$csv_delete= (bool) $_GET['csvdel'];

	$stop=_PATH_TMP."/".$_SESSION['import']['filename'].".stop";
	$dat=_PATH_TMP."/".$_SESSION['import']['filename'].".dat";
	$csv=_PATH_TMP."/".$_SESSION['import']['filename'];
	$sql=_PATH_TMP."/".$_SESSION['import']['filename'].".sql";

	if(file_exists($stop)) unlink($stop);
	if(file_exists($dat)) unlink($dat);

	if(file_exists($csv) && $csv_delete){
		unlink($csv);

		if(file_exists($sql)) unlink($sql);
	}

	echo 1;
}


####################################################################

if(!isset($_GET['step'])) $step=0;
else $step=(int) $_GET['step'];

if(isset($_GET['oid']) && is_numeric($_GET['oid'])) $oid=$_GET['oid'];
else if(isset($_GET['oid'])) $oid = intval(str_replace(_BASE64_PASSFRASE,"",base64_decode($_GET['oid'])));
else $oid=0;

switch($step){

	case 0: import_step0();
	break;

	case 1: import_step1();
	break;

	case 11: import_step1_options();
	break;

	case 2: import_step2();
	break;

	case 21: import_step2_ins();
	break;

	case 3: import_step3();
	break;

	case 4: import_step4();
	break;

	case 5: exec_query_import();
	break;

	case 6: import_query();
	break;

	case 61: import_query(true);
	break;

	case 7: pulisci_import();
	break;


	default: import_step0();

}
