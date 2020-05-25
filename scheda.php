<?php
/**
* Generatore della maschera per tabelle e viste.
* Questo file rappresenta il cuore dell'applicazione VFront: attraverso questo file vengono 
* generate le maschere per utilizzare il database, mediante permessi e regole definite nei registri 
* di VFront.
* E' un file complesso che genera HTML e codice dinamico Javascript.
* Si appoggia a numerose librerie php e Javascript: il file principale che viene utilizzato
* a lato client è ./js/scheda.js
* 
* @package VFront
* @author Mario Marcello Verona <marcelloverona@gmail.com>
* @copyright 2007-2010 M.Marcello Verona
* @version 0.96 $Id: scheda.php 1169 2017-05-12 18:02:46Z marciuz $
* @license http://www.gnu.org/licenses/gpl.html GNU Public License
*/

require_once("./inc/conn.php");
require_once("./inc/layouts.php");
require_once("./inc/func.browser_detection.php");

proteggi(1);


$info_browser=browser_detection('full');
if(!is_array($info_browser)) $info_browser=array();

$htmltable_type = 'dhtmlxgrid';
$skins=array(
            'terrace',
            'web',
            'skyblue',
            'vfront',
        );

$selected_skin = $skins[3];


#####################################################################
#
#   PRENDI LE IMPOSTAZIONI DA FRONTEND
#
#

$oid = (int) $_GET['oid'];
if($oid == 0){
    openErrorGenerico('Ups! The table you\'re looking for doesn\'t exists!', false);
    exit;
}
$gid = $_SESSION['gid'];
$Scheda = new Scheda($oid);
// $Scheda->get_fields_info();
$nome_tab = $Scheda->table_name();
$RPC = new Rpc($nome_tab);

$RPC->set_default_where();

if (isset($_GET['w'])){
    $RPC->set_where($_GET['w']);
}

$counter = $Scheda->get_counter();

$Scheda->set_max($RPC);

#
#
############################################################################




$FORM='';


// filters -------------------

$_WHERE=$RPC->get_where();
$_WHERE_DEFAULT=$RPC->get_where_default();

if(isset($_WHERE_DEFAULT) && count($_WHERE_DEFAULT)>0){
    $FORM.= "<div id=\"filter-admin\">\n";
    $FORM.=_('Please note: The records shown are pre-filtered by the administrator');
    $FORM.= "</div>\n";
}

if(isset($_WHERE) && count($_WHERE)>0){

    $FORM.= "<div id=\"filter\">\n";

    $FORM.=_('Records filtered by');
    $FORM.=" - <span class=\"fakelink cancel_all_filter\">"._('Remove all filters')."</span>";

    foreach($_WHERE as $ffield => $fvalue){

    $FORM.= "
    <div class=\"filter_criteria\" id=\"filter-$ffield\">
        <div class=\"filter-label\">$ffield:</div>
        <div class=\"filter-value\">$fvalue:</div>
        <div class=\"filter-remove\"><img class=\"cancel_filter\" src=\"img/cancel.png\" rel=\"$ffield\" alt=\"cancel\" /></div>
    </div>\n";
    }

    $FORM.= "</div>\n";

}

$FORM.= "<form action=\"" . Common::phpself() . "\" method=\"post\" id=\"singleform\" name=\"singleform\">\n";

#########################################
#
#   Ciclo sui CAMPI

$FORM_0 = $Scheda->field_iterator();


# Hidden per le chiavi primarie
for($k=0;$k<count($Scheda->info_pk);$k++){

    $FORM_0.="\t\t<input type=\"hidden\" name=\"pk[".$Scheda->info_pk[$k]."]\" id=\"pk_".$Scheda->info_pk[$k]."\" value=\"\" class=\"pkname\" data-pkname=\"".$Scheda->info_pk[$k]."\" />\n";

}

$FORM.="<div>$FORM_0</div>\n";

$FORM.="</form>";


    // GET SUBMASKS
    $submasks = RegTools::prendi_sottomaschere($oid,false,true);

    // GET BUTTONS
    $buttons = RegTools::prendi_pulsanti($oid);

    // SHADOWBOX TEST
    $shadowbox_active= true; 


#############################################################################################################
#
#   INIZIA L'APERTURA DEL LAYOUT
#
#############################################################################################################


    $files=array(
        "js/scriptaculous/lib/prototype.js",
        "js/scriptaculous/src/scriptaculous.js?load=effects,controls",
        "sty/scheda.css"
     );

    if($htmltable_type == 'dhtmlxgrid'){

        $files[]= "js/dhtmlxgrid4/skins/{$selected_skin}/dhtmlxgrid.css";
        $files[]= "js/dhtmlxgrid4/codebase/dhtmlxgrid.js";
    }

//      $files[]="js/tinydhtmlHistory.js";
    $files[]="js/rsh.compressed.js";
    $files[]="js/mostra_nascondi_id.js";

    if($shadowbox_active){      
        $files[]="js/shadowbox/shadowbox.js";
        $files[]="js/shadowbox/shadowbox.css";
    }




    // SE ci sono campi data , datetime o timestamp, prendi il calendario
    if($Scheda->load_calendar){
        $files[]="js/jscalendar/calendar.js";
        $files[]="js/jscalendar/lang/calendar-".substr(FRONT_LANG,0,2).".js";
        $files[]="js/jscalendar/calendar-setup.js";
        $files[]="sty/jscalendar/calendar-win2k-cold-1.css";
    }

    // Leaflet
    $files[]="plugins/leaflet/leaflet.js";
    $files[]="plugins/leaflet/leaflet.css";

    if($Scheda->carica_md5){

        $files[]="js/md5.js";
    }

    if($Scheda->carica_sha1){

        $files[]="js/sha1.js";
    }


    if(count($Scheda->rules)>0){

        $files[]="js/yav/yav-config-it.js";
        $files[]="js/yav/yav.js";
    }

    //$files[]='js/jquery/jquery.min.js';
    $files[]='js/jquery/jquery.query.js';

    if($htmltable_type == 'datatables'){

        $files[] = "js/datatables/css/jquery.dataTables.css";
        $files[] = "js/datatables/jquery.dataTables.js";
    }


    $files[]='js/scheda.js';

    if(file_exists(FRONT_ROOT."/usr/personal_settings.css")){
        $files[]='usr/personal_settings.css';
    }


    $LAYOUT = openLayout1(_("Form")." ".$nome_tab,$files);

    if($_SESSION['VF_VARS']['shortcut_tastiera_attivi']==1)
        $LAYOUT = str_replace("<body>","<body onload=\"\" onkeydown=\"hotKeys(event);\">",$LAYOUT);
    else 
        $LAYOUT = str_replace("<body>","<body onload=\"\">",$LAYOUT);

    $usaHistory = (isset($_SESSION['VF_VARS']['usa_history']) 
                && $_SESSION['VF_VARS']['usa_history']==1 
                && $info_browser[8]!='mobile') ? "true":"false";


    $alias_tabella = ($Scheda->PT->table_type=='VIEW' && isset($Scheda->PT->fonte_al) && $Scheda->PT->fonte_al!='')
                    ? $Scheda->PT->fonte_al : $Scheda->PT->table_name;

    if(!isset($_GET['parent_field'])) $_GET['parent_field']='';
    if(!isset($_GET['parent_table'])) $_GET['parent_table']='';




    $lista_submask=array();
    $alias_submask=array();
    $lista_embed=array();
    $alias_embed=array();

    $array_fk_parent=array();
    $array_id_submask=array();
    $div_embeds=array();
    $submasks_not_embedded=array();

    for($i=0;$i<count($submasks);$i++){

            $nome_front= (trim($submasks[$i]['nome_frontend'])!="") ? $submasks[$i]['nome_frontend']:$submasks[$i]['nome_tabella'];
            $array_fk_parent[$submasks[$i]['id_submask']]=$submasks[$i]['campo_pk_parent'];

            if($submasks[$i]['tipo_vista']=='embed'){

                $lista_embed[]=$submasks[$i]['id_submask'];
                $alias_embed[]=$nome_front;

                $div_embeds[]="<div class=\"sm_embed\" id=\"sm_embed_".$submasks[$i]['id_submask']."\"></div>\n";
            }
            else{

                $lista_submask[]= $submasks[$i]['nome_tabella'] ;
                $alias_submask[]= $nome_front ;
                $array_id_submask[]=$submasks[$i]['id_submask'];

                $submasks_not_embedded[]=$submasks[$i];
            }

    }



    $pathRelativo = Common::dir_name();

    $jstest_js = ($_SESSION['VF_VARS']['js_test']==1 && count($Scheda->rules)>0) ? 1:0;

    $permetti_link = ( Common::is_true($Scheda->PT->permetti_link)) ? 1:0;

    $permetti_allegati = ( Common::is_true($Scheda->PT->permetti_allegati)) ? 1:0;

    // identifico il db per checkbox ed altro
    $PGdb= ($db1['dbtype']=='postgres') ? "true":"false";

    $GET_QS = (isset($_GET['qs'])) ? $_GET['qs']:'';

    $outputType = (isset($USE_JSON) && $USE_JSON===false) ? 'XML' : 'JSON';

    $xg = $Scheda->xgrid_settings();
    $paginazione = (isset($_SESSION['VF_VARS']['n_record_tabella'])) ? $_SESSION['VF_VARS']['n_record_tabella']:20;

    // VFRONT JS vars
    $VFVars =array(
       'counter'=>$counter,
        'max' => $Scheda->MAX,
        'modifiche_attive'=>false,
        'ricerca'=>false,
        'campi_mod'=>array(),
        'tipo_salva'=>false,
        'passoVeloce' => (int) $_SESSION['VF_VARS']['passo_avanzamento_veloce'], 
        'campiReq'=> $Scheda->campi_req ,
        'campiAutocompleterFrom' => $Scheda->fields_autocompleter_from,
        'record_bloccato' => false,
        'tabella' => $nome_tab,
        'tabella_alias' => $alias_tabella,
        'idRecord'=>0,
        'localIDRecord'=>0,
        'focusScheda'=>true,
        'initGrid'=>false,
        'nuovoRecord'=>false,
        'modificaRecord'=>false,
        'usaHistory'=> (bool) $usaHistory,
        'parentField' => preg_replace("'[\W]'",'',$_GET['parent_field']),
        'parentTable' => preg_replace("'[\W]'",'',$_GET['parent_table']),
        'permettiLink' => $permetti_link,
        'permettiAllegati' => $permetti_allegati,
        'nTendine'=> 0,
        'tendineAttese'=>$Scheda->n_tendine_attese,
        'initScheda'=>false,
        'basePath'=>$pathRelativo,
        'pathRelativo' => $pathRelativo."/rpc",
        'qr_search' => '',
        'sottomaschere'=> $lista_submask,
        'sottomaschere_alias' => $alias_submask,
        'sm_embed' =>  $lista_embed,
        'sm_alias_embed' => $alias_embed,
        'fkparent'=> $array_fk_parent,
        'jstest'=> (bool) $jstest_js,
        'PGdb'=> (int) $PGdb,
        'dateEncode'=> Scheda_View::get_date_format(),
        'GETqs' => $GET_QS,
        'outputType'=> $outputType,
        'fck_attivo' => (count($Scheda->CKEditors)>0) ? true: false,
        'fck_vars' => $Scheda->CKEditors,
        'fck_pronti' => 0,
        'oFCK' => array(),
        'htmltable'=>$htmltable_type,
        'xg_campi'=>$xg['campi'],
        'xg_alias'=>$xg['alias'],
        'xg_misure'=>$xg['misure'],
        'xg_align' => $xg['align'],
        'xg_tipo' => $xg['tipo'],
        'xg_sort' => $xg['sort'],
        'xg_pages' => $paginazione,
        'gid'=>$gid,
        'skin' => $selected_skin,
        'autoload_geom' => false,
        'geom_field' => '',
    );


    $js_manuale = "
    <script type=\"text/javascript\">

    // <!-- 

        var \$j=jQuery.noConflict();
        var VF = ".json_encode($VFVars).";
        var initGrid = false;
        var haveParent = (window.opener==null) ? false:true;
        ";  

    if($shadowbox_active){

        $js_manuale.="Shadowbox.init();\n";
    }


    if($Scheda->load_calendar){

        $js_manuale.="

         function caldis(cal){
            return (VF.nuovoRecord || VF.modificaRecord || VF.ricerca) ? false:true;
         }

         function catcalc(cal) {

            if(VF.nuovoRecord || VF.modificaRecord || VF.ricerca){
                mod(cal.params.inputField.id);
            }
        }
        ";
    }


    if($_SESSION['VF_VARS']['js_test']){

        $js_manuale.="\n\t\t var rules=new Array();\n";

        for($i=0;$i<count($Scheda->rules);$i++)

            $js_manuale.="\t\trules[$i]='".addslashes($Scheda->rules[$i])."';\n";

    }


// Scorciatoie da tastiera, condizionate dalle variabili
if($_SESSION['VF_VARS']['shortcut_tastiera_attivi']==1){

     $shortcuts_pre = $Scheda->print_shortcuts($permetti_link, $permetti_allegati);
     $js_manuale.=$shortcuts_pre[0];
 }

    $js_manuale.="

    // -->
    </script>
    ";

    $LAYOUT = str_replace("</head>",$js_manuale."</head>",$LAYOUT);

    echo $LAYOUT;

?>
<!--[if lt IE 8]>
<style type="text/css">

#singleform input, #singleform textarea{
    margin-left:-12px;
}

#loader-scheda{
    padding:0px 24px 12px 0px;
    height:2400px;
}


</style>

<![endif]-->

<div id="loader-scheda0">
        <div id="loader-scheda"></div>

        <div id="pop-loader-contenitore" align="center">
            <div id="pop-loader-scheda" >
                <?php echo _("Loading...");?><br /><br />
                <img src="img/refresh1.gif" alt="Loading" height="25" width="25" />
            </div>
        </div>
</div>

<div id="feedback">
    <span id="risposta"></span>
</div>

<?php 

    echo breadcrumbs(array("HOME",_("Form table")." ". $nome_tab));


    $classe_h1 = ($Scheda->PT->table_type=='VIEW')? "verde":"var";

    $show_comment= (isset($_SESSION['VF_VARS']['show_comment_in_table']) 
                    && $_SESSION['VF_VARS']['show_comment_in_table']=='1'
                    && trim($Scheda->PT->commento)!='')
                    ?
                    "<div class=\"comment-scheda\">".$Scheda->PT->commento."</div>\n"
                    : '';


    $table_name_sh = ($Scheda->PT->table_alias=='') ? $nome_tab : $Scheda->PT->table_alias;

    echo "<h1>". _('Table')." <span class=\"$classe_h1\">".$table_name_sh."</span>$show_comment</h1>\n";


    echo "<div id=\"counter_container\"><span id=\"numeri\"></span>&nbsp;&nbsp;<span id=\"refresh\">&nbsp;</span></div>\n";



    // PULSANTI NAV
    echo "
<div id=\"pulsanti\">

    <input title=\"". _("first record")."\" type=\"button\" id=\"p_primo\" name=\"p_primo\" value=\"   |&lt;   \" onclick=\"sndReq('".$nome_tab."','min',true);reloadGrid();\" accesskey=\"7\" />
    <input title=\"".sprintf(strtolower(_("Go back %s records")),$_SESSION['VF_VARS']['passo_avanzamento_veloce'])."\" type=\"button\" id=\"p_prev10\" name=\"p_indietro10\" value=\"   &lt;&lt;   \" onclick=\"sndReq('".$nome_tab."','prev10',true);reloadGrid();\" accesskey=\"1\" />
    <input title=\"". _("previous")."\" type=\"button\" id=\"p_prev\" name=\"p_indietro\" value=\"   &lt;   \" onclick=\"sndReq('".$nome_tab."','prev',true);\" accesskey=\"4\" />
    <input title=\"". _("next")."\" type=\"button\" id=\"p_next\" name=\"p_avanti\" value=\"   &gt;   \" onclick=\"sndReq('".$nome_tab."','next',true);\" accesskey=\"6\"/>
    <input title=\"". sprintf(strtolower(_("Go forward %s records")),$_SESSION['VF_VARS']['passo_avanzamento_veloce'])."\"  type=\"button\" id=\"p_next10\" name=\"p_avanti10\" value=\"   &gt;&gt;   \" onclick=\"sndReq('".$nome_tab ."','next10',true);reloadGrid();\" accesskey=\"3\"  />
    <input title=\"". _("last record")."\" type=\"button\" id=\"p_ultimo\" name=\"p_ultimo\" value=\"   &gt;|   \" onclick=\"sndReq('".$nome_tab."','max',true);reloadGrid();\" accesskey=\"9\" />
    ";
    // <!--<input accesskey=\"k\" type=\"button\" id=\"p_TEST\" name=\"p_TEST\" value=\" TEST XML \" onclick=\"debug_xml();\" />-->

    if ($Scheda->PT->in_export==1) {  
        echo "<input accesskey=\"x\" type=\"button\" id=\"p_export\" name=\"p_export\" value=\" ". _("Export data") ." \" onclick=\"openWindow('admin/export_data.php?idt=". base64_encode($oid._BASE64_PASSFRASE) ."','esportazione_dati',65);\" />\n";
    } 

    if ($Scheda->PT->in_import==1) { 
        echo "<input type=\"button\" id=\"p_import\" name=\"p_import\" value=\" "._("Import data")." \" onclick=\"openWindow('import.php?oid=". base64_encode($oid._BASE64_PASSFRASE) ."','importazione_dati',90);\" />\n";
  }
    //<!--<input type=\"button\" id=\"p_TEST2\" name=\"p_TEST2\" value=\" DEBUG Var \" onclick=\"debug_var();\" />-->

    echo "</div>\n";



    echo $Scheda->action_buttons();







    // BUTTON SUBMASK & CUSTOM BUTTONS

        if(count($submasks_not_embedded)>0 || count($buttons)>0){

            echo "\t<div id=\"pulsanti-submask\">\n";

            // SUBMASK
            for($i=0;$i<count($submasks_not_embedded);$i++){

                echo $Scheda->pulsante_sottomaschera($submasks_not_embedded[$i]);
            }

            // CUSTOM BUTTONS
            for($i=0;$i<count($buttons);$i++){

                echo $Scheda->pulsante_custom($buttons[$i]);
            }

            echo "\t</div>\n";

        }
        else{

            echo "<br />\n";
        }


    // DIV EMBED
        if(count($div_embeds)>0){

            foreach($div_embeds as $div_embed){

                echo $div_embed;
            }

        }



    echo $FORM;




    if($_SESSION['user']['livello']==3){

        $amministrazione_tabella_up="
            <td class=\"comm_img\"><a href=\"admin/gestione_tabelle_gruppi.php?det=$oid&amp;gid={$Scheda->PT->gid}\"><img src=\"img/rotelle.gif\" alt=\""._('administer form')."\" class=\"noborder\" /></a></td>";

        $amministrazione_tabella_down="
            <td class=\"comm_txt\"><a href=\"admin/gestione_tabelle_gruppi.php?det=$oid&amp;gid={$Scheda->PT->gid}\">"._('administer')."</a></td>";
    }
    else{
        $amministrazione_tabella_up='';
        $amministrazione_tabella_down='';
    }



    echo "<div id=\"tipo-vista2\">
        <table border=\"0\" summary=\""._('view settings')."\" class=\"switch-vista\">
            <tr> 
                ".$amministrazione_tabella_up."
                <td class=\"comm_img\"><img src=\"img/vista_scheda_h.gif\" alt=\"<?php echo _('form view');?>\" class=\"noborder\" /></td>
                <td class=\"comm_img\"><a href=\"javascript:;\" onclick=\"switch_vista();\"><img src=\"img/vista_tab.gif\" alt=\""._('grid view')."\" class=\"noborder\" /></a></td>
            </tr>
            <tr> 
                ".$amministrazione_tabella_down."
                <td class=\"comm_txt\">". _('form view')."</td>
                <td class=\"comm_txt\"><a href=\"javascript:;\" onclick=\"switch_vista();\">". _('grid view')."</a></td>
            </tr>
        </table>

    </div>
    ";

    if(isset($Scheda->PT->pemetti_allegati) || isset($Scheda->PT->permetti_link)){

        echo $Scheda->print_attach_and_links();
    }


    echo "</div>\n";


// DIV PER LA DUPLICAZIONE


 if($Scheda->PT->in_duplica==1){


?>

<div id="popup-duplica">
    <div class="chiudi-box"><span class="fakelink" onclick="jQuery('#popup-duplica').toggle();"><?php echo _('Close');?> [X]</span></div>

    <p><strong><?php echo _('Select the subforms to duplicate');?></strong></p>

    <?php

for($i=0;$i<count($array_id_submask);$i++){

    echo "<input type=\"checkbox\" name=\"sotto__".$array_id_submask[$i]."\" value=\"1\" /> ".str_replace("'","",$alias_submask[$i])." <br />\n";

}


    echo "<br /><hr />\n";

    echo "<p><strong>"._('Duplicate other objects:')."</strong></p>\n";

    echo "<input type=\"checkbox\" name=\"duplica_allegati\" value=\"1\" /> "._('Duplicate attachments')."<br /><br />\n";
    echo "<input type=\"checkbox\" name=\"duplica_link\" value=\"1\" /> "._('Duplicate link')."<br />\n";

    echo "<br /><hr />\n";

    echo "<br /><input type=\"button\" onclick=\"prepara_duplica()\" name=\"duplicatore\" value=\" "._('Duplicate')." \" />\n";
    echo " <input type=\"button\" onclick=\"mostra_nascondi('popup-duplica')\" name=\"annulla_duplica\" value=\" "._('Cancel')." \" />\n";

    echo "</div>\n";

 }


 if($_SESSION['VF_VARS']['shortcut_tastiera_attivi']==1 && $_SESSION['VF_VARS']['shortcut_tastiera_popup']){ 

    echo $Scheda->print_hotkeys_pop();
}







 ?>


<div id="scheda-tabella" style="display:none;">

    <div id="scheda-tabella-cont">
        <?php

        $altezza_iframe_tabella=(28 * 20) + 6;

        if($htmltable_type=='dataTables'){



            echo '<table id="gridTableView" class="display" cellspacing="0" width="100%" style="height:'.$altezza_iframe_tabella.'px;" >
                <thead>
                    <tr>
                    ';
                    foreach (explode(", ", $xg['campi']) as $colname) {
                        echo "\t\t\t\t<th>" . $colname . "</th>\n";
                    }

            echo '
                    </tr>
                </thead>
            </table>';

        }
        else{
            echo '<div id="gridbox" style="height:'.$altezza_iframe_tabella.'px;" ></div>';
        }


        ?>

    </div>


    <div id="tipo-vista1">
        <table border="0" summary="<?php echo _('view settings');?>" class="switch-vista">
            <tr>
                <?php echo $amministrazione_tabella_up; ?>
                <td class="comm_img"><a href="javascript:;" onclick="switch_vista();"><img src="img/vista_scheda.gif" alt="vista scheda" class="noborder" /></a></td>
                <td class="comm_img"><img src="img/vista_tab_h.gif" alt="vista tabella" class="noborder" /></td>
            </tr>
            <tr> 
                <?php echo $amministrazione_tabella_down; ?>
                <td class="comm_txt"><a href="javascript:;" onclick="exit_table_search();"><?php echo _('form view');?></a></td>
                <td class="comm_txt"><?php echo _('grid view');?></td>
            </tr>
        </table>
    </div>

</div>


    <script>
         inizializza_pulsanti_modifica(); 
    if(VF.usaHistory){ 
        history_initialize();
    } 
    </script>
<?php echo closeLayout1(); ?>