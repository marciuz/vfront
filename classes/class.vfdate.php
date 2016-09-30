<?php


/**
 * Description of class
 *
 * @author marcello
 */
class VFDate {

    
    /**
     * Codifica la data in base al parametro di configurazione FRONT_DATE_FORMAT
     *
     * @param date $dataISO
     * @param bool $ora
     * @param string $special
     * @return string
     */
    static public function date_encode($dataISO,$ora=false,$special=''){

        switch (FRONT_DATE_FORMAT){

            case 'ita': return self::dataITA($dataISO,$ora,$special);
            break;

            case 'eng': return self::dataENG($dataISO,$ora,$special);
            break;

            default: return ($ora) ? $dataISO : substr($dataISO,0,10);
        }
    }


    static public function date_decode($dataenc){

        if(FRONT_DATE_FORMAT) return $dataenc;

        $tk0=explode(" ",$dataenc);

        $d0=explode("/",$tk0[0]);

        $ora0=(isset($tk0[1])) ? " ".$tk0[1] : '';

        switch (FRONT_DATE_FORMAT){

            case 'ita': return $d0[2].$d0[1].$d0[0].$ora0;
            break;

            case 'eng': return $d0[2].$d0[0].$d0[1].$ora0;
            break;

            default: return $dataenc;
        }
    }

    

    /**
     * Estrae le parti di una data in formato internazionale e restituisce le parti della stessa in un array.
     * Fornisce inoltre un valore di chiave "ita" già pronto con la data in formato italiano.
     *
     * @param string $dataISO
     * @param bool $ora
     * @return array
     */
    static public function dataISO2ITA($dataISO,$ora=false){

        $dataITA['d']=substr($dataISO,8,2);
        $dataITA['m']=substr($dataISO,5,2);
        $dataITA['Y']=substr($dataISO,0,4);
        $dataITA['ita']=$dataITA['d']."/".$dataITA['m']."/".$dataITA['Y'];

        if($ora){
            $dataITA['h']=substr($dataISO,11,2);
            $dataITA['i']=substr($dataISO,14,2);
            $dataITA['s']=substr($dataISO,17,2);
            $dataITA['ita_noh']=$dataITA['ita'];

            $dataITA['ita'].= " alle ".$dataITA['h'].":".$dataITA['i'];
        }

        return $dataITA;
    }




    /**
     * Funzione di trasformazione di data da formato internazionale a formato italiano o con data e la T. 
     * Richiama a sua volta la funzione dataISO2ITA()
     *
     * @param date $dataISO
     * @param bool $ora
     * @param string $special 'string' (mostra la data con la parola ALLE + $ora | 'ods' (caso speciale per l'esportazione open office)
     * @see static public function dataISO2ITA
     * @return string
     */
    static public function dataITA($dataISO,$ora=false,$special=''){

        $d= self::dataISO2ITA($dataISO,$ora);
        if($ora){

            // caso open office export
            if($special=='ods')
                return $d['ita_noh']."T".$d['h'].$d['i'].$d['s'];
            else if($special=='string')
                return $d['ita'];
            else
                return $d['ita_noh']." ".$d['h'].":".$d['i'].":".$d['s'];
        }
        else{
            return $d['ita'];
        }

    }




    /**
     * Estrae le parti di una data in formato internazionale e restituisce le parti della stessa in un array.
     * Fornisce inoltre un valore di chiave "eng" pronto con la data in formato english.
     *
     * @param string $dataISO
     * @param bool $ora
     * @return array
     */
    static public function dataISO2ENG($dataISO,$ora=false){

        $dataENG['d']=substr($dataISO,8,2);
        $dataENG['m']=substr($dataISO,5,2);
        $dataENG['Y']=substr($dataISO,0,4);
        $dataENG['eng']=$dataENG['m']."/".$dataENG['d']."/".$dataENG['Y'];

        if($ora){
            $dataENG['h']=substr($dataISO,11,2);
            $dataENG['i']=substr($dataISO,14,2);
            $dataENG['s']=substr($dataISO,17,2);
            $dataENG['eng_noh']=$dataENG['eng'];

            $dataENG['eng'].= " on ".$dataENG['h'].":".$dataENG['i'];
        }

        return $dataENG;
    }




    /**
     * Funzione di trasformazione di data da formato internazionale a formato english 
     * o con data e altre opzioni relative a $special
     * Richiama a sua volta la funzione dataISO2ENG()
     *
     * @param date $dataISO
     * @param bool $ora
     * @param string $special
     * @see static public function dataISO2ENG
     * @return string
     */
    static public function dataENG($dataISO,$ora=false,$special=''){

        $d= self::dataISO2ENG($dataISO,$ora);
        if($ora){

            // caso open office export
            if($special=='ods')
                return $d['eng_noh']."T".$d['h'].$d['i'].$d['s'];
            else
                return $d['eng_noh']." ".$d['h'].":".$d['i'].":".$d['s'];
        }
        else{
            return $d['eng'];
        }

    }

}
