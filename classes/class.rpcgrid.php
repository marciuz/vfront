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
class RPCGrid extends Rpc {

    public function __construct($table, $outputType = 'XML') {
        parent::__construct($table, $outputType);
    }


    /**
     * Da una query SQL viene restituito il JSON generato per la griglia
     *
     * @param string $sql
     * @param int $offset
     * @param string $filename
     * @param bool $header
     * @param string $PK
     * @return void
     */
    public function json_grid($sql,$offset=0,$filename=null,$header=true,$PK=null){

        global  $vmsql, $vmreg;

        $q = $vmsql->query($sql);

        if($vmsql->num_rows($q)==0){

            return null;	
        }

        // Inizia a fare l'xml
        $offset++;

        $data = array();

        while($RS=$vmsql->fetch_assoc($q)){

            $temp = array();

            foreach($RS as $k=>$val){

                // Il campo speciale prendi valore imposta l'offset per la ricerca
                if($k!='pk'){
                    $val = trim($val);

                    if(isset($_SESSION['VF_VARS']['max_char_tabella']) && intval($_SESSION['VF_VARS']['max_char_tabella'])>0){
                        if(strlen($val)>$_SESSION['VF_VARS']['max_char_tabella']) 
                            $val = substr($val,0,$_SESSION['VF_VARS']['max_char_tabella'])."...";
                    }

                    $temp[]=$val;
                }
            }

            $data[] = $temp;
        }

        print json_encode($data);	
    }


    /**
     * Da una query SQL viene restituito l'XML generato per la griglia dxhtmlGrid 
     *
     * @param string $sql
     * @param int $offset
     * @param string $filename
     * @param bool $header
     * @param string $PK
     * @return void
     */
    public function xmlize_grid($sql,$offset=0,$filename=null,$header=true,$PK=null){

        global  $vmsql;

        $q = $vmsql->query($sql);

        if($vmsql->num_rows($q)==0){

            return null;	
        }

        // Inizia a fare l'xml
        $offset++;

        $XML= ($header) ? "<?xml version=\"1.0\" encoding=\"".FRONT_ENCODING."\"?>\n" : "";

        $XML.="<rows>\n";

        while($RS=$vmsql->fetch_assoc($q)){


            # Se è un contesto di ricerca...
            if(isset($_REQUEST['q'])){

                $RS['n_offset'] = (isset($RS['n_offset'])) ? $RS['n_offset']:0;

                # identificativo ($RS)
                $offset_ricerca=$RS['n_offset']+1;

                $XML.="\t".$this->xmlize_campo_grid('row',array("id"=>$RS['pk']))."\n";
                $XML.="\t\t<cell>".$offset_ricerca."</cell>\n";

            }
            else{
                # identificativo (offset)
                $XML.="\t".$this->xmlize_campo_grid('row',array("id"=>$RS['pk']))."\n";
                $XML.="\t\t<cell>".$offset."</cell>\n";
            }

            foreach($RS as $k=>$val){


                // Il campo speciale prendi valore imposta l'offset per la ricerca
                if($k!='pk'){

                    $XML.="\t\t".$this->xmlize_campo_grid('cell',array());

                    //$val = Common::vf_utf8_encode(trim($val));
                    $val = trim(strip_tags($val));

                    if(isset($_SESSION['VF_VARS']['max_char_tabella']) && intval($_SESSION['VF_VARS']['max_char_tabella'])>0){
                        if(strlen($val)>$_SESSION['VF_VARS']['max_char_tabella']) $val = substr($val,0,$_SESSION['VF_VARS']['max_char_tabella'])."...";
                    }

                    // togli new line e sostituisci gli spazi
                    //$val = str_replace(array(" ","-","\n","\r","\n\r","\r\n"),array("&nbsp;","&nbsp;","","","",""),$val);

                    if($val!='' && !is_numeric($val)) $val="<![CDATA[".$val."]]>";

                    $XML.=$val;
                    $XML.="</cell>\n";
                }
            }

            $XML.="\t</row>\n";


            $offset++;
        }

        $XML.="</rows>\n";


        if(is_null($filename)){

            if ( stristr($_SERVER["HTTP_ACCEPT"],"application/xhtml+xml") ) { 
                header("Content-type: application/xhtml+xml"); 
            } 
            else { 
                header("Content-type: text/xml; charset=".FRONT_ENCODING); 
            }

            print $XML;

        }
        else{

            $fp =fopen($filename,"w");
            fwrite($fp,$XML);
            fclose($fp);
            return true;
        }

    }
    
    public function is_utf8($str) {
        return (bool) preg_match('//u', $str);
    }


    /**
     * Da una query SQL viene restituito l'XML generato per la griglia dxhtmlGrid 
     *
     * @param string $sql
     * @param int $offset
     * @param string $filename
     * @param bool $header
     * @param string $PK
     * @return void
     */
    public function json_dhtmlx_grid($sql,$offset=0,$filename=null,$header=true,$PK=null){

        global  $vmsql;

        $q = $vmsql->query($sql);

        if($vmsql->num_rows($q)==0){

            return null;	
        }

        // initialize the json
        $o = new stdClass();
        $o->rows=array();
        $offset++;

        while($RS=$vmsql->fetch_assoc($q)){


            # Se è un contesto di ricerca...
            if(isset($_REQUEST['q'])){

                # identificativo ($RS)
                if(isset($RS['n_offset'])) $offset=$RS['n_offset']+1;
            }

            $rso= new stdClass();
            $rso->id = $RS['pk'];
            unset($RS['pk']);

            foreach($RS as $key=>$val){
                $RS[$key]=  trim(preg_replace("/[\s|\v]+/u", ' ',strip_tags($val)));
            }

            $RS1 = array($offset) + $RS;

            $rso->data = array_values($RS1);
            $offset++;
            $o->rows[]= $rso;
        }


        if(is_null($filename)){

            if ( stristr($_SERVER["HTTP_ACCEPT"],"application/json") ) { 
                header("Content-type: application/json; charset=".FRONT_ENCODING); 
            } 
            else { 
                header("Content-type: text/json; charset=".FRONT_ENCODING); 
            }
            
            print json_encode($o);

        }
        else{

            $fp =fopen($filename,"w");
            fwrite($fp,json_encode($o));
            fclose($fp);
            return true;
        }

    }


    /**
     * Genera l'XML dal campo dato con gli eventuali attributi
     *
     * @param string $tag
     * @param array $attr
     * @return string
     */
    private function xmlize_campo_grid($tag,$attr){

            $attributi="";

            foreach($attr as $k=>$val){

                $attributi .=" $k=\"$val\"";
            }

            return "<".$tag.$attributi.">";

    }



}
