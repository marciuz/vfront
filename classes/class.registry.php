<?php

class Registry {
    
    protected $vmreg;
    protected $prefix;
    public $T;
    public $PK;
    private $use_cache=false;
    public $cache_is_used;
    
    
    /**
     * 
     * @global object $vmreg
     * @global array $db1
     */
    public function __construct() {
        global $vmreg, $db1;
        
        $this->vmreg = $vmreg;
        $this->db1 = $db1;
        $this->prefix = $db1['frontend'].$db1['sep'];
        $this->T= new RegTable();
    }
    
    public function use_cache($use_cache){
        $this->use_cache = (bool) $use_cache;
    }
    
    public function load_registry($id_or_name, $gid=0){
        
        if(!is_numeric($id_or_name)){
            $id_table = RegTools::name2oid($id_or_name, $gid);
        }
        else{
            $id_table = (int) $id_or_name;
        }
        
        
        // Load from cache
        $cached = $this->cache_get($id_table);
        if(is_a($cached, 'RegTable')){
            $this->T = $cached;
            $this->cache_is_used = true;
        }
        else{
            
            $this->cache_is_used = false;
        
            $this->T = $this->load_table_by_id($id_table, $gid);

            // load columns
            $this->T->columns=$this->load_columns($id_table);
            
            // load submasks
            $this->T->submasks=$this->load_submasks();

            // load buttons
            $this->T->buttons=$this->load_buttons();

            // load default filters
            $this->T->default_filters=$this->parse_default_filters();
            
            if($this->use_cache){
                $this->cache_set($id_table, $this->T);
            }
        }
        
        // Load PKs
        $this->PK = $this->prendi_PK();
    }
    
    /**
     * 
     * @param int $id_table
     * @return RegTable
     */
    public function load_table_by_id($id_table, $gid){
        
        if(Common::is_admin()){
            $sql_security='';
        }
        else{
            $sql_security = " AND gid=".intval($gid);
        }
        
        $sql="SELECT * FROM {$this->prefix}registro_tab WHERE id_table=".intval($id_table)." ".$sql_security;
        $q = $this->vmreg->query($sql);
        if($this->vmreg->num_rows($q) == 1){
            return $this->vmreg->fetch_object($q, 'RegTable');
        }
        else{
            return new RegTable();
        }
    }
    
    public function load_table_by_name($table_name, $gid=0){
        
        $id_table = RegTools::name2oid($table_name, $gid);
        return $this->load_table_by_id($id_table);
    }
    
    public function load_tables($gid){
        
        $sql="SELECT * FROM {$this->prefix}registro_tab WHERE gid=".intval($gid);
        $q = $this->vmreg->query($sql);
        if($this->vmreg->num_rows($q)> 0){
            return $this->vmreg->fetch_object_all($q, 'RegTable');
        }
        else{
            return array();
        }
    }
    
    private function load_columns($id_table){
        
        $sql = "SELECT * FROM {$this->prefix}registro_col 
            WHERE id_table=".intval($id_table)." 
            ORDER BY in_ordine, ordinal_position";
        
        $q=$this->vmreg->query($sql);
        
        return $this->vmreg->fetch_object_all($q, 'RegColumn');
    }
    
    private function parse_default_filters(){
        if(is_a($this->T, 'RegTable')){
            return (array) json_decode($this->T->default_filters);
        }
    }
    
    // Public methods
    
    public function campo_is_numeric($campo){
        
        if(is_array($this->T->columns) && count($this->T->columns)>0){
            foreach($this->T->columns as $col){
                if($col->column_name == $campo){
                    
                    return (bool) preg_match("/^(tinyint|mediumint|int|bigint|double|numeric|float|decimal)/i", $col->column_type);
                }
            }
        }
        
        return false;
    }
    
    
    /**
     * Funzione che, interrogando l'information_schema, recupera la chiava primaria di una tabella
     *
     * @param string $tabella
     * @param int $gid
     * @return string
     */
    public function prendi_PK(){

        if($this->T->table_type=='VIEW'){
            
            $campoPK = $this->T->view_pk;
        }
        else{
            $IS = new iSchema();	
            $campoPK=$IS->get_primary_keys($this->T->table_name);
        }

        return (array) $campoPK;
    }
    
    private function load_submasks(){
        
       $sql = "SELECT * FROM {$this->prefix}registro_submask
                WHERE id_table=".intval($this->T->id_table)." 
                ORDER BY nome_tabella
                ";

        $q=$this->vmreg->query($sql);

        return ($this->vmreg->num_rows($q)>0) 
                ? 
                $this->vmreg->fetch_object_all($q, 'RegSubmask') 
                : array();
    }
    
    private function load_buttons(){
        
        $sql = "SELECT * FROM {$this->prefix}button
                WHERE id_table=".intval($this->T->id_table)." 
                ORDER BY id_button
                ";

        $q=$this->vmreg->query($sql);

        return ($this->vmreg->num_rows($q)>0) 
                ? 
                $this->vmreg->fetch_object_all($q, 'RegButton') 
                : array();
    }
    
    
    public function get_column_tableview(){
        
        $tds = array();
        foreach($this->T->columns as $col){
            if($col->in_table == 1){
                $tds[] = $col;
            }
        }
        return $tds;
    }
    
    public function get_column_schedaview(){
        
        $c = array();
        foreach($this->T->columns as $col){
            if($col->in_visibile == 1){
                $c[] = $col;
            }
        }
        return $c;
    }
    
    public function public_table(){
        
        if(Common::is_admin()){
            return $this->T;
        }
        else{
            try{
                if($this->T->visibile == 1){
                    return $this->T;
                }
                else{
                    throw new Exception('Table not available');
                }
            }
            catch(Exception $e){
                openErrorGenerico('Ups! The table doesn\'t exists', false, 'The table you looking for doesn\'t exists or you don\'t have the permission to see it');
            }
        }
    }
    
    
    
    /**
     * Funzione che recupera l'ordinamento impostato in una tabella data
     *
     * @param string $tabella
     * @param int $gid
     * @return string
     */
    public function prendi_orderby(){
        
        $orderby = $this->T->orderby;
        $orderby_sort = $this->T->orderby_sort;

        // se non è stato impostato un orderby prende la chiave primaria
        if($orderby==''){
            $orderby= implode(",", $this->PK);
        }

        // orderby e orderby_sort possono essere valori oppure liste separate da virgola
        $orderby_a=explode(",",$orderby);
        $orderby_sort_a=explode(",",$orderby_sort);

        $string_orderby='';

        for($i=0;$i<count($orderby_a);$i++){

            $orderby_sort_a_string = (isset($orderby_sort_a[$i]) && $orderby_sort_a[$i]!='') ? $orderby_sort_a[$i] : "ASC";
            $string_orderby.= $orderby_a[$i]." ".$orderby_sort_a_string.",";
        }

        return substr($string_orderby,0,-1);
    }
    
    
    
    
    private function cache_get($id_table){
        
        if($this->use_cache === false){
            return null;
        }
        else{
            $q=$this->vmreg->query("SELECT obj FROM {$this->prefix}cache_reg WHERE id=".intval($id_table));
            if($this->vmreg->num_rows($q) == 1) {
                $RS = $this->vmreg->fetch_row($q);
                return unserialize($RS[0]);
            }
            else{
                return null;
            }
        }
    }
    
    private function cache_set($id_table, RegTable $object){
        
        if($this->use_cache === false) {
            return null;
        }
        else{
        
            $this->cache_flush($id_table);
            $serialized = serialize($object);
            $sql="INSERT INTO {$this->prefix}cache_reg (id, obj, last_update) 
                VALUES (".intval($id_table).", '".$this->vmreg->escape($serialized)."', '".date("c")."')";
            $q=$this->vmreg->query($sql);
            return $this->vmreg->affected_rows($q);
        }
    }
    
    public function cache_flush($id_table='all'){
        
        if($this->use_cache === false) return false;
        
        if($id_table !== 'all'){
            $sql_add= " WHERE id=".intval($id_table);
        }
        else{
            $sql_add='';
        }
        
        $this->vmreg->query("DELETE FROM {$this->prefix}cache_reg $sql_add");
    }

}