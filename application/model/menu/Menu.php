<?php

namespace menu;

/**
 * Menu definition
 *
 * @package application/model/menu
 * @author Integry Systems <http://integry.com>
 */
class Menu extends \system\MultilingualObject
{
	public $ID;
	public $handle;
	public $type;
	public $name;

	public function initialize()
	{
        $this->hasMany('ID', '\menu\MenuItem', 'menuID', array('alias' => 'MenuItems'));
	}
	
	public function getItems()
	{
		return $this->getRelated('MenuItems', array('order' => 'position'));
	}
}

?>
