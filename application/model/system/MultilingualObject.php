<?php

ClassLoader::import("application.model.ActiveRecordModel");
ClassLoader::import("application.model.system.MultilingualObjectInterface");

/**
 * Multilingual data object
 *
 * @author Integry Systems
 * @package application.model.system
 */
abstract class MultilingualObject extends ActiveRecordModel implements MultilingualObjectInterface
{
	private static $multilingualFieldList = array();

	const NO_DEFAULT_VALUE = false;

	public function setValueByLang($fieldName, $langCode, $value)
	{
		$valueArray = $this->getFieldValue($fieldName);
		if (!is_array($valueArray)) {
			$valueArray = array();
		}
		$valueArray[$langCode] = $value;
		$this->setFieldValue($fieldName, $valueArray);
	}

	public function getValueByLang($fieldName, $langCode, $returnDefaultIfEmpty = true)
	{
		$valueArray = $this->getFieldValue($fieldName);
		return $valueArray[$langCode];
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
					$this->setValueByLang($fieldName, $langCode, $request->getValue($requestVarName));
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
	 * @param unknown_type $includeLangData
	 * @todo cleanup
	 * @return array
	 */
	public function toArray($recursive = false, $convertToUnderscope = true)
	{
		$store = Store::getInstance();
		$defaultLangCode = $store->getDefaultLanguageCode();
		$currentLangCode = $store->getLocaleCode();

		$data = array();
		foreach ($this->data as $fieldName => $valueContainer)
		{
			$field = $valueContainer->getField();
			$fieldValue = $valueContainer->get();
			if ($field instanceof ARForeignKey)
			{			
				if (is_object($fieldValue))
				{
				  	$data[$field->getForeignClassName()] = $fieldValue->toArray();					  				  
				}
				else
				{
					$data[$field->getForeignClassName()] = $fieldValue;	
				}
			}
			else
			{
				if ($convertToUnderscope && is_array($fieldValue) && $field->getDataType() instanceof ARArray)
				{
					foreach ($fieldValue as $langCode => $multilingualValue)
					{
						if ($langCode != $defaultLangCode)
						{
							$data[$fieldName . '_' . $langCode] = $multilingualValue;
						}
						else
						{
							$data[$fieldName] = $multilingualValue;
						}
					}

					// value in active language (default language value is used
					// if there's no value in active language)
					$data[$fieldName . '_lang'] = !empty($data[$fieldName . '_' . $currentLangCode]) ?
														$data[$fieldName . '_' . $currentLangCode] :
														!empty($data[$fieldName]) ? $data[$fieldName] : '';
				}
				else
				{
					$data[$fieldName] = $fieldValue;
				}
			}
		} 
		return $data;
	}
    
	/**
     * Set a whole language field at a time. You can allways skip some language, bat as long as it occurs in
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
		$currentLanguage = Store::getInstance()->getLocaleCode();
		$defaultLanguage = Store::getInstance()->getDefaultLanguageCode();
		
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
}

?>