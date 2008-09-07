<?php

ClassLoader::import("application.controller.backend.abstract.ActiveGridController");
ClassLoader::import("application.model.discount.DiscountCondition");

/**
 *
 * @package application.controller.backend
 * @author Integry Systems
 * @role product
 */
class DiscountController extends ActiveGridController
{
	public function index()
	{
		return $this->getGridResponse();
	}

	public function add()
	{
		return new ActionResponse('form', $this->buildForm());
	}

	public function edit()
	{
		$condition = ActiveRecordModel::getInstanceById('DiscountCondition', $this->request->get('id'), DiscountCondition::LOAD_DATA, DiscountCondition::LOAD_REFERENCES);

		$response = new ActionResponse('condition', $condition->toArray());
		$form = $this->buildForm();
		$form->setData($condition->toArray());
		$response->set('form', $form);

		return $response;
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

	public function update()
	{
		$manufacturer = ActiveRecordModel::getInstanceById('Manufacturer', $this->request->get('id'), Manufacturer::LOAD_DATA, Manufacturer::LOAD_REFERENCES);
		$validator = $this->buildValidator($manufacturer);

		if ($validator->isValid())
		{
			$manufacturer->loadRequestData($this->request);
			$manufacturer->save();
			return new JSONResponse(array('manufacturer' => $manufacturer->toFlatArray()), 'success', $this->translate('_manufacturer_was_successfully_saved'));
		}
		else
		{
			return new JSONResponse(array('errors' => $validator->getErrorList()), 'failure', $this->translate('_could_not_save_manufacturer'));
		}
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
		// we don't need the root node
		return new ARSelectFilter(new NotEqualsCond(new ARFieldHandle($this->getClassName(), 'ID'), 1));
	}

	protected function setDefaultSortOrder(ARSelectFilter $filter)
	{
		$filter->setOrder(new ARFieldHandle($this->getClassName(), 'position'), 'ASC');
	}

	private function buildValidator()
	{
		ClassLoader::import("framework.request.validator.RequestValidator");

		$validator = new RequestValidator("discountCondition", $this->request);
		$validator->addCheck("name", new IsNotEmptyCheck($this->translate("_rule_name_empty")));

		return $validator;
	}

	/**
	 * Builds a category form instance
	 *
	 * @return Form
	 */
	private function buildForm()
	{
		ClassLoader::import("framework.request.validator.Form");

		return new Form($this->buildValidator());
	}
}

?>