<?php

/**
 * Classe per la gestione delle tendine mediante hash md5. 
 * Come output viene mandato JSON tramite JSONP gestire le tendine che contengo i valori di un'altra tabella,
 * nelle relazioni 1 a molti 
 * 
 * @package VFront
 * @subpackage Function-Libraries
 * @since 0.99
 * @see class.hash.iframe.php
 *
 */
class Select_Values {

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
	public	$hash_js;
	
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
     *
     * @var type string
     */
    public $JSON_OUTPUT;
	
	/**
	 * Numero di record presenti attualmente nella tabella considerata
	 *
	 * @var int
	 */
	public $n=0;
    
    public function __construct(){
        
        require_once(FRONT_ROOT."/plugins/php-sql-parser/src/PHPSQLParser.php");
    }
    
    
    public function set_data($sql_tendina=null, $is_submask=false, $n=0){

		global $vmsql;
		
		$this->is_submask=$is_submask;
		$this->n=$n;
        
        // Pre exec query?
        if(!$vmsql->query_try($sql_tendina,true)){
            return false;
        }
        
        $Parser = new PHPSQLParser();
        $pp = $Parser->parse($sql_tendina);
        
        $campi = array();
        
        if(count($pp['SELECT']) == 1){
            $campi[] = $pp['SELECT'][0]['base_expr'];
        }
        else if(count($pp['SELECT']) > 1){
            foreach($pp['SELECT'] as $ff){
                $campi[]=$ff['base_expr'];
            }
        }
		
        // Order By
		if(is_array($campi)){
			if(isset($pp['ORDER'][0])){
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
		
		$_tablename = (isset($pp['FROM'][0])) ? $pp['FROM'][0]['table'] : null;

		$info_tab=RegTools::prendi_info_tabella($_tablename, "in_insert , visibile, id_table");

		$this->in_insert_tab=(isset($info_tab['in_insert'])) ? $info_tab['in_insert'] : '';
		$this->in_visibile=(isset($info_tab['visibile'])) ? $info_tab['visibile'] : '';
		$this->id_table_ref=(isset($info_tab['id_table'])) ?$info_tab['id_table'] : '';

		$q_selectfrom = $vmsql->query($sql);

		while ($RS=$vmsql->fetch_row($q_selectfrom)) {
			if(count($campi)==1){
				$valori[]=array($RS[0],$RS[0]);
			}
			elseif(count($campi)==2){
				$valori[]=array($RS[0],$RS[1]);
			}
		}

		$this->JSON_OUTPUT = json_encode($valori);

		// Imposto il nome del file html per l'iframe
		$this->hash_js = md5($this->JSON_OUTPUT);
		$this->path_html = FRONT_REALPATH."/files/html/{$this->hash_js}.json";

		// Se non esiste l'HTML per l'IFRAME lo scrivo in un file
		if(!is_file($this->path_html)){

			if($fp = fopen($this->path_html,"w")){
				fwrite($fp,$this->JSON_OUTPUT);
				fclose($fp);
			}
			else{
				openErrorGenerico(_("Filesystem write error for JSON file"),true);
			}
		}
	}
}
