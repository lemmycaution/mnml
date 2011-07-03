<?php

/**
 * Inflector class definition.
 *
 * LICENSE
 *
 * This source file is subject to the MIT license that is bundled with this
 * package in the file MIT-LICENSE.
 *
 * It is also available through the world-wide-web at this URL:
 * http://www.opensource.org/licenses/mit-license.php
 * 
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@zend.com so we can send you a copy immediately.
 *
 * @copyright  2010 Javier Martinez Fernandez
 * @license    http://www.opensource.org/licenses/mit-license.php MIT License
 * @link       http://github.com/comodo/comodo/blob/master/lib/inflector.php
 * @since      1.0
 * @version    $Id$
 */

/**
 * Inflector class definition.
 * 
 * The Inflector transforms words from singular to plural, class names to table
 * names and some other string conversions.
 * 
 * The default inflections for pluralization, singularization, and uncountable
 * words are kept in config/inflections.php. 
 *
 * @copyright  2010 Javier Martinez Fernandez
 * @license    http://www.opensource.org/licenses/mit-license.php MIT License
 * @link       http://github.com/comodo/comodo/blob/master/lib/inflector.php
 * @since      1.0
 * @version    $Id$
 */
abstract class Inflector
{
	/**
     * Check if the given word is in singular form.
     * 
     * @param  string word The word to check.
     * @return boolean     True if it's in singular form, false otherwise.
     */
    public static function is_singular($word)
    {
        

        return self::singularize($word) == $word;
    }

    /**
     * Check if the given word is in plural form.
     * 
     * @param  string word The word to check.
     * @return boolean     True if it's in pluralform, false otherwise.
     */
    public static function is_plural($word)
    {
       

        return self::pluralize($word) == $word;
    }
    
function pluralize($word) {
        $result = strval($word);

        if (in_array(strtolower($result), self::uncountable_words()) || strpos(strtolower($result),"join")>-1 ) {
            return $result;
        } else {
            foreach(self::plural_rules() as $rule => $replacement) {
                if (preg_match($rule, $result)) {
                    $result = preg_replace($rule, $replacement, $result);
                    break;
                }
            }

            return $result;
        }
    }

    function singularize($word) {
        $result = strval($word);

        if ( in_array(strtolower($result), self::uncountable_words()) || strpos(strtolower($result),"join")>-1 ) {
            return $result;
        } else {
            foreach(self::singular_rules() as $rule => $replacement) {
                if (preg_match($rule, $result)) {
                    $result = preg_replace($rule, $replacement, $result);
                    break;
                }
            }

            return $result;
        }
    }

    function camelize($lower_case_and_underscored_word) {
        return preg_replace('/(^|_)(.)/e', "strtoupper('\\2')", strval($lower_case_and_underscored_word));
    }
  
    function underscore($camel_cased_word) {
        return strtolower(preg_replace('/([A-Z]+)([A-Z])/','\1_\2', preg_replace('/([a-z\d])([A-Z])/','\1_\2', strval($camel_cased_word))));
    }

    function humanize($lower_case_and_underscored_word) {
        return ucfirst(strtolower(str_replace('_', " ", strval($lower_case_and_underscored_word))));
    }

    function demodulize($class_name_in_module) {
        return preg_replace('/^.*::/', '', strval($class_name_in_module));
    }

    function tableize($class_name) {
        return self::pluralize(self::underscore($class_name));
    }

    function classify($table_name) {
        return self::camelize(self::singularize($table_name));
    }

    function foreign_key($class_name, $separate_class_name_and_id_with_underscore = true) {
        return self::underscore(self::demodulize($class_name)) .
          ($separate_class_name_and_id_with_underscore ? "_id" : "id");
    }

    function constantize($camel_cased_word=NULL) {
    }

 	function ordinalize($number)
    {
        //Observer::trigger('Inflector.ordinalize', array(&$number));

        if (in_array(($number % 100), range(11, 13)))
            return $number . 'th';

        switch (($number % 10)) {
            case 1:
                return $number . 'st';

            case 2:
                return $number . 'nd';

            case 3:
                return $number . 'rd';

            default:
                return $number . 'th';
        }
    }

	function urlize( $name ){
		return str_replace(" ", "-", $name );
	}
	function deurlize( $name ){
		return str_replace("-", " ", $name );
	}

    function uncountable_words() { #:doc
        return array( 'equipment', 'information', 'rice', 'money', 'species', 'series', 'fish', 'cms','Amoyese', 'bison', 'Borghese', 'bream',
    'breeches', 'britches', 'buffalo', 'cantus', 'carp', 'chassis', 'clippers',
    'cod', 'coitus', 'Congoese', 'contretemps', 'corps', 'debris', 'diabetes',
    'djinn', 'eland', 'elk', 'equipment', 'Faroese', 'flounder', 'Foochowese',
    'gallows', 'Genevese', 'Genoese', 'Gilbertese', 'graffiti', 'headquarters',
    'herpes', 'hijinks', 'Hottentotese', 'information', 'innings',
    'jackanapes', 'Kiplingese', 'Kongoese', 'Lucchese', 'mackerel', 'Maltese',
    'mews', 'moose', 'mumps', 'Nankingese', 'news', 'nexus', 'Niasese',
    'Pekingese', 'Piedmontese', 'pincers', 'Pistoiese', 'pliers', 'Portuguese',
    'proceedings', 'rabies', 'rice', 'rhinoceros', 'salmon', 'Sarawakese',
    'scissors', 'sea[- ]bass', 'series', 'Shavese', 'shears', 'siemens',
    'species', 'swine', 'testes', 'trousers', 'trout', 'tuna', 'Vermontese',
    'Wenchowese', 'whiting', 'wildebeest', 'Yengeese', );
    }
  
    function plural_rules() { #:doc:
        return array(
            '/^(ox)$/'                => '\1\2en',     # ox
            '/([m|l])ouse$/'          => '\1ice',      # mouse, louse
            '/(matr|vert|ind)ix|ex$/' => '\1ices',     # matrix, vertex, index
            '/(x|ch|ss|sh)$/'         => '\1es',       # search, switch, fix, box, process, address
            #'/([^aeiouy]|qu)ies$/'    => '\1y', -- seems to be a bug(?)
            '/([^aeiouy]|qu)y$/'      => '\1ies',      # query, ability, agency
            '/(hive)$/'               => '\1s',        # archive, hive
            '/(?:([^f])fe|([lr])f)$/' => '\1\2ves',    # half, safe, wife
            '/sis$/'                  => 'ses',        # basis, diagnosis
            '/([ti])um$/'             => '\1a',        # datum, medium
            '/(p)erson$/'             => '\1eople',    # person, salesperson
            '/(m)an$/'                => '\1en',       # man, woman, spokesman
            '/(c)hild$/'              => '\1hildren',  # child
            '/(buffal|tomat)o$/'      => '\1\2oes',    # buffalo, tomato
            '/(bu)s$/'                => '\1\2ses',    # bus
            '/(alias|status)/'        => '\1es',       # alias
            '/(octop|vir)us$/'        => '\1i',        # octopus, virus - virus has no defined plural (according to Latin/dictionary.com), but viri is better than viruses/viruss
            '/(ax|cri|test)is$/'      => '\1es',       # axis, crisis
            '/s$/'                    => 's',          # no change (compatibility)
            '/$/'                     => 's'
        );
    }

    function singular_rules() { #:doc:
        return array(
            '/(matr)ices$/'         =>'\1ix',
            '/(vert|ind)ices$/'     => '\1ex',
            '/^(ox)en/'             => '\1',
            '/(alias)es$/'          => '\1',
            '/([octop|vir])i$/'     => '\1us',
            '/(cris|ax|test)es$/'   => '\1is',
            '/(shoe)s$/'            => '\1',
            '/(o)es$/'              => '\1',
            '/(bus)es$/'            => '\1',
            '/([m|l])ice$/'         => '\1ouse',
            '/(x|ch|ss|sh)es$/'     => '\1',
            '/(m)ovies$/'           => '\1\2ovie',
            '/(s)eries$/'           => '\1\2eries',
            '/([^aeiouy]|qu)ies$/'  => '\1y',
            '/([lr])ves$/'          => '\1f',
            '/(tive)s$/'            => '\1',
            '/(hive)s$/'            => '\1',
            '/([^f])ves$/'          => '\1fe',
            '/(^analy)ses$/'        => '\1sis',
            '/((a)naly|(b)a|(d)iagno|(p)arenthe|(p)rogno|(s)ynop|(t)he)ses$/' => '\1\2sis',
            '/([ti])a$/'            => '\1um',
            '/(p)eople$/'           => '\1\2erson',
            '/(m)en$/'              => '\1an',
            '/(s)tatuses$/'         => '\1\2tatus',
            '/(c)hildren$/'         => '\1\2hild',
            '/(n)ews$/'             => '\1\2ews',
            '/s$/'                  => ''
        );
    }
    

}