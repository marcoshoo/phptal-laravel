<?php

namespace MarcosHoo\LaravelPHPTAL\Engines;

use Illuminate\View\Engines\EngineInterface;
use MarcosHoo\LaravelPHPTAL\PHPTALFilterChain;
use MarcosHoo\LaravelPHPTAL\Translator;
use Illuminate\Foundation\Application;
use Illuminate\Filesystem\Filesystem;

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
    protected $translationSettings = array();

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

        $preFilters = $this->config->get('phptal.preFilters', array());
        $postFilters = $this->config->get('phptal.postFilters', array());
        $encoding = $this->config->get('phptal.encoding', 'UTF-8');
        $outputMode = $this->config->get('phptal.outputMode', \PHPTAL::HTML5);
        $phpCodeDestination = $this->config->get('phptal.phpCodeDestination', $app['path.storage'] . '/framework/views');
        $forceReparse = $this->config->get('phptal.forceParse', true);
        $templateRepositories = $this->config->get('phptal.templateRepositories', $app['path.base'] . '/resources/views' . (TEMPLATE_ID ? '/' . TEMPLATE_ID : ''));
        $translationClass = $this->config->get('phptal.translationClass');
        $translationLanguages = $this->config->get('phptal.translationLanguages', [
            'en'
        ]);
        $translationFilename = $this->config->get('phptal.translationFilename', 'translations');
        $translationPath = $this->config->get('phptal.translationPath', $app['path.base'] . '/lang/');

        // Create code destination directory
        $disk = $app['files'];
        if (!$disk->isDirectory($phpCodeDestination)) {
            $disk->makeDirectory($phpCodeDestination, 0755, true, true);
        }

        // Setting up translation settings
        $this->translationSettings['encoding'] = $encoding;
        $this->translationSettings['path'] = $translationPath;
        $this->translationSettings['languages'] = $translationLanguages;

        if (!empty($translationClass)) {
            $this->setTranslator($translationLanguages, $translationFilename, $translationClass);
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
    public function setTranslator($languages = null, $domain = '', $translatorClass = '\PHPTAL_GetTextTranslator')
    {
        if ($languages === null) {
            $languages = array(
                $this->config->get('app.locale')
            );
        }

        $translator = new $translatorClass();

        call_user_func_array(array(
            $translator,
            'setLanguage'
        ), $languages);
        $translator->setEncoding($this->translationSettings['encoding']);
        $translator->addDomain($domain, $this->translationSettings['path']);

        $this->phptal->setTranslator($translator);
    }

    /**
     * Get the evaluated contents of the view.
     *
     * @param string $path
     * @param array $data
     *
     * @return string
     */
    public function get($path, array $data = array())
    {
        if (!empty($data)) {
            foreach ($data as $field => $value) {
                if (!preg_match('/^_|\s/', $field)) {
                    $this->phptal->set($field, $value);
                }
            }
        }
        $this->phptal->setTemplate($path);
        return $this->phptal->execute();
    }
}
