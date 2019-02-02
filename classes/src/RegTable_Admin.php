<?php


class RegTable_Admin extends RegTable {
    
    private $vmreg;
    private $prefix;
    private $save_method;
    
    public function __construct() {
        
        global $vmreg, $db1;
        
        $this->vmreg =$vmreg; 
        $this->prefix = $db1['frontend'].$db1['sep'];
    }
    
    /**
     * Extends the RegTable to a RegTable_Admin
     * @param RegTable $RegTable
     */
    public function load_extend(RegTable $RegTable){
        
        foreach($RegTable as $attr=>$value){
            if(property_exists('RegTable_Admin', $attr)){
                $this->{$attr} = $value;
            }
            else {
                trigger_error("The $attr isn't a property of ".get_class($this), E_NOTICE);
            }
        }
        
        if(count($this->columns)==0){
            $this->columns = $this->load_columns($this->id_table);
        }
    }
    
    public function load($id_table){
        
        $q=$this->vmreg->query("SELECT * FROM {$this->prefix}registro_tab WHERE id_table=".intval($id_table));
        $o = $this->vmreg->fetch_object($q);
        foreach($o as $attr=>$value){
            if(property_exists('RegTable_Admin', $attr)){
                $this->{$attr} = $value;
            }
            else {
                trigger_error("The $attr isn't a property of ".get_class($this), E_NOTICE);
            }
        }
        
        $this->columns = $this->load_columns($id_table);
    }
    
    private function load_columns($id_table){
        
        $sql = "SELECT * FROM {$this->prefix}registro_col 
            WHERE id_table=".intval($id_table)." 
            ORDER BY in_ordine, ordinal_position";
        
        $q=$this->vmreg->query($sql);
        
        return $this->vmreg->fetch_object_all($q, 'RegColumn_Admin');
    }
    
    public function save(){
        
        if($this->id_table === null){
            $this->save_method='insert';
            return $this->insert();
        }
        else if(intval($this->id_table) > 0) {
            $this->save_method='update';
            return $this->update();
        }
        else{
            return -1;
        }
    }
    
    
    public function delete(){
        
        $q = $this->vmreg->query("DELETE FROM {$this->prefix}registro_tab WHERE id_table=".intval($this->id_table));
        return ($this->vmreg->affected_rows($q) == 1) ? true : false;
    }
    
    public function __clone() {
        $this->id_table=null;
    }
    
    
    private function insert(){
        
        $sql_tab_dett= sprintf("INSERT INTO {$this->prefix}registro_tab (
				gid,
				table_name, 
				table_type, 
                visibile,
                
                in_insert,
                in_duplica,
                in_update,
                in_delete,
                
                in_export,
                in_import,
                data_modifica,
                orderby,
                
                orderby_sort,
                permetti_allegati,
                permetti_allegati_ins,
                permetti_allegati_del,
                
                permetti_link,
                permetti_link_ins,
                permetti_link_del,
                
                view_pk,
                fonte_al,
                table_alias,
                allow_filters,
				commento,
                default_view,
                default_filters
                )
				VALUES 
                (%d, '%s', '%s', %d,
                %d, %d, %d, %d, 
                %d, %d, %d, '%s',
                '%s',%d, %d, %d,
                %d, %d, %d, 
                '%s', '%s', '%s', %d, '%s' , '%s', '%s'
                )",
                $this->gid,
                $this->vmreg->escape($this->table_name),
                $this->vmreg->escape($this->table_type),
                $this->visibile ,

                $this->in_insert ,
                $this->in_duplica ,
                $this->in_update ,
                $this->in_delete ,

                $this->in_export ,
                $this->in_import ,
                $this->data_modifica ,
                $this->vmreg->escape($this->orderby) ,

                $this->vmreg->escape($this->orderby_sort) ,
                $this->permetti_allegati ,
                $this->permetti_allegati_ins ,
                $this->permetti_allegati_del ,

                $this->permetti_link ,
                $this->permetti_link_ins ,
                $this->permetti_link_del ,

                $this->vmreg->escape($this->view_pk) ,
                $this->vmreg->escape($this->fonte_al) ,
                $this->vmreg->escape($this->table_alias) ,
                $this->allow_filters ,
                $this->vmreg->escape($this->commento),
                $this->default_view,
                $this->vmreg->escape($this->default_filters)
         );
        
        $q=$this->vmreg->query($sql_tab_dett);
        
        $this->id_table = $this->vmreg->insert_id( $this->prefix . 'registro_tab', 'id_table');
        
        return $this->id_table;
    }
    
    private function update(){
        
        $clausola_view_pk = ($this->view_pk === null ) ?  "NULL" : "'".$this->view_pk."'" ;
        $clausola_view_fonte_al = ($this->fonte_al === null) ? 'NULL' : "'".$this->fonte_al."'";
        
        $this->default_view = (in_array($this->default_view, array('form','table'))) ? $this->default_view : 'form';
        
        $sql="UPDATE {$this->prefix}registro_tab 
            
                SET 
                    table_name='".$this->vmreg->escape($this->table_name)."',
                    table_type='".$this->vmreg->escape($this->table_type)."',
                    orderby='".$this->vmreg->escape($this->orderby)."', 
                    orderby_sort='".$this->vmreg->escape($this->orderby_sort)."', 
                    visibile=".intval($this->visibile).",
                    in_insert=".intval($this->in_insert).",
                    in_duplica=".intval($this->in_duplica).",
                    in_update=".intval($this->in_update).",
                    in_delete=".intval($this->in_delete).",
                    in_export=".intval($this->in_export).",
                    in_import=".intval($this->in_import).",
                    permetti_allegati=".intval($this->permetti_allegati).",
                    permetti_allegati_ins=".intval($this->permetti_allegati_ins).",
                    permetti_allegati_del=".intval($this->permetti_allegati_del).",
                    permetti_link=".intval($this->permetti_link).",
                    permetti_link_ins=".intval($this->permetti_link_ins).",
                    permetti_link_del=".intval($this->permetti_link_del).",
                    table_alias='".$this->vmreg->escape($this->table_alias)."',
                    commento='".$this->vmreg->escape($this->commento)."',
                    allow_filters='".intval($this->allow_filters)."',
                    view_pk=$clausola_view_pk
                    fonte_al=$clausola_view_fonte_al
                    data_modifica=".time().",
                    default_view='".$this->default_view."',
                    default_filters='".$this->vmreg->escape($this->default_filters)."'
                        
                WHERE id_table=".intval($this->id_table);
        
        $q=$this->vmreg->query($sql);
        
        return $this->vmreg->affected_rows($q);
    }
}
