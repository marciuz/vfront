<?php
/**
 * File sperimentale che gestisce l'output delle tabelle in XML 
 * con i record delle sottomaschere
 * 
 * @desc Output XML comprese le sottomaschere
 * @package VFront
 * @subpackage VFront_XML
 * @author M.Marcello Verona
 * @copyright 2007-2010 M.Marcello Verona
 * @version 0.96 $Id: xmlsub.php 1078 2014-06-13 15:35:53Z marciuz $
 * @license http://www.gnu.org/licenses/gpl.html GNU Public License 
 */


require("../inc/conn.php");
require("../inc/func.xmlize.php");




/**
 * Funzione che genera l'XML con anche i record delle sottomaschere. 
 * Partendo da una tabella prestabilita con anche la gestione 
 * dei record figli presenti nelle sottomaschere attivate.
 *
 * @param int $oid ID della tabella per il gruppo
 * @param string $filename Nome della tabella
 * @param bool $sub Mostra anche i record delle sottomaschere
 * @param int $offset Offset riportato nell'XML
 * @param int $tot Totale di record, riportato nell'XML
 * @param string $xsl Eventuale foglio di stile da associare
 * @param string $dtd Eventuale DTD da associare
 * @param bool $header Manda un header di tipo "Content-type=XML" (default: true) 
 * @return string XML output 
 * @see xml.php
 * @todo Questa funzione se ottimizzata pu� sostituire la procedura richiamata dal file xml.php
 */
function xmlize_table_sub($oid,$filename=null,$sub=true,$offset=0,$tot=0,$xsl='',$dtd='',$header=true){
	
	global  $vmsql, $vmreg;
	
	
	$cols=RegTools::prendi_colonne_frontend($oid,"column_name,table_name, orderby, orderby_sort",true);
	
//	var_dump($cols);
	


	$ORDER_BY = ($cols[2][0]!='') ? "ORDER BY ".$cols[2][0]." ".$cols[3][0] : "";

	$OFF= ($offset==0) ? "" : "$offset";
	
	$LIMIT = ($tot==0) ? "" : $vmsql->limit($tot,$OFF);
	
	$sql="SELECT ".implode(",",$cols[0])." FROM ".$cols[1][0]." $ORDER_BY  $LIMIT";
	
	
	
	$q = $vmsql->query($sql);
	
	if($vmsql->num_rows($q)==0){
		
		return null;	
	}
	
	// Inizia a fare l'xml
	
	
	
	$XML="";
	
	
	
	$XML.= ($header) ? "<?xml version='1.0' encoding='utf-8'?>\n" : "";
	
	$XML.= ($dtd) ? "<!DOCTYPE vfront SYSTEM \"$dtd\">\n" : "";
	
	$XML.= ($xsl!='') ? "<?xml-stylesheet type=\"text/xsl\" href=\"$xsl\" ?>\n" : "";
	
	$XML.="<recordset tot=\"$tot\">\n";
	
	if($offset==0){
		$auto_offset=true;
		$offset =1;
	}else{
		$auto_offset=false;
	}
	
	$fkk = RegTools::prendi_K_relazione_sub($oid);
	
	while($RS=$vmsql->fetch_assoc($q)){
		
		$XML.="\t".xmlize_campo('row',array("offset"=>$offset))."\n";
		
		foreach($RS as $k=>$val){
			
			$val = Common::vf_utf8_encode(trim($val));
			
			if($val!="" && !is_numeric($val)){
				$val="<![CDATA[".$val."]]>";
			}
			
			$XML.="\t\t".xmlize_campo($k,array());
			$XML.=trim($val);
			$XML.="</$k>\n";
			
			if($fkk[0][0]==$k){
		
//				echo "ORA!";
				$XML.=xmlize_sottomaschera($val,$oid);

			}
		}
		
		
		
		$XML.="\t</row>\n";
		
		if($auto_offset){
			$offset++;
		}
		
	}
	
	$XML.="</recordset>\n";
	
	
	
	
	if(is_null($filename)) return $XML;
	else{
		
		$fp =fopen($filename,"w");
		fwrite($fp,$XML);
		fclose($fp);
		return true;
	}
	
}


/**
 * Funzione di generazione di XML da una sottomaschera
 *
 * @param int $id_parent id_table della tabella parent di questa sottomaschera
 * @param int $oid ID della sottomaschera considerata
 * @param int $gid ID del gruppo considerato
 * @param bool $solo_campi_visibili Se vera mostra solo i campi con diritti SELECT della sottomaschera
 * @return string Frammento di XML
 */
function xmlize_sottomaschera($id_parent,$oid,$gid=0,$solo_campi_visibili=false){

	global  $vmsql, $vmreg, $db1;

	$sub=RegTools::prendi_sottomaschere($oid);
	
	
	for($i=0;$i<count($sub);$i++){
		
		$order_sub = ($sub[$i]["orderby_sub"]!='') ?  "ORDER BY ".$sub[$i]["orderby_sub"] : "";
		
		$sql_sub="SELECT * FROM ".$sub[$i]['nome_tabella']." WHERE ".$sub[$i]['campo_fk_sub']."='$id_parent' $order_sub limit 10";
		
		$q_sub=$vmsql->query($sql_sub);
		
		if($vmsql->num_rows($q_sub)>0){
		
			$XMLSUB.="\t\t<subrecordset tabella=\"".$sub[$i]['nome_tabella']."\" nomefrontend=\"".$sub[$i]['nome_frontend']."\">\n";
		
			$offset=1;
			
			while($RS=$vmsql->fetch_assoc($q_sub)){
		
				$XMLSUB.="\t\t\t".xmlize_campo('subrow',array("offset"=>$offset))."\n";
				
				foreach($RS as $k=>$val){
					
					$val = Common::vf_utf8_encode(trim($val));
					
					if($val!="" && !is_numeric($val)){
						$val="<![CDATA[".$val."]]>";
					}
					
					$XMLSUB.="\t\t\t\t".xmlize_campo($k,array());
					$XMLSUB.=$val;
					$XMLSUB.="</$k>\n";
				}
				
				$XMLSUB.="\t\t\t</subrow>\n";
				
				$offset++;
				
			}
			
			$XMLSUB.="\t\t</subrecordset>\n";
		
		}
		
	}
	
	return $XMLSUB;
}

//header('Content-type: text/xml');
//echo xmlize_table_sub(1,null,true,0,50);


	$OID = (int) $_GET['oid'];
	$SUB = (bool) $_GET['sub'];
	
	$xsl = (isset($_GET['type']) && $_GET['type']=='XML') ? "" : FRONT_DOCROOT."/xml/".RegTools::oid2name($OID)."/stile.xml";

	
	
// SINGOLO RECORD BASATO SULLA SERIE ORDINATA
	if(is_numeric($_GET['c'])){
		$XML = xmlize_table_sub($OID,null,$SUB,$_GET['c'],$tot_records,$xsl);
		
	}
	
	
	// INTERVALLO DI RECORD BASATO SULLA SERIE ORDINATA
	elseif(preg_match("'([0-9]+),([0-9]+)'",$_GET['c'],$match)>0){
		
		$offset=intval($match[1]);
		$limit=intval($match[2]);
		
		$XML = xmlize_table_sub($OID,null,$SUB,$offset,$limit,$xsl);
		
//		$XML = xmlize("SELECT ".$campi_tabella." FROM $tabella ORDER BY $orderby_doppio LIMIT $offset,$limit",null,true,false,$tot_records,$xsl,$dtd);
	}
	
	
	// TUTTI I RECORD 
	elseif($_GET['c']=='all'){
		
		$XML = xmlize_table_sub($OID,null,$SUB,0,0,$xsl);
//		$XML = xmlize("SELECT ".$campi_tabella." FROM $tabella ORDER BY $orderby_doppio ",null,true,false,$tot_records,$xsl);
	}
	
	
	// RECORD PER SINGOLO ID (DA CAMPO PK)		
	elseif($_GET['id']>0){
		
		$XML = xmlize_table_sub($OID,null,$SUB,$offset,$limit,$xsl);
//		$XML = xmlize("SELECT ".$campi_tabella." FROM $tabella WHERE $PK=".intval($_GET['id'])." ORDER BY $orderby_doppio ",null,true,false,$tot_records,$xsl,$dtd);
	}

	header("Content-Type: text/xml; charset=".FRONT_ENCODING);
	echo $XML;


?>