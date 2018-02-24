<?php

namespace youconix\core\services;

/**
 * Language-handler for making your website language-independand
 *
 * This file is part of Miniature-happiness
 *
 * @copyright Youconix
 * @author Rachelle Scheijen
 * @version 1.0
 * @since 1.0
 */
abstract class Language extends \youconix\core\services\Service implements \Language
{
    /** @var \youconix\core\services\Language */
    protected static $_instance;

    /** @var string */
    protected $encoding = null;

    /** @var \youconix\core\services\File */
    protected $file;

    /** @var string */
    protected $cacheDir;

    /** @var string */
    protected $language;

    /** @var array */
    protected $mainLanguage = [];

    /** @var array */
    protected $fallbackLanguage = [];

    /** @var string */
    protected $mainStartTag = 'language';

    public function __construct(\youconix\core\services\File $file, \Config $config)
    {
        $this->file = $file;

        $this->language = $config->getLanguage();
        $languageFallback = $config->getSettings()->get('fallbackLanguage');
        $this->cacheDir = $config->getCacheDirectory();

        $this->mainLanguage = $this->readLanguage($this->language);
        if (!empty($languageFallback)) {
            $this->fallbackLanguage = $this->readLanguage($languageFallback);
        }

        /* Get encoding */
        $this->encoding = $this->get('language/encoding');
    }

    /**
     * Returns if the object schould be treated as singleton
     *
     * @return boolean True if the object is a singleton
     */
    public static function isSingleton()
    {
        return true;
    }

    /**
     * @param string $language
     * @return array
     */
    abstract protected function readLanguage($language);

    /**
     * Gets the name belonging to the language code
     *
     * @param string $code The language code
     * @return string   The language name
     */
    public function getLanguageText($code)
    {
        \youconix\core\Memory::type('string', $code);

        $codes = $this->getLanguageCodes();
        if (array_key_exists($code, $codes)) {
            return $codes[$code];
        }

        return $code;
    }

    /**
     * Returns the language codes
     *
     * @return array    The codes
     */
    public function getLanguageCodes()
    {
        return [
            'nl-BE' => 'Vlaams',
            'nl-NL' => 'Nederlands',
            'en-AU' => 'English Australia',
            'en-BW' => 'English (Botswana)',
            'en-CA' => 'English (Canada)',
            'en-DK' => 'English (Denmark)',
            'en-GB' => 'English (Great Brittan)',
            'en-UK' => 'English (Great Brittan)',
            'en-HK' => 'English (Hong Kong)',
            'en-IE' => 'English (Ireland)',
            'en-IN' => 'English (India)',
            'en-NZ' => 'English (New Zealand)',
            'en-PH' => 'English (Philippines)',
            'en-SG' => 'English (Singapore)',
            'en-US' => 'English (United States)',
            'en-ZA' => 'English (South Africa)',
            'en-ZW' => 'English (Zimbabwe)'
        ];
    }

    /**
     * Sets the language
     *
     * @param string $language
     * @throws \IOException when the language code does not exist
     */
    public function setLanguage($language)
    {
        \youconix\core\Memory::type('string', $language);

        $this->language = $language;

        $this->mainLanguage = $this->readLanguage($this->language);
    }

    /**
     * Returns the set language
     *
     * @return string The set language
     */
    public function getLanguage()
    {
        return $this->language;
    }

    /**
     * Returns the set encoding
     *
     * @return string The set encoding
     */
    public function getEncoding()
    {
        return $this->encoding;
    }

    /**
     * @param string $key
     * @return string
     * @throws \IOException
     */
    public function get($key)
    {
        \youconix\core\Memory::type('string', $key);

        $key = $this->encodeKey($key);

        if (array_key_exists($key, $this->mainLanguage)) {
            return $this->mainLanguage[$key];
        }
        if (array_key_exists($this->mainStartTag . '.' . $key, $this->mainLanguage)) {
            return $this->mainLanguage[$this->mainStartTag . '.' . $key];
        }
        if (array_key_exists($key, $this->fallbackLanguage)) {
            return $this->fallbackLanguage[$key];
        }
        if (array_key_exists($this->mainStartTag . '.' . $key, $this->fallbackLanguage)) {
            return $this->fallbackLanguage[$this->mainStartTag . '.' . $key];
        }

        throw new \IOException('Call to unknown language key ' . $key . '.');
    }

    /**
     * Changes the language-values with the given values
     * Collects the text from the language file via the path
     *
     * @param String $path
     *            The path to the language-part
     * @param array $fields
     *            accepts also a string
     * @param array $values
     *            accepts also a string
     * @return string changed language-string
     * @throws IOException when the path does not exist
     */
    public function insertPath($path, $fields, $values)
    {
        \youconix\core\Memory::type('string', $path);

        $text = $this->get($path);
        return $this->insert($text, $fields, $values);
    }

    /**
     * Changes the language-values with the given values
     *
     * @param string $text
     * @param array $fields
     *            accepts also a string
     * @param array $values
     *            accepts also a string
     * @return string changed language-string
     */
    public function insert($text, $fields, $values)
    {
        \youconix\core\Memory::type('string', $text);

        if (!is_array($fields)) {
            $text = str_replace('[' . $fields . ']', $values, $text);
        } else {
            for ($i = 0; $i < count($fields); $i++) {
                $text = str_replace('[' . $fields[$i] . ']', $values[$i], $text);
            }
        }

        return $text;
    }

    /**
     * Checks of the given part of the loaded file exists
     *
     * @param string $key
     * @return boolean, true if the part exists otherwise false
     */
    public function exists($key)
    {
        $key = $this->encodeKey($key);

        if (array_key_exists($key, $this->mainLanguage)) {
            return true;
        }
        if (array_key_exists($key, $this->fallbackLanguage)) {
            return true;
        }
        return false;
    }

    /**
     * @param string $key
     * @return string
     */
    protected function encodeKey($key)
    {
        $key = str_replace('/', '.', $key);
        $keys = explode('.', $key);

        return $key;
    }

    /**
     * @param string $language
     * @return array|null
     */
    protected function readCacheFile($language)
    {
        $cacheFile = $this->createCacheFileName($language);
        if (defined('DEBBUG') || !$this->file->exists($cacheFile)) {
            return null;
        }

        $content = $this->file->readFile($cacheFile);

        return unserialize($content);
    }

    /**
     * @param string $language
     * @param array $documents
     */
    protected function writeCacheFile($language, array $documents)
    {
        $cacheFile = $this->createCacheFileName($language);
        $this->file->writeFile($cacheFile, serialize($documents));
    }

    /**
     * @param string $language
     * @return string
     */
    protected function createCacheFileName($language)
    {
        return $this->cacheDir . 'language_' . $language . '.php';
    }

    /**
     * Returns the text
     * Alias of get()
     *
     * @param string $key
     *            The path to the language-part
     * @return string The content of the requested part
     * @throws \IOException when the path does not exist
     */
    public static function text($key)
    {
        if (is_null(Language::$_instance)) {
            Language::$_instance = \Loader::inject('\Language');
        }

        return Language::$_instance->get($key);
    }
}