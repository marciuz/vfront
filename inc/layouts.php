<?php
/**
* Libreria di funzioni per la creazione del layout di pagina dell'applicazione. 
* 
* @package VFront
* @subpackage Function-Libraries
* @author Mario Marcello Verona <marcelloverona@gmail.com>
* @copyright 2007-2010 M.Marcello Verona
* @version 0.96 $Id: layouts.php 1158 2015-11-25 20:59:07Z marciuz $
* @license http://www.gnu.org/licenses/gpl.html GNU Public License
*/


/**
 * Scrive l'HTML di apertura di una pagina
 *
 * @param string $title Il titolo della pagina mostrato nel tag TITLE
 * @param array $files Un array di files da includere. Possono essere javascript o css, la funzione li smister� automaticamente
 * @param string $tipo Può essere ad esempio "sottomaschera". In tal caso il layout apparir� differente.
 * @return string HTML 
 */
function openLayout1($title,$files=array(),$tipo=''){

	$GLOBALS['layout_APERTO']=1;
	
	$css = array();
	$js  = array();
	
    // default js
    $js[]="js/jquery/jquery.min.js";
    
// SMISTA I FILES CSS E JS MANDATI ALLA FUNZIONE	
	foreach($files as $file){

		if(substr($file,-4,4)==".css"){
			$css[]=$file;
		}
		elseif(substr($file,-3,3)==".js" || preg_match("|\.js\??|i",$file)){
			$js[]=$file;
		}
	
	}
	
	
$THEME=(isset($_SESSION['VF_VARS']['layout']) && is_dir(FRONT_ROOT."/themes/".$_SESSION['VF_VARS']['layout']."/"))
	   ? $_SESSION['VF_VARS']['layout'] : 'default';

if(function_exists('browser_detection')){
	
	$mobile_test=browser_detection('mobile_test');
}
else{
	include_once(FRONT_ROOT."/inc/func.browser_detection.php");
	$mobile_test=browser_detection('mobile_test');
}
	
if($mobile_test){
	
	$meta_mobile="<meta name=\"viewport\" content=\"width=320; initial-scale=1.0; maximum-scale=1.0; user-scalable=0;\" />\n";
	$meta_mobile.="<meta name=\"format-detection\" content=\"telephone=no\" />\n";
}
else{ 
	$meta_mobile='';
}


$OUT= "<!DOCTYPE html PUBLIC \"-//W3C//DTD XHTML 1.0 Transitional//EN\" \"http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd\">
<html xmlns=\"http://www.w3.org/1999/xhtml\" lang=\"it\">
<head>
<title>$title</title>
<meta http-equiv=\"Content-Type\" content=\"text/html; charset=".FRONT_ENCODING."\" />$meta_mobile
<link rel=\"shortcut icon\" href=\"".FRONT_DOCROOT."/themes/$THEME/favicon.png\" type=\"image/x-icon\" />
<style type=\"text/css\" media=\"all\">
";


	# CSS
	
	if(isset($_SESSION['VF_VARS']['join_css']) && $_SESSION['VF_VARS']['join_css']==1){
		
		$array_files[]=FRONT_DOCROOT."/sty/base.css";
		$array_files[]=FRONT_DOCROOT."/themes/".$THEME."/".$THEME.".css";
		
		if($tipo=="sottomaschera"){
			
			$array_files[]= FRONT_DOCROOT."/sty/sottomaschera.css";
		}
		
		foreach($css as $css){
			$array_files[]=FRONT_DOCROOT."/$css";
		}
		
		$OUT.= "@import \""._PATH_TMP_HTTP."/".file_cat($array_files,'css');
	}
	
	else{

		# DEFAULT CSS
			$OUT.= "@import \"".FRONT_DOCROOT."/sty/base.css\";\n";
			
		# THEMES
			$OUT.= "@import \"".FRONT_DOCROOT."/themes/".$THEME."/".$THEME.".css\";\n";
			
			
			if($tipo=="sottomaschera"){
				
				$OUT.= "@import \"".FRONT_DOCROOT."/sty/sottomaschera.css\";\n";
			}
		
	
		# CSS
		foreach($css as $css){
			$OUT.= "@import \"".FRONT_DOCROOT."/$css\";\n";
		}
		
	}
	
$OUT.= "</style>\n";
$OUT.="<link media=\"only screen and (max-device-width: 480px)\" href=\"".FRONT_DOCROOT."/sty/mobile.css\" type=\"text/css\" rel=\"stylesheet\" />\n";


	// JS LANG
	
	if(isset($_SESSION['VF_VARS']['lang']) && $_SESSION['VF_VARS']['lang']!=''){
		
		$jslang = strtolower(substr($_SESSION['VF_VARS']['lang'],0,2));
	}
	else{
		
		$jslang = strtolower(substr(FRONT_LANG,0,2));
	}

	$OUT.="<script type=\"text/javascript\" src=\"".FRONT_DOCROOT."/js/lang/vfront-".$jslang.".js\"></script>\n";
	
	
	
	
	
	# JS
	if(count($js)>0){
		
		$cache_js=0;
		
		###########################
		// cache js
		
		if($cache_js==1){
			
			$cache_js_hash=md5(implode("",$js));
			
			$cache_js_path=_PATH_TMP."/".$cache_js_hash.".js";
			$cache_js_path_abs=_PATH_TMP_HTTP."/".$cache_js_hash.".js";
			
			if(is_file($cache_js_path)){
				
				$OUT.= "<script type=\"text/javascript\" src=\"$cache_js_path_abs\" ></script>\n";
			}
			else{
				
				// create the file
				$fpjs=fopen($cache_js_path,'a');
				
				foreach($js as $jsf){
					
					list($jsf)=explode("?",$jsf);
					
					fwrite($fpjs,join("",file(FRONT_ROOT."/".$jsf))."\n");
				}
				fclose($fpjs);
				
				$OUT.= "<script type=\"text/javascript\" src=\"$cache_js_path_abs\" ></script>\n";
			}
			
		
		} # end cache js
		else{
			
			foreach($js as $js){
				$OUT.= "<script type=\"text/javascript\" src=\"".FRONT_DOCROOT."/$js\" ></script>\n";
			}
			
		}
	
		
	}


$OUT.= "
</head>";


		
		
// LAYOUT 
	
	
	
	if($tipo!="sottomaschera" && $tipo!='popup' && $tipo!='popup1'){
	    
		
		$OUT.= "<body>";

		$OUTMENU =(isset($_SESSION['user']['livello'])) ?  menu($_SESSION['user']['livello']) : "";
		
		$OUT.="<div id=\"header\"><div id=\"version\">VERSION ".Common::vfront_version()."</div><span>&nbsp;</span>".$OUTMENU."</div>\n";
	
	}
	elseif ($tipo=='popup'){
	    
	    $OUT.= "<body class=\"popup\">";
	    $OUT.="<div id=\"header\"><div id=\"version\">VERSION ".Common::vfront_version()."</div><span>&nbsp;</span></div>\n";
	}
	elseif ($tipo=='popup1'){
	    
	    $OUT.= "<body class=\"popup1\">";
	    $OUT.="<div id=\"header-popup\"></div>\n";
	}
	else{
		$OUT.= "<body>";
		$OUT.="<div id=\"header\"><div id=\"version\">VERSION ".Common::vfront_version()."</div><span>&nbsp;</span></div>\n";
	}

	
	
	
	
$OUT.= "<div id=\"contenuto\">
	<!--CONTENUTO-->
	";

	

	return $OUT;
}


/**
 * Scrive l'HTML di chiusura di una pagina
 *
 * @param mixed $back
 * @return string HTML 
 */
function closeLayout1($back="", $no_identity=false){
	
	$OUTPUT_CLOSE="<!-- Fine contenuto-->
	</div>";
	

	if(isset($_SESSION['user'])){
		$nome_visualizzato= ($_SESSION['user']['nome']." ".$_SESSION['user']['cognome']!=' ') ? $_SESSION['user']['nome']." ".$_SESSION['user']['cognome'] : $_SESSION['user']['email'];
		$OUTPUT_CLOSE.= ($no_identity) ? '' : "<div id=\"identita\">"._("Logged in as")." <br /><a href=\"".FRONT_DOCROOT."/dati_personali.php\">".$nome_visualizzato."</a> ("._('group').":". Common::gid2group_name($_SESSION['gid']).")</div>\n";
	}
	
	if($back!=""){
		$OUTPUT_CLOSE.="<div id=\"back-dx\">&nbsp;</div>\n";
	
	}
	
	
	if($GLOBALS['DEBUG_SQL_SHOW_QUERY']===true){

		if(!isset($GLOBALS['DEBUG_SQL_STRING'])){
			$GLOBALS['DEBUG_SQL_STRING']=array();
		}
		
		$LISTA_SQL = str_replace(array("\n","\t")," ",implode("<br /><br />",$GLOBALS['DEBUG_SQL_STRING']));
		
		$sum_time=0;
		
		foreach($GLOBALS['DEBUG_SQL_STRING'] as $str){
			list($ms,$null)=explode(" ---",$str,2);
			$sum_time+= (double) $ms;
		}
		
		$OUTPUT_CLOSE.= "
		<script type=\"text/javascript\">
		
			/* <![CDATA[ */
			
			var xdebug = window.open('', 'xdebug', 'width=800,height=600,toolbar=yes, location=no,status=yes,menubar=no,scrollbars=yes,resizable=yes'); 
			with (xdebug.document) {
			  open('text/html', 'replace');
			  write(\"<html><head><title>VFront DEBUG</title></head><body><code>"
				.str_replace("&lt;br /&gt;","<br />",htmlentities(addslashes($LISTA_SQL), ENT_QUOTES, FRONT_ENCODING))
				."<br /><br />"
				."<strong>". count($GLOBALS['DEBUG_SQL_STRING'])."</strong> queries in <strong>".$sum_time."</strong> sec"
				."</code></body></html>\");
			  close();
			}
			
			/* ]]> */
		
		</script>\n";
	}
	
	$OUTPUT_CLOSE.="\n</body>\n</html>";
	
	return $OUTPUT_CLOSE;
}

/**
 * Scrive l'HTML di chiusura di una pagina
 *
 * @return string HTML 
 */
function closeLayout2(){
	
	return "\n</div>\n</body>\n</html>";
	
}


/**
 * Scrive l'HTML di chiusura di una pagina
 *
 * @param int $livello Livello di amministrazione dell'utente che ha fatto login
 * @return string HTML 
 */
function menu($livello=0){
	
	$MENU=array();
	
/*	
	if($livello>0){
		$MENU[]="<a href=\"statistiche.php\">Statistiche</a>";
		
	}
*/	
	if($livello>0){
		$MENU[]="<a href=\"".FRONT_DOCROOT."/\">"._('Home')."</a>";	
	}	


	if($livello>1){
		$MENU[]="<a href=\"".FRONT_DOCROOT."/admin/\">"._('Administration')."</a>";	
	}
	
	
	$MENU[]="<a href=\"".FRONT_DOCROOT."/index.php?logout\">"._('Logout')."</a>";
	
	return "<ul><li>".implode("</li><li>", $MENU)."</li></ul>";
	
}


/**
 * Funzione di gestione a video degli errori. 
 * Se lo script in azione va in errore � possibile interrompere lo script e generare l'apertura
 * di un errore visibile su pagina.
 * La funzione mostra un errore generico a video e manda un'email all'amministratore ed 
 * allo sviluppatore con il dettaglio dell'errore.
 *
 * @param string $tipo_query
 * @param string $messaggio_completo
 */
function openError($err){
	
	
	// Elimina l'output generato nel contenuto di pagina e vai oltre...
	if(isset($GLOBALS['layout_APERTO']) && $GLOBALS['layout_APERTO']==1)
	ob_clean();

	$msg='';

	foreach ($err as $k=>$v){

		$msg.=$k.": $v\n";
	}


	if(isset($_SESSION['VF_VARS']['send_debug_email'])){

		// manda una email per il debug
		@mail(_SYS_ADMIN_MAIL.","._DEV_MAIL,"["._NOME_PROJ." DB] "._('DB error in'),$msg);
	}
	
	
	$OUT= openLayout1(_("Database query problem"));
	$OUT.= "<h1 class=\"var\">"._('Database query problem')."</h1>\n";
	
	$OUT.= "<p>"._("The operation generated an anomaly.")."<br />
	"._("If this event persists, contact")."
	<a href=\"mailto:"._SYS_ADMIN_MAIL."\">"._("the system administrator")."</a>.<br />
	"._("We apologise for the inconvenience.")."</p>
	<p><a href=\"index.php\">"._("Back to home")."</a></p>\n";

	if(Common::is_admin()){

		$OUT.= "<code>".nl2br($msg)."</code>\n";
		$OUT.= "<p>"._('<em>Note</em>: this debug message is showed only because you\'re admin!')."</p>\n";
		
	}


	$OUT.= closeLayout1('', true);

	print $OUT;
	
	exit;
	
}


/**
 * Funzione che richiama un errore esplicitamante
 * Pu� essere richiamata nel codice dove si voglia.
 * In caso di condizioni critiche si pu� scegliere di mandare una email all'amministratore di sistema (di default=true)
 * E' possibile inoltre impostare un testo specifico, in caso contrario verr� stampato il messaggio di default
 *
 * @param string $messaggio
 * @param bool $email
 * @param string $testo_custom
 */
function openErrorGenerico($messaggio,$email=true, $testo_custom=""){
	
	
	// Elimina l'output generato nel contenuto di pagina e vai oltre...
	if(isset($GLOBALS['layout_APERTO']) && $GLOBALS['layout_APERTO']==1)
	ob_clean();	
	
	if($email){
		$testo=sprintf(_("This problem occurred on %s"),date("d/m/Y \a\l\l\e H:i:s")).": $messaggio\n".
			   _("on server ").$_SERVER['HTTP_HOST']." (".$_SERVER['REMOTE_ADDR'].")\n";
		
		// manda una email per il debug
		mail(_SYS_ADMIN_MAIL.","._DEV_MAIL,"["._NOME_PROJ." Error] $messaggio",$testo);
	}
	
	echo openLayout1($messaggio, array("sty/base.css"));
	echo "<h1 class=\"var\">$messaggio</h1>\n";
	
	if($testo_custom==""){
		echo "<p>"._("The operation generated an anomaly.")."<br />
		"._("If this event persists, contact")."
		<a href=\"mailto:"._SYS_ADMIN_MAIL."\">"._("the system administrator")."</a>.<br />
		"._("We apologise for the inconvenience.")."</p>
		<p><a href=\"index.php\">"._("Back to home")."</a></p>\n";
	}
	else{
		
		echo "<p>".$testo_custom."</p>
		<p><a href=\"".FRONT_DOCROOT."/index.php\">"._("Back to home")."</a></p>\n";
	}
	
	
	
	echo closeLayout1();
	
	exit;
	
}


function file_cat($array_files,$type='css'){
	
	$file_cat[$type]='';
	
	for($i=0;$i<count($array_files);$i++){
		
		$file_cat[$type].=file_get_contents($array_files[$i]);
	}
	
	$file_cat[$type]=preg_replace("/(\n)+|(\t)+| +/si",' ',$file_cat[$type]);
	
	$hash=md5($file_cat[$type]);
	
	$fn=_PATH_TMP."/".$hash.".$type";
	
	if(!file_exists($fn)){
		$fp=fopen($fn,'w');
		fwrite($fp,$file_cat[$type]);
		fclose($fp);
	}
	
	return $hash.".".$type;
}


function breadcrumbs($array,$separator=" &raquo; "){

	$OUT="<div id=\"briciole\">";

	$c=1;

	foreach($array as $url=>$name){

		if($c>1) $OUT.=$separator;

		if($name=='HOME'){

			$OUT.="<a href=\"".FRONT_DOCROOT."/\">"._('home')."</a>";
		}
		else if($name=='ADMIN'){

			$OUT.= ($c==count($array)) ? _('administration')
				: "<a href=\"".FRONT_DOCROOT."/admin/\">"._('administration')."</a>";
		}
		else{
            
            // Multibyte support?
            if(function_exists('mb_strtolower')){
                $OUT.=($c==count($array)) ? mb_strtolower($name, FRONT_ENCODING) : "<a href=\"$url\">".mb_strtolower($name, FRONT_ENCODING)."</a>";
            }
            else{
                $OUT.=($c==count($array)) ? strtolower($name) : "<a href=\"$url\">".strtolower($name)."</a>";
            }
		}

		$c++;
	}

	$OUT.="</div>\n";

	return $OUT;
	
}
