<?php

namespace MarcosHoo\LaravelPHPTAL;

// github enhancement request #1 by towel (https://github.com/towel)
// This an example of implementing your own Translation Service
class PHPTALTranslator implements \PHPTAL_TranslationService
{
    protected $language = 'en';
    protected $encoding = 'UTF-8';
    protected $path = '';
    protected $domains = array();
    protected $context = array();

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
        $found = false;
        if (!array_key_exists($domain, $this->domains)) {
            $files = [
                base_path() . "/resources/lang/{$this->language}/{$domain}.php",
                base_path() . "/resources/lang/{$this->language}/domains/{$domain}.php",
                base_path() . "/lang/{$this->language}/domains/{$domain}.php"
            ];
            foreach ($files as $file) {
                if (is_readable($file)) {
                    $found = true;
                    break;
                }
            }
            if ($found) {
                $this->domains[$domain] = include ($file);
                $this->context = $this->domains[$domain];
            }
        }
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
        bindtextdomain($domain, $path);
        if ($this->encoding) {
            bind_textdomain_codeset($domain, $this->encoding);
        }
        $this->useDomain($domain);
    }
    /**
     * Translate a gettext key and interpolate variables.
     *
     * @param string $key
     *            - translation key, e.g. "hello ${username}!"
     * @param string $htmlescape
     *            - if true, you should HTML-escape translated string. You should never HTML-escape interpolated variables.
     */
    function translate($key, $htmlescape = true)
    {
        $value = $this->context[$key];

        if ($htmlescape) {
            $value = htmlspecialchars($value, ENT_COMPAT | ENT_HTML401, $this->encoding);
        }

        while (preg_match('/\${(.*?)\}/sm', $value, $matches)) {
            list($source,$field) = $matches;
            if (!array_keys_exists($field, $this->context)) {
                throw new \Exception(sprintf('Interpolation error, variable "%s" not set', $field));
            }
            $value = str_replace($source, $this->context[$var], $value);
        }
        return $value;
    }
}
