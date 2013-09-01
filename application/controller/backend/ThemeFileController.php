<?php


/**
 * Manage design themes
 *
 * @package application/controller/backend
 * @author Integry Systems
 */
class ThemeFileController extends StoreManagementController
{
	public function indexAction()
	{
		$request = $this->getRequest();
		$theme = $request->get('id');
		$tfh = ThemeFile::getNewInstance($theme);

		$this->set('form',$this->buildForm());
		$this->set('filesList',$tfh->getFiles());
		$this->set('theme',$theme);
		$this->set('maxSize', ini_get('upload_max_filesize'));
	}

	public function uploadAction()
	{
		$request = $this->getRequest();
		$theme = $request->get('theme');
		$tfh = ThemeFile::getNewInstance($request->get('theme'));
		$res = $tfh->processFileUpload('file', $request->get('filename'), $request->get('orginalFileName'));
		$this->setLayout('iframeJs');

		$this->set('theme', $theme);
		$this->set('result', json_encode($tfh->getFiles()));
		if(is_array($res))
		{
			$this->set('highlightFileName', $res['filename']);
		}
	}

	public function deleteAction()
	{
		$request = $this->getRequest();
		$tfh = ThemeFile::getNewInstance($request->get('theme'));
		$tfh->removeFile($request->get('file'));
	}

	/**
	 * Builds an image upload form validator
	 *
	 * @return \Phalcon\Validation
	 */
	protected function buildValidator()
	{
		$validator = $this->getValidator('themeFileValidator', $this->request);
		/*
		$uploadCheck = new IsFileUploadedCheck($this->translate(!empty($_FILES['image']['name']) ? '_err_too_large' :'_err_not_uploaded'));
		$uploadCheck->setFieldName('image');

		$validator->add('image', $uploadCheck);
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