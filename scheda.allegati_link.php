<?php
/**
* Script per mostrare il box di allegati e link nella pagina {@link scheda.php}
* 
* @package VFront
* @author Mario Marcello Verona <marcelloverona@gmail.com>
* @copyright 2007-2010 M.Marcello Verona
* @version 0.96 $Id: scheda.allegati_link.php 1150 2015-05-06 19:41:33Z marciuz $
* @license http://www.gnu.org/licenses/gpl.html GNU Public License
*/

	$allegati_tab = ($data_tab['permetti_allegati']=='1') ? 1:0;
	$link_tab	  = ($data_tab['permetti_link']=='1') 	   ? 1:0;
	
	if($allegati_tab || $link_tab){
		
		$DIV_ALLEGATI_LINK = "\t<div id=\"allegati-link\">\n";
		
		if($allegati_tab) $DIV_ALLEGATI_LINK .= "<a href=\"javascript:;\" onclick=\"openWindow('add.attach.php?t='+VF.tabella_alias+'&amp;id='+localIDRecord,'Allegati',70);\" id=\"href_tab_allegati\">"._("attachments")." (0)</a><br />";
		if($link_tab) $DIV_ALLEGATI_LINK .= "<a href=\"javascript:;\" onclick=\"openWindow('add.link.php?t='+VF.tabella_alias+'&amp;id='+VF.localIDRecord,'Link',80);\"  id=\"href_tab_link\">"._("link")." (0)</a><br />";
		
		$DIV_ALLEGATI_LINK .= "\t</div>\n";
		
		echo $DIV_ALLEGATI_LINK;
		
	}
	