<?php

ClassLoader::import("application.model.ActiveRecordModel");
ClassLoader::import("application.model.system.MultilingualObjectInterface");

/**
 * Multilingual data object
 *
 * @author Saulius Rupainis <saulius@integry.net>
 * @package application.model.system
 */
abstract class MultilingualObject extends ActiveRecordModel implements MultilingualObjectInterface
{
	private static $multilingualFieldList = array();

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
	 * Tranforms data array to a folowing format:
	 *
	 * simpleField => value,
	 * multilingualField_langCode => value,
	 * multilingualField2_langCode => otherValue, and etc.
	 *
	 * @param unknown_type $includeLangData
	 */
	public function toArray($convertToUnderscope=true)
	{
		$store = Store::getInstance();
		$defaultLangCode = $store->getDefaultLanguageCode();

		$data = array();
		foreach ($this->data as $fieldName => $valueContainer)
		{
			$field = $valueContainer->getField();
			$fieldValue = $valueContainer->get();
			if ($field instanceof ARForeignKey)
			{
				$data[$field->getForeignClassName()] = $valueContainer->get()->toArray();
			}
			else
			{
				if ($convertToUnderscope && is_array($fieldValue) && $field->getDataType() instanceof ARArray)
				{
					foreach ($fieldValue as $langCode => $multilingualValue)
					{
						if ($langCode != $defaultLangCode)
						{
							$data[$fieldName . "_" . $langCode] = $multilingualValue;
						}
						else
						{
							$data[$fieldName] = $multilingualValue;
						}
					}
				}
				else
				{
					$data[$fieldName] = $valueContainer->get();
				}
			}
		}
		return $data;
	}
}

?>