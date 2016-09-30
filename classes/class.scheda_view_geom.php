<?php

class Scheda_View_Geom {
    
    
    static public function type_geom($data_col_value, $in_line){
        
        $class_width= (intval($in_line)===1) ? 'halftextarea':'fulltextarea';

        $input="<div class=\"geometry-container\" class=\"$class_width\" >"
            ."<div class=\"geometry-placeholder\" data-geom=\"$data_col_value\" id=\"map-$data_col_value\"></div> "
            ."<input type=\"button\" value=\"Show geometry\" class=\"geometry-button\" data-trigger=\"$data_col_value\" />"
        ."</div>\n";
        
        return $input;
    }
    
    
    public static function rpc_type_geom($pk_name, $id_record, $table, $field_name){
        
        global $db1, $vmsql;
        
        if($db1['dbtype']!='postgres'){
            
            return false;
        }
        
        $sql="SELECT ST_AsGeoJSON($field_name) FROM $table WHERE $pk_name='".$vmsql->escape($id_record)."'";
        
        if($vmsql->query_try($sql)){
            $q=$vmsql->query($sql);
            $RS = $vmsql->fetch_row($q);
            
            return $RS[0];
        }
        
    }
}
