<?php

ClassLoader::import('application.model.product.ProductOptionChoice');
ClassLoader::import('application.model.order.OrderedItem');
ClassLoader::import('library.image.ImageManipulator');

/**
 * Represents a shopping basket item configuration value
 *
 * @package application.model.order
 * @author Integry Systems <http://integry.com>
 */
class OrderedItemOption extends ActiveRecordModel
{
	/**
	 * Define database schema used by this active record instance
	 *
	 * @param string $className Schema name
	 */
	public static function defineSchema($className = __CLASS__)
	{
		$schema = self::getSchemaInstance($className);
		$schema->setName($className);

		$schema->registerField(new ARPrimaryForeignKeyField("orderedItemID", "OrderedItem", "ID", "OrderedItem", ARInteger::instance()));
		$schema->registerField(new ARPrimaryForeignKeyField("choiceID", "ProductOptionChoice", "ID", "ProductOptionChoice", ARInteger::instance()));

		$schema->registerField(new ARField("priceDiff", ARFloat::instance()));
		$schema->registerField(new ARField("optionText", ARText::instance()));

		$schema->registerCircularReference('Choice', 'ProductOptionChoice');
		$schema->registerCircularReference('DefaultChoice', 'ProductOptionChoice');
	}

	/*####################  Static method implementations ####################*/

	public static function getNewInstance(OrderedItem $item, ProductOptionChoice $choice)
	{
		$instance = parent::getNewInstance(__CLASS__);
		$instance->orderedItem->set($item);
		$instance->choice->set($choice);

		return $instance;
	}

	public static function loadOptionsForItemSet(ARSet $orderedItems)
	{
		// load applied product option choices
		$ids = array();
		foreach ($orderedItems as $key => $item)
		{
			$ids[] = $item->getID();
		}

		$f = new ARSelectFilter(new INCond(new ARFieldHandle('OrderedItemOption', 'orderedItemID'), $ids));
		foreach (ActiveRecordModel::getRecordSet('OrderedItemOption', $f, array('DefaultChoice' => 'ProductOptionChoice', 'Option' => 'ProductOption', 'Choice' => 'ProductOptionChoice')) as $itemOption)
		{
			$itemOption->orderedItem->get()->loadOption($itemOption);
		}
	}

	/*####################  Saving ####################*/

	public function save()
	{
		if (!$this->orderedItem->get()->customerOrder->get()->isFinalized->get())
		{
			$this->updatePriceDiff();
		}

		return parent::save();
	}

	protected function insert()
	{
		$this->updatePriceDiff();

		return parent::insert();
	}

	public function delete()
	{
		return self::deleteByID($this->getID());
	}

	public static function deleteByID($recordID)
	{
		$record = ActiveRecordModel::getInstanceByID(__CLASS__, $recordID, self::LOAD_DATA);
		parent::deleteByID(__CLASS__, $recordID);
		$record->deleteFile();
	}

	public function deleteFile()
	{
		if ($this->optionText->get() && $this->choice->get()->option->get()->isFile())
		{
			unlink(self::getFilePath($this->optionText->get()));
		}
	}

	private function updatePriceDiff()
	{
		$currency = $this->orderedItem->get()->customerOrder->get()->currencyID->get()->getID();
		$this->priceDiff->set($this->choice->get()->getPriceDiff($currency));
	}

	public function setFile($fileArray)
	{
		// avoid breaking out of directory
		$fileArray['name'] = str_replace(array('/', '\\'), '', $fileArray['name']);

		$item = $this->orderedItem->get();
		$fileName = $item->customerOrder->get()->getID() . '_' . rand(1, 10000) . time() . '___' . $fileArray['name'];
		$path = self::getFilePath($fileName);

		$dir = dirname($path);
		if (!file_exists($dir))
		{
			mkdir($dir, 0777);
			chmod($dir, 0777);
		}

		move_uploaded_file($fileArray['tmp_name'], $path);
		$this->optionText->set($fileName);

		// create thumbnails for images
		if ($paths = self::getImagePaths($path))
		{
			$this->resizeImage($path, $paths['large_path'], '2');
			$this->resizeImage($paths['large_path'], $paths['small_path'], '1');
		}
	}

	public function getFile()
	{
		return ObjectFile::getNewInstance('ObjectFile', self::getFilePath($this->optionText->get()), self::getFileName($this->optionText->get()));
	}

	protected function resizeImage($source, $target, $confSuffix)
	{
		$dir = dirname($target);
		if (!file_exists($dir))
		{
			mkdir($dir, 0777);
			chmod($dir, 0777);
		}

		$conf = self::getApplication()->getConfig();
		$img = new ImageManipulator($source);
		$img->setQuality($conf->get('IMG_O_Q_' . $confSuffix));
		$img->resize($conf->get('IMG_O_W_' . $confSuffix), $conf->get('IMG_O_H_' . $confSuffix), $target);
	}

	protected static function getFilePath($file)
	{
		return ClassLoader::getRealPath('storage.customerUpload.') . $file;
	}

	protected static function getFileName($storedFileName)
	{
		return array_pop(explode('___', $storedFileName));
	}

	protected static function getImagePaths($filePath)
	{
		$ext = strtolower(pathinfo($filePath, PATHINFO_EXTENSION));
		if (!in_array($ext, array('png', 'jpg', 'gif')))
		{
			return array();
		}

		$name = basename($filePath);
		$res = array('small' => 'small_' . $name,
					 'large' => 'large_' . $name
					);

		foreach ($res as $size => $name)
		{
			$res[$size . '_url'] = 'upload/optionImage/' . $name;
			$res[$size . '_path'] = ClassLoader::getRealPath('public.upload.optionImage.') . $name;
		}

		return $res;
	}

	/*####################  Data array transformation ####################*/

	public static function transformArray($array, ARSchema $schema)
	{
		$array = parent::transformArray($array, $schema);

		$array['formattedPrice'] = array();

		if (!empty($array['OrderedItem']['CustomerOrder']))
		{
			$currency = Currency::getInstanceByID($array['OrderedItem']['CustomerOrder']['Currency']['ID']);
			$array['formattedPrice'] = $currency->getFormattedPrice($array['priceDiff']);
		}

		// get uploaded file name
		if (!empty($array['optionText']) && strpos($array['optionText'], '___'))
		{
			$array['fileName'] = self::getFileName($array['optionText']);
			$array = array_merge($array, self::getImagePaths($array['optionText']));
		}

		return $array;
	}

}

?>