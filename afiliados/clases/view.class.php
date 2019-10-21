<?php
class View {
    public static function create($theme, $variables = array()) {
        foreach ($variables as $key => $value) {
            eval('$' . $key . '=$value;');
        }
        require_once("view/".$theme); 
    }
    
}