<?php

/**
 * Class RPC
 */
if (!defined('FRONT_ROOT')) {

    exit;
}

require_once(FRONT_ROOT . "/inc/func.xmlize.php");

class Rpc {

    protected $table;
    protected $PK;
    protected $orderby;
    protected $tot_records = null;
    protected $where = '';
    protected $_WHERE = array();
    protected $_WHERE_DEFAULT = array();
    public $outputType = '';
    protected $Reg;

    public function __construct($table, $outputType='XML') {

        $this->outputType= (in_array($outputType, array('XML', 'JSON'))) ? $outputType : 'XML';
        $this->table = $table;
        $this->Reg = new Registry();
        $this->Reg->load_registry($this->table, intval($_SESSION['gid']));
        $this->PK = $this->Reg->PK;
        $this->orderby = $this->Reg->prendi_orderby();

        //aggiungi l'ID all'order by
        if ($this->PK != '') {

            foreach($this->PK as $pk){
                $this->orderby.=", ".$pk." ASC";
            }
        }
    }

    public function PK() {
        return $this->PK;
    }

    /**
     *
     * @global object $vmsql
     * @param array $ww ([field1]=>val1, [field2]=>val2, ...)
     * @return void
     */
    public function set_where($ww) {

        global $vmsql;

        if (!is_array($ww))
            return null;

        $F = new FieldType();
        $IS = new iSchema();
        $col_types = $IS->get_column_types($this->table);

        if (stripos($this->where, 'WHERE') === false) {

            $this->where.="WHERE 1=1 ";
        }

        foreach ($ww as $f => $v) {

            if (isset($col_types[$f])) {

                $this->where.= ($F->is_numeric($col_types[$f]) || $F->is_boolean($col_types[$f])) 
                        ? $F->is_numeric($col_types[$f]) ? ' AND ' . $f . "=" . floatval($v) : ' AND ' . $f . "=" . intval($v)
                        : ' AND ' . $f . "='" . $vmsql->escape($v) . "'";
                $this->_WHERE[$f] = $v;
            } else {

                // field not exists!
            }
        }
    }

    /**
     *
     * @global object $vmsql
     * @param array $ww ([field1]=>array('value'=>val1, 'op'=>operator, , [field2]=>val2, ...)
     * @return void
     */
    public function set_default_where() {

        global $vmsql;

        if(!is_array($this->Reg->T->default_filters) || count($this->Reg->T->default_filters) == 0){
            return null;
        }

        $F = new FieldType();
        $IS = new iSchema();
        $col_types = $IS->get_column_types($this->table);

        if (stripos($this->where, 'WHERE') === false) {

            $this->where.="WHERE 1=1 ";
        }

        foreach ($this->Reg->T->default_filters as $f => $v) {

            if (isset($col_types[$f])) {

                $operator = (isset($v->op)) ? Admin_Registry::get_default_filters_ops($v->op) : '=';
                $value = $vmsql->escape($v->value);

                $this->where.= ($F->is_numeric($col_types[$f]) || $F->is_boolean($col_types[$f])) 
                        ? $F->is_numeric($col_types[$f]) ? ' AND ' . $f . " $operator ". floatval($value) : ' AND ' . $f . " $operator "  . intval($value)
                        : ' AND ' . $f . " $operator '" . $value . "'";
                $this->_WHERE_DEFAULT[$f] = $v;
            } else {

                // field not exists!
            }
        }
    }

    public function get_string_where($skip_first_declaration = false) {

        if ($skip_first_declaration)
            return str_replace("WHERE 1=1", '', $this->where);
        else
            return $this->where;
    }

    public function get_where() {
        return $this->_WHERE;
    }

    public function get_where_default() {
        return $this->_WHERE_DEFAULT;
    }

    /**
     * Funzione per la codifica in javascript di caratteri speciali nelle url
     *
     * @param string $str
     * @return string encoded
     */
    private function urldecode_js($str) {

        $str = urldecode($str);

        $find = array("%u201C", "%u201D", "%u2019", "%u2013");

        $replace = array("\"", "\"", "\\'", "-");

        return str_replace($find, $replace, $str);
    }

    /**
     *
     * @global object $vmsql
     * @return int
     */
    public function tot_records() {

        if ($this->tot_records === null) {

            global $vmsql;
            $q = $vmsql->query("SELECT count(*) FROM $this->table $this->where ");
            list($tot_records) = $vmsql->fetch_row($q);
            $this->tot_records = intval($tot_records);
        }

        return $this->tot_records;
    }

    /**
     *
     * @global object $vmsql
     * @global array $db1
     * @param mixed $ID
     * @return int 
     */
    public function get_offset_1($ID) {

        global $vmsql;

        // SE c'è l'id in GET prendi calcola a che punto dell'elenco si è arrivati

        $ID_COUNTER = $vmsql->escape($ID);

        $_COUNTER = (is_numeric($ID_COUNTER)) ? $ID_COUNTER : "'" . $ID_COUNTER . "'";

        // CASE MYSQL
        if (VFRONT_DBTYPE == 'mysql') {

            $sql_mysql = "SET @N=-1;
		    SELECT numero FROM (SELECT @N := @N +1 AS numero, ".$this->PK[0]." FROM $this->table t ORDER BY $this->orderby)
						  as temp WHERE ".$this->PK[0]."=$_COUNTER";

            $qq = $vmsql->multi_query($sql_mysql);

            $array_res = $vmsql->fetch_row_multi($qq);

            $offset = (isset($array_res[0][0])) ? $array_res[0][0] : 0;
        }

        // Case Sqlite | Oracle
        else if (VFRONT_DBTYPE == 'sqlite' || VFRONT_DBTYPE == 'oracle') {

            $ROWNUM = (VFRONT_DBTYPE == 'sqlite') ? "rowid" : "rownum";

            $sql_row = "SELECT rrrr FROM
			(SELECT $ROWNUM rrrr, ".$this->PK[0]." FROM $this->table t ORDER BY $this->orderby) temp
			WHERE ".$this->PK[0]."=$_COUNTER";

            $qr = $vmsql->query($sql_row);

            $RS = $vmsql->fetch_row($qr);

            $offset = ($RS[0] - 1);
        }

        // CASE POSTGRES (da MIGLIORARE L'EFFICIENZA!)
        else {

            $sql_pg = "SELECT ".$this->PK[0]." FROM $this->table t ORDER BY $this->orderby";

            $q_pg = $vmsql->query($sql_pg);

            $i = 0;

            while ($RS = $vmsql->fetch_row($q_pg)) {

                if ($RS[0] == $ID_COUNTER) {
                    $offset = $i;
                    break;
                }

                $i++;
            }
        } // end postgresql

        return $offset;
    }

    /**
     *
     * @global object $vmsql
     * @param string $sql
     * @return string XML
     */
    private function get_xml_1($sql, $offset) {

        $XML = xmlize($sql, null, // filename
                true, // header
                $offset, 
                $this->tot_records());

        return $XML;
    }


    /**
     *
     * @global object $vmsql
     * @param string $sql
     * @return string JSON
     */
    private function get_json_1($sql, $offset) {

        global $vmsql;

        $offset++;

        $q=$vmsql->query($sql);
        $RS = $vmsql->fetch_assoc($q);

         $tablename='';
        if(preg_match("/FROM +([a-z0-9_]+)/si",$sql,$found)){
            $tablename=$found[1];
        }

        $n_rows=$vmsql->num_rows($q);


        $o = new stdClass();
        $o->tot = $this->tot_records();
        $o->minoffset = $offset;
        $o->maxoffset = ($offset+($n_rows-1));
        $o->tablename = $tablename;
        $o->row[] = array('offset'=> $offset, 'data'=> $RS);

        return json_encode($o);
    }

    /**
     *
     * @global object $vmsql
     * @param string $sql
     * @return string XML
     */
    private function get_xml_all($sql) {

        $XML = xmlize($sql, null, // filename
                true, // header
                false, // offset
                $this->tot_records());

        return $XML;
    }
    /**
     *
     * @global object $vmsql
     * @param string $sql
     * @return string JSON
     */
    private function get_json_all($sql) {

        global $vmsql;


        $tablename='';
        if(preg_match("/FROM +([a-z0-9_]+)/si",$sql,$found)){
            $tablename=$found[1];
        }

        $q=$vmsql->query($sql);
        $n_rows=$vmsql->num_rows($q);

        $o = new stdClass();
        $o->tot = $n_rows;
        $o->minoffset = 1;
        $o->maxoffset = $n_rows;
        $o->tablename = $tablename;

        $i = 1;
        while($RS = $vmsql->fetch_assoc($q)){
            $o->row[] = array('offset'=> $i, 'data'=> $RS);
            $i++;
        }

        return json_encode($o);
    }

    /**
     * Get and render 1 record from the table
     *
     * @global object $vmsql
     * @param int $offset
     * @return string
     */
    public function get_output_1($offset) {

        global $vmsql;

        $sql = "SELECT * FROM $this->table $this->where ORDER BY $this->orderby " . $vmsql->limit(1, $offset);

        if ($this->outputType == 'XML') {
            return $this->get_xml_1($sql, $offset);
        }
        else if($this->outputType == 'JSON'){
            return $this->get_json_1($sql, $offset);
        }
    }

    /**
     * Get and render all records from the table
     * @return string
     */
    public function get_output_all() {

        $sql = "SELECT * FROM $this->table $this->where  ORDER BY $this->orderby ";

        if ($this->outputType == 'XML') {
            return $this->get_xml_all($sql);
        }
        else if($this->outputType == 'JSON'){
            return $this->get_json_all($sql);
        }
    }


    public function get_grid_rules() {

        return $this->Reg->get_column_tableview();
    }

    /**
     *  Send a header after rendering the output (XML or JSON)
     */
    public function send_header() {

        if ($this->outputType == 'XML') {
            header("Content-Type: text/xml; charset=" . FRONT_ENCODING);
        } else if ($this->outputType == 'JSON') {
            header("Content-Type: application/json; charset=" . FRONT_ENCODING);
        }
    }

    /**
     * Funzione di modifica di un record.
     * Restituisce l'SQL per la modifica
     *
     * @param array $_dati
     * @param array $_pk
     * @return string SQL
     */
    public function rpc_query_update($_dati, $_pk) {

        global $vmsql, $db1;

        $sql = "UPDATE $this->table SET ";

        // get special types
        $info_cols = RegTools::columns_info($this->table);

        foreach ($_dati as $k => $val) {

            $val_hidden = RegTools::variabili_campi($val);

            if ($val_hidden != false) {
                $val = $val_hidden;
            }

            $val = $this->urldecode_js($val);

            if ($info_cols[$k]['type'] == 'date' && trim($val) == '') {

                $sql.="\n $k=NULL,";
            } else {
                $sql.="\n $k='" . $vmsql->escape($val) . "',";
            }
        }

        $sql = substr($sql, 0, -1);

        if (count($_pk) <= 0) {
            return false;
        } else {

            $sql.="\n WHERE ";

            foreach ($_pk as $k => $val) {
                $sql.="$k='" . $vmsql->escape($val) . "' \n AND ";
            }
        }

        $sql = substr($sql, 0, -4);

        $sql.= (VFRONT_DBTYPE == 'mysql') ? " LIMIT 1" : "";

        Common::rpc_debug($sql);

        return $sql;
    }

    /**
     * Funzione di inserimento nuovo record.
     * Restituisce l'SQL per l'inserimento.
     *
     * @param array $_dati
     * @return string SQL
     */
    public function rpc_query_insert($_data) {

        global $vmsql;

        $fields = '';
        $values = '';

        // get special types
        $info_cols = RegTools::columns_info($this->table);



        foreach ($_data as $k => $val) {

            $fields.="$k,";

            //imposto gli eventuali hidden
            if (RegTools::variabili_campi($val) != false)
                $val = RegTools::variabili_campi($val);

            $val = urldecode($val);

            if ($info_cols[$k]['type'] == 'date' && trim($val) == '') {
                $values.=" NULL,";
            }
            else if($info_cols[$k]['type'] == 'numeric'){

                if(trim($val) == ''){
                    $values.=" NULL,";
                }
                else{
                    $values.= floatval($val).",";
                }
            }
            else{
                $values.="'" . $vmsql->escape($val) . "',";
            }


        }

        $fields = substr($fields, 0, -1);
        $values = substr($values, 0, -1);

        $sql = "INSERT INTO $this->table ($fields) VALUES ($values)";

        return $sql;
    }

    /**
     * Funzione di preparazione query di cancellazione record mediante la maschera di VFront.
     * La funzione è richimata via chiamata esterna Javascript e restituisce il codice SQL.
     *
     * @param array $_pk
     * @return string SQL
     */
    public function rpc_query_delete($_pk) {

        global $vmsql;

        $campi = "";
        $valori = "";
        $condizione = '';

        foreach ($_pk as $k => $val) {
            $condizione.=" $k='" . $vmsql->escape($val) . "' AND";
        }

        $condizione = substr($condizione, 0, -3);

        $sql = "DELETE FROM $this->table WHERE $condizione";

        if (VFRONT_DBTYPE == 'mysql')
            $sql.=" LIMIT 1";

        return $sql;
    }

}

