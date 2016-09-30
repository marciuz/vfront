<?php
/**
* Libreria di funzioni legate all'esportazione dei dati.  
* 
* @package VFront
* @subpackage Function-Libraries
* @author Mario Marcello Verona <marcelloverona@gmail.com>
* @copyright 2007-2010 M.Marcello Verona
* @version 0.96 $Id: class.export.php 1118 2014-12-16 00:44:03Z marciuz $
* @license http://www.gnu.org/licenses/gpl.html GNU Public License
*/



class Export {

	
	public $ids_search='';
	
	public $raw;
	
	private $oid=0;
	
	private $nome_tabella;
	
	
	
	function __construct($OID){
		
		$this->oid = (int) $OID;
		
		$this->nome_tabella = RegTools::oid2name($this->oid);
	}
	
	
	
	private function __query_exec($sql){
		
		global  $vmsql, $vmreg, $db1;
		
		if($this->ids_search!=''){
			
			// prendi campo PK
			$campo_pk=RegTools::prendi_PK($this->nome_tabella);

			$vals = explode(",",$this->ids_search);
			$val_str=' ';

			for($i=0;$i<count($vals);$i++){

				$val_str.= (is_numeric($vals[$i])) ? $vals[$i]."," : "'".$vmsql->escape($vals[$i])."',";
			}

			$val_str=substr($val_str,0,-1);
			
			$sql=str_replace("{{{SEARCH}}}"," AND $campo_pk IN (".$val_str.") ",$sql);
		}
		else{
			
			$sql=str_replace("{{{SEARCH}}}",'',$sql);
		}
		
		$q=$vmsql->query($sql);
		
		return $q;		
	}
	

	/**
	 * Funzione per l'esportazione dei dati
	 *
	 * @param bool $only_visibile
	 * @todo riconducibile alla funzione
	 * @return resource
	 */
	private function __tabella_elaborata($only_visibile=true){

		
		// PRENDI INFO Colonne della TABELLA
		$fields="t.orderby, t.orderby_sort ";

		$matrice_info=RegTools::prendi_colonne_frontend($this->oid, $fields, $only_visibile,"session","assoc");

		foreach($matrice_info as $k=>$info){

			if($k==0){
				
				if($info['orderby']==''){
					
					$ORDERBY='';
				}
				else{
					
					$ORDERBY="ORDER BY ";
					
					$oby=explode(",",$info['orderby']);
					$obysort=explode(",",$info['orderby_sort']);
					
					for($i=0;$i<count($oby);$i++){
					
						$ORDERBY.=" ".$oby[$i]." ".$obysort[$i].",";
					}
				}
				
				$ORDERBY = (strlen($ORDERBY)>0) ? substr($ORDERBY,0,-1) : "";
			}
		}

		
		$campi= RegTools::campi_elaborati($this->oid, $only_visibile);

		$query_elab=$this->__query_exec("SELECT $campi FROM {$this->nome_tabella} t WHERE 1=1 {{{SEARCH}}} $ORDERBY");

		return $query_elab;
	}











	/**
	 * Funzione di esportazione generica tabella in formato CSV
	 *
	 * @param string $sep
	 */
	public function tabella_csv($sep=','){

		global  $vmsql, $vmreg, $db1;

		$nome_file_tmp=_PATH_TMP."/".md5($this->oid).".csv";

		// Prende la tabella in XML
		if($this->raw){

			$q=$this->__query_exec("SELECT * FROM {$this->nome_tabella} WHERE 1=1 {{{SEARCH}}}");

		}
		else{
			$q=$this->__tabella_elaborata();
		}

		$RIGHE="";

		$i=0;

		while($RS=$vmsql->fetch_assoc($q)){

			if($i==0){

				$campi=array_keys($RS);
				$fp=fopen($nome_file_tmp,'w');
				fwrite($fp,"\"".implode('"'.$sep.'"',$campi)."\"\n");
				fclose($fp);

			}

			$RIGA='';

			foreach($RS as $val){
				$RIGA.="\"".addslashes($val)."\"$sep";
			}

			$RIGA=stripslashes($RIGA);
			$RIGA=str_replace(array("\r\n","\n\r","\n","\r")," ",$RIGA);

			$fp=fopen($nome_file_tmp,'a');
			fwrite($fp,substr($RIGA,0,-1)."\n");
			fclose($fp);

			$i++;
		}


		//Begin writing headers
		header("Content-Type: application/csv-tab-delimited-table");
		header("Content-disposition: filename={$this->nome_tabella}-".date("Ymd").".csv");
		print join('',file($nome_file_tmp));
		unlink($nome_file_tmp);
		exit;

	}


	/**
	 * Funzione di trasfomazione di tabella in HTML
	 *
	 * @param string $colore_th
	 */
	 public	function tabella_html($colore_th=''){

		//Begin writing headers
		print $this->_table2html($colore_th);
		exit;
	}



	/**
	 * Funzione interna di generazione della tabella HTML. 
	 * Viene poi utilizzata da altre funzioni di questa libreria per generare l'output.
	 *
	 * @param string $colore_th Espresso in RGB esadecimale. Ad es. #FF0000
	 * @return string
	 */
	 private function _table2html($colore_th=''){

		global  $vmsql, $vmreg, $db1;

		$nome_file_tmp=_PATH_TMP."/".md5($this->nome_tabella).".htm";

		// PREN DE LA QUERY
		if($this->raw){

			$q=$this->__query_exec("SELECT * FROM {$this->nome_tabella} WHERE 1=1 {{{SEARCH}}}");
		}
		else{
			$q=$this->__tabella_elaborata();
		}



		$HTML="<!DOCTYPE html>"
                ."<html>\n<head>\n<meta http-equiv=\"Content-type\" content=\"text/html; charset=".FRONT_ENCODING."\">"
                ."<title></title>\n"
                ."<style>body{ font-family: Arial, sans; font-size: 0.85em;} table { border-collapse: collapse; } table tr td,table tr th{ padding: 2px; font-size: 0.9em; }</style>\n"
                ."</head>\n"
                ."<body>\n";
        
        $HTML.="<h1>"._('Table')." {$this->nome_tabella}</h1>\n";

		$i=0;
		$info_colore= ($colore_th=='') ? " bgcolor=\"#FF9900\"" : " bgcolor=\"$colore_th\"";
		$TR_TD="";

		$fp=fopen($nome_file_tmp,'w');

		while($RS=$vmsql->fetch_assoc($q)){

			if($i==0){

				$campi=array_keys($RS);

				$TR_TH="<tr><th$info_colore>".implode("</th><th$info_colore>",$campi)."</th></tr>\n";

			}

			$TR_TD="<tr><td>".implode("</td><td>",$RS)."</td></tr>\n";

			fwrite($fp,$TR_TD);




			$i++;
		}

		fclose($fp);

		return "
$HTML
<table border=\"1\" summary=\"{$this->nome_tabella}\">
$TR_TH
".join('',file($nome_file_tmp))."
</table>
</body>
</html>";
	}








	/**
	 * Funzione di export excel mediante l'uso del pacchetto PEAR Spreadsheet_Excel_Writer
	 *
	 * @param mixed $colore_th
	 * @param mixed $font_color_th
	 */
	 private function _spreadsheet($colore_th=44,$font_color_th='black'){

		global  $vmsql, $vmreg, $db1;


		include_once('Spreadsheet/Excel/Writer.php');

		$clausola_visibile = (!$this->raw) ? "AND c.in_visibile=1" : "";

		// PRENDI INFO Colonne della TABELLA
		$query2 = $vmreg->query("SELECT c.column_name , c.data_type , c.character_maximum_length as maxsize
						FROM {$db1['frontend']}{$db1['sep']}registro_col c, {$db1['frontend']}{$db1['sep']}registro_tab t
						WHERE c.id_table={$this->oid} 
						AND c.id_table=t.id_table
						$clausola_visibile
						ORDER BY c.ordinal_position");

		$matrice_info=$vmreg->fetch_assoc_all($query2,true);

		// Prende la tabella in XML
		if($this->raw){

			$q=$this->__query_exec("SELECT * FROM {$this->nome_tabella} WHERE 1=1 {{{SEARCH}}}");

		}
		else{
			$q=$this->__tabella_elaborata();
		}



		// larghezza cols default:
		$standard_col=12;
		$maxsize_col=30;
		if($colore_th=='') $colore_th=44 ;

		// Creating a workbook
		$workbook = new Spreadsheet_Excel_Writer();

		// sending HTTP headers
		$workbook->send($this->nome_tabella.'-'.date("Ymd").'.xls');

		// Stile cella
		$format0 =& $workbook->addFormat();
		$format0->setBorder(1);

		// stile intestazione
		$format1 =& $workbook->addFormat();
		$format1->setColor($font_color_th);
		$format1->setFgColor($colore_th);
		$format1->setBorderColor('grey');
		$format1->setBorder(1);
		$format1->setBold();
		$format1->setAlign('center');
		$format1->setTextWrap();


		$worksheet[0] =& $workbook->addWorksheet('table');

		$x=0;
		$y=0;

		// intestazioni
		foreach($matrice_info['column_name'] as $u=>$val_eti){

			// lunghezza colonna
			if(in_array($matrice_info['data_type'][$u],array('char','varchar'))){

				$n_cols=($matrice_info['maxsize'][$u]>$standard_col) ? $matrice_info['maxsize'][$u] : $standard_col;
				if($n_cols>$maxsize_col) $n_cols=$maxsize_col;
			}
			// lunghezza colonna
			else if(in_array($matrice_info['data_type'][$u],array('text','mediumtext','longtext'))){

				$n_cols=$maxsize_col;
			}
			else{
				// lunghezza chars eti
				if(strlen($val_eti)>=$standard_col)
				$n_cols=strlen($val_eti)+3;

				else
				$n_cols=$standard_col;
			}

			$worksheet[0]->setColumn($x,$x,$n_cols);

			$worksheet[0]->write(0, $x, $val_eti,$format1);

			$x++;
		}


		// giornate
		while ($RS=$vmsql->fetch_row($q)) {

			$y++;
			$xx=0;

			// per ogni ulente esito della rilevazione
			for($i=0;$i<count($RS);$i++){

				$worksheet[0]->write($y, $xx, $RS[$i],$format0);

				$xx++;
			}
		}

		$workbook->close();

		exit;

	}










	#################################################################
	#
	#	EXCEL (html letto da excel)
	#


	/**
	 * Genera una tabella XLS creando tabella HTML e mandando un header di excel
	 *
	 * @param string $colore_th
	 */
	public function tabella_xls($colore_th=''){

		global  $vmsql, $vmreg, $db1;

		$test_pear_pk=(include_once('Spreadsheet/Excel/Writer.php'));

		if($test_pear_pk){
			// Prende la tabella in XML
			$xls_out=$this->_spreadsheet($colore_th);
		}
		else{
			// Prende la tabella in XML
			$xls_out=$this->_table2html($colore_th);
		}

		$ctype="application/vnd.ms-excel";

		//Begin writing headers
		header("Pragma: public");
		header("Expires: 0");
		header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
		header("Cache-Control: public");
		header("Content-Description: File Transfer");

		//Use the switch-generated Content-Type
		header("Content-Type: $ctype");

		//Force the download
		$header="Content-Disposition: attachment; filename={$this->nome_tabella}-".date("Ymd").".xls;";
		header($header );
		print $xls_out;
		exit;

	}



	/**
	 * Genera una tabella nello standard Open Document Spreadsheet (ad es. openOffice)
	 *
	 * @param string $colore_th
	 */
	public function tabella_ods($colore_th=''){

		global  $vmsql, $vmreg, $db1;

		// PRENDI LE IMPOSTAZIONI DEI CAMPI DELLA TABELLA

		$q0=$vmreg->query("SELECT column_name,data_type FROM {$db1['frontend']}{$db1['sep']}registro_col WHERE id_table={$this->oid} ORDER BY ordinal_position");

		while($RS0=$vmreg->fetch_row($q0)){
			$tipo_campo[$RS0[0]]=$RS0[1];
		}

		if($this->raw){
			$q=$this->__query_exec("SELECT * FROM {$this->nome_tabella} WHERE 1=1 {{{SEARCH}}}");
		}
		else{
			$q=$this->__tabella_elaborata();
		}





		// Cancella se esiste già il file
		@unlink(_PATH_TMP.'/content.xml');


		// prendi il file content.xml
		$cont=join('',file(FRONT_REALPATH."/plugins/ods/content.xml"));

		// array contenuto;
		$ar_cont=explode('<!-- contenuto -->',$cont);



		$XML_INT='';
		$XML_CONT='';

		$i=0;
		$ultima_scrittura=0;


		while($RS=$vmsql->fetch_assoc($q)){

			// prima riga, scrive le intestazioni

			$XML_CONT.="<table:table-row table:style-name=\"ro1\">";

			foreach($RS as $lab=>$val){

				if($i==0){

					$XML_INT.="
					<table:table-cell table:style-name=\"ce1\" office:value-type=\"string\">
						<text:p>{$lab}</text:p>
					</table:table-cell>";
				}

				$XML_CONT.=$this->_ods_type($tipo_campo,$lab,$val);
			}


			$XML_CONT.='</table:table-row>';

			// scrittura prima volta:
			if($i==0){

				$apertura_XML='
					<table:table table:name="'.$this->nome_tabella.'" table:style-name="ta1" table:print="false">
						<table:table-column table:style-name="co1" table:number-columns-repeated="'.($i+1).'" table:default-cell-style-name="Default"/>
						<table:table-row table:style-name="ro1">
						'.$XML_INT.'
						</table:table-row>
						';

				// Scrittura inizio di tabella
				if($fp=@fopen(_PATH_TMP.'/content.xml','a')){
					fwrite($fp,$ar_cont[0]);
					fwrite($fp,$apertura_XML);
				}

			}

			// scrivo la roba in ciclo ogni 10 record
			if($i%20==0){
				if($fp=@fopen(_PATH_TMP.'/content.xml','a')){
					fwrite($fp,$XML_CONT);
					fclose($fp);

					$ultima_scrittura=$i;

					// unset
					$XML_CONT='';
				}
			}


			$i++;
		}

		// ultima scrittura fuori dal ciclo (per i record esclusi dal multiplo...)

		if($i>$ultima_scrittura){
			if($fp=@fopen(_PATH_TMP.'/content.xml','a')){
				fwrite($fp,$XML_CONT);
				fclose($fp);

				// unset
				$XML_CONT='';
			}
		}


		$chiusura_XML=	'\t</table:table>';


		// chiudi il nuovo file
		if($fp=@fopen(_PATH_TMP.'/content.xml','a')){
			fwrite($fp,$chiusura_XML);
			fwrite($fp,$ar_cont[1]);
			fclose($fp);
		}
		else {
			openErrorGenerico(_("Error: could not write to filesystem."),false,_('Could not create file to set server write permission.')."<br />"._("If this problem persists, contact your system administrator"));
			exit;
		}


		// METODO PHP
		// CREA IL PACCHETTO

		require_once(FRONT_REALPATH."/inc/EasyZIP.class.php");

		$zip = new EasyZIP();

		$zip->addFile("content.xml",_PATH_TMP."/");
		$zip->addDir(FRONT_REALPATH."/plugins/ods","Configurations2");
		$zip->addDir(FRONT_REALPATH."/plugins/ods","META-INF");
		$zip->addDir(FRONT_REALPATH."/plugins/ods","Pictures");
		$zip->addFile("meta.xml",FRONT_REALPATH."/plugins/ods/");
		$zip->addFile("settings.xml",FRONT_REALPATH."/plugins/ods/");
		$zip->addFile("styles.xml",FRONT_REALPATH."/plugins/ods/");
		$zip->addFile("mimetype",FRONT_REALPATH."/plugins/ods/");

		$tmp_ods=_PATH_TMP.'/'.md5(time()).".ods";

		$zip->zipFile($tmp_ods);

		$ctype="application/vnd.oasis.opendocument.spreadsheet";

		if(is_file($tmp_ods)){

			//Begin writing headers
			header("Pragma: public");
			header("Expires: 0");
			header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
			header("Cache-Control: public");
			header("Content-Description: File Transfer");

			//Use the switch-generated Content-Type
			header("Content-Type: $ctype");

			//Force the download
			$header="Content-Disposition: attachment; filename={$this->nome_tabella}-".date("Ymd").".ods;";
			header($header);

			print join('',file($tmp_ods));
		}
		else{

			openErrorGenerico(_("Error: could not write to filesystem."),
							  false,
							  _('Could not create file to set server write permission.').'<br />'
							  ._("If this problem persists, contact your system administrator"));
			exit;

		}

		unlink(_PATH_TMP."/content.xml");
		unlink($tmp_ods);
		exit;
	}





	/**
	 * Funzione interna usata nella generazione di foglio di calcolo OpenDocument.
	 * Questa funzione � richiamata dalla funzione {@see tabella_ods}
	 *
	 * @param array $array_tipo_campo
	 * @param string $nome_campo
	 * @param mixed $value
	 * @see function tabella_ods
	 * @return string Il frammento di XML per il campo scelto
	 */
	private function _ods_type($array_tipo_campo,$nome_campo,$value){

		switch($array_tipo_campo[$nome_campo]){

			case 'int':
			case 'float':
			case 'double':
				return '
					<table:table-cell office:value-type="float" office:value="'.$value.'">
						<text:p>'.$value.'</text:p>
					</table:table-cell>';
				break;



			case 'date' :
				return '
					<table:table-cell office:value-type="date" office:date-value="'.$value.'">
						<text:p>'.VFDate::date_encode($value,false).'</text:p>
					</table:table-cell>';
				break;



			case 'timestamp':
			case 'datetime':
				return '
					<table:table-cell office:value-type="date" office:date-value="'.$value.'">
						<text:p>'.VFDate::date_encode($value,true,'ods').'</text:p>
					</table:table-cell>';


			default : // varie string
			$value=str_replace(array("\n","\r"),"",$value);
			$value=htmlspecialchars(Common::vf_utf8_encode($value));

			return '
					<table:table-cell office:value-type="string">
						<text:p>'.$value.'</text:p>
					</table:table-cell>';
		}
	}


}