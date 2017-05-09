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
        // We don't validate for external source
        if(strtolower(substr($value,0,4)) === 'http')
            return true;
        
        return is_file(BASEPATH . $value);
    }
}