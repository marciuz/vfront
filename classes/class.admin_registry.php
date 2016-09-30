<?php
/**
 * Sono qui presenti numerose funzioni per la gestione del registro di VFront. 
 * 
 * @desc Libreria di funzioni per la gestione del registro di VFront
 * @package VFront
 * @subpackage Administration
 * @author M.Marcello Verona
 * @copyright 2007-2014 M.Marcello Verona
 * @version 0.99 $Id:$
 * @license http://www.gnu.org/licenses/gpl.html GNU Public License 
 */


class Admin_Registry {
    
    
    static public function get_default_filters_ops($op=null){
        
        $ops = array(
            'equal'=>'=',
            'not_equal'=>'!=',
            'maj'=>'>',
            'maj_equal'=>'>=',
            'min'=>'<',
            'min_equal'=>'<=',
            'like'=>'LIKE',
            'not_like'=>'NOT LIKE',
            'is_null'=>'IS NULL',
            'is_not_null'=>'IS NOT NULL',
        );
        
        if($op === null){
            return $ops;
        }
        else{
            return (isset($ops[$op])) ? $ops[$op] : null;
        }
        
    }


    /**
     * Funzione di inserimento di una tabella nel registro. 
     * Questa funzione scrive nelle tabelle registro_tab e registro_col
     * informazioni su una tabella del database, recuperate a sua volta dall'information_schema
     *
     * @param string $val Nome della tabella
     * @param int $gid Identificativo del gruppo
     * @return void
     */
    static public function inserisci_registro($val,$gid=0){

        global $vmreg, $db1;

        // Verifica che non ci siano già record per questo gruppo
        $test_gid=$vmreg->test_id("gid", $gid, "{$db1['frontend']}{$db1['sep']}registro_tab", "AND table_name='{$val['table_name']}'");

        if($test_gid){
            return null;
        }

        $RT = new RegTable_Admin();
        $RT->gid = (int) $gid;
        $RT->table_name = $val['table_name'];
        $RT->table_type = $val['table_type'];
        $RT->commento = $val['comment'];

        $ID_TABLE = $RT->save();
        

        #########################
        #
        #	Table details
        #	
        $IS=new iSchema();
        $matrice1=$IS->get_columns($val['table_name']);


        foreach ($matrice1 as $k=>$valori){

            $gid = (int) $gid;

            $RC = new RegColumn_Admin();

            $RC->gid=$gid;
            $RC->id_table=$ID_TABLE;
            $RC->column_name=$valori['column_name'];
            $RC->ordinal_position=$valori['ordinal_position'];
            $RC->column_default=$valori['column_default'];
            $RC->is_nullable=$valori['is_nullable'];
            $RC->column_type=$valori['column_type'];
            $RC->character_maximum_length=$valori['character_maximum_length'];
            $RC->data_type=$valori['data_type'];
            $RC->extra=$valori['extra'];
            $RC->commento=$valori['column_comment'];

            $RC->save();

        }
    }





    /**
     * Funzione di clonazione registro. 
     * Prende le impostazioni del gruppo $gid_old (il gruppo default = 0)
     * e le applica al nuovo gruppo $gid_new
     *
     * @param int $gid_new ID del nuovo gruppo
     * @param int $gid_old ID del gruppo origine
     * @return bool Esito dell'operazione
     */
    static public function clona_settaggio($gid_new,$gid_old=0){

        $ids_tables = RegTools::index_group($gid_old);
        $t=0;
        
        foreach($ids_tables as $id_table){
            
            $T0 = new RegTable_Admin();
            $T0->load($id_table);
            
            $T1 = clone $T0;
            $T1->gid=$gid_new;
            $T1->save();
            
            if($T1->id_table > 0){
                
                foreach($T0->columns as $C){
                    $C2 = $C->clona($T1->id_table, $T1->gid);
                    $C2->save();
                }
                
                $t++;
            }
        }
        
        return (count($ids_tables) == $t) ? true: false;
    }



    /**
     * Dato un gid crea un registro tabelle per quel gruppo partendo da zero. 
     * Come inizializza registro ma non crea il record nel gruppo.
     * Restituisce vero|falso
     * @param int $gid Id del gruppo
     * @return bool Esito dell'operazione
     */
    static public function genera_registro_vuoto($gid){

        global  $vmsql, $vmreg, $db1;

            // Inizio
            $vmreg->begin();

            $IS = new iSchema();
            $matrice0=$IS->get_tables();

            foreach ($matrice0 as $k=>$val){
                self::inserisci_registro($val,$gid);
            }	

            if(isset($GLOBALS['VMSQL_ERROR'])){
                $vmreg->rollback();
                return false;
            }
            else{
                $vmreg->commit();
                return true;
            }
    }





    /**
     * Inizializzazione del registro. 
     * Viene creato il gid 0 e la prima copia del registro e viene eliminato il gruppo -1 
     * creato nell'installazione.
     * Restituisce vero se l'operazione va a buon fine, falso se fallisce.
     * @return bool Esito dell'operazione
     */
    static public function inizializza_registro(){

        global  $vmsql, $vmreg, $db1;

        // Inizio
        $vmreg->begin();


        // Verifico l'esistenza del gruppo 0
        $Group = new Group();
        
        if(!$Group->exists(0)){
            $Group->inizialize();
        }

        ########################
        #
        # Def tabelle
        #
        $IS = new iSchema();
        $matrice0 = $IS->get_tables();
        
        foreach ($matrice0 as $k=>$val){

            self::inserisci_registro($val,0);
        }	

        if(isset($GLOBALS['VMSQL_ERROR'])){
            
            $vmreg->rollback( $vmsql, $vmreg);
            return false;
        }
        else{

            // metti l'utente 1 (admin) nel gruppo 0
            $q_ut=$vmreg->query("UPDATE {$db1['frontend']}{$db1['sep']}utente SET gid=0 WHERE id_utente=1");

            // aggiorna la sessione
            $_SESSION['user']['gid']=0;
            $_SESSION['gid']=0;

            // Elimina il gruppo temporaneo -1
            $q_del_g=$vmreg->query("DELETE FROM {$db1['frontend']}{$db1['sep']}gruppo WHERE gid=-1 ");

            $vmreg->commit();

            return true;
        }

    }




    /**
     * Inizializzazione (creazione) della sottomaschera. 
     * Viene inserita in registro la nuova sottomaschera e le sue caratteristiche di dettaglio.
     *
     * @param int $oid_parent ID della tabella padre (la maschera di cui questa sarà sottomaschera)
     * @param string $tabella_sub Tabella che svolgerà il ruolo di sottomaschera
     * @return bool Esito dell'operazione
     */
    static public function inizializza_sottomaschera($oid_parent,$tabella_sub){

        global $db1,  $vmsql, $vmreg;

        $vmreg->begin();

        // Inserimento dati minimi del master
        $sql_ins_sub = "INSERT INTO ".$db1['frontend'].$db1['sep']."registro_submask (id_table,nome_tabella) 
                            VALUES ('$oid_parent','$tabella_sub')";

        $q_ins_sub=$vmreg->query($sql_ins_sub);

        if($vmreg->affected_rows($q_ins_sub)==1){

            // inserimento del dettaglio
            $id_submask = $vmreg->insert_id( $db1['frontend'].".registro_submask" ,'id_submask');



            #########################
            #
            #	Get table detail
            #
            $IS=new iSchema();
            $matrice1=$IS->get_columns($tabella_sub);


            foreach ($matrice1 as $k=>$valori){

                $gid = (int) $_POST['gid'];

                $valori=$vmreg->recursive_escape($valori);

                $sql2= "INSERT INTO {$db1['frontend']}{$db1['sep']}registro_submask_col (
                        id_submask, 
                        column_name, 
                        ordinal_position, 
                        column_default, 
                        is_nullable, 
                        column_type, 
                        character_maximum_length, 
                        data_type, 
                        extra, 
                        commento)
                        VALUES 
                        (".intval($id_submask).",". 
                        "'".$valori['column_name']."',".
                        "'".$valori['ordinal_position']."',".
                        "'".$valori['column_default']."',".
                        "'".$valori['is_nullable']."',".
                        "'".$valori['column_type']."',".
                        intval($valori['character_maximum_length']).",".
                        "'".$valori['data_type']."',".
                        "'".$valori['extra']."',".
                        "'".$valori['column_comment']."')";

                $q2=$vmreg->query($sql2,true);
            }

        }
        else{
            $vmreg->rollback();
            return false;
        }


        $vmreg->commit();

        return true;
    }





    /**
     * Funzione di clonazione delle sottomaschere. 
     * Questa è un'utility per copiare una sottomaschera di un gruppo per un altro gruppo
     * 
     * @param int $gid_new ID del gruppo destinazione
     * @param int $gid_old ID del gruppo origine
     * @param bool $solo_id_table 
     * @return bool Esito dell'operazione
     */
    static public function clona_sottomaschere($gid_new,$gid_old,$solo_id_table=0){

        global $vmreg, $db1;

        $clausola_id_table = ($solo_id_table>0) ? "AND t.id_table=".intval($solo_id_table) : "";

        // prendi le tabelle che hanno sottomaschere da clonare

        $q0 =$vmreg->query("SELECT s.id_submask, s.id_table ,t.table_name
                        FROM ".$db1['frontend'].$db1['sep']."registro_submask s, ".$db1['frontend'].$db1['sep']."registro_tab t
                        WHERE t.gid=$gid_old
                        AND s.id_table=t.id_table
                        $clausola_id_table
                        ORDER BY t.table_name");

        if($vmreg->num_rows($q0)==0){

            return 0; // si ferma qui.
        }

        list($old_id_submask,$old_id_table, $old_table_name) =$vmreg->fetch_row_all($q0,true);

        // ora prende l'array delle tabelle del nuovo gid

        for($j=0;$j<count($old_table_name);$j++){

            $new_id_table[] = RegTools::name2oid($old_table_name[$j],$gid_new);

        }


        # CICLO CLONAZIONE SUBMASK MASTER

        # INIZIO
        $vmreg->begin();


        for($i=0;$i<count($old_id_submask);$i++){

            // PRENDI UNA SOTTOMASCHERA

            $q1 =$vmreg->query("SELECT id_submask,
                                    id_table,
                                    sub_select,
                                    sub_insert,
                                    sub_update,
                                    sub_delete,
                                    nome_tabella,
                                    nome_frontend,
                                    campo_pk_parent,
                                    campo_fk_sub,
                                    orderby_sub,
                                    orderby_sub_sort,
                                    data_modifica,
                                    max_records,
                                    tipo_vista

                        FROM ".$db1['frontend'].$db1['sep']."registro_submask
                        WHERE id_submask=".$old_id_submask[$i]."
                        ");

            $RS1=$vmreg->fetch_assoc($q1);


            // ---> PREPARA LA QUERY DI INSERIMENTO
            $sql_ins1 =sprintf("
            INSERT INTO ".$db1['frontend'].$db1['sep']."registro_submask

            (id_table,	sub_select,	sub_insert,	sub_update,	sub_delete,	nome_tabella,nome_frontend,
            campo_pk_parent,campo_fk_sub, orderby_sub, orderby_sub_sort, data_modifica, max_records,tipo_vista)

            VALUES (%d,%d,%d,%d,%d,'%s','%s',
                    '%s','%s','%s','%s',%d,%d,'%s')",
                $new_id_table[$i],
                $RS1['sub_select'],
                $RS1['sub_insert'],
                $RS1['sub_update'],
                $RS1['sub_delete'],
                $RS1['nome_tabella'],
                $vmreg->escape($RS1['nome_frontend']),
                $RS1['campo_pk_parent'],
                $RS1['campo_fk_sub'],
                $RS1['orderby_sub'],
                $RS1['orderby_sub_sort'],
                time(),
                $RS1['max_records'],
                $RS1['tipo_vista']
                );

            // INSERISCI IL MASTER 

            $q_ins1 = $vmreg->query($sql_ins1);

            if($vmreg->affected_rows($q_ins1)!=1){

                $vmreg->rollback();
                openErrorGenerico(_("Error in cloning subforms"),true);
            }


                // recupera l'id inserito.

                $new_id_submask = $vmreg->insert_id( $db1['frontend'].".registro_submask",'id_submask');

                // REcupera i dati del dettaglio

                $q2 =$vmreg->query("SELECT *
                        FROM ".$db1['frontend'].$db1['sep']."registro_submask_col
                        WHERE id_submask=".$old_id_submask[$i]."
                        ");

                while($RS2=$vmreg->fetch_assoc($q2)){

                    $RS2=$vmreg->recursive_escape($RS2);

                    // Prepara la query di inserimento

                    $sql_ins2=sprintf("INSERT INTO ".$db1['frontend'].$db1['sep']."registro_submask_col

                        (id_submask,  column_name,	ordinal_position, column_default, is_nullable,
                        column_type, character_maximum_length,	data_type,	extra, in_tipo,
                        in_default,	in_visibile, in_richiesto, alias_frontend,	commento)

                        VALUES

                        (%d,'%s',%d,'%s','%s',
                        '%s','%s','%s','%s','%s',
                        '%s',%d,%d,'%s','%s')
                        ",
                    $new_id_submask,
                    $RS2['column_name'],
                    $RS2['ordinal_position'],
                    $RS2['column_default'],
                    $RS2['is_nullable'],
                    $RS2['column_type'],
                    $RS2['character_maximum_length'],
                    $RS2['data_type'],
                    $RS2['extra'],
                    $RS2['in_tipo'],
                    $vmreg->escape($RS2['in_default']),
                    $RS2['in_visibile'],
                    $RS2['in_richiesto'],
                    $vmreg->escape($RS2['alias_frontend']),
                    $vmreg->escape($RS2['commento'])
                    );

                    $q_ins2=$vmreg->query($sql_ins2);

                    if($vmreg->affected_rows($q_ins2)!=1){

                        $vmreg->rollback();
                        openErrorGenerico(_("Error in cloning subforms"),true);
                    }



                }




        }

        $vmreg->commit();

        return true;


    }




    /**
     * Funzione di clonazione dei pulsanti
     * Questa è un'utility per copiare una sottomaschera di un gruppo per un altro gruppo
     *
     * @param int $gid_new ID del gruppo destinazione
     * @param int $gid_old ID del gruppo origine
     * @param bool $solo_id_table
     * @return bool Esito dell'operazione
     */
    static public function clona_buttons($gid_new,$gid_old){

        global $vmreg, $db1;




        // prendi le tabelle che hanno sottomaschere da clonare

        $q0 =$vmreg->query("SELECT b.id_button, b.id_table, t.table_name
                        FROM ".$db1['frontend'].$db1['sep']."button b, ".$db1['frontend'].$db1['sep']."registro_tab t
                        WHERE t.gid=$gid_old
                        AND b.id_table=t.id_table
                        ORDER BY t.table_name");

        if($vmreg->num_rows($q0)==0){

            return 0; // si ferma qui.
        }

        list($old_id_button,$old_id_table, $old_table_name) =$vmreg->fetch_row_all($q0,true);

        // ora prende l'array delle tabelle del nuovo gid

        for($j=0;$j<count($old_table_name);$j++){

            $new_id_table[] = RegTools::name2oid($old_table_name[$j],$gid_new);

        }


        # CICLO CLONAZIONE SUBMASK MASTER

        # INIZIO
        $vmreg->begin();


        for($i=0;$i<count($old_id_button);$i++){

            // PRENDI UNA SOTTOMASCHERA

            $q1 =$vmreg->query("SELECT
                                      definition,
                                      button_type ,
                                      background,
                                      color,
                                      button_name,
                                      last_data,
                                      id_utente,
                                      settings

                        FROM ".$db1['frontend'].$db1['sep']."button
                        WHERE id_button=".$old_id_button[$i]."
                        ");

            $RS1=$vmreg->fetch_assoc($q1);

            $RS1=$vmreg->recursive_escape($RS1);


            // ---> PREPARA LA QUERY DI INSERIMENTO
            $sql_ins1 =sprintf("
            INSERT INTO ".$db1['frontend'].$db1['sep']."button

            (id_table, definition, button_type ,background,
            color, button_name, last_data, id_utente, settings)

            VALUES ( %d,'%s','%s','%s',
                    '%s','%s','%s',%d,'%s')",
                $new_id_table[$i],
                $RS1['definition'],
                $RS1['button_type'],
                $RS1['background'],
                $RS1['color'],
                $RS1['button_name'],
                date('Y-m-d H:i:s'),
                $RS1['id_utente'],
                $RS1['settings']
                );

            // INSERISCI IL MASTER

            $q_ins1 = $vmreg->query($sql_ins1);

            if($vmreg->affected_rows($q_ins1)!=1){

                $vmreg->rollback();
                openErrorGenerico(_("Error in cloning buttons"),true);
            }

        }

        $vmreg->commit();

        return true;


    }




    /**
     * Funzione di manutenzione dei registri. 
     * Copia le sottomaschere per le viste
     *
     * @param int $id_vista_new
     * @param int $id_tabella_old
     * @return bool
     */
    static public function copia_sottomaschere_viste($id_vista_new,$id_tabella_old){

        global  $vmreg, $db1;

        # CICLO CLONAZIONE SUBMASK MASTER

        # INIZIO
        $vmreg->begin();


            // PRENDI UNA SOTTOMASCHERA

            $q1 =$vmreg->query("SELECT id_submask,
                                    id_table,
                                    sub_select,
                                    sub_insert,
                                    sub_update,
                                    sub_delete,
                                    nome_tabella,
                                    nome_frontend,
                                    campo_pk_parent,
                                    campo_fk_sub,
                                    orderby_sub,
                                    orderby_sub_sort,
                                    data_modifica,
                                    max_records,
                                    tipo_vista

                        FROM ".$db1['frontend'].$db1['sep']."registro_submask
                        WHERE id_table=$id_tabella_old

                        ");



            while($RS1=$vmreg->fetch_assoc($q1)){





                // ---> PREPARA LA QUERY DI INSERIMENTO
                $sql_ins1 =sprintf("
                INSERT INTO ".$db1['frontend'].$db1['sep']."registro_submask

                (id_table,	sub_select,	sub_insert,	sub_update,	sub_delete,	nome_tabella,nome_frontend,
                campo_pk_parent,campo_fk_sub, orderby_sub, orderby_sub_sort, data_modifica, max_records,tipo_vista)

                VALUES (%d,%d,%d,%d,%d,'%s','%s',
                        '%s','%s','%s','%s',%d,%d,'%s')",
                    $id_vista_new,
                    $RS1['sub_select'],
                    $RS1['sub_insert'],
                    $RS1['sub_update'],
                    $RS1['sub_delete'],
                    $RS1['nome_tabella'],
                    $vmreg->escape($RS1['nome_frontend']),
                    $RS1['campo_pk_parent'],
                    $RS1['campo_fk_sub'],
                    $RS1['orderby_sub'],
                    $RS1['orderby_sub_sort'],
                    time(),
                    $RS1['max_records'],
                    $RS1['tipo_vista']
                    );

                // INSERISCI IL MASTER 

                $q_ins1 = $vmreg->query($sql_ins1);

                if($vmreg->affected_rows($q_ins1)!=1){

                    $vmreg->rollback();
                    openErrorGenerico(_("Error in cloning subforms"),true);
                }


                    // recupera l'id inserito.

                    $new_id_submask = $vmreg->insert_id($db1['frontend'].".registro_submask", "id_submask");

                    // REcupera i dati del dettaglio

                    $q2 =$vmreg->query("SELECT *
                            FROM ".$db1['frontend'].$db1['sep']."registro_submask_col
                            WHERE id_submask=".$RS1['id_submask']);

                    while($RS2=$vmreg->fetch_assoc($q2)){


                        // Prepara la query di inserimento

                        $sql_ins2=sprintf("INSERT INTO ".$db1['frontend'].$db1['sep']."registro_submask_col

                            (id_submask,  column_name,	ordinal_position, column_default, is_nullable,
                            column_type, character_maximum_length,	data_type,	extra, in_tipo,
                            in_default,	in_visibile, in_richiesto, alias_frontend,	commento)

                            VALUES

                            (%d,'%s',%d,'%s','%s',
                            '%s','%s','%s','%s','%s',
                            '%s',%d,%d,'%s','%s')
                            ",
                        $new_id_submask,
                        $RS2['column_name'],
                        $RS2['ordinal_position'],
                        $vmreg->escape($RS2['column_default']),
                        $RS2['is_nullable'],
                        $RS2['column_type'],
                        $RS2['character_maximum_length'],
                        $RS2['data_type'],
                        $RS2['extra'],
                        $RS2['in_tipo'],
                        $vmreg->escape($RS2['in_default']),
                        $RS2['in_visibile'],
                        $RS2['in_richiesto'],
                        $vmreg->escape($RS2['alias_frontend']),
                        $vmreg->escape($RS2['commento'])
                        );

                        $q_ins2=$vmreg->query($sql_ins2);

                        if($vmreg->affected_rows($q_ins2)!=1){

                            $vmreg->rollback();
                            openErrorGenerico(_("Error in cloning subforms"),true);
                        }



                    } // -fine while interno

            } // -fine while esterno




        $vmreg->commit();

        return true;


    }


    /**
     * Funzionedi utility per le operazioni interne 
     * Copia le impostazioni dei campi di una tabella per un gruppo  
     * e le applica alla tabella per un altro gruppo
     *
     * @param int $id_table_fonte id_table della tabella fonte
     * @param int $id_table_destinazione id_table della tabella destinazione
     * @return bool Esito dell'operazione
     */
    static public function copia_impostazione_campi($id_table_fonte,$id_table_destinazione){

        global  $vmreg, $db1;

        // Prendi i valori del vecchio gid
            $sql_col1 = "SELECT column_name,
                                extra,
                                in_tipo,
                                in_default,
                                in_visibile,
                                in_richiesto,
                                in_suggest,
                                in_table,
                                in_ordine,
                                jstest,
                                alias_frontend

                        FROM {$db1['frontend']}{$db1['sep']}registro_col
                        WHERE id_table=".intval($id_table_fonte)."
                        ORDER BY column_name, ordinal_position

            ";

            $q_col1 = $vmreg->query($sql_col1);

            $vmreg->begin();

            while($RS2=$vmreg->fetch_assoc($q_col1)){


                // prende l'id_reg corrispettivo

                $RS2=$vmreg->recursive_escape($RS2);

                $q_reg=$vmreg->query("SELECT id_reg FROM {$db1['frontend']}{$db1['sep']}registro_col
                                    WHERE id_table=".intval($id_table_destinazione)."
                                    AND column_name='".$RS2['column_name']."'");

                list($ID_REG)=$vmreg->fetch_row($q_reg);

                // Prepara la query di aggiornamento

                $sql_up=sprintf("UPDATE {$db1['frontend']}{$db1['sep']}registro_col 
                                SET 
                                extra='%s',
                                in_tipo='%s', 
                                in_default='%s',
                                in_visibile=%d, 
                                in_richiesto=%d, 
                                in_suggest=%d, 
                                in_table=%d,
                                in_ordine=%d,
                                jstest='%s',
                                alias_frontend='%s'

                                WHERE id_reg=%d
                                ",
                                $RS2['extra'],
                                $RS2['in_tipo'],
                                $RS2['in_default'],
                                $RS2['in_visibile'],
                                $RS2['in_richiesto'],
                                $RS2['in_suggest'],
                                $RS2['in_table'],
                                $RS2['in_ordine'],
                                $RS2['jstest'],
                                $RS2['alias_frontend'],
                                $ID_REG);



                $res_up = $vmreg->query_try($sql_up,false);

                if(!$res_up){
                    $vmreg->rollback();
                    openErrorGenerico(_("Error in copying fields import"),true);
                    exit;
                }

            }

        $vmreg->commit();

        return true;



    }




    /**
     * Funzione di utilita' 
     * 
     * @param int $gid_new
     * @param int $gid_old
     * @param int $id_table_fonte
     * @param int $id_table_dest
     */
    static public function copia_impostazione_sottomaschere($gid_new,$gid_old,$id_table_fonte,$id_table_dest){

        global $vmreg, $db1;

        // Elimina eventuali vecchie sottomaschere
        $sql="DELETE FROM {$db1['frontend']}{$db1['sep']}registro_submask WHERE id_table=".intval($id_table_dest);
        $q_del_sub = $vmreg->query($sql);

        $esito = self::clona_sottomaschere($gid_new,$gid_old,$id_table_fonte);

    }


    /**
     * Funzione di sincronizzazione dei campi del registro frontend. 
     * Si associa a aggiorna registri, ma opera a livello di confronto di campo.
     *
     * @param string $tabella
     * @param string $campo
     * @param string $tipo_aggiornamento (UPDATE | INSERT | DELETE)
     * @todo update and insert in subforms (sottomaschere)
     */
    static public function aggiorna_campo($tabella,$campo,$tipo_aggiornamento="UPDATE"){

        global  $vmreg, $db1;

        if($tipo_aggiornamento=="UPDATE"){

            // prendi gli id dal frontend

            $sql_c="SELECT c.id_reg, c.id_table FROM {$db1['frontend']}{$db1['sep']}registro_col c, 
                    {$db1['frontend']}{$db1['sep']}registro_tab t
                    WHERE c.id_table=t.id_table 
                    AND t.table_name='$tabella'
                    AND c.column_name='$campo'";

            $qc=$vmreg->query($sql_c);

            $mat_c=$vmreg->fetch_assoc_all($qc);


            $IS=new iSchema();
            list($RSi)=$IS->get_columns($tabella,$campo);

            $RSi=$vmreg->recursive_escape($RSi);

            // PREPARA l'update

            for($i=0;$i<count($mat_c);$i++){

                // AGGIORNAMENTO MASCHERE
                $max_length= (is_numeric($RSi['character_maximum_length']) && $RSi['character_maximum_length']>0) ? intval($RSi['character_maximum_length']) : 'NULL';

                    $sql_up="UPDATE {$db1['frontend']}{$db1['sep']}registro_col
                             SET column_default='".$RSi['column_default']."',
                             is_nullable='".$RSi['is_nullable']."',
                             ordinal_position='".$RSi['ordinal_position']."',
                             column_type='".$RSi['column_type']."',
                             character_maximum_length=".$max_length.",
                             data_type='".$RSi['data_type']."',
                             extra='',
                             in_tipo = NULL,
                             in_default = NULL,
                             in_visibile=0,
                             in_richiesto=0,
                             in_table=0,
                             in_ordine=0,
                             jstest = NULL,
                             commento='".  Common::vf_utf8_decode($RSi['column_comment'])."'

                             WHERE id_table=".intval($mat_c[$i]['id_table'])."
                             AND id_reg=".intval($mat_c[$i]['id_reg'])."

                            "; 

                $q_up = $vmreg->query($sql_up);

            }

        }


        else if($tipo_aggiornamento=="INSERT"){


            // prendi le info del campo dal information_schema
            $IS=new iSchema();
            list($RSi)=$IS->get_columns($tabella,$campo);

            // prendi gli ID table da coinvolgere

            $q_idt=$vmreg->query("SELECT id_table,gid FROM {$db1['frontend']}{$db1['sep']}registro_tab
                                WHERE table_name='$tabella'");


            list($idtables,$gids)=$vmreg->fetch_row_all($q_idt,true);


            // prepara la query

            for($i=0;$i<count($idtables);$i++){

                $RSi['character_maximum_length']= (is_numeric($RSi['character_maximum_length'])
                                                    && $RSi['character_maximum_length']>0) ?
                                                    $RSi['character_maximum_length'] 
                                                    :
                                                    'NULL';



                $sql_in="INSERT INTO {$db1['frontend']}{$db1['sep']}registro_col

                    (id_table,gid,column_name,
                    column_default,is_nullable,ordinal_position,
                    column_type,character_maximum_length,data_type,in_visibile,
                    commento)

                    VALUES

                    (".$idtables[$i].", ".$gids[$i]." ,'$campo',
                    '".$vmreg->escape($RSi['column_default'])."','".$RSi['is_nullable']."',".intval($RSi['ordinal_position']).",
                    '".$vmreg->escape($RSi['column_type'])."',". $RSi['character_maximum_length'].",'".$RSi['data_type']."',0,
                    '".$vmreg->escape($RSi['column_comment'])."')

                    ";

                $q_in = $vmreg->query($sql_in);
            }
        }


        else if($tipo_aggiornamento=="DELETE"){


            // AGGIORNAMENTO MASCHERE

            // prendi le colonne coinvolte

            $sql_del="SELECT c.id_reg FROM {$db1['frontend']}{$db1['sep']}registro_col c, {$db1['frontend']}{$db1['sep']}registro_tab t
                    WHERE c.id_table=t.id_table 
                    AND t.table_name='$tabella'
                    AND c.column_name='$campo'";

            $q_del=$vmreg->query($sql_del);

            list($idregs) = $vmreg->fetch_row_all($q_del,true);

            if(count($idregs)>0){

                $sql_del2="DELETE FROM {$db1['frontend']}{$db1['sep']}registro_col WHERE id_reg IN (".implode(",",$idregs).")";

                $q_del2 = $vmreg->query($sql_del2);

            }


            // AGGIORNAMENTO SOTTO MASCHERE

            $sql_del_sub="SELECT sc.id_reg_sub
                    FROM {$db1['frontend']}{$db1['sep']}registro_submask_col sc
                    INNER JOIN {$db1['frontend']}{$db1['sep']}registro_submask s ON sc.id_submask=s.id_submask 
                    WHERE s.nome_tabella='$tabella'
                    AND sc.column_name='$campo'";

            $q_del_sub=$vmreg->query($sql_del_sub);

            list($idregs_sub) = $vmreg->fetch_row_all($q_del_sub,true);

            if(count($idregs_sub)>0){

                $sql_del3="DELETE FROM {$db1['frontend']}{$db1['sep']}registro_col WHERE id_reg IN (".implode(",",$idregs_sub).")";

                $q_del3 = $vmreg->query($sql_del3);

            }
        }
    }

}