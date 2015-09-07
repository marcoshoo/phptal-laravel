Allows you to use [PHPTAL](http://www.phptal.org) seamlessly in [Laravel](http://www.laravel.com)

# Installation

Add `MarcosHoo\LaravelPHPTAL` as a requirement to composer.json:

```javascript
{
    "require": {
        "phptal/phptal": "dev-master",
        "marcoshoo/phptal-laravel": "dev-master"
    }
}
```

Update your packages with `composer update` or install with `composer install`.

Once Composer has installed or updated your packages you need to register PHPTAL with Laravel. Open up app/config/app.php and find the providers key towards the bottom and add:

```php
MarcosHoo\LaravelPHPTAL\PHPTALServiceProvider::class,
```

Next step is copy example config to your `config` directory.

```php
php ./artisan vendor:publish --provider 'MarcosHoo\LaravelPHPTAL\PHPTALServiceProvider'
```

# Configuration

Currently PHPTAL is set by default to HTML5, however, you can change this setting in its config.php file, XHTML, or XML.


# Filters

PHPTAL also has a nice feature which you can use called filters, for example I have one that bust the cache for images, js, css, this is configurable via the config file.

```php
'preFilters' => [
    'bustCache',
    'minimizeJs',
    'adderJs'
]
```

#Usage

You call the PHPTAL template like you would any other view:

```php
view('hello', [...])
```

#### Translation

PHPTALTranslator class uses Laravel translation service, so the translation files should be placed in the same translation files directory of the framework. Examples of how to use the a translation with id 'welcome' contained in a file called 'messages.php':

```XML
<div i18n:domain="messages" i18n:translate="string:welcome"></div>
```

```XML
<div i18n:domain="messages" i18n:translate="">welcome</div>
```

```XML
<div i18n:translate="string:messages.welcome"></div>
```

```XML
<div i18n:translate="">messages.welcome</div>
```

#### Pluralization

Translation file:
```php
<?php
# File 'messages.php'
return [
    'apples' => 'There is one apple|There are many apples',
    'oranges' => '{0} There are none|[1,19] There are some|[20,Inf] There are many',    
];
```

```XML
<div 18n:domain="messages" i18n:translate="string:apples|5">
    <!--There are many apples -->
</div>
```

```XML
<span tal:define="q php:22" i18n:translate="string:messages.oranges|${q}">
    <!--There are many -->
</span> oranges
```

#### Interpolation

```php
<?php
# File 'messages.php'
return [
    'welcome_ex1' => 'Welcome ${user}, you have ${mails} unread mails.',
    'welcome_ex2' => 'Welcome :name!',
    'welcome_ex3' => 'Welcome :anonymous!',
    'anonymous' => 'anonymous user',
];
```

```XML
<div 18n:domain="messages" i18n:translate="string:welcome_ex1">
    <span i18name="name" tal:replace="user/name"/>
    <span i18name="name" tal:replace="user/nbrMails"/>
</div>
```

```XML
<div 18n:domain="messages" i18n:translate="string:welcome_ex1||user=${user/name}|mails=${user/nbrMails}">    
</div>
```

```XML
<div tal:define="name user/name" 18n:domain="messages" i18n:translate="string:welcome_ex2">    
</div>
```


```XML
<div 18n:domain="messages" i18n:translate="string:welcome_ex3">
    <!-- Welcome anonymous user! -->    
</div>
```
