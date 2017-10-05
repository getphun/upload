<?php
/**
 * upload config file
 * @package upload
 * @version 0.0.1
 * @upgrade true
 */

return [
    '__name' => 'upload',
    '__version' => '0.0.1',
    '__git' => 'https://github.com/getphun/upload',
    '__files' => [
        'modules/upload' => [ 'install', 'remove', 'update' ],
        'media'          => [ 'install' ]
    ],
    '__dependencies' => [
        'core',
        '/user/db-mysql'
    ],
    '_services' => [
        'uploader' => 'Upload\\Service\\Uploader'
    ],
    '_autoload' => [
        'classes' => [
            'Upload\\Controller\\MainController'    => 'modules/upload/controller/MainController.php',
            'Upload\\Model\\Media'                  => 'modules/upload/model/Media.php',
            'Upload\\Validator\\File'               => 'modules/upload/validator/File.php',
            'Upload\\Service\\Uploader'             => 'modules/upload/service/Uploader.php'
        ],
        'files' => []
    ],
    
    'form_validation' => [
        'validator' => [
            'file' => [
                'message' => 'Field :field is pointing to not exists file, or is not acceptable',
                'options' => [],
                'handler' => [
                    'class' => 'Upload\\Validator\\File',
                    'action'=> 'test'
                ]
            ]
        ]
    ]
];