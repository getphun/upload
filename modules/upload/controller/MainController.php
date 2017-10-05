<?php
/**
 * Upload controller
 * @package upload
 * @version 0.0.1
 * @upgrade true
 */

namespace Upload\Controller;

class MainController extends \Controller
{
    public function uploadAction(){
        if($this->req->method != 'POST')
            return $this->show404();
        
        $file = $this->uploader->fromForm();
        if(is_string($file))
            return $this->ajax(['error'=>$file]);
        
        $this->ajax([
            'name' => $file['name'],
            'path' => $file['path']
        ]);
    }
}