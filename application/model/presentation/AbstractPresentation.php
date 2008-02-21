<?php

ClassLoader::import('application.model.ActiveRecordModel');

/**
 * Store entity presentation configuration (products, categories)
 *
 * @package application.model.product
 * @author Integry Systems <http://integry.com>
 */
abstract class AbstractPresentation extends ActiveRecordModel
{
	public abstract function getReferencedClass();

	public static function defineSchema($className = __CLASS__)
	{
		$schema = self::getSchemaInstance($className);
		$schema->setName($className);

		$schema->registerField(new ARPrimaryForeignKeyField("ID", call_user_func(array($className, 'getReferencedClass')), 'ID', null, ARInteger::instance()));
		$schema->registerField(new ARField("theme", ARVarchar::instance(20)));

		return $schema;
	}

	public static function getInstance(ActiveRecordModel $parent)
	{
		$parentClass = get_class($parent);
		$selfClass = $parentClass . 'Presentation';
		$set = $parent->getRelatedRecordSet($selfClass, new ARSelectFilter(), array($parentClass));
		if ($set->size())
		{
			return $set->get(0);
		}
		else
		{
			return call_user_func_array(array($selfClass, 'getNewInstance'), array($parent, array($parentClass)));
		}
	}

	public function getTheme()
	{
		return $this->theme->get();
	}

	public function save()
	{
		$operation = $this->data['ID']->isModified() ? self::PERFORM_INSERT : self::PERFORM_UPDATE;

		return parent::save($operation);
	}
}

?>