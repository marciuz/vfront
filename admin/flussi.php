<?php
/**
 * @desc File sperimentale per la gestione di procedure di inserimento dati e flussi di lavoro
 * @package VFront
 * @subpackage Administration
 * @author M.Marcello Verona
 * @copyright 2007-2010 M.Marcello Verona
 * @version 0.96 $Id: flussi.php 1078 2014-06-13 15:35:53Z marciuz $
 * @license http://www.gnu.org/licenses/gpl.html GNU Public License 
 */


include("../inc/conn.php");
include("../inc/layouts.php");

if(count($_POST)>0){
	
	print_r($_POST);
}


// TENDINA TABELLE

$tabelle = RegTools::prendi_tabelle(0);


$SELECT_TABELLE = "\t<select name=\"tab[]\" onchange=\"scegli_campi_tab(this);\">\n";

for($i=0;$i<count($tabelle);$i++){
	
	$SELECT_TABELLE.="\t\t<option value=\"{$tabelle[$i]['id_table']}\" >{$tabelle[$i]['table_name']}</option>\n";
	
	list($CAMPI[$tabelle[$i]['id_table']])=RegTools::prendi_colonne_frontend($tabelle[$i]['table_name'],'column_name',false);
}

$JS="var campi=new Array();\n";

foreach($CAMPI as $idt=>$arr_c){
	
	$JS.="\t\tcampi[$idt]=new Array('".implode("','",$arr_c)."');\n";
}



$SELECT_TABELLE.="\t</select>\n";


	$files=array("sty/admin.css","sty/linguette.css","js/flussi.js");

	$OUT= openLayout1("Flussi di inserimento",$files);

	$OUT.=breadcrumbs(array("HOME","ADMIN","flussi di inserimento dati"));

	$OUT.="<h1>Gestione flussi di inserimento dati</h1>\n";
	
	$OUT.="<img src=\"../img/flussi.gif\" class=\"img-float\" alt=\"impostazioni registri\" />\n";
	
	echo $OUT;

	
	echo "	
	<script type=\"text/javascript\">
	
		var divs = new Array('nuovoflusso','adminflussi');
	
	
		function eti(ido){
			
			for (var i in divs){
				document.getElementById('cont-eti-'+divs[i]).style.display='none';
				document.getElementById('li-'+divs[i]).className='disattiva';
			}
			
			// attiva il selezionato
			document.getElementById('cont-eti-'+ido).style.display='';
			document.getElementById('li-'+ido).className='attiva';
			
		}
	
		$JS
	
	</script>
	";
	

	echo "	
<div id=\"contenitore-variabili\">
	<div id=\"box-etichette\">
		
		<ul class=\"eti-var-gr\">

		
			<li onclick=\"eti('nuovoflusso');\" id=\"li-nuovoflusso\" class=\"attiva\">Crea nuovo flussi</li>
			<li onclick=\"eti('adminflussi');\" id=\"li-adminflussi\" class=\"disattiva\">Amministrazione flussi</li>

		</ul>
	
	</div>";

	
	$CAMPO="<select name=\"campi_fk\" style=\"display:none\"><option>&nbsp;</option></select>\n";
	
	// LINGUETTA NUOVO FLUSSO
	echo "
	<div class=\"cont-eti\" id=\"cont-eti-nuovoflusso\" >
	
			<p><strong>Attenzione!</strong><br />
			Se si ripristina la configurazione di una tabella si annulleranno tutte le impostazioni finora definite per quella tabella per tutti i gruppi,<br />
			comprese le impostazioni per le sottomaschere. Usare questa funzione con cautela.</p>

			<form action=\"".$_SERVER['PHP_SELF']."?passo2\" method=\"post\" >
			
				<ol>
					<li id=\"modello\">$SELECT_TABELLE". " ". $CAMPO.
					" <a href=\"javascript:;\" onclick=\"add_tendina_fl();\">aggiungi</a> |".
					" <a href=\"javascript:;\" onclick=\"del_tendina_fl(this);\">elimina</a>
					</li>
				</ol>
				
				<input type=\"submit\" value=\"Vai al passo 2\" name=\"passo2\" />
				
			</form>

	
	</div>
	";
	
	
	// LINGUETTA AMMINISTRAZIONE FLUSSI
	echo "
	<div class=\"cont-eti\" id=\"cont-eti-adminflussi\" style=\"display:none;\">
	
			<p><strong>Attenzione!</strong><br />
			Se si ripristina la configurazione di una tabella si annulleranno tutte le impostazioni finora definite per quella tabella per tutti i gruppi,<br />
			comprese le impostazioni per le sottomaschere. Usare questa funzione con cautela.</p>


	
	</div>
	";
	
	
echo "</div><!-- fine contenitore -->\n\n";

echo "<pre>";

print_r($CAMPI);
echo closeLayout1();

?>