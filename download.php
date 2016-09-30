<?php
/**
* Script di download. 
* Attraverso questo script vengono processati i file allegati e spediti con un header di tipo attachment.
* Qualora richiesto, viene creato un file .zip con più allegati
* 
* @package VFront
* @author Mario Marcello Verona <marcelloverona@gmail.com>
* @copyright 2007-2010 M.Marcello Verona
* @version 0.96 $Id: download.php 1078 2014-06-13 15:35:53Z marciuz $
* @license http://www.gnu.org/licenses/gpl.html GNU Public License
*/


include("./inc/conn.php");

proteggi(1);

if(isset($_GET['type']) && $_GET['type']=='all'){
	
	require_once("./inc/EasyZIP.class.php");
	
	// PRENDI CON UNA QUERY TUTTI I FILES ED I NOMI
	// deduce da un ID tutti gli altri
	
	$id_pilota = (int) str_replace(_BASE64_PASSFRASE,'',base64_decode($_GET['idr']));
	
	$sql="SELECT t1.codiceallegato, t1.nomefileall, ".$vmreg->concat("'allegati_',t1.tipoentita,t1.codiceentita")."
			FROM "._TABELLA_ALLEGATO." t1, "._TABELLA_ALLEGATO." t2
			WHERE t2.codiceallegato=$id_pilota
			AND t1.codiceentita=t2.codiceentita
			AND t1.tipoentita=t2.tipoentita
			ORDER BY nomefileall";
	
	$q=$vmreg->query($sql);
	
	$num_rows=$vmreg->num_rows($q);
	
	// se ci sono errori
	if($num_rows==0){
		
		echo _("Error, file not found");
	}
	
	$dir=md5(rand());
	
	// creo la directory
	mkdir(_PATH_TMP."/$dir");
	
	$z = new EasyZIP;
 	
	chdir(_PATH_TMP."/$dir/");
	
	// COPIA TUTTI I FILES in una tabella temporanea

	while($RS=$vmreg->fetch_row($q)){
		
			
		$test=@copy(_PATH_ATTACHMENT."/".$RS[0].".dat",$RS[1]);
		
		$z -> addFile($RS[1]);
		
		$nome_origine_all[]=$RS[1];
		
		$nome_zip=$RS[2];
	}
	
	
	
	$z -> zipFile("../$nome_zip.zip");
	
	
	
	// elimino la directory
	
	for($k=0;$k<count($nome_origine_all);$k++){
		unlink($nome_origine_all[$k]);
	}
	
	chdir(dirname(__FILE__));
	
	rmdir(_PATH_TMP."/$dir");
 
	if(file_exists(_PATH_TMP."/$nome_zip.zip")){
           header("Pragma: public");
           header('Expires: '.gmdate('D, d M Y H:i:s').' GMT');
           header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
           header("Cache-Control: private",false);
           header("Content-Type: application/zip");
           header('Content-Disposition: attachment; filename="'.$nome_zip.'.zip"');
           header("Content-Transfer-Encoding: binary");
           header('Content-Length: '.filesize(_PATH_TMP."/$nome_zip.zip"	));
           set_time_limit(0);
           @readfile(_PATH_ATTACHMENT."/$nome_zip.zip") OR die("<html><body OnLoad=\"javascript: alert('Nessun file trovato');history.back();\" bgcolor=\"#F0F0F0\"></body></html>");
        
           unlink(_PATH_TMP."/$nome_zip.zip"); 
   	}
	else{
		die("<html><body onload=\"javascript: alert('"._('No file to download!')."');history.back();\" bgcolor=\"#F0F0F0\"></body></html>");
	}

	
	
	exit;
	
}
elseif(isset($_GET['f'])){

	$id_file = (int) str_replace(_BASE64_PASSFRASE,'',base64_decode($_GET['f']));

	$sql="SELECT codiceallegato, nomefileall
			FROM "._TABELLA_ALLEGATO."
			WHERE codiceallegato=$id_file
			";
	
	$q=$vmreg->query($sql);
	$num_rows=$vmreg->num_rows($q);
	
		
	// se ci sono errori
	if($num_rows==0){
		
		echo _("Error, file not found");
		exit;
	}
	
	$RS=$vmreg->fetch_row($q);
	
	
	


	
	

	$dir=md5(rand());
	
	// creo la directory
	mkdir(_PATH_TMP."/$dir");
	
	$file_originale=_PATH_TMP."/$dir/".$RS[1];
	

	
	
	$test=@copy(_PATH_ATTACHMENT."/".$RS[0].".dat",$file_originale);
		
	if(file_exists($file_originale)){
           header("Pragma: public");
           header('Expires: '.gmdate('D, d M Y H:i:s').' GMT');
           header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
           header("Cache-Control: private",false);
           header("Content-Type: application/force-download");
           header('Content-Disposition: attachment; filename="'.$RS[1].'"');
           header("Content-Transfer-Encoding: binary");
           header('Content-Length: '.filesize($file_originale));
           set_time_limit(0);
           @readfile($file_originale) OR die("<html><body OnLoad=\"javascript: alert('"._('File not found')."');history.back();\" bgcolor=\"#F0F0F0\"></body></html>");
         
   	}	   
	else{
		die("<html><body onload=\"javascript: alert('"._('No file to download!')."');history.back();\" bgcolor=\"#F0F0F0\"></body></html>");
	}

	unlink($file_originale);
	rmdir(_PATH_TMP."/$dir");

}


?>