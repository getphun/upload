<?php
/**
 * Uploader service
 * @package upload 
 * @version 0.0.1
 * @upgrade true
 */

namespace Upload\Service;
use Upload\Model\Media;

class Uploader
{
    private $user = null;
    
    private $trans_mime = [
        'image/jpg' => 'image/jpeg'
    ];
    
    public function __construct(){
        $dis = \Phun::$dispatcher;
        
        if(module_exists('user')){
            $this->user = false;
            if($dis->user->isLogin())
                $this->user = $dis->user;
            elseif(module_exists('app')){
                if($dis->app->exists && $dis->app->user)
                    $this->user = $dis->app->user;
            }
        }
    }
    
    private function processFile($file, $form){
        $dis = \Phun::$dispatcher;
        
        if(!$form)
            return '`form` field is required';
        
        $forms = explode('.', $form);
        if(count($forms) != 2)
            return 'Form name is invalid';
        
        $f_form_name  = $forms[0];
        $f_form_field = $forms[1];
        
        $c_form = $dis->config->form[$f_form_name] ?? null;
        if(!$c_form)
            return 'Form name not registered';
        
        if(!isset($c_form[$f_form_field]))
            return 'Form field not registered';
        
        $c_field = $c_form[$f_form_field];
        $c_rules = $c_field['rules'];
        
        // tinyMCE will accept image and video by default
        if($c_field['type'] == 'wysiwyg')
            $c_rules['file'] = 'image/*,video/*';
        
        if(!isset($c_rules['file']))
            return 'Form field not accept file input';
        
        $rule = $c_rules['file'];
        
        $u_file_mime = $file['type'];
        if(isset($this->trans_mime[$u_file_mime]))
            $u_file_mime = $file['type'] = $this->trans_mime[$u_file_mime];
        
        $f_file_mime = mime_content_type($file['tmp_name']);
        if(isset($this->trans_mime[$f_file_mime]))
            $f_file_mime = $this->trans_mime[$f_file_mime];
        
        if($u_file_mime != $f_file_mime)
            return 'File mime type is not acceptable';
        
        $mimes = explode(',', $rule);
        $accept = false;
        foreach($mimes as $mime){
            if(fnmatch($mime, $u_file_mime)){
                $accept = true;
                break;
            }
        }
        
        if(!$accept)
            return 'File mime type is not acceptable';
        
        $media = [
            'name'      => md5_file($file['tmp_name']),
            'original'  => $file['name'],
            'mime'      => $f_file_mime,
            'path'      => '',
            'form'      => $form
        ];
        
        if($this->user)
            $media['user'] = $this->user->id;
        
        $f_exts = explode('.', $media['original']);
        $f_ext  = end($f_exts);
        
        // remove question mark on the extenstion
        $f_ext = preg_replace('/\?.+$/', '', $f_ext);
        $media['name'].= '.' . $f_ext;
        
        // create directories
        $dir_base = BASEPATH . '/media';
        $path_base= '/media';
        
        for($i=0; $i<3; $i++){
            $subname = substr($media['name'], ($i*2), 2);
            $path_base.= '/' . $subname;
            $dir_base .= '/' . $subname;
            if(!file_exists($dir_base))
                mkdir($dir_base);
        }
        
        $dir_base .= '/' . $media['name'];
        $path_base.= '/' . $media['name'];
        $media['path'] = $path_base;
        
        if(is_uploaded_file($file['tmp_name'])){
            if(!move_uploaded_file($file['tmp_name'], $dir_base))
                return 'Failed on saving the file';
        }else{
            if(!rename($file['tmp_name'], $dir_base))
                return 'Failed on moving the file';
        }
        
        // save it to DB?
        if(module_exists('db-mysql'))
            Media::create($media);
        
        return $media;
    }
    
    public function fromForm(){
        if($this->user === false)
            return 'Not authorized';
        
        $dis = \Phun::$dispatcher;
        
        $u_file = $dis->req->getFile('file');
        if(!$u_file)
            return '`file` field is required';
        
        $file = [
            'type'      => $u_file['type'],
            'tmp_name'  => $u_file['tmp_name'],
            'name'      => $u_file['name']
        ];
        
        $form = $dis->req->getPost('form');
        
        return $this->processFile($file, $form);
    }
    
    public function fromUrl($url, $form, $curl_headers=[]){
        if($this->user === false)
            return 'Not authorized';
        
        $dis = \Phun::$dispatcher;
        
        if(!filter_var($url, FILTER_VALIDATE_URL))
            return 'URL is not valid';
        
        $file = [
            'type'      => '',
            'tmp_name'  => tempnam(sys_get_temp_dir(), 'Phun-Download-'),
            'name'      => basename($url)
        ];
        
        $f = fopen($file['tmp_name'], 'w');
        
        $cu = curl_init($url);
        curl_setopt($cu, CURLOPT_RETURNTRANSFER, true);
        if(substr($url,0,5) === 'https')
            curl_setopt($cu, CURLOPT_SSL_VERIFYPEER, false);
        if($curl_headers)
            curl_setopt($cu, CURLOPT_HTTPHEADER, $curl_headers);
        curl_setopt($cu, CURLOPT_FILE, $f);
        
        // download the file
        if(!curl_exec($cu))
            return 'Fail on downloading the file';
        
        $header = curl_getinfo($cu);
        if($header['http_code'] != 200)
            return 'Fail on downloading the file ('.$header['http_code'].')';
        
        $file['type'] = $header['content_type'];
        
        $ext = explode('/', $file['type']);
        if(isset($ext[1]))
            $file['name'].= '.'.$ext[1];
        
        return $this->processFile($file, $form);
    }
}