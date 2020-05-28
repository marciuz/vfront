<?php

/**
 * Description of RpcJSON
 *
 * @author M.Marcello Verona
 */
class RpcJSON {
    
    public static function send($_data, $require_login = true, $autoprint = true ) {
        
        $is_logged = User_Session::is_logged();
        
        // remove the output.
        if($require_login && $is_logged===false) {
            $_data = [];
        }
        
        $data = json_encode($_data);
        
        if($autoprint){
            header("Content-type: application/json; charset=" . FRONT_ENCODING);
            header("X-VFront-Auth: ".intval($is_logged));
            print $data;
        }
        else{
            return $data;
        }
        
    }
}
