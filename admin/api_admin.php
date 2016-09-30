<?php
/**
 * Sono qui presenti le procedure fondamentali per la gestione del registro di VFront.
 * Il file è organizzato per aree e svolge numerose funzioni
 * 
 * @desc File per la gestione del registro di VFront
 * @package VFront
 * @subpackage Api
 * @author M.Marcello Verona
 * @copyright 2013 M.Marcello Verona
 * @version 0.97 $Id$
 * @license http://www.gnu.org/licenses/gpl.html GNU Public License 
 */

include("../inc/conn.php");
include("../inc/layouts.php");

proteggi(3);

$OUT='';


if(isset($_POST['newkey'])){
    
    $data = $vmreg->recursive_escape($_POST);
    
    // Verify IP
    if(strpos($data['ip_address'], '/')!==false){
        
        list($ip, $classes) = explode('/',$data['ip_address'],2);
    }
    else{
        $ip=$data['ip_address'];
        $classes='';
    }
    
    if(filter_var($ip,FILTER_VALIDATE_IP)){
        
        $ip.= ($classes!='') ? '/'.intval($classes) : '';
        
        $api_key=  API::gen_key();
    
        $sql="INSERT INTO {$db1['frontend']}{$db1['sep']}api_console
            (ip_address, rw, api_key, last_update)
            VALUES 
            ('$ip',".intval($data['rw']).", '$api_key', '".date('c')."')";
        
        $q=$vmreg->query($sql);
        
        $res=$vmreg->affected_rows($q);
    }
    else{
        
        $res=-2;
    }
    
    header("Location: ".$_SERVER['PHP_SELF']."?feed=".$res."&opt=insert");
    exit;
    
}
else if(isset($_POST['deletekey'])){
    
    $data = $vmreg->recursive_escape($_POST);
    
    
    if(isset($data['id']) && intval($data['id'])>0){
    
        $q=$vmreg->query("DELETE FROM {$db1['frontend']}{$db1['sep']}api_console WHERE id=".intval($data['id']));
        $res=$vmreg->affected_rows($q);
        
        
    }
    else{
        $res=-2;
    }
    
    header("Location: ".$_SERVER['PHP_SELF']."?feed=".$res."&opt=delete");
    exit;
}

else if(isset($_POST['changekey'])){
    
    $data = $vmreg->recursive_escape($_POST);
    
    if(isset($data['id']) && intval($data['id'])>0){
        
        $sql=sprintf("UPDATE {$db1['frontend']}{$db1['sep']}api_console
            SET ip_address='%s',
                rw=%d,
                last_update='%s'
            WHERE id=%s",
            $data['ip_address'],
            $data['rw'],
            date('c'),
            $data['id']
        );
        
        $q=$vmreg->query($sql);
        
        $res=$vmreg->affected_rows($q);
    }
    else{
        
        $res=-2;
    }
    
    header("Location: ".$_SERVER['PHP_SELF']."?feed=".$res."&opt=update");
    exit;
    
}

else if(isset($_GET['update']) || isset($_GET['new_key'])){
    
    if(isset($_GET['update'])){
        $q=$vmreg->query("SELECT * FROM {$db1['frontend']}{$db1['sep']}api_console WHERE id=".intval($_GET['update']));
        $RS=$vmreg->fetch_assoc($q);
        $display_ak='';
        $opt='changekey';
        $msg_btn=_('Update key');
    }
    else{
        $RS=array('ip_address'=>'', 'rw'=>'', 'api_key'=>'', 'id'=>0);
        $display_ak='style="display:none"';
        $opt='newkey';
        $msg_btn=_('Create new key');
    }
    
    $RW_sel = ($RS['rw']==1) ? 'selected="selected"' : '';
    
    $OUT.="<form action=\"".$_SERVER['PHP_SELF']."\" method=\"post\">";
    $OUT.="
        <fieldset style=\"width:70%; padding:20px;\">
        <legend style=\"font-weigth:bold\">"._('Parameter for the API KEY')."</legend>

        <div class=\"column-form\">
            <label for=\"id_address\">"._('IP Address').":</label>
            <input type=\"text\" value=\"".$RS['ip_address']."\" name=\"ip_address\" />
            <div class=\"info-campo\">"._('Accept single IP or IP with classes (e.g. /8)')."</div>
        </div>
        <div class=\"column-form\">
            <label for=\"rw\">"._('Read/Write').":</label>
            <select value=\"".$RS['rw']."\" name=\"rw\" />
               <option value=\"0\">"._('Only read')."</option>
               <option value=\"1\" $RW_sel>"._('Read/Write')."</option>
            </select>
            <div class=\"info-campo\">"._('If Only read POST, PUT and DELETE methods will be denied')."</div>
        </div>
        <div class=\"column-form\" $display_ak>
            <label for=\"api_key\">"._('Authorization Key').":</label>
            <input type=\"text\" readonly=\"readonly\" value=\"".$RS['api_key']."\" size=\"80\" />
            <div class=\"info-campo\">"._('VFront generated authorization key')."</div>
        </div>
        
        <div class=\"column-form\">
            <input type=\"hidden\" name=\"$opt\" value=\"1\" />
            <input type=\"hidden\" name=\"id\" value=\"".intval($RS['id'])."\" />
            <input type=\"submit\" value=\"".$msg_btn."\" />
        </div>
        
        ";
    
    $OUT.="</fieldset>\n</form>\n";
}
else if(isset($_GET['delete'])){
    
    $OUT.="<p>"._('You will delete the api key. This operation is not recoverable')
            ."<br />"._('Are you sure?')."</p>\n";
    
    $OUT.="<form action=\"".$_SERVER['PHP_SELF']."\" method=\"post\" >
            <input type=\"hidden\" name=\"deletekey\" value=\"1\" />
            <input type=\"hidden\" name=\"id\" value=\"".intval($_GET['delete'])."\" />
            <input type=\"submit\" value=\""._('Delete key')."\" />
           </form>
        ";
}


// Mostra chiavi disponibili
else {
    
    $OUT='';
    
    $q=$vmreg->query("SELECT * FROM {$db1['frontend']}{$db1['sep']}api_console ORDER BY id");
    
    $OUT.="<p><a href=\"?new_key=1\">"._('Create new API KEY')."</a></p>";
    
    if($vmreg->num_rows($q)==0){
        
        $OUT.="<p>"._('No key set')."</p>\n";
    }
    else{
        
        
        
        $table="<table class=\"tab-color\" summary=\"api table\" >\n";
        
        $table.="
        <tr>
            <th>ID</th>
            <th>ip_address</th>
            <th>rw</th>
            <th>api_key</th>
            <th>last_update</th>
            <th>"._('update')."</th>
            <th>"._('delete')."</th>
        </tr>";
        
        $table."</tr>\n";
        
        while($RS=$vmreg->fetch_assoc($q)){
            
            $table.="<tr>
                <td>".$RS['id']."</td>
                <td>".$RS['ip_address']."</td>
                <td>".$RS['rw']."</td>
                <td>".$RS['api_key']."</td>
                <td>".$RS['last_update']."</td>
                <td><a href=\"?update=".$RS['id']."\">Update key</a></td>
                <td><a href=\"?delete=".$RS['id']."\">Delete key</a></td>
                </tr>\n";
        }
        
        $table.="</table>\n";
        
        $OUT.=$table;
    }
    
}



$output= openLayout1(
            _("VFront API administration"),
            array(
                "sty/admin.css", 
                "sty/tabelle.css", 
                )
        );
$breadcrumbs=array("HOME","ADMIN",$_SERVER['PHP_SELF']=>_('API administration'));
if(count($_GET)>0){
    $breadcrumbs[]='api key';
}

$output.=breadcrumbs($breadcrumbs);

$output.="<h1>"._("VFront API administration")."</h1>\n";
$output.=$OUT;
$output.=closeLayout1();

print $output;
