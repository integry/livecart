<?php

ClassLoader::import("application.controller.backend.abstract.ActiveGridController");
ClassLoader::import("application.model.discount.DiscountCondition");
ClassLoader::import("application.model.discount.DiscountConditionRecord");
ClassLoader::import("application.model.discount.DiscountAction");
ClassLoader::import("application.model.product.Product");
ClassLoader::import("application.model.product.Manufacturer");
ClassLoader::import("application.model.category.Category");
ClassLoader::import("application.model.user.User");
ClassLoader::import("application.model.user.UserGroup");
ClassLoader::import("application.model.delivery.DeliveryZone");
ClassLoader::import('application.model.order.OfflineTransactionHandler');

/**
 *
 * @package application.controller.backend
 * @author Integry Systems
 * @role product
 */
class DiscountController extends ActiveGridController
{
	const TYPE_TOTAL = 0;
	const TYPE_COUNT = 1;
	const TYPE_ITEMS = 2;
	const TYPE_USERGROUP = 3;
	const TYPE_USER = 4;
	const TYPE_DELIVERYZONE = 5;
	const TYPE_PAYMENTMETHOD = 101;

	public function index()
	{
		$response = $this->getGridResponse();

		$response->set('form', $this->buildForm());

		$response->set('conditionForm', $this->buildConditionForm());

		$response->set('conditionTypes', array( self::TYPE_TOTAL => $this->translate('_type_order_total'),
						self::TYPE_COUNT => $this->translate('_type_order_count'),
						self::TYPE_ITEMS => $this->translate('_type_items_in_cart'),
						self::TYPE_USERGROUP => $this->translate('_type_user_group'),
						self::TYPE_USER => $this->translate('_type_user'),
						self::TYPE_DELIVERYZONE => $this->translate('_type_delivery_zone'),
						self::TYPE_PAYMENTMETHOD => $this->translate('_type_payment_method'),
						));

		$response->set('comparisonTypes', array(
						DiscountCondition::COMPARE_GTEQ => $this->translate('_compare_gteq'),
						DiscountCondition::COMPARE_LTEQ => $this->translate('_compare_lteq'),
						DiscountCondition::COMPARE_EQ => $this->translate('_compare_eq'),
						DiscountCondition::COMPARE_NE => $this->translate('_compare_ne'),
						DiscountCondition::COMPARE_DIV => $this->translate('_compare_div'),
						DiscountCondition::COMPARE_NDIV => $this->translate('_compare_ndiv'),
						));

		$response->set('comparisonFields', array(
						'count' => $this->translate('_with_count'),
						'subTotal' => $this->translate('_with_subTotal'),
						));

		$response->set('actionTypes', array(
						DiscountAction::ACTION_PERCENT => $this->translate('_percentage_discount'),
						DiscountAction::ACTION_AMOUNT => $this->translate('_fixed_amount_discount'),
						DiscountAction::ACTION_SURCHARGE_PERCENT => $this->translate('_percentage_surcharge'),
						DiscountAction::ACTION_SURCHARGE_AMOUNT => $this->translate('_fixed_amount_surcharge'),
						DiscountAction::ACTION_DISABLE_CHECKOUT => $this->translate('_type_disable_checkout'),
						DiscountAction::ACTION_SUM_VARIATIONS => $this->translate('_type_sum_variations'),
					  ));

		$response->set('applyToChoices', array(
						DiscountAction::TYPE_ORDER_DISCOUNT => $this->translate('_apply_order'),
						DiscountAction::TYPE_ITEM_DISCOUNT => $this->translate('_apply_matched_items'),
						DiscountAction::TYPE_CUSTOM_DISCOUNT => $this->translate('_apply_custom_items'),
					  ));

		$response->set('currencyCode', $this->application->getDefaultCurrencyCode());

		return $response;
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

	public function add()
	{
		$response = new ActionResponse('form', $this->buildForm());
		$this->setEditResponse($response);
		return $response;
	}

	public function edit()
	{
		$condition = ActiveRecordModel::getInstanceById('DiscountCondition', $this->request->get('id'), DiscountCondition::LOAD_DATA, DiscountCondition::LOAD_REFERENCES);
		$condition->loadAll();

		$response = new ActionResponse('condition', $condition->toArray());

		$records = array();
		$zones = ActiveRecordModel::getRecordSetArray('DeliveryZone', new ARSelectFilter());
		//$zones = array_merge(array(DeliveryZone::getDefaultZoneInstance()->toArray()), $zones);
		//$this->loadLanguageFile('backend/DeliveryZone');
		//$zones[0]['name'] = $this->translate('_default_zone');
		$records['DeliveryZone'] = $zones;
		$records['UserGroup'] = ActiveRecordModel::getRecordSetArray('UserGroup', new ARSelectFilter());

		$response->set('records', $records);

		$response->set('serializedValues', array(
						self::TYPE_PAYMENTMETHOD => $this->getPaymentMethods(),
						));

		$form = $this->buildForm();
		$form->setData($condition->toArray());
		$response->set('form', $form);

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

		$response->set('actions', $actions->toArray());

		$this->setEditResponse($response);

		return $response;
	}

	private function setEditResponse(ActionResponse $response)
	{
		$response->set('couponLimitTypes', array(
						'' => $this->translate('_coupon_limit_none'),
						DiscountCondition::COUPON_LIMIT_ALL => $this->translate('_coupon_limit_all'),
						DiscountCondition::COUPON_LIMIT_USER => $this->translate('_coupon_limit_user'),
					  ));
	}

	public function save()
	{
		$validator = $this->buildValidator();

		if ($validator->isValid())
		{
			$instance = ($id = $this->request->get('id')) ? ActiveRecordModel::getInstanceByID('DiscountCondition', $id, ActiveRecordModel::LOAD_REFERENCES) : DiscountCondition::getNewInstance();
			$instance->loadRequestData($this->request);
			$instance->save();

			return new JSONResponse(array('condition' => $instance->toFlatArray()), 'success', $this->translate('_rule_was_successfully_saved'));
		}
		else
		{
			return new JSONResponse(array('errors' => $validator->getErrorList()), 'failure');
		}
	}

	public function addCondition()
	{
		$parent = ActiveRecordModel::getInstanceByID('DiscountCondition', $this->request->get('id'), DiscountCondition::LOAD_DATA);
		$child = DiscountCondition::getNewInstance($parent);
		$child->isEnabled->set(true);
		$child->save();

		return new JSONResponse($child->toArray());
	}

	public function deleteCondition()
	{
		$condition = ActiveRecordModel::getInstanceByID('DiscountCondition', $this->request->get('id'), DiscountCondition::LOAD_DATA);
		$condition->delete();

		return new JSONResponse(true);
	}

	public function updateConditionField()
	{
		list($fieldName, $id) = explode('_', $this->request->get('field'));

		$field = 'comparisonValue' == $fieldName ? ($this->request->get('type') == self::TYPE_COUNT ? 'count' : 'subTotal') : $fieldName;

		$condition = ActiveRecordModel::getInstanceByID('DiscountCondition', $id, DiscountCondition::LOAD_DATA);
		$condition->serializedCondition->setNull();

		if ($this->request->get('type') == self::TYPE_ITEMS && 'comparisonValue' == $fieldName)
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

	public function addRecord()
	{
		$condition = ActiveRecordModel::getInstanceByID('DiscountCondition', $this->request->get('id'), DiscountCondition::LOAD_DATA);
		$condition->serializedCondition->setNull();

		$object = DiscountConditionRecord::getOwnerInstance($this->request->get('class'), $this->request->get('recordID'));
		$record = DiscountConditionRecord::getNewInstance($condition, $object);
		$record->save();

		$this->deleteOtherTypeRecords($condition, $object);

		return new JSONResponse(array('className' => get_class($object), 'data' => $record->toArray()));
	}

	public function deleteRecord()
	{
		$record = ActiveRecordModel::getInstanceByID('DiscountConditionRecord', $this->request->get('id'), DiscountConditionRecord::LOAD_DATA);
		$record->delete();

		return new JSONResponse(true);
	}

	public function saveSelectRecord()
	{
		$condition = ActiveRecordModel::getInstanceByID('DiscountCondition', $this->request->get('id'), DiscountCondition::LOAD_DATA);
		$condition->serializedCondition->setNull();

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

	public function saveSelectValue()
	{
		$condition = ActiveRecordModel::getInstanceByID('DiscountCondition', $this->request->get('id'), DiscountCondition::LOAD_DATA);

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

	public function addAction()
	{
		$parent = ActiveRecordModel::getInstanceByID('DiscountCondition', $this->request->get('id'), DiscountCondition::LOAD_DATA);
		$child = DiscountAction::getNewInstance($parent);
		$child->isEnabled->set(true);
		$child->save();

		return new JSONResponse($child->toArray());
	}

	public function deleteAction()
	{
		$action = ActiveRecordModel::getInstanceByID('DiscountAction', $this->request->get('id'), DiscountAction::LOAD_DATA);
		$action->delete();

		return new JSONResponse(true);
	}

	public function updateActionField()
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
			$action->$fieldName->set($value);
		}

		$action->save();

		return new JSONResponse($fieldName);
	}

	public function sortActions()
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

	public function changeColumns()
	{
		parent::changeColumns();
		return $this->getGridResponse();
	}

	private function getGridResponse()
	{
		$response = new ActionResponse();
		$this->setGridResponse($response);
		return $response;
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

	public function getAvailableColumns()
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
		$filter->setOrder(new ARFieldHandle($this->getClassName(), 'position'), 'ASC');
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

}

?>