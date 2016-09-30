<?php
/**
* Alias per vmsql.mysqli.php
* 
* @package VFront
* @subpackage DB-Libraries
* @author Mario Marcello Verona <marcelloverona@gmail.com>
* @copyright 2007-2010 M.Marcello Verona
* @version 0.96 $Id: vmsql.mysqlold.php 1086 2014-06-16 12:42:09Z marciuz $
* @see vmsql.mysqli.php
* @license http://www.gnu.org/licenses/gpl.html GNU Public License
*/



class mysql_vmsql {

	public $vmsqltype='mysql';

	public $link_db;

	protected $connected=false;

	protected $error_handler=null;

	protected $last_error=null;

	

	/**
	 * @desc DB Connection
	 * @param array $array_db
	 * @return resource
	 */
	public function connect($array_db){

		$this->link_db = @mysql_connect($array_db['host'].":".$array_db['port'],
								  $array_db['user'],
								  $array_db['passw']);

		if(!is_resource($this->link_db)){

			die("Connection error: is MySQL running? Otherwise please check your conf file");
		}

		@mysql_select_db($array_db['dbname'],$this->link_db) or die(_("Database does not exist"));

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

		$obj = @mysql_query($sql,$this->link_db) or $this->error($sql);

		if($GLOBALS['DEBUG_SQL']){
			$GLOBALS['DEBUG_SQL_STRING'][] = round((microtime(true) - $getmicro),4) . " --- ". $sql;
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
	 * @return bool Esito della query
	 */
	public function query_try($sql,$secure_mode=true){

		$sql=trim(str_replace(array("\n","\r")," ",$sql));

		if($secure_mode){
			// piccolo accorgimento per la sicurezza...
			if(!preg_match("'^SELECT 'i",$sql)) return 0;
			$sql2=preg_replace("'([\W](UPDATE)|(DELETE)|(INSERT)|(DROP)|(ALTER)|(UNION)|(TRUNCATE)|(SHOW)|(CREATE)|(INFORMATION_SCHEMA)[\W])'ui","",$sql);
			if($sql2!=$sql){
				return -1;
			}
		}

		if(is_resource($this->link_db)){
			$res = @mysql_query($sql,$this->link_db) or $this->error($sql);
			if($res) @mysql_free_result($res);

			if($GLOBALS['DEBUG_SQL']){
				$GLOBALS['DEBUG_SQL_STRING'][] = round((microtime(true) - $getmicro),4) . " --- ". $sql;
			}
		}

		return ($res) ? 1:0;
	}



	/**
	 * @return array
	 * @param resource $res
	 * @desc Funzione di fetch_row
	*/
	public function fetch_row(&$res){

		if(is_resource($res)){

			$RS= @mysql_fetch_row($res);
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

		if(is_resource($res)){

			$RS= @mysql_fetch_assoc($res);
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

		if(is_resource($res)){

			$RS= @mysql_fetch_array($res);
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

			$RS= ($class_name==null) ? @mysql_fetch_object($res) :  @mysql_fetch_object($res, $class_name);

			if($RS) return $RS;
			else return false;
		}
	}



	public function num_rows(&$res){

		if(is_resource($res)){
			return @mysql_num_rows($res);

		}
	}


	/**
	 * @return int
	 * @param resource $res
	 * @desc Funzione di insert ID che restituisce l'ultimo ID autoincrement inserito (MySQL)
	*/
	public function insert_id(){


		if(is_resource($this->link_db)){
			return @mysql_insert_id($this->link_db);

		}

	}



	/**
	 * @return int
	 * @desc Funzione affected rows
	*/
	public function affected_rows(){


		if(is_resource($this->link_db)){
			return @mysql_affected_rows($this->link_db);

		}

	}



	public function list_tables($dbname){

		return @mysql_list_tables($dbname);

	}



	public function num_fields($dbname){

		return @mysql_num_fields($dbname);

	}


	public function fetch_field($dbname){

		return @mysql_fetch_field($dbname);

	}


	/**
	 * @desc Funzione di free_result
	 * @return void
	 * @param string $result
	*/
	public function free_result($result){

		mysql_free_result($result);
	}






	#########################################################################################
	#
	#
	#	FUNZIONI DI ELABORAZIONE
	#


	/**
	 *  Recupera informazioni dal file e dalla query ed apre la funzione openError del file design/layouts.php dove cancella il buffer e manda a video l'errore codificato
	 *
	 * @return void
	 * @param string $sql
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
				  'code'=>mysql_errno($this->link_db),
				  'msg'=>mysql_error($this->link_db),
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


	public function error_debug($format='string'){

		if($format=='string'){

			var_dump($this->last_error);
		}

	}




	/**
	 * @return  matrix
	 * @param matrix $matrix
	 * @desc restituisce una traslata della matrice partendo da indici numerici
	*/
	public function reverse_matrix($matrix){

		if(!is_array($matrix)) return false;

		$keys = array_keys($matrix[0]);

		for($i=0;$i<count($matrix);$i++){

			for($j=0;$j<count($keys);$j++)	$rev[$keys[$j]][$i] = $matrix[$i][$keys[$j]];
		}

		return $rev;
	}




	/**
	 * @return array (matrice)
	 * @param resource $res
	 * @desc Funzione utility di fetch_assoc che restituisce tutta la matrice dei risultati
	*/
	public function fetch_assoc_all(&$res, $reverse=false){

		if(is_resource($res)){

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
	 * @return resource
	 * @param resource $res
	 * @desc Funzione utility di fetch_row che restituisce tutta la matrice dei risultati
	*/
	public function fetch_row_all(&$res,$reverse=false){

		if(is_resource($res)){

			while($RS= $this->fetch_row($res)) $matrice[]=$RS;

			$this->free_result($res);

			if($reverse)
				return $this->reverse_matrix($matrice);

			else
				return $matrice;

		}
	}




	/**
	 * @desc Funzione di transazione che corrisponde ad un BEGIN
	 */
	public function begin(){

		$this->query("BEGIN");
	}

	/**
	 * @desc Funzione di transazione di ROLLBACK
	 */
	public function rollback(){

		$this->query("ROLLBACK");
	}


	/**
	 * @desc Funzione di transazione di COMMIT
	 */
	public function commit(){

		$this->query("COMMIT");
	}


	/**
	 * Funzione di utilità
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
	 * @desc Esegue diverse query $sql (forzata per mysql e sperimentale)
	 * @param string $sql
	 * @return object
	 */
	public function multi_query($sql_multi){

		$sql_array=explode(";",$sql_multi);

		foreach ($sql_array as $sql){

			$obj[] = @mysql_query($sql,$this->link_db) or $this->error($sql,mysql_error($this->link_db),false);

			if($GLOBALS['DEBUG_SQL']){
				$getmicro=microtime(true);
				$GLOBALS['DEBUG_SQL_STRING'][] = round((microtime(true) - $getmicro),3) . " --- ". $sql;
			}
		}

		return $obj;

	}


	/**
	 * Funzione di fetch row in caso di multiple query (forzata per mysql e sperimentale)
	 *
	 * @param resource $res
	 * @return array
	 */
	public function fetch_row_multi(&$res){


		$output=array();
		$res =(!is_array($res)) ? $res=array() : $res;

		foreach ($res as $result){
			/* store first result set */
				while ($row = mysql_fetch_row($result)) {
				   $output[]=$row;
				}

		}

		return $output;
	}

	/**
	 * Escape function
	 *
	 * @param string $string
	 * @return string
	 */
	public function escape($string=null){

		return mysql_real_escape_string(stripslashes($string),$this->link_db);
	}


	/**
	 * Unescape function
	 *
	 * @param string $string
	 * @return string
	 */
	public function unescape($string=null){

		return str_replace(mysql_real_escape_string("'",$this->link_db),"'",$string);
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

		if($this->error_handler!==null)  $this->db_error_log($this->error_handler);

		return ($this->connected) ? mysql_close($this->link_db) : null;
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