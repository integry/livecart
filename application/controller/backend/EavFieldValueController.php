<?php


/**
 * Category specification field value controller
 *
 * @package application/controller/backend
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
	public function deleteAction()
	{
		return parent::delete();
	}

	/**
	 * Sort specification field values
	 *
	 * @role update
	 * return JSONResponse Indicates status
	 */
	public function sortAction()
	{
		return parent::sort();
	}

	/**
	 * @role update
	 */
	public function mergeValuesAction()
	{
		return parent::mergeValues();
	}

	public function autoCompleteAction()
	{
	  	$f = new ARSelectFilter();
		$f->limit(20);

		$resp = array();

		$field = $this->request->get('field');

		if ('specField_' == substr($field, 0, 10))
		{
			list($foo, $id) = explode('_', $field);

			$handle = new ARFieldHandle('EavStringValue', 'value');
			$locale = $this->locale->getLocaleCode();
			$searchHandle = MultilingualObject::getLangSearchHandle($handle, $locale);

		  	$f->setCondition(new EqualsCond(new ARFieldHandle('EavStringValue', 'fieldID'), $id));
			$f->mergeCondition(new LikeCond($handle, '%:"' . $this->request->get($field) . '%'));
			$f->mergeCondition(new LikeCond($searchHandle, $this->request->get($field) . '%'));

		  	$f->order($searchHandle, 'ASC');

		  	$results = ActiveRecordModel::getRecordSet('EavStringValue', $f);

		  	foreach ($results as $value)
		  	{
				$resp[$value->getValueByLang('value', $locale, MultilingualObject::NO_DEFAULT_VALUE)] = true;
			}

			$resp = array_keys($resp);
		}

		return new AutoCompleteResponse($resp);
	}
}