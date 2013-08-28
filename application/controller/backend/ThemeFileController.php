<?php


/**
 * Manage design themes
 *
 * @package application.controller.backend
 * @author Integry Systems
 */
class ThemeFileController extends StoreManagementController
{
	public function indexAction()
	{
		$request = $this->getRequest();
		$theme = $request->gget('id');
		$tfh = ThemeFile::getNewInstance($theme);
		$response = new ActionResponse();
		$response->set('form',$this->buildForm());
		$response->set('filesList',$tfh->getFiles());
		$response->set('theme',$theme);
		$response->set('maxSize', ini_get('upload_max_filesize'));
		return $response;
	}

	public function uploadAction()
	{
		$request = $this->getRequest();
		$theme = $request->gget('theme');
		$tfh = ThemeFile::getNewInstance($request->gget('theme'));
		$res = $tfh->processFileUpload('file', $request->gget('filename'), $request->gget('orginalFileName'));
		$this->setLayout('iframeJs');
		$response = new ActionResponse();
		$response->set('theme', $theme);
		$response->set('result', json_encode($tfh->getFiles()));
		if(is_array($res))
		{
			$response->set('highlightFileName', $res['filename']);
		}
		return $response;
	}

	public function deleteAction()
	{
		$request = $this->getRequest();
		$tfh = ThemeFile::getNewInstance($request->gget('theme'));
		$tfh->removeFile($request->gget('file'));
	}

	/**
	 * Builds an image upload form validator
	 *
	 * @return RequestValidator
	 */
	protected function buildValidator()
	{
		$validator = $this->getValidator('themeFileValidator', $this->request);
		/*
		$uploadCheck = new IsFileUploadedCheck($this->translate(!empty($_FILES['image']['name']) ? '_err_too_large' :'_err_not_uploaded'));
		$uploadCheck->setFieldName('image');

		$validator->addCheck('image', $uploadCheck);
		*/

		return $validator;
	}

	/**
	 * Builds a category image form instance
	 *
	 * @return Form
	 */
	protected function buildForm()
	{
		return new Form($this->buildValidator());
	}
}

?>