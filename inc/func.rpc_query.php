<?php
/**
* Libreria di funzioni RPC. 
* Queste funzioni sono richiamata tendenzialmente dal file {@link rpc.php} per eseguire 
* chiamate al database dalle maschere. 
* Sono presenti inoltre alcune funzioni di utilit�.
* 
* @package VFront
* @subpackage Function-Libraries
* @author Mario Marcello Verona <marcelloverona@gmail.com>
* @copyright 2007-2010 M.Marcello Verona
* @version 0.96 $Id: func.rpc_query.php 1159 2015-11-25 23:14:53Z marciuz $
* @license http://www.gnu.org/licenses/gpl.html GNU Public License
*/


/**
 * Ricerca mediante la maschera di VFront 
 * La funzione � richimata via chiamata esterna Javascript
 * e restituisce gli ID dei record trovati
 *
 * @param array $_dati
 * @param string $tabella
 * @return array
 */
function rpc_query_search($_dati,$tabella){

	global  $vmsql, $db1;

	if(count($_dati)==0){
	    return null;
	}

	// force postgresql to case-insensitive
	if($db1['dbtype']=='postgres'){

        $IS = new iSchema();
	    $DATA_TYPES=  $IS->get_column_types($tabella);

	    $TypeCheck=new FieldType();

	    $OP_EQ = $OP_LIKE = 'ILIKE';
	}
	else{
	    $OP_EQ = '=';
	    $OP_LIKE = 'LIKE';
	}

	$orderby = RegTools::prendi_orderby($tabella,$_SESSION['gid']);

	$condizione='';

	foreach ($_dati as $k=>$val){

		$val=trim($val);

		if(strlen($val)>0){

		    // Like with * || %
		    if(preg_match('!\*|%!',$val)){

			    $val=str_replace("*","%",$val);

			    if($db1['dbtype']=='postgres'){

                    if($TypeCheck->is_char($DATA_TYPES[$k])){

                        $condizione.=" $k $OP_LIKE '".$vmsql->escape($val)."' AND";
                    }
                    else{ // skip a like in a int|bool|date context!
                        $condizione.='';
                    }
			    }
			    else{
                    $condizione.=" $k $OP_LIKE '".$vmsql->escape($val)."' AND";
			    }

		    }
		    else{

                if($db1['dbtype']=='postgres' && !$TypeCheck->is_char($DATA_TYPES[$k])){

                    $condizione.=" $k='".$vmsql->escape($val)."' AND";
                }
                else{
                    $condizione.=" $k $OP_EQ '".$vmsql->escape($val)."' AND";
                }
		    }
		}			
	}

	$condizione=substr($condizione,0,-3);

	// PRENDI LA CHIAVE PRIMARIA DELLA TABELLA

	$campoPK = RegTools::prendi_PK($tabella);


	$sql = "SELECT $campoPK FROM $tabella WHERE $condizione ORDER BY $orderby, $campoPK ";

	$query = $vmsql->query($sql);

	$n_record = $vmsql->num_rows($query);

	list($IDs) = $vmsql->fetch_row_all($query,true);

	Common::rpc_debug($sql);

	return $IDs;

}


/**
 * Search from subforms
 * 
 *
 * @param array $_dati
 * @param string $tabella
 * @param int $oid_sub
 * @return array
 */
function rpc_query_search_from_sub($_dati,$tabella,$oid_sub){

	global  $vmsql, $vmreg, $db1;

	if(!isset($_dati[0]) || count($_dati[0])==0){
		return null;
	}


	// force postgresql to case-insensitive
	if($db1['dbtype']=='postgres'){

	    $DATA_TYPES=  iSchema::get_column_types($tabella);

	    $TypeCheck=new FieldType();

	    $OP_EQ = $OP_LIKE = 'ILIKE';
	}
	else{
	    $OP_EQ = '=';
	    $OP_LIKE = 'LIKE';
	}


	//$orderby = RegTools::prendi_orderby($tabella,$_SESSION['gid']);

	$condizione='';

	foreach ($_dati[0] as $k=>$val){

		$val=trim($val);

		// Like with * || %
		    if(preg_match('!\*|%!',$val)){

			    $val=str_replace("*","%",$val);
			    if($db1['dbtype']=='postgres'){

				if($TypeCheck->is_char($DATA_TYPES[$k])){

				    $condizione.=" $k $OP_LIKE '".$vmsql->escape($val)."' AND";
				}
				else{ // skip a like in a int|bool|date context!
				    $condizione.='';
				}
			    }
			    else{
				$condizione.=" $k $OP_LIKE '".$vmsql->escape($val)."' AND";
			    }

		    }
		    else{

			if($db1['dbtype']=='postgres' && !$TypeCheck->is_char($DATA_TYPES[$k])){

			    $condizione.=" $k='".$vmsql->escape($val)."' AND";
			}
			else{
			    $condizione.=" $k $OP_EQ '".$vmsql->escape($val)."' AND";
			}
		    }			
	}

	$condizione=substr($condizione,0,-3);

	// PRENDI LA CHIAVE PRIMARIA DELLA TABELLA

	$sql_0="SELECT campo_fk_sub, campo_pk_parent, nome_tabella 
	    FROM {$db1['frontend']}{$db1['sep']}registro_submask 
	    WHERE id_submask=".intval($oid_sub);

	$q_0=$vmreg->query($sql_0);

	list($FK_parent, $campo_pk_parent, $submask_table)=$vmreg->fetch_row($q_0);


	$sql = "SELECT DISTINCT $FK_parent FROM $submask_table WHERE $condizione "; //ORDER BY $orderby";

	$query = $vmsql->query($sql);

	//$n_record = $vmsql->num_rows($query);

	list($IDs_FK) = $vmsql->fetch_row_all($query,true);

	if(count($IDs_FK)>0){

	    $campoPK = RegTools::prendi_PK($tabella);

	    $IDs_FK=$vmsql->recursive_escape($IDs_FK);

	    $sql2="SELECT DISTINCT $campoPK FROM $tabella WHERE $campo_pk_parent IN ('".implode("','",$IDs_FK)."')";

	    Common::rpc_debug($sql2);

	    $q2=$vmsql->query($sql2);

	    list($IDs) = $vmsql->fetch_row_all($q2,true);

	}
	else{

	    $IDs_FK=array();
	}

	return $IDs;

}


/**
 * Funzione RPC per la duplicazione di un record
 *
 * @param array $_pk
 * @param string $tabella
 * @return mixed
 */
function rpc_query_insert_duplicato($_pk,$tabella="",$oid_sub="",$duplica_allegati=0,$duplica_link=0){

	global  $vmsql, $db1;

	// prendi i campi
	list($info_tabella)=RegTools::prendi_colonne_frontend($tabella,'column_name',false);

	list($campo_id, $val_id) = each($_pk);


	for($i=0;$i<count($info_tabella);$i++){
		if($info_tabella[$i]!=$campo_id)  $campi[]=$info_tabella[$i];
	}

	if(count($info_tabella)>count($campi)){

		// test campo numerico
		if(!RegTools::campo_is_numeric($tabella,$campo_id)){

			$val_id="'".$vmsql->escape($val_id)."'";
		}



		// ... vai avanti
		$sql="INSERT INTO $tabella (".implode(",",$campi).") 
			SELECT ".implode(",",$campi)." FROM $tabella WHERE ".$campo_id."=".$val_id."";

		Common::rpc_debug($sql);

		$test=$vmsql->query_try($sql,false);

		$insert_id = (int) $vmsql->insert_id($tabella,$campo_id);


		// TODO: duplicazione dei dati delle sottomaschere

		if($insert_id>0 && strlen(trim($oid_sub))>0){

			$duplicazione_sub_test=array();

			// sottomaschere da duplicare:
			$oid_sub=str_replace("_", "," ,substr($oid_sub,0,-1));

			// prendi le info sottomaschere
			$sql_sub="SELECT * FROM {$db1['frontend']}{$db1['sep']}registro_submask WHERE id_submask in ($oid_sub)";

			$q_sub = $vmsql->query($sql_sub);

			while($RS_sub=$vmsql->fetch_assoc($q_sub)){

				// SQL per i campi NON auto_increment da prendere
				$sql_campi="SELECT sc.column_name 
							FROM {$db1['frontend']}{$db1['sep']}registro_submask_col AS sc
										 WHERE id_submask=".$RS_sub['id_submask']." AND sc.extra!='auto_increment'";

				$q_campi_sub=$vmsql->query($sql_campi);

				list($lista_campi)=$vmsql->fetch_row_all($q_campi_sub,true);

				$duplicazione_sub_test[] = duplica_record_sottomaschera($RS_sub['nome_tabella'],$lista_campi,$RS_sub['campo_fk_sub'],$val_id,$insert_id);
			}



			Common::rpc_debug($sql_sub);

		}


		// DUPLICAZIONE DEGLI ALLEGATI
		if($duplica_allegati){
			$test_allegati=duplica_allegati($tabella,$val_id,$insert_id);
		}

		// DUPLICAZIONE DEi LINK
		if($duplica_link){
			$test_link=duplica_link($tabella,$val_id,$insert_id);
		}

		return $test."|".$insert_id;
	}

	// altrimenti...

	return -1;
}


/**
 * Funzione di duplicazione record
 *
 * @param string $tabella_sub
 * @param array $elenco_campi
 * @param string $campo_fk
 * @param string $valore_fk
 * @param int|string $nuovo_valore
 * @return bool
 */
function duplica_record_sottomaschera($tabella_sub,$elenco_campi,$campo_fk,$valore_fk,$nuovo_valore){

	global  $vmsql;

	$elenco=array();

	for($i=0;$i<count($elenco_campi);$i++){

		if($elenco_campi[$i] != $campo_fk) {
			$elenco[]=$elenco_campi[$i];
		}
	}

	$sql="INSERT INTO ".$tabella_sub. " ($campo_fk, ".implode(",",$elenco).") 
	(SELECT '$nuovo_valore', ".implode("," , $elenco)." FROM $tabella_sub WHERE $campo_fk='$valore_fk') ";

	Common::rpc_debug($sql);
	$test=$vmsql->query_try($sql,false);

	return $test;
}


/**
 * Funzione di duplicazione (su richiesta) degli allegati collegati ad un record. 
 * Duplica i record informativi su DB e fa una copia fisica dei file 
 *
 * @param string $tabella
 * @param int $id_old
 * @param int $id_new
 * @return int
 */
function duplica_allegati($tabella,$id_old,$id_new){

	global  $vmsql;

	$matrice_att= RegTools::prendi_allegati($tabella,$id_old);

	$ok=false;

	if(count($matrice_att)>0){

		$ok=0;

		// copia i file 
		for($i=0;$i<count($matrice_att);$i++){

			$q0=$vmsql->query("BEGIN");

			// copia via SQL
			$q=$vmsql->query("INSERT INTO "._TABELLA_ALLEGATO." (tipoentita, codiceentita, autoreall, lastdata, nomefileall)
							VALUES ('$tabella',
							$id_new, 
							'".$vmsql->escape($_SESSION['user']['nome']." ".$_SESSION['user']['cognome'])."', 
							'".date("Y-m-d H:i:s")."', '".$matrice_att[$i]['nomefileall']."')"
							);

			// se ha inserito il record
			if($vmsql->affected_rows($q)==1){

				$id_new_attach=$vmsql->insert_id(_TABELLA_ALLEGATO,'codiceallegato');

				// copia il file
				if(copy(_PATH_ATTACHMENT."/".$matrice_att[$i]['codiceallegato'].".dat",_PATH_ATTACHMENT."/".$id_new_attach.".dat")){
					$q2=$vmsql->query("COMMIT");
					$ok++;
				}
				else{
					$q2=$vmsql->query("ROLLBACK");
				}

			}
		}
	}

	if($ok===false){

		return 0;
	}
	else if($ok!=count($matrice_att)){
		return -1;
	}
	else {
		return $ok;
	}

}


/**
 * Funzione di duplicazione (su richiesta) dei link collegati ad un record.
 *
 * @param string $tabella
 * @param int $id_old
 * @param int $id_new
 * @return int
 */
function duplica_link($tabella,$id_old,$id_new){

	global $vmreg;

	$matrice_link= RegTools::prendi_link($tabella,$id_old);

	$ok=false;

	if(count($matrice_link)>0){

		$ok=0;

		// copia i file 
		for($i=0;$i<count($matrice_link);$i++){

			// copia via SQL
			$q=$vmreg->query("INSERT INTO "._TABELLA_LINK." (tipoentita, codiceentita, link, lastdata, descrizione)
							VALUES ('$tabella',
							$id_new, 
							'".$vmreg->escape($matrice_link[$i]['link'])."', 
							'".date("Y-m-d H:i:s")."', 
							'".$matrice_link[$i]['descrizione']."')"
							);

			// se ha inserito il record
			if($vmreg->affected_rows($q)==1) $ok++;
		}
	}

	if($ok===false){

		return 0;
	}
	else if($ok!=count($matrice_link)){
		return -1;
	}
	else {
		return $ok;
	}
}


###############################################################################################
#
#	FUNZIONI RPC per le sottomaschere
#
###############################################################################################


/**
 * Funzione di modifica dei record delle sottomaschere. 
 * Restituisce codice SQL
 *
 * @param array $_dati
 * @param string $_str_pk_indipendente
 * @param string $tabella
 * @param string $hash_campo
 * @return string SQL
 */
function rpc_sub_query_update($_dati,$_str_pk_indipendente,$tabella,$hash_campo=''){

	global  $vmsql, $vmreg, $db1;


	$sql_out=array();

	if(!is_array($_dati)){

		return null;
	}


	$sql_auto="SELECT c.column_name 
		FROM {$db1['frontend']}{$db1['sep']}registro_col c , {$db1['frontend']}{$db1['sep']}registro_tab t
		WHERE c.extra='auto_increment'
		AND t.gid=0
		AND t.id_table=c.id_table
		AND t.table_name='$tabella'";

	// Cerca eventuali campi Autoincrement:
	$q_auto=$vmreg->query($sql_auto);

	if($vmreg->num_rows($q_auto)>0){
		list($campo_auto_inc) = $vmreg->fetch_row($q_auto);
	}
	else{
		$campo_auto_inc='';
	}



	foreach($_dati as $k=>$arval){


		$buffer_string="";
		$campi='';
		$valori='';
		$campo='';
		$valore='';
		$campo_pk_indip="";
		$valore_pk_indip="";





		$hash_campo_obj=(isset($hash_campo[$k])) ? unserialize(base64_decode($hash_campo[$k])) : null;

		$info_fields= RegTools::columns_info($tabella, true);

		// NUOVO DATO
		if(!isset($hash_campo[$k])){


			foreach($arval as $campo=>$valore){

				// non mettere i campi auto_increment se ci dovessero essere
				if($campo!=$campo_auto_inc){

					$campi.=$campo.",";

					if($db1['dbtype']=='postgres' && $info_fields[$campo]['type']=='numeric'){
						$valori.= $vmsql->escape($valore).",";
					}
					else if($db1['dbtype']=='postgres' && $info_fields[$campo]['type']=='bool'){
					    $valori.= (trim($valore)=='null') ? "'f'," : "'t',";
					}
					else{
						$valori.="'".$vmsql->escape($valore)."',";
					}
				}
			}


			list($campo_pk_indip,$valore_pk_indip) = explode("=",$_str_pk_indipendente);

			// accodo i dati della pk indipendente se non � auto_increment
			if($campo_pk_indip!=$campo_auto_inc){
				$campi.=$campo_pk_indip;
				$valori.=$valore_pk_indip;
			}
			else{
				$campi=substr($campi,0,-1);
				$valori=substr($valori,0,-1);
			}

			$buffer_string="INSERT INTO $tabella ($campi) VALUES ($valori)";

		}	//-- fine INSERT



		// DATO DA MODIFICARE
		else{


		    if(is_array($hash_campo) && count($hash_campo_obj)>0){

			    $WHERE='WHERE 1=1 ';

			    foreach($hash_campo_obj as $campo_h=>$valore_h){

				    if($valore_h!=''){

					//if($db1['dbtype']=='postgres' && campo_is_numeric($tabella,$campo_h)){
					if($db1['dbtype']=='postgres'&& $info_fields[$campo_h]['type']=='numeric'){
						$WHERE.=" AND $campo_h=".$vmsql->escape($valore_h);
					}
					else{
					    $WHERE.=" AND $campo_h='".$vmsql->escape($valore_h)."'";
					}

				    }
				    else{

					if($db1['dbtype']=='postgres' && in_array($info_fields[$campo_h]['type'], array('numeric','bool', 'date'))){
					    $WHERE.=" AND ($campo_h IS NULL)";
					}
					else{
					    $WHERE.=" AND ($campo_h IS NULL OR $campo_h='')";
					}
				    }
			    }
		    }

		    // PREVENGO LA CREAZIONE DI UN UPDATE SENZA CONDIZIONI
		    else return '';


		    $buffer_string="UPDATE $tabella SET ";

		    foreach($arval as $campo=>$valore){

			    if($db1['dbtype']=='postgres' && RegTools::campo_is_numeric($tabella,$campo)){
				    $buffer_string.=" $campo=".$vmsql->escape($valore).",";
			    }
			    else{
				if(trim($valore)==''){

				    if($db1['dbtype']=='postgres' && in_array($info_fields[$campo]['type'], array('numeric','bool', 'date'))){
                        $buffer_string.=" $campo=NULL,";
				    }
				    else{
                        $buffer_string.=" $campo='',";
				    }
				}
				else{

				    if($info_fields[$campo]['type']=='bool' && $valore=='null'){
					$buffer_string.=" $campo=NULL,";
				    }
				    else{
					$buffer_string.=" $campo='".$vmsql->escape($valore)."',";
				    }
				}
			    }
		    }

		    $buffer_string=substr($buffer_string,0,-1);

		    if(!isset($WHERE) || $WHERE=='WHERE 1=1 '){
			    return '';
		    }

		    $buffer_string.=" $WHERE ";


		} //-- fine UPDATE


		$sql_out[]=$buffer_string;



	} // -- fine ciclo


	return $sql_out;

}


/**
 * Funzione per la cancellazione di un record in sottomaschera. 
 * Restituisce il codice SQL
 *
 * @param string $tabella
 * @param string $hash
 * @return string SQL
 */
function rpc_sub_query_delete($tabella,$hash){

	global $vmsql;

	for($i=0;$i<count($hash);$i++){

		$hash_campo_obj=unserialize(base64_decode($hash[$i]));

		$WHERE='WHERE 1=1 ';
                $WHERE_ADD='';

		if(is_array($hash_campo_obj)){

			foreach($hash_campo_obj as $campo_h=>$valore_h){

				if($valore_h!=''){

					$WHERE_ADD.=" AND $campo_h='".$vmsql->escape($valore_h)."'";

				}
				else{

					$WHERE_ADD.=" AND ($campo_h='".$vmsql->escape($valore_h)."' OR $campo_h IS NULL)";
				}
			}


		}


                if($WHERE_ADD!=''){
                    $sql_out[]="DELETE FROM $tabella $WHERE $WHERE_ADD ";
                }
                else{
                    $sql_out[]=''; 
                }
	}

	return $sql_out;

}


/**
 * Funzione di cancellazione di un allegato di un record.
 *
 * @param string $tabella
 * @param int $id
 * @return bool
 */
function rpc_delete_attach($tabella,$id){


	global  $vmreg, $db1;

	$sql_att="SELECT codiceallegato FROM "._TABELLA_ALLEGATO." WHERE codiceentita='$id' AND tipoentita='$tabella'";

	$q_att = $vmreg->query($sql_att);

	list($array_codice_allegati)=$vmreg->fetch_row_all($q_att,true);

	$test_del_fs=null;
	$test_del=null;

	for($i=0;$i<count($array_codice_allegati);$i++){

		// elimino dal filesystem
		$test_del_fs=@unlink(_PATH_ATTACHMENT."/".$array_codice_allegati[$i].".dat");

		// elimino dal db
		$sql_del="DELETE FROM "._TABELLA_ALLEGATO." WHERE codiceallegato=".$array_codice_allegati[$i];
//		Common::rpc_debug($sql_del);
		$test_del=$vmreg->query($sql_del);


	}

	 return ($test_del_fs && $test_del) ? true:false;
}


/**
 * Funzione di cancellazione di un link di una scheda.
 *
 * @param string $tabella
 * @param int $id
 * @return bool
 */
function rpc_delete_link($tabella,$id){

	global $vmreg, $db1;

	$sql_link = "SELECT codicelink FROM "._TABELLA_LINK." WHERE codiceentita='$id' AND tipoentita='$tabella'";

	$test_del=null;

	$q_link = $vmreg->query($sql_link);

	list($array_codice_link)=$vmreg->fetch_row_all($q_link,true);

	for($i=0;$i<count($array_codice_link);$i++){

		// elimino dal db
		$sql_del="DELETE FROM "._TABELLA_LINK." WHERE codicelink=".$array_codice_link[$i];
//		Common::rpc_debug($sql_del);
		$test_del=$vmreg->query($sql_del);

	}

	 return $test_del;

}
