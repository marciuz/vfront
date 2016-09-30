<?php
/**
* LIBRERIA SQL per SQLite2 con gestione errori ed altre utility
* 
* @package VFront
* @subpackage DB-Libraries
* @author Mario Marcello Verona <marcelloverona@gmail.com>
* @copyright 2007-2010 M.Marcello Verona
* @version 0.96 $Id: vmsql.sqlite2.php 1086 2014-06-16 12:42:09Z marciuz $
* @see vmsql.mysqli.php
* @license http://www.gnu.org/licenses/gpl.html GNU Public License
*/


class sqlite2_vmsql {

	public $vmsqltype='sqlite2';

	public $link_db;

	protected $transaction_is_open=false;

	protected $connected=false;

	/*
	 * Busy timeout
	 */
	protected $timeout=1000;

	protected $error_handler;

	protected $last_error=null;

	
	/**
	 * @desc DB Connection
	 * @param string $filepath Path of sqlite DB
	 * @param string $charset default UTF-8
	 * @return object
	*/
	public function connect($filepath,$charset='',$flags='0666',$encryption_key=NULL){

		if(is_array($filepath) && isset($filepath['filename'])){
			$filepath=$filepath['filename'];
		}

		// test if exists and is writable
		if(!file_exists($filepath)){

			die("Connection error: file does not exists, please check your conf file or your sqlite db");
		}
		else if(!is_writable ($filepath)){

			die("Connection error: file is not writeable, please check your conf file or your sqlite db");
		}

		$this->link_db = new SQLiteDatabase($filepath, $flags);

		if(!is_object($this->link_db)){
			die("Connection error: please check your conf file or your sqlite db");
		}

		if(is_object($this->link_db) && $charset!=''){
			$this->link_db->queryExec("PRAGMA encoding='{$charset}'");
			$this->link_db->queryExec("PRAGMA short_column_names=1");
			$this->link_db->busyTimeout($this->timeout);

			$this->connected=true;
		}

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
	 * @param resource $this->link_db
	 * @param bool $transazione
	 * @return object
	 */
	public function query($sql,$transazione=false){

		$getmicro=microtime(true);

		if(is_object($this->link_db)){

			$resource = @$this->link_db->query($sql)
								or $this->error($sql);

			if(isset($GLOBALS['DEBUG_SQL']) && $GLOBALS['DEBUG_SQL']){
				$GLOBALS['DEBUG_SQL_STRING'][] = round((microtime(true) - $getmicro),4) . " --- ". $sql;
			}

		}
		else $this->error($sql,"Link al DB non disponibile",$transazione, $this->link_db);

		if(is_object($resource)) {
			if($transazione) $this->commit($this->link_db);
			return $resource;
		}
		else{
			if($transazione) $this->rollback($this->link_db);
			return false;
		}


	}


	/**
	 * Esegue uno script $sql
	 *
	 * @param string $sql
	 * @param object $this->link_db
	 * @return mixed
	 */
	public function exec($sql){

		$resource=false;

		if(is_object($this->link_db)){

			$getmicro=microtime(true);

			$resource = @$this->link_db->queryExec($sql)
								or $this->error($sql,$this->link_db->lastErrorMsg());

			if($GLOBALS['DEBUG_SQL']){
				$GLOBALS['DEBUG_SQL_STRING'][] = round((microtime(true) - $getmicro),4) . " --- ". $sql;
			}

		}

		return $resource;
	}



	/**
	 * Esegue una query $sql e restisce vero|falso a seconda dell'esito
	 * il secure_mode (di default) permette l'uso di sole query SELECT
	 *
	 * @param string $sql
	 * @param object $this->link_db
	 * @param bool $secure_mode
	 * @return bool
	 */
	public function query_try($sql,$secure_mode=true){ //,$prendi_errorn=false){

		$sql=trim(str_replace(array("\n","\r")," ",$sql));

		if($secure_mode){
			// piccolo accorgimento per la sicurezza...
			if(!preg_match("'^SELECT 'i",$sql)) return 0;
			$sql2=preg_replace("'([\W](UPDATE)|(DELETE)|(INSERT)|(DROP)|(ALTER)|(UNION)|(TRUNCATE)|(SHOW)|(CREATE)[\W])'ui","",$sql);
			if($sql2!=$sql){
				return -1;
			}
		}
		if(is_object($this->link_db)){

			$getmicro=microtime(true);

			$resource = @$this->link_db->queryExec($sql);

			if(isset($GLOBALS['DEBUG_SQL']) && $GLOBALS['DEBUG_SQL']){
				$GLOBALS['DEBUG_SQL_STRING'][] = round((microtime(true) - $getmicro),4) . " --- ". $sql;
			}
		}


		return ($resource===true) ? 1:0;
	}


	/**
	 * @return array
	 * @param resource $res
	 * @desc Funzione di fetch_row
	*/
	public function fetch_row(&$res){

		if(is_object($res)){

			$RS= @$res->fetch(SQLITE_NUM);
			if($RS) return $RS;
			else return false;

		}


	}

	/**
	 * @return array
	 * @param resource $res
	 * @desc Funzione di fetch_assoc
	*/
	public function fetch_assoc(&$res){

		if(is_object($res)){

			$RS= @$res->fetch(SQLITE_ASSOC);
			if($RS) return $RS;
			else return false;

		}
	}


	/**
	 * @return array
	 * @param resource $res
	 * @desc Funzione di fetch_array
	*/
	public function fetch_array(&$res){

		if(is_object($res)){

			$RS= @$res->fetch(SQLITE_BOTH);
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

			if($class_name !== null && class_exists($class_name)){
                $c=new $class_name;
            }
            else{
                $c=new stdClass();
            }

			$RS= @$res->fetch(SQLITE_ASSOC);

			if($RS!==false){
				foreach($RS as $k=>$val){
					$c->{$k}=$val;
				}
			}

			if($RS) return $c;
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

			return $res->numRows();
		}
	}


	/**
	 * @return int
	 * @param resource $res
	 * @desc Funzione di insert ID che restituisce l'ultimo ID autoincrement inserito (Postgres)
	*/
	public function insert_id($tablename='',$fieldname=''){

		return $result= @$this->link_db->lastInsertRowid();
	}



	/**
	 * @return int
	 * @desc Funzione affected rows
	*/
	public function affected_rows($query=''){

		if(is_object($this->link_db)){
			return $this->link_db->changes();

		}

	}




	/**
	 * @desc Funzione di num_fields
	 * @return int
	 * @param string $dbname
	*/
	public function num_fields($res){

		if(is_object($res)){
		return @$res->numFields();
		}
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

		$matrice=array();

		if(is_object($res)){

			while($RS= $this->fetch_assoc($res)) $matrice[]=$RS;

			if($reverse)
				return $this->reverse_matrix($matrice);

			else
				return $matrice;

		}
	}


    /**
	 * @return array (matrice)
	 * @param resource $res
	 * @desc Funzione utility di fetch_object che restituisce tutta la matrice dei risultati
	*/
	public function fetch_object_all(&$res, $class_name=null){
        
        $matrice=array();
		if(is_object($res)){
			while($o= $this->fetch_object($res, $class_name)) $matrice[]=$o;
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
	public function fields($tabella){

		$res = $this->query("SELECT * FROM $tabella LIMIT 1");
		$i = @pg_num_fields($res);
		for ($j = 0; $j < $i; $j++) {
		   $fieldname = @pg_field_name($res, $j);
		   $tab_fields[$fieldname]=@pg_field_type($res, $j);
		}

		return $tab_fields;
	}


	/**
	 * Recupera informazioni dal file e dalla query ed apre la funzione
	 * openError del file design/layouts.php dove cancella il buffer e manda a video l'errore codificato
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
			$this->error_handler->settedTimeout=$this->timeout;
			$this->error_handler->errors=array();
		}

		$trace=debug_backtrace();
		$last=count($trace)-1;
		$file_line=str_replace(FRONT_ROOT, '', $trace[$last]['file']).":".$trace[$last]['line'];

		$ee=array('date'=>date("c"),
				  'sql'=>$sql,
				  'code'=>$this->link_db->lastError(),
				  'msg'=>sqlite_error_string($this->link_db->lastError()),
				  'file'=>$file_line
			);

		$this->error_handler->errors[]=$ee;

		$this->last_error=$ee;


	   if(isset($GLOBALS['DEBUG_SQL']) && $GLOBALS['DEBUG_SQL']){

			$this->error_debug();
		}
		else{

			if(!function_exists('openError')){

				include_once(FRONT_REALPATH."/inc/layouts.php");
			}
			// richiamo la funzione openError
			openError($this->last_error);
			exit;
		}
	}

	/**
	 * Questa funzione viene eseguita da {@link $this->query} qualora il debug sia attivato
	 * @desc Funzione che restituisce a video l'SQL che ha generato l'errore
	 * @param unknown_type $sql
	 * @param unknown_type $message
	 */
	public function error_debug($format='string'){

		if($format=='string'){

			var_dump($this->last_error);
		}
	}







	// FUNZIONI DI TRANSAZIONE

	/**
	 * @desc Funzione di transazione che corrisponde ad un BEGIN
	 */
	public function begin(){

		if(!$this->transaction_is_open){
			$q=$this->query("BEGIN TRANSACTION");
			$this->transaction_is_open=true;
		}
	}


	/**
	 * @desc Funzione di transazione di ROLLBACK
	 */
	public function rollback(){

		if($this->transaction_is_open){
			$q=$this->query("ROLLBACK");
			$this->transaction_is_open=false;
		}
	}


	/**
	 * @desc Funzione di transazione di COMMIT
	 */
	public function commit(){

		if($this->transaction_is_open){
			$q=$this->query("COMMIT");
			$this->transaction_is_open=false;
		}

	}





	/**
	 * Funzione di utilit�
	 * Testa l'esistenza di un $valore (di solito l'ID) nel $campo di una $tabella,
	 * con eventuali clausole $and
	 *
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
	public function escape($string){

		return sqlite_escape_string(stripslashes($string));

	}



	/**
	 * Escape function
	 *
	 * @param string $string
	 * @return string
	 */
	public function unescape($string){

		return str_replace(sqlite_escape_string("'"),"'",$string);

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

		$str=str_replace(","," || ",$args);

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

		$str= "LIMIT $limit";

		if($offset!='') $str.=" OFFSET $offset";

		return $str;
	}


	public function db_version(){

		return "SQLite ".sqlite_libversion();
	}

	/**
	 * Close the connection
	 *
	 * @return string
	 */
	public function close(){

		if($this->error_handler!==null) $this->db_error_log($this->error_handler);

		//return ($this->connected) ? $this->link_db->close() : null;

		return null;
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


	/**
	 * Closing connection in destructor
	 */
	function  __destruct() {

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