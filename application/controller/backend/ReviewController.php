<?php

ClassLoader::import('application.controller.backend.abstract.ActiveGridController');
ClassLoader::import('application.model.category.Category');
ClassLoader::import('application.model.product.Product');
ClassLoader::import('application.model.product.ProductReview');

/**
 *
 * @package application.controller.backend
 * @author Integry Systems
 * @role product
 */
class ReviewController extends ActiveGridController
{
	public function index()
	{
		$response = $this->getGridResponse();
		$response->set('id', ($this->isCategory() ? 'c' : '') . $this->getID());
		$response->set('container', $this->request->get('category') ? 'tabReviews' : 'tabProductReviews');
		return $response;
	}

	public function edit()
	{
		$manufacturer = ActiveRecordModel::getInstanceById('Manufacturer', $this->request->get('id'), Manufacturer::LOAD_DATA, Manufacturer::LOAD_REFERENCES);
		$manufacturer->getSpecification();

		$response = new ActionResponse('manufacturer', $manufacturer->toArray());
		$form = $this->buildForm($manufacturer);
		$form->setData($manufacturer->toArray());

		$manufacturer->getSpecification()->setFormResponse($response, $form);
		$response->set('form', $form);

		return $response;
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
		$this->loadLanguageFile('backend/Category');

		$response = new ActionResponse();
		$this->setGridResponse($response);
		return $response;
	}

	protected function getClassName()
	{
		return 'ProductReview';
	}

	protected function getCSVFileName()
	{
		return 'reviews.csv';
	}

	protected function getDefaultColumns()
	{
		return array('ProductReview.ID', 'ProductReview.title', 'Product.name', 'ProductReview.nickname', 'ProductReview.dateCreated', 'ProductReview.isEnabled');
	}

	protected function getDisplayedColumns()
	{
		return parent::getDisplayedColumns(null, array('Product.ID' => 'numeric'));
	}

	public function getAvailableColumns()
	{
		$availableColumns = parent::getAvailableColumns();

		unset($availableColumns['ProductReview.ratingSum']);
		unset($availableColumns['ProductReview.ratingCount']);
		unset($availableColumns['ProductReview.ip']);

		return $availableColumns;
	}

	protected function getCustomColumns()
	{
		if ($this->isCategory())
		{
			$availableColumns['Product.name'] = 'text';
			$availableColumns['Product.ID'] = 'numeric';

			return $availableColumns;
		}

		return array();
	}

	protected function setDefaultSortOrder(ARSelectFilter $filter)
	{
		$filter->setOrder(new ARFieldHandle($this->getClassName(), 'ID'), 'DESC');
	}

	protected function getSelectFilter()
	{
		$id = $this->getID();

		if ($this->isCategory())
		{
			$owner = Category::getInstanceByID($id, Category::LOAD_DATA);

			$cond = new EqualsOrMoreCond(new ARFieldHandle('Category', 'lft'), $owner->lft->get());
			$cond->addAND(new EqualsOrLessCond(new ARFieldHandle('Category', 'rgt'), $owner->rgt->get()));
		}
		else
		{
			$cond = new EqualsCond(new ARFieldHandle('ProductReview', 'productID'), $id);
		}

		return new ARSelectFilter($cond);
	}

	private function isCategory()
	{
		$id = array_pop(explode('_', $this->request->get('id')));
		return (substr($id, 0, 1) == 'c') || $this->request->get('category');
	}

	private function getID()
	{
		$id = array_pop(explode('_', $this->request->get('id')));

		if ($this->isCategory() && (substr($id, 0, 1) == 'c'))
		{
			$id = substr($id, 1);
		}

		return $id;
	}

	protected function getReferencedData()
	{
		return array('Product', 'Category');
	}

	private function buildValidator(Manufacturer $manufacturer)
	{
		ClassLoader::import("framework.request.validator.RequestValidator");

		$validator = new RequestValidator("manufacturer", $this->request);
		$validator->addCheck("name", new IsNotEmptyCheck($this->translate("_manufacturer_name_empty")));

		$manufacturer->getSpecification()->setValidation($validator);

		return $validator;
	}

	/**
	 * Builds a category form instance
	 *
	 * @return Form
	 */
	private function buildForm(Manufacturer $manufacturer)
	{
		ClassLoader::import("framework.request.validator.Form");

		return new Form($this->buildValidator($manufacturer));
	}
}

?>