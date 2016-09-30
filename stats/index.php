<?php
/**
* Da questo file è possibile leggere e creare nuove statistiche. 
* Le statistiche sul DB sono eseguite per mezzo di query registrate in database nella tabella stats 
* 
* @desc File di menu per le statistiche sul database
* @package VFront
* @subpackage Stats
* @author Mario Marcello Verona <marcelloverona@gmail.com>
* @copyright 2007-2010 M.Marcello Verona
* @version 0.96 $Id: index.php 1078 2014-06-13 15:35:53Z marciuz $
* @license http://www.gnu.org/licenses/gpl.html GNU Public License
*/


require("../inc/conn.php");
require("../inc/layouts.php");

proteggi(2);

$files=array("sty/admin.css");

echo openLayout1(_("Database statistics"),$files);

 echo breadcrumbs(array("HOME","ADMIN",_("database statistics")));
 
 echo "<br /><img src=\"../img/statistiche.gif\" alt=\""._("statistics")."\" class=\"img-float\" />\n";
 
 echo "<h1>"._("Database statistics")."</h1>\n";
  

 	// SEZIONE FEEDBACK
 	
 	if(isset($_GET['feed'])){
		switch($_GET['feed']){
			
			case 'okdel': $feed_str="<p class=\"feed-mod-ok\">"._("Statistic removed correctly")."</p>\n";
			break;
			
			case 'kodel': $feed_str="<p class=\"feed-mod-ko\">"._("Error in removing statistic!")."</p>\n";
			break;
			
			case 'konew': $feed_str="<p class=\"feed-mod-ko\">"._("Error creating the statistic")."</p>\n";
			break;
			
			case 'komod': $feed_str="<p class=\"feed-mod-ko\">"._("Error in modifying statistic!")."</p>\n";
			break;
			
			case 'okmod': $feed_str="<p class=\"feed-mod-ok\">"._("Statistics modified correctly")."</p>\n";
			break;
			
		}
		
 	}
 	
 	else $feed_str='';
 

 	echo $feed_str;
 
 	echo "<p><a href=\"stat.personal.php?new\">"._("Create new statistic")."</a></p>\n";

	echo "<ul>
		<li><a href=\"stat.count_table.php\">"._("Data for populating tables")."</a></li>
	</ul>";
	
	
	
	
	// PRENDI LE STAT PERSONALIZZATE
	
	$sql = "SELECT s.id_stat, s.nome_stat , s.autore, s.data_stat, ".$vmreg->concat("u.nome, ' ', u.cognome", 'nomecognome')."
			FROM {$db1['frontend']}{$db1['sep']}stat s, {$db1['frontend']}{$db1['sep']}utente u
			WHERE s.autore=u.id_utente
			AND (auth_stat=1
				OR (auth_stat=2 AND u.gid=".intval($_SESSION['gid']).")
				OR (auth_stat=3 AND u.id_utente=".intval($_SESSION['user']['uid']).")
				)
			ORDER BY data_stat DESC";
	
	$q=$vmreg->query($sql);
	
	$stat_pers=array();
	
	while($RS=$vmreg->fetch_assoc($q)){
		
		// se � proprietario o amministratore pu� modificare o cancellare la statistica
		$aggiunta_mod_del = (($_SESSION['user']['uid']==$RS['autore']) || $_SESSION['user']['livello']==3) ? 
							" - <a href=\"stat.personal.php?modifica=".$RS['id_stat']."\">modifica</a> - <a href=\"stat.personal.php?elimina=".$RS['id_stat']."\" class=\"rosso\">"._("delete")."</a>" : "";
		
		$stat_pers[]="<li><a href=\"stat.personal.php?id_s=".$RS['id_stat']."\">".$RS['nome_stat']."</a> (".$RS['nomecognome'].", ".VFDate::date_encode($RS['data_stat']).") $aggiunta_mod_del</li>\n";
	}
	

	if(count($stat_pers)>0){
		
		echo "<ul style=\"margin-left:40px;\">\n".implode("",$stat_pers)."</ul>\n";
		
	}






echo closeLayout1();


?>