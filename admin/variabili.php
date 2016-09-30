<?php
/**
 * Attraverso questo menu è possibile leggere e modificare i valori delle variabili
 * di ambiente di VFront.
 * Vengono qui rilevate tutte le variabili gestite dal database di regole (tabella variabili).
 * E' possibile inoltre inserire variabili "locali" per i diversi gruppi.
 * Nel caso di presenza di una variabile "locale" per un gruppo e quella generale avrà la priorità quella "locale".
 * Per aggiungere nuove variabili da utilizzare nel codice è necessario aggiungerle direttamente
 * in database. 
 * In questo caso specificare anche la tipologia di variabile (bool, int, string).
 * 
 * 
 * @desc Menu delle variabili di ambiente di VFront
 * @package VFront
 * @subpackage Administration
 * @author M.Marcello Verona
 * @copyright 2007-2010 M.Marcello Verona
 * @version 0.96 $Id: variabili.php 1153 2015-06-02 12:46:40Z marciuz $
 * @license http://www.gnu.org/licenses/gpl.html GNU Public License 
 */


require_once("../inc/conn.php");
require_once("../inc/layouts.php");


 proteggi(3);

	if(isset($_GET['modglob'])){
		
		
		$var=$vmreg->recursive_escape($_POST['var']);
		
		$sql="";
		$c=0;
        
        
		
		foreach($var as $k=>$val){
            
            $k = filter_var($k, FILTER_SANITIZE_STRING);
            $val = filter_var($val, FILTER_SANITIZE_STRING);
            
			$sql = "UPDATE ".$db1['frontend'].$db1['sep']."variabili SET valore='$val' WHERE variabile='$k' AND gid=0";	
			$q=$vmreg->query($sql);
			if($vmreg->affected_rows($q)==1){
				$c++;
			}
		}
		
		if(isset($_POST['rpc_query'])){
			echo $c; 
			exit;
		}

		$_SESSION['VF_VARS']=var_frontend('session','session');
		
		header("Location: ".$_SERVER['PHP_SELF']."?feed=$c&gidfocus=0");
		exit;
	}

	
	
	if(isset($_GET['modvar'])){
		
		
		$var=$vmreg->recursive_escape($_POST['var']);
		
		
		$gid= (int) $_GET['modvar'];
		$sql="";
		$c=0;
		
		foreach($var as $k=>$val){
            
            $k = filter_var($k, FILTER_SANITIZE_STRING);
            $val = filter_var($val, FILTER_SANITIZE_STRING);
            $gid = intval($gid);
            
			$sql = "UPDATE ".$db1['frontend'].$db1['sep']."variabili SET valore='$val' WHERE variabile='$k' AND gid=$gid";	
			$q=$vmreg->query($sql);
			if($vmreg->affected_rows($q)==1){
				$c++;
			}
		}

		$_SESSION['VF_VARS']=var_frontend('session','session');
		
		header("Location: ".$_SERVER['PHP_SELF']."?feed=$c&gidfocus=$gid&feedlocale");
		exit;
	}
	
	
	if(isset($_GET['add_locale'])){
		
		$nome_var=$vmreg->recursive_escape($_POST);
		
		// Test prezenza variabile
		$test_var_locale=$vmreg->test_id('variabile',"'".$nome_var['variabile_nuova']."'",$db1['frontend'].".variabili"," AND gid=". (int) $_GET['add_locale']);
		
		if(!$test_var_locale){
			
			// Prendi valore del gruppo default
			$q_valore_def=$vmreg->query("SELECT valore, tipo_var,descrizione FROM ".$db1['frontend'].$db1['sep']."variabili WHERE variabile='".$nome_var['variabile_nuova']."' AND gid=0 ");
			list($valore_def,$tipo_var, $descrizione)=$vmreg->fetch_row($q_valore_def);
			
			$q_nuova_var = $vmreg->query("INSERT INTO ".$db1['frontend'].$db1['sep']."variabili (valore,tipo_var,descrizione,variabile,gid) VALUES ('$valore_def','$tipo_var','".$vmsql->escape($descrizione)."','".$nome_var['variabile_nuova']."',".intval($_GET['add_locale']).")");
		}

		$_SESSION['VF_VARS']=var_frontend('session','session');
		
		header("Location: ".$_SERVER['PHP_SELF']."?feed=".$_GET['add_locale']."&gidfocus=".$_GET['add_locale'].".&feedlocale");
		
		exit;
		
	}
	
	
	/**
	 * Prende le variabili e genera il form con il trattamento dovuto alle variabili in base
	 * alla tipologia delle stesse (specificata in database)
	 *
	 * @desc Form per la gestione delle variabili
	 * @param int $gid ID del gruppo di riferimento
	 * @return string HTML output
	 */
	function form_variabili($gid=0){
		
		global  $vmsql, $vmreg, $db1;
		
		if($gid==0){
			
			$azione = "modglob";
			$testo_submit=_("Modify global variables");
			$indicatore_locale="";
		}
		else{
			
			$azione = "modvar=".intval($gid);
			$testo_submit=_("Modify group variables");
			$indicatore_locale="&amp;feedlocale=1";
		}
		
		$OUT="<form method=\"post\" action=\"".$_SERVER['PHP_SELF']."?{$azione}{$indicatore_locale}\" >\n";
		
		$q=$vmreg->query("SELECT * FROM ".$db1['frontend'].$db1['sep']."variabili WHERE gid=".intval($gid)." AND pubvar=1 ORDER BY variabile");
		
		if($vmreg->num_rows($q)==0){
			
			return false;
		}
		
		
		$m_vars=$vmreg->fetch_assoc_all($q);
		
		
		
		foreach($m_vars as $k=>$val){
			
			$OUT.= "
			<div class=\"variabile\">
				<label for=\"".$val['variabile']."_gid\">".$val['variabile']."</label>\n";
			
			
			if($val['tipo_var']=="bool"){
				
				$check_si = ($val['valore']=="1") ? "selected=\"selected\" " : "";
				$check_no = ($val['valore']=="0") ? "selected=\"selected\" " : "";
				
				$OUT.="\t\t<select class=\"on\" style=\"margin-bottom:1px;\" name=\"var[".$val['variabile']."]\" id=\"".$val['variabile']."_gid\" >
						<option value=\"0\" $check_no>"._("No")."</option>
						<option value=\"1\" $check_si>"._("Yes")."</option>
					</select>
				\n";
			}
			
			// CASE LAYOUT
			elseif ($val['variabile']=='layout'){
				$OUT.="\t\t<select class=\"on\" style=\"margin-bottom:1px;\" name=\"var[".$val['variabile']."]\" id=\"".$val['variabile']."_gid\" >\n";
				
				$dh  = opendir("../themes");
				while (false !== ($filename = readdir($dh))) {
				    if($filename!='.' && $filename!='..' && $filename!='_original' && $filename!='.svn' && is_dir("../themes/$filename")){
				    	
				    	$sel=($filename==$val['valore']) ? "selected=\"selected\"" : "";
				       	$OUT.="<option value=\"$filename\" $sel>$filename</option>\n";
				    }
				}
				
				$OUT.="</select>\n";
				
			}
			
			
			// CASE LANGUAGE
			elseif ($val['variabile']=='lang'){
				$OUT.="\t\t<select class=\"on\" style=\"margin-bottom:1px;\" name=\"var[".$val['variabile']."]\" id=\"".$val['variabile']."_gid\" >\n";
				
				$OUT.="<option value=\"\" >"._('Default')." (".FRONT_LANG.")</option>\n";
				
				$dh  = opendir("../locale");
				while (false !== ($filename = readdir($dh))) {
				    if($filename!='.' && $filename!='..' && $filename!='.svn' && is_dir("../locale/$filename")){
				    	
				    	$sel=($filename==$val['valore']) ? "selected=\"selected\"" : "";
				       	$OUT.="<option value=\"$filename\" $sel>$filename</option>\n";
				    }
				}
				
				$OUT.="</select>\n";
				
			}
			
			else{
				$OUT.="\t\t<input class=\"on\" style=\"margin-bottom:1px;\" type=\"text\" name=\"var[".$val['variabile']."]\" value=\"".$val['valore']."\"  id=\"".$val['variabile']."_gid\"/>\n";
			}
			
			
			$OUT.="\t\t<div class=\"desc-campo\">".Common::vf_utf8_encode($val['descrizione'])."</div>
			</div>
			";
		}
		
		$OUT.="
			<div class=\"variabile\">
				<input type=\"submit\" name=\"modifica\" value=\"  $testo_submit  \" />
			</div>
			";
			
		$OUT.="</form>\n";
		
		return $OUT;
	}
	
	
	########################################################################
	#
	#	ELENCO VARIBILI
	#
	
	$q_vars=$vmreg->query("SELECT DISTINCT variabile FROM ".$db1['frontend'].$db1['sep']."variabili ORDER BY variabile");
	
	list($array_variabili)=$vmreg->fetch_row_all($q_vars,true);

	$opzioni_vars='';
	
	for($k=0;$k<count($array_variabili);$k++){
		
		$opzioni_vars.="\t\t<option value=\"".$array_variabili[$k]."\">".$array_variabili[$k]."</option>\n";
		
	}
	
	$select_vars="\t<select name=\"variabile_nuova\">".$opzioni_vars."</select>\n";

	#
	#
	########################################################################
	
	
	
	$files = array("sty/admin.css","sty/scheda.css","sty/linguette.css","js/mostra_nascondi_id.js");

	$OUT = openLayout1(_("Global variables"),$files);
	
	$OUT.= breadcrumbs(array("HOME","ADMIN",
					strtolower(_("System variables"))
					));

	// Feedback
	if(isset($_GET['feed'])){
		if($_GET['feed']>0){
			
			$feed_str= (isset($_GET['feedlocale']))
				? "<p class=\"feed-mod-ok\">"._("The local variables have been modified correctly")."</p>\n"
				: "<p class=\"feed-mod-ok\">"._("The global variables have been modified correctly")."</p>\n";
		}			
		else{
			$feed_str="<p class=\"feed-mod-ko\">"._("No modification in global variables")."</p>\n";
		}

		$OUT.= $feed_str;
	}
	
	
	$OUT.= "<h1>"._("System variables")."</h1>\n";
	
	$OUT.="<br /><img src=\"../img/settings.gif\" class=\"img-float\" alt=\"impostazioni\" style=\"margin-left:5px;\" />\n";	

	
	$OUT.="<div id=\"contenitore-variabili\">\n";

	$OUT.="\t<h2 class=\"tit-admin\">"._("Global variables")."</h2>\n";
	
	
	$OUT.=form_variabili();
	
	
	echo $OUT;
	
	
	###############################################################################################
	
	
	
	// Prendi i gruppi
	
	
	
	$q_gr = $vmreg->query("SELECT gid, nome_gruppo FROM ".$db1['frontend'].$db1['sep']."gruppo
						WHERE gid>0
						ORDER BY nome_gruppo");
	
	$gids=array();
	$nomi_gr=array();
	
	list($gids,$nomi_gr) = $vmreg->fetch_row_all($q_gr,true);
	
	
	$n_gr = count($gids);
	
	
	if($n_gr>0){
		
		echo "
		<p>&nbsp;</p>
		\t<h2 class=\"tit-admin\">"._("Variables for the registry/group")."</h2>\n";
		
	}
	
	?>
	
	
		
	<script type="text/javascript">
	
		var divs = new Array(<?php echo (count($nomi_gr)>0) ? "'".implode("','",$nomi_gr)."'" :"";?>);
	
	
		function eti(ido){
			
			for (var i in divs){
				document.getElementById('cont-eti-'+divs[i]).style.display='none';
				document.getElementById('li-'+divs[i]).className='disattiva';
			}
			
			// attiva il selezionato
			document.getElementById('cont-eti-'+ido).style.display='';
			document.getElementById('li-'+ido).className='attiva';
			
		}
	
	
	</script>
	
	<?php
	
	$c=  (isset($_GET['feed'])) ? intval($_GET['feed']) : 0;
	
	$gidfocus = (isset($_GET['gidfocus'])) ?  intval($_GET['gidfocus']) : 0;
	
	
	
	if(count($nomi_gr)>0){
	
		echo "
		
		
		
		<div id=\"box-etichette\">
			
			<ul class=\"eti-var-gr\">
			";

			$LI='';
			$DIV_VARS='';
		
			for($i=0;$i<count($nomi_gr);$i++){
				
				// Visibilit� linguette
				$class= (($gids[$i]==$gidfocus) || (!isset($_GET['gidfocus']) && $i==0))   ? "attiva":"disattiva";
				$style_div = (($gids[$i]==$gidfocus) || (!isset($_GET['gidfocus']) && $i==0)) ? "":"style=\"display:none;\"";
				
				$LI.= "<li onclick=\"eti('".$nomi_gr[$i]."');\" id=\"li-".$nomi_gr[$i]."\" class=\"$class\" >".$nomi_gr[$i]."</li>\n";
				
				$FORM_VARIABILE = form_variabili($gids[$i]);
				
				if(!$FORM_VARIABILE){
					
					$FORM_VARIABILE="<p>"._("No variable set for this group")."</p>";
				}
				
				$FORM_NUOVE_VARS= "<form action=\"".$_SERVER['PHP_SELF']."?add_locale=".$gids[$i]."\" method=\"post\" style=\"display:none\" id=\"nuove_variabili_".$gids[$i]."\">
					$select_vars
					<input type=\"submit\" name=\"nuova_variabile\" value=\""._("Add variable")."\"/>
				</form>\n";
				
				$DIV_VARS.= "<div class=\"cont-eti\" id=\"cont-eti-".$nomi_gr[$i]."\" $style_div>
				
								<p><a href=\"javascript:;\" onclick=\"mostra_nascondi('nuove_variabili_".$gids[$i]."');\" >"._("Set new variable")."</a></p>
				
								$FORM_NUOVE_VARS
								
								".$FORM_VARIABILE."
								
							</div>
							";
				
			}
			
			echo "
				$LI
			</ul>
		
		</div>
		
		
		$DIV_VARS
			
	
		
		";
		
	}
	
	echo "</div> <!-- fine contenitore-variabili -->\n";
	
	echo closeLayout1();
?>