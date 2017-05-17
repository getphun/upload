<?php
/**
 * File validator
 * @package upload
 * @version 0.0.1
 * @upgrade true
 */

namespace Upload\Validator;

class File
{
    static function test($value){
        if(!$value)
            return true;
        
        $value = (array)$value;
        
        foreach($value as $val){
            // We don't validate for external source
            if(strtolower(substr($val,0,4)) === 'http')
                continue;
            if(!is_file(BASEPATH . $val))
                return false;
        }
        
        return true;
    }
}