<?php
/**
 * Genera un XSL dinamico prendendo le informazioni di tabella. 
 * 
 * @desc File XSL dinamico di default
 * @package VFront
 * @subpackage VFront_XML
 * @author M.Marcello Verona
 * @copyright 2007-2010 M.Marcello Verona
 * @version 0.96 $Id: xsl.php 1078 2014-06-13 15:35:53Z marciuz $
 * @license http://www.gnu.org/licenses/gpl.html GNU Public License 
 */


require("../inc/conn.php");

function genera_xsl_base($rif_tabella,$solo_visibili=0){
	
	global  $vmsql, $vmreg, $db1;
	
	
	$QB = (isset($_GET['querybased']) && $_GET['querybased']=='@') ? 1:0;
	
	// richiesta di foglio querybased
	if($QB){
		
		$nome_report = preg_replace("|[^a-z0-9_-]+|i","_",$_GET['action']);
		
		// prendi la query
		$q_def = $vmreg->query("SELECT def_query FROM {$db1['frontend']}{$db1['sep']}xml_rules WHERE nome_report='$nome_report' LIMIT 1");
		
		list($sql_def)=$vmreg->fetch_row($q_def);
		
		if($vmreg->query_try($sql_def)){
			$q_campi=$vmreg->query($sql_def);
			
			$RS=$vmreg->fetch_assoc($q_campi);
						
			$cols=array_keys($RS);
		}
		else{
			$cols=array();
		}
		
		$titolo = "Report $nome_report";
		
	}
	else{
		
		if(RegTools::is_tabella($rif_tabella)){
			
			// PRENDE LE CARATTERISTICHE DELLA TABELLA
			$tabella = $rif_tabella;
		}
		else{
			
			// prende il nome della tabella
			$tabella = RegTools::oid2name($rif_tabella);
			
		}
		
		list($cols,$alias)= RegTools::prendi_colonne_frontend($tabella,'column_name, alias_frontend',$solo_visibili,intval($_SESSION['gid']));
	
		$titolo= _('Table')." $tabella";
	}
	
	
/*

<xsl:output method="xhtml" indent="yes"
	doctype-system="http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd" 
	doctype-public="-//W3C//DTD XHTML 1.0 Transitional//EN" 
	omit-xml-declaration="yes"
	/>
	
*/

$LANG = FRONT_LANG;

$label_record=_('Record');
$label_of=_('of');
	
$XSL=<<<XSL
<?xml version="1.0" encoding="utf-8" ?>
<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform">
<xsl:output method="xml" indent="yes"
	doctype-system="http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd" 
	doctype-public="-//W3C//DTD XHTML 1.0 Transitional//EN" 
	omit-xml-declaration="yes"
	/>

<xsl:template match="/recordset">
<html xmlns="http://www.w3.org/1999/xhtml" lang="$LANG">
<head>
<title>$titolo</title>
<style type="text/css">

	body{
	
		font-family: Arial,sans;
	}
	
	h1{
		font-size:1.8em;
		color:#6495ED;
		border-top:2px solid #6495ED;
		border-bottom:2px solid #6495ED;
	}
	
	table{
		border:0;
		border-collapse:collapse;
		margin-bottom:20px;
	}
	
	table tr td{	
		padding: 0.1em 0.7em;
		font-size:0.75em;
	}
	
	table tr th{	
		padding: 0.1em 0.7em;
		text-align:right;
		font-size:0.75em;
	}
	
	p {
		font-size:0.75em;
	}
	
</style>
</head>

<body>
<h1>$titolo</h1>
<p>$label_record <xsl:value-of select="@minoffset" /> - <xsl:value-of select="@maxoffset" /> $label_of <xsl:value-of select="@tot" /></p>
<xsl:for-each select="row">
<table summary="tabella riepilogo">
XSL;

for($i=0;$i<count($cols);$i++){
	
	$display_name = ($alias[$i]=='') ? $cols[$i] : $alias[$i];
	
	$XSL.="<tr><th>$display_name: </th><td><xsl:value-of select=\"{$cols[$i]}\" /></td></tr>\n";
}

$XSL.=<<<XSL
</table>
<hr />
</xsl:for-each>

</body>
</html>


</xsl:template>
</xsl:stylesheet>
XSL;

header("Content-Type: text/xml; charset=".FRONT_ENCODING);
echo $XSL;

}

genera_xsl_base($_GET['action'],!intval($_GET['vis']));

?>