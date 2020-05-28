<?php

/**
 * Class for User session management
 *
 * @author M.Marcello Verona
 */
class User_Session {
    
    const KEY = 'user';
    
    const FIELD_UID = 'uid';
    const FIELD_GROUP_ID = 'gid';
    const FIELD_LEVEL = 'livello';
    const FIELD_NICKNAME= 'nick';
    const FIELD_FIRST_NAME = 'nome';
    const FIELD_LAST_NAME = 'cognome';
    const FIELD_INSERT_DATE = 'data_ins';
    const FIELD_EMAIL = 'email';
    
    public static function setuser(array $data) {
        $_SESSION[self::KEY] = $data;
    }
    
    /**
     * Get or set an attribute to the user session.
     *
     * @param string $key
     * @param mixed $value
     * @return mixed
     */
    public static function attr($key, $value = null) {
        
        if($value === null) {
            return $_SESSION[self::KEY][$key] ?? null;
        }
        else if(self::is_logged()) {
            $_SESSION[self::KEY][$key] = $value;
            return true;
        }
        else{
            return false;
        }
    }
    
    /**
     * Check if the user is logged.
     *
     * @return boolean
     */
    public static function is_logged() {
        
        $is_logged = (isset($_SESSION[self::KEY][self::FIELD_UID]) 
                && $_SESSION[self::KEY][self::FIELD_UID] > 0);
        
        return $is_logged;
    }
    
    /**
     * Get the internal user id.
     *
     * @return int
     */
    public static function id() {
        return $_SESSION[self::KEY][self::FIELD_UID] ?? null;
    }
    
    /**
     * Get the internal user group.
     * @return int
     */
    public static function gid() {
        return $_SESSION[self::KEY][self::FIELD_GROUP_ID] ?? null;
    }
    
    public static function firstname() {
        return $_SESSION[self::KEY][self::FIELD_FIRST_NAME] ?? null;
    }
    
    public static function lastname() {
        return $_SESSION[self::KEY][self::FIELD_LAST_NAME] ?? null;
    }
    
    public static function fullname() {
        return self::firstname() . ' ' .self::lastname();
    }
    
    public static function email() {
        return $_SESSION[self::KEY][self::FIELD_EMAIL] ?? null;
    }
    
    /**
     * Get the level number of the user.
     *
     * @return int
     */
    public static function level() {
        return $_SESSION[self::KEY][self::FIELD_LEVEL] ?? null;
    }
    
    /**
     * Unset the user Session.
     */
    public static function delete() {
        unset($_SESSION[self::KEY]);
    }
}
