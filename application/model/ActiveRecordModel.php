<?php

ClassLoader::import("library.activerecord.ActiveRecord");
ClassLoader::import("application.model.*");
ClassLoader::import("application.model.locale.*");

ActiveRecord::$creolePath = ClassLoader::getRealPath("library.creole");

include ClassLoader::getRealPath("storage.configuration.database") . '.php';
ActiveRecord::setDSN($GLOBALS['dsn']);
ActiveRecord::getLogger()->setLogFileName(ClassLoader::getRealPath("cache") . DIRECTORY_SEPARATOR . "activerecord.log");

/**
 * Base class for all ActiveRecord based models of application (single entry point in
 * application specific model class hierarchy)
 *
 * @package application.model
 */
abstract class ActiveRecordModel extends ActiveRecord
{
	public function loadRequestData(Request $request)
	{
		$schema = ActiveRecordModel::getSchemaInstance(get_class($this));
		foreach ($schema->getFieldList() as $field)
		{
			if (!($field instanceof ARForeignKey || $field instanceof ARPrimaryKey))
			{
				$name = $field->getName();
				if ($request->isValueSet($name))
				{
					switch (get_class($field->getDataType()))
					{
						case 'ARArray':
							$this->setValueArrayByLang(array($name), Store::getInstance()->getDefaultLanguageCode(), Store::getInstance()->getLanguageArray(Store::INCLUDE_DEFAULT), $request);
						break;
								
						case 'ARBool':
							echo $name . "\n";
							$this->setFieldValue($name, in_array($request->getValue($name), array('on', 1)));
						break;
							
						default:
							$this->setFieldValue($name, $request->getValue($name));	
						break;	
					}
				}
				else if('ARBool' == get_class($field->getDataType()))
				{
					if($this->getField($name)) $this->setFieldValue($name, 0);
				}
			}
		}	
	}
}

?>