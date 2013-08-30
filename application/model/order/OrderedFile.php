<?php


/**
 *
 *
 * @package application/model/order
 * @author Integry Systems <http://integry.com>
 */
class OrderedFile extends ActiveRecordModel
{
	/**
	 * Define database schema used by this active record instance
	 *
	 * @param string $className Schema name
	 */



		public $ID;
		public $orderedItemID", "OrderedItem", "ID", "OrderedItem;
		public $productFileID", "ProductFile", "ID", "ProductFile;
		public $timesDownloaded;
		public $lastDownloadTime;
	}

	/*####################  Static method implementations ####################*/

	public static function getNewInstance(OrderedItem $item, ProductFile $file)
	{
		$instance = new self();
		$instance->orderedItem = $item;
		$instance->productFile = $file;
		return $instance;
	}

	public static function getInstance(OrderedItem $item, ProductFile $file)
	{
		$instance = $item->getRelatedRecordSet('OrderedFile', select(eq('OrderedFile.productFileID', $file->getID())))->shift();
		if (!$instance)
		{
			$instance = self::getNewInstance($item, $file);
		}

		return $instance;
	}

	public function registerDownload()
	{
		$this->timesDownloaded = $this->timesDownloaded->get() + 1);
		$this->save();
	}
}

?>