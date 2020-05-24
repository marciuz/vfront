<?php


class Scheda_View {
    
    const INT_DEFAULT_SIZE=10;
    const DATE_DEFAULT_SIZE=20;
    const TEXT_DEFAULT_SIZE=59;
    const TEXT_DOUBLE_DEFAULT_SIZE=135;
    const UNKNOW_DEFAULT_SIZE=30;
    
    const CK_WIDTH=708;
    const CK_HEIGHT=220;

    static public function type_int($data_col_value){

        $input="<input class=\"off int\" name=\"dati[".$data_col_value."]\" ".
               "id=\"dati_".$data_col_value."\" value=\"\" size=\"".self::INT_DEFAULT_SIZE."\" readonly=\"readonly\" type=\"text\" />";
        
        return $input;
    }
    
    
    static public function type_char($data_col_value, $maxsize, $in_suggest, $table_name, $inline=true){
        
        if($maxsize>100){
            
            if($maxsize<=80){
                $size=$maxsize[$i];
            }
            else{
                $size=self::TEXT_DOUBLE_DEFAULT_SIZE;
            }
            
            $class_width= ($inline) ? 'char':'longchar';
        }
        else {
            $size=self::TEXT_DEFAULT_SIZE;
            $class_width='char';
        }

        $class_ac = ($in_suggest=="1") ? ' autocomp':'';

        $input="<input class=\"off {$class_width}{$class_ac}\" name=\"dati[".$data_col_value."]\" ".
               "id=\"dati_".$data_col_value."\" value=\"\" size=\"$size\" readonly=\"readonly\" type=\"text\" />";


        // IMPOSTAZIONI SUGGEST PER LA RICERCA -----------------------

        if($in_suggest=="1"){

            $input.="
            <div id=\"suggest-$data_col_value\" class=\"campo-update\" style=\"display:none;border:1px solid black;background-color:white;\"></div>
                    <script type=\"text/javascript\" language=\"javascript\" charset=\"".FRONT_ENCODING."\">
                        new Ajax.Autocompleter('dati_$data_col_value','suggest-$data_col_value','rpc/rpc.suggest.php?t=$table_name',
                        { onCreate: function(){\$('dati_$data_col_value').addClassName('autocomp_active');},
                          onSuccess: function(){\$('dati_$data_col_value').removeClassName('autocomp_active');} 
                        });
                    </script>
            ";


        }
        
        return $input;

    }
    
    
    
    static public function type_text($data_col_value, $in_line){

        $class_width= (intval($in_line)===1) ? 'halftextarea':'fulltextarea';

        $input="<textarea class=\"off $class_width\" name=\"dati[".$data_col_value."]\" ".
               "id=\"dati_".$data_col_value."\" cols=\"".self::TEXT_DOUBLE_DEFAULT_SIZE."\" rows=\"9\" readonly=\"readonly\" ></textarea>";
        
        return $input;
    }
    
    static public function type_bool($data_col_value, $db_type){

        if($db_type == 'postgres'){
            $input="<input type=\"checkbox\" onclick=\"this.value=(this.value=='f' || this.value=='')?'t':'f'; mod(this.id);\" class=\"off\" name=\"dati[".$data_col_value."]\" ".
               "id=\"dati_".$data_col_value."\" disabled=\"disabled\" style=\"margin-left:0;\" value=\"0\" />";
        }
        else{
            $input="<input type=\"checkbox\" onclick=\"this.value=(this.value==0 || this.value=='')?1:0; mod(this.id);\" class=\"off\" name=\"dati[".$data_col_value."]\" ".
               "id=\"dati_".$data_col_value."\" disabled=\"disabled\" style=\"margin-left:0;\" value=\"0\" />";
        }
        
        return $input;
    }
    
    static public function type_password($data_col_value, $in_default){
        
        $input="<input class=\"off\" name=\"dati[".$data_col_value."]\" ".
               "id=\"dati_".$data_col_value."\" value=\"\" size=\"".self::TEXT_DEFAULT_SIZE."\" readonly=\"readonly\" type=\"password\" title=\"$in_default\" />";
        
        return $input;
    }
    
    static public function type_richtext($data_col_value){
        
        include_once(FRONT_ROOT."/plugins/ckeditor/ckeditor.php");

        $CKEditor1 = new CKEditor();

        $nome_dir= Common::dir_name();

        $config=array('width'=>self::CK_WIDTH, 'height'=>self::CK_HEIGHT, 'skin'=>'v2','toolbarStartupExpanded'=>true);
        $config['toolbar'] = array(
            array( 'Source', '-', 'Bold', 'Italic', 'Underline', 'Strike' ),
            array( 'NumberedList','BulletedList','-','Outdent','Indent','Blockquote','CreateDiv'),
            array( 'Format', '-', 'Table', 'Image', 'Link', 'Unlink', 'Anchor' )
        );
        $CKEditor1->basePath = $nome_dir."/plugins/ckeditor/";
        $CKEditor1->returnOutput=true;

        $input=$CKEditor1->editor('dati_'.$data_col_value, "", $config);
        $input.="<input type=\"hidden\" id=\"dati_".$data_col_value."\" 
            name=\"dati[".$data_col_value."]\" value=\"\" />\n";
        
        return $input;

    }
    
    
    static public function type_hidden($data_col_value, $in_default, $extra=''){
        
        if(isset($in_default)){
            $valore_hidden = $in_default;
            // stringa  per il default
        }
        else{
            $valore_hidden="";
            $str_hidden_default="";
        }

        $str_hidden_default="<span style=\"display:none\" id=\"hd_dati_".$data_col_value."\" >$valore_hidden</span>\n";


        if($extra=='1'){ // sovrascrittura in modifica
            $classe_hidden=' class="nomodify"';
        }
        else{ // scrittura solo in insert
            $classe_hidden="";
        }


        $input="
        $str_hidden_default
        <input name=\"dati[".$data_col_value."]\" ".
               "id=\"dati_".$data_col_value."\" $classe_hidden value=\"".Common::vf_utf8_encode($valore_hidden)."\" type=\"hidden\" />";
        
        return $input;
    }
    
    
    static public function type_select($data_col_value, $in_default){
        
        $input="<select onchange=\"mod(this.id);\" class=\"off\" name=\"dati[".$data_col_value."]\" ".
               "id=\"dati_".$data_col_value."\" disabled=\"disabled\" >\n";

        $value_select =  str_replace("[|]","\n",$in_default);
        $valori = explode("\n",$value_select);
        if(!is_array($valori)){
            $valori=array();
        }

        foreach($valori as $k=>$val){
            // se sono stati messi i separatori chiave, valore
            if(preg_match('|=|',$val)){
                list($kk,$val)=explode("=",$val);
            }
            else $kk=$val;

            $input.="\t\t<option value=\"$kk\">$val</option>\n";
        }

        $input.="</select>\n";

        return $input;
    }
    
    static public function type_selectfrom($data_col_value, $sql, $nome_tab){
        //return self::type_selectfrom_html($data_col_value, $sql, $nome_tab);
        return self::type_selectfrom_json($data_col_value, $sql, $nome_tab);
    }
    
    
    static public function type_selectfrom_html($data_col_value, $in_default, $nome_tab){
        
        $IFRAME = new Hash_Iframe($data_col_value,$in_default);
        $input="<div id=\"target_".$data_col_value."\"></div>\n";

        $input.="<iframe style=\"width:1px;height:1px;border:0;\" 
                id=\"i_id_".$data_col_value."\" 
                src=\"".FRONT_DOCROOT."/files/html/{$IFRAME->hash_html}.html\"	
                ></iframe>\n";

        // SE LA TABELLA E' SCRIVIBILE METTI UN LINK
        if($IFRAME->in_insert_tab=="1" && $IFRAME->in_visibile && $_SESSION['VF_VARS']['crea_nuovo_valore_ref']){

            $href_nuovo_record=" <a class=\"inalto\" href=\"javascript:;\" onclick=\"openWindow('scheda.php?oid=".$IFRAME->id_table_ref."&parent_field=".$data_col_value."&parent_table={$nome_tab}','nuovo_valore',80)\" title=\""._("Add a new value for this listing")."\">"._("New entry")."</a>\n";
        }

        // cancello l'istanza
        unset($IFRAME);
        
        return $input;
    }
    
    static public function type_selectfrom_json($data_col_value, $sql, $nome_tab){
        $SV = new Select_Values();
        $SV->set_data($sql, $nome_tab);
        $input= "<div class=\"select_values\" data-target=\"".$data_col_value."\" data-require=\"$SV->hash_js\">";
        $input.="<select name=\"dati[".$data_col_value."]\" id=\"dati_$data_col_value\" ></select>";
        $input.="</div>\n";
        return $input;
    }
    
    
    static public function type_autocomplete_from($data_col_value, $id_reg){
        
        $size=self::TEXT_DEFAULT_SIZE;
        $class_width='char';

        $input="<input class=\"off $class_width autocomp autocomp_from\" name=\"dati[".$data_col_value."]\" ".
               "id=\"dati_ac_".$data_col_value."\" value=\"\" size=\"$size\" readonly=\"readonly\" type=\"text\" />";

        $input.="<input name=\"dati[".$data_col_value."]\" id=\"dati_".$data_col_value."\" value=\"\" type=\"hidden\" class=\"autocomp_from_hidden\" />";


        // IMPOSTAZIONI SUGGEST PER LA RICERCA -----------------------

        $input.="
            <div id=\"suggest-{$data_col_value}\" class=\"campo-update\" style=\"display:none;border:1px solid black;background-color:white;\"></div>
                    <script type=\"text/javascript\" language=\"javascript\" charset=\"".FRONT_ENCODING."\">
                        new Ajax.Autocompleter('dati_ac_{$data_col_value}','suggest-{$data_col_value}','rpc/rpc.suggest.php?id_col={$id_reg}',{ afterUpdateElement : get_autocompleter_from_id });
                    </script>
        ";
                        
         return $input;
    }
    
    static public function type_date($data_col_value, $load_calendar){
        
       $size=self::DATE_DEFAULT_SIZE;
        

       $input="<input class=\"off data\" name=\"dati[".$data_col_value."]\" ".
               "id=\"dati_".$data_col_value."\" value=\"\" size=\"$size\" readonly=\"readonly\" type=\"text\" />";

        if($load_calendar){

            // Formato data calendarietti
            switch (self::get_date_format()) {

                case 'ita':	$formato_data="%d/%m/%Y";
                break;

                case 'eng':	$formato_data="%m/%d/%Y";
                break;

                default: $formato_data="%Y-%m-%d";
                break;
            }


            // Pulsante calendario

            $input.=" <img src=\"img/cal_small.gif\" id=\"trigger__dati_".$data_col_value."\" "
                ." class=\"calendarietto\" title=\"Date selector\" alt=\"Date selector\" "
                ." onmouseover=\"this.style.background='red';\" "
                ." onmouseout=\"this.style.background=''\" />";

            $input.="

                <script type=\"text/javascript\">

                /* <![CDATA[ */

                Calendar.setup({
                     inputField     :    \"dati_{$data_col_value}\",   // id of the input field
                     button	       :    \"trigger__dati_{$data_col_value}\",   // id of the img field
                     firstDay	   :    1,
                     ifFormat       :    \"{$formato_data}\",       // format of the input field
                     showsTime      :    false,
                     timeFormat     :    \"24\", 
                     disableFunc    :    caldis,
                     onUpdate       :    catcalc
                 });    

                 /* ]]> */

                 </script>
                ";
        }
        
        return $input;
    }
    
    
    static public function type_datetime($data_col_value, $load_calendar, $i){
        
        $input="<input class=\"off data\" name=\"dati[".$data_col_value."]\" ".
               "id=\"dati_".$data_col_value."\" value=\"\" size=\"".self::DATE_DEFAULT_SIZE."\" readonly=\"readonly\" type=\"text\" />";

        if($load_calendar){	

            // Formato data calendarietti
            switch (self::get_date_format()) {

                case 'ita':	$formato_dataora="%d/%m/%Y %H:%M";
                break;

                case 'eng':	$formato_dataora="%m/%d/%Y %H:%M";
                break;

                default: $formato_dataora="%Y-%m-%d %H:%M";
                break;
            }

                // Pulsante calendario

            $input.=" <img src=\"img/cal_small.gif\" id=\"trigger__dati_".$data_col_value."\" class=\"calendarietto\" "
                    ." title=\"Date selector\" "
                    ." alt=\"Date selector\" onmouseover=\"this.style.background='red';\" "
                    ." onmouseout=\"this.style.background=''\" onclick=\"get_trigger_cal$i();\" />";

            $input.="

                <script type=\"text/javascript\">
                /* <![CDATA[ */
                 MyCal_$i = Calendar.setup({
                             inputField     :    \"dati_{$data_col_value}\",   // id of the input field
                             button	       :    \"trigger__dati_{$data_col_value}\" ,   // id of the img field
                             firstDay	   :    1,
                             ifFormat       :    \"{$formato_dataora}\",       // format of the input field
                             showsTime      :    true,
                             timeFormat     :    \"24\",
                             disableFunc    :    caldis,
                             onUpdate       :    catcalc
                         });   

                 /* ]]> */
                 </script>
                 ";

        }
        
        return $input;
    }
    
    
    static public function type_onlyread($data_col_value){
        
        $input="<div class=\"onlyread-field\" >"
            ."<input type=\"text\" "
            ."id=\"dati_".$data_col_value."\" class=\"hh_field\" "
            ."value=\"\" readonly=\"readonly\" name=\"dati[".$data_col_value."]\" />"
        ."</div>\n";
        
        return $input;
    }
    
    static public function type_onlyread_multi($data_col_value, $in_line){
        
        $class_width= (intval($in_line)===1) ? 'halftextarea':'fulltextarea';

        $input="<div class=\"onlyread-field\" >"
            ."<textarea id=\"dati_".$data_col_value."\" class=\"hh_field $class_width\" "
            ."cols=\"132\" rows=\"9\" readonly=\"readonly\" name=\"dati[".$data_col_value."]\" ></textarea>"
        ."</div>\n";
        
        return $input;
    }

    static public function type_unknow($data_col_value){
        
        $size=self::UNKNOW_DEFAULT_SIZE;
    
        $input="<input class=\"off\" name=\"dati[".$data_col_value."]\" ".
               "id=\"dati_".$data_col_value."\" value=\"\" size=\"$size\" readonly=\"readonly\" type=\"text\" />";
        
        return $input;
    }

    
    static public function get_date_format(){
        
        $DATE_FORMAT = (isset($_SESSION['VF_VARS']['force_isodate_on_mask']) 
                              && ($_SESSION['VF_VARS']['force_isodate_on_mask']==1))
                         ? 'iso'
                         : FRONT_DATE_FORMAT;
        
        return $DATE_FORMAT;
    }
            
}