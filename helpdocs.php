<?php
/**
* Script che permette di vedere, carica e scaricare documenti. 
* I documenti sono poi visibili dal link "Documenti utili" presenti in home page
* 
* @package VFront
* @author Mario Marcello Verona <marcelloverona@gmail.com>
* @copyright 2007-2010 M.Marcello Verona
* @version 0.96 $Id: helpdocs.php 1076 2014-06-13 13:03:44Z marciuz $
* @license http://www.gnu.org/licenses/gpl.html GNU Public License
*/


include_once("./inc/conn.php");
include_once("./inc/layouts.php");

proteggi();

// Dimensione del max_upload preso dal php.ini (� indicato in M)
$max_kb=(int) ini_get('post_max_size') *1024 *1024;




/**
 * Dato un nome di file restituisce il mime/type dall'estensione utilizzata
 *
 * @param string $filename
 * @return string Il content-type per l'header
 */
function mime_content_type_($filename)
{
	
	$info=pathinfo($filename);

   $mime = array(
       'rtf' => 'application/rtf',
       'doc' => 'application/doc',
       'pdf' => 'application/pdf',
       'xls' => 'application/xls',
       'odt' => 'application/odt',
       'sxw' => 'application/sxw',
       'zip' => 'application/x-zip-compressed',
       'rar' => 'application/rar',
       'txt' => 'text/txt'
     
   );
   
   
   return $mime[$info['extension']];
}


/**
 * Dato un nome di file restituisce un frammento HTML per richiamare l'immagine associata. 
 * Le immagini sono presenti nella directory img/mime/ 
 *
 * @param string $filename
 * @return string HTML image
 */
function mime_content_type_image($filename)
{
	
	
   $mime = array(
       '.rtf' => 'doc.gif',
       '.doc' => 'doc.gif',
       '.pdf' => 'pdf.gif',
       '.xls' => 'xls.gif',
       '.odt' => 'txt.gif',
       '.sxw' => 'txt.gif',
       '.zip' => 'zip.gif',
       '.rar' => 'zip.gif',
       '.txt' => 'generic.gif'     
   );
   
   return "<img src=\"img/mime/".$mime[strrchr($filename,'.')]."\" alt=\"tipo: ".substr(strrchr($filename, '.'),1)."\" />";
}






// Se c'� inserisce il file
if(isset($_GET['add']) && count($_FILES)>0 && $_SESSION['user']['livello']>1){
	
		// il formato � corretto?
		if(strlen(mime_content_type_($_FILES['documento']['name']))==0){
			header("Location: ".$_SERVER['PHP_SELF']."?ko=tipo_non_valido");
			exit;
		}
		
		// Se non � troppo grande
		if($_FILES['documento']['size']>$max_kb){
			
			header("Location: ".$_SERVER['PHP_SELF']."?ko=max_kb");
			exit;
		}
		
		$NOME_FILE=preg_replace("'[^a-z0-9._-]+'i","_",$_FILES['documento']['name']);
		
//		echo $_FILES['documento']['tmp_name'] . " ---- ". _PATH_HELPDOCS."/".$NOME_FILE;
		
		
		$op=move_uploaded_file($_FILES['documento']['tmp_name'], _PATH_HELPDOCS."/".$NOME_FILE);
		
		if($op){
			header("Location: ".$_SERVER['PHP_SELF']."?ok");
			exit;
		}
		else{
			header("Location: ".$_SERVER['PHP_SELF']."?ko=operazione_non_riuscita");
			exit;
		}
	
	
}






// CANCELLAZIONE DEL FILE:


if(isset($_GET['del']) && $_SESSION['user']['livello']>1){
	
	$id_file= $_GET['del'];
	
	// PRENDI I FILES
	
	$dir= _PATH_HELPDOCS;
	
	// Open a known directory, and proceed to read its contents
	if (is_dir($dir)) {
	    if ($dh = opendir($dir)) {
	        while (($file = readdir($dh)) !== false) {
	        	if(!is_dir($file)){
	        	
		        	$docs[]=array('nome'=>$file, 
		        				  'type'=>mime_content_type_($dir."/".$file), 
		        				  'data'=>filectime($dir."/".$file),
		        				  'size'=>filesize($dir."/".$file)
		        				  );
	        	}
	        }
	        closedir($dh);
	    }
	}
	
	$docs=Common::ordina_matrice($docs,$data,'DESC');

	if(!is_file($dir."/".$docs[$id_file]['nome'])){
		header("Location: ".$_SERVER['PHP_SELF']."?ko=file_inesistente");
		exit;
	}
		

	$op=unlink($dir."/".$docs[$id_file]['nome']);
	
	if($op){
			header("Location: ".$_SERVER['PHP_SELF']."?eliminato");
			exit;
	}
	else{
			header("Location: ".$_SERVER['PHP_SELF']."?ko=operazione_non_riuscita");
			exit;
	}
	
}

// DOWNLOAD DEL FILE
	

else if(isset($_GET['doc'])){
	
	

	// PRENDI I FILES
	
	$dir= _PATH_HELPDOCS;
	$docs=array();
	$data='';
	
	// Open a known directory, and proceed to read its contents
	if (is_dir($dir)) {
	    if ($dh = opendir($dir)) {
	        while (($file = readdir($dh)) !== false) {
	        	if(!is_dir($file)){
	        	
		        	$docs[]=array('nome'=>$file, 
		        				  'type'=>mime_content_type_($dir."/".$file), 
		        				  'data'=>filemtime($dir."/".$file),
		        				  'size'=>filesize($dir."/".$file)
		        				  );
	        	}
	        }
	        closedir($dh);
	    }
	}
	
	$docs=Common::ordina_matrice($docs,$data,'DESC');
	
	
	$kk=(int) $_GET['doc'];
	
		if(file_exists(_PATH_HELPDOCS."/".$docs[$kk]['nome'])){
           header("Pragma: public");
           header('Expires: '.gmdate('D, d M Y H:i:s').' GMT');
           header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
           header("Cache-Control: private",false);
           header("Content-Type: application/force-download");
           header('Content-Disposition: attachment; filename="'.$docs[$kk]['nome'].'"');
           header("Content-Transfer-Encoding: binary");
           header('Content-Length: '.$docs[$kk]['size']);
           set_time_limit(0);
           @readfile(_PATH_HELPDOCS."/".$docs[$kk]['nome']) OR die("<html><body OnLoad=\"javascript: alert('Nessun file trovato');history.back();\" bgcolor=\"#F0F0F0\"></body></html>");
       
		}
		else{    
			
			openErrorGenerico(_("File not found!"),true);
			
		}
  exit;
	
}







$files=array("sty/tabelle.css","sty/admin.css","js/confirm_delete.js");	


echo openLayout1(_("Useful documents"),$files);

	echo breadcrumbs(array("HOME",_('useful documents')));

	echo "<h1>"._('Useful documents')."</h1>\n";

	if(isset($_GET['ko'])){
		
		switch($_GET['ko']){
			
			case "max_kb": echo "<p><strong class=\"var\">"._('Warning!')."</strong> "._("File too big. It is only possible to send a file up to this size")." ".($max_kb/1024)." kb</p>\n";
			break;
			
			case "tipo_non_valido": echo "<p><strong class=\"var\">"._('Warning!')."</strong> "._("not a valid file format. Permitted formats: doc, xls, pdf, rtf, zip, txt, odt, sxw")."</p>\n";
			break;
			
			case "operazione_non_riuscita": echo "<p><strong class=\"var\">"._('Warning!')."</strong> "
				 .sprintf(_("operation failed. If the type and size of file (%s kb maximum) were correct and the problem still happened,"),($max_kb/1024))
				 ." "._('contact the')."<a href=\"mailto:"._SYS_ADMIN_MAIL."\">"._('system administrator')."</a>.</p>\n";
			break;
		}
	}
	elseif (isset($_GET['ok'])){
		
		echo "<p><strong class=\"verde\">"._('File uploaded')."</strong></p>\n";
	}
	elseif(isset($_GET['eliminato'])){
		echo "<p><strong class=\"arancio\">"._('File correctly deleted.')."</strong></p>\n";
	}
	
	
	// PRENDI I FILES
	
	$dir= _PATH_HELPDOCS;
	
	$docs=array();
	$data='';
	
	// Open a known directory, and proceed to read its contents
	if (is_dir($dir)) {
	    if ($dh = opendir($dir)) {
	        while (($file = readdir($dh)) !== false) {
	        	if(!is_dir($file)){
	        	
		        	$docs[]=array('nome'=>$file, 
		        				  'type'=>mime_content_type_($dir."/".$file), 
		        				  'data'=>filemtime($dir."/".$file),
		        				  'size'=>filesize($dir."/".$file)
		        				  );
	        	}
	        }
	        closedir($dh);
	    }
	}
	
	$docs=Common::ordina_matrice($docs,$data,'DESC');
	
	echo "<div class=\"info\">
		<p>"._("This section contains documentation about using the application and the database.")
			." <br />"
			._('Left-click on the document name to open it, or right -click on the document name and select <em> Save target as </em> to download it to your computer')."<br />
		</p>
		</div>\n";
	
	if($_SESSION['user']['livello']>1){
		echo "<p><a href=\"javascript:;\" onclick=\"document.getElementById('add_record').style.display='';\">"._('Add file')."</a></p>\n";
	
	}
	
	
	echo "<table summary=\"Tabella file help\" class=\"tab-report-dip\">\n";
	
	echo "<tr>
		<th class=\"lilla\" colspan=\"2\">"._('Document')."</th>
		
		<th class=\"lilla\">"._('Last modified')."</th>
		<th class=\"lilla\">"._('Size')."</th>",
		($_SESSION['user']['livello']>1) ?
		"<th class=\"lilla\">"._('Delete')."</th>" : "",
		"</tr>\n
		";
		
//	$dir_down=str_replace(FRONT_DOCROOT,'',_PATH_HELPDOCS);
	
	for($i=0;$i<count($docs);$i++){
		
		echo "\t\t<tr><td>".mime_content_type_image($docs[$i]['nome'])."</td>
			<td><a href=\"?doc=$i\">".$docs[$i]['nome']."</a></td>
			<td>".date("d/m/Y H:i",$docs[$i]['data'])."</td>
			<td>".round($docs[$i]['size']/1024,0)." Kb</td>",
			($_SESSION['user']['livello']>1) ?
			"<td><a href=\"javascript:;\" onclick=\"confirm_delete_f(this,$i);\" >"._('delete')."</a></td>" : "",
			"</tr>";
	}
	
	echo "</table>\n";
	
	$ADD_FILE=
  '	
	<div id="add_record" style="display: none; top: 20%; left: 20%; width:500px; height: 230px; ">
 		 	 			
		<div style="text-align: right;">
		
		    <a href="javascript:;" onclick="document.getElementById(\'add_record\').style.display=\'none\';"> '._('Close').' X </a>
		
		</div>
		
		 			
		<div style="margin: 20px;">
		
		    <h3> '._('Add file').'</h3>
		
		    <p>'._('The following file extensions are allowed: <strong>doc, xls, pdf, rtf, zip, txt, odt, sxw').'</strong></p>
		    
		    <form method="post" enctype="multipart/form-data" action="'.$_SERVER['PHP_SELF'].'?add"> 
		    	
		    	 <label for="immagine"><strong>'._('File to add').'</strong></label><br/>
  				<input type="file" name="documento" id="documento" size="60" />
				<br /><br />
			    <input name="invia" value="   '._('Send').'   " type="submit" />
		    
		    </form>
		
		</div>

 	
</div>';
  
  if($_SESSION['user']['livello']>1){
  	echo $ADD_FILE;
  }
  
	
echo closeLayout1();
	
?>