<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of class
 *
 * @author marcello
 */
class RegTools {
    

    /**
     * Da l'oid di tabella restituisce il nome della stessa
     * 
     * @param int $oid
     * @param bool $solo_visibili
     * @return string
     */
    static public function oid2name($oid=0,$solo_visibili=false){

        global $vmreg, $db1;

        $oid= (int) $oid;

        if($oid>0){

            $sql_vis = ($solo_visibili) ? "AND visibile=1" : "";

            $q=$vmreg->query("SELECT table_name
                            FROM {$db1['frontend']}{$db1['sep']}registro_tab
                            WHERE id_table=$oid
                            $sql_vis
                            LIMIT 1");

            if($vmreg->num_rows($q)==1){

                list($nome_tabella) = $vmreg->fetch_row($q);

                return $nome_tabella;
            }
            else{

                return 0;
            }
        }
        else{

            return 0;	
        }
    }




    /**
     * Da l'oid di tabella restituisce il nome della stessa
     * 
     * @param int $oid
     * @param bool $solo_visibili
     * @return string
     */
    static public function oidsub2name($oid=0,$solo_visibili=false){

        global $vmreg, $db1;

        $oid= (int) $oid;

        if($oid>0){

            $sql_vis = ($solo_visibili) ? "AND sub_select=1" : "";

            $q=$vmreg->query("SELECT nome_tabella
                            FROM {$db1['frontend']}{$db1['sep']}registro_submask
                            WHERE id_submask=$oid
                            $sql_vis
                            LIMIT 1");

            if($vmreg->num_rows($q)==1){

                list($nome_tabella) = $vmreg->fetch_row($q);

                return $nome_tabella;
            }
            else{

                return 0;
            }
        }
        else{

            return 0;	
        }
    }



    /**
     * Da nome tabella e gruppo restituisce l'id tabella (oid)
     *
     * @param string $name
     * @param int $gid
     * @return int
     */
    static public function name2oid($name,$gid=0){

        global $vmreg, $db1;

        $gid= (int) $gid;


        $q=$vmreg->query("SELECT id_table
                FROM {$db1['frontend']}{$db1['sep']}registro_tab
                WHERE table_name='$name'
                AND gid=$gid
                LIMIT 1");

        if($vmreg->num_rows($q)==1){

            list($oid) = $vmreg->fetch_row($q);

            return $oid;
        }
        else{

            return 0;
        }
    }

    /**
     * Prendi le tabelle presenti nel registro
     *
     * @param int $gid default 0
     * @param bool $solo_visibili
     * @param bool $only_base_table
     * @param bool $extended
     * @return array
     */
    static public function prendi_tabelle($gid=0,$solo_visibili=false, $only_base_table=false, $extended=false){

        global $vmreg, $db1;

            $fields = ($extended===false) ? "id_table, table_name" : "*";

            $sql_vis = ($solo_visibili) ? "AND visibile=1" : "";

            $sql_views = ($only_base_table) ? "AND (table_type='BASE TABLE' OR table_type='TABLE')" : '';

            $gid= (int) $gid;

            $sql="SELECT $fields , coalesce(table_alias, table_name) as table_alias2
                FROM {$db1['frontend']}{$db1['sep']}registro_tab
                WHERE gid=$gid
                $sql_vis
                $sql_views
                ORDER BY table_alias2, table_name";

            $q=$vmreg->query($sql);

            if($vmreg->num_rows($q)>0){

                return $vmreg->fetch_assoc_all($q);
            }
            else{
                return array();
            }
    }
    
    static public function index_group($gid){
        
        global $vmreg, $db1;
        
        $sql="SELECT id_table FROM {$db1['frontend']}{$db1['sep']}registro_tab WHERE gid=".intval($gid);
        $q=$vmreg->query($sql);
        list($ids_table) = $vmreg->fetch_row_all($q, true);
        
        return $ids_table;
    }

    /**
     * Prendi le viste presenti nel registro
     *
     * @param bool $solo_visibili
     * @param bool $extended
     * @return array
     */
    static public function prendi_viste($gid=0,$solo_visibili=false,$extended=false){

        global $vmreg, $db1;

            $fields = ($extended===false) ? "id_table, table_name" : "*";

            $sql_vis = ($solo_visibili) ? "AND visibile=1" : "";

            $gid= (int) $gid;

            $q=$vmreg->query("SELECT $fields , coalesce(table_alias, '') as table_alias2
                            FROM {$db1['frontend']}{$db1['sep']}registro_tab
                            WHERE gid=$gid
                            $sql_vis
                            AND table_type='VIEW'
                            ORDER BY table_alias2, table_name");

            if($vmreg->num_rows($q)>0){

                return $vmreg->fetch_assoc_all($q);
            }
            else{
                return array();
            }
    }




    /**
     * Prendi info della tabella/vista presente nel registro
     *
     * @param bool $solo_visibili
     * @return array
     */
    static public function prendi_info_tabella($oid_or_name=0,$fields="*",$solo_visibili=false,$gid='session'){

        global $vmreg, $db1;

            $sql_vis = ($solo_visibili) ? "AND visibile=1" : "";

            $sql_vis = ($solo_visibili) ? "AND in_visibile=1" : "";

            // e' oid?
            if(is_numeric($oid_or_name)){

                $oid=intval($oid_or_name);

                $clausola_ID = "AND t.id_table=$oid";

            }
            // otherwise table_name... need GID
            else{

                if($gid=="session"){
                    $gid= (int) $_SESSION['gid'];
                }
                else{
                    $gid= (int) $gid;
                }

                $clausola_ID = "AND t.table_name='$oid_or_name'
                                AND t.gid=$gid	";
            }

            $q=$vmreg->query("SELECT $fields
                            FROM {$db1['frontend']}{$db1['sep']}registro_tab t
                            WHERE 1=1
                            $sql_vis
                            $clausola_ID
                            ORDER BY table_name");

            if($vmreg->num_rows($q)>0){

                return $vmreg->fetch_assoc($q);
            }
            else{
                return array();
            }
    }


    /**
     * Prendi le informazioni sui campi di una data tabella dal nome oppure dall'oid per un dato gruppo
     *
     * @param mixed $oid_or_name Nome della tabella
     * @param string $campi Campi della tabella che si vogliono recuperare, separati da virgola (se pi� di uno)
     * @param bool $solo_visibili Prende solo le tabelle visibili (diritto SELECT) dal gruppo specificato
     * @param int $gid Gruppo secondo il quale recuperare le informazioni sulle tabelle. Se non specificato � il gruppo in sessione
     * @param string $restype row | assoc
     * @return matrix Matrice di risultati SQL
     */
    static public function prendi_colonne_frontend($oid_or_name,$campi="*",$solo_visibili=true,$gid="session",$restype='row'){

        global $vmreg, $db1;

        $sql_vis = ($solo_visibili) ? "AND in_visibile=1" : "";

        // e' oid?
        if(is_numeric($oid_or_name)){

            $oid=intval($oid_or_name);
            $clausola_ID = "AND t.id_table=$oid";
        }
        // otherwise table_name... need GID
        else{

            $gid = ($gid=="session") ? intval($_SESSION['gid']) : intval($gid);
            $clausola_ID = "AND t.table_name='$oid_or_name' AND t.gid=$gid	";
        }

        $sql="SELECT $campi
                FROM {$db1['frontend']}{$db1['sep']}registro_col c, {$db1['frontend']}{$db1['sep']}registro_tab t
                WHERE c.id_table=t.id_table
                $clausola_ID
                $sql_vis
                ORDER BY c.in_ordine, c.ordinal_position";

        $q_info = $vmreg->query($sql);


        $matrice = ($restype=='row') ? $vmreg->fetch_row_all($q_info,true)
                                    : $vmreg->fetch_assoc_all($q_info);

        return $matrice;
    }






    /**
     * Funzione di test per verificare l'esistenza di una tabella 
     *
     * @param string $tabella
     * @return bool
     */
    static public function is_tabella($tabella){

        global  $vmreg, $db1;

        $q=$vmreg->query("SELECT id_table FROM {$db1['frontend']}{$db1['sep']}registro_tab WHERE table_name='$tabella' ");

        return ($vmreg->num_rows($q)>0) ? true:false;
    }



    /**
     * Funzione di test per verificare l'esistenza di una tabella attraverso l'oid
     *
     * @param int $oid
     * @return bool
     */
    static public function is_tabella_by_oid($oid,$only_visibile=false){

        global $vmreg, $db1;

        $only_vis = ($only_visibile) ? ' AND visibile=1' : '';

        $q=$vmreg->query("SELECT id_table FROM {$db1['frontend']}{$db1['sep']}registro_tab WHERE id_table=".intval($oid) . $only_vis);

        return ($vmreg->num_rows($q)>0) ? true:false;

    }

    /**
     * Funzione di test per verificare l'esistenza di un campo di tabella 
     *
     * @param string $campo
     * @return bool
     */
    static public function is_campo($campo){

        global $vmreg, $db1;

        $q=$vmreg->query("SELECT column_name FROM {$db1['frontend']}{$db1['sep']}registro_col WHERE column_name='$campo' ");

        return ($vmreg->num_rows($q)>0) ? true:false;

    }


    /**
     * Data una tabella ed un opzionale campo, restituisce le tabelle che sono ad essa collegate e dipendenti
     * tramite un'analisi dei campi chiavi esterne.
     *
     * @param string $tabella
     * @param string $campo
     * @return matrix
     */
    static public function ref_campo($tabella){

        $IS = new iSchema();
        $matrice_ref=$IS->get_referenced($tabella);
        return $matrice_ref;
    }



    /**
     * Prende le FK 
     * Individua i campi coinvolti in una relazione esterna e tabella.campo a cui � legato
     * Funziona sia passando l'oid numerico che una stringa che rappresenta il nome di tabella + il gruppo
     *
     * @param mixed $tab
     * @param int $gid
     * @return array
     */
    static public function prendi_FK($tab,$gid=0){

        global $vmreg, $db1;	


        // se $tab � un numero � l'oid
        // altrimenti il nome tabella
        if(is_numeric($tab)){
            $table= self::oid2name($tab);
        }
        else{
            $table=$tab;
        }


        $IS = new iSchema();

        $FKa= $IS->get_foreign_keys($table);

        if(isset($FKa[0])){

            $n_rows= count($FKa);

            $FK=$vmreg->reverse_matrix($FKa);

            $mat=array($FK['column_name'],$FK['references']);

            return $mat;
        }
        else{
            return array(array(),array());
        }

    }



    /**
     * Sostituisce lato server alcune variabili specificate nell'impostazione dei campi da parte dell'amministratore 
     * e che possono essere inserite ad esempio nei campi hidden e restituisce il valore
     *
     * @param string $variabile
     * @return string
     */
    static public function variabili_campi($variabile){

        $variabile=trim($variabile);

        switch ($variabile){

            case '%uid' : $out= (isset($_SESSION['user']['uid'])) ? $_SESSION['user']['uid'] : false; break;
            case '%nick' : $out= (isset($_SESSION['user']['nick'])) ? $_SESSION['user']['nick'] : false; break;
            case '%email' : $out= (isset($_SESSION['user']['email'])) ? $_SESSION['user']['email'] : false; break;
            case '%gid' : $out= (isset($_SESSION['gid'])) ? $_SESSION['gid'] : false; break;
            case '%gruppo' : $out= (isset($_SESSION['gid'])) ? Common::gid2group_name($_SESSION['gid']) : false; break;
            case '%nome' : 
            case '%name' : $out= (isset($_SESSION['user']['nome'])) ? $_SESSION['user']['nome'] : false; break;
            case '%cognome' : 
            case '%surname'	: $out= (isset($_SESSION['user']['cognome'])) ? $_SESSION['user']['cognome'] : false; break;
            case '%nomecognome' : 
            case '%namesurname' : $out= (isset($_SESSION['user']['nome']) && isset($_SESSION['user']['cognome'])) 
                 ? $_SESSION['user']['nome']." ".$_SESSION['user']['cognome'] : false; break;

            case '%nomecognome' : 
            case '%surnamename' : $out= (isset($_SESSION['user']['cognome']) && isset($_SESSION['user']['nome'])) 
                 ? $_SESSION['user']['cognome']." ".$_SESSION['user']['nome'] : false; break;

            case '%now' : $out= date('Y-m-d'); break;
            case '%timestamp' : $out= date('Y-m-d H:i:s'); break;

            default: $out=false;	
        }

        return ($out!=false && $out!='') ? $out : false;

    }



    /**
     * Funzione che, interrogando l'information_schema, recupera la chiava primaria di una tabella
     *
     * @param string $tabella
     * @param int $gid
     * @return string
     */
    static public function prendi_PK($tabella,$gid='session'){

        global $vmreg, $db1;

        if($gid=='session'){

            $gid = (int) $_SESSION['gid'];
        }
        else{
            $gid= (int) $gid;
        }

        // E' tabella o vista?
        $sql_tipo="SELECT table_type FROM {$db1['frontend']}{$db1['sep']}registro_tab 
                             WHERE table_name='$tabella' AND gid=$gid";
        $q_tipo=$vmreg->query($sql_tipo);
        list($tipo_tab)=$vmreg->fetch_row($q_tipo);

        if($tipo_tab=='VIEW'){

            // Chiave primaria esplicita delle viste
            $sql_pk = "SELECT view_pk AS campo_pk 
                    FROM {$db1['frontend']}{$db1['sep']}registro_tab rb 
                    WHERE rb.table_name='$tabella'
                    AND rb.gid=$gid
                    ";

            $q=$vmreg->query($sql_pk);

            list($campoPK)=$vmreg->fetch_row($q);

        }
        else{


            $IS = new iSchema();	
            $campoPK=$IS->get_primary_keys($tabella);
            if(is_array($campoPK)) $campoPK=$campoPK[0];

        }


        return $campoPK;
    }



    /**
     * Funzione che recupera tutte le chiavi primarie di una tabella.
     * Funziona come prendi_PK() ma restituisce una o più chiavi sotto forma di array
     * Da utilizzare quando si presume che le PK possano essere più di una
     *
     * @param string $tabella
     * @param int $gid
     * @return array
     */
    static public function prendi_all_PK($tabella,$gid='session'){

        global $vmreg, $db1;

        if($gid=='session'){

            $gid = (int) $_SESSION['gid'];
        }
        else{
            $gid= (int) $gid;
        }

        // E' tabella o vista?
        $q_tipo=$vmreg->query("SELECT table_type FROM {$db1['frontend']}{$db1['sep']}registro_tab
                             WHERE table_name='$tabella' AND gid=$gid");
        list($tipo_tab)=$vmreg->fetch_row($q_tipo);



        if($tipo_tab=='VIEW'){

            // Chiave primaria esplicita delle viste
            $sql_pk = "SELECT view_pk AS campo_pk 
                    FROM {$db1['frontend']}{$db1['sep']}registro_tab rb 
                    WHERE rb.table_name='$tabella'
                    AND rb.gid=$gid
                    ";
            $q=$vmreg->query($sql_pk);

            $campoPK=$vmreg->fetch_assoc_all($q);
        }
        else{

            $IS = new iSchema();
            $campoPK= $IS->get_primary_keys($tabella);
            if(is_string($campoPK)) $campoPK=array($campoPK);

        }


        return $campoPK;
    }



    static public function prendi_all_PK_submask_oid($id_submask,$gid='session'){

        global $vmreg, $db1;

        if($gid=='session'){

            $gid = (int) $_SESSION['gid'];
        }
        else{
            $gid= (int) $gid;
        }

        $IS=new iSchema();
        $campoPK=$IS->get_primary_keys( self::oidsub2name($id_submask));

        if(is_string($campoPK)) $campoPK=array($campoPK);

        return $campoPK;
    }


    /**
     * Prende la chiave primaria di una tabella in base all'OID (Id della tabella) passato
     *
     * @param int $oid
     * @param string $table_type "BASE TABLE" | "VIEW"
     * @return array
     */
    static public function prendi_PK_oid($oid,$table_type='BASE TABLE'){

        global $vmreg, $db1;

        $oid=(int) $oid;

        if($table_type=='VIEW'){

        // Chiave primaria esplicita delle viste
            $sql_pk = "SELECT view_pk AS campo_pk 
                    FROM {$db1['frontend']}{$db1['sep']}registro_tab rb 
                    WHERE rb.id_table=$oid
                    ";
            $q_pk=$vmreg->query($sql_pk);

            list($PK)=$vmreg->fetch_row_all($q_pk,true);

        }
        else{
            $table = self::oid2name($oid);
            $PK= self::prendi_all_PK($table);
        }

        return $PK;
    }


    /**
     * Prende le sottomaschere impostate per una data maschera data l'ID (oid) del record
     *
     * @param int $oid
     * @param bool $reverse
     * @param bool $solo_visibili
     * @return array
     */
    static public function prendi_sottomaschere($oid,$reverse=false,$solo_visibili=false){

        global $db1, $vmreg;

        $clausola_solo_visibili = ($solo_visibili) ? "AND sub_select='1' " : "";

        $sql = "SELECT * FROM ".$db1['frontend'].$db1['sep']."registro_submask
                WHERE id_table=$oid
                $clausola_solo_visibili
                ORDER BY nome_tabella
                ";

        $q=$vmreg->query($sql);

        $matrice = ($vmreg->num_rows($q)>0) ? $vmreg->fetch_assoc_all($q,$reverse) : array();

        return $matrice;

    }




    /**
     * Restituisce unamatrice bidimensionale (gid=>nome_gruppo) dei gruppi presenti in DB VFront
     * Utile per (ad esempio) mostrare una tendina
     * Si possono escludere dei gruppi mediante il parametro $escludi_gid
     *
     * @param mixed $escludi_gid
     * @return array
     */
    static public function prendi_gruppi($escludi_gid=''){

        global $vmreg, $db1;


        $mat_gruppi=array();

        $clausola_esclusione = ($escludi_gid!='') ? " WHERE gid!=".intval($escludi_gid) : '';

        $q_g=$vmreg->query("SELECT gid, nome_gruppo FROM ".$db1['frontend'].$db1['sep']."gruppo $clausola_esclusione ORDER BY gid");

        if($vmreg->num_rows($q_g)>0)	$mat_gruppi=$vmreg->fetch_assoc_all($q_g);

        return $mat_gruppi;
    }


    static public function prendi_K_relazione_sub($id_table, $result_type='row'){

        global $vmreg, $db1;

        $sql="SELECT campo_pk_parent, campo_fk_sub , nome_tabella, id_submask 
            FROM {$db1['frontend']}{$db1['sep']}registro_submask 
            WHERE id_table=".intval($id_table);
            
        $q=$vmreg->query($sql);

        if($result_type == 'row'){
            return ($vmreg->num_rows($q)>0) ? $vmreg->fetch_row_all($q) : array();
        }
        else{
            return ($vmreg->num_rows($q)>0) ? $vmreg->fetch_assoc_all($q) : array();
        }
    }




    /**
     * Recupera i file allegati associati ad un dato record di una data tabella
     * presenti nella tabella "allegato" sotto forma di matrice
     *
     * @param string $tabella
     * @param mixed $valore_id
     * @return array
     */
    static public function prendi_allegati($tabella,$valore_id){

        global $vmreg, $db1;

        $sql="SELECT * FROM "._TABELLA_ALLEGATO." WHERE tipoentita='$tabella' AND codiceentita='$valore_id'";
        $q=$vmreg->query($sql);

        if($vmreg->num_rows($q)>0){

            return $vmreg->fetch_assoc_all($q);
        }
        else{

            return array();
        }

    }



    /**
     * Recupera i link associati al record di una data tabella 
     * dalla tabella "Link" sotto forma di matrice
     *
     * @param string $tabella
     * @param mixed $valore_id
     * @return array
     */
    static public function prendi_link($tabella,$valore_id){

        global $vmreg;

        $sql="SELECT * FROM "._TABELLA_LINK." WHERE tipoentita='$tabella' AND codiceentita='$valore_id'";
        $q=$vmreg->query($sql);

        if($vmreg->num_rows($q)>0){

            return $vmreg->fetch_assoc_all($q);
        }
        else{

            return array();
        }

    }


    /**
     * Cancella dal DB e dal filesystem un allegato
     *
     * @param int $id_allegato
     * @return bool
     */
    static public function elimina_allegato($id_allegato=null){

        global $vmreg, $db1;

        // elimino dal file system
        $test= @unlink(_PATH_ATTACHMENT."/".$id_allegato.".dat");

        if($test){

            $q=$vmreg->query("DELETE FROM "._TABELLA_ALLEGATO." WHERE codiceallegato='$id_allegato' ");

            return true;

        }else return false;

    }



    /**
     * Funzione per la determinazione del tipo di campo
     * Utile soprattutto per PostgreSQL
     *
     * @param mixed $tabella_o_gid
     * @param string $campo
     * @return bool
     */
    static public function campo_is_numeric($tabella_o_gid,$campo){

        global $vmreg, $db1;

        if(is_numeric($tabella_o_gid)){

            $id_table=$tabella_o_gid;
        }
        else{

            $id_table= self::name2oid($tabella_o_gid,$_SESSION['gid']);
        }


        $sql="SELECT column_type FROM {$db1['frontend']}{$db1['sep']}registro_col 
              WHERE id_table=$id_table AND column_name='$campo'";

        $q=$vmreg->query($sql);

        if($vmreg->num_rows($q)>0){
            list($tipo)=$vmreg->fetch_row($q);

            if(preg_match("/^(tinyint|mediumint|int|double|numeric|float|decimal)/i",$tipo))
                return true;
            else 
                return false;
        }
        else return false;
    }



    /**
     * Check if the field is a autoincrement
     *
     * @param string $table
     * @param string $field
     * @return bool
     */
    static public function is_autoincrement($table,$field){

        $IS=new iSchema();

        if($IS->is_view($table)){

            $parent_table = $IS->show_view_table_ref($table);

            $def_col=$IS->get_columns($parent_table,$field);

            if(isset($def_col[0]['extra']) && $def_col[0]['extra']=='auto_increment'){

                return true;
            }
            else{
                return false;
            }
        }
        else{

            $def_col=$IS->get_columns($table,$field);

            if(isset($def_col[0]['extra']) && $def_col[0]['extra']=='auto_increment'){

                return true;
            }
            else{
                return false;
            }

        }
    }


    static public function allegato_filesize($id_allegato){

        return self::formatBytes(filesize(_PATH_ATTACHMENT."/".$id_allegato.".dat"));
    }


    static public function formatBytes($bytes, $precision = 2) {
        $units = array('B', 'KB', 'MB', 'GB', 'TB');

        $bytes = max($bytes, 0);
        $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
        $pow = min($pow, count($units) - 1);

        $bytes /= pow(1024, $pow);

        return round($bytes, $precision) . ' ' . $units[$pow];
    } 


    static public function prendi_pulsanti($id_table){

        global $db1, $vmreg;

        $q=$vmreg->query("SELECT * FROM {$db1['frontend']}{$db1['sep']}button
                        WHERE id_table=".intval($id_table)."
                        order by button_name");

        $matrice = ($vmreg->num_rows($q)>0) ? $vmreg->fetch_assoc_all($q) : array();

        return $matrice;
    }

    static public function uid2name($uid){

        global $vmreg, $db1;

        $q=$vmreg->query("SELECT nome, cognome FROM {$db1['frontend']}{$db1['sep']}utente WHERE id_utente=".intval($uid));

        $RS=$vmreg->fetch_assoc($q);

        return $RS;
    }



    /**
     * Funzione per recuperare le impostazioni del gruppo attuale per la tabella in oggetto
     * Restituisce SQL con eventuali subquery
     *
     * @param string $nome_tabella
     * @param bool $only_visibile
     * @todo Verificare la regexp
     * @return string (sql)
     */
    static public function campi_elaborati($nome_tabella,$only_visibile=true){
        
        require_once(FRONT_ROOT."/plugins/php-sql-parser/src/PHPSQLParser.php");
        
        $Parser = new PHPSQLParser();
        
        $fields ="c.column_name, c.data_type, "
                ."c.in_tipo, c.in_default, t.orderby, t.orderby_sort";

        $matrice_info= self::prendi_colonne_frontend($nome_tabella, $fields, $only_visibile,"session","assoc");

        $campi='';
        $ORDERBY='';

        foreach($matrice_info as $k=>$info){

            if($k==0){
                $ORDERBY = ($info['orderby']!='') ? "ORDER BY ".$info['orderby']." ".$info['orderby_sort'] : "";
            }

            if($info['in_tipo']=="select_from" && trim($info['in_default'])!='' ){
                
                $prsql = $Parser->parse($info['in_default']);

                //$reg_exp = "|SELECT ([\w.]+) ?, ?([\w.() ,' -]+) +FROM +([\w]+).*?|i";
                //preg_match($reg_exp,$info['in_default'],$campo_k);
                
                if(count($prsql['SELECT']) == 1){
                    $f1 = $f2 = $prsql['SELECT'][0]['base_expr'];
                }
                else{
                    $f1 = $prsql['SELECT'][0]['base_expr'];
                    $f2 = $prsql['SELECT'][1]['base_expr'];
                }

                //$campi.=" (SELECT {$campo_k[2]} FROM {$campo_k[3]} t$k WHERE t$k.{$campo_k[1]}=t.{$info['column_name']}) ".$info['column_name'].",";
                $campi.=" (SELECT $f2 FROM {$prsql['FROM'][0]['table']} t$k WHERE t$k.$f1=t.{$info['column_name']}) ".$info['column_name'].",";
                
            }
            else{

                $campi.=" t.".$info['column_name'].",";
            }
        }

        $campi=substr($campi,0,-1);

        return $campi;
    }



    /**
     * Funzione che recupera l'ordinamento impostato in una tabella data
     *
     * @param string $tabella
     * @param int $gid
     * @return string
     */
    static public function prendi_orderby($tabella,$gid=0){

        global $vmreg, $db1;

        // Prendi il campo di ordinamento dalla tabella:
        $q_orderby = $vmreg->query("SELECT orderby , orderby_sort FROM {$db1['frontend']}{$db1['sep']}registro_tab WHERE table_name='$tabella' AND gid=$gid ");

        list($orderby,$orderby_sort) = $vmreg->fetch_row($q_orderby);


        // se non è stato impostato un orderby prende la chiave primaria
        if($orderby==''){

            $orderby= self::prendi_PK($tabella);
        }



        // orderby e orderby_sort possono essere valori oppure liste separate da virgola
        $orderby_a=explode(",",$orderby);
        $orderby_sort_a=explode(",",$orderby_sort);

        $string_orderby='';

        for($i=0;$i<count($orderby_a);$i++){

            $orderby_sort_a_string=(isset($orderby_sort_a[$i]) && $orderby_sort_a[$i]!='') ? $orderby_sort_a[$i] : "ASC";

            $string_orderby.=$orderby_a[$i]." ".$orderby_sort_a_string.",";
        }

        return substr($string_orderby,0,-1);
    }


    static public function columns_info($table, $is_submask=false){

        global $vmreg, $db1;

        $id_table= self::name2oid($table);

        $sql="SELECT c.column_name, c.column_type, c.is_nullable 
            FROM {$db1['frontend']}{$db1['sep']}registro_col c
            WHERE id_table=".intval($id_table);

        $q=$vmreg->query($sql);

        $res=array();

        while($RS=$vmreg->fetch_assoc($q)){

        $res[$RS['column_name']]['is_nullable']=$RS['is_nullable'];

        if(preg_match("/^(tinyint|mediumint|int|bigint|double|numeric|float|decimal|serial)/i",$RS['column_type'])){

            $res[$RS['column_name']]['type']='numeric';
        }
        else if(preg_match("/^(timestamp|date|interval)/i",$RS['column_type'])){
            $res[$RS['column_name']]['type']='date';
        }
        else if($RS['column_type']=='bool'){

            $res[$RS['column_name']]['type']='bool';
        }
        else{

            $res[$RS['column_name']]['type']=$RS['column_type'];
        }
        }

        return $res;
    }

}
