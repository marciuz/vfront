<?php
/**
 * VFront Cron 
 * @package VFront
 * @author M.Marcello Verona
 * @copyright 2007-2010 M.Marcello Verona
 * @version 0.96 $Id: cron.php 180 2008-12-25 06:37:32Z marciuz $
 * @license http://www.gnu.org/licenses/gpl.html GNU Public License 
 * 
 */

require("./inc/conn.php");

// Funzioni di CRON
// manutenzione delle cartelle temporanee


function cron_clean_dir($dir, $tieni_file_entro_gg=0){
	
	
	$gg=(60*60*24);
	
	$tempo_validita=$gg*$tieni_file_entro_gg;
	
	if (is_dir($dir)) {
	   if ($dh = opendir($dir)) {
	      while (($file = readdir($dh)) !== false) {
	      		
	      		$data_file=filemtime($dir .$file);
	      	
	      		if((time()-$tempo_validita)>$data_file && $file!='.' && $file!='..'){
	      			
	      			@unlink($dir .$file);
	      		}
	      	}
	        closedir($dh);
	    }
	}
}


/**
 * Funzione di cancellazione dei file presenti nella cartella /html
 * Si tratta delle tendine richiamate dinamicamente dalle schede con i record delle tabelle collegate 1 a molti
 * E' utile tenere per un po' di tempo questi file, in modo che sia il server che il client possano risparmiare
 * lavoro, di default vengono cancellati quelli più vecchi di trenta giorni.
 *
 * @param int $tieni_file_entro_gg
 */
function cron_clean_html($gg=0){
	
	$dir=FRONT_REALPATH."/files/html/";
	
	cron_clean_dir($dir,$gg);	
	
}



function cron_clean_tmp($gg=0){
	
	$dir=_PATH_TMP."/";
	
	cron_clean_dir($dir,$gg);	

}




function cron_clean_files_tmp($gg=0){
	
	$dir=FRONT_REALPATH."/";
	
	cron_clean_dir($dir,$gg);	

}


$DAYS_MIN = (isset($_GET['clearall'])) ? 0 : (int) $_SESSION['VF_VARS']['cron_days_min'];

cron_clean_html($DAYS_MIN);

cron_clean_tmp($DAYS_MIN);

cron_clean_files_tmp($DAYS_MIN);


?>