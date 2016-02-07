<?php

namespace MarcosHoo\LaravelPHPTAL\Engines;

use Illuminate\View\Engines\EngineInterface;
use MarcosHoo\LaravelPHPTAL\PHPTALFilterChain;
use MarcosHoo\LaravelPHPTAL\Translator;
use Illuminate\Foundation\Application;

class PHPTALEngine implements EngineInterface
{
    /**
     *
     * @var \PHPTAL
     */
    protected $phptal;

    /**
     *
     * @var Illuminate\Foundation\Application
     */
    protected $app;

    /**
     *
     * @var array
     */
    protected $config;

    /**
     *
     * @var array
     */
    protected $translationSettings = [];

    /**
     * Prep the PHPTAL object
     *
     * @param
     *            $app
     */
    public function __construct($app)
    {
        $this->app = $app;
        $this->config = $app['config'];

        $this->phptal = new \PHPTAL();

        // Override the defaults with information from config file

        $preFilters = $this->config->get('phptal.preFilters', []);
        $postFilters = $this->config->get('phptal.postFilters', []);
        $encoding = $this->config->get('phptal.translationEncoding', 'UTF-8');
        $outputMode = $this->config->get('phptal.outputMode', \PHPTAL::HTML5);
        $phpCodeDestination = $this->config->get('phptal.phpCodeDestination', $app['path.storage'] . '/framework/views');
        $forceReparse = $this->config->get('phptal.forceParse', true);
        $templateRepositories = $this->config->get('phptal.templateRepositories', $app['path.base'] . '/resources/views' . (TEMPLATE_ID ? '/' . TEMPLATE_ID : ''));
        $translationClass = $this->config->get('phptal.translationClass');
        $translationDomain = $this->config->get('phptal.translationDomain', 'messages');
        $translationLanguage = [
            $this->app->getLocale()
        ];

        // Setting up translation settings

        $this->translationSettings['encoding'] = $encoding;
        if (!empty($translationClass)) {
            $this->setTranslator($translationLanguage, $translationDomain, $translationClass);
        }
        // Setting up all the filters

        if (!empty($preFilters)) {
            foreach ($preFilters as $filter) {
                $this->phptal->addPreFilter($filter);
            }
        }

        if (!empty($postFilters)) {
            $filterChain = new PHPTALFilterChain();
            foreach ($postFilters as $filter) {
                $filterChain->add($filter);
            }
            $this->phptal->setPostFilter($filterChain);
        }

        $this->phptal->setForceReparse($forceReparse);
        $this->phptal->setOutputMode($outputMode);
        $this->phptal->setTemplateRepository($templateRepositories);
        $this->phptal->setPhpCodeDestination($phpCodeDestination);
    }

    /**
     * Sets the translator
     *
     * @param null $language
     * @param string $domain
     * @param string $translatorClass
     */
    public function setTranslator($languages = [ 'en' ], $domain = 'messages', $translatorClass = '\PHPTAL_GetTextTranslator')
    {
        $translator = new $translatorClass($this);
        $languages = !is_array($languages) ? [
            $languages
        ] : $languages;

        call_user_func_array([
            $translator,
            'setLanguage'
        ], $languages);
        $translator->setEncoding($this->translationSettings['encoding']);
        $translator->addDomain($domain);

        $this->phptal->setTranslator($translator);
    }

    /**
     * Return PHPTAL object.
     *
     * @return \PHPTAL
     */
    public function getPHPTAL()
    {
        return $this->phptal;
    }

    /**
     * Get the evaluated contents of the view.
     *
     * @param string $path
     * @param array $data
     *
     * @return string
     */
    public function get($path, array $data = [])
    {
        if (!empty($data)) {
            foreach ($data as $field => $value) {
                // Creating error properties in ViewErrorBag
                if ($field == 'errors') {
                    foreach ($value->getBags() as $bkey => $bag) {
                        $keys = $bag->keys();
                        foreach ($bag->keys() as $key) {
                            if ($bkey != 'default') {
                                $this->phptal->set($key, [ $bag->get($key) ]);
                            } else {
                                $this->phptal->set($key, $bag->get($key));
                            }
                        }
                    }
                }
                if (!preg_match('/^_|\s/', $field)) {
                    $this->phptal->set($field, $value);
                }
            }
        }
        $this->phptal->setTemplate($path);
        return $this->phptal->execute();
    }
}

if (! function_exists('dc')) {
    /**
     * Dump the passed variables and end the script.
     *
     * @param  mixed
     * @return void
     */
    function dc()
    {
        array_map(function ($x) {
            (new \Illuminate\Support\Debug\Dumper)->dump($x);
        }, func_get_args());

    }
}
