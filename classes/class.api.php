<?php
/**
 * @package VFront
 * @subpackage API
 * @author Mario Marcello Verona <marcelloverona@gmail.com>
 * @copyright 2013 M.Marcello Verona
 * @version 0.98 $Id$
 * @license http://www.gnu.org/licenses/gpl.html GNU Public License 
 * 
 */


/*
 * DB SCHEMA:
 * 
 CREATE TABLE `api_console` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `ip_address` varchar(20) NOT NULL DEFAULT '',
  `rw` tinyint(1) NOT NULL DEFAULT '0' COMMENT '0=read only, 1=read and write',
  `api_key` varchar(100) NOT NULL DEFAULT '',
  `last_update` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `api_key` (`api_key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
 * 
 */

class API {
    
    protected $return;
    
    private $auth_info;
    
    protected $tables;
    
    
    
    public function __construct() {
    
        $this->IS = new iSchema();
        
        $this->return=new stdClass();
        $this->return->error=false;
        $this->return->error_message='';
        
        $out=$this->get_auth();
        
        if($this->return->error){
            
            die($out);
        }
        else{
            
            //$IS = new iSchema();
            $this->tables=$this->IS->list_tables();
        }
    }
    
    
    
    /**
     * Check the allowed IP from API key
     * 
     * @param string $ip_allowed
     * @return boolean
     */
    public function check_ip($ip_allowed){
        
        $check=false;
        
        $user_ip=$this->getIP();
        
        if(strpos($ip_allowed,"/")!==false){
           
            list($ip_allowed_1,$wc)=explode("/",$ip_allowed);
        }
        else{
            $ip_allowed_1=$ip_allowed;
        }
        
        // With class (/8 /16 /24)
        if($ip_allowed_1!=$ip_allowed){
            
            $iptk=explode(".",$user_ip);
            
            switch($wc){
                
                case 8:
                    $check= preg_match("/^".$iptk[0]."\.".$iptk[1]."\.".$iptk[2]."\.[0-9]+/",$ip_allowed_1);
                break;
            
                case 16:
                    $check= preg_match("/^".$iptk[0]."\.".$iptk[1]."\.[0-9]+\.[0-9]+/",$ip_allowed_1);
                break;
            
                case 24:
                    $check= preg_match("/^".$iptk[0]."\.[0-9]+\.[0-9]+\.[0-9]+/",$ip_allowed_1);
                break;
            
                case 32:
                    $check= true;
                break;
            
                default: $check=false;
            }
        }
        else{
            
            if($user_ip == $ip_allowed){
                
                $check=true;
            }
        }
        
        return $check;
    }
    
    
    /**
     * Get the client IP
     * 
     * @return string
     */
    public function getIP() {
        if (!empty($_SERVER['HTTP_CLIENT_IP'])){
          $ip=$_SERVER['HTTP_CLIENT_IP'];
        }
        elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
          $ip=$_SERVER['HTTP_X_FORWARDED_FOR'];
        }
        else {
          $ip=$_SERVER['REMOTE_ADDR'];
        }
        return $ip;
    }
    
    
    
    public function send_json(){
        
        $this->__tojson($this->return, true);
    }
    
    public function __tojson($obj, $send=false){
        
        if($send){
            header("Content-type: application/json");
            print json_encode($obj);
            exit;
        }
        else{ 
            return json_encode($obj);
        }
    }
    
    
    
    /**
     * Get and check the authorization
     * 
     * @global object $vmreg
     * @global array $db1
     * @return boolean
     */
    protected function get_auth(){
        
        global $vmreg, $db1;
        
        $headers=apache_request_headers();
        
        if(!isset($headers['Authorization'])){
            
            $this->set_error('You need a Authorization to use this API. Please check your API key.');
        }
        else{
            
            // Check api key and IP
            
            $auth=trim($headers['Authorization']);
            
            $sql="SELECT * FROM {$db1['frontend']}{$db1['sep']}api_console WHERE api_key='".$vmreg->escape($auth)."'";
            
            $q=$vmreg->query($sql);
            
            if($vmreg->num_rows($q)==1){
                
                $this->auth_info=$vmreg->fetch_assoc($q);
                
                // Check the ip
                $check=$this->check_ip($this->auth_info['ip_address']);
                
                if($check){
                    
                }
                else{
                    
                    $this->set_error('This Authorization Key is not allowed from your IP.');
                }
            }
            else{
                
                $this->set_error('Your Authorization Key is not valid.');
            }
        }
        
        if($this->return->error){
            
            $this->send_json();
        }
        else{
            return true;
        }
    }
    
    
    private function set_error($string, $send_and_exit=true){
        
        $this->return->error=true;
        $this->return->error_message=$string;
        
        if($send_and_exit) $this->send_json();
    }
    
    
    /**
     * Wrapper for http header request
     */
    public function dispacher(){
        
        $method = $_SERVER['REQUEST_METHOD'];
        $request=explode("/",$_GET['qs']);

        switch ($method) {
          case 'PUT':
            $this->rest_put($request);  
            break;
          case 'POST':
            $this->rest_post($request);  
            break;
          case 'GET':
            $this->rest_get($request);  
            break;
          case 'HEAD':
            $this->rest_head($request);  
            break;
          case 'DELETE':
            $this->rest_delete($request);  
            break;
          case 'OPTIONS':
            $this->rest_options($request);    
            break;
          default:
            $this->rest_error($request);  
            break;
        }
    }
    
    
    /**
     * Generate a new auth key
     * 
     * @return string
     */
    public function gen_key(){
        
        return "VF-".sha1($_SESSION['user']['email'].rand());
    }
    
    
    
    // REST FUNCTIONS ---------------
    
    
    
    /**
     * GET responder
     * 
     * @global type $vmsql
     * @param string $req
     */
    protected function rest_get($req){
        
        global $vmsql;
        
        if($req[0]==''){
            
            $this->set_error('Your request seems to be empty.');
        }
        
        // SPECIAL: SHOW TABLES or DESCRIBE TABLES 
        
        if($req[0]=='tables'){
            
            
            // SHOW TABLES
            if(empty($req[1])){
            
                $this->return->results = $this->tables;

                $this->send_json($this->return);
            }
            
            
            // DESCRIBE TABLE
            else{
                
                if(in_array($req[1], $this->tables)){
                    
                    $columns=$this->IS->get_columns($req[1]);
                    
                    $this->return->results = $columns;
                }
                // errore: non esiste la tabella
                else{
                    
                    $this->set_error(sprintf('The table "%s" doesn\'t exists', $req[1]));
                }
                
                 $this->send_json($this->return);
            }
            
            
        }
        
        // NORMAL CASE: /table_name/
        
        //else if(isset($req[1]) && !empty($req[1])){
        else {
            
            // find the tables
            if(!$this->IS->table_exist($req[0])){
                
                $this->set_error(sprintf('The table "%s" doesn\'t exists', $req[0]));
            }
            else{
                
                $mat=$this->__select($req);
                
                // OUT TYPE
                if(isset($_GET['out_type']) && $_GET['out_type']=='csv'){
                    
                    
                }
                // DEFAULT JSON
                else{
                    $this->return->n = count($mat);
                    $this->return->results = $mat;
                    
                    $this->send_json();
                }
                
                
            }
        }
    }
    
    
    protected function rest_post($req){
        
        // check 
        if($this->auth_info['rw']!=1){
            $this->set_error("Write operations are not allowed in read-only API key");
        }
        else{
            $this->__insert($req);
        }
    }
    
    
    protected function rest_put($req){
        
        // check 
        if($this->auth_info['rw']!=1){
            $this->set_error("Write operations are not allowed in read-only API key");
        }
        else{
            $this->__update($req);
        }
    }
    
    protected function rest_delete($req){
        
        // check 
        if($this->auth_info['rw']!=1){
            $this->set_error("Write operations are not allowed in read-only API key");
        }
        else{
            $this->__delete($req);
        }
    }
    
    
    protected function rest_options($req){
        
        $this->set_error("This method is not allowed");
    }
    
    protected function rest_head($req){
        
        $this->set_error("This method is not allowed");
    }
    
    
    protected function rest_error($req){
        
        $this->set_error("This method is not allowed");
    }
    
    
    
    protected function __select($req){
        
        global $vmsql;
        
        $IS = new iSchema();
    
        // Limit 
        if(isset($_GET['limit']) && intval($_GET['limit'])>0){
            
            $offset=(isset($_GET['offset'])) ? intval($_GET['offset']) : 0;

            $LIMIT=$vmsql->limit(intval($_GET['limit']), $offset);
        }
        else{
            $LIMIT='';
        }

        if(isset($_GET['fields']) && $_GET['fields']!=''){

            $columns_all=$IS->get_columns($req[0]);//, 'column_name');
            $columns_all=$vmsql->reverse_matrix($columns_all);

            $columns=$columns_all['column_name'];

            $req_col=explode(",",$_GET['fields']);


            $allowed_cols = array();

            if(is_array($req_col) && is_array($columns)){
                foreach($req_col as $col){

                    if(in_array($col, $columns)){

                        $allowed_cols[]=$col;
                    }
                }
            }

            if(count($allowed_cols)>0){
                $COLUMNS=implode(",",$allowed_cols);
            }
            else{
                $COLUMNS='*';
            }
        }
        else{
            $COLUMNS='*';
        }

        
        
        // WHERE conditions --------------
        
        if(isset($req[1]) && $req[1]=='id' && isset($req[2])){
            
            // set WHERE
            $WHERE=$this->create_where($req);
        }
        else{
            $WHERE='';
        }
        
        
        // Create query
        
        $sql="SELECT $COLUMNS FROM ".$req[0]." $WHERE $LIMIT";
        
        if(isset($_GET['debug'])){
            $this->return->sql=$sql;
        }
        
        $q=$vmsql->query($sql);

        $n=$vmsql->num_rows($q);

        if(isset($_GET['headers']) && $_GET['headers']!='true'){
            $mat=$vmsql->fetch_row_all($q);
        }
        else{
            $mat=$vmsql->fetch_assoc_all($q);
        }
        
        return $mat;
    }
    
    
    
    
    /**
     * Parse raw data and convert it in array field=>value
     * 
     * @param string $fp
     * @return array
     */
    protected function parse_data($fp){
        
        $_DATA=array();
        
        $tk0=explode("&",$fp);
        
        for($i=0;$i<count($tk0);$i++){
            
            $tk=explode("=",$tk0[$i]);
            
            $_DATA[$tk[0]]= (isset($tk[1])) ? urldecode($tk[1]) : null; 
        }
        
        return $_DATA;
    }
    
    
    
    protected function create_where($req){
        
        global $vmsql;
        
        // get column types
        $cols_type=$this->IS->get_column_types($req[0]);
        
        // get column names
        //$cols=  array_keys($cols_type);
        
        if(!isset($req[2]) || !isset($req[1]) || $req[1]!='id'){
            
            $this->set_error("Syntax error: you should specify a valid primary key in query string");
        }
        
        $FType= new FieldType();
        
        $PKs=$this->IS->get_primary_keys($req[0]);
        
        if(is_string($PKs)){
            $PKs=array($PKs);
        }
        
        $vals=explode(",",$req[2]);
        
        if(count($PKs)!=count($vals)){
            
            $this->set_error("Primary keys and requested ID(s) arguments doesn't match");
        }
        else{
            
            for($i=0;$i<count($PKs);$i++){
                
                if($i==0){
                    $WHERE="WHERE ".$PKs[$i]."=";
                }
                else{
                    $WHERE.=" AND ".$PKs[$i]."=";
                }
           
                // field type
                if($FType->is_integer($cols_type[$PKs[$i]])){
                    $WHERE.=intval($vals[$i]);
                }
                else if($FType->is_double($cols_type[$PKs[$i]])){
                    $WHERE.=floatval($vals[$i]);
                }
                else{
                    $WHERE.="'".$vmsql->escape($vals[$i])."'";
                }
            }
            
            return $WHERE;
        }
        
    }
    
    
    /**
     * Insert data in a table
     * model: /{table_name}/
     * 
     * @global object $vmsql
     * @param array $req
     */
    private function __insert($req){
        
        global $vmsql;
        
        $vmsql->quiet=true;
        
        $FType= new FieldType();
        
        // table exists?
        if(!in_array($req[0], $this->tables)){
            
            $this->set_error(sprintf('The table "%s" doesn\'t exists', $req[0]));
        }

        // get raw input
        $fp=@file_get_contents('php://input');

        // parse raw input
        $_DATA = $this->parse_data($fp);
        
        // get column types
        $cols_type=$this->IS->get_column_types($req[0]);
        
        // get column names
        $cols=  array_keys($cols_type);
        
        // set vars
        $fields='';
        $values='';
        $fields_error='';
        
        // create the query
        foreach($_DATA as $k=>$v){
            
            if(in_array($k,$cols)){
                
                $fields.=$k.",";
                $values.= ($FType->is_numeric($cols_type[$k]) || $FType->is_boolean($cols_type[$k])) 
                        ?  $vmsql->escape($v)."," 
                        : "'".$vmsql->escape($v)."',";
            }
            else{
                
                $fields_error=$k.", ";
            }
        }
        
        // some field doesn't exists?
        if($fields_error!=''){
            
            $this->set_error(sprintf('Warning: the fields "%s" doesn\'t exists', substr($fields_error,0,-2)));
        }
        
        // create sql
        $sql="INSERT INTO ".$req[0]." (".substr($fields,0,-1).") VALUES (".substr($values, 0, -1).")";
        
        // execute sql
        $q=@$vmsql->query($sql);
        
        // sql errors?
        if(!$q){
            
            $ERROR = $vmsql->get_error();
            $this->return->error_sql=$ERROR;
            $this->set_error($ERROR['msg']);
        }
        
        // get affected rows
        $aff_rows=$vmsql->affected_rows($q);
        
        // set results
        $this->return->affected_rows= (int) $aff_rows;
        
        // set auto increment value
        $PK = $this->IS->get_primary_keys($req[0]);
       
        if(is_string($PK) && $aff_rows>0){
            $this->return->return_id= $vmsql->insert_id($req[0], $PK);
        }
        else{
            $this->return->return_id=null;
        }
        
        // return json
        $this->send_json();
    }
    
    
    /**
     * Update table data
     * model: /{table_name}/id/{pk}
     * 
     * @global object $vmsql
     * @param array $req
     */
    private function __update($req){
        
        global $vmsql;
        $vmsql->quiet=true;
        
        $FType= new FieldType();
        
        // table exists?
        if(!in_array($req[0], $this->tables)){
            
            $this->set_error(sprintf('The table "%s" doesn\'t exists', $req[0]));
        }

        // get raw input
        $fp=@file_get_contents('php://input');

        // parse raw input
        $_DATA = $this->parse_data($fp);
        
        // get column types
        $cols_type=$this->IS->get_column_types($req[0]);
        
        // get column names
        $cols=  array_keys($cols_type);
        
        // set vars
        $sql_tk='';
        $fields_error='';
        
        // create the query
        foreach($_DATA as $k=>$v){
            
            if(in_array($k,$cols)){
                
                $sql_tk.=$k."=";
                $sql_tk.= ($FType->is_numeric($cols_type[$k]) || $FType->is_boolean($cols_type[$k])) 
                        ?  $vmsql->escape($v)."," 
                        : "'".$vmsql->escape($v)."',";
            }
            else{
                
                $fields_error=$k.", ";
            }
        }
        
        // some field doesn't exists?
        if($fields_error!=''){
            
            $this->set_error(sprintf('Warning: the fields "%s" doesn\'t exists', substr($fields_error,0,-2)));
        }
        
        // set WHERE
        
        $WHERE=$this->create_where($req);
        
        // create sql
        $sql="UPDATE ".$req[0]." SET ".  substr($sql_tk, 0, -1)." $WHERE";
        
        // execute sql
        $q=@$vmsql->query($sql);
        
        // sql errors?
        if(!$q){
            
            $ERROR = $vmsql->get_error();
            $this->return->error_sql=$ERROR;
            $this->set_error($ERROR['msg']);
        }
        
        // get affected rows
        $aff_rows=$vmsql->affected_rows($q);
        
        // set results
        $this->return->affected_rows= (int) $aff_rows;
        
        // return json
        $this->send_json();
    }
    
    
    
    private function __delete($req){
        
        global $vmsql;
        $vmsql->quiet=true;
        
        $FType= new FieldType();
        
        // table exists?
        if(!in_array($req[0], $this->tables)){
            
            $this->set_error(sprintf('The table "%s" doesn\'t exists', $req[0]));
        }
        else if(!isset($req[1]) || $req[1]!='id' || !isset($req[2])){
            
            $this->set_error('Syntax error: the delete sintax is {table_name}/id/{id_value} ');
        }
        else{
            
            // set WHERE
            $WHERE=$this->create_where($req);
            
            $sql="DELETE FROM ".$req[0]." $WHERE";
            
            $q=@$vmsql->query($sql);
            
            
            // sql errors?
            if(!$q){

                $ERROR = $vmsql->get_error();
                $this->return->error_sql=$ERROR;
                $this->set_error($ERROR['msg']);
            }

            // get affected rows
            $aff_rows=$vmsql->affected_rows($q);

            // set results
            $this->return->affected_rows= (int) $aff_rows;

            // return json
            $this->send_json();
        }
    }
    
}