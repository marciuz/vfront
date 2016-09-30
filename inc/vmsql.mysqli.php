<?php
/**
* LIBRERIA SQL per MySQL(i) con gestione errori ed altre utility
* 
* @package VFront
* @subpackage DB-Libraries
* @author Mario Marcello Verona <marcelloverona@gmail.com>
* @copyright 2007-2010 M.Marcello Verona
* @version 0.96 $Id: vmsql.mysqli.php 1086 2014-06-16 12:42:09Z marciuz $
* @see vmsql.postgres.php
* @license http://www.gnu.org/licenses/gpl.html GNU Public License
*/


class mysqli_vmsql {

	public $vmsqltype='mysqli';

	public $link_db=null;

	protected $transaction_is_open=false;

	protected $connected;

	protected $error_handler=null;

	protected $last_error=null;

	public $quiet=false;

	/**
	 * @desc DB Connection
	 * @param array $array_db
	 * @param string $charset
	 * @return object
	*/
	public function connect($array_db,$charset=''){

		 $this->link_db=@mysqli_connect($array_db['host'],
								  $array_db['user'],
								  $array_db['passw'],
								  $array_db['dbname'],
								  $array_db['port']);

		if(!is_object($this->link_db)){
			die("Connection error: is MySQL running? Otherwise please check your conf file");
		}

		 if($charset!='' && $this->link_db!=false){
			mysqli_set_charset($this->link_db, $charset);
		 }

		 $this->connected=true;

		 return $this->link_db;
	}

	public function get_error($last=true){

	    if($last){

		return $this->last_error;
	    }
	    else{
		return $this->error_handler;
	    }
	}



	/**
	 * @desc Esegue una query $sql
	 * @param string $sql
	 * @param bool $transazione
	 * @return object
	 */
	public function query($sql,$transazione=false){

		$getmicro=microtime(true);

		$obj = @mysqli_query($this->link_db,$sql,MYSQLI_STORE_RESULT)
				or $this->error($sql);

		if($GLOBALS['DEBUG_SQL']){
			
			$GLOBALS['DEBUG_SQL_STRING'][] = round((microtime(true) - $getmicro),4) . " --- ". $sql;
		}

		return $obj;

	}




	/**
	 * @desc Esegue diverse query $sql
	 * @param string $sql
	 * @return object
	 */
	public function multi_query($sql){

		$getmicro=microtime(true);

		$obj = @mysqli_multi_query($this->link_db,$sql)
				or $this->error($sql);

		if(isset($GLOBALS['DEBUG_SQL']) && $GLOBALS['DEBUG_SQL']){
			$GLOBALS['DEBUG_SQL_STRING'][] = round((microtime(true) - $getmicro),3) . " --- ". $sql;
		}

		return $obj;

	}


	/**
	 * Esegue una query $sql  e restisce vero|falso a seconda dell'esito
	 * il secure_mode (di default) permette l'uso di sole query SELECT.
	 * Se l'sql contiene errori la funzione restituisce false, ma l'esecuzione prosegue.
	 *
	 * @param string $sql Query SQL da testare
	 * @param bool $secure_mode Imposta il secure mode per le query, invalidando tutte le query con comandi pericolosi
	 * @param bool $prendi_errorn prende il numero di errore come output (al posto di 0)
	 * @return bool Esito della query
	 */
	public function query_try($sql,$secure_mode=true){

		$getmicro=microtime(true);
		
		$sql=trim(str_replace(array("\n","\r")," ",$sql));

		if($secure_mode){
			// piccolo accorgimento per la sicurezza...
			if(!preg_match("'^SELECT 'i",$sql)) return 0;
			$sql2=preg_replace("'([\W](UPDATE)|(DELETE)|(INSERT)|(DROP)|(ALTER)|(UNION)|(TRUNCATE)|(SHOW)|(CREATE)|(INFORMATION_SCHEMA)[\W])'ui","",$sql);
			if($sql2!=$sql){
				return -1;
			}
		}

		if(is_object($this->link_db)){
			$res = @mysqli_query($this->link_db,$sql);
			if($res) @mysqli_free_result($res);

			if(isset($GLOBALS['DEBUG_SQL']) && $GLOBALS['DEBUG_SQL']){
				$GLOBALS['DEBUG_SQL_STRING'][] = round((microtime(true) - $getmicro),3) . " --- ". $sql;
			}
		}
		
		return ($res) ? 1:0;
	}


	/**
	 * @desc Funzione di fetch_row
	 * @return array
	 * @param resource $res
	*/
	public function fetch_row(&$res){

		if(is_object($res)){

			$RS= @mysqli_fetch_row($res);
			if($RS) return $RS;
			else return false;

		}


	}


	/**
	 * Funzione di fetch row in caso di multiple query
	 *
	 * @param resource $res
	 * @return array
	 */
	public function fetch_row_multi(&$res){


		$output=array();

		do {
			/* store first result set */
			if ($result = mysqli_store_result($this->link_db)) {
				while ($row = mysqli_fetch_row($result)) {
				   $output[]=$row;
				}
				mysqli_free_result($result);
			}

		} while (mysqli_more_results($this->link_db) && mysqli_next_result($this->link_db));


		return $output;

	}



	/**
	 * @desc Funzione di fetch_assoc
	 * @return array
	 * @param resource $res
	*/
	public function fetch_assoc(&$res){

		if(is_object($res)){

			$RS= @mysqli_fetch_assoc($res);
			if($RS) return $RS;
			else return false;

		}


	}


	/**
	 * @desc Funzione di fetch_array
	 * @return array
	 * @param resource $res
	*/
	public function fetch_array(&$res){

		if(is_object($res)){

			$RS= @mysqli_fetch_array($res);
			if($RS) return $RS;
			else return false;

		}
	}


	/**
	 * @desc Funzione di fetch_object
	 * @return object
	 * @param resource $res
	*/
	public function fetch_object(&$res,$class_name=null){

		if(is_object($res)){

			$RS= ($class_name==null) ? @mysqli_fetch_object($res) :  @mysqli_fetch_object($res, $class_name);

			if($RS) return $RS;
			else return false;
		}
	}


	/**
	 * @desc Funzione di num_rows
	 * @return array
	 * @param resource $res
	*/
	public function num_rows(&$res){

		if(is_object($res)){
			return @mysqli_num_rows($res);

		}
	}


	/**
	 * @desc  Funzione di insert ID che restituisce l'ultimo ID autoincrement inserito (MySQL)
	 * @param resource $res
	 * @param string $tablename Per compatibilit� con Postgres
	 * @param string $fieldname Per compatibilit� con Postgres
	 * @return int
	*/
	public function insert_id($tablename=null,$fieldname=null){

		if(is_object($this->link_db)){
			return mysqli_insert_id($this->link_db);
		}
		else{
			return null;
		}
	}



	/**
	 * @desc Funzione di affected_rows
	 * @return int
	 * @param resource $query Per compatibilit� con Postgres
	*/
	public function affected_rows($query){


		if(is_object($this->link_db)){
			return @mysqli_affected_rows($this->link_db);

		}

	}




	/**
	 * @desc Funzione di num_fields
	 * @return int
	 * @param string $dbname
	*/
	public function num_fields($dbname){

		return @mysqli_num_fields($dbname);

	}


	/**
	 * @desc Funzione di free_result
	 * @return void
	 * @param string $result
	*/
	public function free_result($result){

		mysqli_free_result($result);
	}






	#########################################################################################
	#
	#
	#	FUNZIONI DI ELABORAZIONE
	#






	/**
	 * @return array (matrice)
	 * @param resource $res
	 * @desc Funzione utility di fetch_assoc che restituisce tutta la matrice dei risultati
	*/
	public function fetch_assoc_all(&$res, $reverse=false){

		if(is_object($res)){

			$matrice=array();

			while($RS= $this->fetch_assoc($res)) $matrice[]=$RS;

			$this->free_result($res);

			if($reverse)
				return $this->reverse_matrix($matrice);

			else
				return $matrice;

		}
	}




	/**
	 * @return array (matrice)
	 * @param resource $res
	 * @desc Funzione utility di fetch_assoc che restituisce tutta la matrice dei risultati
	*/
	public function fetch_object_all(&$res, $class_name=null){
        
        $matrice=array();
		if(is_object($res)){

			while($o= $this->fetch_object($res, $class_name)) $matrice[]=$o;

			$this->free_result($res);
		}
        
        return $matrice;
	}




	/**
	 * @return  matrix
	 * @param matrix $matrix
	 * @desc restituisce una traslata della matrice partendo da indici numerici
	*/
	public function reverse_matrix($matrix){

		if(!is_array($matrix) || count($matrix)==0) return false;

	//	if(!is_array($matrix[0])) return false;

		$keys = array_keys($matrix[0]);

		for($i=0;$i<count($matrix);$i++){

			for($j=0;$j<count($keys);$j++)	$rev[$keys[$j]][$i] = $matrix[$i][$keys[$j]];
		}

		return $rev;
	}


	/**
	 * @return resource
	 * @param resource $res
	 * @desc Funzione utility di fetch_row che restituisce tutta la matrice dei risultati
	*/
	public function fetch_row_all(&$res,$reverse=false){

		$matrice=array();

		if(is_object($res)){

			while($RS= $this->fetch_row($res)) $matrice[]=$RS;

			$this->free_result($res);

			if($reverse)
				return $this->reverse_matrix($matrice);

			else
				return $matrice;

		}
	}


	/**
	 * Funzione che recupera le informazioni sui campi di una tabella data
	 *
	 * @param string $tabella
	 * @param resource $this->link_db
	 * @return array
	 */
	/*public function fields($tabella,$this->link_db){

		$res = $this->query("SELECT * FROM $tabella LIMIT 1",$this->link_db);
		$i = @pg_num_fields($res);
		for ($j = 0; $j < $i; $j++) {
		   $fieldname = @pg_field_name($res, $j);
		   $tab_fields[$fieldname]=@pg_field_type($res, $j);
		}

		return $tab_fields;
	}*/


	/**
	 *  Recupera informazioni dal file e dalla query ed apre la funzione openError del file design/layouts.php dove cancella il buffer e manda a video l'errore codificato
	 *
	 * @return void
	 * @param string $sql
	 * @param string $message
	 * @desc Handler degli errori per le query.
	*/
	public function error($sql, $message=''){


		if(!is_object($this->error_handler)){

			$this->error_handler= new stdClass();

			$this->error_handler->dbtype=$this->vmsqltype;
			$this->error_handler->errors=array();
		}

		$trace=debug_backtrace();
		$last=count($trace)-1;
		$file_line=str_replace(FRONT_ROOT, '', $trace[$last]['file']).":".$trace[$last]['line'];

		$ee=array('date'=>date("c"),
				  'sql'=>$sql,
				  'code'=>mysqli_errno($this->link_db),
				  'msg'=>mysqli_error($this->link_db),
				  'file'=>$file_line
			);

		$this->error_handler->errors[]=$ee;

		$this->last_error=$ee;


		if($GLOBALS['DEBUG_SQL'] && !$this->quiet){

			$this->error_debug();
		}
		else if(!$this->quiet){

			if(!function_exists('openError')){
				include_once(FRONT_REALPATH."/inc/layouts.php");
			}
			openError($this->last_error);
		}
		else{

		    // no action in quiet mode
		}


		

	}


	/**
	 * Questa funzione viene eseguita da {@link $this->query} qualora il debug sia attivato
	 * @desc Funzione che restituisce a video l'SQL che ha generato l'errore
	 * @param string $format default "string"
	 */
	public function error_debug($format="string"){

		if($format=='string'){

			var_dump($this->last_error);
		}
	}







	// FUNZIONI DI TRANSAZIONE


	/**
	 * @desc Funzione di transazione che corrisponde ad un BEGIN
	 * @param resource $this->link_db
	 */
	public function begin(){

		mysqli_autocommit($this->link_db, FALSE);
		$this->transaction_is_open=true;
	}

	/**
	 * @desc Funzione di transazione di ROLLBACK
	 * @param resource $this->link_db
	 */
	public function rollback(){

		if($this->transaction_is_open){
			mysqli_rollback($this->link_db);
			$this->transaction_is_open=false;
		}
	}


	/**
	 * @desc Funzione di transazione di COMMIT
	 * @param resource $this->link_db
	 */
	public function commit(){
		
		if($this->transaction_is_open){
			mysqli_commit($this->link_db);
			$this->transaction_is_open=false;
		}
	}





	/**
	 * Funzione di utilit�
	 * Testa l'esistenza di un $valore (di solito l'ID) nel $campo di una $tabella,
	 * con eventuali clausole $and
	 *
	 * @param resource $this->link_db
	 * @param string $campo
	 * @param mixed $valore_id
	 * @param string $tabella
	 * @param string $and
	 * @return bool
	 */
	public function test_id($campo,$valore_id,$tabella,$and="",$secure_test=false){

		$sql= "SELECT * FROM $tabella WHERE $campo=$valore_id $and";

		if($secure_test){
			if($this->query_try($sql)){
				$q=$this->query($sql);

				return ($this->num_rows($q)>0) ? true:false;
			}
			else return null;
		}
		else{

			$q=$this->query($sql);
			return ($this->num_rows($q)>0) ? true:false;
		}
	}

	/**
	 * Escape function
	 *
	 * @param string $string
	 * @return string
	 */
	public function escape($string=null){

		return mysqli_real_escape_string($this->link_db,stripslashes($string));
	}

	/**
	 * Unescape function
	 *
	 * @param string $string
	 * @return string
	 */
	public function unescape($string=null){

		return str_replace(mysqli_real_escape_string($this->link_db,"'"),"'",$string);
	}




	/**
	 * Recursive escape. Work on strings, numbers, array, objects
	 *
	 * @param mixed $mixed
	 * @return mixed
	 */
	public function recursive_escape($mixed){

		if(is_string($mixed)){

			$escaped= $this->escape($mixed);
		}
		else if(is_numeric($mixed)){

			$escaped= $mixed;
		}
		else if(is_array($mixed)){

			foreach ($mixed as $k=>$val)
				$escaped[$k]=$this->recursive_escape($val);
		}
		else if(is_object ($mixed)){

			foreach ($mixed as $k=>$val)
				$escaped->{$k}=$this->recursive_escape($val);
		}
		else{
		    $escaped=$mixed;
		}

		return $escaped;
	}


	
	/**
	 * Concat DB sintax
	 *
	 * @param string $args
	 * @param string $args
	 * @return string
	 */
	public function concat($args,$as=''){

		$str="CONCAT($args)";

		if($as!='') $str.=" AS $as";

		return $str;
	}



	/**
	 * Set the LIMIT|OFFSET sintax
	 *
	 * @param int $limit
	 * @param int $offset
	 * @return string
	 */
	public function limit($limit,$offset=''){

		if($offset!='') $str="LIMIT $offset,$limit";
		else $str="LIMIT $limit";

		return $str;
	}


	public function db_version(){

		$q=$this->query("SELECT VERSION()");
		list($db_version)=$this->fetch_row($q);

		return $db_version;
	}

	

	public function close(){

		if($this->error_handler!==null) $this->db_error_log($this->error_handler);

		if($this->transaction_is_open){

			if($this->error_handler===null) $this->commit();
			else $this->rollback();
		}

		return ($this->connected) ? mysqli_close($this->link_db) : null;
	}


	/**
	 *	For Oracle and MySQLi compatibility
	 *
	 * @param statement $stmt
	 * @return bool
	 */
	public function stmt_close($stmt){

		return true;
	}

	public function __destruct() {

		$this->close();
	}
    
    

    public function db_error_log($obj){

        $fp=fopen(FRONT_ERROR_LOG,"a");
        $towrite='';

        if(is_array($obj->errors)){

            foreach($obj->errors as $e){

                // prende il tipo query (SELECT , INSERT, UPDATE, DELETE) se il tipo è diverso ahi ahi
                $tipo_query = substr(trim($e['sql']), 0 , strpos(trim($e['sql'])," "));

                // restituisci la query che ha dato errore
                $sql_una_linea = trim(preg_replace("'\s+'"," ",$e['sql']));

                // Scrittura del file di errore
                $towrite.= "[".$e['date']."]\t"
                            . $e['file']."\t"
                            . $_SERVER['HTTP_HOST']. " (".$_SERVER['SERVER_ADDR']. ")\t"
                            . "<".$tipo_query . ">\t"
                            . $obj->dbtype."\t"
                            . $e['code'] . "\t"
                            . $e['msg'] . "\t"
                            . $sql_una_linea. "\n";
            }

            fwrite($fp,$towrite);

        }

        fclose($fp);
    }

}



?>