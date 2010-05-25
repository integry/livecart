<?php

ClassLoader::import('application.model.datasync.ModelApi');
ClassLoader::import('application.model.product.Product');
ClassLoader::import('application.model.category.Category');

/**
 * Web service access layer for Product model
 *
 * @package application.model.datasync
 * @author Integry Systems <http://integry.com>
 * 
 */
class ProductApi extends ModelApi
{
	public static function canParse(Request $request)
	{
		return parent::canParse($request, array('XmlProductApiReader'));
	}

	public function __construct(LiveCart $application)
	{
		parent::__construct(
			$application,
			'Product',
			array_keys(Product::getNewInstance(Category::getRootNode())->getSchema()->getFieldList())
		);
	}

	// ------ 

	public function get()
	{
		$request = $this->application->getRequest();

		$parser = $this->getParser();
		$parser->loadDataInRequest($request);
		$products = ActiveRecordModel::getRecordSetArray('Product',
			select(eq(f('Product.sku'), $request->get('SKU')))
		);
		if(count($products) == 0)
		{
			throw new Exception('Product not found');
		}
		$apiFieldNames = $parser->getApiFieldNames();
		// --
		$response = new SimpleXMLElement('<response datetime="'.date('c').'"></response>');
		$responseProduct = $response->addChild('product');
		while($product = array_shift($products))
		{
			foreach($product as $k => $v)
			{
				if(in_array($k, $apiFieldNames))
				{
					$responseProduct->addChild($k, $v);
				}
			}
		}
		return new SimpleXMLResponse($response);
	}
	
	public function create()
	{
		$updater = new ApiProductImport($this->application);
		$updater->allowOnlyCreate();
		$profile = new CsvImportProfile('Product');
		$reader = $this->getDataImportIterator($updater, $profile);
		$updater->setCallback(array($this, 'productImportCallback'));
		$updater->importFile($reader, $profile);

		return $this->statusResponse($this->importedIDs, 'created');
	}
	
	public function update()
	{
		die('---');
		$updater = new ApiUserImport($this->application);
		$updater->allowOnlyUpdate();
		$profile = new CsvImportProfile('User');
		$reader = $this->getDataImportIterator($updater, $profile);
		$updater->setCallback(array($this, 'userImportCallback'));
		$updater->importFile($reader, $profile);

		return $this->statusResponse($this->importedIDs, 'updated');
	}
	
	
	private function getDataImportIterator($updater, $profile)
	{
		// parser can act as DataImport::importFile() iterator
		$parser = $this->getParser();
		$parser->populate($updater, $profile);
		return $parser;
	}

	public function productImportCallback($record, $updated)
	{
		$this->importedIDs[] = $record->getID();
	}
}



ClassLoader::import('application.model.datasync.import.ProductImport');
ClassLoader::import('application/model.datasync.CsvImportProfile');
// misc things
// @todo: in seperate file!
class ApiProductImport extends ProductImport
{
	const CREATE = 1;
	const UPDATE = 2;
	
	private $allowOnly = null;

	public function allowOnlyUpdate()
	{
		$this->allowOnly = self::UPDATE;
	}

	public function getClassName()  // because dataImport::getClassName() will return ApiUser, not User.
	{
		return 'Product';
	}

	public function allowOnlyCreate()
	{
		$this->allowOnly = self::CREATE;
	}

	public // one (bad) implementation of delete() action calls this method, therefore public
	function getInstance($record, CsvImportProfile $profile)
	{
		$instance = parent::getInstance($record, $profile);
		$id = $instance->getID();
		if($this->allowOnly == self::CREATE && $id > 0) 
		{
			throw new Exception('Record exists');
		}
		if($this->allowOnly == self::UPDATE && $id == 0) 
		{
			throw new Exception('Record not found');
		}
		return $instance;
	}
}
?>
