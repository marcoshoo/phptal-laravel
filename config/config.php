<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Template File Extensions
    |--------------------------------------------------------------------------
    */
    
    'extensions' => [ 'html', 'xhtml' ],
    
    /*
    |--------------------------------------------------------------------------
    | Template Output Filters
    |--------------------------------------------------------------------------
    */
    
    'preFilters' => [],
    'postFilters' => [],
    
    /*
    |--------------------------------------------------------------------------
    | Template Encoding
    |--------------------------------------------------------------------------
    */
    
    'encoding' => 'UTF-8',
    
    /*
    |--------------------------------------------------------------------------
    | Template Output Mode
    |--------------------------------------------------------------------------
    */
    
    'outputMode' => PHPTAL::HTML5,
    
    /*
    |--------------------------------------------------------------------------
    | PHP Code Destination 
    |--------------------------------------------------------------------------
    */
    
    'phpCodeDestination' => ( 
            
         app('config')->get('view.compiled') 
             ? app('config')->get('view.compiled') 
             : realpath(storage_path('framework/views'))
            
    ),
    
    /*
    |--------------------------------------------------------------------------
    | Force Reparse Template
    |--------------------------------------------------------------------------
    */
    
    'forceReparse' => env('APP_DEBUG',true),
    
    /*
    |--------------------------------------------------------------------------
    | Template Repositories
    |--------------------------------------------------------------------------
    */
    
    'templateRepositories' => ( 
            
         app('config')->get('view.paths')
             ? app('config')->get('view.paths')
             : base_path('/resources/views')
            
    ) . (defined('TEMPLATE_ID') ? '/' . TEMPLATE_ID : ''),
    
    /*
    |--------------------------------------------------------------------------
    | Translation Properties
    |--------------------------------------------------------------------------
    */
    
    'translationClass' => \MarcosHoo\LaravelPHPTAL\PHPTALTranslator::class,
    'translationPath' => base_path('resources/lang/'),
    'translationFilename' => 'messages',    
    'translationLanguages' => [
        app('config')->get('app.locale'),
    ]
    
];
