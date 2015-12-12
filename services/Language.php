<?php
namespace core\services;

/**
 * Miniature-happiness is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Lesser General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * Miniature-happiness is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU Lesser General Public License
 * along with Miniature-happiness. If not, see <http://www.gnu.org/licenses/>.
 *
 * Language-handler for making your website language-independand
 *
 * This file is part of Miniature-happiness
 *
 * @copyright Youconix
 * @author Rachelle Scheijen
 * @version 1.0
 * @since 1.0
 */
abstract class Language extends \core\services\Service  implements \Language
{
    private static $_instance;

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
     * Gets the name belonging to the language code
     * 
     * @param string $s_code    The language code
     * @return string   The language name
     */
    public function getLanguageText($s_code)
    {
        $a_codes = $this->getLanguageCodes();
        if (array_key_exists($s_code, $a_codes)) {
            return $a_codes[$s_code];
        }
        
        return $s_code;
    }

    /**
     * Returns the language codes
     * 
     * @return array    The codes
     */
    public function getLanguageCodes()
    {
        return array(
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
        );
    }

    /**
     * Sets the language
     *
     * @param string $s_language
     *            code
     * @throws IOException when the language code does not exist
     */
    public function setLanguage($s_language)
    {
        $this->s_language = $s_language;
        
        $this->a_documents = $this->loadLanguageParser($this->s_language);
    }

    /**
     * Returns the set language
     *
     * @return string The set language
     */
    public function getLanguage()
    {
        return $this->s_language;
    }

    /**
     * Returns the set encoding
     *
     * @return string The set encoding
     */
    public function getEncoding()
    {
        return $this->get('encoding');
    }

    /**
     * Returns the text
     * Alias of get()
     * 
     * @param string $s_path
     *            The path to the language-part
     * @return string The content of the requested part
     * @throws XMLException when the path does not exist
     */
    public static function text($s_key)
    {
        if (is_null(Language::$_instance)) {
            Language::$_instance = \Loader::inject('\Language');
        }
        
        return Language::$_instance->get($s_key);
    }
}