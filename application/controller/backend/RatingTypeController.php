<?php


/**
 * @package application/controller/backend
 * @author Integry Systems
 * @role ratingcategory
 */
class RatingTypeController extends StoreManagementController
{
	public function indexAction()
	{
		$category = Category::getInstanceByID($this->request->get('id'), Category::LOAD_DATA);
		$types = ProductRatingType::getCategoryRatingTypes($category)->toArray();
		$this->set('typeList', $types);
		$this->set('form', $this->buildForm());
		$this->set('id', $this->request->get('id'));
	}

	/**
	 * @role update
	 */
	public function editAction()
	{
		$form = $this->buildForm();
		$type = ProductRatingType::getInstanceByID($this->request->get('id'), ProductRatingType::LOAD_DATA);
		$form->loadData($type->toArray());
		$this->set('form', $form);
	}

	/**
	 * @role update
	 */
	public function saveAction()
	{
		$validator = $this->buildValidator();
		if (!$validator->isValid())
		{
			return new JSONResponse(array('err' => $validator->getErrorList()));
		}

		$post = $this->request->get('id') ? ProductRatingType::getInstanceByID($this->request->get('id'), ActiveRecordModel::LOAD_DATA) : ProductRatingType::getNewInstance(Category::getInstanceByID($this->request->get('categoryId'), Category::LOAD_DATA));
		$post->loadRequestData($this->request);
		$post->save();

		return new JSONResponse($post->toArray());
	}

	/**
	 * Create new record
	 * @role create
	 */
	public function addAction()
	{
		return $this->save();
	}

	/**
	 * Remove a news entry
	 *
	 * @role delete
	 * @return JSONResponse
	 */
	public function deleteAction()
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
	public function saveOrderAction()
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
		$validator->add('name', new Validator\PresenceOf(array('message' => $this->translate('_err_enter_name'))));

		return $validator;
	}
}

?>