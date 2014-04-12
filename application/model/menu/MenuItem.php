<?php

namespace menu;

/**
 * Menu definition
 *
 * @package application/model/menu
 * @author Integry Systems <http://integry.com>
 */
class MenuItem extends \system\MultilingualObject
{
	public $ID;
	public $handle;
	public $type;
	public $title;
	public $link;
	public $position;
	
	public function initialize()
	{
		$this->belongsTo('menuID', '\menu\Menu', 'ID');
		$this->hasOne('staticPageID', '\staticpage\StaticPage', 'ID', array('alias' => 'StaticPage'));
		$this->hasOne('categoryID', '\category\Category', 'ID', array('alias' => 'Category'));
	}
	
	public function getLink()
	{
		if ($page = $this->staticPage)
		{
			return $this->getDI()->get('url')->get(route($page));
		}
		else
		{
			return $this->link;
		}
	}
	
	public function getTitle()
	{
		if ($page = $this->staticPage)
		{
			return $page->title;
		}
		else
		{
			return $this->title;
		}
	}
}

?>
