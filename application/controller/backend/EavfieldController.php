<?php

require_once(dirname(__FILE__) . '/abstract/ActiveGridController.php');

use eav\EavField;
use eav\EavValue;
use eav\EavFieldManager;
use Phalcon\Validation\Validator;

/**
 * Custom fields controller
 *
 * @package application/controller/backend
 * @author Integry Systems
 */
class EavfieldController extends ActiveGridController
{
	/**
	 * Configuration data
	 *
	 * @see self::getSpecFieldConfig
	 * @var array
	 */
	protected $specFieldConfig = array();

	protected function getClassName()
	{
		return 'eav\EavField';
	}
	
	protected function getDefaultColumns()
	{
		return array('eav\EavField.ID', 'eav\EavField.name');
	}
	
	/**
	 * Displays form for creating a new or editing existing one product group specification field
	 *
	 * @return JSONResponse
	 */
	public function getAction($id = null)
	{
		if ($id)
		{
			$field = EavField::getInstanceByID($id);
			$array = $field->toArray();
			$array['values'] = $field->getValues()->toArray();
		}
		else
		{
			$array = array('type' => '3', 'values' => array(array('value' => '')));
		}
		
		echo json_encode($array);
		
		/*
		$scope.vals = {type: "3", values: [{title: ''}]};
		$specFieldList = parent::item()->getValue();

		$specFieldList['categoryID'] = $specFieldList['classID'];

		$specFieldList = $this->getFieldInstanceByID($this->request->get('id'), true, true)->toArray(false, false);

		$valueClass = call_user_func(array(call_user_func(array($this->getFieldClass(), 'getSelectValueClass')), 'getValueClass'));
		$values = call_user_func_array(array($valueClass, 'getRecordSetArray'), array($specFieldList['ID']));
		foreach($values as $value)
		{
		   $specFieldList['values'][$value['ID']] = $value;
		}

		return new JSONResponse($specFieldList);
		*/
	}

	/**
	 * Delete specification field from database
	 *
	 * @return JSONResponse
	 */
	public function deleteAction()
	{
		$id = $this->request->get("id", null, false);
		if(ActiveRecordModel::objectExists($this->getFieldClass(), $id))
		{
			ActiveRecordModel::deleteById($this->getFieldClass(), $id);
			return new JSONResponse(false, 'success');
		}
		else
		{
			return new JSONResponse(false, 'failure', $this->translate('_could_not_remove_attribute'));
		}
	}

	public function updateAction()
	{
		if(ActiveRecordModel::objectExists($this->getFieldClass(), $this->request->get('ID')))
		{
			$specField = $this->getFieldInstanceByID($this->request->get('ID'));
		}
		else
		{
			return new JSONResponse(array(
					'errors' => array('ID' => $this->translate('_error_record_id_is_not_valid')),
					'ID' => (int)$this->request->get('ID')
				)/*,
				'failure',
				$this->translate('_could_not_save_attribute') */
			);
		}

		return $this->save($specField);
	}

	/**
	 * Sort specification fields
	 *
	 * @role update
	 * @return JSONResponse
	 */
	/*
	public function sortAction()
	{
		$target = $this->request->get('target');
		preg_match('/_(\d+)$/', $target, $match); // Get group.

		foreach($this->request->get($target, null, array()) as $position => $key)
		{
			if(!empty($key))
			{
				$specField = $this->getFieldInstanceByID($key);
				$specField->setFieldValue('position', $position);

				if(isset($match[1]))
				{
					$specField->getGroup()->set($this->getGroupInstanceByID($match[1])); // Change group
				}
				else
				{
					$specField->getGroup() = null;
				}

				$specField->save();
			}
		}

		return new JSONResponse(false, 'success');
	}
	*/

	/**
	 * Create and return configurational data. If configurational data is already created just return the array
	 *
	 * @see self::$specFieldConfig
	 * @return array
	 */
	protected function getSpecFieldConfig()
	{
		if(!empty($this->specFieldConfig)) return $this->specFieldConfig;

		$languages[$this->application->getDefaultLanguageCode()] =  $this->locale->info()->getOriginalLanguageName($this->application->getDefaultLanguageCode());
		foreach ($this->application->getLanguageSetArray() as $lang)
		{
			$languages[$lang['ID']] = $this->locale->info()->getOriginalLanguageName($lang['ID']);
		}

		$this->specFieldConfig = array(
			'languages' => $languages,
			'languageCodes' => array_keys($languages),
			'messages' => array
			(
				'deleteField' => $this->translate('_delete_field'),
				'removeFieldQuestion' => $this->translate('_remove_field_question')
			),

			'selectorValueTypes' => EavField::getSelectorValueTypes(),
			'doNotTranslateTheseValueTypes' => array(2),
			'countNewValues' => 0
		);

		return $this->specFieldConfig;
	}

	protected function getFieldInstanceByID($id, $loadData = false, $loadReferencedRecords = false)
	{
		return call_user_func_array(array($this->getFieldClass(), 'getInstanceByID'), array($id, $loadData, $loadReferencedRecords));
	}

	protected function getGroupInstanceByID($id, $loadData = false, $loadReferencedRecords = false)
	{
		return call_user_func_array(array($this->getFieldClass() . 'Group', 'getInstanceByID'), array($id, $loadData, $loadReferencedRecords));
	}

	/**
	 * Creates a new or modifies an exisitng specification field (according to a passed parameters)
	 *
	 * @return JSONResponse Returns success status or failure status with array of erros
	 */
	public function saveAction()
	{
		if ($id = $this->request->getJson('ID'))
		{
			$specField = EavField::getInstanceByID($id);
		}
		else
		{
			$specField = new EavField;
			$specField->classID = $this->request->getJson('eavType');
		}

		if (!is_numeric($this->request->getJson('eavType')))
		{
			$specField->stringIdentifier = $this->request->getJson('eavType');
		}
		
		$specField->loadRequestData($this->request);
		
		$type = $this->request->getJson('advancedText') ? EavField::TYPE_TEXT_ADVANCED : (int)$this->request->getJson('type');
		$dataType = EavField::getDataTypeFromType($type);

		$specField->dataType = $dataType;
		$specField->type = $type;

		$specField->save();
		
		$values = $this->request->getJson('values');
		if (is_array($values))
		{
			$existingValues = array();
			foreach ($specField->getValues() as $value)
			{
				$existingValues[$value->getID()] = $value;
			}
			
			foreach ($values as $key => &$value)
			{
				if (empty($value['value']))
				{
					continue;
				}

				if (empty($value['ID']))
				{
					$val = EavValue::getNewInstance($specField);
				}
				else
				{
					$val = isset($existingValues[$value['ID']]) ? $existingValues[$value['ID']] : EavValue::getNewInstance($specField);
					unset($existingValues[$value['ID']]);
				}
				
				$val->assign($value);
				$val->position = $key;
				$val->save();
			}
			
			// existing values not present in the posted data are deleted
			foreach ($existingValues as $deleted)
			{
				$deleted->delete();
			}
		}
		
		$arr = $specField->toArray();
		$arr['values'] = $specField->getValues()->toArray();
		
		echo json_encode($arr);
	}

	/**
	 * Validates specification field form
	 *
	 * @param array $values List of values to validate.
	 * @param array $config
	 * @return array List of all errors
	 */
	protected function validate($values = array(), $languageCodes)
	{
		$errors = array();

		if(!isset($values['name']) || $values['name'] == '')
		{
			$errors['name'] = '_error_name_empty';
		}

		if(!isset($values['handle']) || $values['handle'] == '' || preg_match('/[^\w\d_.]/', $values['handle']))
		{
			$errors['handle'] = '_error_handle_invalid';
		}
		else
		{
			$values['ID'] = !isset($values['ID']) ? -1 : $values['ID'];
			$filter = new ARSelectFilter();
				$handleCond = new EqualsCond(new ARFieldHandle($this->getFieldClass(), 'handle'), $values['handle']);
				$handleCond->andWhere(new EqualsCond(new ARFieldHandle($this->getFieldClass(), call_user_func(array($this->getFieldClass(), 'getOwnerIDColumnName'))), $values['categoryID']));
				$handleCond->andWhere(new NotEqualsCond(new ARFieldHandle($this->getFieldClass(), 'ID'), $values['ID']));
			$filter->setCondition($handleCond);
			if(count(ActiveRecordModel::getRecordSetArray($this->getFieldClass(), $filter)) > 0)
			{
				$errors['handle'] =  '_error_handle_exists';
			}
		}

		if(!isset($values['handle']) || $values['handle'] == '')
		{
			$errors['handle'] = '_error_handle_empty';
		}

		if(in_array($values['type'], EavField::getSelectorValueTypes()) && isset($values['values']) && is_array($values['values']))
		{
			$countValues = count($values['values']);
			$i = 0;
			foreach ($values['values'] as $key => $v)
			{
				$i++;
				if($countValues == $i && preg_match('/new/', $key) && empty($v[$languageCodes[0]]))
				{
					continue;
				}
				else if(!strlen($v[$languageCodes[0]]))
				{
					$errors["values[$key][{$languageCodes[0]}]"] = '_error_value_empty';
				}
				else if(EavField::getDataTypeFromType($values['type']) == 2 && !is_numeric($v[$languageCodes[0]]))
				{
					$errors["values[$key][{$languageCodes[0]}]"] = '_error_value_is_not_a_number';
				}
			}
		}

		return $errors;
	}
	
	protected function getParent($id)
	{
		return new EavFieldManager($id);
	}

	protected function getFieldClass()
	{
		return 'EavField';
	}

	public function indexAction()
	{
		$nodes = array();
		foreach (EavField::getEavClasses() as $class => $id)
		{
			$nodes[] = array('id' => $id, 'title' => $this->translate($class));
		}
		
		$nodes = array('children' => $nodes);
		/*
		// get offline payment methods
		$offlineMethods = array();
		foreach (OfflineTransactionHandler::getEnabledMethods() as $method)
		{
			$id = substr($method, -1);
			$offlineMethods[] = array('ID' => $method, 'name' => $this->config->get('OFFLINE_NAME_' . $id));
		}

		if ($this->config->get('CC_ENABLE'))
		{
			$offlineMethods[] = array('ID' => 'creditcard', 'name' => $this->config->get('CC_HANDLER'));
		}

		if ($offlineMethods)
		{
			$nodes[] = array('ID' => 'offline methods', 'name' => $this->translate('_offline_methods'), 'sub' => $offlineMethods);
		}
		*/

		$this->set('nodes', $nodes);
	}
	
	public function listAction()
	{
	}

	public function addAction()
	{
		$this->setValidator($this->getEavFieldValidator());
	}

	public function editAction()
	{
		$this->setValidator($this->getEavFieldValidator());
	}
	
	protected function getSelectFilter()
	{
		$f = parent::getSelectFilter();
		$f->andWhere('classID = :classid:', array('classid' => $this->dispatcher->getParam(0)));
		return $f;
	}

	public function aindexAction()
	{
		$categoryID = $this->request->get('id');

		$defaultSpecFieldValues = array
		(
			'ID' => $categoryID.'_new',
			'name' => array(),
			'description' => array(),
			'handle' => '',
			'values' => array(),
			'rootId' => 'specField_item_new_'.$categoryID.'_form',
			'type' => EavField::TYPE_TEXT_SIMPLE,
			'dataType' => EavField::DATATYPE_TEXT,
			'categoryID' => $categoryID,
			'isDisplayed' => true,
		);


		$this->set('categoryID', $categoryID);
		$this->set('configuration', $this->getSpecFieldConfig());
		$this->set('specFieldsList', $defaultSpecFieldValues);
		$this->set('specFieldsWithGroups', $this->getParent($categoryID)->getSpecFieldsWithGroupsArray());

		$fields = $response->get('specFieldsWithGroups');
		foreach ($fields as &$field)
		{
			if (isset($field['EavFieldGroup']))
			{
				$field['SpecFieldGroup'] = $field['EavFieldGroup'];
				$field['SpecFieldGroup']['Category']['ID'] = $response->get('categoryID');
			}
		}
		$this->set('specFieldsWithGroups', $fields);
	}
	
	protected function getEavFieldValidator()
	{
		$validator = $this->getValidator('eavField');
		$validator->add('name', new Validator\PresenceOf(array('message' => $this->application->translate('_error_name_empty'))));
		$validator->add('handle', new Validator\PresenceOf(array('message' => $this->application->translate('_error_handle_empty'))));
		return $validator;
	}
}

?>
