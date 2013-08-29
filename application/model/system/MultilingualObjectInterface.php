<?php

/**
 * Interface for multilingual record manipulation
 *
 * @author Integry Systems <http://integry.com>  
 * @package application/model/system
 */
interface MultilingualObjectInterface
{
	/**
	 * Sets multilingual field value
	 *
	 * @param string $fieldName
	 * @param string $langCode
	 * @param mixed $value
	 */
	public function setValueByLang($fieldName, $langCode, $value);

	/**
	 * Gets value of multilingual field
	 *
	 * @param string $fieldName
	 * @param string $langCode
	 * @param mixed $returnDefaultIfEmpty
	 */
	public function getValueByLang($fieldName, $langCode = null, $returnDefaultIfEmpty = true);

	/**
	 * "Shorthand" mothod for setting field
	 *
	 * Example:
	 *
	 * Lets say there are 3 installed languages in system: en(default), lt and ru.
	 *
	 * Product table (defined by ActiveRecord subclass):
	 * ID, name (contains multilingual data), description (multilingual), price
	 *
	 * Creating new product in controller:
	 * $product = getInstanceFromSomewhere();
	 * $product->setValueArrayByLang(array("name", "description"), "en", array("en", "lt", "ru"), $this->request);
	 *
	 * As request may contain multilingual field translation data, this method checks
	 * all posible field and language combinations in request and makes an assigment
	 * to a record instance.
	 *
	 * So, this method checks in request for: name, description (for english data),
	 * name_lt, name_ru, description_lt, description_ru.
	 *
	 * @param array $fieldNameArray Array of multilingual field names
	 * @param array $langCodeArray Language code list
	 * @param Request $request
	 */
	public function setValueArrayByLang($fieldNameArray, $defaultLangCode, $langCodeArray, Request $request);
}

?>