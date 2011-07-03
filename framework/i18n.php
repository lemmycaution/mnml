<?
class I18n{
	/*
	 * stores languages
	 */
	public static $languages = array();
	
	/*
	 * stores translated words (public/i18n)
	 */	
	public static $translations = array();
	
	/*
	 * translates given string to current or given language
	 */	
	public static function translate( $string, $lang=LANG ){
		if ( array_key_exists($lang,self::$translations) && array_key_exists($string,self::$translations[$lang] ) )
			return self::$translations[$lang][$string];
		return null;
	}
}
?>