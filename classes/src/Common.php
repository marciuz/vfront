<?php

/**
 * Libreria di funzioni comuni. 
 * Si tratta di funzioni incluse in quasi tutti gli script e di uso comune in tutta l'applicazione VFront
 * 
 * @package VFront
 * @subpackage Function-Libraries
 * @author Mario Marcello Verona <marcelloverona@gmail.com>
 * @copyright 2007-2014 M.Marcello Verona
 * @version 0.99 $Id:$
 * @license http://www.gnu.org/licenses/gpl.html GNU Public License
 */

/**
 * Common functions
 *
 * @author Mario Marcello Verona <marcelloverona@gmail.com>
 */
class Common {

    /**
     * Pulisce da spazi bianchi, aggiunge gli slash e rimuove eventuali tags i campi inseriti. 
     * Viene utilizzata per ragioni di sicurezza in contesti di invio di dati da parte del client.
     *
     * @param array $post_dom
     * @return array
     */
    static public function pulisci_dom($post_dom) {

        if (!is_array($post_dom)) {

            return false;
        }

        while (list($k, $val) = each($post_dom)) {
            if (is_array($val)) {
                while (list($kk, $valval) = each($val)) {
                    $_var[$k][$kk] = addslashes(stripslashes(strip_tags(trim($valval))));
                }
            }
            else {
                $_var[$k] = addslashes(stripslashes(strip_tags(trim($val))));
            }
        }

        return $_var;
    }

    /**
     * Dato un numerico indicante l'ID del gruppo viene restituito il nome dello stesso
     *
     * @param int $gid ID del gruppo 
     * @return string Nome del gruppo
     */
    static public function gid2group_name($gid) {

        global $vmreg, $db1;

        $q = $vmreg->query("SELECT nome_gruppo FROM {$db1['frontend']}{$db1['sep']}gruppo WHERE gid=" . intval($gid));
        list($nome_gr) = $vmreg->fetch_row($q);

        return $nome_gr;
    }

    /**
     * Dato oid di tabella recupera il gruppo a cui appartiene
     *
     * @param int $oid ID della tabella
     * @return int gid
     */
    static public function oid2gid($oid) {

        global $vmreg, $db1;

        $q = $vmreg->query("SELECT gid FROM {$db1['frontend']}{$db1['sep']}registro_tab WHERE id_table=" . intval($oid) . " LIMIT 1");
        list($gid) = $vmreg->fetch_row($q);

        return $gid;
    }

    /**
     * Funzione interna per la determinazione dei campi coinvolti da una data query SQL
     *
     * @param string $sql
     * @return string
     */
    static public function analisi_select_from($sql) {

        global $vmsql, $vmreg;

        if (!$vmsql->query_try($sql, true)) {
            return false;
        }

        // parsing della query
        $campi = preg_replace("|SELECT|i", '', $sql);

        list($campi, $monnezza) = preg_split("'[\W]FROM[\W]'i", $campi);

        // quanti campi ci sono?
        if (preg_match('|,|', $campi)) {

            $ar_campi = explode(",", $campi);

            return array(trim($ar_campi[0]), trim($ar_campi[1]));
        }
        else {
            return array(trim($campi));
        }
    }

    /**
     * Utilit� che da un 1:0 restituisce "Si" o "No" con la classe CSS omonima
     *
     * @param int $int
     * @return string
     */
    static public function highlight_yes_no($int) {

        return ($int) ? '<span class="si">' . _("Yes") . '</span>' : '<span class="no">' . _("No") . '</span>';
    }

    /**
     * Funzione per generare l'ordinamento delle tabelle secondo una colonna data
     *
     * @param string $etichetta
     * @param int $ord
     * @param string $desc
     */
    static public function table_sort($etichetta, $ord, $desc = "") {

        $gid = isset($_GET['gid']) ? intval($_GET['gid']) : '';

        $getord = (isset($_GET['ord'])) ? (int) $_GET['ord'] : $ord;
        $sort = (isset($_GET['sort'])) ? $_GET['sort'] : 0;

        if ($desc != "") {

            $title = "title=\"$desc\"";
        }
        else {
            $title = "";
        }

        if (isset($_GET['ord']) && $ord == $_GET['ord']) {

            $class = "tab-ord";
            if ($sort == "d") {
                return "<a class=\"$class desc\" href=\"" . Common::phpself() . "?gid=" . $gid . "&amp;ord=$ord&amp;sort=a\" $title>$etichetta</a>";
            }
            else {

                return "<a class=\"$class asc\" href=\"" . Common::phpself() . "?gid=" . $gid . "&amp;ord=$ord&amp;sort=d\" $title>$etichetta</a>";
                ;
            }
        }
        else {
            return "<a href=\"" . Common::phpself() . "?gid=" . $gid . "&amp;ord=$ord&amp;sort=a\" $title>$etichetta</a>";
        }
    }

    /**
     * Funzione di debug. 
     * Scrive in un file di testo le query o il testo che viene passato con il parametro $sql
     * E' utile soprattutto in operazioni di tipo AJAX.
     *
     * @param string $sql
     * @param string $filename
     */
    static public function rpc_debug($sql, $filename = "./rpc.debug.txt") {

        global $RPC_DEBUG;

        if ($RPC_DEBUG) {

            if ($fp = @fopen($filename, "a")) {

                fwrite($fp, date("Y-m-d H:i:s") . " --- " . str_replace(array("\n", "\r"), " ", $sql) . "\n");
                fclose($fp);
            }
        }
    }

    /**
     * Funzione di ordinamento dei valori di una matrice 
     * secondo una colonna scelta in ordine ascendente o discendente
     * Funziona con una logica simile a l'SQL
     *
     * @param array $matrice La matrice data
     * @param string $campo Il nome del campo
     * @param string $tipo Metodo di ordinamento (ASC o DESC) 
     * @return array La nuova matrice ordinata
     */
    static public function ordina_matrice($matrice, $campo, $tipo = "ASC") {

        if (!is_array($matrice) || count($matrice) < 2) {
            return $matrice;
        }

        // Scorre la matrice
        foreach ($matrice as $k => $ar) {
            $new[$k] = array($ar[$campo], $ar);
        }

        if ($tipo == "DESC") {
            rsort($new);
        }
        else {
            sort($new);
        }

        $ordinata = array();

        while (list($k, $ar) = each($new)) {

            $ordinata[$k] = $new[$k][1];
        }

        return $ordinata;
    }

    /**
     * Testa se il permesso/i specificato in $perm � posseduto dall'utente
     *
     * @param mixed $perm permesso o array di permessi
     * @return bool
     */
    static public function permesso($perm) {

        // sempre e comunque gli amministratori possono accedere
        if (User_Session::level() == 3)
            return true;

        // DEBUG... per ora restituisce sempre vero...
        //	return true;

        if (!is_array($_SESSION['user']['permessi']))
            $_SESSION['user']['permessi'] = array();

        if (is_string($perm)) {

            return (in_array($perm, $_SESSION['user']['permessi'])) ? true : false;
        }
        elseif (is_array($perm)) {

            foreach ($perm as $p) {
                if (!in_array($p, $_SESSION['user']['permessi']))
                    return false;
            }

            return true;
        }
    }

    /**
     * Testa se l'utente = admin
     *
     * @return bool
     */
    static public function is_admin() {

        return (User_Session::level() >= 3) ? true : false;
    }

    static public function vf_utf8_encode($string) {

        //return (defined(FRONT_ENCODING) && strtoupper(FRONT_ENCODING)=='UTF-8') ? $string : utf8_encode($string);

        return $string;
    }

    static public function vf_utf8_decode($string) {

        //return (defined(FRONT_ENCODING) && strtoupper(FRONT_ENCODING)!='UTF-8') ? utf8_decode($string) : $string;

        return $string;
    }

    static public function is_true($v) {

        if ($v === 1 || $v === '1' || $v === 't' || $v === true)
            return true;
        else
            return false;
    }

    static public function is_false($v) {

        if ($v === 0 || $v === '0' || $v === 'f' || $v === null || $v === false)
            return true;
        else
            return false;
    }

    static public function vfront_version($extended = false) {

        if (function_exists('simplexml_load_file')) {

            $xml = simplexml_load_file(FRONT_ROOT . '/vf_version.xml');
            return $xml->version;
        }
        else {
            preg_match("|<version>(.+)</version>|U", file_get_contents(FRONT_ROOT . '/vf_version.xml'), $r);
            return (isset($r[0])) ? $r[0] : null;
        }
    }

    static public function dir_name() {
        $dn = dirname($_SERVER['PHP_SELF']);
        return ( $dn == '/' || $dn == '\\') ? "" : $dn;
    }

    public function noxss($string) {
        return htmlspecialchars($string, ENT_QUOTES, FRONT_ENCODING);
    }

    public static function phpself() {
        //return filter_input(INPUT_SERVER, 'PHP_SELF', FILTER_SANITIZE_URL);
        return htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, FRONT_ENCODING);
    }

}
