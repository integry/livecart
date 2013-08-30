<?php


/**
 * @package application/controller/backend
 * @author Integry Systems
 * @role news
 */
class SiteNewsController extends StoreManagementController
{
	public function indexAction()
	{
		$f = new ARSelectFilter();
		$f->setOrder(new ARFieldHandle('NewsPost', 'position'), 'DESC');
		$response = new ActionResponse('newsList', ActiveRecordModel::getRecordSetArray('NewsPost', $f));
		$this->set('form', $this->buildForm());
	}

	/**
	 * @role update
	 */
	public function editAction()
	{
		$form = $this->buildForm();
		$form->loadData(NewsPost::getInstanceById($this->request->get('id'), NewsPost::LOAD_DATA)->toArray());
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

		$post = $this->request->get('id') ? ActiveRecordModel::getInstanceById('NewsPost', $this->request->get('id'), ActiveRecordModel::LOAD_DATA) : new NewsPost;
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
			ActiveRecordModel::deleteById('NewsPost', $this->request->get('id'));
			return new JSONResponse(false, 'success');
		}
		catch (Exception $exc)
		{
			return new JSONResponse(false, 'failure', $this->translate('_could_not_remove_news'));
		}
	}

	/**
	 * Save news entry order
	 * @role sort
	 * @return RawResponse
	 */
	public function saveOrderAction()
	{
	  	$order = array_reverse($this->request->get('newsList'));

		foreach ($order as $key => $value)
		{
			$update = new ARUpdateFilter();
			$update->setCondition(new EqualsCond(new ARFieldHandle('NewsPost', 'ID'), $value));
			$update->addModifier('position', $key);
			ActiveRecord::updateRecordSet('NewsPost', $update);
		}

		$resp = new RawResponse();
	  	$resp->setContent($this->request->get('draggedId'));
		return $resp;
	}

	/**
	 * @role status
	 * @return JSONResponse
	 */
	public function setEnabledAction()
	{
		$post = ActiveRecordModel::getInstanceById('NewsPost', $this->request->get('id'), NewsPost::LOAD_DATA);
		$post->isEnabled->set($this->request->get("status"));
		$post->save();

		return new JSONResponse($post->toArray());
	}

	private function buildForm()
	{
		return new Form($this->buildValidator());
	}

	private function buildValidator()
	{
		$validator = $this->getValidator("newspost", $this->request);
		$validator->addCheck('text', new IsNotEmptyCheck($this->translate('_err_enter_text')));

		return $validator;
	}
}

?>