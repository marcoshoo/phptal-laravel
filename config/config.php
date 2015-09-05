<?php

return [
    'extensions' => 'html,xhtml',
    'preFilters' => [],
    'postFilters' => [],
    'encoding' => 'UTF-8',
    'outputMode' => PHPTAL::HTML5,
    'phpCodeDestination' => storage_path() . '/phptal',
    'forceReparse' => true,
    'templateRepositories' => base_path() . '/resources/views' . (defined('TEMPLATE_ID') ? '/' . TEMPLATE_ID : ''),
    'translationClass' => \MarcosHoo\LaravelPHPTAL\PHPTALTranslator::class,
    'translationPath' => base_path() . 'resources/lang/',
    'translationFilename' => 'messages',
    'translationLanguages' => [
        'en',
    ]
];
