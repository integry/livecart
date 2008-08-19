<?php

ClassLoader::import('application.controller.backend.abstract.eav.EavFieldValueControllerCommon');
ClassLoader::import('application.model.eav.EavValue');

/**
 * Category specification field value controller
 *
 * @package application.controller.backend
 * @author	Integry Systems
 * @role category
 */
class EavFieldValueController extends EavFieldValueControllerCommon
{
	protected function getClassName()
	{
		return 'EavValue';
	}

	/**
	 * Delete specification field value from database
	 *
	 * @role update
	 * @return JSONResponse Indicates status
	 */
	public function delete()
	{
		return parent::delete();
	}

	/**
	 * Sort specification field values
	 *
	 * @role update
	 * return JSONResponse Indicates status
	 */
	public function sort()
	{
		return parent::sort();
	}

	/**
	 * @role update
	 */
	public function mergeValues()
	{
		return parent::mergeValues();
	}

	public function autoComplete()
	{
	  	$f = new ARSelectFilter();
		$f->setLimit(20);

		$resp = array();

		$field = $this->request->get('field');

		if ('specField_' == substr($field, 0, 10))
		{
			list($foo, $id) = explode('_', $field);

			$handle = new ARFieldHandle('EavStringValue', 'value');
			$locale = $this->locale->getLocaleCode();
			$searchHandle = MultiLingualObject::getLangSearchHandle($handle, $locale);

		  	$f->setCondition(new EqualsCond(new ARFieldHandle('EavStringValue', 'fieldID'), $id));
			$f->mergeCondition(new LikeCond($handle, '%:"' . $this->request->get($field) . '%'));
			$f->mergeCondition(new LikeCond($searchHandle, $this->request->get($field) . '%'));

		  	$f->setOrder($searchHandle, 'ASC');

		  	$results = ActiveRecordModel::getRecordSet('EavStringValue', $f);

		  	foreach ($results as $value)
		  	{
				$resp[$value->getValueByLang('value', $locale, MultiLingualObject::NO_DEFAULT_VALUE)] = true;
			}

			$resp = array_keys($resp);
		}

		return new AutoCompleteResponse($resp);
	}
}