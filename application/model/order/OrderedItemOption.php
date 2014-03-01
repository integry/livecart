<?php


/**
 * Represents a shopping basket item configuration value
 *
 * @package application/model/order
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


		$schema->registerField(new ARPrimaryForeignKeyField("orderedItemID", "OrderedItem", "ID", "OrderedItem;
		$schema->registerField(new ARPrimaryForeignKeyField("choiceID", "ProductOptionChoice", "ID", "ProductOptionChoice;

		public $priceDiff;
		public $optionText;

		$schema->registerCircularReference('Choice', 'ProductOptionChoice');
		$schema->registerCircularReference('DefaultChoice', 'ProductOptionChoice');
	}

	/*####################  Static method implementations ####################*/

	public static function getNewInstance(OrderedItem $item, ProductOptionChoice $choice)
	{
		$instance = new self();
		$instance->orderedItem = $item;
		$instance->choice = $choice;

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

		$f = new ARSelectFilter(new INCond('OrderedItemOption.orderedItemID', $ids));
		foreach (ActiveRecordModel::getRecordSet('OrderedItemOption', $f, array('DefaultChoice' => 'ProductOptionChoice', 'Option' => 'ProductOption', 'Choice' => 'ProductOptionChoice')) as $itemOption)
		{
			$itemOption->orderedItem->loadOption($itemOption);
		}
	}

	/*####################  Saving ####################*/

	public function save()
	{
		if (!$this->orderedItem->customerOrder->isFinalized)
		{
			$this->updatePriceDiff();
		}

		return parent::save();
	}

	public function beforeCreate()
	{
		$this->updatePriceDiff();


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
		if ($this->optionText && $this->choice->option->isFile())
		{
			unlink(self::getFilePath($this->optionText));
		}
	}

	public function updatePriceDiff()
	{
		$currency = $this->orderedItem->customerOrder->currencyID->getID();
		$this->priceDiff = $this->choice->getPriceDiff($currency));
	}

	public function setFile($fileArray)
	{
		// avoid breaking out of directory
		$fileArray['name'] = str_replace(array('/', '\\'), '', $fileArray['name']);

		$item = $this->orderedItem;
		$fileName = $item->customerOrder->getID() . '_' . rand(1, 10000) . time() . '___' . $fileArray['name'];
		$path = self::getFilePath($fileName);

		$dir = dirname($path);
		if (!file_exists($dir))
		{
			mkdir($dir, 0777);
			chmod($dir, 0777);
		}

		move_uploaded_file($fileArray['tmp_name'], $path);
		$this->optionText = $fileName;

		// create thumbnails for images
		if ($paths = self::getImagePaths($path))
		{
			$this->resizeImage($path, $paths['large_path'], '2');
			$this->resizeImage($paths['large_path'], $paths['small_path'], '1');
		}
	}

	public function getFile()
	{
		return ObjectFile::getNewInstance('ObjectFile', self::getFilePath($this->optionText), self::getFileName($this->optionText));
	}

	public function resizeImage($source, $target, $confSuffix)
	{
		$dir = dirname($target);
		if (!file_exists($dir))
		{
			mkdir($dir, 0777);
			chmod($dir, 0777);
		}

		$conf = $this->getConfig();
		$img = new ImageManipulator($source);
		$img->setQuality($conf->get('IMG_O_Q_' . $confSuffix));
		$img->resize($conf->get('IMG_O_W_' . $confSuffix), $conf->get('IMG_O_H_' . $confSuffix), $target);
	}

	protected static function getFilePath($file)
	{
		return $this->config->getPath('storage/customerUpload/') . $file;
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
			$res[$size . '_path'] = $this->config->getPath('public/upload/optionImage/') . $name;
		}

		return $res;
	}

	/*####################  Data array transformation ####################*/

	public static function transformArray($array, ARSchema $schema)
	{
		$array = parent::transformArray($array, $schema);

		$array['formattedPrice'] = '';

		if (!empty($array['OrderedItem']['customerOrderID']))
		{
			$order = CustomerOrder::getInstanceByID($array['OrderedItem']['customerOrderID']);
			$currency = $order->getCurrency();
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