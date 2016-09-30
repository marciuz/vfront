<?php
/**
 * Classe per la gestione degli iframe mediante hash md5. 
 * Gli iframe sono utilizzati per gestire le tendine che contengo i valori di un'altra tabella,
 * nelle relazioni 1 a molti 
 * @package VFront
 * @subpackage Function-Libraries
 * @see class.select_values.php
 * @deprecated since version 0.99 
 */
class hash_iframe{

	
	/**
	 * HTML generato come output dalla classe
	 *
	 * @var string HTML
	 */
	public	$HTML_IFRAME="";
	
	/**
	 * Hash md5 che riassume il contenuto di HTML_IFRAME, identificandolo univocamente
	 *
	 * @var string md5
	 */
	public	$hash_html;
	
	/**
	 * Path della cartella dove salvare e leggere i file per gli iframe
	 *
	 * @var string Path per gli iframe
	 */
	public	$path_html;
	
	/**
	 * Variabile presa dal DB che identifica se la tabella padre è scrivibile (INSERT)
	 *
	 * @var bool
	 */
	public $in_insert_tab;
	
	/**
	 * Variabile presa dal DB che identifica se la tabella padre è visibile (SELECT)
	 *
	 * @var bool
	 */
	public $in_visibile;
	
	/**
	 * ID (oid) della tabella nella tabella di regole 
	 *
	 * @var int
	 */
	public $id_table_ref;
	
	/**
	 * Variabile che identifica se la tabella considerata è persa da VFront come sottomaschera o maschera
	 *
	 * @var bool
	 */
	public $is_submask;
	
	/**
	 * Numero di record presenti attualmente nella tabella considerata
	 *
	 * @var int
	 */
	public $n=0;
	
	
	/**
	 * Funzione che scive sul filesystem il nuovo iframe
	 * nominandolo con un hash md5 corrispondente al suo contenuto con il suffisso .html
	 * Gli iframe sono contenuti nella directory /html
	 *
	 * @param string $nome_campo Nome del campo per cui generare la tendina
	 * @param string $sql_tendina Codice SQL che recupera i dati sotto forma di chiave, valore
	 * @param bool $is_submask Identifica se si tratta di una sottomaschera
	 * @param int $n Numero di record attualemnte presenti nella tabella
	 * @return void
	 */
	function  __construct($nome_campo,$sql_tendina,$is_submask=false,$n=0){

		global $db1, $vmsql, $vmreg;
		
		$this->is_submask=$is_submask;
		$this->n=$n;
		
		

		// CASO SPECIALE SELECT FROM


		$campi = $this->analisi_select_from($sql_tendina);

		
		if(is_array($campi)){
			if(preg_match('!ORDER BY!i',$sql_tendina)){
				$sql = $sql_tendina;
			}
			else if(count($campi)==1){
				$sql = $sql_tendina. " ORDER BY 1";
			}
			else{
				$sql = $sql_tendina. " ORDER BY 2";
			}

		}
		else{
			$sql ="";
		}

		
		$test_tabella = preg_match("'FROM ([a-zA-Z0-9_]+)'i",$sql_tendina,$arr_tabella);

		if(is_array($arr_tabella) && count($arr_tabella)==2){
			$tabella=$arr_tabella[1];
		}
		else $tabella = null;

		$info_tab=RegTools::prendi_info_tabella($tabella, "in_insert , visibile, id_table");

		$this->in_insert_tab=(isset($info_tab['in_insert'])) ? $info_tab['in_insert'] : '';
		$this->in_visibile=(isset($info_tab['visibile'])) ? $info_tab['visibile'] : '';
		$this->id_table_ref=(isset($info_tab['id_table'])) ?$info_tab['id_table'] : '';


		$q_selectfrom = $vmsql->query($sql);


		while ($RS=$vmsql->fetch_row($q_selectfrom)) {
			if(count($campi)==1){
				$valori[$RS[0]]=$RS[0];
			}
			elseif(count($campi)==2){
				$valori[$RS[0]]=htmlentities($RS[1], ENT_QUOTES, FRONT_ENCODING);
			}
		}



		$this->HTML_IFRAME = $this->hash_iframe_cont($valori,$nome_campo);








		// Imposto il nome del file html per l'iframe
		$this->hash_html = md5($this->HTML_IFRAME);
		$this->path_html = FRONT_REALPATH."/files/html/{$this->hash_html}.html";

		// Se non esiste l'HTML per l'IFRAME lo scrivo in un file
		if(!is_file($this->path_html)){

			if($fp = fopen($this->path_html,"w")){
				fwrite($fp,$this->HTML_IFRAME);
				fclose($fp);
			}
			else{
				openErrorGenerico(_("Filesystem write error for Iframe"),true);
			}

		}
	}

	/**
	 * Funzione che genera il contenuto HTML dell'iframe 
	 * e lo restituisce come html valido
	 *
	 * @param array $valori I valori da inserire nella tendina
	 * @param string $campo Nome del campo
	 * @todo Trovare un modo di non far andare in errore il parser W3C con le opzioni vuote
	 * @return string HTML
	 */
	function hash_iframe_cont($valori,$campo){

		// Se � sottomaschera 
		if($this->is_submask){
			
			$id_campo = "dati__{$this->n}__{$campo}";
			$nome_campo = "dati[$this->n][$campo]";
			
		}
		else{
			
			$id_campo = "dati_".$campo;
			$nome_campo = "dati[".$campo."]";
		}
		
		$HTML_IFRAME="
				<!DOCTYPE html PUBLIC \"-//W3C//DTD XHTML 1.0 Transitional//EN\" \"http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd\">
				<html>
					<head>
						<title>vfront_iframe</title>
						<meta http-equiv=\"Content-Type\" content=\"text/html; charset=".FRONT_ENCODING."\" />
					</head>
				<body>
					<div id=\"i_target\">
					<select onchange=\"mod(this.id);\" class=\"off\" name=\"$nome_campo\" ".
					"id=\"$id_campo\" disabled=\"disabled\" >";

					// Aggiungo una riga vuota
					$HTML_IFRAME .= "<option value=\"\">&nbsp;</option>";

					if(count($valori)>0){
						foreach($valori as $k=>$val){
							$val_parsed = str_replace(array("\n","\r")," ",addslashes($val));
							
							$HTML_IFRAME .= "<option value=\"$k\">".Common::vf_utf8_encode($val_parsed)."</option>";
						}
					}





					$HTML_IFRAME.="</select></div>
			
		<script type=\"text/javascript\">
		
			function carica(){
				//T1 = new Date().getTime();
				
			";
						
			if(!$this->is_submask){
				
					$HTML_IFRAME.="
				top.document.getElementById('target_".$campo."').innerHTML=document.getElementById('i_target').innerHTML;
				top.document.getElementById('feed_".$campo."').innerHTML='';
				
				";
			}	
			else{	
			
					$HTML_IFRAME.="
				top.document.getElementById('target_{$this->n}_".$campo."').innerHTML=document.getElementById('i_target').innerHTML;
				
				";	
			}
			
				if($this->is_submask){	
					$HTML_IFRAME.="	
					
				top.trigger_assegnazione();
				
				";
				}
				else{$HTML_IFRAME.="	
				
				top.triggerLoadTendina();
				";
					
				}
			
				
				
				$HTML_IFRAME.="
				
				//T2 = new Date().getTime(); alert((T2-T1)/1000);
			}
			
			setTimeout(\"carica()\",120);
			
			";
					
			
				
				
			$HTML_IFRAME.="
		</script>
		
		</body></html>";

					return $HTML_IFRAME;

	}
    
    

    /**
     * Funzione interna per la determinazione dei campi coinvolti da una data query SQL
     *
     * @param string $sql
     * @return string
     */
    function analisi_select_from($sql){

        global  $vmsql, $vmreg;

        if(!$vmsql->query_try($sql,true)){
            return false;
        }

        // parsing della query
        $campi = preg_replace("|SELECT|i",'',$sql);

        list($campi,$monnezza) = preg_split("'[\W]FROM[\W]'i",$campi);

        // quanti campi ci sono?
        if(preg_match('|,|',$campi)){

            $ar_campi=explode(",",$campi);

            return array(trim($ar_campi[0]),trim($ar_campi[1]));
        }
        else{
            return array(trim($campi));
        }

    }



}