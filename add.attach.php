<?php
/**
* Finestra per la gestione degli allegati dalla scheda 
* Viene aperta in popup dal file {@link scheda.php}
* 
* @package VFront
* @author Mario Marcello Verona <marcelloverona@gmail.com>
* @copyright 2007-2010 M.Marcello Verona
* @version 0.96 $Id: add.attach.php 1078 2014-06-13 15:35:53Z marciuz $
* @license http://www.gnu.org/licenses/gpl.html GNU Public License
*/




// Imposto l'esecuzione massima in 10 minuti
ini_set('max_execution_time',600);
	

include("./inc/conn.php");
include("./inc/layouts.php");

proteggi(1);


/**
 * Permette di modificare il nome di un file eliminando i caratteri accentati latin1 e 
 * sostituendo ciò che non sono lettere, numeri o trattini con il carattere underscore
 *
 * @param string $nomefile
 * @return string Nome del file senza caratteri accenti o spazi
 */
function rinomina_file($nomefile){
	
	$nomefile=str_replace(array('à','è','é','ò','ì','ù'),
						  array('a','e','e','o','i','u'),
						  $nomefile);
	
	$nomefile=preg_replace("'[^\w-.]+'","_",trim($nomefile));
	
	return $nomefile;

}


/**
 * Controlla la tipologia dei file mediante la lettura delle estensioni
 * ed il confronto con le estensioni permesse nella variabile "formati_attach"
 *
 * @param string $nomefile
 * @param string $header_file
 * @todo Impostare un controllo mediante la lettura del MIME del file
 * @return bool
 */
function verifica_tipo_file($nomefile,$header_file){
	
	$estensioni_accettate=explode(",",$_SESSION['VF_VARS']['formati_attach']);
	
	foreach($estensioni_accettate as $k=>$v) $estensioni_accettate[$k]=trim($v);
	
	$infofile=pathinfo($nomefile);
	
//	var_dump($estensioni_accettate);
	
	return (in_array($infofile['extension'],$estensioni_accettate)) ?  true:false;
	
}





/**
 * Funzione per la determinazione del tipo di file immagine mediante il nome e l'estensione
 * Restituisce una icona con il tipo (se presente nei casi descritti)
 *
 * @param string $nomefile
 * @return string
 */
function img_filetype($nomefile){
	
	switch (substr($nomefile,-4,4)){
		
		case '.pdf': $mime='pdf.gif'; $alt='pdf'; break;
		case '.doc': $mime='doc.gif'; $alt='documento Word'; break;
		case '.xls': $mime='xls.gif'; $alt='foglio di calcolo Excel'; break;
		case '.zip': $mime='zip.gif'; $alt='file compresso zip'; break;
		default   : $mime='generic.gif'; $alt='file'; break;
		
		
	}
	
	
	return "<img src=\"img/mime/$mime\" alt=\"$alt\" />";
	
}




######################################################################
#
#	CASO ELIMINA ALLEGATO
#


if(isset($_GET['del'])){
	
	$id_da_eliminare=str_replace(_BASE64_PASSFRASE,'',base64_decode($_GET['del']));
	
	$id_da_eliminare=intval($id_da_eliminare);
	
	// elimino dal DB
	$q_del=$vmreg->query("DELETE FROM "._TABELLA_ALLEGATO." WHERE codiceallegato=$id_da_eliminare");
	
	$test_del_db= ($vmreg->affected_rows($q_del)==1) ? true:false;

	
	// elimino dal filesystem
	$test_del_fs=@unlink(_PATH_ATTACHMENT."/$id_da_eliminare.dat");
	
	if($test_del_db && $test_del_fs){
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

if(count($_FILES)>0){
	
	
	// variabile per il test finale
	$success=0;
	$tipo_errori_up= array();
	
	// CICLO SUI FILES
	for($i=0;$i<count($_FILES['gfile']['tmp_name']);$i++){
		
		// test sulla correttezza dell'upload
		if(is_file($_FILES['gfile']['tmp_name'][$i]) && $_FILES['gfile']['error'][$i]==0){
			
			$nome_pulito = rinomina_file($_FILES['gfile']['name'][$i]);
		}
		else{
			$tipo_errori_up[$i]='generico di upload';
			continue;
		}
		
		
		// Test sul tipo di file
		if(!verifica_tipo_file($nome_pulito,$_FILES['gfile']['type'][$i])){
			$tipo_errori_up[$i]=_('file type not allowed (the available extensions are: ').str_replace(",",", ",$_SESSION['VF_VARS']['formati_attach']).')';
			continue;
		}
		
		
		// INSERISCE IN DATABASE
		
		$sql_ins=sprintf("INSERT INTO "._TABELLA_ALLEGATO."
				 (tipoentita,codiceentita,nomefileall,descroggall,autoreall,lastdata)
				 VALUES ('%s','%s','%s','%s','%s','%s')",
				 $vmreg->escape($_POST['t']),
				 $vmreg->escape($_POST['id']),
				 $vmreg->escape($nome_pulito),
				 '',
				 $vmreg->escape(ucfirst($_SESSION['user']['nome'])." ".ucfirst($_SESSION['user']['cognome'])),
				 date('Y-m-d H:i:s')
				 );

		
		$q_ins=$vmreg->query($sql_ins);
		
		$id_ultimo=$vmreg->insert_id(_TABELLA_ALLEGATO,'codiceallegato');
		
		$test_move=move_uploaded_file($_FILES['gfile']['tmp_name'][$i],_PATH_ATTACHMENT."/$id_ultimo.dat");
		
		if($test_move){
			
			$success++;
		}
		else{
			$tipo_errori_up[$i]=_('it is not possible to upload the file in the folder');
			continue;
		}
		
		
		
	}
	
	if($success==count($_FILES['gfile']['tmp_name'])){
		
		header("Location: ".$_SERVER['PHP_SELF']."?t=".$_POST['t']."&id=".$_POST['id']."&feed=ok");
	}
	else{
		header("Location: ".$_SERVER['PHP_SELF']."?t=".$_POST['t']."&id=".$_POST['id']."&feed=ko&msg=".implode("|",$tipo_errori_up));
	}
	
	exit;
}
########################################################################













########################################################################
#
#	VISTA PAGINA - QUERY DI RICERCA ALLEGATI
#





# CASO SPECIALE NEW:
// L'utente ha cliccato su allegato quando ancora il record non era salvato..
if($_GET['id']=='new'){
	
	$msg=_("To attach a file, save the new record first, and then upload the attached files.");
	openErrorGenerico(_('You cannot attach a file until you have first saved the new record'),false,$msg,'popup');
	exit;
}

if($_GET['id']=='ric'){
	
	$msg=_("You are in the middle of a search. Complete or cancel the search before trying to insert attachments.");
	openErrorGenerico(_('You cannot attach files while performing a search'),false,$msg,'popup');
	exit;
}


$tabella = preg_replace("'[\W]+'","",trim($_GET['t']));
$id = preg_replace("'[\W]+'","",trim($_GET['id']));



// PRENDI IMPOSTAZIONI ALLEGATI PER TABELLA/REGISTO
$info_tab=RegTools::prendi_info_tabella($tabella, "permetti_allegati_ins, permetti_allegati_del");

$allegati_ins=$info_tab['permetti_allegati_ins'];
$allegati_del=$info_tab['permetti_allegati_del'];



if(!RegTools::is_tabella($tabella) || $id==''){
	
	openErrorGenerico(_('Request error'),false);
	exit;
}




// CERCA ALLEGATI PER QUESTA TABELLA
$qa=$vmreg->query("SELECT * FROM "._TABELLA_ALLEGATO."
				WHERE tipoentita='$tabella'
				AND codiceentita='$id'
				ORDER BY nomefileall, lastdata");

$num_allegati=$vmreg->num_rows($qa);

$matrice_info_allegati=$vmreg->fetch_assoc_all($qa);









$files = array('sty/linguette.css','js/uploadprogress/BytesUploaded.js','js/uploadprogress/LoadVars.js','js/clona_attach.js','sty/attach.css');

$INIZIO_LAYOUT= openLayout1("Allegati",$files,'popup');

echo str_replace("<body>","<body onload=\"window.focus();\">",$INIZIO_LAYOUT);



echo "<img src=\"./img/flussi.gif\" style=\"float: left;\" alt=\""._('Manage attachments ')."\" />\n";

echo "<h1 style=\"font-size:1.6em;\">"._('Attachments for record')." <span style=\"color:#666;\">".$_GET['id']."</span> "._('of table')." <span class=\"var\">".$_GET['t']."</span></h1>";

echo "<br style=\"clear:left;\" />";


$JS_aggiorna= (isset($_GET['feed']) && $_GET['feed']=='ok') ?  'window.opener.richiediAL();' : "";


	echo "	
	<script type=\"text/javascript\">
	
		$JS_aggiorna
	
		var nuoviAllegati=0;
		
		var bUploaded = new BytesUploaded('whileuploading.php',500);
	
		var divs = new Array('allegati','nuoviallegati');
	
	
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
	
	
	if(isset($_GET['feed']) && $_GET['feed']=='ko'){
		
		
		$messaggi=str_replace("|","<br />",$_GET['msg']);
		
		echo "<p><strong>"._('Warning!')."</strong><br />\n$messaggi</p>\n";
	}
	

	echo "	
<div id=\"contenitore-variabili\">
	<div id=\"box-etichette\">
		
		<ul class=\"eti-var-gr\">

		
			<li onclick=\"eti('allegati');\" id=\"li-allegati\" class=\"attiva\">"._('Attachments')."</li>
			", ($allegati_ins) ? "<li onclick=\"eti('nuoviallegati');\" id=\"li-nuoviallegati\" class=\"disattiva\">"._('Add attachment')."</li>" : "","

		</ul>
	
	</div>";

	
	// opzione scarica tutti
	if($num_allegati>=2){
		
		$link_scarica_tutti=" - <a href=\"download.php?type=all&amp;idr=".base64_encode($matrice_info_allegati[0]['codiceallegato']._BASE64_PASSFRASE)."\">Scarica tutti gli allegati</a>";
	}
	else{
		$link_scarica_tutti='';
	}
	
	// LINGUETTA NUOVO FLUSSO
	echo "
	<div class=\"cont-eti\" id=\"cont-eti-allegati\" >
	
		<p>"._('Attachments for this record').": <strong>$num_allegati</strong> $link_scarica_tutti</p>
		
		<hr class=\"light2\" />
		";
	
		// MOSTRA GLI ALLEGATI
		for($i=0;$i<count($matrice_info_allegati);$i++){
			
			$dimensione = RegTools::allegato_filesize($matrice_info_allegati[$i]['codiceallegato']);
			
			$scarica=(preg_match('|Dimensione|i',$dimensione)) ? "": " - <a href=\"download.php?f=".base64_encode($matrice_info_allegati[$i]['codiceallegato']._BASE64_PASSFRASE)."\">Scarica</a>";
			$elimina=($allegati_del) ? " - <span class=\"fakelink-rosso\" onclick=\"if(confirm('"._('Are you sure you want to delete this attachment?')."')){ window.location='".$_SERVER['PHP_SELF']."?t=$tabella&amp;id=$id&amp;del=".base64_encode($matrice_info_allegati[$i]['codiceallegato']._BASE64_PASSFRASE)."';}\" >"._('Delete')."</span>" : "";
			
			$estensione=substr($matrice_info_allegati[$i]['nomefileall'],-3,3);
			
			if($estensione=='gif' || $estensione=='jpg' || $estensione=='png' || 
			$estensione=='GIF' || $estensione=='JPG' || $estensione=='PNG'){
				
				$immagine='<img src="thumb.php?id='.$matrice_info_allegati[$i]['codiceallegato'].'" alt="'.$matrice_info_allegati[$i]['nomefileall'].'" class="thumb" /><div style="clear:both">&nbsp;</div>';
			}
			else{
				
				$immagine = img_filetype($matrice_info_allegati[$i]['nomefileall']);
			}
			
			echo "
			<div class=\"allegato\">
				<div class=\"allegato-img\">".$immagine."</div>
				<div class=\"allegato-info\">
					<strong>".$matrice_info_allegati[$i]['nomefileall']."</strong><br />
					$dimensione $scarica $elimina
					
				</div>
			</div>\n";
		}
	
	
	
	
	echo "
	    <br class=\"clear\" />
	</div>
	";
	
	
	// LINGUETTA AMMINISTRAZIONE FLUSSI
	if($allegati_ins){
	echo "
	<div class=\"cont-eti\" id=\"cont-eti-nuoviallegati\" style=\"display:none;\">
		<br />
		";
	?>
	<form enctype="multipart/form-data" method="post" action="<?php echo $_SERVER['PHP_SELF'];?>" onsubmit="bUploaded.start('fileprogress');">
			<div>
				<div id="contenitore-file"><div><input type="file" name="gfile[]" size="60" /> <span onclick="rimuovi_attach(this);" class="fakelink" style="font-size:0.7em;">rimuovi</span><br /></div></div>
				
				<span onclick="clona_attach();" class="fakelink"><?php echo _('Add another file');?></span><br /><br /><br />
				
				
				<input type="hidden" name="t" value="<?php echo $tabella;?>" />
				<input type="hidden" name="id" value="<?php echo $id;?>" />
				
				<input type="submit" name="aggiungi" value="  <?php echo _('Send');?>  " onclick="submit();this.value='<?php echo _('Please wait...');?>';this.disabled=true;" />
					
			</div>
		</form>
		<div id="fileprogress" style="font-weight: bold;"> </div>

<?php
	
	echo "</div>\n";
	} // -- fine clausola nuovi inserimenti
	
echo "</div><!-- fine contenitore -->\n\n";




echo closeLayout1();
?>