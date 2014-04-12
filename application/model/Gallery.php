<?php

/**
 * Product image (icon). One product can have multiple images.
 *
 * @package application/model/product
 * @author Integry Systems <http://integry.com>
 */
class Gallery extends \ActiveRecordModel
{
	public $ID;
	public $name;
	public $position;

	public function initialize()
	{
        $this->hasOne('defaultImageID', 'GalleryImage', 'ID', array('alias' => 'DefaultImage'));
        $this->hasMany('ID', 'GalleryImage', 'galleryID', array('alias' => 'GalleryImages'));
	}
}

?>
