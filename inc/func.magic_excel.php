<?php
/**
* File di inclusione per la funzione magic_excel
* 
* @package VFront
* @subpackage Function-Libraries
* @author Mario Marcello Verona <marcelloverona@gmail.com>
* @copyright 2007-2010 M.Marcello Verona
* @version 0.96 $Id: func.magic_excel.php 819 2010-11-21 17:07:24Z marciuz $
* @license http://www.gnu.org/licenses/gpl.html GNU Public License
*/


/**
 * Funzione che genera un tasto "Scarica tabella in XLS".
 * Per utilizzare questa funzione generare la tabella HTML in una variabile (non quindi mediante l'uso di echo)
 * e PRIMA di stampare la tabella a video passare alla funzione il codice della tabella
 *
 * @param string $tabella La tabella HTML completa che si vuole trasformare in XSL
 * @param string $filename Nome del file che verr� mandato dal browser
 * @param string $titolo Titolo per il foglio Excel
 * @param string $action Reindirizzamento verso lo script mexcel.php
 * @param bool $toglibr Se vero elimina le andate a capo HTML dal contenuto delle celle. Di default "false"
 * @return string Blocco html con form verso il file mexcel.php che genera il foglio excel.
 */
function magic_excel($tabella,$filename,$titolo,$action='',$toglibr=false){
	
	
	// toglie i link dalla tabella
	$tabella=preg_replace("'(<a[^>]+>)|(</a>)'i","",$tabella);
	$tabella=preg_replace("'(<img.*alt=\"([^\"]*)\"[^>]+/>)'i","$2",$tabella);
	
	if($toglibr){
		
		$tabella=str_replace(array("<br>","<br />","<br/>")," @ ",$tabella);
	}
	
	$action = ($action=='') ? FRONT_DOCROOT."/mexcel.php" : $action;
	
	$str_arr=serialize(array('tab'=>$tabella,'tit'=>$titolo,'fn'=>$filename));
	
	$form ="<div class=\"mexcel-div\">\n";
	$form.="<form action=\"".$action."\" method=\"post\">";
	$form.="<input type=\"hidden\" name=\"mexcel\" value=\"".base64_encode($str_arr)."\" />";
	$form.="<span class=\"mexcel\">"._('Download table in xls:')."</span> <input type=\"image\" src=\"".FRONT_DOCROOT."/img/xls.gif\" name=\"mexcel_gen\" value=\"1\" alt=\""._("Download table in xls")."\" title=\""._("Download table in xls")."\" />";
	$form.="</form>\n";
	$form.="</div>\n";
	
	return $form;
	
}

?>