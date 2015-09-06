<?php

return [
    'extensions' => 'html,xhtml',
    'preFilters' => [],
    'postFilters' => [],
    'encoding' => 'UTF-8',
    'outputMode' => PHPTAL::HTML5,
    'phpCodeDestination' => ( 
            
         app('config')->get('view.compiled') 
             ? app('config')->get('view.compiled') 
             : realpath(storage_path('framework/views'))
            
    ),
    'forceReparse' => true,
    'templateRepositories' => ( 
            
         app('config')->get('view.paths')[0]
             ? app('config')->get('view.paths')[0]
             : base_path('/resources/views')
            
    ) . (defined('TEMPLATE_ID') ? '/' . TEMPLATE_ID : ''),
    'translationClass' => \MarcosHoo\LaravelPHPTAL\PHPTALTranslator::class,
    'translationPath' => base_path('resources/lang/'),
    'translationFilename' => 'messages',
    'translationLanguages' => [
        'en',
    ]
];
