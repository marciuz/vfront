<?php
/**
* File che recupera i valori da mostrare nei suggerimenti dei campi. 
* Viene richiamato dallo script {@link scheda.php} e dalle funzioni di scriptaculous
* 
* @package VFront
* @subpackage RPC
* @author Mario Marcello Verona <marcelloverona@gmail.com>
* @copyright 2007-2010 M.Marcello Verona
* @version 0.99 $Id: rpc.suggest.php 1120 2014-12-16 09:33:02Z marciuz $
* @license http://www.gnu.org/licenses/gpl.html GNU Public License
*/

include("../inc/conn.php");
require_once(FRONT_ROOT."/plugins/php-sql-parser/src/PHPSQLParser.php");


proteggi(1);


echo "<ul>\n";
if(is_array($_REQUEST['dati']) && isset($_REQUEST['t'])){
	
	$TABELLA = $_REQUEST['t'];
	
	list($campo,$valore)=each($_REQUEST['dati']);
	
	$valore = trim($vmsql->escape($valore));
	
	$sql_campo = "SELECT DISTINCT $campo FROM $TABELLA WHERE $campo LIKE '%".$valore."%' LIMIT 25";
	$query_campo=$vmsql->query($sql_campo);
	
	while ($dati=$vmsql->fetch_row($query_campo)) {
		
	  $testo=trim($dati[0]);
	  ?>
	  <li onMouseOver="this.className='mouse2';" onMouseOut="this.className='mouse1';"><?php echo Common::vf_utf8_encode($testo); ?></li>
	  <?php
	}
	
}
else if(is_array($_REQUEST['dati']) && isset($_REQUEST['id_col'])){
    
    $Parser = new PHPSQLParser();
	
	$id_col=(int) $_REQUEST['id_col'];
	
	list($campo,$valore)=each($_REQUEST['dati']);
	
	$valore = trim($vmreg->escape($valore));
    
	$sql="SELECT in_default FROM {$db1['frontend']}{$db1['sep']}registro_col WHERE id_reg=$id_col";
    
	$q1=$vmreg->query($sql);
	
	if($vmreg->num_rows($q1)==1){
		
		list($sql_def)=$vmreg->fetch_row($q1);
        
        $prsql = $Parser->parse($sql_def);
        
		if(is_array($prsql['SELECT']) && count($prsql['SELECT'])>0){
            
            $label_sql = (isset($prsql['SELECT'][1])) ? $prsql['SELECT'][1]['base_expr'] : $prsql['SELECT'][0]['base_expr'];
            $col_sql =  $prsql['SELECT'][0]['base_expr'];
            $table = $prsql['FROM'][0]['table'];
            
            $sql_campo = "SELECT DISTINCT $col_sql, $label_sql FROM $table WHERE ($label_sql) LIKE '%".$valore."%' LIMIT 25";
			
			$query_campo=$vmsql->query($sql_campo);
			
			while ($dati=$vmsql->fetch_row($query_campo)) {
		
			  $id_colonna=trim($dati[0]);
			  $testo=trim($dati[1]);
			  ?>
			  <li id="<?php echo "ac___{$campo}___{$id_colonna}";?>" onMouseOver="this.className='mouse2';" onMouseOut="this.className='mouse1';"><?php echo Common::vf_utf8_encode($testo); ?></li>
			  <?php
			}
		}
	}
	
}
echo "</ul>\n";