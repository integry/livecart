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
	public function toArray()
	{
		$store = Store::getInstance();
		$defaultLangCode = $store->getDefaultLanguageCode();

		$data = parent::toArray();
		$transformedData = array();
		$schema = self::getSchemaInstance(get_class($this));
		foreach ($data as $name => $value)
		{
			if (is_array($value))
			{
				if ($schema->getField($name)->getDataType() instanceof ARArray)
				{
					foreach ($value as $langCode => $multilingualValue)
					{
						if ($langCode != $defaultLangCode)
						{
							$transformedData[$name . "_" . $langCode] = $multilingualValue;
						}
						else
						{
							$transformedData[$name] = $multilingualValue;
						}
					}
				}
			}
			else
			{
				$transformedData[$name] = $value;
			}
		}
		return $transformedData;
	}
}

?>