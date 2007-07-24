<?php

ClassLoader::import("application.model.ActiveRecordModel");
ClassLoader::import("application.model.system.MultilingualObjectInterface");

/**
 * Multilingual data object. Provides facilities to provide field data in various languages
 * as well as data retrieval for the particular language.
 *
 * @author Integry Systems <http://integry.com>  
 * @package application.model.system
 */
abstract class MultilingualObject extends ActiveRecordModel implements MultilingualObjectInterface
{
    private static $defaultLanguageCode = null;
    
    private static $currentLanguageCode = null;
    
	const NO_DEFAULT_VALUE = false;

	public function setValueByLang($fieldName, $langCode, $value)
	{
		if (is_null($langCode))
		{
			if (!self::$defaultLanguageCode)
			{
                self::loadLanguageCodes();
            }
            $langCode = self::$defaultLanguageCode;
		}
	
		$valueArray = $this->getFieldValue($fieldName);
		if (!is_array($valueArray)) 
		{
			$valueArray = array();
		}
		$valueArray[$langCode] = $value;
		
		$this->setFieldValue($fieldName, $valueArray);
	}

	public function getValueByLang($fieldName, $langCode, $returnDefaultIfEmpty = true)
	{
		$valueArray = $this->getFieldValue($fieldName);

		if (!isset($valueArray[$langCode]) && $returnDefaultIfEmpty)
		{
			if (!self::$defaultLanguageCode)
			{
                self::loadLanguageCodes();
            }
            $langCode = self::$defaultLanguageCode;
		}
		
		if (isset($valueArray[$langCode]))
		{
            return $valueArray[$langCode];    
        }
	}

	public function setValueArrayByLang($fieldNameArray, $defaultLangCode, $langCodeArray, Request $request)
	{
		foreach ($fieldNameArray as $fieldName)
		{
			foreach ($langCodeArray as $langCode)
			{
				if ($langCode == $defaultLangCode)
				{
					$requestVarName = $fieldName;
				}
				else
				{
					$requestVarName = $fieldName . "_" . $langCode;
				}
				if ($request->isValueSet($requestVarName))
				{
					$this->setValueByLang($fieldName, $langCode, $request->get($requestVarName));
				}
			}
		}
	}

	/**
	 * Tranforms object data to data array in the following format:
	 *
	 * simpleField => value,
	 * multilingualField_langCode => value,
	 * multilingualField2_langCode => otherValue, and etc.
	 *
	 * @param array $array
	 * @todo cleanup
	 * @return array
	 */
    public static function transformArray($array, $className)
    {		
		$array = parent::transformArray($array, $className);

        foreach (self::getSchemaInstance($className)->getArrayFieldList() as $fieldName => $field)
		{
			if (!empty($array[$fieldName]))
			{
				$data = $array[$fieldName];
				
                if (!is_array($data)) 
				{ 
				    continue;
                }					
				
    			if (!self::$defaultLanguageCode)
    			{
                    self::loadLanguageCodes();
                }

				foreach ($data as $lang => $value)
				{
				  	$array[$fieldName . '_' . $lang] = $value;					  	
				}

                $array[$fieldName] = !empty($data[self::$defaultLanguageCode]) ? $data[self::$defaultLanguageCode] : '';

                $array[$fieldName . '_lang'] = !empty($data[self::$currentLanguageCode]) ? $data[self::$currentLanguageCode] : $array[$fieldName];
			}	    
		}
		
		return $array;  	
	}
    
	/**
     * Set a whole language field at a time. You can allways skip some language, but as long as it occurs in
     * languages array it will be writen into the database as empty string. I spent 2 hours writing this feature =]
     *
     * @example $specField->setLanguageField('name', array('en' => 'Name', 'lt' => 'Vardas', 'de' => 'Name'), array('lt', 'en', 'de'))
     *
     * @param string $fieldName Field name in database schema
     * @param array $fieldValue Field value in different languages
     * @param array $langCodeArray Language codes
     */
	public function setLanguageField($fieldName, $fieldValue, $langCodeArray)
	{
	    foreach ($langCodeArray as $lang)
	    {
	        $this->setValueByLang($fieldName, $lang, isset($fieldValue[$lang]) ? $fieldValue[$lang] : '');
	    }
	}
	
	/**
	 *	Creates an ARExpressionHandle for ordering a record set by field value in particular language
	 *
	 *	Basically what the SQL expression does, it parses serialized PHP array and returns the value
	 *	for the particular language. If there's no value entered for the current language, default language
	 * 	value is returned.
	 *
	 *	@return ARExpressionHandle
	 */
	public static function getLangOrderHandle(ARFieldHandle $field)
	{
		$currentLanguage = self::getApplication()->getLocaleCode();
		$defaultLanguage = self::getApplication()->getDefaultLanguageCode();
		
		if ($currentLanguage == $defaultLanguage)
		{
			$expression = "	  	
			SUBSTRING_INDEX(
				SUBSTRING_INDEX(
					SUBSTRING(
						" . $field->toString() . ",
						LOCATE('\"" . $defaultLanguage . "\";s:', " . $field->toString() . ") + 7
					)
				,'\";',1)
			,':\"',-1)		  
			";			  
		}
		else
		{
			$expression = "	  	
			SUBSTRING_INDEX(
				SUBSTRING_INDEX(
					SUBSTRING(
						" . $field->toString() . ",
						IFNULL(
							NULLIF(
								LOCATE('\"" . $currentLanguage . "\";s:', " . $field->toString() . "), LOCATE('\"" . $currentLanguage . "\";s:0:', " . $field->toString() . ")
							)
							,
							LOCATE('\"" . $defaultLanguage . "\";s:', " . $field->toString() . ")
						) + 7
					)
				,'\";',1)
			,':\"',-1)		  
			";		  
		}
		  
	  	return new ARExpressionHandle($expression);	  	
	}
	
	/**
   	 *	Creates an ARExpressionHandle for performing searches over language fields (finding a value in particular language)
	 *
	 *	@return ARExpressionHandle  	 
   	 */	
	public static function getLangSearchHandle(ARFieldHandle $field, $language)
	{
		$expression = "
			SUBSTRING(
				SUBSTRING_INDEX(" . $field->toString() . ",'\"" . $language . "\";s:',-1), 
				LOG10(
					SUBSTRING_INDEX(
						SUBSTRING_INDEX(" . $field->toString() . ",'\"" . $language . "\";s:',-1), 
						':',
						1) + 1
					) + 4,
				SUBSTRING_INDEX(
					SUBSTRING_INDEX(" . $field->toString() . ",'\"" . $language . "\";s:',-1), 
					':',
					1)
				)";
	 
	  	return new ARExpressionHandle($expression);	
	}
	
	private static function loadLanguageCodes()
	{
		$app = self::getApplication();

        self::$currentLanguageCode = $app->getLocaleCode();
        self::$defaultLanguageCode = $app->getDefaultLanguageCode();
    }
}

?>