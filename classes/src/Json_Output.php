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
class Json_Output {

    public static function stream($obj, $return='print'){
        header("Content-type: application/json");
        if($return === 'print'){
            print json_encode($obj);
        }
        else{
            return json_encode($obj);
        }
    }
    
}
