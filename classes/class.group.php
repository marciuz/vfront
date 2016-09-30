<?php

class Group {
   
    public $gid;
    public $nome_gruppo;
    public $descrizione_gruppo;
    public $data_gruppo;
    
    private $vmreg;
    private $prefix;
    
    public function __construct() {
        global $vmreg, $db1;
        $this->vmreg=$vmreg;
        $this->prefix=$db1['frontend'].$db1['sep'];
    }
    
    public function exists($gid){
        $q = $this->vmreg->query("SELECT gid FROM {$this->prefix}gruppo WHERE gid=".intval($gid));
        return (bool) $this->vmreg->num_rows($q);
    }
    
    public function save(){
        if($this->gid === null){
            return $this->insert();
        }
        else {
            return $this->update();
        }
    }
    
    private function insert(){
        
        if($this->gid === null){
            $q0= $this->vmreg->query("SELECT MAX(gid)+1 FROM {$this->prefix}gruppo");
            $RS = $this->vmreg->fetch_row($q0);
            $this->gid = $RS[0];
        }
        
        $sql=sprintf("INSERT INTO {$this->prefix}gruppo 
            (gid, nome_gruppo, descrizione_gruppo, data_gruppo)
            VALUES (%d, '%s', '%s', '%s')",
            $this->gid,
            $this->vmreg->escape($this->nome_gruppo),
            $this->vmreg->escape($this->descrizione_gruppo),
            $this->vmreg->escape($this->data_gruppo)
         );
        
        $q = $this->vmreg->query($sql);
        
        return $this->vmreg->affected_rows($q);
    }
    
    private function update(){
        
        $sql=sprintf("UPDATE {$this->prefix}gruppo 
            SET nome_gruppo='%s', 
            descrizione_gruppo='%s',
            data_gruppo='%s'
            
            WHERE gid=%d ",
            $this->vmreg->escape($this->nome_gruppo),
            $this->vmreg->escape($this->descrizione_gruppo),
            $this->vmreg->escape($this->data_gruppo),
            $this->gid
         );
        
        $q = $this->vmreg->query($sql);
        
        return $this->vmreg->affected_rows($q);
    }
    
    public function inizialize(){
        $this->gid= 0;
        $this->nome_gruppo= 'default';
        $this->descrizione_gruppo= _('Default group');
        $this->data_gruppo= date('Y-m-d H:i:s');
        $this->save();
    }
    
}