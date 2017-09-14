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
        
        $user = null;
        
        if(module_exists('user')){
            if($this->user->isLogin())
                $user = $this->user;
            elseif(module_exists('app')){
                if($this->app->exists && $this->app->user)
                    $user = $this->app->user;
            }
            
            if(!$user)
                return $this->show404();
        }
        
        $u_file = $this->req->getFile('file');
        if(!$u_file)
            return $this->ajax(['error'=>'`file` field is required']);
        
        $u_form = $this->req->getPost('form');
        if(!$u_form)
            return $this->ajax(['error'=>'`form` field is required']);
        
        $forms = explode('.', $u_form);
        if(count($forms) != 2)
            return $this->ajax(['error'=>'Form name is invalid']);
        
        $f_form_name  = $forms[0];
        $f_form_field = $forms[1];
        
        $c_form = \Phun::$config['form'][$f_form_name] ?? null;
        if(!$c_form)
            return $this->ajax(['error'=>'Form name is not registered']);
        
        if(!isset($c_form[$f_form_field]))
            return $this->ajax(['error'=>'Form field is not registered']);
        
        $c_field = $c_form[$f_form_field];
        $c_rules = $c_field['rules'];
        
        // tinyMCE will accept image and video by default
        if($c_field['type'] == 'wysiwyg')
            $c_rules['file'] = 'image/*,video/*';
        
        if(!isset($c_rules['file']))
            return $this->ajax(['error'=>'Form field is not accept file upload']);
        
        $rule = $c_rules['file'];
        
        $u_file_mime = $u_file['type'];
        $f_file_mime = mime_content_type($u_file['tmp_name']);
        if( $u_file_mime != $f_file_mime )
            return $this->ajax(['error'=>'File mime type is not acceptable']);
        
        $mimes = explode(',', $rule);
        $accept = false;
        foreach($mimes as $mime){
            if(fnmatch($mime, $u_file_mime)){
                $accept = true;
                break;
            }
        }
        
        if(!$accept)
            return $this->ajax(['error'=>'File mime type is not acceptable']);
        
        $file = [
            'name'      => md5_file($u_file['tmp_name']),
            'original'  => $u_file['name'],
            'mime'      => $f_file_mime,
            'path'      => '',
            'form'      => $u_form
        ];
        
        if($user)
            $file['user'] = $user->id;
        
        $f_exts = explode('.', $file['original']);
        $f_ext  = end($f_exts);
        
        // remove question mark on the extenstion
        $f_ext = preg_replace('/\?.+$/', '', $f_ext);
        
        $file['name'].= '.' . $f_ext;
        
        // create directories
        $dir_base = BASEPATH . '/media';
        $path_base= '/media';
        
        for($i=0; $i<3; $i++){
            $subname = substr($file['name'],($i*2),2);
            $path_base.= '/' . $subname;
            $dir_base .= '/' . $subname;
            if(!file_exists($dir_base))
                mkdir($dir_base);
        }
        
        $dir_base .= '/' . $file['name'];
        $path_base.= '/' . $file['name'];
        $file['path'] = $path_base;
        
        if(!move_uploaded_file($u_file['tmp_name'], $dir_base))
            return $this->ajax(['error'=>'Fail on saving the file']);
        
        // save it to DB?
        if(module_exists('db-mysql'))
            \Upload\Model\Media::create( $file );
        
        $this->ajax([
            'name' => $file['name'],
            'path' => $file['path']
        ]);
    }
}