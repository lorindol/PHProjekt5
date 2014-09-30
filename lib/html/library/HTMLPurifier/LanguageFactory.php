<?php

require_once 'HTMLPurifier/Language.php';
require_once 'HTMLPurifier/AttrDef/Lang.php';

/**
 * Class responsible for generating HTMLPurifier_Language objects, managing
 * caching and fallbacks.
 * @note Thanks to MediaWiki for the general logic, although this version
 *       has been entirely rewritten
 */
class HTMLPurifier_LanguageFactory
{

    /**
     * Cache of language code information used to load HTMLPurifier_Language objects
     * Structure is: $factory->cache[$language_code][$key] = $value
     * @value array map
     */
    var $cache;

    /**
     * Valid keys in the HTMLPurifier_Language object. Designates which
     * variables to slurp out of a message file.
     * @value array list
     */
    var $keys = array('fallback', 'messages');

    /**
     * Instance of HTMLPurifier_AttrDef_Lang to validate language codes
     * @value object HTMLPurifier_AttrDef_Lang
     */
    var $validator;

    /**
     * Cached copy of dirname(__FILE__), directory of current file without
     * trailing slash
     * @value string filename
     */
    var $dir;

    /**
     * Keys whose contents are a hash map and can be merged
     * @value array lookup
     */
    var $mergeable_keys_map = array('messages' => true);

    /**
     * Keys whose contents are a list and can be merged
     * @value array lookup
     */
    var $mergeable_keys_list = array();

    /**
     * Retrieve sole instance of the factory.
     * @static
     * @param $prototype Optional prototype to overload sole instance with,
     *                   or bool true to reset to default factory.
     */
    function &instance($prototype = null) {
        static $instance = null;
        if ($prototype !== null) {
            $instance = $prototype;
        } elseif ($instance === null || $prototype == true) {
            $instance = new HTMLPurifier_LanguageFactory();
            $instance->setup();
        }
        return $instance;
    }

    /**
     * Sets up the singleton, much like a constructor
     * @note Prevents people from getting this outside of the singleton
     */
    function setup() {
        $this->validator = new HTMLPurifier_AttrDef_Lang();
        $this->dir = dirname(__FILE__);
    }

    /**
     * Creates a language object, handles class fallbacks
     * @param $code string language code
     */
    function create($code) {

        $config = $context = false; // hope it doesn't use these!
        $code = $this->validator->validate($code, $config, $context);
        if ($code === false) $code = 'en'; // malformed code becomes English

        $pcode = str_replace('-', '_', $code); // make valid PHP classname
        static $depth = 0; // recursion protection

        if ($code == 'en') {
            $class = 'HTMLPurifier_Language';
            $file  = $this->dir . '/Language.php';
        } else {
            $class = 'HTMLPurifier_Language_' . $pcode;
            $file  = $this->dir . '/Language/classes/' . $code . '.php';
            // PHP5/APC deps bug workaround can go here
            // you can bypass the conditional include by loading the
            // file yourself
            if (file_exists($file) && !class_exists($class)) {
				include_once $file;
			}
        }

        if (!class_exists($class)) {
            // go fallback
            $fallback = HTMLPurifier_Language::getFallbackFor($code);
            $depth++;
            $lang = Language::factory( $fallback );
            $depth--;
        } else {
            $lang = new $class;
        }
        $lang->code = $code;

        return $lang;

    }

    /**
     * Returns the fallback language for language
     * @note Loads the original language into cache
     * @param $code string language code
     */
    function getFallbackFor($code) {
        $this->loadLanguage($code);
        return $this->cache[$code]['fallback'];
    }

    /**
     * Loads language into the cache, handles message file and fallbacks
     * @param $code string language code
     */
    function loadLanguage($code) {
        static $languages_seen = array(); // recursion guard

        // abort if we've already loaded it
        if (isset($this->cache[$code])) return;

        // generate filename
        $filename = $this->dir . '/Language/messages/' . $code . '.php';

        // default fallback : may be overwritten by the ensuing include
        $fallback = ($code != 'en') ? 'en' : false;

        // load primary localisation
        if (!file_exists($filename)) {
            // skip the include: will rely solely on fallback
            $filename = $this->dir . '/Language/messages/en.php';
            $cache = array();
        } else {
            include $filename;
            $cache = compact($this->keys);
        }

        // load fallback localisation
        if (!empty($fallback)) {

            // infinite recursion guard
            if (isset($languages_seen[$code])) {
                trigger_error('Circular fallback reference in language ' .
                    $code, E_USER_ERROR);
                $fallback = 'en';
            }
            $language_seen[$code] = true;

            // load the fallback recursively
            $this->loadLanguage($fallback);
            $fallback_cache = $this->cache[$fallback];

            // merge fallback with current language
            foreach ( $this->keys as $key ) {
				if (isset($cache[$key]) && isset($fallback_cache[$key])) {
                    if (isset($this->mergeable_keys_map[$key])) {
                        $cache[$key] = $cache[$key] + $fallback_cache[$key];
                    } elseif (isset($this->mergeable_keys_list[$key])) {
                        $cache[$key] = array_merge( $fallback_cache[$key], $cache[$key] );
                    }
				} else {
					$cache[$key] = $fallback_cache[$key];
				}
            }

        }

        // save to cache for later retrieval
        $this->cache[$code] = $cache;

        return;
    }
}
?>