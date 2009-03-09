<?php

ClassLoader::import('application.controller.backend.abstract.StoreManagementController');
ClassLoader::import('application.model.category.Category');
ClassLoader::import('application.model.category.ProductRatingType');

/**
 * @package application.controller.backend
 * @author Integry Systems
 * @role ratingcategory
 */
class RatingTypeController extends StoreManagementController
{
	public function index()
	{
		$category = Category::getInstanceByID($this->request->get('id'), Category::LOAD_DATA);
		$types = ProductRatingType::getCategoryRatingTypes($category)->toArray();
		$response = new ActionResponse('typeList', $types);
		$response->set('form', $this->buildForm());
		$response->set('id', $this->request->get('id'));
		return $response;
	}

	/**
	 * @role update
	 */
	public function edit()
	{
		$form = $this->buildForm();
		$type = ActiveRecordModel::getInstanceByID('ProductRatingType', $this->request->get('id'), ProductRatingType::LOAD_DATA);
		$form->loadData($type->toArray());
		return new ActionResponse('form', $form);
	}

	/**
	 * @role update
	 */
	public function save()
	{
		$validator = $this->buildValidator();
		if (!$validator->isValid())
		{
			return new JSONResponse(array('err' => $validator->getErrorList()));
		}

		$post = $this->request->get('id') ? ActiveRecordModel::getInstanceById('ProductRatingType', $this->request->get('id'), ActiveRecordModel::LOAD_DATA) : ProductRatingType::getNewInstance(Category::getInstanceByID($this->request->get('categoryId'), Category::LOAD_DATA));
		$post->loadRequestData($this->request);
		$post->save();

		return new JSONResponse($post->toArray());
	}

	/**
	 * Create new record
	 * @role create
	 */
	public function add()
	{
		return $this->save();
	}

	/**
	 * Remove a news entry
	 *
	 * @role delete
	 * @return JSONResponse
	 */
	public function delete()
	{
		try
	  	{
			ActiveRecordModel::deleteById('ProductRatingType', $this->request->get('id'));
			return new JSONResponse(false, 'success');
		}
		catch (Exception $exc)
		{
			return new JSONResponse(false, 'failure', $this->translate('_could_not_remove_rating_type'));
		}
	}

	/**
	 * Save news entry order
	 * @role sort
	 * @return RawResponse
	 */
	public function saveOrder()
	{
		$order = array_reverse($this->request->get('typeList_' . $this->request->get('id')));

		foreach ($order as $key => $value)
		{
			$update = new ARUpdateFilter();
			$update->setCondition(new EqualsCond(new ARFieldHandle('ProductRatingType', 'ID'), $value));
			$update->addModifier('position', $key);
			ActiveRecord::updateRecordSet('ProductRatingType', $update);
		}

		return new RawResponse($this->request->get('draggedId'));
	}

	private function buildForm()
	{
		return new Form($this->buildValidator());
	}

	private function buildValidator()
	{
		$validator = $this->getValidator("ProductRatingType", $this->request);
		$validator->addCheck('name', new IsNotEmptyCheck($this->translate('_err_enter_name')));

		return $validator;
	}
}

?>