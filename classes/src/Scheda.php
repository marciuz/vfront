<?php

/**
 * Description of class
 *
 * @author Marcello Verona
 */
class Scheda {
    
    private $data_tab;
    
    protected $gid, $oid;
    
    /*private $data_type, $is_nullable, $in_richiesto,
            $in_suggest, $in_tipo, $in_default, $commento, $jstest, $alias_frontend, 
            $in_line, $id_reg;
    */
    
    public $MAX;
    
    public $info_pk;
    
    public $load_calendar=false;
    
    public $carica_md5 = false;
    public $carica_sha1 = false;
    public $fields_autocompleter_from=array();
    public $fields_select_from=array();
    public $n_tendine_attese = 0;
    public $campi_req= array();
    public $campi_suggest= array();
    public $CKEditors= array();
    
    public $outputType = 'JSON';
    
    public $rules=array();
    
    public $Reg;
    
    public $js_select=array();
    
    public function __construct($oid, $gid=null) {
        
        $this->oid = (int) $oid;
        //$this->data_tab = $this->get_data_table($oid);
        $this->gid = ($gid === null) ? $_SESSION['gid'] : $gid;
        
        $this->load_calendar = ($_SESSION['VF_VARS']['usa_calendari']==1) ? true:false;
        
        $this->Reg = new Registry();
        $this->Reg->load_registry($this->oid, $this->gid);
        $this->PT = $this->Reg->public_table();
        $this->info_pk = $this->Reg->PK;
    }
    
    protected function get_data_table(){
        
        return RegTools::prendi_info_tabella($this->oid);
    }
    
    public function table_name(){
        return isset($this->PT->table_name) ? $this->PT->table_name : '';
    }
    
    
    public function set_max(Rpc $RPC){
        $this->MAX = $RPC->tot_records();
    }

    /**
     * Funzione per la definizione di larghezza del campo
     *
     * @param string $nome
     * @param string $xg_tipo
     * @return int
     */
    public function campo_len($nome,$xg_tipo){

        $lentxt=strlen($nome)*10;

        if($xg_tipo=="timestamp"){
            $lentxt=120;
        }

        return $lentxt;
    }


    /**
     * Creazione del pulsante per le sottomaschere
     *
     * @param array $sm
     */
    public function pulsante_sottomaschera($sm){

        $nome_front= (trim($sm['nome_frontend'])!="") ? $sm['nome_frontend']:$sm['nome_tabella'];

        $in_shadowbox= ($sm['tipo_vista']=='schedash') ? 'true':'false';

        return "<input class=\"pulsante-submask\" type=\"button\" name=\"sm[".$sm['nome_tabella']."]\"  id=\"sm_".$sm['nome_tabella']."\" value=\"$nome_front\" "
             ."onclick=\"apri_submask(".$sm['id_table'].",".$sm['id_submask'].", $in_shadowbox);\" />\n";

    }


    /**
     * Creazione del pulsante per le sottomaschere
     *
     * @param array $pp
     */
    public function pulsante_custom($pp){

        $color = ($pp['color']=='') ? '#000' : $pp['color'];
        $background = ($pp['background']=='') ? '#FCF' : $pp['background'];

        $def_pulsante_parsed = preg_replace('|{{([a-z0-9_:]+)}}|si',"'+get_scheda_val('$1')+'",$pp['definition']);

        switch($pp['button_type']){

            case 'link_self': 
                    $js_action=" location.href='".($def_pulsante_parsed)."';";
            break;

            case 'link_blank' :
                    $js_action=" openWindow('$def_pulsante_parsed','CustomButtom{$pp['id_button']}',70);";
            break;

            case 'link_shadow' :

                    $settings1=explode("&",$pp['settings']);
                    $settings=array();
                    if(is_array($settings1)){
                        foreach ($settings1 as $val){
                            if($val!=''){
                                list($k,$v)=explode("=",$val);
                                $settings[$k]=$v;
                            }
                        }
                    }

                    $height=(isset($settings['height'])) ?  ',height:'.$settings['height'] : '';
                    $width=(isset($settings['width'])) ?    ',width:'.$settings['width'] : '';

                    $js_action=" lll='$def_pulsante_parsed'; Shadowbox.open({ content:lll, player: 'iframe' $height $width });";
            break;

            default: $js_action=$def_pulsante_parsed;

        }

        $js_action_ret=" try{ $js_action } catch(e){ alert(e); } ";

        return "<input class=\"pulsante-submask\" type=\"button\" name=\"custom_button{$pp['id_button']}\" "
             ."value=\"{$pp['button_name']}\" "
             ."onclick=\"$js_action_ret\" "
             ."style=\"background-color:#$background; color:#$color;\"/>\n";

    }


    private function shadowbox_is_active($buttons){
        return true;
    }

    public function print_shortcuts($permetti_link, $permetti_allegati) {


        $js_manuale = <<<JS

            var keyActions = new Array ();

            keyActions [0] = {character:  39, // freccia dx + shift - record avanti veloce
                              actionType: "code", 
                              param:      "if(!modifiche_attive && !ricerca && (counter+passoVeloce)<max){sndReq(tabella ,'next10',true);reloadGrid();}",
                              mod:        "CTRL+SHIFT"  };

            keyActions [1] = {character:  37, // freccia sx + shift - record indietro veloce
                              actionType: "code", 
                              param:      "if(!modifiche_attive && !ricerca && (counter-passoVeloce)>0){sndReq(tabella,'prev10',true);reloadGrid();}",
                              mod:        "CTRL+SHIFT"  };

            keyActions [2] = {character:  38, // freccia su - vai al primo record
                              actionType: "code", 
                              param:      "if(!modifiche_attive && !ricerca){sndReq(tabella ,'min',true);reloadGrid();}",
                              mod:        "CTRL"    }; 

            keyActions [3] = {character:  40, // freccia giu' - vai all'ultimo record
                              actionType: "code", 
                              param:      "if(!modifiche_attive && !ricerca){sndReq(tabella ,'max',true);reloadGrid();}",
                              mod:        "CTRL"    };

            keyActions [4] = {character:  39, // freccia dx - record avanti
                              actionType: "code", 
                              param:      "if(!modifiche_attive && !ricerca){sndReq(tabella ,'next',true);}",
                              mod:        "CTRL"    };

            keyActions [5] = {character:  37, // freccia sx - record indietro
                              actionType: "code", 
                              param:      "if(!modifiche_attive && !ricerca){sndReq(tabella ,'prev',true);}",
                              mod:        "CTRL"    }; 

            keyActions [6] = {character:  82, // R - Cerca
                              actionType: "code", 
                              param:      "cerca()",
                              mod:        "ALT"     };

            keyActions [7] = {character:  13, // invio in modalita ricerca
                              actionType: "code", 
                              param:      "if(ricerca){cerca()}",
                              mod:        "CTRL"    };

            keyActions [8] = {character:  65, // A - Annulla
                              actionType: "code", 
                              param:      "annulla()",
                              mod:        "ALT"     };

JS;


        // contatore per l'array JS keyActions 
        $contatore_key = 9;


        if ($this->PT->in_insert == 1) {

            $js_manuale.="
            keyActions [$contatore_key] = {character:  78, // N - Nuovo record
                              actionType: \"code\", 
                              param:      \"nuovo_record()\",
                              mod:        \"ALT\"   };
                ";

            $contatore_key++;


            if ($this->PT->in_duplica == 1) {

                $js_manuale.="
                keyActions [$contatore_key] = {character:  68, // D - Duplica il record
                                  actionType: \"code\", 
                                  param:      \"$('popup-duplica').style.display='';\",
                          mod:        \"ALT\"   };
                    ";

                $contatore_key++;
            }
        }


        if ($this->PT->in_update == 1) {

            $js_manuale.="
            keyActions [$contatore_key] = {character:  77, // M - Modifica
                                  actionType: \"code\", 
                                  param:      \"if(!modifiche_attive && !nuovoRecord){modifica();}\",
                                  mod:        \"ALT\"   };
                                  ";
            $contatore_key++;
        }


        if ($this->PT->in_update == 1 || $this->PT->in_insert == 1) {

            $js_manuale.="
            keyActions [$contatore_key] = {character:  83, // S - Salva
                              actionType: \"code\", 
                              param:      \"if(modifiche_attive){salva()}\",
                              mod:        \"ALT\"   }; 
            ";

            $contatore_key++;
        }

        if ($this->PT->in_delete == 1) {

            $js_manuale.="
            keyActions [$contatore_key] = {character:  69, // E - Elimina
                              actionType: \"code\", 
                              param:      \"elimina()\",
                              mod:        \"ALT\"   };
            ";
            $contatore_key++;
        }

        if ($permetti_link) {

            $js_manuale.="
            keyActions [$contatore_key] = {character:  76, // L - Apri i link
                              actionType: \"code\", 
                              param:      \"openWindow('add.link.php?t='+VF.tabella_alias+'&id='+VF.localIDRecord,'Link',80);\",
                              mod:        \"ALT\"   };
            ";

            $contatore_key++;
        }

        if ($permetti_allegati) {

            $js_manuale.="
            keyActions [$contatore_key] = {character:  71, // G - Apri gli allegati
                              actionType: \"code\", 
                              param:      \"openWindow('add.attach.php?t='+VF.tabella_alias+'&id='+VF.localIDRecord,'Allegati',70);\",
                              mod:        \"ALT\"   };
            ";

            $contatore_key++;
        }

        return array($js_manuale, $contatore_key);
    }

    
    /**
     * Js counter
     */
    public function get_counter(){
      
        global $vmsql;
        
        // SE c'è l'id in GET prendi calcola a che punto dell'elenco si è arrivati
        if(isset($_GET['id']) && $_GET['id']!=0){

            // PK multiple
            if(strpos($_GET['id'], ",")!==false){

                $tk_id=explode(",",$_GET['id']);

                if(count($this->Reg->PK)==count($tk_id)){

                    $where_multi='';

                    for($k=0;$k<count($this->info_pk);$i++){

                        $where_multi.="AND ".$this->info_pk[$k]."='".$vmsql->escape($tk_id[$k])."'";
                    }

                    $sql_calcola_sub = "SELECT ".$this->PT->orderby."
                                    FROM ".$this->PT->table_name."
                                    WHERE 1=1
                                    $where_multi";
                }
                else{
                    $sql_calcola_sub='NULL';
                }
            }

            // PK single
            else{

                $sql_calcola_sub = "SELECT ".$this->PT->orderby."
                                    FROM ".$this->PT->table_name."
                                    WHERE ".$this->info_pk[0]."='".$vmsql->escape($_GET['id'])."'";
            }

                $sql_calcola = "SELECT count(*) FROM ".$this->PT->table_name."
                                    WHERE ".$this->PT->orderby."<($sql_calcola_sub)";

                $query_calcola = $vmsql->query($sql_calcola);

                list($counter)=$vmsql->fetch_row($query_calcola);
        }
        else if(isset($_GET['counter']) && intval($_GET['counter'])>0){

            // Contatore attuale:
            $counter=intval($_GET['counter']);
        }
        else{
            $counter=0;
        }
        
        return $counter;
    }
    
    public function field_iterator(){
        
        global $db1;
        
        $riga_aperta=false;
        
        $FORM_0 = '';
        
        foreach($this->Reg->get_column_schedaview() as $i=>$C){
        
            // variabili da impostare dentro il ciclo.  

            $label=true;
            $riga_singola=false;
            $riga_singola_override=($C->in_line=='') ? false:true;
            $href_nuovo_record="";

            if($_SESSION['VF_VARS']['js_test']){
                
                if(!empty($C->jstest)) $this->rules[]=trim($C->jstest);
            }

            // SE IMPOSTATO, sovrascrivo il tipo sovraimposto a quello di default

            $C->data_type = ($C->in_tipo=='' || $C->in_tipo==null) ? $C->data_type : $C->in_tipo;


            // Impostazioni del campo

            // INT  ----------------------------------------------------------------------------------------------------------------

            if(in_array(strtolower($C->data_type), array('numeric','int','tinyint', 'mediumint', 'float', 'double'))){

                $input = Scheda_View::type_int($C->column_name);
            }


            // VARCHAR ---------------------------------------------------------
            else if(in_array(strtolower($C->data_type), array('varchar','char'))){
            
                $riga_singola=true;
                $input=Scheda_View::type_char($C->column_name, $C->character_maximum_length, $C->in_suggest, $this->table_name(), $C->in_line);
            }


            // TEXTAREA --------------------------------------------------------

            else if(in_array($C->data_type, array('text', 'mediumtext', 'longtext'))){

                $riga_singola=true;
                $input = Scheda_View::type_text($C->column_name, $C->in_line);
            }


            // BOOL ------------------------------------------------------------

            else if($C->data_type=='bool'){
                
                $riga_singola=false;
                $input = Scheda_View::type_bool($C->column_name, $db1['dbtype']);
            }

            // PASSWORD --------------------------------------------------------
            else if($C->data_type=='password'){

                $riga_singola=true;
                // se c'è almeno una codifica MD5 carica il file JS
                if($C->in_default=='md5') $this->carica_md5=true;
                if($C->in_default=='sha1') $this->carica_sha1=true;
                
                $input = Scheda_View::type_password($C->column_name, $C->in_default);
            }


            // Tipo richtext: FCKEDITOR ----------------------------------------
            else if($C->data_type=='richtext'){

                $riga_singola=true;
                $input = Scheda_View::type_richtext($C->column_name);
                $this->CKEditors[]=$C->column_name;
            }

            // tipo speciale hidden   ------------------------------------------
            else if($C->data_type=='hidden'){

                $label=false;
                $input = Scheda_View::type_hidden($C->column_name, $C->in_default, $C->extra);
            }


            // tipo speciale SELECT --------------------------------------------
            else if($C->data_type=='select' || $C->data_type=='select_enum' ){
                
                $input = Scheda_View::type_select($C->column_name, $C->in_default);
            }

            // tipo speciale SELECT FROM ---------------------------------------
            else if($C->data_type=='select_from'){

                $riga_singola=true;
                $input = Scheda_View::type_selectfrom($C->column_name, $C->in_default, $this->table_name());
                $this->fields_select_from[]="dati_".$C->column_name;
                $this->n_tendine_attese++;
            }

            // AUTOCOMPLETER_FROM ----------------------------------------------
            else if($C->data_type=='autocompleter_from'){

                $input = Scheda_View::type_autocomplete_from($C->column_name, $C->id_reg);
                $this->fields_autocompleter_from[]="dati_".$C->column_name;
            }

            // DATE ------------------------------------------------------------
            else if($C->data_type=='date'){

                $input = Scheda_View::type_date($C->column_name, $this->load_calendar);
            }


            // DATAORA ---------------------------------------------------------
            else if($C->data_type=='datetime' || $C->data_type=='timestamp'){

                $input = Scheda_View::type_datetime($C->column_name, $this->load_calendar, $i);
            }

            // Caso sola lettura (ONLYREAD)
            elseif($C->data_type=='onlyread'){
                $input = Scheda_View::type_onlyread($C->column_name);
            }

            // Caso sola lettura (ONLYREAD MULTI)
            elseif($C->data_type=='onlyread-multi'){
                $input = Scheda_View::type_onlyread_multi($C->column_name, $C->in_line);
            }

            // Caso geometry
            elseif($C->data_type=='geometry'){
                $input = Scheda_View_Geom::type_geom($C->column_name, $C->in_line);
            }

            // Caso sconosciuto  (default) -------------------------------------------------------------------------------
            else {
                $label=true;
                $input = Scheda_View::type_unknow($C->column_name);
            }
            


            // Impostazioni di campi obbligatori
            if($C->is_nullable=="NO" || $C->in_richiesto=="1"){
                $obbligatorio = "<span class=\"red\">*</span>";
                $this->campi_req[]=$C->column_name;
            }
            else{
                $obbligatorio="";
            }


            // Se è tendina dinamica metti un span di feedback
            
            
            // Filter by field
            if(isset($this->PT->allow_filters) && 
                    $this->PT->allow_filters == 1){
                
                $desaturate = (isset($_GET['w']) && isset($_GET['w'][$C->column_name])) ? '':'desaturate';
                
                $filter="<span class=\"filter_by_field\" data-k=\"".$C->column_name."\">"
                        ."<img class=\"$desaturate\" src=\"img/filter_add_16x16.gif\" title=\""._('Filter by this field/value')."\" alt=\"filter\" width=\"10\" height=\"10\" />"
                        ."</span>";
            }
            else{
                $filter='';
            }

            $span_feed = ($C->data_type=='select_from') 
                ? " <span id=\"feed_".$C->column_name."\" class=\"feed-tendina\">"
                  ."<img src=\"img/refresh1.gif\" alt=\"caricamento\" /> "._("Loading...")."</span>" : "";

            $showed_name= (trim($C->alias_frontend)=='') ? 
                    $C->column_name 
                    : htmlentities(trim($C->alias_frontend),ENT_QUOTES, FRONT_ENCODING);

            $str_label = ($label) ? "<label for=\"dati_".$C->column_name."\" title=\""
                    .htmlentities($C->commento,ENT_QUOTES, FRONT_ENCODING)."\">"
                    .$showed_name . $obbligatorio . $span_feed . $href_nuovo_record . $filter. "</label>" : "";
            

            if($riga_singola_override){

                if($C->in_line==0){

                    $FORM_0.="
                        <div class=\"row-s\">
                        {$str_label}{$input}
                        </div>
                        ";

                    $riga_aperta=false;
                }
                else{


                    if($riga_aperta){

                        $FORM_0.="
                        <div class=\"row-d2\">
                            {$str_label}{$input}
                        </div><br class=\"sep\" />
                        ";
                        $riga_aperta=false;
                    }
                    else{

                        $FORM_0.="
                        <div class=\"row-d1\">
                            {$str_label}{$input}
                        </div>
                        ";

                        $riga_aperta=true;
                    }

                }

            }
            else{

                if($riga_singola){

                    $FORM_0.="
                        <div class=\"row-s\">
                        {$str_label}{$input}
                        </div>
                        ";

                    $riga_aperta=false;
                }
                else{

                    if($riga_aperta){

                        $FORM_0.="
                        <div class=\"row-d2\">
                            {$str_label}{$input}
                        </div><br class=\"sep\" />
                        ";
                        $riga_aperta=false;
                    }
                    else{

                        $FORM_0.="
                        <div class=\"row-d1\">
                            {$str_label}{$input}
                        </div>
                        ";

                        $riga_aperta=true;
                    }
                }
            }


        } // -- fine ciclo sui campi
        
        return $FORM_0;
    }
    
    

    /**
     * Impostazioni per xgrid
     */
    public function xgrid_settings(){
        
        $maxlen = array();
        $xg_campi='';
        $xg_misure='';
        $xg_tipo='';
        $xg_sort='';
        $xg_align='';
        $xg_alias='';
        
        $tfields = $this->Reg->get_column_tableview();
        
        $FType= new FieldType();
        
        for($i=0;$i<count($tfields);$i++){
            
            if($tfields[$i]->in_table != 1) continue;

            $lentxt=Scheda::campo_len($tfields[$i]->column_name, $tfields[$i]->data_type);

            // replacement of comma, prevention of error
            $xg_alias.= ($tfields[$i]->alias_frontend == '' ) 
                    ? str_replace(",", '', $tfields[$i]->column_name) 
                    : str_replace(",", '', $tfields[$i]->alias_frontend);
            $xg_alias.=", ";
            
            // replacement of comma, prevention of error
            $xg_campi.= str_replace(",",'',$tfields[$i]->column_name) . ", ";

            // Impostazioni lunghezza campi
            if(!isset($maxlen[$tfields[$i]->column_name]) || $maxlen[$tfields[$i]->column_name]<$lentxt) {

                if( in_array($tfields[$i]->data_type, array('varchar' , 'varchar2', 'text', 'mediumtext', 'select_from'))
                     && $lentxt<200){
                    $maxlen[$tfields[$i]->column_name]= 160;
                }
                else{
                    $maxlen[$tfields[$i]->column_name]= ($lentxt+20);
                }
            }

            $xg_misure.=$maxlen[$tfields[$i]->column_name].",";
            $xg_tipo.="ro,";
            $xg_sort.=($FType->is_numeric($tfields[$i]->data_type) && $tfields[$i]->in_tipo!='select_from') ? "int,":"str,";
            $xg_align.=($FType->is_numeric($tfields[$i]->data_type) && $tfields[$i]->in_tipo!='select_from') ? "right,":"left,";
        }

        // tolgo la virgola dai campi xgrid
        $xg['campi'] = substr($xg_campi,0,-2);
        $xg['alias'] = substr($xg_alias,0,-2);
        $xg['misure']= substr($xg_misure,0,-1);
        $xg['tipo']= substr($xg_tipo,0,-1);
        $xg['sort']= substr($xg_sort,0,-1);
        $xg['align']= substr($xg_align,0,-1);
        $xg['maxlen'] = $maxlen;
        
        return $xg;

    }
    
    
    public function action_buttons(){
        
        $buttons='';

        // BUTTON SEARCH RESULTS
        $buttons.= "<div id=\"buttons_on_research\" style=\"display:none\">\n";

        $buttons.= '<input title="'._('Reset search').'" type="button" id="p_annulla2" name="annulla" value=" '._('Reset search').' " onclick="exit_table_search();"  />'."\n";

        if ($this->PT->in_export==1) {   
            $buttons.= "<input accesskey=\"x\" type=\"button\" id=\"p_export2\" name=\"p_export\" value=\" "._("Export results")." \""
            ." onclick=\"openWindow('admin/export_data.php?t='+VF.tabella+'&amp;qr='+VF.tabella,'esportazione_dati',65);\" />"; 
        } 

        $buttons.= " "._('Double click on a row to open records');

        $buttons.= "</div>\n\n";


        $buttons.= "<div id=\"scheda1\">
            <div id=\"pulsanti-azioni\">
                ";


        if ($this->PT->in_insert == 1) {
            $buttons.= '<input title="' . _('New record') . '" type="button" id="p_insert" name="insert" value=" ' . _('New') . ' " onclick="nuovo_record();" accesskey="n" />' . "\n";
        }
        else
            $buttons.= "<input type=\"hidden\" id=\"p_insert\" />\n";

        if ($this->PT->in_update == 1) {
            $buttons.= '<input title="' . _('Update record') . '" type="button" id="p_update" name="update" value=" ' . _('Modify') . ' " onclick="modifica();" accesskey="m" />' . "\n";
        } 
        else
            $buttons.= "<input type=\"hidden\" id=\"p_update\" />\n";

        // if($this->PT->in_insert==1 || $this->PT->in_update){
        $buttons.= '<input title="' . _('Cancel') . '" type="button" id="p_annulla" name="annulla" value=" ' . _('Cancel') . ' " onclick="annulla();" accesskey="a" />' . "\n";
        // } else $buttons.= "<input type=\"hidden\" id=\"p_annulla\" />\n";

        if ($this->PT->in_insert == 1 || $this->PT->in_update) {
            $buttons.= '<input title="' . _('Save record') . '" type="button" id="p_save" name="save" value=" ' . _('Save') . ' " onclick="salva();" accesskey="s" />' . "\n";
        } 
        else
            $buttons.= "<input type=\"hidden\" id=\"p_save\" />\n";

        if ($this->PT->in_delete == 1) {
            $buttons.= '<input title="' . _('Delete record') . '" type="button" id="p_delete" name="delete" value=" ' . _('Delete') . ' " onclick="elimina();" accesskey="e" />' . "\n";
        } 
        else
            $buttons.= "<input type=\"hidden\" id=\"p_delete\" />\n";

        if ($this->PT->in_insert == 1 && $this->PT->in_duplica == 1) {
            $buttons.= '<input title="' . _('Duplicate record') . '" type="button" id="p_duplica" name="duplica" value="' . _('Duplicate') . '" />' . "\n";
        } 
        else
            $buttons.= "<input type=\"hidden\" id=\"p_duplica\" />\n";

        // Pulsante ricerca
        $buttons.= '<input title="' . _('Search mode') . '" type="button" id="p_cerca" name="cerca" value=" ' . _(' Search ') . ' " onclick="cerca();" accesskey="r" />' . "\n";

        $buttons.= "\t</div>\n";

        return $buttons;

    }
    
    
    public function print_attach_and_links(){
        
        $allegati_tab = ($this->PT->permetti_allegati=='1') ? 1:0;
        $link_tab     = ($this->PT->permetti_link=='1') ? 1:0;

        if($allegati_tab || $link_tab){

            $DIV_ALLEGATI_LINK = "\t<div id=\"allegati-link\">\n";

            if($allegati_tab) $DIV_ALLEGATI_LINK .= "<a href=\"javascript:;\" onclick=\"openWindow('add.attach.php?t='+VF.tabella_alias+'&amp;id='+VF.localIDRecord,'Allegati',70);\" id=\"href_tab_allegati\">"._("attachments")." (0)</a><br />";
            if($link_tab) $DIV_ALLEGATI_LINK .= "<a href=\"javascript:;\" onclick=\"openWindow('add.link.php?t='+VF.tabella_alias+'&amp;id='+VF.localIDRecord,'Link',80);\"  id=\"href_tab_link\">"._("link")." (0)</a><br />";

            $DIV_ALLEGATI_LINK .= "\t</div>\n";

            return $DIV_ALLEGATI_LINK;
        }
        else{
            return '';
        }
    }
    
    
    
    public function print_hotkeys_pop(){
        
        $scorciatoie = (file_exists("img/scorciatoie_".substr(FRONT_LANG,0,2).".gif")) ? substr(FRONT_LANG,0,2) : 'en';

        $html= "<div id=\"popup-hotkeys\"><img src=\"img/scorciatoie_{$scorciatoie}.gif\" alt=\""._('Keyboard shortcuts')."\" "
            ." width=\"24\" height=\"152\" onclick=\"mostra_nascondi('box-scorciatoie');\" />
         </div>\n";
        
        $html.= "
            <div id=\"box-scorciatoie\" 
                 style=\"display:none;\">
                <div class=\"chiudi-box\"><span class=\"fakelink\" onclick=\"mostra_nascondi('box-scorciatoie');\">"._('Close')." [X]</span></div>
                 <p><strong><em>"._('Browse records')."</em></strong></p>
                <dl>
                    <dt>"._('CTRL + right arrow')."</dt>
                    <dd>"._('Go forward one record')."</dd>
                    
                    <dt>"._('CTRL + left arrow')."</dt>
                    <dd>"._('Go back one record')."</dd>
                    
                    <dt>"._('CTRL + SHIFT + right arrow')."</dt>
                    <dd>".sprintf(_('Go forward %s records'),$_SESSION['VF_VARS']['passo_avanzamento_veloce'])."</dd>
                    
                    <dt>"._('CRTL + SHIFT + left arrow')."</dt>
                    <dd>".sprintf(_('Go back %s records'),$_SESSION['VF_VARS']['passo_avanzamento_veloce'])."</dd>
                    
                    <dt>"._('CRTL + down arrow')."</dt>
                    <dd>"._("Last record")."</dd>
                    
                    <dt>"._('CRTL + up arrow')."</dt>
                    <dd>"._('First record')."</dd>
                </dl>
                <hr />
                <p><strong><em>"._('Actions')."</em></strong></p>
                <dl>
                    <dt>"._('CTRL + ALT + N')."</dt>
                    <dd>"._('New record')."</dd>
                    
                    <dt>"._('CTRL + ALT + M')."</dt>
                    <dd>"._('Modify')."</dd>
                    
                    <dt>"._('CTRL + ALT + S')."</dt>
                    <dd>"._('Save')."</dd>
                    
                    <dt>"._('CTRL + ALT + A')."</dt>
                    <dd>"._('Cancel')."</dd>
                    
                    <dt>"._('CTRL + ALT + E')."</dt>
                    <dd>"._('Delete record')."</dd>
                    
                    <dt>"._('CTRL + ALT + D')."</dt>
                    <dd>"._('Duplicate record')."</dd>
                    
                    <dt>"._('CTRL + ALT + R')."</dt>
                    <dd>"._('Search')."</dd>
                    
                    <dt>"._('CTRL + ENTER (in search mode)')."</dt>
                    <dd>"._('Start search')."</dd>
                    
                </dl>
                <hr />
                <p><strong><em>"._('Attachments and link')."</em></strong></p>
                <dl>
                    <dt>"._('CTRL + ALT + G')."</dt>
                    <dd>"._('Open attachments (if present in form)')."</dd>
                    
                    <dt>"._('CTRL + ALT + L')."</dt>
                    <dd>"._('Open link (if present in form)')."</dd>
                </dl>
                
            </div>
            ";
        
        return $html;
    }
}