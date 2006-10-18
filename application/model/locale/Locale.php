<?php

ClassLoader::import("library.*");

/**
 * Class for locale support. Gets defined translation values. Also gets languages, countries, currencies and of locale.
 * Depends on Language, InterfaceTranslation, I18Nv2.
 *
 * <code>
 *  // usage of Locale class
 *	$locale = Locale::getInstance("lt");
 *	echo $locale->translate('hello_world');
 *  echo $locale->getCountry('ru')."<br>";
 *  echo $locale->getLangauge('ru');
 *
 * </code>
 * @todo I18Nv2::getInfo();
 */
class Locale {
  
	const value = 'v';
  	const file = 'f';
  	
  	protected static $instanceMap;
  	
  	/**
  	* Locale ID (en, lt, ru, ...)
  	*/
  	private $locale;  	
  	
  	private $definitions = array();
    
	private $country;
	
	private $language;
	
	private $currency;
	
	private $mylocale_make;
	
	private static $currentLocale = false;	
		  
	/**
	 * Gets locale. Flyweight pattern is used, to load data just once.
	 * @param $locale	
	 */
	public static function getInstance($locale) 
	{	  			
	  	if(empty(self::$instanceMap[$locale])) 
		{	  	
		  	$instance = Locale::create($locale);  	  	
		  	if (!$instance)
		  	{
			    return false;
			}
			
			self::$instanceMap[$locale] = $instance;			
		}				
		
		return self::$instanceMap[$locale];	  	
	}
	
	/**
	 * Creates locale by locale ID.
	 * @param string $locale E.g. "en", "lt", "ru"
	 */
	protected static function create($locale) 
	{		
		// gets translation definitions
		try 
		{
			$data = ActiveRecord::getInstanceById("InterfaceTranslation", array("ID" => $locale), true, true);		
		} 
		catch (Exception $ex) 
		{
			return false;
		}

		$instance = new Locale($locale);	

		$instance->definitions = unserialize((string)$data->interfaceData->get());					
	
		foreach ($instance->definitions as $key => $value) 
		{		  
		  	$instance->definitions[$key][Locale::value] = stripslashes($value[Locale::value]);
		}	
		
		return $instance;	
	}
		
	/**
	 * Do not allow to call constructor directly as we need to verify that such locale exists first
	 */
	private function __construct($locale)
	{
		$this->locale = $locale;	  
	}
	
	/**
	 * Sets current locale
	 * @param string $locale.
	 */
	public static function setCurrentLocale($locale) {
		
		self::$currentLocale = $locale;
	}
	
	/**
	 * Gets locale, whick is defined as current {@see Locale::setCurrentLocale}
	 * @return Locale
	 */
	public static function getCurrentLocale() {
	  
	  	return Locale::getInstance(self::$currentLocale);
	}
	
	/**
	 * Translates text to current locale.
	 * @param string $key
	 * @return string
	 */
	public function translate($key) {
	 
	 	if (!empty($this->definitions[$key][Locale::value])) {
		  
			return $this->definitions[$key][Locale::value];
		} else {
		  
		  	return $key;
		}	  		
	}
	
	/**
	 *
	 */
	public function makeText($key, $params) {
	  	  		  
	  	if (!empty($this->definitions[$key][Locale::value])) {
			
			$lh = $this->getLocaleMakeTextInstance();
			$list[] = $this->translate($key);		
						
			$list = array_merge($list, split(",", $params));						
			return call_user_func_array(array($lh, "_"), $list);
		} else {

		  	return $key;
		}
	}
		
	/**
	 * Gets all defition files
	 * @param string $ext
	 * @return array
	 */
	public function getDefinitionFiles($ext = '') {
	  
	  	$files = array();
	  	foreach ($this->definitions as $key => $value) {
		    		    
		    if (empty($files[$value[Locale::file]])) {
			  
				$files[$value[Locale::file]] = $value[Locale::file].$ext;
			}
		}
	  	
	 	return $files; 	
	}
	 
	/**
	 * Gets all definitions.
	 */
	public function /*&*/getFullDefinitionsArray() {
	  
	  	return $this->definitions;
	}
	 
	 
	public function getDefinitionsFromFile($file) {
	  
	  	$defs = array();
	  	foreach ($this->definitions as $key => $value)  {
		    
		    if ($value[Locale::file] == $file) {
	
			    $defs[$key] = $value[Locale::value];
			}
		}
		
		return $defs;
	} 	
		
	public function getAllDefinitions() {
	  	  
	  	$defs = array();
	  	foreach ($this->definitions as $key => $value)  {
		    
		    $defs[$key] = $value[Locale::value];
		}
		
		return $defs;
	}
	
	protected function getLocaleMakeTextInstance() {
	  
	  	if (empty($this->mylocale_make)) {

			$this->mylocale_make = MyLocale_Maketext::factory('MyLocale_Maketext', $this->locale);	    
		}
		
		return $this->mylocale_make;
	}
		
	
	protected function countryInstance() {
	  
	  	if (empty($this->country)) {
	  	  
	  	 	require_once("I18Nv2\\Country.php");
			$this->country = new I18Nv2_Country($this->locale);		    
		}
		
		return $this->country;
	}
	
	/**
	 * Gets array of countries names.
	 * @return array
	 */
	public function getCountries() {
	  
	  	$country = $this->CountryInstance();
	  	return $country->getAllCodes();	
	}
	
	/**
	 * Gets country name.
	 * $param string $code
	 * @return string
	 */
	public function getCountry($code) {
	  
	  	$country = $this->CountryInstance();
	  	return $country->getName($code);
	}
	
	protected function languageInstance() {
	  
	  	if (empty($this->language)) {
	  	  
	  	 	require_once("I18Nv2\\Language.php");
			$this->language = new I18Nv2_Language($this->locale);		    
		}
		
		return $this->language;
	}
	
	/**
	 * Gets array of languages names.
	 * @return array
	 */		
	public function getLanguages() {
	  
	  	return $this->LanguageInstance()->getAllCodes();	
	}
	
	/**
	 * Gets country name.
	 * $param string $code
	 * @return string
	 */
	public function getLanguage($code) {
	  	  	
	  	return $this->LanguageInstance()->getName($code);
	}
	
	protected function currencyInstance() {
	  
	  	if (empty($this->currency)) {
	  	  
	  	 	require_once("I18Nv2\\Currency.php");
			$this->currency = new I18Nv2_Currency($this->locale);		    
		}
		
		return $this->currency;
	}
	
	/**
	 * Gets array of currencies names.
	 * @return array
	 */
	public function getCurrencies() {
	  
	  	return $this->CurrencyInstance()->getAllCodes();	
	}
	
	/**
	 * Gets name of currency.
	 * @param string $code
	 * $return string
	 */	 
	public function getCurrency($code) {
	  
		return $this->CurrencyInstance()->getName($code);
	}
	
}

?>