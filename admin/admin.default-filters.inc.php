<?php

$FType = new FieldType();
	
##############################################################
#
#	 FORM IMPOSTAZIONI DEFAULT-FILTERS
#
#
#

function format_default_filters_row($campo, $tipo, $value='', $op=null, $FType=null){
    
    $value = addslashes(stripslashes(trim($value)));
    
    /*
    $out = "<div class=\"default-filter-row\">\n";
    $out.="<div class=\"df-eti\">".$campo."</div>";
    $out.="<div class=\"df-field\"><input data-type=\"$tipo\" type=\"text\" name=\"df[$campo]\" value=\"$value\" /></div>\n"; 
    $out.="</div>\n";
    */
    
    switch($tipo){
        case $FType->is_numeric($tipo):
            $class_tipo = 'numeric';
        break;
    
        case $FType->is_shortchar($tipo):
            $class_tipo = 'shortchar';
        break;
    
        case $FType->is_longchar($tipo):
            $class_tipo = 'longchar';
        break;
    
        case $FType->is_boolean($tipo):
            $class_tipo = 'boolean';
        break;
    
        case $FType->is_date($tipo):
            $class_tipo = 'date';
        break;
    
        default: $class_tipo='';
    }
    
    $operators="<select name=\"op[$campo]\" class=\"op-selector\">\n";
    $ops = Admin_Registry::get_default_filters_ops();
    $ops_keys = array_keys($ops);
    
    foreach($ops as $ok=>$ov){
        if($op !==null && in_array($op, $ops_keys) && $ok==$op){
            $selected = "selected=\"selected\"" ;
        }
        else{
            $selected='';
        }
        $operators.="<option value=\"".$ok."\" $selected>".$ov."</option>\n";
    }
    
    $operators.="</select>\n";
    
    $out = "<tr>\n";
    $out.="<th class=\"df-eti\">".$campo."</th>\n";
    $out.="<td class=\"df-op\">".$operators."</td>\n";
    $out.="<td class=\"df-field\"><input class=\"$class_tipo\" data-type=\"$tipo\" type=\"text\" name=\"df[$campo]\" value=\"$value\" /></td>\n"; 
    $out.="</tr>\n";
    
    return $out;
}

$R = new Registry();

$R->load_registry(intval($_GET['det']));

$df=$R->T->default_filters;


$st_sub = (isset($_GET['a']) && $_GET['a']==7) ? "" : "display:none;"; 

echo "<div class=\"default-filters\" id=\"default-filters\" style=\"$st_sub\">\n";

echo "<form action=\"".$_SERVER['PHP_SELF']."?det=".$_GET['det']."&amp;gid=".$_GET['gid']."&amp;a=7\" method=\"post\" >\n";
echo "<table class=\"tabella tab-default-filter\">\n";

for($i=0;$i<count($campi_tabella);$i++){
    
    if(isset($df[$campi_tabella[$i]])){
        $value = $df[$campi_tabella[$i]]->value;
        $op = $df[$campi_tabella[$i]]->op;
    }
    else{
        $value= $op = '';
    }
    
    echo format_default_filters_row($campi_tabella[$i], $in_tipo01[$i], $value, $op, $FType);
}

echo "</table>\n";

echo "<div class=\"df-buttons\">
    <input type=\"hidden\" name=\"det\" value=\"".intval($_GET['det'])."\" />  
    <input type=\"hidden\" name=\"gid\" value=\"".intval($_GET['gid'])."\" />  
    <input type=\"submit\" value=\" "._('Submit')." \" />  
    <input type=\"button\" id=\"df-reset\" value=\" "._('Reset')." \" />  
    </div>\n";

echo "</form>\n";
echo "</div>\n";

	
        
        
//-- fine impostazioni DEFAULT-FILTERS
	
	
	
	
	
	
