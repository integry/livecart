<?php

ClassLoader::import('application.model.ObjectImage');

/**
 * Manufacturer image (logo). One manufacturer can have multiple images.
 *
 * @package application.model.manufacturer
 * @author Integry Systems <http://integry.com>
 */
class ManufacturerImage extends ObjectImage
{
	public static $imageSizes;

	public static function defineSchema($className = __CLASS__)
	{
		$schema = parent::defineSchema($className);
		$schema->registerField(new ARForeignKeyField("manufacturerID", "Manufacturer", "ID", "Manufacturer", ARInteger::instance()));
	}

	/*####################  Static method implementations ####################*/

	public static function getNewInstance(Manufacturer $manufacturer)
	{
	  	$image = ActiveRecord::getNewInstance(__CLASS__);
	  	$image->manufacturer->set($manufacturer);
	  	return $image;
	}

	/*####################  Value retrieval and manipulation ####################*/

	public function getPath($size = 0)
	{
		if (!$this->isLoaded)
		{
			$this->load(array('Manufacturer'));
		}

		return self::getImagePath($this->getID(), $this->manufacturer->get()->getID(), $size);
	}

	public static function getImageSizes()
	{
		if (!self::$imageSizes)
		{
			$config = self::getApplication()->getConfig();

			$sizes = array();
			$k = 0;
			while ($config->isValueSet('IMG_M_W_' . ++$k))
			{
				$sizes[$k] = array($config->get('IMG_M_W_' . $k), $config->get('IMG_M_H_' . $k));
			}

			self::$imageSizes = $sizes;
		}

		return self::$imageSizes;
	}

	protected static function getImagePath($imageID, $manufacturerID, $size)
	{
		return 'upload/manufacturerimage/' . $manufacturerID. '-' . $imageID . '-' . $size . '.jpg';
	}

	/*####################  Saving ####################*/

	public static function deleteByID($id)
	{
		parent::deleteByID(__CLASS__, $id, 'manufacturerID');
	}

	protected function insert()
	{
		return parent::insert('manufacturerID');
	}

	/*####################  Data array transformation ####################*/

	public static function transformArray($array, ARSchema $schema)
	{
throw new ApplicationException('test');
		$array = parent::transformArray($array, $schema);

		$array['paths'] = $array['urls'] = array();
		$baseUrl = self::getApplication()->getRouter()->getBaseUrl();

		foreach (self::getImageSizes() as $key => $value)
	  	{
			$manufacturerID = isset($array['Manufacturer']['ID']) ? $array['Manufacturer']['ID'] : (isset($array['manufacturerID']) ? $array['manufacturerID'] : false);

			if (!$manufacturerID)
			{
				break;
			}

			$array['paths'][$key] = self::getImagePath($array['ID'], $manufacturerID, $key);
			$array['urls'][$key] = $baseUrl . $array['paths'][$key];
		}

		return $array;
	}

	/*####################  Get related objects ####################*/

	public function getOwner()
	{
		return $this->manufacturer->get();
	}
}

?>