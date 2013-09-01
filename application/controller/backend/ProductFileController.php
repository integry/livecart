<?php


/**
 * Controller for handling product based actions performed by store administrators
 *
 * @package application/controller/backend
 * @author Integry Systems
 * @role product
 */
class ProductFileController extends StoreManagementController
{
	public function indexAction()
	{
		$product = Product::getInstanceByID((int)$this->request->get('id'));



		$languages = array();
		foreach($this->application->getLanguageList()->toArray() as $language) $languages[$language['ID']] = $language;
		$this->set('languages', $languages);
		$this->set('productID', $product->getID());
		$this->set('productFilesWithGroups', $product->getFilesMergedWithGroupsArray());

	}

	/**
	 * @role update
	 */
	public function updateAction()
	{
		$productFile = ProductFile::getInstanceByID((int)$this->request->get('ID'), ActiveRecord::LOAD_DATA);
		$productFile->fileName->set($this->request->get('fileName'));
		$productFile->filePath->set($this->request->get('filePath'));

		if ($productFile->filePath->get())
		{
			$productFile->extension->set(pathinfo($productFile->filePath->get(), PATHINFO_EXTENSION));
		}

		$uploadFile = $this->request->get('uploadFile');
		if($this->request->isValueSet('uploadFile'))
		{
			$productFile->storeFile($uploadFile['tmp_name'], $uploadFile['name']);
		}

		return $this->save($productFile);
	}

	/**
	 * @role update
	 */
	public function createAction()
	{
		$product = Product::getInstanceByID((int)$this->request->get('productID'));
		if ($uploadFile = $this->request->get('uploadFile'))
		{
			$tmpPath = $uploadFile['tmp_name'];
			$name = $uploadFile['name'];
		}
		else
		{
			$tmpPath = null;
			$name = basename($this->request->get('filePath'));
		}

		$productFile = ProductFile::getNewInstance($product, $tmpPath, $name, $this->request->get('filePath'));
		return $this->save($productFile);
	}

	private function save(ProductFile $productFile)
	{

		$response->setHeader("Cache-Control", "no-cache, must-revalidate");
		$response->setHeader("Expires", "Mon, 26 Jul 1997 05:00:00 GMT");

		$validator = $this->buildValidator((int)$this->request->get('ID'));
		if($validator->isValid())
		{
			foreach ($this->application->getLanguageArray(true) as $lang)
	   		{
	   			if ($this->request->isValueSet('title_' . $lang))
					$productFile->setValueByLang('title', $lang, $this->request->get('title_' . $lang));

	   			if ($this->request->isValueSet('description_' . $lang))
					$productFile->setValueByLang('description', $lang, $this->request->get('description_' . $lang));
	   		}

	   		// Use title as description if no description was provided
	   		$defaultLang = $this->application->getDefaultLanguageCode();
	   		if(!$this->request->isValueSet('description_' . $defaultLang) || $this->request->get('description_' . $defaultLang) == '')
	   		{
				$productFile->setValueByLang('description', $defaultLang, $this->request->get('title_' . $defaultLang));
	   		}

	   		$productFile->allowDownloadDays->set((int)$this->request->get('allowDownloadDays'));
	   		$productFile->allowDownloadCount->set((int)$this->request->get('allowDownloadCount'));
	   		$productFile->isEmbedded->set($this->request->get('isEmbedded') != false);
	   		$productFile->isPublic->set($this->request->get('isPublic') != false);

	   		$productFile->save();
			$this->set('status', 'success');
			$this->set('productFile', $productFile->toArray());
		}
		else
		{
			$this->set('status', 'failure');
			$this->set('errors', $validator->getErrorList());
		}

	}

	public function editAction()
	{
		$productFile = ProductFile::getInstanceByID((int)$this->request->get('id'), ActiveRecord::LOAD_DATA);

		return new JSONResponse($productFile->toArray());
	}

	/**
	 * @role update
	 */
	public function deleteAction()
	{
		ProductFile::getInstanceByID((int)$this->request->get('id'))->delete();

		return new JSONResponse(false, 'success');
	}

	/**
	 * @role download
	 */
	public function downloadAction()
	{
		$productFile = ProductFile::getInstanceByID((int)$this->request->get('id'), ActiveRecord::LOAD_DATA);

		return new ObjectFileResponse($productFile);
	}

	/**
	 * @role update
	 */
	public function sortAction()
	{
		$target = $this->request->get('target');
		preg_match('/_(\d+)$/', $target, $match); // Get group.

		foreach($this->request->get($this->request->get('target'), array()) as $position => $key)
		{
			if(empty($key)) continue;

			$file = ProductFile::getInstanceByID((int)$key);
			$file->position->set((int)$position);

			if(isset($match[1])) $file->productFileGroup->set(ProductFileGroup::getInstanceByID((int)$match[1]));
			else $file->productFileGroup->setNull();

			$file->save();
		}

		return new JSONResponse(false, 'success');
	}

	/**
	 * @return \Phalcon\Validation
	 */
	private function buildValidator($existingProductFile = true)
	{
		$validator = $this->getValidator("productFileValidator", $this->request);

		$validator->add('title_' . $this->application->getDefaultLanguageCode(), new Validator\PresenceOf(array('message' => $this->translate('_err_file_title_is_empty'))));
		$validator->add('allowDownloadDays', new IsNumericCheck($this->translate('_err_allow_download_days_should_be_a_number')));
		$validator->add('allowDownloadDays', new Validator\PresenceOf(array('message' => $this->translate('_err_allow_download_days_is_empty'))));
		if(!$existingProductFile && !$this->request->get('filePath')) $validator->add('uploadFile', new IsFileUploadedCheck($this->translate('_err_file_could_not_be_uploaded_to_the_server')));
		if($existingProductFile) $validator->add('fileName', new Validator\PresenceOf(array('message' => $this->translate('_err_fileName_should_not_be_empty'))));

		return $validator;
	}

}
?>