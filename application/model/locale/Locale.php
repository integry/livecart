<?php

ClassLoader::import("library.*");

/**
 * Class for locale support. Gets defined translating values. Also gets languages, countries, currencies and of locale.
 * Depends on Language, InterfaceTranslation, I18Nv2.
 *
 * <code>
 *  // using of Locale class
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
  	
  	private $locale;  	
  	
  	private $definitions = array();
    
	private $country;
	
	private $language;
	
	private $currency;
	  
	/**
	 * Constructs locale.
	 * @param string $locale E.g. "en", "lt", "ru"
	 */
	public function __construct($locale) {
		
		$this->locale = $locale;		
		
		//gets translation definitions
		try {

			$data = ActiveRecord::getInstanceById("InterfaceTranslation", array("ID" => $this->locale), true, true);		
		} catch (Exception $ex) {
			
			$this->definitions = array();
			return;
		}
				
		if (empty($data) || !$data->interfaceData->get()) {
		  
			$this->definitions = array();  
		  	return;
		}	
	//	echo (string)$data->interfaceData->get();
		$this->definitions = unserialize((string)$data->interfaceData->get());					
	
		//print_r($this->definitions);
		foreach ($this->definitions as $key => $value) {
		  
		  	$this->definitions[$key][Locale::value] = stripslashes($value[Locale::value]);
		}		
	}
	
	/**
	 * Gets locale. Flyweight pattern is used, to get load data just once.
	 * @param $locale	
	 */
	public static function getInstance($locale) {
	  			
	  	if(empty(self::$instanceMap[$locale])) {
	  	
		  	$instance = new Locale($locale);  	  	
			self::$instanceMap[$locale] = $instance;
			
			if (empty($locale)) {
			  
				self::$instanceMap[$locale] = $instance;  	
			}
		}
				
		return self::$instanceMap[$locale];	  	
	}
	
	private static $currentLocale;
		
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
	 * Traslates text to current locale.
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
	public function &getFullDefinitionsArray() {
	  
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
	
	
	private $mylocale_make;
	
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