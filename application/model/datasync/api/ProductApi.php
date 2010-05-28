<?php

ClassLoader::import('application.model.datasync.ModelApi');
ClassLoader::import('application.model.product.Product');
ClassLoader::import('application.model.category.Category');
ClassLoader::import('application.helper.LiveCartSimpleXMLElement');

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
			array('childSettings') // fields to ignore in Product model
		);
	}

	// ------ 

	public function get()
	{
		$request = $this->application->getRequest();
		$products = ActiveRecordModel::getRecordSetArray('Product',
			select(eq(f('Product.sku'), $request->get('SKU'))), array('Category', 'Manufacturer', 'ProductImage')
		);
		if(count($products) == 0)
		{
			throw new Exception('Product not found');
		}
		// --
		$response = new LiveCartSimpleXMLElement('<response datetime="'.date('c').'"></response>');
		$responseProduct = $response->addChild('product');
		while($product = array_shift($products))
		{
			$this->fillResponseItem($responseProduct, $product);
		}
		return new SimpleXMLResponse($response);
	}

	public function filter()
	{
		$parser = $this->getParser();		
		$request = $this->getApplication()->getRequest();
		$name = $request->get('name');
		$request->set('name','%'.serialize($name).'%');
		$response = new LiveCartSimpleXMLElement('<response datetime="'.date('c').'"></response>');
		$products = Product::getRecordSetArray(
			'Product',
			$parser->getARSelectFilter(), 
			array('Category', 'Manufacturer', 'ProductImage')
		);
		// $fieldNames = $parser->getApiFieldNames();
		foreach($products as $product)
		{
			$this->fillResponseItem($response->addChild('product'), $product);
		}
		return new SimpleXMLResponse($response);
	}
	
	private function fillResponseItem($responseProduct, $product)
	{
		$parser = $this->getParser();
		$apiFieldNames = $parser->getApiFieldNames();
		foreach($product as $k => $v)
		{
			if(in_array($k, $apiFieldNames))
			{
				$responseProduct->addChild($k, (string)$v);
			}
		}
	
		// product image
		if(array_key_exists('ProductImage', $product))
		{
			foreach($product['ProductImage'] as $k => $v)
			{
				if($k == 'title' || (substr($k, 0, 6) == 'title_' && $v))
				{
					$responseProduct->addChild('ProductDefaultImage_'.$k, $v);
				}
			}
			if(array_key_exists('urls', $product['ProductImage']) && is_array($product['ProductImage']['urls']))
			{
				end($product['ProductImage']['urls']);
				$url = current($product['ProductImage']['urls']);
				reset($product['ProductImage']['urls']);
				$responseProduct->addChild('ProductDefaultImage_URL', $url);
			}
		}
			
		// manufacturer
		if(array_key_exists('Manufacturer',$product))
		{
			$responseProduct->addChild('Manufacturer_name', $product['Manufacturer']['name']);
		}

		// category
		if(array_key_exists('Category', $product))
		{
			$categoryFieldNames = array('name');
			foreach($product['Category'] as $k => $v)
			{
				if(is_string($v) && in_array($k,$categoryFieldNames))
				{
					$responseProduct->addChild('Category_'.$k, $v);
				}
			}
		}
		return $responseProduct;
	}

	public function delete()
	{
		$request = $this->getApplication()->getRequest();
		$instance = Product::getInstanceBySKU($request->get('SKU'));
		if(!$instance)
		{
			throw new Exception('Product not found');
		}		
		$id = $instance->sku->get(); // SKU of deleted record, not SKU of requested to delete record (if finding item by SKU fails or smth)
		$instance->delete();

		return $this->statusResponse($id, 'deleted');
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