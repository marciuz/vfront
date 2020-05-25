<?php
/**
* LIBRERIA SQL per PostgreSQL con gestione errori ed altre utility
* 
* @package VFront
* @subpackage DB-Libraries
* @author Mario Marcello Verona <marcelloverona@gmail.com>
* @copyright 2007-2010 M.Marcello Verona
* @version 0.96 $Id: vmsql.postgres.php 1165 2016-04-25 10:18:17Z marciuz $
* @see vmsql.mysqli.php
* @license http://www.gnu.org/licenses/gpl.html GNU Public License
*/


class postgres_vmsql {

	public $vmsqltype='postgres';

	public $link_db;

	protected $transaction_is_open=false;

	protected $connected=false;

	protected $error_handler=null;

	protected $last_error=null;

    protected $query_type_asynch = false;

	/**
	 * @desc DB Connection
	 * @param array $array_db
	 * @param string $charset
	 * @return resource
	*/
	public function connect($array_db,$charset=''){

        $connection_string = "host={$array_db['host']} port={$array_db['port']} dbname={$array_db['postgres_dbname']} user={$array_db['user']} password={$array_db['passw']}";
		$this->link_db = @pg_connect($connection_string);

		$charset = ($charset=='utf8') ? "UNICODE" : $charset;

		if(!is_resource($this->link_db)){
			die("Connection error: is Postgres running? Otherwise please check your conf file");
		}

		if(is_resource($this->link_db)){

			$this->connected=true;
			pg_set_client_encoding($this->link_db, $charset);

            // Set the search path
            pg_query($this->link_db, "SET search_path TO ".$array_db['dbname']);
		}

		return $this->link_db;

	}


    public function set_query_asynch($bool){
        $this->query_type_asynch = (bool) $bool;
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

		$resource=null;

		$err=null;

		if(is_resource($this->link_db) && $this->connected){

            if($this->query_type_asynch){

                pg_send_query($this->link_db, $sql);

                while ( $resource[] = pg_get_result($this->link_db) ){

                    $err=pg_result_error_field($resource,PGSQL_DIAG_SQLSTATE);

                    if($err!==null){
                        $this->error($sql, array('code'=>$err,'message'=>strtok(pg_result_error($resource),"\n")));
                    }

                    pg_free_result( $resource );
                }
            }
            else{
                $resource = pg_query($this->link_db, $sql);
                if(!$resource){
                    $err = true;
                    $this->error($sql, array('code'=> 1, 'message'=> pg_last_error($this->link_db)));
                }
            }

            if(isset($GLOBALS['DEBUG_SQL']) && $GLOBALS['DEBUG_SQL']){
                $GLOBALS['DEBUG_SQL_STRING'][] = round((microtime(true) - $getmicro),4) . " --- ". $sql;
            }

		}
		else {
            $this->error($sql,array('message'=>_("Link al DB non disponibile")));
        }

		if(is_resource($resource) && $err===null) {
			if($transazione) $this->commit();
			return (is_array($resource) && count($resource) == 1 ) ? $resource[0] : $resource;
		}
		else{
			if($transazione) $this->rollback();
			return false;
		}
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
		if(is_resource($this->link_db)){

			$test = @pg_query($this->link_db, $sql);
			$err = pg_last_error($this->link_db);

		}

		return (is_resource($test) && empty($err)) ? 1:0;
	}


	/**
	 * @return array
	 * @param resource $res
	 * @desc Funzione di fetch_row
	*/
	public function fetch_row(&$res){

		if(is_resource($res)){

			$RS= @pg_fetch_row($res);
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

			$RS= @pg_fetch_assoc($res);
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

			$RS= @pg_fetch_array($res);
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

		if(is_resource($res)){
			$obj= ($class_name==null) ? @pg_fetch_object($res) :  @pg_fetch_object($res, null, $class_name);
			return (is_object($obj)) ? $obj : false;
		}
	}


	/**
	 * @desc Funzione di num_rows
	 * @return array
	 * @param resource $res
	*/
	public function num_rows(&$res){

		if(is_resource($res)){
			return @pg_num_rows($res);

		}
	}


	/**
	 * @return int
	 * @param resource $res
	 * @desc Funzione di insert ID che restituisce l'ultimo ID autoincrement inserito (Postgres)
	*/
	public function insert_id($tablename,$fieldname){

		$result= @pg_query($this->link_db, "SELECT last_value FROM {$tablename}_{$fieldname}_seq");
        if ($result) {
            $arr = @pg_fetch_row($result,0);
            pg_freeresult($result);
            if (isset($arr[0])) return $arr[0];
        }
		return false;
	}


	/**
	 * @return int
	 * @desc Funzione affected rows
	*/
	public function affected_rows($query){


		if(is_resource($query)){
			return @pg_affected_rows($query);

		}

	}


	/**
	 * @desc Funzione di num_fields
	 * @return int
	 * @param string $dbname
	*/
	public function num_fields($dbname){

		return @pg_num_fields($dbname);

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

		if(is_resource($res)){

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
		if(is_resource($res)){
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

		if(is_resource($res)){

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
	public function error($sql, $message){

        var_dump($message);


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
				  'code'=> $message['code'],
				  'msg'=>$message['message'],
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
			$q=$this->query("BEGIN");
		}
		$this->transaction_is_open=true;
	}


	/**
	 * @desc Funzione di transazione di ROLLBACK
	 */
	public function rollback(){

		if($this->transaction_is_open){
			$q=$this->query("ROLLBACK");
		}
		$this->transaction_is_open=false;
	}


	/**
	 * @desc Funzione di transazione di COMMIT
	 */
	public function commit(){

		if($this->transaction_is_open){
			$q=$this->query("COMMIT");
		}
		$this->transaction_is_open=false;
	}


	/**
	 * Utilities
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

		return pg_escape_string($this->link_db,stripslashes($string));
	}


	/**
	 * Unescape function
	 *
	 * @param string $string
	 * @return string
	 */
	public function unescape($string){

		return str_replace(pg_escape_string($this->link_db,"'"),"'",$string);
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

			$escaped = new stdClass();
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

		$q=$this->query("SELECT VERSION()");
		list($db_version)=$this->fetch_row($q);

		return $db_version;
	}


	public function close(){

		if($this->error_handler!==null) $this->db_error_log($this->error_handler);

		if(is_resource($this->link_db)){
			pg_close($this->link_db);
		}
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
