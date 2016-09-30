<?php
/**
* Libreria di funzioni per la trasformazione delle query al database in file XML. 
* La libreria e' utilizzata sia per le chiamate AJAX nella scheda che per altre funzioni di VFront,
* ad esempio i report.
* 
* @package VFront
* @subpackage Function-Libraries
* @author Mario Marcello Verona <marcelloverona@gmail.com>
* @copyright 2007-2010 M.Marcello Verona
* @version 0.96 $Id: func.xmlize.php 1077 2014-06-13 13:44:31Z marciuz $
* @license http://www.gnu.org/licenses/gpl.html GNU Public License
*/





/**
 * Funzione che genera l'XML da una data query. 
 * La funzione e' utilizzata in molte occasioni da VFront.
 *
 * @param string $sql SQL SELECT che genera l'XML
 * @param string $filename Eventuale nome del file, se è necessario scriverlo. In caso contrario l'XML viene mandato in output
 * @param bool $header Scrive le intestazioni XML
 * @param int $offset OFFSET della query SQL
 * @param int $tot TOT di record dei risultati della query SQL
 * @param string $xsl Se presente applica un file di stile XSL all'XML
 * @param string $dtd Se presente genera ed applica un DTD all'XML
 * @return string XML risultato della query
 */
function xmlize($sql,$filename=null,$header=false,$offset=0,$tot=0,$xsl='',$dtd=''){
	
	global  $vmsql, $vmreg, $db1;
	
	$q = $vmsql->query($sql);
	
	$n_rows=$vmsql->num_rows($q);
	
	if($n_rows==0){
		
		return null;	
	}
	
	// Inizia a fare l'xml
	
	
	
	$XML="";
	
	$XML.= ($header) ? "<?xml version='1.0' encoding='utf-8'?>\n" : "";
	
	$XML.= ($dtd) ? "<!DOCTYPE vfront SYSTEM \"$dtd\">\n" : "";
	
	$XML.= ($xsl!='') ? "<?xml-stylesheet type=\"text/xsl\" href=\"$xsl\" ?>\n" : "";
	
	if($offset===false){
		$auto_offset=true;
		$offset =1;
	}else{
		$auto_offset=false;
		$offset++;
	}
	
	if(preg_match("/FROM +([a-z0-9_]+)/si",$sql,$finded)){
		
		$tablename=$finded[1];
	}
	else{
		$tablename='';
	}
	
	$XML.="<recordset tot=\"$tot\" minoffset=\"$offset\" maxoffset=\"". ($offset+($n_rows-1))."\" tablename=\"$tablename\">\n";
	
	
	
	while($RS=$vmsql->fetch_assoc($q)){
		
		$XML.="\t".xmlize_campo('row',array("offset"=>$offset))."\n";
		
		foreach($RS as $k=>$val){

            if($db1['dbtype']=='oracle'){
                $k=strtoupper($k);
            }
			
			//$val = Common::vf_utf8_encode(trim($val));
			
			$val = trim($val);
			
			if($val!="" && !is_numeric($val)){
				$val="<![CDATA[".$val."]]>";
			}
			
			$XML.="\t\t".xmlize_campo($k,array());
			$XML.=$val;
			$XML.="</$k>\n";
		}
		
		$XML.="\t</row>\n";
		
		$offset++;
		
	}
	
	$XML.="</recordset>";
	
	
	
	
	if(is_null($filename)) return $XML;
	else{
		
		$fp =fopen($filename,"w");
		fwrite($fp,$XML);
		fclose($fp);
		return true;
	}
	
}


/**
 * Funzione che genera l'XML per uno specifico campo
 *
 * @param string $tag Il nome del campo (che diverr� il nome del tag)
 * @param array $attr Array di attributi (nome_attributo=>valore)
 * @return string XML
 */
function xmlize_campo($tag,$attr){
	
		$attributi="";
	
		foreach($attr as $k=>$val){
			
			$attributi .=" $k=\"$val\"";
		}
	
		return "<".$tag.$attributi.">";
	
}

?>