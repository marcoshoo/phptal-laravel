<?php

namespace MarcosHoo\LaravelPHPTAL;

use MarcosHoo\LaravelPHPTAL\Engines\PHPTALEngine;

class PHPTALTranslator implements \PHPTAL_TranslationService
{
    protected $language = 'en';
    protected $encoding = 'UTF-8';
    protected $domain = 'messages';
    protected $context = [];
    protected $engine = null;

    /**
     *
     * @param PHPTALEngine $engine
     */
    public function __construct(PHPTALEngine $engine = null)
    {
        $this->engine = $engine;
    }

    /**
     * Set the target language for translations.
     * From the func_get_args()
     *
     * @return string - chosen language
     */
    function setLanguage()
    {
        $languages = func_get_args();
        foreach ($languages as $language) {
            $this->language = $language;
            break;
        }
        return $this->language;
    }

    /**
     * PHPTAL will inform translation service what encoding page uses.
     * Output of translate() must be in this encoding.
     */
    function setEncoding($encoding)
    {
        $this->encoding = $encoding;
    }

    /**
     * Set the domain to use for translations (if different parts of application are translated in different files.
     * This is not for language selection).
     */
    function useDomain($domain)
    {
        if ($domain && $domain != $this->domain) {
            $this->domain = $domain;
        }
        return $domain;
    }

    /**
     * Set XHTML-escaped value of a variable used in translation key.
     *
     * You should use it to replace all ${key}s with values in translated strings.
     *
     * @param string $key
     *            - name of the variable
     * @param string $value_escaped
     *            - XHTML markup
     */
    function setVar($key, $value_escaped)
    {
        $this->context[$key] = $value_escaped;
    }

    /**
     *
     * @param
     *            $domain
     * @param string $path
     */
    function addDomain($domain, $path = './locale')
    {
        $this->useDomain($domain);
    }
    /**
     * Translate a message key and interpolate variables.
     *
     * @param string $key
     *            - translation key, e.g. "hello ${username}!" or "hello :username!"
     * @param string $htmlescape
     *            - if true, you should HTML-escape translated string. You should never HTML-escape interpolated variables.
     */
    function translate($message, $htmlescape = true)
    {
        $info = explode('|', $message);
        $number = count($info) > 1 ? ($info[1] != 'null' && $info[1] != '' ? floatval($info[1]) : null) : null;
        $params = count($info) > 2 ? array_slice($info, 2) : [];
        $info = explode('.', head($info));
        $key = count($info) > 1 ? $info[1] : $info[0];
        $domain = count($info) > 1 ? $info[0] : $this->domain;
        $fullkey = $domain . '.' . $key;
        $contextkey = $this->language . '.' . $domain . '.' . $key;

        // Get parameters in call
        $parameters = [];
        foreach ($params as $key) {
            $info = explode('=', $key);
            $pkey = trim(head($info));
            $pvalue = count($info) ? implode('=', array_slice($info, 1)) : null;
            $pvalue = htmlspecialchars($pvalue, ENT_COMPAT | ENT_HTML401, $this->encoding) ?  : '';
            $parameters = array_merge($parameters, eval('return [\'' . $pkey . '\'=>\'' . $pvalue . '\'];'));
        }

        // Search context
        if (!array_key_exists($contextkey, $this->context)) {
            // Not found
            // Get Laravel translation
            $value = ($number !== null) ? trans_choice($fullkey, $number, $parameters, $domain, $this->language) : trans($fullkey, $parameters, $domain, $this->language);
            if ($value != $fullkey) {
                $this->context[$contextkey] = $value;
            }
        } else {
            // Found
            $value = $this->context[$contextkey];
        }

        if ($htmlescape) {
            $value = htmlspecialchars($value, ENT_COMPAT | ENT_HTML401, $this->encoding);
        }

        // Interpolation
        while (preg_match('/\${(.*?)\}|:([A-Za-z](\w+)?)/sm', $value, $matches)) {
            if (count($matches) == 2) {
                list($source,$key) = $matches;
            } else {
                list($source,$dummy,$key) = $matches;
            }
            $fullkey = $this->language . '.' . $domain . '.' . $key;
            $found = false;
            if (array_key_exists($key, $parameters)) { // Search in parameters
                $value = str_replace($source, $parameters[$key], $value);
                $found = true;
            } elseif (array_key_exists($key, $this->context)) { // Search i18n:name in context
                $value = str_replace($source, $this->context[$key], $value);
                $found = true;
            } elseif (array_key_exists($fullkey, $this->context)) { // Search in context
                $value = str_replace($source, $this->context[$fullkey], $value);
                $found = true;
            }
            if (!$found) {
                try {
                    // Try to get a translation
                    $trans = $this->translate($key, $htmlescape);
                    $this->useDomain($domain);
                    if ($trans == $key) {
                        throw new \Exception(sprintf('Interpolation error, variable "%s" not set', $key));
                    }
                    $value = str_replace($source, $trans, $value);
                    $found = true;
                } catch (Exception $e) {
                    if ($this->engine) { // Search in PHPTAL context
                        if (isset($this->engine->getPHPTAL()->getContext()->$key)) { // Local context
                            $key = $this->engine->getPHPTAL()->getContext()->$key;
                            $value = str_replace($source, $key, $value);
                        } elseif (isset($this->engine->getPHPTAL()->getGlobalContext()->$key)) { // Global context
                            $key = $this->engine->getPHPTAL()->getGlobalContext()->$key;
                            $value = str_replace($source, $key, $value);
                        } else {
                            throw new \Exception(sprintf('Interpolation error, variable "%s" not set', $matches[0]));
                        }
                    }
                }
            }
        }

        return $value;
    }
}
