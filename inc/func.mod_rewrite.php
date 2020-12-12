<?php

function check_mod_rewrite($root){
        $test = file_get_contents($root.'/inc/mod_rewrite/test');
        return $test == 'YES';
}

?>
