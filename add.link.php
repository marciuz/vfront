<?php
/**
* Finestra per la gestione dei link dalla scheda 
* Viene aperta in popup dal file {@link scheda.php}
* 
* @package VFront
* @author Mario Marcello Verona <marcelloverona@gmail.com>
* @copyright 2007-2010 M.Marcello Verona
* @version 0.96 $Id: add.link.php 1078 2014-06-13 15:35:53Z marciuz $
* @license http://www.gnu.org/licenses/gpl.html GNU Public License
*/

// Imposto l'esecuzione massima in 10 minuti
ini_set('max_execution_time',600);
	

include("./inc/conn.php");
include("./inc/layouts.php");



proteggi(1);




/**
 * Funzione di parsing del link. 
 * Controlla la validità del link e qualora non sia espresso un suffisso valido come http:// o https://
 * se sovreascrive il contenuto
 *
 * @param string $user_link
 * @todo La funzione è da scrivere, attualmente restituisce la stringa così com'è
 * @return string
 */
function parse_link($user_link){
	
	return $user_link;
}




######################################################################
#
#	CASO ELIMINA ALLEGATO
#


if(isset($_GET['del'])){
	
	$id_da_eliminare=str_replace(_BASE64_PASSFRASE,'',base64_decode($_GET['del']));
	
	$id_da_eliminare=intval($id_da_eliminare);
	
	// elimino dal DB
	$q_del=$vmsql->query("DELETE FROM "._TABELLA_LINK." WHERE codicelink=$id_da_eliminare");
	
	$test_del_db= ($vmsql->affected_rows($q_del)==1) ? true:false;

		
	if($test_del_db){
		header("Location: ".$_SERVER['PHP_SELF']."?t=".$_GET['t']."&id=".$_GET['id']."&az=del&feed=ok");
		
	}
	else{
		header("Location: ".$_SERVER['PHP_SELF']."?t=".$_GET['t']."&id=".$_GET['id']."&az=del&feed=ko");
	}
	
	exit;
}


#
#
######################################################################



#######################################################################
#
#	CASO UPLOAD ESEGUITO
#

if(count($_POST['links'])>0){
	
	$tabella = preg_replace("'[\W]+'","",trim($_POST['t']));
	$id = preg_replace("'[\W]+'","",trim($_POST['id']));
	
	
	$success=0;
		
	for($i=0;$i<count($_POST['links']);$i++){
		
		$sql_ins="INSERT into "._TABELLA_LINK." (tipoentita,codiceentita,link)
				 VALUES('$tabella','$id','".$_POST['links'][$i]."')";
		
		$q_ins=$vmsql->query($sql_ins);
		if($vmsql->affected_rows($q_ins)==1){
			$success++;
		}
	}
	
	if(count($_POST['links'])==$success){
		
		header("Location: ".$_SERVER['PHP_SELF']."?t=".$_POST['t']."&id=".$_POST['id']."&feed=ok");
		exit;
	}
	else{
		header("Location: ".$_SERVER['PHP_SELF']."?t=".$_POST['t']."&id=".$_POST['id']."&feed=ko");
	}
	

	exit;
}
########################################################################













########################################################################
#
#	VISTA PAGINA - QUERY DI RICERCA LINK
#




# CASO SPECIALE NEW:
// L'utente ha cliccato su allegato quando ancora il record non era salvato..
if($_GET['id']=='new'){
	
	$msg=_("To inset a link, first save the new record, and then insert the link.");
	openErrorGenerico(_('You cannont insert a link until you have first saved the new record'),false,$msg,'popup');
	exit;
}

if($_GET['id']=='ric'){
	
	$msg=_("You are in the middle of a search. Complete or cancel  the search before inserting the link.");
	openErrorGenerico(_('You cannot insert a link while in search mode'),false,$msg,'popup');
	exit;
}

$tabella = preg_replace("'[\W]+'","",trim($_GET['t']));
$id = preg_replace("'[\W]+'","",trim($_GET['id']));

// PRENDI IMPOSTAZIONI ALLEGATI PER TABELLA/REGISTO
$info_tab=RegTools::prendi_info_tabella($tabella, "permetti_link_ins, permetti_link_del");

$link_ins=$info_tab['permetti_link_ins'];
$link_del=$info_tab['permetti_link_del'];


if(!RegTools::is_tabella($tabella) || $id==''){
	
	openErrorGenerico(_('Request error'),false);
	exit;
}




// CERCA ALLEGATI PER QUESTA TABELLA
$qa=$vmsql->query("SELECT * FROM "._TABELLA_LINK."
				WHERE tipoentita='$tabella'
				AND codiceentita='$id'
				ORDER BY link, lastdata");

$num_link=$vmsql->num_rows($qa);

$matrice_info_link=$vmsql->fetch_assoc_all($qa);













$files = array('sty/linguette.css','sty/admin.css','js/clona_link.js','sty/attach.css');

$INIZIO_LAYOUT= openLayout1("Link",$files,'popup');

echo str_replace("<body>","<body onload=\"self.focus();\">",$INIZIO_LAYOUT);

echo "<img src=\"./img/network.gif\" style=\"float: left;\" alt=\""._('link management')."\" />\n";

echo "<h1 style=\"font-size:1.6em;\">"._('Link for record')." <span class=\"var\">".$_GET['id']."</span> "._('of table')." <span class=\"var\">".$_GET['t']."</span></h1>";

echo "<br style=\"clear:left;\" />";



$JS_aggiorna= (isset($_GET['feed']) && $_GET['feed']=='ok') ?  'window.opener.richiediAL();' : "";

	

	// prendi aree tematiche
	/*$q_aree=$vmsql->query("SELECT codiceAreatematica, descAreatematica FROM areatematica ORDER BY descAreatematica");
	
	$SELECT = "\t<select name=\"area[]\">\n";
	
	while($RS=$vmsql->fetch_assoc($q_aree)){
		$areetematiche[$RS['codiceAreatematica']]=$RS['descAreatematica'];
		$SELECT.="\t\t<option value=\"".$RS['codiceAreatematica']."\">".$RS['descAreatematica']."</option>\n";
	}
	
	$SELECT.="\t</select>\n";
	*/
	
	

	echo "	
	<script type=\"text/javascript\">
		
		$JS_aggiorna
	
		var divs = new Array('link','nuovilink');
	
		var nuoviLink=0;
	
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
	";
	

	echo "	
<div id=\"contenitore-variabili\">
	<div id=\"box-etichette\">
		
		<ul class=\"eti-var-gr\">

		
			<li onclick=\"eti('link');\" id=\"li-link\" class=\"attiva\">"._('Link')."</li>
			", ($link_ins) ? "<li onclick=\"eti('nuovilink');\" id=\"li-nuovilink\" class=\"disattiva\">"._('Add link')."</li>" : "","

		</ul>
	
	</div>";

		
	// LINGUETTA NUOVO FLUSSO
	echo "
	<div class=\"cont-eti\" id=\"cont-eti-link\" >
	
		<p>"._('Link for this record:')." <strong>$num_link</strong></p>
		
		<hr class=\"light2\" />
		";
	
	
		// MOSTRA GLI ALLEGATI
		for($i=0;$i<count($matrice_info_link);$i++){
			
			$elimina= ($link_del) ? " - <span class=\"fakelink-rosso\" onclick=\"if(confirm('"._('Do you really want to cancel this link?')."')){ window.location='".$_SERVER['PHP_SELF']."?t=$tabella&amp;id=$id&amp;del=".base64_encode($matrice_info_link[$i]['codicelink']._BASE64_PASSFRASE)."';}\" >"._('Delete')."</span>" : "";
			
			echo "
			<div class=\"link\">
				<p><a href=\"".parse_link($matrice_info_link[$i]['link'])."\">".$matrice_info_link[$i]['link']."</a> $elimina<br />
				".
//			"<span class=\"grigio\">Area: ".$areetematiche[$matrice_info_link[$i]['codiceAreaTematica']]."</span>".
			"</p>
			</div>\n";
		}
	
	
	
	
	echo "
	</div>
	";
	
	

	
	// LINGUETTA AMMINISTRAZIONE FLUSSI
	
	if($link_ins){
		
	echo "
	<div class=\"cont-eti\" id=\"cont-eti-nuovilink\" style=\"display:none;\">
		<br />
		";
	
	
	?>
	<form method="post" action="<?php echo $_SERVER['PHP_SELF'];?>" >
			<div>
				<div id="contenitore-link"><div><input type="text" name="links[]" size="68" value="http://" /><?php echo $SELECT; ?>
				<span onclick="rimuovi_link(this);" class="fakelink" style="font-size:0.7em;"><?php echo _('remove');?></span><br /></div></div>
				
				<span onclick="clona_link();" class="fakelink"><?php echo _('Add another link');?></span><br /><br /><br />
				
				
				<input type="hidden" name="t" value="<?php echo $tabella;?>" />
				<input type="hidden" name="id" value="<?php echo $id;?>" />
				
				<input type="submit" name="aggiungi" value="  <?php echo _('Send');?>  " onclick="submit();this.value='<?php echo _('Please wait...');?>';this.disabled=true;"  />
					
			</div>
		</form>

<?php

	
		echo "</div>\n";
	} //-- fine clausola diritti nuovi inserimenti
	
	
echo "</div><!-- fine contenitore -->\n\n";




echo closeLayout1();
?>