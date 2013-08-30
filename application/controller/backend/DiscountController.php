<?php


/**
 *
 * @package application/controller/backend
 * @author Integry Systems
 * @role product
 */
class DiscountController extends ActiveGridController
{
	public function indexAction()
	{
		$response = $this->getGridResponse();

		$this->set('form', $this->buildForm());

		$this->set('conditionForm', $this->buildConditionForm());

		$this->set('comparisonTypes', array(
						DiscountCondition::COMPARE_GTEQ => $this->translate('_compare_gteq'),
						DiscountCondition::COMPARE_LTEQ => $this->translate('_compare_lteq'),
						DiscountCondition::COMPARE_EQ => $this->translate('_compare_eq'),
						DiscountCondition::COMPARE_NE => $this->translate('_compare_ne'),
						DiscountCondition::COMPARE_DIV => $this->translate('_compare_div'),
						DiscountCondition::COMPARE_NDIV => $this->translate('_compare_ndiv'),
						));

		$this->set('comparisonFields', array(
						'count' => $this->translate('_with_count'),
						'subTotal' => $this->translate('_with_subTotal'),
						));

		$conditions = $this->getClasses('application/model/businessrule/condition');
		unset($conditions['RuleConditionRoot']);
		$this->set('conditionTypes', $conditions);
		$this->set('actionTypes', $this->getClasses('application/model/businessrule/action'));

		$this->set('applyToChoices', array(
						DiscountAction::TYPE_ORDER_DISCOUNT => $this->translate('_apply_order'),
						DiscountAction::TYPE_ITEM_DISCOUNT => $this->translate('_apply_matched_items'),
						DiscountAction::TYPE_CUSTOM_DISCOUNT => $this->translate('_apply_custom_items'),
					  ));

		$this->set('currencyCode', $this->application->getDefaultCurrencyCode());

		$actionFields = $itemActions = array();
		foreach ($response->get('actionTypes') as $class => $name)
		{
			$actionFields[$class] = call_user_func(array($class, 'getFields'));
			$reflection = new ReflectionClass($class);
			$itemActions[$class] = $reflection->implementsInterface('RuleItemAction');
		}

		$this->set('actionFields', array_filter($actionFields));
		$this->set('itemActions', $itemActions);

		$ruleFields = array();
		foreach ($conditions as $class => $name)
		{
			$ruleFields[$class] = call_user_func(array($class, 'getFields'));
		}

		$this->set('ruleFields', array_filter($ruleFields));

	}

	private function getCurrencies()
	{
		$currencies = ActiveRecordModel::getApplication()->getCurrencyArray();
		return array_combine($currencies, $currencies);
	}

	private function getShippingMethods()
	{
				$methods = array();
		$f = select();
		$f->setOrder(f('DeliveryZone.ID'));
		$f->setOrder(f('ShippingService.position'));
		foreach (ActiveRecord::getRecordSetArray('ShippingService', $f, array('DeliveryZone')) as $service)
		{
			$methods[$service['ID']] = $service['name_lang'] . ' (' . $service['DeliveryZone']['name'] . ')';
		}

		return $methods;
	}

	private function getPaymentMethods()
	{
		$this->loadLanguageFile('backend/Settings');
		$this->application->loadLanguageFiles();

		$handlers = array();
		foreach (array_merge($this->application->getPaymentHandlerList(true), array($this->config->get('CC_HANDLER')), $this->application->getExpressPaymentHandlerList(true)) as $class)
		{
			$handlers[$class] = $this->translate($class);
		}

		foreach (OfflineTransactionHandler::getEnabledMethods() as $offline)
		{
			$handlers[$offline] = OfflineTransactionHandler::getMethodName($offline);
		}

		return $handlers;
	}

	public function addAction()
	{
		$this->set('form', $this->buildForm());
		$this->setEditResponse($response);
	}

	public function editAction()
	{
		$condition = ActiveRecordModel::getInstanceById('DiscountCondition', $this->request->get('id'), DiscountCondition::LOAD_DATA, DiscountCondition::LOAD_REFERENCES);
		$condition->loadAll();

		$this->set('condition', $condition->toArray());

		$records = array();
		$zones = ActiveRecordModel::getRecordSetArray('DeliveryZone', new ARSelectFilter());
		//$zones = array_merge(array(DeliveryZone::getDefaultZoneInstance()->toArray()), $zones);
		//$this->loadLanguageFile('backend/DeliveryZone');
		//$zones[0]['name'] = $this->translate('_default_zone');
		$records['DeliveryZone'] = $zones;
		$records['UserGroup'] = ActiveRecordModel::getRecordSetArray('UserGroup', new ARSelectFilter());

		$this->set('records', $records);

		$this->set('serializedValues', array(
						'RuleConditionPaymentMethodIs' => $this->getPaymentMethods(),
						'RuleConditionCurrencyIs' => $this->getCurrencies(),
						'RuleConditionShippingMethodIs' => $this->getShippingMethods(),
						));

		$form = $this->buildForm();
		$form->setData($condition->toArray());
		$this->set('form', $form);

		// actions
		$f = new ARSelectFilter();
		$f->setOrder(new ARFieldHandle('DiscountAction', 'position'));
		$actions = $condition->getRelatedRecordSet('DiscountAction', $f, array('DiscountCondition', 'DiscountCondition_ActionCondition'));
		foreach ($actions as $action)
		{
			if ($action->actionCondition->get())
			{
				$action->actionCondition->get()->load();
				$action->actionCondition->get()->loadAll();
			}
		}

		$this->set('actions', $actions->toArray());

		$this->setEditResponse($response);

	}

	private function setEditResponse(ActionResponse $response)
	{
		$this->set('couponLimitTypes', array(
						'' => $this->translate('_coupon_limit_none'),
						DiscountCondition::COUPON_LIMIT_ALL => $this->translate('_coupon_limit_all'),
						DiscountCondition::COUPON_LIMIT_USER => $this->translate('_coupon_limit_user'),
					  ));
	}

	public function saveAction()
	{
		$validator = $this->buildValidator();

		if ($validator->isValid())
		{
			$instance = ($id = $this->request->get('id')) ? ActiveRecordModel::getInstanceByID('DiscountCondition', $id, ActiveRecordModel::LOAD_REFERENCES) : DiscountCondition::getNewInstance();
			$instance->loadRequestData($this->request);
			$instance->conditionClass->setNull();
			$instance->save();

			return new JSONResponse(array('condition' => $instance->toFlatArray()), 'success', $this->translate('_rule_was_successfully_saved'));
		}
		else
		{
			return new JSONResponse(array('errors' => $validator->getErrorList()), 'failure');
		}
	}

	public function addConditionAction()
	{
		$parent = ActiveRecordModel::getInstanceByID('DiscountCondition', $this->request->get('id'), DiscountCondition::LOAD_DATA);
		$child = DiscountCondition::getNewInstance($parent);
		$child->isEnabled->set(true);
		$child->save();

		return new JSONResponse($child->toArray());
	}

	public function deleteConditionAction()
	{
		$condition = ActiveRecordModel::getInstanceByID('DiscountCondition', $this->request->get('id'), DiscountCondition::LOAD_DATA);
		$condition->delete();

		return new JSONResponse(true);
	}

	public function updateConditionFieldAction()
	{
		list($fieldName, $id) = explode('_', $this->request->get('field'));

		$field = 'comparisonValue' == $fieldName ? ($this->request->get('type') == 'RuleConditionOrderItemCount' ? 'count' : 'subTotal') : $fieldName;

		$condition = ActiveRecordModel::getInstanceByID('DiscountCondition', $id, DiscountCondition::LOAD_DATA);

		if ('isAllSubconditions' != $fieldName)
		{
			$condition->conditionClass->set($this->request->get('type'));
		}

		$condition->serializedCondition->setNull();

		if ($this->request->get('type') == 'RuleConditionContainsProduct' && 'comparisonValue' == $fieldName)
		{
			$field = $this->request->get('productField');
		}

		if (('count' == $field) || ('subTotal' == $field))
		{
			$condition->comparisonType->set($this->request->get('comparisonType'));
			$condition->count->set(null);
			$condition->subTotal->set(null);
		}

		$value = $this->request->get('value');

		if ('isAnyRecord' == $field)
		{
			$value = !$value;
		}

		$condition->$field->set($value);
		$condition->save();

		return new JSONResponse($fieldName);
	}

	public function addRecordAction()
	{
		$condition = ActiveRecordModel::getInstanceByID('DiscountCondition', $this->request->get('id'), DiscountCondition::LOAD_DATA);
		$condition->conditionClass->set($this->request->get('type'));
		$condition->serializedCondition->setNull();
		$condition->save();

		$object = DiscountConditionRecord::getOwnerInstance($this->request->get('class'), $this->request->get('recordID'));
		$record = DiscountConditionRecord::getNewInstance($condition, $object);
		$record->save();

		$this->deleteOtherTypeRecords($condition, $object);

		return new JSONResponse(array('className' => get_class($object), 'data' => $record->toArray()));
	}

	public function deleteRecordAction()
	{
		$record = ActiveRecordModel::getInstanceByID('DiscountConditionRecord', $this->request->get('id'), DiscountConditionRecord::LOAD_DATA);
		$record->delete();

		return new JSONResponse(true);
	}

	public function saveSelectRecordAction()
	{
		$condition = ActiveRecordModel::getInstanceByID('DiscountCondition', $this->request->get('id'), DiscountCondition::LOAD_DATA);
		$condition->serializedCondition->setNull();
		$condition->conditionClass->set($this->request->get('type'));
		$condition->save();

		// delete existing record
		$record = ActiveRecordModel::getInstanceByID($this->request->get('class'), $this->request->get('recordID'));
		foreach ($record->getRelatedRecordSet('DiscountConditionRecord', new ARSelectFilter(new EqualsCond(new ARFieldHandle('DiscountConditionRecord', 'conditionID'), $condition->getID()))) as $existing)
		{
			$existing->delete();
		}

		$this->deleteOtherTypeRecords($condition, $record);

		// create
		if ($this->request->get('state') == 'true')
		{
			$rec = DiscountConditionRecord::getNewInstance($condition, $record);
			$rec->save();
		}
	}

	public function saveSelectValueAction()
	{
		$condition = ActiveRecordModel::getInstanceByID('DiscountCondition', $this->request->get('id'), DiscountCondition::LOAD_DATA);
		$condition->conditionClass->set($this->request->get('type'));
		$condition->save();

		if ($condition->recordCount->get())
		{
			foreach ($condition->getRelatedRecordSet('DiscountConditionRecord') as $record)
			{
				$record->delete();
			}
		}

		$condition->count->setNull();
		$condition->subTotal->setNull();
		$condition->comparisonType->setNull();

		$condition->setType($this->request->get('type'));
		$value = $this->request->get('value');
		if ('true' == $this->request->get('state'))
		{
			$condition->addValue($value);
		}
		else
		{
			$condition->removeValue($value);
		}

		$condition->save();
	}

	public function setSerializedValueAction()
	{
		$parts = explode('_', $this->request->get('field'));
		$type = array_shift($parts);
		$key = array_shift($parts);
		$condition = ActiveRecordModel::getInstanceByID('DiscountCondition', $this->request->get('id'), DiscountCondition::LOAD_DATA);
		$condition->conditionClass->set($this->request->get('type'));
		$condition->setSerializedValue($type, $key, $this->request->get('value'));
		$condition->save();

		return new JSONResponse($this->request->get('field'));
	}

	private function deleteOtherTypeRecords(DiscountCondition $condition, ActiveRecordModel $record)
	{
		if (in_array(get_class($record), array('Manufacturer', 'Category', 'Product')))
		{
			foreach(array('categoryID', 'productID', 'manufacturerID') as $field)
			{
				$c = new IsNullCond(new ARFieldHandle('DiscountConditionRecord', $field));
				if (isset($cond))
				{
					$cond->addAND($c);
				}
				else
				{
					$cond = $c;
				}
			}
		}
		else
		{
			$class = get_class($record);
			$field = strtolower(substr($class, 0, 1)) . substr($class, 1) . 'ID';
			$cond = new IsNullCond(new ARFieldHandle('DiscountConditionRecord', $field));
		}

		foreach ($condition->getRelatedRecordSet('DiscountConditionRecord', new ARSelectFilter($cond)) as $oldType)
		{
			$oldType->delete();
		}
	}

	public function addActionAction()
	{
		$parent = ActiveRecordModel::getInstanceByID('DiscountCondition', $this->request->get('id'), DiscountCondition::LOAD_DATA);
		$child = DiscountAction::getNewInstance($parent);
		$child->isEnabled->set(true);
		$child->save();

		return new JSONResponse($child->toArray());
	}

	public function deleteActionAction()
	{
		$action = ActiveRecordModel::getInstanceByID('DiscountAction', $this->request->get('id'), DiscountAction::LOAD_DATA);
		$action->delete();

		return new JSONResponse(true);
	}

	public function updateActionFieldAction()
	{
		list($fieldName, $id) = explode('_', $this->request->get('field'));
		$value = $this->request->get('value');

		$action = ActiveRecordModel::getInstanceByID('DiscountAction', $id, DiscountAction::LOAD_DATA, array('DiscountCondition', 'DiscountCondition_ActionCondition'));

		if ('type' == $fieldName)
		{
			switch ($value)
			{
				case DiscountAction::TYPE_ORDER_DISCOUNT:
					$action->type->set(DiscountAction::TYPE_ORDER_DISCOUNT);
					$action->actionCondition->set(null);
					break;

				case DiscountAction::TYPE_ITEM_DISCOUNT:
					$action->type->set(DiscountAction::TYPE_ITEM_DISCOUNT);
					$action->actionCondition->set($action->condition->get());
					break;

				case DiscountAction::TYPE_CUSTOM_DISCOUNT:
					$newCondition = DiscountCondition::getNewInstance();
					$newCondition->isEnabled->set(true);
					$newCondition->isActionCondition->set(true);
					$newCondition->isAnyRecord->set(true);
					$newCondition->save();

					$action->actionCondition->set($newCondition);
					$action->type->set(DiscountAction::TYPE_ITEM_DISCOUNT);
					$action->save();

					return new JSONResponse(array('field' => $fieldName, 'condition' => $newCondition->toArray()));

					break;
			}
		}
		else
		{
			if ($this->request->get('isParam') != 'false')
			{
				$action->setParamValue($fieldName, $value);
				$fieldName .= '_' . $action->getID();
			}
			else
			{
				$action->$fieldName->set($value);
			}
		}

		$action->save();

		return new JSONResponse($fieldName);
	}

	public function sortActionsAction()
	{
	  	$order = $this->request->get('actionContainer_' . $this->request->get('conditionId'));
		foreach ($order as $key => $value)
		{
			$update = new ARUpdateFilter();
			$update->setCondition(new EqualsCond(new ARFieldHandle('DiscountAction', 'ID'), $value));
			$update->addModifier('position', $key);
			ActiveRecord::updateRecordSet('DiscountAction', $update);
		}

		$resp = new RawResponse();
	  	$resp->setContent($this->request->get('draggedId'));
		return $resp;
	}

	public function changeColumnsAction()
	{
		parent::changeColumns();
		return $this->getGridResponse();
	}

	private function getGridResponse()
	{

		$this->setGridResponse($response);
	}

	protected function getClassName()
	{
		return 'DiscountCondition';
	}

	protected function getCSVFileName()
	{
		return 'discounts.csv';
	}

	protected function getDefaultColumns()
	{
		return array('DiscountCondition.ID', 'DiscountCondition.isEnabled', 'DiscountCondition.name', 'DiscountCondition.couponCode', 'DiscountCondition.position');
	}

	public function getAvailableColumnsAction()
	{
		$availableColumns = parent::getAvailableColumns();
		$validColumns = array('DiscountCondition.name', 'DiscountCondition.isEnabled', 'DiscountCondition.couponCode', 'DiscountCondition.validFrom', 'DiscountCondition.validTo', 'DiscountCondition.position');

		return array_intersect_key($availableColumns, array_flip($validColumns));
	}

	protected function getSelectFilter()
	{
		// we don't need the root node or action conditions
		$f = new ARSelectFilter(new EqualsCond(new ARFieldHandle($this->getClassName(), 'parentNodeID'), 1));
		$f->mergeCondition(new NotEqualsCond(new ARFieldHandle($this->getClassName(), 'isActionCondition'), 1));
		return $f;
	}

	protected function setDefaultSortOrder(ARSelectFilter $filter)
	{
		$handle = new ARFieldHandle($this->getClassName(), 'position');

		if (!$filter->isSortedBy($handle))
		{
			$filter->setOrder($handle, 'ASC');
		}
	}

	private function buildValidator()
	{
		$validator = $this->getValidator("discountCondition", $this->request);
		$validator->addCheck("name", new IsNotEmptyCheck($this->translate("_rule_name_empty")));

		return $validator;
	}

	/**
	 * Builds a condition main details form instance
	 *
	 * @return Form
	 */
	private function buildForm()
	{
		return new Form($this->buildValidator());
	}

	private function buildConditionValidator()
	{
		return $this->getValidator("discountConditionRule", $this->request);
	}

	/**
	 * Builds a condition main details form instance
	 *
	 * @return Form
	 */
	private function buildConditionForm()
	{
		return new Form($this->buildConditionValidator());
	}

	private function getClasses($mountPath)
	{
		$classes = $order = array();
		foreach($this->application->getPluginClasses($mountPath) as $class)
		{
			include_once $this->application->getPluginClassPath($mountPath, $class);
			$classes[$class] = $this->translate($class);
			$order[$class] = call_user_func(array($class, 'getSortOrder'));
		}

		asort($order);
		$classes = array_merge($order, $classes);

		return $classes;
	}
}

?>