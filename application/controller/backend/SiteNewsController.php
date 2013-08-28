<?php


/**
 * @package application.controller.backend
 * @author Integry Systems
 * @role news
 */
class SiteNewsController extends StoreManagementController
{
	public function index()
	{
		$f = new ARSelectFilter();
		$f->setOrder(new ARFieldHandle('NewsPost', 'position'), 'DESC');
		$response = new ActionResponse('newsList', ActiveRecordModel::getRecordSetArray('NewsPost', $f));
		$response->set('form', $this->buildForm());
		return $response;
	}

	/**
	 * @role update
	 */
	public function edit()
	{
		$form = $this->buildForm();
		$form->loadData(NewsPost::getInstanceById($this->request->gget('id'), NewsPost::LOAD_DATA)->toArray());
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

		$post = $this->request->gget('id') ? ActiveRecordModel::getInstanceById('NewsPost', $this->request->gget('id'), ActiveRecordModel::LOAD_DATA) : ActiveRecordModel::getNewInstance('NewsPost');
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
			ActiveRecordModel::deleteById('NewsPost', $this->request->gget('id'));
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
	public function saveOrder()
	{
	  	$order = array_reverse($this->request->gget('newsList'));

		foreach ($order as $key => $value)
		{
			$update = new ARUpdateFilter();
			$update->setCondition(new EqualsCond(new ARFieldHandle('NewsPost', 'ID'), $value));
			$update->addModifier('position', $key);
			ActiveRecord::updateRecordSet('NewsPost', $update);
		}

		$resp = new RawResponse();
	  	$resp->setContent($this->request->gget('draggedId'));
		return $resp;
	}

	/**
	 * @role status
	 * @return JSONResponse
	 */
	public function setEnabled()
	{
		$post = ActiveRecordModel::getInstanceById('NewsPost', $this->request->gget('id'), NewsPost::LOAD_DATA);
		$post->isEnabled->set($this->request->gget("status"));
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