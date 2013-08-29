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
		$product = Product::getInstanceByID((int)$this->request->gget('id'));

		$response = new ActionResponse();

		$languages = array();
		foreach($this->application->getLanguageList()->toArray() as $language) $languages[$language['ID']] = $language;
		$response->set('languages', $languages);
		$response->set('productID', $product->getID());
		$response->set('productFilesWithGroups', $product->getFilesMergedWithGroupsArray());

		return $response;
	}

	/**
	 * @role update
	 */
	public function updateAction()
	{
		$productFile = ProductFile::getInstanceByID((int)$this->request->gget('ID'), ActiveRecord::LOAD_DATA);
		$productFile->fileName->set($this->request->gget('fileName'));
		$productFile->filePath->set($this->request->gget('filePath'));

		if ($productFile->filePath->get())
		{
			$productFile->extension->set(pathinfo($productFile->filePath->get(), PATHINFO_EXTENSION));
		}

		$uploadFile = $this->request->gget('uploadFile');
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
		$product = Product::getInstanceByID((int)$this->request->gget('productID'));
		if ($uploadFile = $this->request->gget('uploadFile'))
		{
			$tmpPath = $uploadFile['tmp_name'];
			$name = $uploadFile['name'];
		}
		else
		{
			$tmpPath = null;
			$name = basename($this->request->gget('filePath'));
		}

		$productFile = ProductFile::getNewInstance($product, $tmpPath, $name, $this->request->gget('filePath'));
		return $this->save($productFile);
	}

	private function save(ProductFile $productFile)
	{
		$response = new ActionResponse();
		$response->setHeader("Cache-Control", "no-cache, must-revalidate");
		$response->setHeader("Expires", "Mon, 26 Jul 1997 05:00:00 GMT");

		$validator = $this->buildValidator((int)$this->request->gget('ID'));
		if($validator->isValid())
		{
			foreach ($this->application->getLanguageArray(true) as $lang)
	   		{
	   			if ($this->request->isValueSet('title_' . $lang))
					$productFile->setValueByLang('title', $lang, $this->request->gget('title_' . $lang));

	   			if ($this->request->isValueSet('description_' . $lang))
					$productFile->setValueByLang('description', $lang, $this->request->gget('description_' . $lang));
	   		}

	   		// Use title as description if no description was provided
	   		$defaultLang = $this->application->getDefaultLanguageCode();
	   		if(!$this->request->isValueSet('description_' . $defaultLang) || $this->request->gget('description_' . $defaultLang) == '')
	   		{
				$productFile->setValueByLang('description', $defaultLang, $this->request->gget('title_' . $defaultLang));
	   		}

	   		$productFile->allowDownloadDays->set((int)$this->request->gget('allowDownloadDays'));
	   		$productFile->allowDownloadCount->set((int)$this->request->gget('allowDownloadCount'));
	   		$productFile->isEmbedded->set($this->request->gget('isEmbedded') != false);
	   		$productFile->isPublic->set($this->request->gget('isPublic') != false);

	   		$productFile->save();
			$response->set('status', 'success');
			$response->set('productFile', $productFile->toArray());
		}
		else
		{
			$response->set('status', 'failure');
			$response->set('errors', $validator->getErrorList());
		}

		return $response;
	}

	public function editAction()
	{
		$productFile = ProductFile::getInstanceByID((int)$this->request->gget('id'), ActiveRecord::LOAD_DATA);

		return new JSONResponse($productFile->toArray());
	}

	/**
	 * @role update
	 */
	public function deleteAction()
	{
		ProductFile::getInstanceByID((int)$this->request->gget('id'))->delete();

		return new JSONResponse(false, 'success');
	}

	/**
	 * @role download
	 */
	public function downloadAction()
	{
		$productFile = ProductFile::getInstanceByID((int)$this->request->gget('id'), ActiveRecord::LOAD_DATA);

		return new ObjectFileResponse($productFile);
	}

	/**
	 * @role update
	 */
	public function sortAction()
	{
		$target = $this->request->gget('target');
		preg_match('/_(\d+)$/', $target, $match); // Get group.

		foreach($this->request->gget($this->request->gget('target'), array()) as $position => $key)
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
	 * @return RequestValidator
	 */
	private function buildValidator($existingProductFile = true)
	{
		$validator = $this->getValidator("productFileValidator", $this->request);

		$validator->addCheck('title_' . $this->application->getDefaultLanguageCode(), new IsNotEmptyCheck($this->translate('_err_file_title_is_empty')));
		$validator->addCheck('allowDownloadDays', new IsNumericCheck($this->translate('_err_allow_download_days_should_be_a_number')));
		$validator->addCheck('allowDownloadDays', new IsNotEmptyCheck($this->translate('_err_allow_download_days_is_empty')));
		if(!$existingProductFile && !$this->request->gget('filePath')) $validator->addCheck('uploadFile', new IsFileUploadedCheck($this->translate('_err_file_could_not_be_uploaded_to_the_server')));
		if($existingProductFile) $validator->addCheck('fileName', new IsNotEmptyCheck($this->translate('_err_fileName_should_not_be_empty')));

		return $validator;
	}

}
?>