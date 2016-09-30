<?php

class RegColumn_Admin extends RegColumn {
    
    private $vmreg;
    private $prefix;
    
    public function __construct() {
        
        global $vmreg, $db1;
        
        $this->vmreg =$vmreg; 
        $this->prefix = $db1['frontend'].$db1['sep'];
    }
    
    public function save(){
        
        if($this->id_reg === null){
            return $this->insert();
        }
        else if( intval($this->id_reg) > 0 ){
            return $this->update();
        }
        else{
            
            // exception
        }
    }
    
    private function insert(){
        
        $sql= sprintf("INSERT INTO {$this->prefix}registro_col (
            
				id_table, 
                gid,
				column_name,
				ordinal_position,
				column_default,
                
				is_nullable,
				column_type,
				character_maximum_length,
				data_type,
				
                extra,
				commento,
                
                in_tipo,
                in_default,
                in_visibile,
                in_richiesto,
                
                in_suggest,
                in_table,
                in_line,
                in_ordine,
                
                jstest,
                alias_frontend
                )
				VALUES 
				( %d, %d, '%s', %d, '%s',
                 '%s', '%s', %d, '%s',
                 '%s', '%s',
                 '%s', '%s', %d, %d,
                  %d, %d, %d, %d,
                 '%s', '%s'
                 )",

                    $this->id_table,
                    $this->gid,
                    $this->vmreg->escape($this->column_name),
                    $this->ordinal_position,
                    $this->vmreg->escape($this->column_default),

                    $this->vmreg->escape($this->is_nullable),
                    $this->vmreg->escape($this->column_type),
                    $this->character_maximum_length,
                    $this->data_type,

                    $this->extra,
                    $this->vmreg->escape($this->commento),
                
                    $this->vmreg->escape($this->in_tipo),
                    $this->vmreg->escape($this->in_default),
                    $this->in_visibile,
                    $this->in_richiesto,
                
                    $this->in_suggest,
                    $this->in_table,
                    $this->in_line,
                    $this->in_ordine,
                
                    $this->vmreg->escape($this->jstest),
                    $this->vmreg->escape($this->alias_frontend)
                );
        
        $q=$this->vmreg->query($sql);
        
        if($this->vmreg->affected_rows($q)>0){
            return $this->id_reg = $this->vmreg->insert_id( $this->prefix .'registro_col', 'id_reg');
        }
        else{
            return false;
        }
    }
    
    private function update(){
        
    }
    
    public function delete(){
        
        $q = $this->vmreg->query("DELETE FROM {$this->prefix}registro_col WHERE id_reg=".intval($this->id_reg));
        return ($this->vmreg->affected_rows($q) == 1) ? true : false;
    }
    
    public function __clone() {
        $this->id_reg=null;
    }
    
    public function clona($id_parent, $gid){
        
        $C2= clone $this;
        $C2->id_table= (int) $id_parent;
        $C2->gid= (int) $gid;
        
        return $C2;
    }
}
