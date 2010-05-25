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
		$updater = new ApiProductImport($this->application);
		$updater->allowOnlyUpdate();
		$profile = new CsvImportProfile('Product');
		$reader = $this->getDataImportIterator($updater, $profile);
		$updater->setCallback(array($this, 'productImportCallback'));
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

	public function productImportCallback($record)
	{
		//$this->importedIDs[] = array('id'=>$record->getID(), 'sku'=>$record->sku->get());
		
		$this->importedIDs[] = $record->sku->get();
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

	// UserImport does not use getInstance(), will append onlyCreate(), onlyUpdate() constraints to importInstance()
	public function importInstance($record, CsvImportProfile $profile)
	{
		if(array_key_exists('sku', $record))
		{
			$instance = Product::getInstanceBySKU($record['sku']);
			$id = $instance ? $instance->getID() : 0;
			
			if($this->allowOnly == self::CREATE && $id > 0) 
			{
				throw new Exception('Record exists');
			}
			if($this->allowOnly == self::UPDATE && $id == 0) 
			{
				throw new Exception('Record not found');
			}
		} else {
			// if identified by smth else what then?
		}
		
		return parent::importInstance($record, $profile);
	}

}
?>
