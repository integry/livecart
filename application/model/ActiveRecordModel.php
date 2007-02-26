<?php

ClassLoader::import("library.activerecord.ActiveRecord");
ClassLoader::import("application.model.*");
ClassLoader::import("application.model.locale.*");

ActiveRecord::$creolePath = ClassLoader::getRealPath("library.creole");

ClassLoader::import("storage.configuration.database");
ActiveRecord::setDSN("mysql://root@192.168.1.6/livecart_dev");
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
							$this->setFieldValue($name, (int)(strtolower($request->getValue($name)) == 'on'));
						break;
							
						default:
							$this->setFieldValue($name, $request->getValue($name));	
						break;	
					}
				}
			}
		}	
	}
}

?>