<?php
/**
 * Questo file permette di generare l'XSL-FO di default che verr� poi usato da Apache FOP
 * 
 * @desc Generazione dinamica di XSL-FO
 * @package VFront
 * @subpackage VFront_XML
 * @author M.Marcello Verona
 * @copyright 2007-2010 M.Marcello Verona
 * @version 0.96 $Id: fo.php 1078 2014-06-13 15:35:53Z marciuz $
 * @license http://www.gnu.org/licenses/gpl.html GNU Public License 
 * @see fop_exec.php
 */


require_once("../inc/conn.php");

/*function genera_xsl_base($rif_tabella,$solo_visibili=0){
	
	global  $vmsql, $vmreg, $db1;*/

$rif_tabella=$_GET['action'];
	
	if(RegTools::is_tabella($rif_tabella)){
		
		// PRENDE LE CARATTERISTICHE DELLA TABELLA
		$tabella = $rif_tabella;
	}
	else{
		
		// prende il nome della tabella
		$tabella = RegTools::oid2name($rif_tabella);
		
	}
	
list($cols,$alias)= RegTools::prendi_colonne_frontend($tabella,'column_name, alias_frontend',$solo_visibili,intval($_SESSION['gid']));

$str_tabella = ucfirst($tabella);
	
	
$XSL=<<<XSLFO
<?xml version="1.0" encoding="UTF-8"?>
<xsl:stylesheet version="2.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform" xmlns:fo="http://www.w3.org/1999/XSL/Format" xmlns:fn="http://www.w3.org/2005/xpath-functions" xmlns:xdt="http://www.w3.org/2005/xpath-datatypes" xmlns:xs="http://www.w3.org/2001/XMLSchema" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance">
	<xsl:output version="1.0" method="xml" encoding="UTF-8" indent="no"/>
	<xsl:param name="SV_OutputFormat" select="'PDF'"/>
	<xsl:variable name="XML" select="/"/>
	<xsl:variable name="fo:layout-master-set">
		<fo:layout-master-set>
			<fo:simple-page-master master-name="default-page" page-height="11in" page-width="8.5in" margin-left="0.6in" margin-right="0.6in">
				<fo:region-body margin-top="0.79in" margin-bottom="0.79in"/>
				<fo:region-after extent="0.79in"/>
			</fo:simple-page-master>
		</fo:layout-master-set>
	</xsl:variable>
	<xsl:template match="/">
		<xsl:variable name="maxwidth" select="7.30000"/>
		<fo:root>
			<xsl:copy-of select="\$fo:layout-master-set"/>
			<fo:page-sequence master-reference="default-page" initial-page-number="1" format="1">
				<xsl:call-template name="footerall"/>
				<fo:flow flow-name="xsl-region-body">
					<fo:block>
						<fo:block>
							<fo:leader leader-pattern="space"/>
						</fo:block>
						<fo:block>
							<fo:leader leader-pattern="space"/>
						</fo:block>
						<fo:block font-size="24pt" font-weight="bold">
							<fo:block font-size="14pt">
								<fo:inline>
									<xsl:text>$str_tabella</xsl:text>
								</fo:inline>
							</fo:block>
						</fo:block>
						<fo:block>
							<fo:leader leader-pattern="space"/>
						</fo:block>
						<fo:block>
							<fo:leader leader-pattern="space"/>
						</fo:block>
						<fo:block>
							<fo:leader leader-pattern="space"/>
						</fo:block>
						<xsl:for-each select="\$XML">
							<xsl:for-each select="recordset">
								<xsl:for-each select="row">
XSLFO;


for($i=0;$i<count($cols);$i++){
	
	$display_name = ($alias[$i]=='') ? $cols[$i] : $alias[$i];
	
	
	/*if($cols[$i]=='email'){
		
		$XSL.="						<fo:block>
										<fo:leader leader-pattern=\"space\"/>
									</fo:block>
									<xsl:if test=\"{$cols[$i]} != &apos;&apos;\">
										<fo:inline>
											<fo:leader leader-pattern=\"space\"/>
										</fo:inline>
										<xsl:for-each select=\"{$cols[$i]}\">
											<fo:inline color=\"#0046ff\">
												<xsl:apply-templates/>
											</fo:inline>
										</xsl:for-each>
										<fo:block>
											<fo:leader leader-pattern=\"space\"/>
										</fo:block>
									</xsl:if>\n";
	}
	else{*/
		
		$XSL.="						<fo:block font-size=\"1pt\">
										<fo:leader leader-pattern=\"space\"/>
									</fo:block>
									<xsl:for-each select=\"{$cols[$i]}\">
										<fo:inline font-weight=\"bold\" font-size=\"10pt\">
											{$display_name}: 
										</fo:inline>
										<fo:inline font-size=\"10pt\">
											<xsl:apply-templates/>
										</fo:inline>
									</xsl:for-each>
									";
/*	}*/
}








$XSL.=<<<XMLFO2
									
									<fo:block text-align="center" space-before.optimum="-8pt">
										<fo:leader leader-pattern="space"/>
										<fo:leader leader-length="100%" leader-pattern="rule" rule-thickness="1pt"/>
									</fo:block>
									<fo:block>
										<xsl:text>&#xA;</xsl:text>
									</fo:block>
								</xsl:for-each>
							</xsl:for-each>
						</xsl:for-each>
						<fo:block>
							<fo:leader leader-pattern="space"/>
						</fo:block>
					</fo:block>
					<fo:block id="SV_RefID_PageTotal"/>
				</fo:flow>
			</fo:page-sequence>
		</fo:root>
	</xsl:template>
	<xsl:template name="footerall">
		<xsl:variable name="maxwidth" select="7.30000"/>
		<fo:static-content flow-name="xsl-region-after">
			<fo:block>
				<xsl:variable name="tablewidth0" select="\$maxwidth * 1.00000"/>
				<xsl:variable name="sumcolumnwidths0" select="0.04167 + 0.04167"/>
				<xsl:variable name="defaultcolumns0" select="1 + 1"/>
				<xsl:variable name="defaultcolumnwidth0">
					<xsl:choose>
						<xsl:when test="\$defaultcolumns0 &gt; 0">
							<xsl:value-of select="(\$tablewidth0 - \$sumcolumnwidths0) div \$defaultcolumns0"/>
						</xsl:when>
						<xsl:otherwise>
							<xsl:value-of select="0.000"/>
						</xsl:otherwise>
					</xsl:choose>
				</xsl:variable>
				<xsl:variable name="columnwidth0_0" select="\$defaultcolumnwidth0"/>
				<xsl:variable name="columnwidth0_1" select="\$defaultcolumnwidth0"/>
				<fo:table margin-left="0.0in" margin-right="0.0in" width="{\$tablewidth0}in" border-collapse="separate" border-separation="0.04167in" color="black" display-align="center" text-align="left">
					<fo:table-column column-width="{\$columnwidth0_0}in"/>
					<fo:table-column column-width="{\$columnwidth0_1}in"/>
					<fo:table-body>
						<fo:table-row>
							<fo:table-cell height="0.31250in" number-columns-spanned="2" padding-top="0.00000in" padding-bottom="0.00000in" padding-left="0.00000in" padding-right="0.00000in">
								<fo:block padding-top="1pt" padding-bottom="1pt"/>
							</fo:table-cell>
						</fo:table-row>
						<fo:table-row>
							<fo:table-cell number-columns-spanned="2" padding-top="0.00000in" padding-bottom="0.00000in" padding-left="0.00000in" padding-right="0.00000in">
								<fo:block padding-top="1pt" padding-bottom="1pt">
									<fo:block text-align="center" space-before.optimum="-8pt">
										<fo:leader leader-length="100%" leader-pattern="rule" rule-thickness="1pt" color="black"/>
									</fo:block>
								</fo:block>
							</fo:table-cell>
						</fo:table-row>
						<fo:table-row>
							<fo:table-cell font-size="inherited-property-value(&apos;font-size&apos;) - 2pt" number-columns-spanned="2" text-align="center" padding-top="0.00000in" padding-bottom="0.00000in" padding-left="0.00000in" padding-right="0.00000in">
								<fo:block padding-top="1pt" padding-bottom="1pt">
									<fo:inline font-weight="bold">
										<xsl:text>&#160;</xsl:text>
									</fo:inline>
									<fo:page-number font-weight="bold"/>
								</fo:block>
							</fo:table-cell>
						</fo:table-row>
					</fo:table-body>
				</fo:table>
			</fo:block>
		</fo:static-content>
	</xsl:template>
	<xsl:template name="double-backslash">
		<xsl:param name="text"/>
		<xsl:param name="text-length"/>
		<xsl:variable name="text-after-bs" select="substring-after(\$text, '\')"/>
		<xsl:variable name="text-after-bs-length" select="string-length(\$text-after-bs)"/>
		<xsl:choose>
			<xsl:when test="\$text-after-bs-length = 0">
				<xsl:choose>
					<xsl:when test="substring(\$text, \$text-length) = '\'">
						<xsl:value-of select="concat(substring(\$text,1,\$text-length - 1), '\\')"/>
					</xsl:when>
					<xsl:otherwise>
						<xsl:value-of select="\$text"/>
					</xsl:otherwise>
				</xsl:choose>
			</xsl:when>
			<xsl:otherwise>
				<xsl:value-of select="concat(substring(\$text,1,\$text-length - \$text-after-bs-length - 1), '\\')"/>
				<xsl:call-template name="double-backslash">
					<xsl:with-param name="text" select="\$text-after-bs"/>
					<xsl:with-param name="text-length" select="\$text-after-bs-length"/>
				</xsl:call-template>
			</xsl:otherwise>
		</xsl:choose>
	</xsl:template>
</xsl:stylesheet>
XMLFO2;


$fp=fopen(_PATH_TMP."/".$tabella.".xslt","w");
fwrite($fp,$XSL);
fclose($fp);

?>