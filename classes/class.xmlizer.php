<?php

class XMLizer {
	
	
	/**
	 * Link to DB
	 *
	 * @var object|resource
	 */
	var $dblink;
	
	/**
	 * DB connection parameters
	 *
	 * @var array
	 */
	var $db1;
	
	/**
	 * Send XML http header 
	 *
	 * @var bool
	 */
	var $header=true;
	
	/**
	 * DTD rules in external file
	 *
	 * @var string
	 */
	var $dtd;
	
	/**
	 * XSL rules in external file
	 *
	 * @var unknown_type
	 */
	private $xsl;
	
	/**
	 * Tot records
	 *
	 * @var int
	 */
	var $tot;
	
	/**
	* Output type: stream | filesystem
	*/
	var $output='stream'; 
	
	/**
	 * FIlename to copy XML (alternative method)
	 *
	 * @var string
	 */
	var $filename=''; 
	
	/**
	 * XML generated source
	 *
	 * @var string
	 */
	private $XML ;
	
	
	
	/**
	 * Contructor
	 *
	 * @param object $dblink
	 * @param array $db1
	 * @return XMLizer
	 */
	function XMLizer($dblink, $db1){
		
		if(is_object($dblink) || is_resource($dblink)){
		
			$this->dblink=$dblink;	
		}
		else{
			die("Impossibile usare la connessione fornita alla classe XMLizer");
		}
		
		if(is_array($db1)){
			
			$this->db1=$db1;
			
		}
		else{
			die("Impossibile usare la variabile db1 fornita alla classe XMLizer");
		}
		
	}
	
	
	
	/**
	 * Add a XSL stylesheet to XML
	 *
	 * @param string $xsl
	 */
	function addXSL($xsl){
		
		$this->xsl=$xsl;
		
	}
	
	/**
	 * Add a DTD reference to XML
	 *
	 * @param string $dtd
	 */
	function addDTD($dtd){
		
		$this->dtd=$dtd;
		
	}
	
	
	function _prendi_diritti_report($rif_tab_nome,$query_based){
		
		
			if($query_based){
			
				// cerca i diritti come report tabella
				$q=$vmreg->query("SELECT * FROM {$this->db1['frontend']}.xml_rules WHERE nome_report='$rif_tab_nome' ORDER BY lastData DESC LIMIT 1",$this->dblink);
			}
			else{
				// cerca i diritti come report tabella
				$q=$vmreg->query("SELECT * FROM {$this->db1['frontend']}.xml_rules WHERE tabella='$rif_tab_nome' ORDER BY lastData DESC LIMIT 1",$this->dblink);
				
			}
			
			
			// Gestione della presenza 
			
			if($vmreg->num_rows($q)==0){
				
				return false;
			}
			else $RS_rules=$vmreg->fetch_assoc($q);
			
			
			
			// Gestione dei diritti
			
			if($RS_rules['accesso']=='PUBLIC'){
				
				// non fa niente e continua con lo script
			}
			elseif($RS_rules['accesso']=='FRONTEND'){
				if(!isset($_SESSION['user']['livello'])) return false;
			}
			elseif($RS_rules['accesso']=='GROUP'){
				
				$gruppi=explode(",",$RS_rules['accesso_gruppo']);
				
				if(is_array($gruppi) && in_array($_SESSION['gid'],$gruppi)){
					
					// va avanti
				}
				else{
					return false;		
				}
			}
			else{ 
				// RESTRICT o altro...
				
				return false;
			}
			
			return true;
	}
	
	
	
	
	
	
	
	
	
	
	
	/**
	 * Create a XML file from table
	 *
	 * @param string $tabella
	 * @param bool $include_submask
	 * @param mixed $tipo
	 * @param int $id_record
	 * @param int $gid
	 * @param bool $RAW
	 */
	function table2XML($tabella,$include_submask=false,$tipo='all',$id_record=null,$gid=null,$RAW=false){
		
		$test_diritti = $this->_prendi_diritti_report($tabella,false);
		
		if(!$test_diritti) die("Non si possiedono i diritti per leggere questo report");
		
		if($gid==null) $gid=intval($_SESSION['gid']);
		
		$PK = RegTools::prendi_PK($tabella,$gid);
		
		
		$orderby = RegTools::prendi_orderby($tabella,$gid);
		
		if(trim($orderby)=='ASC') $orderby = 1;
		
		//aggiungi l'ID all'order by 
		$orderby_doppio=$orderby.", $PK ";
		
		$campo_orderby = str_replace(array(" ASC"," DESC"),"",$orderby);
		$operatore_orderby = preg_match('| DESC|i',$orderby) ? " > " : " < ";
		
	
	
		// CASO RISULTATO DI RICERCA-------------------------------------------------------
		// SE c'� l'id in GET prendi calcola a che punto dell'elenco si � arrivati
		if(isset($_GET['id']) && intval($_GET['id'])>0){
	
			// Prendi il moe del campo orderby ed il vaolre relativo all'id
	
			$sql_calcola_sub = "SELECT s.$campo_orderby 
									FROM $tabella s WHERE s.".$PK."='".$_GET['id']."'";
	
			$sql_calcola = "SELECT count(*) FROM ".$_GET['action']." t
									WHERE t.$campo_orderby $operatore_orderby ($sql_calcola_sub) OR t.$campo_orderby IS NULL";
	
			$query_calcola = $vmsql->query($sql_calcola,$this->dblink);
	
			list($offset)=$vmsql->fetch_row($query_calcola);
	
		}
		else{
			$offset= (int) $_GET['c'];
		}
	
	
		list($tot_records) = $vmsql->fetch_row($vmsql->query("SELECT count(*) FROM $tabella",$this->dblink));
	
		
		
		
		
		if($include_submask){
			
			// SINGOLO RECORD BASATO SULLA SERIE ORDINATA
			if(is_numeric($tipo)){
				
				$this->xmlize_table_sub($tabella,$gid,null,$offset,1);
			}
			// INTERVALLO DI RECORD BASATO SULLA SERIE ORDINATA
			elseif(preg_match("'([0-9]+),([0-9]+)'",$tipo,$match)>0){
				
				$offset=intval($match[1]);
				$limit=intval($match[2]);
				
				$this->xmlize_table_sub($tabella,$gid,null,$offset,$limit);
			}
			// TUTTI I RECORD 
			else{
				
				$this->xmlize_table_sub($tabella,$gid);
				
			}
			
			
			
			
		}
		else{
			
			
			// OPZIONE PER AVERE I CAMPI ROW
			// i campi row sono quelli non elaborati, in caso contrario mostra i campi richiesti in tabella con le subquery
			if($RAW){
				
				$campi_tabella="*";
			}
			else{
				
				$campi_tabella = RegTools::campi_elaborati($tabella,true);
				$tabella = $tabella . " t ";
			}
		
			// SINGOLO RECORD BASATO SULLA SERIE ORDINATA
			if(is_numeric($tipo)){
				
				$sql = "SELECT ".$campi_tabella." FROM $tabella  ORDER BY $orderby_doppio ".$vmsql->limit(1,$offset);
			}
			
			// INTERVALLO DI RECORD BASATO SULLA SERIE ORDINATA
			elseif(preg_match("'([0-9]+),([0-9]+)'",$tipo,$match)>0){
				
				$offset=intval($match[1]);
				$limit=intval($match[2]);
				
				$sql="SELECT ".$campi_tabella." FROM $tabella ORDER BY $orderby_doppio ".$vmsql->limit($limit,$offset);
				
			}
			
			// RECORD PER SINGOLO ID (DA CAMPO PK)		
			elseif(isset($id_record) && $id_record>0){
				
				$sql="SELECT ".$campi_tabella." FROM $tabella WHERE $PK=".intval($_GET['id'])." ORDER BY $orderby_doppio ";
			}
			
			// TUTTI I RECORD 
			else{
				
				$sql="SELECT ".$campi_tabella." FROM $tabella ORDER BY $orderby_doppio ";
				
			}
			
			
			
			// restituisco l'XML
	
			$this->xmlize($sql, $offset);
			
			
		}
		
		
		$this->stream();
			
	}
	
	
	
	
	
	
	
	
	
	
	
	
	function query2XML(){
		
		$test_diritti = $this->_prendi_diritti_report($tabella,true);
		
		if(!$test_diritti) die("Non si possiedono i diritti per leggere questo report");
	}
	
	
	
	
	/**
	 * Funzione che genera l'XML da una data query. 
	 * La funzione � utilizzata in molte occasioni da VFront.
	 *
	 * @param string $sql SQL SELECT che genera l'XML
	 * @param int $offset OFFSET della query SQL
	 * @return string XML risultato della query
	 */
	function xmlize($sql,$offset=0){
		
		
		$q = $vmsql->query($sql,$this->dblink);
		
		$tot=$vmsql->num_rows($q);
		
		if($tot==0){
			
			return null;	
		}
		
		// Inizia a fare l'xml
		
		
		
		$XML="";
		
		$XML.= ($this->header) ? "<?xml version='1.0' encoding='utf-8'?>\n" : "";
		
		$XML.= ($this->dtd) ? "<!DOCTYPE vfront SYSTEM \"".$this->dtd."\">\n" : "";
		
		$XML.= ($this->xsl!='') ? "<?xml-stylesheet type=\"text/xsl\" href=\"".$this->xsl."\" ?>\n" : "";
		
		$XML.="<recordset tot=\"$tot\">\n";
		
		if($offset===false){
			$auto_offset=true;
			$offset =1;
		}else{
			$auto_offset=false;
		}
		
		while($RS=$vmsql->fetch_assoc($q)){
			
			$XML.="\t".$this->xmlize_campo('row',array("offset"=>$offset))."\n";
			
			foreach($RS as $k=>$val){
				
				$val = Common::vf_utf8_encode(trim($val));
				
				if($val!="" && !is_numeric($val)){
					$val="<![CDATA[".$val."]]>";
				}
				
				$XML.="\t\t".$this->xmlize_campo($k,array());
				$XML.=$val;
				$XML.="</$k>\n";
			}
			
			$XML.="\t</row>\n";
			
			if($auto_offset){
				$offset++;
			}
			
		}
		
		$XML.="</recordset>";
		
		
		
		
		if($this->output=='stream') {
			
			$this->XML=$XML;
		}
		
		else{
			
			$fp =fopen($this->filename,"w");
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
	function xmlize_table_sub($tabella,$gid=null,$filename=null,$offset=0,$tot=0){
		
		if($gid==null){
			$gid= (int) $_SESSION['gid'];
		}
		
		$oid=RegTools::name2oid($tabella,$gid);
		
		$cols=RegTools::prendi_colonne_frontend($oid,"column_name,table_name, orderby, orderby_sort",true);
		
		$ORDER_BY = ($cols[2][0]!='') ? "ORDER BY ".$cols[2][0]." ".$cols[3][0] : "";
	
		$OFF= ($offset==0) ? "" : "$offset";
		
		$LIMIT = ($tot==0) ? "" : $vmsql->limit($tot,$OFF);
		
		$sql="SELECT ".implode(",",$cols[0])." FROM ".$cols[1][0]." $ORDER_BY  $LIMIT";
		
		
		
		$q = $vmsql->query($sql,$this->dblink);
		
		if($vmsql->num_rows($q)==0){
			
			return null;	
		}
		
		// Inizia a fare l'xml
		
		
		
		$XML="";
		
		
		
		$XML.= ($this->header) ? "<?xml version='1.0' encoding='utf-8'?>\n" : "";
		
		$XML.= ($this->dtd!='') ? "<!DOCTYPE vfront SYSTEM \"".$this->dtd."\">\n" : "";
		
		$XML.= ($this->xsl!='') ? "<?xml-stylesheet type=\"text/xsl\" href=\"".$this->xsl."\" ?>\n" : "";
		
		$XML.="<recordset tot=\"$tot\">\n";
		
		if($offset==0){
			$auto_offset=true;
			$offset =1;
		}else{
			$auto_offset=false;
		}
		
		$fkk = RegTools::prendi_K_relazione_sub($oid);
		
		while($RS=$vmsql->fetch_assoc($q)){
			
			$XML.="\t".$this->xmlize_campo('row',array("offset"=>$offset))."\n";
			
			foreach($RS as $k=>$val){
				
				$val = Common::vf_utf8_encode(trim($val));
				
				if($val!="" && !is_numeric($val)){
					$val="<![CDATA[".$val."]]>";
				}
				
				$XML.="\t\t".$this->xmlize_campo($k,array());
				$XML.=trim($val);
				$XML.="</$k>\n";
				
				if($fkk[0][0]==$k){
			
	//				echo "ORA!";
					$XML.=$this->xmlize_sottomaschera($val,$oid);
	
				}
			}
			
			
			
			$XML.="\t</row>\n";
			
			if($auto_offset){
				$offset++;
			}
			
		}
		
		$XML.="</recordset>\n";
		
		
		
		
		if($this->output=='stream') $this->XML = $XML;
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
	
		$sub= RegTools::prendi_sottomaschere($oid);
		
		for($i=0;$i<count($sub);$i++){
			
			$order_sub = ($sub[$i]["orderby_sub"]!='') ?  "ORDER BY ".$sub[$i]["orderby_sub"] : "";
			
			$sql_sub="SELECT * FROM ".$sub[$i]['nome_tabella']." WHERE ".$sub[$i]['campo_fk_sub']."='$id_parent' $order_sub limit 10";
			
			$q_sub=$vmsql->query($sql_sub,$this->dblink);
			
			if($vmsql->num_rows($q_sub)>0){
			
				$XMLSUB.="\t\t<subrecordset tabella=\"".$sub[$i]['nome_tabella']."\" nomefrontend=\"".$sub[$i]['nome_frontend']."\">\n";
			
				$offset=1;
				
				while($RS=$vmsql->fetch_assoc($q_sub)){
			
					$XMLSUB.="\t\t\t".$this->xmlize_campo('subrow',array("offset"=>$offset))."\n";
					
					foreach($RS as $k=>$val){
						
						$val = Common::vf_utf8_encode(trim($val));
						
						if($val!="" && !is_numeric($val)){
							$val="<![CDATA[".$val."]]>";
						}
						
						$XMLSUB.="\t\t\t\t".$this->xmlize_campo($k,array());
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
	
	
	/**
	 * Stream output XML
	 *
	 */
	function stream(){
		
		header("Content-Type: text/xml; charset=".FRONT_ENCODING);
		echo $this->XML;
	}
	
	
	
}






?>